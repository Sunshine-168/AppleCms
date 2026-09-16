<?php
namespace app\common\util;

use think\Db;

/**
 * 运营数据的异常检测。
 *
 * 数据源是本项目已有的埋点聚合结果（mac_analytics_hour_dim / day_overview）
 * 与订单表，不新增任何采集。
 *
 * ★ 为什么必须按 hour-of-day 分层 ★
 * PV 在凌晨 3 点和晚上 8 点能差十倍。如果把「过去 14 天 × 24 小时 = 336 个连续样本」
 * 一锅端当基线，标准差会大到任何异常都告不出来 —— 检测器等于没装。
 * 所以小时级指标的基线只取「过去 N 天里同一个钟点」的值，样本数约 14 个。
 *
 * ★ 为什么用中位数 + MAD 而不是均值 + 标准差 ★
 * 样本量小（约 14 个），而且历史本身就可能含异常点（上周恰好被 CC 攻击过）。
 * 均值和标准差会被离群点严重污染：一个 10 倍的离群点就能把 σ 撑大三倍，
 * 于是真正的异常反而落进了「正常范围」。
 * 中位数与 MAD 有 50% 的崩溃点 —— 一半样本是脏的都还能给出正确估计。
 * 小样本 + 脏历史，这是唯一正确的选择。
 */
class AnalyticsAnomaly
{
    /** MAD -> σ 的一致估计常数（正态分布下 σ ≈ 1.4826 × MAD） */
    const MAD_TO_SIGMA = 1.4826;

    /** 小时级指标 */
    const HOURLY_METRICS = [
        'analytics.pv', 'analytics.uv', 'analytics.session_cnt',
        'analytics.order_cnt', 'analytics.order_amount',
    ];

    /** 日级指标 */
    const DAILY_METRICS = [
        'analytics.bounce_rate', 'analytics.recharge_amount',
    ];

    /**
     * 检测一条 analytics 规则。
     *
     * @param array $rule
     * @param int   $now
     * @return array|null null = 样本不足 / 低量保护 / 数据源缺失 -> 跳过（既不告警也不判恢复）
     *                    ['hit'=>bool,'value'=>float,'baseline'=>float,'z'=>float,'summary'=>string]
     */
    public static function detect(array $rule, $now = 0)
    {
        $now = $now > 0 ? intval($now) : time();
        $metric = (string)$rule['rule_metric'];

        if (!self::isSupported($metric)) {
            return null;
        }
        if (!self::sourceReady($metric)) {
            // 埋点没部署 / 表不存在：跳过，绝不当成「一切正常」
            return null;
        }

        $param = self::param($rule);
        $isHourly = in_array($metric, self::HOURLY_METRICS, true);

        // 目标时段：小时级取 now-2h，日级取昨天。
        //
        // 为什么是 now-2h 而不是 now-1h：mac_analytics_hour_dim 由 analytics_hour
        // 定时任务填充，它聚合的是「上一小时」，而定时任务本身每小时最多跑一次、
        // 触发时刻不定。留两小时余量，保证目标时段的数据一定已经落库了。
        // 拿一个还没聚合完的时段去比，只会得到一个假的「暴跌」。
        $target = $isHourly
            ? (intval(floor($now / 3600) * 3600) - 7200)
            : strtotime(date('Y-m-d', $now - 86400));

        $cur = self::fetchValue($metric, $target, $isHourly);
        if ($cur === null) {
            return null;
        }

        $samples = self::baselineSamples($metric, $target, $isHourly, $param['baseline_days']);

        $mode = (string)$rule['rule_detect_mode'];
        switch ($mode) {
            case 'zerodrop':
                return self::detectZeroDrop($metric, $cur, $samples, $param, $target, $isHourly);
            case 'yoy':
                return self::detectRatio($metric, $cur, $target, $isHourly, $rule, $param, 7);
            case 'mom':
                return self::detectRatio($metric, $cur, $target, $isHourly, $rule, $param, 0);
            case 'zscore':
            default:
                return self::detectZScore($metric, $cur, $samples, $rule, $param, $target, $isHourly);
        }
    }

    // ------------------------------------------------------------------
    // 三种检测模式
    // ------------------------------------------------------------------

