<?php
namespace app\common\util;

use think\Db;
use think\Log;

/**
 * 监控 cron 端点的全部业务。控制器只做鉴权 + 转发。
 *
 * 为什么不挂在 application/extra/timming.php 的定时任务体系上：
 * api/controller/Timming.php 的调度用「周-小时」去重，
 * 同一任务每小时最多执行一次，撑不起分钟级采样。
 * 而改核心调度器会动到全站稳定路径与新旧两套后台视图，风险不匹配收益。
 * 所以另开一个专用端点，Timming.php 一行都不动。
 *
 * 每个阶段独立 try/catch：一个阶段炸了不影响其他阶段，错误累进 data['errors']。
 */
class MonitorCron
{
    /**
     * 每分钟最多执行一次的闸门。
     *
     * 取 55 秒而不是 60：crontab 的触发时刻会有秒级抖动，
     * 卡死 60 秒会让「第 59 秒触发的那一次」被误判为重复而丢掉一分钟的数据。
     */
    const GATE_KEY = 'due.cron';
    const GATE_SEC = 55;

    /**
     * @param int $now
     * @return array ['code'=>1,'msg'=>'','data'=>['took_ms'=>..,'stages'=>[..],'errors'=>[..]]]
     */
    public static function run($now = 0)
    {
        $t0 = microtime(true);
        $now = $now > 0 ? intval($now) : time();
        $stages = [];
        $errors = [];

        // 每分钟闸门：crontab 叠跑、站长手动点两下、监控探针误触，全部只跑一次。
        // 注意这是闸门不是锁，执行完不释放 —— 释放了就等于没有幂等性。
        if (!MonitorState::due(self::GATE_KEY, self::GATE_SEC, $now)) {
            return [
                'code' => 1,
                'msg'  => 'skipped',
                'data' => [
                    'took_ms' => self::tookMs($t0),
                    'stages'  => ['locked'],
                    'errors'  => [],
                ],
            ];
        }

        $cfg = self::config();

        self::stage($stages, $errors, 'flush', function () use ($now) {
            return self::flushRequestBuckets($now);
        });

        self::stage($stages, $errors, 'collect', function () use ($now) {
            return self::collectSystem($now);
        });

        self::stage($stages, $errors, 'biz', function () use ($now) {
            return self::collectBiz($now);
        });

        // dead-man switch 的心跳：后台据此判断「监控 cron 是否还活着」
        self::stage($stages, $errors, 'heartbeat', function () use ($now) {
            MonitorState::set('cron_heartbeat', $now, '');
            return 1;
        });

        // 异常访问检测。必须排在 alert 之前：它写回的 sec.* 派生指标
        // 就是告警规则的数据源，晚一步的话规则要等到下一分钟才看得见。
        self::stage($stages, $errors, 'access', function () use ($now, $cfg) {
            if (empty($cfg['access_track_enabled']) || (string)$cfg['access_track_enabled'] !== '1') {
                return null;
            }
            if (!MonitorState::due('due.access', 60, $now)) {
                return null;
            }
            $r = AbnormalAccessDetector::run($now);
            return $r['suspicious'] . '/' . $r['high'];
        });

        // 告警评估。放在采集与 flush 之后，这样规则看到的是本分钟最新的数据。
        self::stage($stages, $errors, 'alert', function () use ($now) {
            if (!MonitorState::due('due.alert', 60, $now)) {
                return null;
            }
            $r = AlertEngine::run($now);
            return $r['fired'] . '/' . $r['resolved'] . '/' . $r['notified'];
        });

        // 运营数据异常检测。每小时一次 —— 数据源（analytics_hour_dim）本身就是小时聚合的，
        // 每分钟跑只会为同一份数据反复拉 14 天基线，白花一堆查询。
        self::stage($stages, $errors, 'anomaly', function () use ($now) {
            if (!MonitorState::due('due.anomaly', 3600, $now)) {
                return null;
            }
            $r = AlertEngine::run($now, null, 'analytics');
            return $r['fired'] . '/' . $r['resolved'] . '/' . $r['notified'];
        });

        self::stage($stages, $errors, 'rollup', function () use ($now) {
            if (!MonitorState::due('due.rollup', 3600, $now)) {
                return null;
            }
            // 卷上一个整点，保证那一小时的分钟数据已经齐了
            $hourStart = intval(floor($now / 3600) * 3600) - 3600;
            return MonitorMetric::rollupHour($hourStart);
        });

        self::stage($stages, $errors, 'purge', function () use ($now, $cfg) {
            if (!MonitorState::due('due.purge', 86400, $now)) {
                return null;
            }
            $res = MonitorMetric::purge(
                isset($cfg['retain_min_days']) ? $cfg['retain_min_days'] : 3,
                isset($cfg['retain_hour_days']) ? $cfg['retain_hour_days'] : 90
            );
            $access = AbnormalAccessDetector::purge(
                isset($cfg['retain_access_days']) ? $cfg['retain_access_days'] : 30
            );
            MonitorBucket::gc(3600);
            MonitorState::purgeBudgetBefore($now);
            return $res['min'] + $res['hour'] + $access;
        });

        // 外部 heartbeat：这是唯一能发现「整台机器挂了」的手段。
        // 机器挂了 -> PHP 挂了 -> cron 端点打不通 -> 告警引擎也跟着死，
        // 站内自监控对此有固有盲区，只能靠外部探测兜底。
        self::stage($stages, $errors, 'ping', function () use ($cfg) {
            $url = isset($cfg['heartbeat_url']) ? trim((string)$cfg['heartbeat_url']) : '';
            if ($url === '') {
                return null;
            }
            return self::pingHeartbeat($url) ? 1 : 0;
        });

        return [
            'code' => 1,
            'msg'  => 'ok',
            'data' => [
                'took_ms' => self::tookMs($t0),
                'stages'  => $stages,
                'errors'  => $errors,
            ],
        ];
    }

