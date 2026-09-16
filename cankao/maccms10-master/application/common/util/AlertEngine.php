<?php
namespace app\common\util;

use think\Db;
use think\Log;

/**
 * 告警规则引擎。
 *
 * 每分钟被 MonitorCron 调用一次，遍历启用的规则，
 * 评估 -> 判定持续时间 -> 开/关事件 -> 投递通知。
 */
class AlertEngine
{
    /**
     * 序列断裂阈值：连续这么久没有跑过 evaluate，就认为 pending 序列已经断了。
     *
     * cron 每分钟一次，留 3 倍余量。
     */
    const SEQUENCE_BREAK_SEC = 180;

    /** p95 规则的最小样本数，样本太少时分位数没有意义，宁可不报也不误报 */
    const MIN_P95_SAMPLES = 20;

    /**
     * @param int           $now
     * @param callable|null $sender 测试注入点，透传给 AlertNotifier
     * @param string        $source 只评估这一类规则：metric（分钟级）| analytics（小时级）
     * @return array
     */
    public static function run($now = 0, $sender = null, $source = 'metric')
    {
        $now = $now > 0 ? intval($now) : time();
        AlertNotifier::resetRun();

        $stat = ['evaluated' => 0, 'fired' => 0, 'resolved' => 0, 'notified' => 0, 'suppressed' => 0, 'skipped' => 0];

        // metric 规则每分钟评估，analytics 规则每小时评估（数据源本身就是小时聚合的）。
        // 分开跑，免得每分钟为 analytics 规则重复拉 14 天基线 —— 那是一堆白花的查询。
        $source = ($source === 'analytics') ? 'analytics' : 'metric';
        $rules = Db::name('MonitorAlertRule')
            ->where('rule_status', 1)
            ->where('rule_source', $source)
            ->select();
        foreach ($rules as $rule) {
            try {
                $stat['evaluated']++;
                $res = self::evaluate($rule, $now);
                if ($res === null) {
                    // 样本不足 / 数据源缺失：既不告警也不判恢复，直接跳过这一轮。
                    // 「没有数据」不等于「一切正常」—— 把它当成正常会让恢复通知误发。
                    $stat['skipped']++;
                    continue;
                }

                if ($res['hit']) {
                    self::handleHit($rule, $res, $now, $sender, $stat);
                } else {
                    self::handleClear($rule, $res, $now, $sender, $stat);
                }
            } catch (\Throwable $e) {
                Log::error('[monitor] alert rule ' . $rule['rule_id'] . ' failed: ' . $e->getMessage());
            }
        }

        return $stat;
    }

    /**
     * 评估一条规则。
     *
     * @param array $rule
     * @param int   $now
     * @return array|null null = 样本不足，跳过（不是「没超阈」）
     */
    public static function evaluate(array $rule, $now)
    {
        // 运营数据异常检测：只是换了一个取数与判定的分支，
        // 事件生命周期（触发 / 静默 / 恢复 / 通知 / 熔断 / 预算）完全复用下面同一套代码。
        if ((string)$rule['rule_source'] === 'analytics') {
            $r = AnalyticsAnomaly::detect($rule, $now);
            if ($r === null) {
                return null;
            }
            return [
                'hit'      => $r['hit'],
                'value'    => $r['value'],
                'baseline' => $r['baseline'],
                'summary'  => $r['summary'],
            ];
        }

        $window = max(1, intval($rule['rule_window_min']));
        $to = intval($now);
        $from = $to - ($window * 60);

        $agg = (string)$rule['rule_agg'];
        $metric = (string)$rule['rule_metric'];

        if ($agg === 'p95') {
            $value = self::evaluateP95($from, $to);
            if ($value === null) {
                return null;
            }
        } else {
            $res = MonitorMetric::fetchSeries([$metric], $from, $to, 'min');
            $series = isset($res['series'][$metric]) ? $res['series'][$metric] : [];
            $value = MonitorMetric::aggregate($series, $agg);
            if ($value === null) {
                return null;
            }
        }

        $threshold = floatval($rule['rule_threshold']);
        $hit = self::compare($value, (string)$rule['rule_op'], $threshold);

        return [
            'hit'     => $hit,
            'value'   => $value,
            'summary' => sprintf(
                '%s(%s, %dm) = %s',
                $agg,
                $metric,
                $window,
                rtrim(rtrim(number_format($value, 4, '.', ''), '0'), '.')
            ),
        ];
    }

