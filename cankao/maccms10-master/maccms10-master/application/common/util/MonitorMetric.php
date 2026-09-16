<?php
namespace app\common\util;

use think\Db;

/**
 * 监控时序指标的读写层（mac_monitor_metric_min / mac_monitor_metric_hour）。
 *
 * 表设计为 KV 窄表（metric_key + stat_min），而非每个指标一列的宽表：
 *  - 新增指标无需改 DDL（本项目 DDL 改动要同步 install.sql 与 data/update/database.php 两处，
 *    成本翻倍，所以把 DDL 改动次数压到 1）
 *  - 维度天然是行（磁盘挂载点、入口名称都是运行期才知道的）
 *  - 稀疏即语义：capability 缺失的指标直接不写行，
 *    与「值为 0」区分得开（宽表只能塞 0/NULL，分不清「CPU 是 0%」和「读不到 /proc/stat」）
 *  - PK (metric_key, stat_min) + ON DUPLICATE KEY UPDATE 覆盖写 => 幂等免费得到
 */
class MonitorMetric
{
    /**
     * 延迟直方图的桶上界（毫秒），11 个上界 => 12 个桶 b0..b11，最后一桶为 (8000, +inf)。
     *
     * ★ 这是常量：一旦改动，历史数据不可比 ★
     * 分位数估算依赖固定边界；改边界等于换了一把尺子，
     * 旧数据算出来的 p95 和新数据算出来的 p95 不再是同一个东西。
     */
    const LATENCY_BOUNDS = [50, 100, 200, 300, 500, 800, 1200, 2000, 3000, 5000, 8000];

    const TYPE_GAUGE   = 1;
    const TYPE_COUNTER = 2;

    private static function tableMin()
    {
        return Db::getConfig('prefix') . 'monitor_metric_min';
    }

    private static function tableHour()
    {
        return Db::getConfig('prefix') . 'monitor_metric_hour';
    }

    /**
     * 把毫秒映射到直方图桶下标。线性扫 11 个边界，O(1) 量级，每请求都会调用。
     *
     * @param int $ms
     * @return int 0..11
     */
    public static function latencyBucketIndex($ms)
    {
        $ms = intval($ms);
        $bounds = self::LATENCY_BOUNDS;
        $n = count($bounds);
        for ($i = 0; $i < $n; $i++) {
            if ($ms <= $bounds[$i]) {
                return $i;
            }
        }
        return $n; // 落在最后一桶 (8000, +inf)
    }

    /**
     * 幂等写入某一分钟的一批指标。
     *
     * 覆盖语义（=VALUES()，不是 +=）：同一分钟被重放多少次，值都不变。
     * 这让 cron 叠跑、手动重打、桶被重复 drain 都变成安全操作。
     *
     * @param int   $statMin 分钟起点 UNIX
     * @param array $rows    [['k'=>'sys.cpu.pct','t'=>1,'v'=>12.5], ...]
     * @return int 写入的行数
     */
    public static function upsertMany($statMin, array $rows)
    {
        if (empty($rows)) {
            return 0;
        }
        $statMin = intval($statMin);
        $now = time();
        $table = self::tableMin();

        $placeholders = [];
        $bind = [];
        foreach ($rows as $row) {
            if (!isset($row['k']) || $row['k'] === '') {
                continue;
            }
            $key = mb_substr((string)$row['k'], 0, 64);
            $type = isset($row['t']) ? intval($row['t']) : self::TYPE_GAUGE;
            if ($type !== self::TYPE_COUNTER) {
                $type = self::TYPE_GAUGE;
            }
            $val = isset($row['v']) ? floatval($row['v']) : 0.0;

            $placeholders[] = '(?,?,?,?,?)';
            $bind[] = $key;
            $bind[] = $statMin;
            $bind[] = $type;
            $bind[] = $val;
            $bind[] = $now;
        }
        if (empty($placeholders)) {
            return 0;
        }

        $sql = "INSERT INTO `{$table}` (`metric_key`,`stat_min`,`metric_type`,`metric_value`,`updated_at`) VALUES "
            . implode(',', $placeholders)
            . " ON DUPLICATE KEY UPDATE `metric_type`=VALUES(`metric_type`),`metric_value`=VALUES(`metric_value`),`updated_at`=VALUES(`updated_at`)";
        Db::execute($sql, $bind);
        return count($placeholders);
    }

