<?php
namespace app\common\util;

/**
 * 内容质量打分（纯规则加权，无 LLM）。
 *
 * 严格纯函数：不访问 Db::/config()/model()，所有输入（内容行、行为聚合、权重、
 * 半衰期、当前时间）均由调用方传入，因此可脱库单测（见
 * tests/regression/content_quality_unit.php）。
 *
 * 四个分项（各 0-100）加权得总分（0-100）：
 * - behavior（行为）：完播率代理 + 跳出率反向 + 转化率 + 播放量对数缩放。
 * - interact（互动）：评分 + 顶踩比 + 收藏/想看量对数 - 举报惩罚（可选）。
 * - complete（完整度）：blurb/tag/pic/content(/actor) 字段齐全度。
 * - fresh（时效）：exp(-Δdays/halflife)*100。
 *
 * 冷启动：$behaviorAgg 为 null，或其 view_pv==0（统计窗口内无行为数据），
 * 判定 is_cold_start=1，此时 score_behavior/score_interact 置 0，
 * score_total 只由 complete + fresh 按二者权重占比重新归一得出（不因缺行为
 * 数据被判 0 分）。
 */
class ContentQualityScorer
{
    // behavior 分项：avg_stay_ms 达到该值（5 分钟）即记满分
    const STAY_MS_CAP = 300000;
    // behavior 分项：转化率（order_cnt/view_uv）达到该比例即记满分
    const CONV_RATE_CAP = 0.05;
    // behavior 分项：播放量（view_pv）对数缩放的上限基准
    const VOLUME_PV_CAP = 10000;
    // interact 分项：收藏+想看量对数缩放的上限基准
    const SOCIAL_CAP = 500;
    // interact 分项：每条举报扣分，最多扣到该上限
    const REPORT_PENALTY_PER = 2;
    const REPORT_PENALTY_MAX = 50;

    public static function defaultWeights()
    {
        return [
            'behavior' => 0.35,
            'interact' => 0.30,
            'complete' => 0.20,
            'fresh' => 0.15,
        ];
    }

    /**
     * 内容完整度：blurb/tag/pic/content 四项 vod/art 都算；vod 额外算 actor
     * （art 表无 art_actor 列，不计入）。齐全 = 100，按占比递减。
     */
    public static function completeness($mid, array $content)
    {
        $prefix = (intval($mid) === 2) ? 'art' : 'vod';
        $fields = ['blurb', 'tag', 'pic', 'content'];
        if ($prefix === 'vod') {
            $fields[] = 'actor';
        }

        $total = count($fields);
        $has = 0;
        foreach ($fields as $field) {
            $key = $prefix . '_' . $field;
            if (isset($content[$key]) && trim((string)$content[$key]) !== '') {
                $has++;
            }
        }

        if ($total <= 0) {
            return 0.0;
        }

        return self::clamp(($has / $total) * 100);
    }

    /**
     * 时效分：exp(-Δdays/halflife)*100。Δdays = max(0,(now-timeAdd)/86400)。
     * halflifeDays<=0 时按默认 30 天处理。
     */
    public static function freshness($timeAdd, $halflifeDays, $now)
    {
        $halflifeDays = floatval($halflifeDays);
        if ($halflifeDays <= 0) {
            $halflifeDays = 30.0;
        }

        $deltaDays = (floatval($now) - floatval($timeAdd)) / 86400;
        if ($deltaDays < 0) {
            $deltaDays = 0;
        }

        $score = exp(-$deltaDays / $halflifeDays) * 100;
        return self::clamp($score);
    }

    /**
     * behavior 分项（0-100）。四个信号各归一化后按固定权重加权：
     * - stayScore（权重 0.35）：avg_stay_ms / STAY_MS_CAP，封顶 100。
     * - bounceScore（权重 0.25）：(1 - bounce_cnt/max(1,view_uv)) * 100。
     * - convScore（权重 0.20）：(order_cnt/max(1,view_uv)) / CONV_RATE_CAP，封顶 100。
     * - volumeScore（权重 0.20）：log10(view_pv+1)/log10(VOLUME_PV_CAP+1) * 100，封顶 100。
     * 无 $behaviorAgg 数据（冷启动）由 scoreRow 直接置 0，不进入本函数。
     */
    private static function behaviorSubScore(array $agg)
    {
        $viewUv = isset($agg['view_uv']) ? floatval($agg['view_uv']) : 0;
        $viewPv = isset($agg['view_pv']) ? floatval($agg['view_pv']) : 0;
        $avgStayMs = isset($agg['avg_stay_ms']) ? floatval($agg['avg_stay_ms']) : 0;
        $bounceCnt = isset($agg['bounce_cnt']) ? floatval($agg['bounce_cnt']) : 0;
        $orderCnt = isset($agg['order_cnt']) ? floatval($agg['order_cnt']) : 0;

        $stayScore = self::clamp(($avgStayMs / self::STAY_MS_CAP) * 100);

        $bounceRate = $bounceCnt / max(1, $viewUv);
        $bounceScore = self::clamp((1 - $bounceRate) * 100);

        $convRate = $orderCnt / max(1, $viewUv);
        $convScore = self::clamp(($convRate / self::CONV_RATE_CAP) * 100);

        $volumeScore = self::clamp((log10($viewPv + 1) / log10(self::VOLUME_PV_CAP + 1)) * 100);

        $score = 0.35 * $stayScore + 0.25 * $bounceScore + 0.20 * $convScore + 0.20 * $volumeScore;
        return self::clamp($score);
    }

