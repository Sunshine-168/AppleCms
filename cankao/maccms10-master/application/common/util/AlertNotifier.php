<?php
namespace app\common\util;

use think\Log;

/**
 * 告警通知的投递层。
 *
 * ★ 站内信绝不能用 Notify::broadcast() ★
 * broadcast() 写的是 user_id=0 的全站公告，而 Notify::listForUser() 的查询是
 * `user_id IN (0, $uid)` —— 也就是说全站每一个前台会员都会看到它。
 * 把「CPU 95%」「订单掉零」「MySQL 连接数爆了」推送给所有用户，
 * 是实打实的信息泄露。所以只能用 Notify::send($uid, ...) 逐个发给
 * monitor.notify_user_ids 里明确配置的管理员账号（默认空 = 不发站内信）。
 *
 * ★ 告警风暴防护 ★
 * 一个根因（MySQL 挂了）会同时点燃 5xx 突增、p95 劣化、连接数异常一大串规则。
 * 五层防护：
 *   1. 每规则静默窗口（rule_silence_min）—— 在 AlertEngine 里
 *   2. 全局每小时通知预算（原子扣减）—— 本类
 *   3. 单次 run 的条数上限 + 时间预算 —— 本类
 *   4. 渠道熔断（PushCircuit）—— 本类
 *   5. curl 硬超时（PushHttp）—— driver 层
 * 超预算的事件照样入库、后台可见，只是不外发。
 */
class AlertNotifier
{
    /** 每次 run 的推送条数上限，防止一轮 cron 被推送拖垮 */
    const DEFAULT_MAX_PER_RUN = 5;

    /** 每次 run 的推送时间预算（毫秒） */
    const DEFAULT_TIME_BUDGET_MS = 8000;

    /** 每小时全局通知预算 */
    const DEFAULT_BUDGET_HOUR = 20;

    /** 本次 run 已发送的条数与耗时，由 AlertEngine 在每轮开始时重置 */
    private static $sentThisRun = 0;
    private static $spentMs = 0;

    /**
     * @return void
     */
    public static function resetRun()
    {
        self::$sentThisRun = 0;
        self::$spentMs = 0;
    }

    /**
     * 投递一条告警（或恢复）通知。
     *
     * @param array         $event     事件行
     * @param array         $rule      规则行
     * @param bool          $isRecover 是否是恢复通知
     * @param callable|null $sender    ★测试注入点：function($channel,$title,$body,$ctx,$cfg){ return ['code'=>1,'msg'=>'']; }
     * @param int           $now
     * @return array ['sent'=>[],'failed'=>[],'skipped'=>[]]
     */
    public static function notify(array $event, array $rule, $isRecover, $sender = null, $now = 0)
    {
        $now = $now > 0 ? intval($now) : time();
        $cfg = self::config();
        $out = ['sent' => [], 'failed' => [], 'skipped' => []];

        $channels = self::parseChannels(isset($rule['rule_channels']) ? $rule['rule_channels'] : '');
        if (empty($channels)) {
            $out['skipped'][] = 'no channel configured';
            return $out;
        }

        // 防护 3：单次 run 的条数与时间预算。
        // 超出就把剩下的留给下一分钟 —— 事件已经入库，不会丢，只是晚一分钟发。
        $maxPerRun = isset($cfg['notify_max_per_run']) ? intval($cfg['notify_max_per_run']) : self::DEFAULT_MAX_PER_RUN;
        $timeBudget = isset($cfg['notify_time_budget_ms']) ? intval($cfg['notify_time_budget_ms']) : self::DEFAULT_TIME_BUDGET_MS;
        if (self::$sentThisRun >= max(1, $maxPerRun)) {
            $out['skipped'][] = 'per-run notification cap reached';
            return $out;
        }
        if (self::$spentMs >= max(500, $timeBudget)) {
            $out['skipped'][] = 'per-run time budget exhausted';
            return $out;
        }

        // 防护 2：全局每小时预算。恢复通知不占预算 —— 「警报解除」是站长最想看到的
        // 消息，不该被自己的告警风暴挤掉。
        // 这里只「只读检查」余额，真正的扣减放到成功发出至少一条之后（见循环末尾）：
        // 否则被熔断/发送失败、一条都没发出去的事件也会白扣预算，而失败不推进 silence
        // 窗口会让它每分钟重触发，一个坏渠道足以在几十分钟内耗尽额度、饿死健康告警。
        $cap = 0;
        $budgetKey = '';
        if (!$isRecover) {
            $cap = isset($cfg['notify_budget_hour']) ? intval($cfg['notify_budget_hour']) : self::DEFAULT_BUDGET_HOUR;
            $budgetKey = 'notify_budget_' . date('YmdH', $now);
            if (MonitorState::budgetExceeded($budgetKey, $cap)) {
                $out['skipped'][] = 'hourly notification budget exhausted (' . $cap . '/h)';
                return $out;
            }
        }

        $title = self::renderTitle($event, $rule, $isRecover);
        $body = self::renderBody($event, $rule, $isRecover, $now);
        $context = self::renderContext($event, $rule, $isRecover);

        $t0 = microtime(true);
        foreach ($channels as $ch) {
            // 防护 4：渠道熔断。一个挂掉的 webhook 每次要耗 5 秒；
            // 熔断后直接跳过，一次 curl 都不发起。
            $circuit = PushCircuit::isClosed($ch, $now);
            if (!$circuit['closed']) {
                $out['skipped'][] = $ch . ': ' . $circuit['reason'];
                continue;
            }

            $res = self::dispatch($ch, $title, $body, $context, $cfg, $sender);
            $ok = isset($res['code']) && intval($res['code']) === 1;

            PushCircuit::record($ch, $ok, $now);

            if ($ok) {
                $out['sent'][] = $ch;
            } else {
                $out['failed'][] = $ch . ': ' . (isset($res['msg']) ? $res['msg'] : 'unknown error');
                Log::error('[monitor] push failed via ' . $ch . ': ' . (isset($res['msg']) ? $res['msg'] : ''));
            }
        }
        self::$spentMs += intval(round((microtime(true) - $t0) * 1000));

        if (!empty($out['sent'])) {
            self::$sentThisRun++;
            // 真正发出去了才扣预算（见上：避免熔断/失败白扣、耗尽额度饿死告警）。
            if (!$isRecover) {
                MonitorState::consumeBudget($budgetKey, $cap, $now);
            }
        }
        return $out;
    }