    /**
     * 取多个指标在窗口内的序列，pivot 成 [metric_key => [ts => value]]。
     *
     * 粒度选择由 metric_min 的保留期决定，而不是由图表库性能决定：
     * 窗口起点仍在分钟表保留期内 => 走分钟表（保留完整分辨率，
     * 3 分钟的 CPU 尖峰、2 分钟的 5xx 突增不会被小时均值抹平）；
     * 否则走小时表。
     *
     * @param array  $metricKeys
     * @param int    $fromTs
     * @param int    $toTs
     * @param string $granularity 'min' | 'hour' | 'auto'
     * @return array ['series' => [key => [ts => val]], 'granularity' => 'min'|'hour']
     */
    public static function fetchSeries(array $metricKeys, $fromTs, $toTs, $granularity = 'auto')
    {
        $metricKeys = array_values(array_unique(array_filter($metricKeys, 'strlen')));
        if (empty($metricKeys)) {
            return ['series' => [], 'granularity' => 'min'];
        }
        $fromTs = intval($fromTs);
        $toTs = intval($toTs);

        if ($granularity === 'auto') {
            $granularity = self::pickGranularity($fromTs);
        }
        if ($granularity !== 'hour') {
            $granularity = 'min';
        }

        $in = implode(',', array_fill(0, count($metricKeys), '?'));
        $bind = $metricKeys;
        $bind[] = $fromTs;
        $bind[] = $toTs;

        if ($granularity === 'hour') {
            $table = self::tableHour();
            $sql = "SELECT `metric_key`,`stat_hour` AS `ts`,`val_avg` AS `v` FROM `{$table}`"
                . " WHERE `metric_key` IN ({$in}) AND `stat_hour` >= ? AND `stat_hour` <= ? ORDER BY `stat_hour` ASC";
        } else {
            $table = self::tableMin();
            $sql = "SELECT `metric_key`,`stat_min` AS `ts`,`metric_value` AS `v` FROM `{$table}`"
                . " WHERE `metric_key` IN ({$in}) AND `stat_min` >= ? AND `stat_min` <= ? ORDER BY `stat_min` ASC";
        }

        $rows = Db::query($sql, $bind);
        $series = [];
        foreach ($metricKeys as $k) {
            $series[$k] = [];
        }
        foreach ($rows as $row) {
            $series[$row['metric_key']][intval($row['ts'])] = floatval($row['v']);
        }
        return ['series' => $series, 'granularity' => $granularity];
    }

    /**
     * 窗口起点是否还在分钟表的保留期内。
     *
     * @param int $fromTs
     * @return string 'min' | 'hour'
     */
    private static function pickGranularity($fromTs)
    {
        $cfg = isset($GLOBALS['config']['monitor']) && is_array($GLOBALS['config']['monitor'])
            ? $GLOBALS['config']['monitor'] : [];
        $retainDays = isset($cfg['retain_min_days']) ? intval($cfg['retain_min_days']) : 3;
        $retainDays = min(14, max(1, $retainDays));
        // 留半天余量，避免刚好卡在保留边界上取到被清理了一半的数据
        $cut = time() - ($retainDays * 86400) + 43200;
        return intval($fromTs) >= $cut ? 'min' : 'hour';
    }