    /**
     * 稳健 z-score。
     *
     * @return array|null
     */
    private static function detectZScore($metric, $cur, array $samples, array $rule, array $param, $target, $isHourly)
    {
        $r = self::robustZ($cur, $samples, $param['min_sample'], $param['min_abs']);
        if ($r === null) {
            return null; // 样本不足或低量保护
        }

        $k = floatval($param['k']);
        $op = (string)$rule['rule_op'];
        if ($op === 'lt' || $op === 'lte') {
            $hit = $r['z'] < -$k;
        } elseif ($op === 'gt' || $op === 'gte') {
            $hit = $r['z'] > $k;
        } else {
            $hit = abs($r['z']) > $k;
        }

        return [
            'hit'      => $hit,
            'value'    => $cur,
            'baseline' => $r['median'],
            'z'        => $r['z'],
            'summary'  => sprintf(
                '%s @ %s = %s (baseline %s, z=%.2f, k=%.1f)',
                $metric,
                self::label($target, $isHourly),
                self::fmt($cur),
                self::fmt($r['median']),
                $r['z'],
                $k
            ),
        ];
    }

    /**
     * 掉零检测。
     *
     * ★ 为什么必须单独处理，不能靠 z-score 兜住 ★
     * cur = 0 时，如果历史波动本来就大（sigma 大），z 可能到不了 -k 而漏报。
     * 但「订单掉零」「充值掉零」是最严重的事故 —— 支付回调挂了、
     * 网关证书过期、数据库写不进去，全都长这个样子。
     * 这类事故一分钟都不该漏，所以只要历史有量而现在是 0，就直接触发。
     *
     * @return array|null
     */
    private static function detectZeroDrop($metric, $cur, array $samples, array $param, $target, $isHourly)
    {
        if (count($samples) < $param['min_sample']) {
            return null;
        }
        $med = self::median($samples);
        if ($med < $param['min_abs']) {
            // 历史本来就没量，现在是 0 也说明不了什么
            return null;
        }

        $hit = (floatval($cur) <= 0.0);

        return [
            'hit'      => $hit,
            'value'    => $cur,
            'baseline' => $med,
            'z'        => 0.0,
            'summary'  => sprintf(
                '%s @ %s = %s (baseline %s) -- zero-drop check',
                $metric,
                self::label($target, $isHourly),
                self::fmt($cur),
                self::fmt($med)
            ),
        ];
    }

    /**
     * 同比 / 环比。
     *
     * ★ 同比为什么是 7 天前而不是 24 小时前 ★
     * 周内效应太强：周一的流量和周日的流量能差一倍。拿周一去比周日，
     * 得到的「暴跌」只是星期几不同而已。往前推 7 天，星期几相同，
     * 才是可比的基准。
     *
     * @param int $daysBack 7 = 同比；0 = 环比（上一个时段）
     * @return array|null
     */
    private static function detectRatio($metric, $cur, $target, $isHourly, array $rule, array $param, $daysBack)
    {
        if ($daysBack > 0) {
            $baseTarget = $isHourly
                ? ($target - ($daysBack * 86400))
                : strtotime('-' . $daysBack . ' day', $target);
        } else {
            $baseTarget = $isHourly ? ($target - 3600) : strtotime('-1 day', $target);
        }

        $base = self::fetchValue($metric, $baseTarget, $isHourly);
        if ($base === null) {
            return null;
        }
        if ($base < $param['min_abs']) {
            return null; // 低量保护
        }

        $dev = ($cur - $base) / max(1.0, abs($base));
        $threshold = abs(floatval($rule['rule_threshold']));
        $op = (string)$rule['rule_op'];

        if ($op === 'lt' || $op === 'lte') {
            $hit = $dev < -$threshold;
        } elseif ($op === 'gt' || $op === 'gte') {
            $hit = $dev > $threshold;
        } else {
            $hit = abs($dev) > $threshold;
        }

        return [
            'hit'      => $hit,
            'value'    => $cur,
            'baseline' => $base,
            'z'        => $dev,
            'summary'  => sprintf(
                '%s @ %s = %s vs %s (%+.1f%%)',
                $metric,
                self::label($target, $isHourly),
                self::fmt($cur),
                self::fmt($base),
                $dev * 100
            ),
        ];
    }