    /**
     * 执行一个阶段并记录结果。任何异常都被吞掉并记进 errors，不中断后续阶段。
     *
     * @param array    $stages
     * @param array    $errors
     * @param string   $name
     * @param callable $fn
     * @return void
     */
    private static function stage(array &$stages, array &$errors, $name, $fn)
    {
        try {
            $res = call_user_func($fn);
            if ($res === null) {
                return; // 未到期，不记入 stages
            }
            $stages[$name] = $res;
        } catch (\Throwable $e) {
            $errors[$name] = $e->getMessage();
            Log::error('[monitor] stage ' . $name . ' failed: ' . $e->getMessage());
        }
    }

    /**
     * 把已封闭分钟的请求桶落库。
     *
     * @param int $now
     * @return int 写入的指标行数
     */
    private static function flushRequestBuckets($now)
    {
        $buckets = MonitorBucket::drainClosed($now, 30);
        if (empty($buckets)) {
            return 0;
        }
        $written = 0;
        foreach ($buckets as $statMin => $fields) {
            $rows = [];
            foreach ($fields as $k => $v) {
                $rows[] = ['k' => $k, 't' => MonitorMetric::TYPE_COUNTER, 'v' => $v];
            }
            $written += MonitorMetric::upsertMany($statMin, $rows);
        }
        return $written;
    }

    /**
     * @param int $now
     * @return int
     */
    private static function collectSystem($now)
    {
        $res = MonitorCollector::collect();
        $statMin = intval(floor($now / 60) * 60);
        if (!empty($res['skipped'])) {
            MonitorState::set('capabilities', $now, json_encode($res['skipped']));
        }
        return MonitorMetric::upsertMany($statMin, $res['metrics']);
    }

    /**
     * 在线人数与近 5 分钟 PV。
     *
     * 数据源优先用已有的埋点表 mac_analytics_pageview；
     * 若埋点未部署或表不存在，回退到请求分钟桶推算，绝不 500
     * （沿用 admin/controller/Analytics.php::isAnalyticsTableMissing() 的降级思路）。
     *
     * @param int $now
     * @return int
     */
    private static function collectBiz($now)
    {
        $statMin = intval(floor($now / 60) * 60);
        $from = $now - 300;
        $rows = [];

        if (self::analyticsPageviewExists()) {
            $pre = Db::getConfig('prefix');
            $r = Db::query(
                "SELECT COUNT(DISTINCT `visitor_id`) AS `uv`, COUNT(*) AS `pv` FROM `{$pre}analytics_pageview` WHERE `ts` >= ?",
                [$from]
            );
            $uv = !empty($r) ? intval($r[0]['uv']) : 0;
            $pv = !empty($r) ? intval($r[0]['pv']) : 0;
            $rows[] = ['k' => 'biz.online', 't' => MonitorMetric::TYPE_GAUGE, 'v' => $uv];
            $rows[] = ['k' => 'biz.pv5m', 't' => MonitorMetric::TYPE_GAUGE, 'v' => $pv];
        } else {
            // 回退：用近 5 分钟的请求数估算，后台卡片会标注「基于请求数估算」
            $pre = Db::getConfig('prefix');
            $r = Db::query(
                "SELECT SUM(`metric_value`) AS `s` FROM `{$pre}monitor_metric_min` WHERE `metric_key`='http.req' AND `stat_min` >= ?",
                [intval(floor($from / 60) * 60)]
            );
            $req = (!empty($r) && $r[0]['s'] !== null) ? intval(round(floatval($r[0]['s']))) : 0;
            $rows[] = ['k' => 'biz.pv5m', 't' => MonitorMetric::TYPE_GAUGE, 'v' => $req];
        }

        return MonitorMetric::upsertMany($statMin, $rows);
    }

    /**
     * @return bool
     */
    private static function analyticsPageviewExists()
    {
        try {
            $pre = Db::getConfig('prefix');
            $rows = Db::query("SHOW TABLES LIKE '{$pre}analytics_pageview'");
            return !empty($rows);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * 外部 dead-man switch 的 ping（healthchecks.io / Uptime Kuma 风格）。
     *
     * 不用 mac_curl_post()：它的 CURLOPT_TIMEOUT 硬编码 30 秒，
     * 一个挂掉的 ping 端点会让每分钟的 cron 卡 30 秒。
     *
     * @param string $url
     * @return bool
     */
    private static function pingHeartbeat($url)
    {
        // 与 webhook 推送同款 SSRF 守卫：拦私网/回环/云元数据地址，防止 heartbeat_url
        // 被配成盲 SSRF 内网探针。scheme（http/https）校验已包含在 guardUrl 内。
        if (\app\common\extend\push\PushHttp::guardUrl($url) !== null) {
            return false;
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_exec($ch);
        $code = intval(curl_getinfo($ch, CURLINFO_HTTP_CODE));
        curl_close($ch);
        return $code >= 200 && $code < 400;
    }

    /**
     * @param float $t0
     * @return int
     */
    private static function tookMs($t0)
    {
        return intval(round((microtime(true) - $t0) * 1000));
    }

    /**
     * @return array
     */
    private static function config()
    {
        return isset($GLOBALS['config']['monitor']) && is_array($GLOBALS['config']['monitor'])
            ? $GLOBALS['config']['monitor'] : [];
    }
}