    /**
     * p95 的专用路径。
     *
     * ★ 分位数不可平均 ★
     * 绝不能先算出每分钟的 p95 再对这些 p95 取 avg/max —— 那在统计上没有任何意义
     * （一分钟全是 50ms、一分钟全是 3000ms，"平均 p95" 得到 1525ms，
     *  但真实的合并 p95 是 3000ms）。
     * 正确做法是把窗口内 12 个延迟桶的 counter 逐位加总，
     * 得到合并直方图，再在合并后的直方图上估分位数。
     *
     * @param int $from
     * @param int $to
     * @return float|null
     */
    private static function evaluateP95($from, $to)
    {
        $buckets = MonitorMetric::fetchLatencyHistogram($from, $to);
        $r = MonitorMetric::estimatePercentile($buckets, 0.95);
        if ($r === null || $r['total'] < self::MIN_P95_SAMPLES) {
            return null;
        }
        return floatval($r['value']);
    }

    /**
     * @param float  $value
     * @param string $op
     * @param float  $threshold
     * @return bool
     */
    public static function compare($value, $op, $threshold)
    {
        switch ($op) {
            case 'gte':
                return $value >= $threshold;
            case 'lt':
                return $value < $threshold;
            case 'lte':
                return $value <= $threshold;
            case 'gt':
            default:
                return $value > $threshold;
        }
    }

    /**
     * @param array  $rule
     * @param string $dim
     * @return string
     */
    public static function fingerprint(array $rule, $dim = '')
    {
        return md5(intval($rule['rule_id']) . '|' . (string)$rule['rule_metric'] . '|' . (string)$dim);
    }

    // ------------------------------------------------------------------
    // 持续时间判定
    // ------------------------------------------------------------------

    /**
     * 条件满足时的处理。
     *
     * ★ 为什么用 pending_since 时间戳而不是「连续 N 次命中」计数器 ★
     * cron 会漏跑（机器忙、crontab 被停、部署重启）。用计数器的话，
     * 一次漏跑就把「连续」的语义搞坏了。
     *
     * ★ 为什么必须做序列断裂检测 ★
     * 光有 pending_since 还不够：如果 cron 停了两小时再恢复，
     * 第一次跑就会发现 now - since 已经两小时 >> for_min，立刻炸出一堆假告警 ——
     * 但那两小时里我们根本没有观测过，凭什么说它「持续超阈」了两小时？
     * 所以一旦发现距上次命中超过 SEQUENCE_BREAK_SEC，就把序列重置，重新开始计时。
     *
     * @param array         $rule
     * @param array         $res
     * @param int           $now
     * @param callable|null $sender
     * @param array         $stat
     * @return void
     */
    private static function handleHit(array $rule, array $res, $now, $sender, array &$stat)
    {
        $ruleId = intval($rule['rule_id']);

        // ★ analytics 规则不走「持续 N 分钟」判定 ★
        //
        // 它们每小时才评估一次，而序列断裂阈值是 180 秒 —— 两次评估之间隔了 3600 秒，
        // 每一次都会被判成「序列断了」而重置 pending，于是 rule_for_min > 0 的规则
        // 永远也累积不到触发条件，一辈子都告不出警。
        //
        // 而且「持续 N 分钟」对小时粒度的数据本来就没有意义：
        // 一个小时的 PV 已经是聚合过的结果，它不会「抖动」。
        // 所以这里直接触发，rule_for_min 在 analytics 规则上被忽略（后台表单也会隐藏它）。
        if ((string)$rule['rule_source'] === 'analytics') {
            MonitorState::purgeByPrefix('alert.recover.' . $ruleId);
            self::fire($rule, $res, $now, $now, $sender, $stat);
            return;
        }

        $pendingKey = 'alert.pending.' . $ruleId;

        $raw = MonitorState::getVal($pendingKey, '');
        $state = $raw !== '' ? json_decode($raw, true) : null;
        if (!is_array($state)) {
            $state = ['since' => 0, 'last_hit' => 0];
        }

        $since = intval(isset($state['since']) ? $state['since'] : 0);
        $lastHit = intval(isset($state['last_hit']) ? $state['last_hit'] : 0);

        if ($since === 0 || ($now - $lastHit) > self::SEQUENCE_BREAK_SEC) {
            // 首次命中，或者观测序列断了 -> 重新开始计时
            $since = $now;
        }
        MonitorState::set($pendingKey, $now, json_encode(['since' => $since, 'last_hit' => $now]));

        $forSec = max(0, intval($rule['rule_for_min'])) * 60;
        if (($now - $since) < $forSec) {
            // 还没持续够久，不触发
            return;
        }

        // 恢复计时清零
        MonitorState::purgeByPrefix('alert.recover.' . $ruleId);

        self::fire($rule, $res, $since, $now, $sender, $stat);
    }

