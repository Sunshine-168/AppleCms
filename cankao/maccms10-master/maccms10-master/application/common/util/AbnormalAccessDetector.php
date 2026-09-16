<?php
namespace app\common\util;

use think\Db;

/**
 * 异常访问检测。
 *
 * 数据源是 MonitorRequest / AntiScrape 在请求路径上打到分钟桶里的 per-IP 计数
 * （零 DB 写入），由 cron 每分钟 drain 一次。
 *
 * ★ 两条设计红线 ★
 *
 * 1. 只落库「已达可疑阈值」的 IP，正常 IP 一行都不写。
 *    「每请求零 DB 写入」的红线不能在这里被绕过成「每 IP 每分钟一行」——
 *    一个正常站点每分钟有几千个不同 IP，那等于每分钟写几千行。
 *
 * 2. 不写第二套告警逻辑。
 *    检测结果被写回 mac_monitor_metric_min 作为 sec.* 派生指标，
 *    于是完全复用 AlertEngine 的规则引擎（rule_source='metric'）——
 *    静默、恢复、通知、熔断、预算全都直接继承，一行都不用重写。
 */
class AbnormalAccessDetector
{
    /** 单 IP 单分钟请求数达到这个量就算可疑（CC 嫌疑的入门线） */
    const DEFAULT_CC_THRESHOLD = 120;

    /** 单 IP 单分钟 4xx 达到这个量就算可疑（扫描/爆破的典型特征） */
    const DEFAULT_ERR4_THRESHOLD = 20;

    /**
     * @param int $now
     * @return array ['minutes'=>N,'suspicious'=>N,'high'=>N]
     */
    public static function run($now = 0)
    {
        $now = $now > 0 ? intval($now) : time();
        $buckets = MonitorBucket::drainClosedIp($now, 30);

        $stat = ['minutes' => 0, 'suspicious' => 0, 'high' => 0];
        if (empty($buckets)) {
            return $stat;
        }

        $ccTh = self::ccThreshold();
        $err4Th = self::err4Threshold();

        foreach ($buckets as $statMin => $ips) {
            $stat['minutes']++;

            $rows = [];
            $maxHits = 0;
            $scanTotal = 0;
            $blockedTotal = 0;
            $highCount = 0;

            foreach ($ips as $ip => $agg) {
                $hit = isset($agg['h']) ? intval($agg['h']) : 0;
                $e4 = isset($agg['e4']) ? intval($agg['e4']) : 0;
                $e5 = isset($agg['e5']) ? intval($agg['e5']) : 0;
                $scan = isset($agg['scan']) ? intval($agg['scan']) : 0;
                $badUa = isset($agg['bad_ua']) ? intval($agg['bad_ua']) : 0;
                $blk = isset($agg['blk']) ? intval($agg['blk']) : 0;

                // 派生指标是对「所有」IP 统计的，不只是可疑的那些
                $maxHits = max($maxHits, $hit);
                $scanTotal += $scan;
                $blockedTotal += $blk;

                if (!self::isSuspicious($hit, $e4, $scan, $blk, $ccTh, $err4Th)) {
                    continue; // ★ 正常 IP 一行都不写
                }

                $score = self::riskScore($hit, $e4, $scan, $badUa, $blk, $ccTh, $err4Th);
                $level = self::riskLevel($score);
                if ($level === 2) {
                    $highCount++;
                }

                $rows[] = [
                    'access_ip'    => mb_substr((string)$ip, 0, 45),
                    'stat_min'     => intval($statMin),
                    'hit_cnt'      => $hit,
                    'err4_cnt'     => $e4,
                    'err5_cnt'     => $e5,
                    'scan_cnt'     => $scan,
                    'bad_ua_cnt'   => $badUa,
                    'blocked_cnt'  => $blk,
                    'risk_score'   => $score,
                    'access_level' => $level,
                    'last_ua'      => mb_substr(isset($agg['ua']) ? (string)$agg['ua'] : '', 0, 255),
                    'last_path'    => mb_substr(isset($agg['path']) ? (string)$agg['path'] : '', 0, 255),
                    'updated_at'   => $now,
                ];
                $stat['suspicious']++;
            }
            $stat['high'] += $highCount;

            if (!empty($rows)) {
                self::upsert($rows);
            }

            // 派生指标写回时序表 -> 告警引擎直接就能对它们下规则
            MonitorMetric::upsertMany(intval($statMin), [
                ['k' => 'sec.cc_max_hits', 't' => MonitorMetric::TYPE_GAUGE, 'v' => $maxHits],
                ['k' => 'sec.abnormal_ip_high', 't' => MonitorMetric::TYPE_GAUGE, 'v' => $highCount],
                ['k' => 'sec.scan_hits', 't' => MonitorMetric::TYPE_COUNTER, 'v' => $scanTotal],
                ['k' => 'sec.blocked', 't' => MonitorMetric::TYPE_COUNTER, 'v' => $blockedTotal],
            ]);
        }

        return $stat;
    }

    /**
     * 是否达到落库门槛。
     *
     * @param int $hit
     * @param int $e4
     * @param int $scan
     * @param int $blk
     * @param int $ccTh
     * @param int $err4Th
     * @return bool
     */
    public static function isSuspicious($hit, $e4, $scan, $blk, $ccTh, $err4Th)
    {
        // 命中扫描路径或被限流拦截过，哪怕只有一次也值得记下来
        return $hit >= $ccTh || $scan >= 1 || $blk >= 1 || $e4 >= $err4Th;
    }