    /**
     * @param array  $series [ts => value]
     * @param string $agg    avg|max|min|sum|last
     * @return float|null null = 样本不足
     */
    public static function aggregate(array $series, $agg)
    {
        if (empty($series)) {
            return null;
        }
        $vals = array_values($series);
        switch ((string)$agg) {
            case 'max':
                return floatval(max($vals));
            case 'min':
                return floatval(min($vals));
            case 'sum':
                return floatval(array_sum($vals));
            case 'last':
                ksort($series);
                $last = end($series);
                return floatval($last);
            case 'avg':
            default:
                return floatval(array_sum($vals) / count($vals));
        }
    }

    /**
     * 把窗口内的 12 个延迟桶 counter 逐位加总，得到合并直方图。
     *
     * 直方图的可合并性是这套方案的决定性优势：
     * N 分钟的桶逐位相加再算分位数，数学上是正确的。
     *
     * @param int $fromTs
     * @param int $toTs
     * @return array 12 个整数
     */
    public static function fetchLatencyHistogram($fromTs, $toTs)
    {
        $n = count(self::LATENCY_BOUNDS) + 1;
        $keys = [];
        for ($i = 0; $i < $n; $i++) {
            $keys[] = 'http.lat.b' . $i;
        }

        $table = self::tableMin();
        $in = implode(',', array_fill(0, count($keys), '?'));
        $bind = $keys;
        $bind[] = intval($fromTs);
        $bind[] = intval($toTs);

        $rows = Db::query(
            "SELECT `metric_key`, SUM(`metric_value`) AS `s` FROM `{$table}`"
            . " WHERE `metric_key` IN ({$in}) AND `stat_min` >= ? AND `stat_min` <= ? GROUP BY `metric_key`",
            $bind
        );

        $buckets = array_fill(0, $n, 0);
        foreach ($rows as $row) {
            $idx = intval(substr((string)$row['metric_key'], strlen('http.lat.b')));
            if ($idx >= 0 && $idx < $n) {
                $buckets[$idx] = intval(round(floatval($row['s'])));
            }
        }
        return $buckets;
    }

    /**
     * 从固定边界直方图估算分位数（桶内均匀分布假设 + 线性插值）。
     *
     * 为什么不存原始样本：每请求一行 = 违反「请求路径零 DB 写入」的红线；
     * t-digest 之类的草图在 PHP 7.0 下每请求要跑几十次浮点运算并序列化几 KB，
     * 远超 1ms 的开销预算。固定桶直方图是唯一同时满足
     * 「O(1) 写入 / 可跨时间合并 / 可用 counter upsert 表达」的方案。
     *
     * 误差（诚实）：真实延迟在桶内右偏，线性插值会系统性低估高分位；
     * 绝对误差上界 = 命中桶的宽度（p95 典型落在 (200,300] 或 (300,500]，即 ±200ms 上界）。
     * 对「p95 有没有劣化」这个决策足够。
     *
     * @param array $buckets 12 个 counter
     * @param float $q       0..1
     * @return array|null ['value'=>float,'clamped'=>bool,'total'=>int]；null = 无样本
     */
    public static function estimatePercentile(array $buckets, $q)
    {
        $bounds = self::LATENCY_BOUNDS;
        $n = count($bounds) + 1;
        $total = 0;
        for ($i = 0; $i < $n; $i++) {
            $total += isset($buckets[$i]) ? intval($buckets[$i]) : 0;
        }
        if ($total <= 0) {
            return null;
        }

        $q = min(1.0, max(0.0, floatval($q)));
        $target = $q * $total;

        $cum = 0;
        for ($i = 0; $i < $n; $i++) {
            $c = isset($buckets[$i]) ? intval($buckets[$i]) : 0;
            if ($c <= 0) {
                continue;
            }
            if ($cum + $c >= $target) {
                if ($i === $n - 1) {
                    // 最后一桶是 (8000, +inf)，无法估上界
                    return [
                        'value'   => floatval($bounds[$n - 2]),
                        'clamped' => true,
                        'total'   => $total,
                    ];
                }
                $lo = ($i === 0) ? 0.0 : floatval($bounds[$i - 1]);
                $hi = floatval($bounds[$i]);
                $frac = ($target - $cum) / $c;
                $frac = min(1.0, max(0.0, $frac));
                return [
                    'value'   => $lo + ($hi - $lo) * $frac,
                    'clamped' => false,
                    'total'   => $total,
                ];
            }
            $cum += $c;
        }

        // 理论不可达（累计必然会追上 target），兜底
        return ['value' => floatval($bounds[$n - 2]), 'clamped' => true, 'total' => $total];
    }

