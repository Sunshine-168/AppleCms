<?php
namespace app\common\util;

use think\Db;

/**
 * 用户画像计算：从 mac_ulog（播放行为）与 mac_order（支付订单）中
 * 算出单个用户的画像数据。纯只读查询 + 计算，$now 由调用方传入以便测试确定性。
 * 任何子查询无结果都应降级为零值/空 JSON，绝不抛异常（避免 Task 3 的 cron 整批失败）。
 */
class UserProfileBuilder
{
    public function buildForUser($uid, $windowDays, $now)
    {
        $uid = intval($uid);
        $windowDays = intval($windowDays);
        $now = intval($now);
        $cutoff = $now - $windowDays * 86400;

        $watchRows = [];
        if ($uid > 0) {
            $watchRows = Db::name('Ulog')
                ->where('user_id', $uid)
                ->where('ulog_type', 4)
                ->where('ulog_time', '>=', $cutoff)
                ->field('ulog_mid,ulog_rid,ulog_point,ulog_duration')
                ->select();
        }
        if (empty($watchRows)) {
            $watchRows = [];
        }

        $watchCntWindow = count($watchRows);

        // avg_completion_rate：仅统计 ulog_duration>0 的行，每行夹取到 [0,1] 后取平均
        $ratioSum = 0.0;
        $ratioCnt = 0;
        foreach ($watchRows as $row) {
            $duration = intval($row['ulog_duration']);
            if ($duration <= 0) {
                continue;
            }
            $point = intval($row['ulog_point']);
            $ratio = $point / max(1, $duration);
            if ($ratio < 0) {
                $ratio = 0;
            } elseif ($ratio > 1) {
                $ratio = 1;
            }
            $ratioSum += $ratio;
            $ratioCnt++;
        }
        $avgCompletionRate = $ratioCnt > 0 ? round($ratioSum / $ratioCnt, 4) : 0.0;

        // activity_level：按 watch_cnt_window 阈值
        if ($watchCntWindow >= 16) {
            $activityLevel = 3;
        } elseif ($watchCntWindow >= 4) {
            $activityLevel = 2;
        } elseif ($watchCntWindow >= 1) {
            $activityLevel = 1;
        } else {
            $activityLevel = 0;
        }

        // prefer_types：按 ulog_mid 分组批量解析 type_id，再计数取 Top-5
        $vodRids = [];
        $artRids = [];
        foreach ($watchRows as $row) {
            $mid = intval($row['ulog_mid']);
            $rid = intval($row['ulog_rid']);
            if ($mid == 1) {
                $vodRids[] = $rid;
            } elseif ($mid == 2) {
                $artRids[] = $rid;
            }
        }

        $vodTypeMap = [];
        $vodTagRows = [];
        if (!empty($vodRids)) {
            // 一次取回 type_id 与 vod_tag，供下方 prefer_types / prefer_tags 复用，避免二次查询 Vod
            $vodList = Db::name('Vod')
                ->whereIn('vod_id', array_unique($vodRids))
                ->field('vod_id,type_id,vod_tag')
                ->select();
            foreach ($vodList as $v) {
                $vodTypeMap[intval($v['vod_id'])] = intval($v['type_id']);
                $vodTagRows[] = $v;
            }
        }

        $artTypeMap = [];
        if (!empty($artRids)) {
            $artList = Db::name('Art')
                ->whereIn('art_id', array_unique($artRids))
                ->field('art_id,type_id')
                ->select();
            foreach ($artList as $a) {
                $artTypeMap[intval($a['art_id'])] = intval($a['type_id']);
            }
        }

        $typeCount = [];
        foreach ($watchRows as $row) {
            $mid = intval($row['ulog_mid']);
            $rid = intval($row['ulog_rid']);
            $typeId = 0;
            if ($mid == 1 && isset($vodTypeMap[$rid])) {
                $typeId = $vodTypeMap[$rid];
            } elseif ($mid == 2 && isset($artTypeMap[$rid])) {
                $typeId = $artTypeMap[$rid];
            }
            if ($typeId <= 0) {
                continue;
            }
            if (!isset($typeCount[$typeId])) {
                $typeCount[$typeId] = 0;
            }
            $typeCount[$typeId]++;
        }

        $preferTypes = '[]';
        if (!empty($typeCount)) {
            arsort($typeCount);
            $total = array_sum($typeCount);
            $typeCount = array_slice($typeCount, 0, 5, true);
            $list = [];
            foreach ($typeCount as $typeId => $cnt) {
                $list[] = [
                    'type_id' => intval($typeId),
                    'w' => $total > 0 ? round($cnt / $total, 4) : 0,
                ];
            }
            $preferTypes = json_encode($list);
        }

        // prefer_tags：best-effort，来自 vod_tag（逗号/斜杠分隔），无数据则空
        $preferTags = '[]';
        if (!empty($vodTagRows)) {
            $tagCount = [];
            foreach ($vodTagRows as $v) {
                $tagStr = trim((string)$v['vod_tag']);
                if ($tagStr === '') {
                    continue;
                }
                $tags = preg_split('/[,\/]+/', $tagStr);
                foreach ($tags as $tag) {
                    $tag = trim($tag);
                    if ($tag === '') {
                        continue;
                    }
                    if (!isset($tagCount[$tag])) {
                        $tagCount[$tag] = 0;
                    }
                    $tagCount[$tag]++;
                }
            }
            if (!empty($tagCount)) {
                arsort($tagCount);
                $tagCount = array_slice($tagCount, 0, 5, true);
                $preferTags = json_encode(array_values(array_keys($tagCount)), JSON_UNESCAPED_UNICODE);
            }
        }

        // pay_amount_window：窗口内已支付订单金额之和
        $payAmountWindow = 0.0;
        if ($uid > 0) {
            $sum = Db::name('Order')
                ->where('user_id', $uid)
                ->where('order_status', 1)
                ->where('order_time', '>=', $cutoff)
                ->sum('order_price');
            $payAmountWindow = round(floatval($sum), 2);
        }

        // last_active_time：该用户全部播放记录（不限窗口/类型）的最大 ulog_time
        $lastActiveTime = 0;
        if ($uid > 0) {
            $max = Db::name('Ulog')
                ->where('user_id', $uid)
                ->max('ulog_time');
            $lastActiveTime = intval($max);
        }

        return [
            'prefer_types' => $preferTypes,
            'prefer_tags' => $preferTags,
            'avg_completion_rate' => $avgCompletionRate,
            'watch_cnt_window' => $watchCntWindow,
            'pay_amount_window' => $payAmountWindow,
            'activity_level' => $activityLevel,
            'last_active_time' => $lastActiveTime,
        ];
    }
}