    /**
     * 把一条通知投递到某个渠道。
     *
     * @param string        $channel
     * @param string        $title
     * @param string        $body
     * @param array         $context
     * @param array         $cfg
     * @param callable|null $sender
     * @return array
     */
    private static function dispatch($channel, $title, $body, array $context, array $cfg, $sender)
    {
        // 测试注入点：把真实的网络调用替换掉
        if (is_callable($sender)) {
            return call_user_func($sender, $channel, $title, $body, $context, $cfg);
        }

        if ($channel === 'notify') {
            return self::sendInSite($title, $body, $cfg);
        }
        if ($channel === 'email') {
            return self::sendEmail($title, $body, $cfg);
        }
        return mac_send_push($channel, $title, $body, $context, $cfg);
    }

    /**
     * 站内信：只发给明确配置的管理员账号。
     *
     * 绝不用 Notify::broadcast()（user_id=0）—— 那是全站公告，
     * 每一个前台会员都能看到服务器的 CPU、订单量和错误率。
     *
     * @param string $title
     * @param string $body
     * @param array  $cfg
     * @return array
     */
    private static function sendInSite($title, $body, array $cfg)
    {
        $raw = isset($cfg['notify_user_ids']) ? trim((string)$cfg['notify_user_ids']) : '';
        if ($raw === '') {
            return ['code' => 905, 'msg' => 'monitor.notify_user_ids not configured'];
        }

        $model = new \app\common\model\Notify();
        $sent = 0;
        foreach (explode(',', $raw) as $one) {
            $uid = intval(trim($one));
            if ($uid <= 0) {
                continue;
            }
            $res = $model->send($uid, 'system', $title, $body, '');
            if (is_array($res) && isset($res['code']) && intval($res['code']) === 1) {
                $sent++;
            }
        }
        if ($sent === 0) {
            return ['code' => 907, 'msg' => 'no valid recipient in monitor.notify_user_ids'];
        }
        return ['code' => 1, 'msg' => 'sent to ' . $sent . ' admin(s)'];
    }

    /**
     * @param string $title
     * @param string $body
     * @param array  $cfg
     * @return array
     */
    private static function sendEmail($title, $body, array $cfg)
    {
        $raw = isset($cfg['alert_emails']) ? trim((string)$cfg['alert_emails']) : '';
        if ($raw === '') {
            // 回退到站点邮箱
            $raw = isset($GLOBALS['config']['site']['site_email']) ? trim((string)$GLOBALS['config']['site']['site_email']) : '';
        }
        if ($raw === '') {
            return ['code' => 905, 'msg' => 'no alert email recipient configured'];
        }

        $sent = 0;
        $lastMsg = '';
        foreach (explode(',', $raw) as $one) {
            $to = trim($one);
            if ($to === '') {
                continue;
            }
            $res = mac_send_mail($to, $title, nl2br(htmlspecialchars($body, ENT_QUOTES, 'UTF-8')));
            if (is_array($res) && isset($res['code']) && intval($res['code']) === 1) {
                $sent++;
            } elseif (is_array($res)) {
                $lastMsg = isset($res['msg']) ? (string)$res['msg'] : '';
            }
        }
        if ($sent === 0) {
            return ['code' => 908, 'msg' => 'email delivery failed: ' . $lastMsg];
        }
        return ['code' => 1, 'msg' => 'sent to ' . $sent . ' address(es)'];
    }