    /**
     * 把 [hourStart, hourStart+3600) 的分钟数据卷到小时表。一条 SQL，幂等。
     *
     * 幂等来自「每次都从分钟表重新聚合再覆盖写」，而不是增量累加。
     *
     * @param int $hourStart 整点 UNIX
     * @return int 卷入的指标条数
     */
    public static function rollupHour($hourStart)
    {
        $hourStart = intval($hourStart);
        $hourEnd = $hourStart + 3600;
        $now = time();
        $min = self::tableMin();
        $hour = self::tableHour();

        $affected = Db::execute(
            "INSERT INTO `{$hour}` (`metric_key`,`stat_hour`,`metric_type`,`val_avg`,`val_max`,`val_min`,`val_sum`,`sample_cnt`,`updated_at`)"
            . " SELECT `metric_key`, ?, MAX(`metric_type`), AVG(`metric_value`), MAX(`metric_value`), MIN(`metric_value`), SUM(`metric_value`), COUNT(*), ?"
            . " FROM `{$min}` WHERE `stat_min` >= ? AND `stat_min` < ? GROUP BY `metric_key`"
            . " ON DUPLICATE KEY UPDATE `metric_type`=VALUES(`metric_type`),`val_avg`=VALUES(`val_avg`),`val_max`=VALUES(`val_max`),"
            . "`val_min`=VALUES(`val_min`),`val_sum`=VALUES(`val_sum`),`sample_cnt`=VALUES(`sample_cnt`),`updated_at`=VALUES(`updated_at`)",
            [$hourStart, $now, $hourStart, $hourEnd]
        );
        return intval($affected);
    }

    /**
     * 分批清理过期数据。
     *
     * 必须分批：一天约 57.6k 行（40 指标 × 1440 分钟），
     * 一次性 DELETE 可能删掉几十万行 => 长事务、锁表、binlog 爆炸。
     * 单次上限 $maxRows 行 + 10 秒墙钟上限，删不完下次 cron 继续（每天跑，追得上）。
     *
     * @param int $minRetainDays
     * @param int $hourRetainDays
     * @param int $maxRows
     * @return array ['min'=>int,'hour'=>int]
     */
    public static function purge($minRetainDays, $hourRetainDays, $maxRows = 50000)
    {
        $minRetainDays = min(14, max(1, intval($minRetainDays)));
        $hourRetainDays = min(730, max(7, intval($hourRetainDays)));
        $maxRows = max(1000, intval($maxRows));

        $t0 = microtime(true);
        $deletedMin = self::purgeTable(self::tableMin(), 'stat_min', time() - ($minRetainDays * 86400), $maxRows, $t0);
        $deletedHour = self::purgeTable(self::tableHour(), 'stat_hour', time() - ($hourRetainDays * 86400), $maxRows, $t0);

        return ['min' => $deletedMin, 'hour' => $deletedHour];
    }

    /**
     * @param string $table
     * @param string $tsCol
     * @param int    $cut
     * @param int    $maxRows
     * @param float  $t0
     * @return int
     */
    private static function purgeTable($table, $tsCol, $cut, $maxRows, $t0)
    {
        $done = 0;
        $batch = 5000;
        do {
            $n = intval(Db::execute(
                "DELETE FROM `{$table}` WHERE `{$tsCol}` < ? LIMIT {$batch}",
                [intval($cut)]
            ));
            $done += $n;
            if ($n < $batch || $done >= $maxRows) {
                break;
            }
        } while ((microtime(true) - $t0) < 10.0);

        return $done;
    }
}