    // ------------------------------------------------------------------
    // 统计（纯函数，供单测）
    // ------------------------------------------------------------------

    /**
     * @param array $xs
     * @return float
     */
    public static function median(array $xs)
    {
        $n = count($xs);
        if ($n === 0) {
            return 0.0;
        }
        $vals = array_values($xs);
        sort($vals, SORT_NUMERIC);
        $mid = intdiv($n, 2);
        if ($n % 2 === 1) {
            return floatval($vals[$mid]);
        }
        return (floatval($vals[$mid - 1]) + floatval($vals[$mid])) / 2.0;
    }

    /**
     * 中位数绝对偏差。
     *
     * @param array $xs
     * @param float $med
     * @return float
     */
    public static function mad(array $xs, $med)
    {
        if (empty($xs)) {
            return 0.0;
        }
        $dev = [];
        foreach ($xs as $x) {
            $dev[] = abs(floatval($x) - floatval($med));
        }
        return self::median($dev);
    }

    /**
     * 稳健 z-score，带三重保护。
     *
     * @param float $cur
     * @param array $samples
     * @param int   $minSample
     * @param float $minAbs
     * @return array|null ['z'=>float,'median'=>float,'sigma'=>float]；null = 被保护规则挡下
     */
    public static function robustZ($cur, array $samples, $minSample, $minAbs)
    {
        // 保护 1：样本不足。新站、刚升级、埋点刚开 —— 没有历史就没有基线。
        if (count($samples) < intval($minSample)) {
            return null;
        }

        $med = self::median($samples);

        // 保护 2：低量保护。★ 这是最重要的一条，堵住 99% 的误报 ★
        // 「昨天这个钟点 2 个 PV，今天 8 个 PV」—— z 值会大得吓人，但毫无意义。
        // 小站的凌晨时段永远是这种个位数，没有这条保护，站长会被垃圾告警淹没。
        if ($med < floatval($minAbs)) {
            return null;
        }

        $sigma = self::MAD_TO_SIGMA * self::mad($samples, $med);

        // 保护 3：防除零。历史 14 天的值完全相同时 MAD = 0。
        // 退回到「中位数的 5%」作为尺度，至少让 z 有意义而不是无穷大。
        if ($sigma < 1e-6) {
            $sigma = max(1.0, $med * 0.05);
        }

        return [
            'z'      => (floatval($cur) - $med) / $sigma,
            'median' => $med,
            'sigma'  => $sigma,
        ];
    }

    // ------------------------------------------------------------------
    // 取数
    // ------------------------------------------------------------------

    /**
     * 取「过去 N 天里同一个钟点（或同一天）」的样本，不含目标时段本身。
     *
     * @param string $metric
     * @param int    $target
     * @param bool   $isHourly
     * @param int    $baselineDays
     * @return array
     */
    public static function baselineSamples($metric, $target, $isHourly, $baselineDays)
    {
        $baselineDays = min(60, max(3, intval($baselineDays)));
        $out = [];
        for ($d = 1; $d <= $baselineDays; $d++) {
            $t = $isHourly
                ? ($target - ($d * 86400))
                : strtotime('-' . $d . ' day', $target);
            $v = self::fetchValue($metric, $t, $isHourly);
            if ($v !== null) {
                $out[] = $v;
            }
        }
        return $out;
    }