    /**
     * 风险分 0-100。
     *
     * 权重的取舍：
     *  - 请求量（40）：CC 的主要特征，但正常用户在高峰期也可能不低，所以不给满权重
     *  - 扫描路径（30）：正经用户不会去请求 /.env —— 这些路径在本站根本不存在，
     *                    所以它是高置信度信号，每命中一次就加 10 分
     *  - 4xx（20）：爆破/枚举的特征，但也可能只是链接失效，权重压低
     *  - 坏 UA（10）：辅助信号，单独出现不足以定罪
     *  - 被拦截（10）：AntiScrape 已经判过一次，这里只作为佐证
     *
     * @return int
     */
    public static function riskScore($hit, $e4, $scan, $badUa, $blk, $ccTh, $err4Th)
    {
        $ccTh = max(1, intval($ccTh));
        $err4Th = max(1, intval($err4Th));

        $score = 0.0;
        $score += min(40.0, ($hit / $ccTh) * 40.0);
        $score += min(30.0, $scan * 10.0);
        $score += min(20.0, ($e4 / $err4Th) * 20.0);
        $score += ($badUa > 0) ? 10.0 : 0.0;
        $score += ($blk > 0) ? 10.0 : 0.0;

        return min(100, max(0, (int)round($score)));
    }

    /**
     * @param int $score
     * @return int 0低 1中 2高
     */
    public static function riskLevel($score)
    {
        $score = intval($score);
        if ($score >= 60) {
            return 2;
        }
        return $score >= 35 ? 1 : 0;
    }

    /**
     * @param array $rows
     * @return void
     */
    private static function upsert(array $rows)
    {
        $table = Db::getConfig('prefix') . 'monitor_abnormal_access';
        $placeholders = [];
        $bind = [];

        foreach ($rows as $r) {
            $placeholders[] = '(?,?,?,?,?,?,?,?,?,?,?,?,?)';
            $bind[] = $r['access_ip'];
            $bind[] = $r['stat_min'];
            $bind[] = $r['hit_cnt'];
            $bind[] = $r['err4_cnt'];
            $bind[] = $r['err5_cnt'];
            $bind[] = $r['scan_cnt'];
            $bind[] = $r['bad_ua_cnt'];
            $bind[] = $r['blocked_cnt'];
            $bind[] = $r['risk_score'];
            $bind[] = $r['access_level'];
            $bind[] = $r['last_ua'];
            $bind[] = $r['last_path'];
            $bind[] = $r['updated_at'];
        }

        Db::execute(
            "INSERT INTO `{$table}` (`access_ip`,`stat_min`,`hit_cnt`,`err4_cnt`,`err5_cnt`,`scan_cnt`,`bad_ua_cnt`,"
            . "`blocked_cnt`,`risk_score`,`access_level`,`last_ua`,`last_path`,`updated_at`) VALUES "
            . implode(',', $placeholders)
            . " ON DUPLICATE KEY UPDATE `hit_cnt`=VALUES(`hit_cnt`),`err4_cnt`=VALUES(`err4_cnt`),`err5_cnt`=VALUES(`err5_cnt`),"
            . "`scan_cnt`=VALUES(`scan_cnt`),`bad_ua_cnt`=VALUES(`bad_ua_cnt`),`blocked_cnt`=VALUES(`blocked_cnt`),"
            . "`risk_score`=VALUES(`risk_score`),`access_level`=VALUES(`access_level`),`last_ua`=VALUES(`last_ua`),"
            . "`last_path`=VALUES(`last_path`),`updated_at`=VALUES(`updated_at`)",
            $bind
        );
    }

    /**
     * 分批清理过期记录。
     *
     * @param int $retainDays
     * @param int $maxRows
     * @return int
     */
    public static function purge($retainDays, $maxRows = 50000)
    {
        $retainDays = min(365, max(1, intval($retainDays)));
        $cut = time() - ($retainDays * 86400);
        $table = Db::getConfig('prefix') . 'monitor_abnormal_access';

        $done = 0;
        $batch = 5000;
        $t0 = microtime(true);
        do {
            $n = intval(Db::execute("DELETE FROM `{$table}` WHERE `stat_min` < ? LIMIT {$batch}", [$cut]));
            $done += $n;
            if ($n < $batch || $done >= $maxRows) {
                break;
            }
        } while ((microtime(true) - $t0) < 10.0);

        return $done;
    }

    private static function ccThreshold()
    {
        $cfg = self::config();
        $n = isset($cfg['access_cc_threshold']) ? intval($cfg['access_cc_threshold']) : self::DEFAULT_CC_THRESHOLD;
        return max(10, $n);
    }

    private static function err4Threshold()
    {
        $cfg = self::config();
        $n = isset($cfg['access_err4_threshold']) ? intval($cfg['access_err4_threshold']) : self::DEFAULT_ERR4_THRESHOLD;
        return max(5, $n);
    }

    private static function config()
    {
        return isset($GLOBALS['config']['monitor']) && is_array($GLOBALS['config']['monitor'])
            ? $GLOBALS['config']['monitor'] : [];
    }
}