    /**
     * @param string $raw
     * @return array
     */
    public static function parseChannels($raw)
    {
        $allowed = ['notify', 'email', 'webhook', 'telegram', 'dingtalk', 'wecom', 'serverchan'];
        $out = [];
        foreach (explode(',', (string)$raw) as $one) {
            $one = strtolower(trim($one));
            if ($one !== '' && in_array($one, $allowed, true) && !in_array($one, $out, true)) {
                $out[] = $one;
            }
        }
        return $out;
    }

    /**
     * @param array $event
     * @param array $rule
     * @param bool  $isRecover
     * @return string
     */
    public static function renderTitle(array $event, array $rule, $isRecover)
    {
        $sev = intval(isset($event['event_severity']) ? $event['event_severity'] : 2);
        $tag = $isRecover
            ? lang('admin/monitor/alert_recovered')
            : self::severityLabel($sev);
        $name = isset($rule['rule_name']) ? (string)$rule['rule_name'] : '';
        $site = isset($GLOBALS['config']['site']['site_name']) ? (string)$GLOBALS['config']['site']['site_name'] : '';
        return '[' . $tag . '] ' . $name . ($site !== '' ? ' - ' . $site : '');
    }

    /**
     * @param array $event
     * @param array $rule
     * @param bool  $isRecover
     * @param int   $now
     * @return string
     */
    public static function renderBody(array $event, array $rule, $isRecover, $now = 0)
    {
        $now = $now > 0 ? intval($now) : time();
        $lines = [];
        $lines[] = lang('admin/monitor/f_metric') . ': ' . (isset($event['event_metric']) ? $event['event_metric'] : '');
        $lines[] = lang('admin/monitor/current_value') . ': ' . self::num($event, 'event_value');
        $lines[] = lang('admin/monitor/f_threshold') . ': ' . self::opLabel($rule) . ' ' . self::num($event, 'event_threshold');
        $lines[] = lang('admin/monitor/started_at') . ': ' . date('Y-m-d H:i:s', intval($event['event_start_ts']));
        if ($isRecover) {
            $end = intval(isset($event['event_end_ts']) ? $event['event_end_ts'] : $now);
            $lines[] = lang('admin/monitor/recovered_at') . ': ' . date('Y-m-d H:i:s', $end > 0 ? $end : $now);
            $dur = max(0, ($end > 0 ? $end : $now) - intval($event['event_start_ts']));
            $lines[] = lang('admin/monitor/duration') . ': ' . self::humanDuration($dur);
        }
        if (!empty($event['event_summary'])) {
            $lines[] = (string)$event['event_summary'];
        }
        return implode("\n", $lines);
    }

    /**
     * webhook 用的结构化字段。
     *
     * @param array $event
     * @param array $rule
     * @param bool  $isRecover
     * @return array
     */
    public static function renderContext(array $event, array $rule, $isRecover)
    {
        return [
            'status'    => $isRecover ? 'resolved' : 'firing',
            'severity'  => self::severityKey(intval(isset($event['event_severity']) ? $event['event_severity'] : 2)),
            'rule'      => isset($rule['rule_name']) ? (string)$rule['rule_name'] : '',
            'metric'    => isset($event['event_metric']) ? (string)$event['event_metric'] : '',
            'value'     => floatval(isset($event['event_value']) ? $event['event_value'] : 0),
            'threshold' => floatval(isset($event['event_threshold']) ? $event['event_threshold'] : 0),
            'start_ts'  => intval(isset($event['event_start_ts']) ? $event['event_start_ts'] : 0),
            'end_ts'    => intval(isset($event['event_end_ts']) ? $event['event_end_ts'] : 0),
        ];
    }

    private static function num(array $row, $key)
    {
        $v = isset($row[$key]) ? floatval($row[$key]) : 0.0;
        return rtrim(rtrim(number_format($v, 4, '.', ''), '0'), '.');
    }

    private static function opLabel(array $rule)
    {
        $map = ['gt' => '>', 'gte' => '>=', 'lt' => '<', 'lte' => '<='];
        $op = isset($rule['rule_op']) ? (string)$rule['rule_op'] : 'gt';
        return isset($map[$op]) ? $map[$op] : '>';
    }

    private static function severityKey($sev)
    {
        if ($sev >= 3) {
            return 'critical';
        }
        return $sev <= 1 ? 'info' : 'warning';
    }

    private static function severityLabel($sev)
    {
        if ($sev >= 3) {
            return lang('admin/monitor/sev_critical');
        }
        return $sev <= 1 ? lang('admin/monitor/sev_info') : lang('admin/monitor/sev_warning');
    }

    private static function humanDuration($sec)
    {
        $sec = max(0, intval($sec));
        if ($sec < 60) {
            return $sec . 's';
        }
        $m = intdiv($sec, 60);
        if ($m < 60) {
            return $m . 'm';
        }
        return intdiv($m, 60) . 'h' . ($m % 60) . 'm';
    }

    private static function config()
    {
        return isset($GLOBALS['config']['monitor']) && is_array($GLOBALS['config']['monitor'])
            ? $GLOBALS['config']['monitor'] : [];
    }
}