    /**
     * @param string $metric
     * @param int    $ts      小时级：该小时的起点 UNIX；日级：该日 00:00 的 UNIX
     * @param bool   $isHourly
     * @return float|null null = 该时段没有数据行
     */
    public static function fetchValue($metric, $ts, $isHourly)
    {
        $ts = intval($ts);

        // 订单直查 mac_order，而不是等 day_overview。
        //
        // day_overview 是日粒度的，「订单掉零」要等一整天才发现 —— 太晚了。
        // 直查订单表可以做到小时粒度，配合 now-2h 的目标时段，
        // 支付链路一挂，两小时内就能报出来。这是本期最有业务价值的一条。
        if ($metric === 'analytics.order_cnt' || $metric === 'analytics.order_amount') {
            $start = $ts;
            $end = $isHourly ? ($ts + 3599) : ($ts + 86399);
            $field = ($metric === 'analytics.order_cnt') ? 'COUNT(*)' : 'IFNULL(SUM(`order_price`),0)';
            $pre = Db::getConfig('prefix');
            $rows = Db::query(
                "SELECT {$field} AS v FROM `{$pre}order` WHERE `order_status` = 1 AND `order_pay_time` BETWEEN ? AND ?",
                [$start, $end]
            );
            return !empty($rows) ? floatval($rows[0]['v']) : 0.0;
        }

        if ($isHourly) {
            // ★ stat_hour 是 DATETIME 字符串（'Y-m-d H:00:00'），不是 UNIX 时间戳 ★
            $col = self::hourColumn($metric);
            if ($col === null) {
                return null;
            }
            $pre = Db::getConfig('prefix');
            $rows = Db::query(
                "SELECT `{$col}` AS v FROM `{$pre}analytics_hour_dim`"
                . " WHERE `stat_hour` = ? AND `dim_type` = 'all' AND `dim_key` = '' LIMIT 1",
                [date('Y-m-d H:00:00', $ts)]
            );
            // 没有行 = 那个小时压根没聚合过，不能当成 0
            return !empty($rows) ? floatval($rows[0]['v']) : null;
        }

        $col = self::dayColumn($metric);
        if ($col === null) {
            return null;
        }
        $pre = Db::getConfig('prefix');
        $rows = Db::query(
            "SELECT `{$col}` AS v FROM `{$pre}analytics_day_overview` WHERE `stat_date` = ? LIMIT 1",
            [date('Y-m-d', $ts)]
        );
        return !empty($rows) ? floatval($rows[0]['v']) : null;
    }

    /**
     * @param string $metric
     * @return string|null
     */
    private static function hourColumn($metric)
    {
        $map = [
            'analytics.pv'          => 'pv',
            'analytics.uv'          => 'uv',
            'analytics.session_cnt' => 'session_cnt',
        ];
        return isset($map[$metric]) ? $map[$metric] : null;
    }

    /**
     * @param string $metric
     * @return string|null
     */
    private static function dayColumn($metric)
    {
        $map = [
            'analytics.bounce_rate'      => 'bounce_rate',
            'analytics.recharge_amount'  => 'recharge_amount',
        ];
        return isset($map[$metric]) ? $map[$metric] : null;
    }

    /**
     * @param string $metric
     * @return bool
     */
    public static function isSupported($metric)
    {
        return in_array($metric, self::HOURLY_METRICS, true)
            || in_array($metric, self::DAILY_METRICS, true);
    }

    /**
     * 数据源是否可用。埋点没部署时表根本不存在，
     * 此时必须跳过而不是 500，也不能当成「一切正常」。
     *
     * @param string $metric
     * @return bool
     */
    private static function sourceReady($metric)
    {
        try {
            $pre = Db::getConfig('prefix');
            if ($metric === 'analytics.order_cnt' || $metric === 'analytics.order_amount') {
                return !empty(Db::query("SHOW TABLES LIKE '{$pre}order'"));
            }
            if (in_array($metric, self::HOURLY_METRICS, true)) {
                return !empty(Db::query("SHOW TABLES LIKE '{$pre}analytics_hour_dim'"));
            }
            return !empty(Db::query("SHOW TABLES LIKE '{$pre}analytics_day_overview'"));
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * @param array $rule
     * @return array
     */
    public static function param(array $rule)
    {
        $raw = isset($rule['rule_detect_param']) ? trim((string)$rule['rule_detect_param']) : '';
        $p = $raw !== '' ? json_decode($raw, true) : [];
        if (!is_array($p)) {
            $p = [];
        }
        return [
            'k'             => isset($p['k']) ? max(1.0, floatval($p['k'])) : 3.0,
            'baseline_days' => isset($p['baseline_days']) ? intval($p['baseline_days']) : 14,
            'min_sample'    => isset($p['min_sample']) ? max(3, intval($p['min_sample'])) : 7,
            'min_abs'       => isset($p['min_abs']) ? max(0.0, floatval($p['min_abs'])) : 50.0,
        ];
    }

    private static function label($ts, $isHourly)
    {
        return $isHourly ? date('Y-m-d H:00', $ts) : date('Y-m-d', $ts);
    }

    private static function fmt($v)
    {
        return rtrim(rtrim(number_format(floatval($v), 2, '.', ''), '0'), '.');
    }
}