    /**
     * 条件不满足时的处理：清 pending，累计恢复时长，够久就判恢复。
     *
     * @param array         $rule
     * @param array         $res
     * @param int           $now
     * @param callable|null $sender
     * @param array         $stat
     * @return void
     */
    private static function handleClear(array $rule, array $res, $now, $sender, array &$stat)
    {
        $ruleId = intval($rule['rule_id']);
        MonitorState::purgeByPrefix('alert.pending.' . $ruleId);

        $active = self::activeEvent($rule);
        if (empty($active)) {
            return;
        }

        $recoverKey = 'alert.recover.' . $ruleId;
        $since = MonitorState::getNum($recoverKey, 0);
        if ($since === 0) {
            MonitorState::set($recoverKey, $now, '');
            $since = $now;
        }

        $recoverSec = max(0, intval($rule['rule_recover_min'])) * 60;
        if (($now - $since) < $recoverSec) {
            return;
        }

        Db::name('MonitorAlertEvent')
            ->where('event_id', intval($active['event_id']))
            ->update([
                'event_status' => 2,
                'event_end_ts' => $now,
            ]);
        MonitorState::purgeByPrefix($recoverKey);
        $stat['resolved']++;

        $active['event_end_ts'] = $now;
        $delivery = AlertNotifier::notify($active, $rule, true, $sender, $now);
        if (!empty($delivery['sent'])) {
            $stat['notified']++;
        }
        self::recordDelivery(intval($active['event_id']), $delivery, $now, false);
    }

    // ------------------------------------------------------------------
    // 事件生命周期
    // ------------------------------------------------------------------