    /**
     * interact 分项（0-100）。三个信号加权后减举报惩罚：
     * - scoreComponent（权重 0.40）：vod_score/art_score（0-10）×10。
     * - ratioComponent（权重 0.25）：up/max(1,up+down) * 100。
     * - socialComponent（权重 0.35）：log10(collect_add+want_add+1)/log10(SOCIAL_CAP+1) * 100。
     * - reportPenalty（可选）：$content 中若有 comment_report 计数，
     *   每条扣 REPORT_PENALTY_PER 分，最多扣 REPORT_PENALTY_MAX 分；
     *   $content 中没有该字段则不扣分。
     */
    private static function interactSubScore($mid, array $content, array $agg)
    {
        $prefix = (intval($mid) === 2) ? 'art' : 'vod';

        $rawScore = isset($content[$prefix . '_score']) ? floatval($content[$prefix . '_score']) : 0;
        $scoreComponent = self::clamp($rawScore * 10);

        $up = isset($content[$prefix . '_up']) ? floatval($content[$prefix . '_up']) : 0;
        $down = isset($content[$prefix . '_down']) ? floatval($content[$prefix . '_down']) : 0;
        $ratioComponent = self::clamp(($up / max(1, $up + $down)) * 100);

        $collectAdd = isset($agg['collect_add']) ? floatval($agg['collect_add']) : 0;
        $wantAdd = isset($agg['want_add']) ? floatval($agg['want_add']) : 0;
        $socialComponent = self::clamp((log10($collectAdd + $wantAdd + 1) / log10(self::SOCIAL_CAP + 1)) * 100);

        $score = 0.40 * $scoreComponent + 0.25 * $ratioComponent + 0.35 * $socialComponent;

        if (isset($content['comment_report'])) {
            $reportPenalty = min(self::REPORT_PENALTY_MAX, floatval($content['comment_report']) * self::REPORT_PENALTY_PER);
            $score -= $reportPenalty;
        }

        return self::clamp($score);
    }

    /**
     * 综合打分。$behaviorAgg 为 null 或其 view_pv==0 时判定冷启动，
     * 此时 score_behavior/score_interact=0，score_total 只由
     * complete+fresh 按二者权重占比重新归一得出。
     */
    public static function scoreRow($mid, array $content, $behaviorAgg, array $weights, $halflifeDays, $now)
    {
        $prefix = (intval($mid) === 2) ? 'art' : 'vod';

        $complete = self::completeness($mid, $content);
        $timeAdd = isset($content[$prefix . '_time_add']) ? $content[$prefix . '_time_add'] : 0;
        $fresh = self::freshness($timeAdd, $halflifeDays, $now);

        $w = self::normalizeWeights($weights);

        $viewPv = ($behaviorAgg !== null && isset($behaviorAgg['view_pv'])) ? floatval($behaviorAgg['view_pv']) : 0;
        $isColdStart = ($behaviorAgg === null || $viewPv <= 0);

        if ($isColdStart) {
            $wSum = $w['complete'] + $w['fresh'];
            if ($wSum > 0) {
                $wComplete = $w['complete'] / $wSum;
                $wFresh = $w['fresh'] / $wSum;
            } else {
                $wComplete = 0.5;
                $wFresh = 0.5;
            }

            $total = self::clamp($wComplete * $complete + $wFresh * $fresh);

            return [
                'score_total' => $total,
                'score_behavior' => 0.0,
                'score_interact' => 0.0,
                'score_complete' => $complete,
                'score_fresh' => $fresh,
                'is_cold_start' => 1,
            ];
        }

        $behavior = self::behaviorSubScore($behaviorAgg);
        $interact = self::interactSubScore($mid, $content, $behaviorAgg);

        $total = self::clamp(
            $w['behavior'] * $behavior
            + $w['interact'] * $interact
            + $w['complete'] * $complete
            + $w['fresh'] * $fresh
        );

        return [
            'score_total' => $total,
            'score_behavior' => $behavior,
            'score_interact' => $interact,
            'score_complete' => $complete,
            'score_fresh' => $fresh,
            'is_cold_start' => 0,
        ];
    }

    /**
     * 权重归一化：若管理员配置的四个权重之和不为 1，按比例缩放到和为 1；
     * 若和 <=0（异常配置），退回默认权重。
     */
    private static function normalizeWeights(array $weights)
    {
        $keys = ['behavior', 'interact', 'complete', 'fresh'];
        $w = [];
        $sum = 0;
        foreach ($keys as $key) {
            $v = isset($weights[$key]) ? floatval($weights[$key]) : 0;
            if ($v < 0) {
                $v = 0;
            }
            $w[$key] = $v;
            $sum += $v;
        }

        if ($sum <= 0) {
            return self::defaultWeights();
        }

        foreach ($keys as $key) {
            $w[$key] = $w[$key] / $sum;
        }

        return $w;
    }

    private static function clamp($value)
    {
        $value = floatval($value);
        if (is_nan($value)) {
            return 0.0;
        }
        if ($value < 0) {
            return 0.0;
        }
        if ($value > 100) {
            return 100.0;
        }
        return $value;
    }
}