    /**
     * 开启或更新一条告警事件。
     *
     * ★ 并发唯一性由数据库保证，而不是 PHP ★
     * uk_active(event_fingerprint, event_end_ts) 上，活跃事件 end_ts 恒为 0，
     * 所以同一指纹最多只能存在一条活跃事件。
     * 两个 cron 进程真的可能同时走到这里（分钟闸门是 55 秒 TTL，理论上有窗口），
     * 此时第二个 INSERT 会撞唯一键 —— catch 住转成 UPDATE 即可。
     * 这里不开事务：单条 INSERT + catch 就够了。
     *
     * @param array         $rule
     * @param array         $res
     * @param int           $since
     * @param int           $now
     * @param callable|null $sender
     * @param array         $stat
     * @return void
     */
    private static function fire(array $rule, array $res, $since, $now, $sender, array &$stat)
    {
        $fp = self::fingerprint($rule);
        $active = self::activeEvent($rule);

        if (empty($active)) {
            $row = [
                'rule_id'           => intval($rule['rule_id']),
                'rule_name'         => (string)$rule['rule_name'],
                'event_metric'      => (string)$rule['rule_metric'],
                'event_severity'    => intval($rule['rule_severity']),
                'event_status'      => 1,
                'event_value'       => $res['value'],
                'event_threshold'   => floatval($rule['rule_threshold']),
                'event_baseline'    => isset($res['baseline']) ? floatval($res['baseline']) : 0,
                'event_summary'     => mb_substr((string)$res['summary'], 0, 500),
                'event_fingerprint' => $fp,
                'event_start_ts'    => intval($since),
                'event_last_ts'     => $now,
                'event_end_ts'      => 0,
                'event_notify_ts'   => 0,
                'event_notify_cnt'  => 0,
            ];

            try {
                $eventId = Db::name('MonitorAlertEvent')->insertGetId($row);
                $row['event_id'] = intval($eventId);
                $stat['fired']++;

                // 首次触发一定通知
                $delivery = AlertNotifier::notify($row, $rule, false, $sender, $now);
                self::afterNotify(intval($eventId), $delivery, $now, $stat);
                return;
            } catch (\Exception $e) {
                // 撞到 uk_active：另一个进程刚开了同一条事件。重新取出来，走更新分支。
                $active = self::activeEvent($rule);
                if (empty($active)) {
                    throw $e;
                }
            }
        }

        // 已有活跃事件：更新现值，按静默窗口决定要不要重复提醒
        Db::name('MonitorAlertEvent')
            ->where('event_id', intval($active['event_id']))
            ->update([
                'event_last_ts' => $now,
                'event_value'   => $res['value'],
                'event_summary' => mb_substr((string)$res['summary'], 0, 500),
            ]);

        $silenceSec = max(0, intval($rule['rule_silence_min'])) * 60;
        $lastNotify = intval($active['event_notify_ts']);

        if ($silenceSec > 0 && $lastNotify > 0 && ($now - $lastNotify) < $silenceSec) {
            // 静默窗口内：数据库已更新（后台看得到最新值），但不外发
            $stat['suppressed']++;
            return;
        }

        $active['event_value'] = $res['value'];
        $active['event_summary'] = $res['summary'];
        $delivery = AlertNotifier::notify($active, $rule, false, $sender, $now);
        self::afterNotify(intval($active['event_id']), $delivery, $now, $stat);
    }

    /**
     * @param int   $eventId
     * @param array $delivery
     * @param int   $now
     * @param array $stat
     * @return void
     */
    private static function afterNotify($eventId, array $delivery, $now, array &$stat)
    {
        $sent = !empty($delivery['sent']);
        if ($sent) {
            $stat['notified']++;
        } else {
            $stat['suppressed']++;
        }
        self::recordDelivery($eventId, $delivery, $now, $sent);
    }

    /**
     * 写回投递结果。
     *
     * 只有真的发出去了才推进 event_notify_ts —— 否则被预算/熔断挡下的那一条
     * 会被误认为「已经通知过」，然后进入静默窗口，等于彻底丢掉。
     *
     * @param int   $eventId
     * @param array $delivery
     * @param int   $now
     * @param bool  $advanceNotifyTs
     * @return void
     */
    private static function recordDelivery($eventId, array $delivery, $now, $advanceNotifyTs)
    {
        $parts = [];
        if (!empty($delivery['sent'])) {
            $parts[] = 'sent:' . implode('/', $delivery['sent']);
        }
        if (!empty($delivery['failed'])) {
            $parts[] = 'failed:' . implode('/', $delivery['failed']);
        }
        if (!empty($delivery['skipped'])) {
            $parts[] = 'skipped:' . implode('/', $delivery['skipped']);
        }

        $update = ['event_notify_result' => mb_substr(implode(' | ', $parts), 0, 500)];
        if ($advanceNotifyTs) {
            $update['event_notify_ts'] = $now;
            $update['event_notify_cnt'] = Db::raw('`event_notify_cnt` + 1');
        }

        Db::name('MonitorAlertEvent')->where('event_id', intval($eventId))->update($update);
    }

    /**
     * @param array $rule
     * @return array
     */
    private static function activeEvent(array $rule)
    {
        $row = Db::name('MonitorAlertEvent')
            ->where('event_fingerprint', self::fingerprint($rule))
            ->where('event_end_ts', 0)
            ->find();
        return is_array($row) ? $row : [];
    }
}
