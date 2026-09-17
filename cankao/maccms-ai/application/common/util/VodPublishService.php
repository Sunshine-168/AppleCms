<?php

namespace app\common\util;

use think\Cache;
use think\Db;

/**
 * 视频草稿 / 定时发布：vod_status 3=草稿 4=待发布，vod_publish_time 为 Unix 上架时间。
 * 注：vod_pubdate 仍为影片「上映日期」元数据，定时上架使用 vod_publish_time。
 */
class VodPublishService
{
    const STATUS_DRAFT = 3;
    const STATUS_SCHEDULED = 4;

    /** 到点可由调度自动上架的状态（不含待审 0，避免绕过人工审核） */
    const AUTO_PUBLISH_STATUSES = [3, 4];

    /**
     * 保存前规范化定时字段与状态。
     */
    public static function normalizeOnSave(array &$data)
    {
        $status = intval($data['vod_status'] ?? 0);
        $publishTime = self::parsePublishTime($data['vod_publish_time'] ?? 0);

        if ($status === VodAuditService::STATUS_APPROVED) {
            $data['vod_publish_time'] = 0;
            return;
        }

        if ($status === VodAuditService::STATUS_REJECTED) {
            $data['vod_publish_time'] = 0;
            return;
        }

        if ($status === self::STATUS_DRAFT) {
            $data['vod_publish_time'] = 0;
            return;
        }

        if ($publishTime > time()) {
            $data['vod_status'] = self::STATUS_SCHEDULED;
            $data['vod_publish_time'] = $publishTime;
            return;
        }

        if ($status === self::STATUS_SCHEDULED) {
            $data['vod_publish_time'] = $publishTime;
            if ($publishTime <= 0) {
                $data['vod_status'] = self::STATUS_DRAFT;
            }
            return;
        }

        // 待审等未升级为定时发布：丢弃无效/过期定时戳，避免原始字符串写入 int 列
        $data['vod_publish_time'] = 0;
    }

    /**
     * 定时任务：到点将草稿/待发布转为已审上架。
     *
     * @return array{code:int,msg:string,published:int,ids:array}
     */
    public static function publishDue($limit = 200)
    {
        $limit = max(1, min(500, (int)$limit));
        $now = time();
        $rows = Db::name('vod')
            ->where('vod_publish_time', '>', 0)
            ->where('vod_publish_time', '<=', $now)
            ->where('vod_status', 'in', self::AUTO_PUBLISH_STATUSES)
            ->order('vod_publish_time asc,vod_id asc')
            ->limit($limit)
            ->field('vod_id,vod_name,vod_en')
            ->select();

        $published = 0;
        $ids = [];
        foreach ((array)$rows as $row) {
            $row = is_array($row) ? $row : $row->toArray();
            $vodId = (int)($row['vod_id'] ?? 0);
            if ($vodId <= 0) {
                continue;
            }
            $update = [
                'vod_status' => VodAuditService::STATUS_APPROVED,
                'vod_publish_time' => 0,
                'vod_time' => $now,
            ];
            $res = Db::name('vod')->where('vod_id', $vodId)->update($update);
            if ($res === false) {
                continue;
            }
            $published++;
            $ids[] = $vodId;
            Cache::rm('vod_detail_' . $vodId);
            if (!empty($row['vod_en'])) {
                Cache::rm('vod_detail_' . $row['vod_en']);
            }
            MeilisearchSync::afterVodSave($vodId);
        }

        return [
            'code' => 1,
            'msg' => lang('admin/vod_publish/done', [$published]),
            'published' => $published,
            'ids' => $ids,
        ];
    }

    public static function parsePublishTime($value)
    {
        if ($value === null || $value === '') {
            return 0;
        }
        if (is_numeric($value)) {
            return max(0, (int)$value);
        }
        $value = trim((string)$value);
        if ($value === '') {
            return 0;
        }
        $ts = strtotime($value);
        return $ts > 0 ? $ts : 0;
    }

    public static function scheduleTimeError($status, $publishTime)
    {
        if (intval($status) !== self::STATUS_SCHEDULED) {
            return '';
        }
        if ($publishTime <= 0) {
            return lang('admin/vod_publish/time_required');
        }
        if ($publishTime <= time()) {
            return lang('admin/vod_publish/time_future');
        }
        return '';
    }

    public static function statusText($status)
    {
        $status = intval($status);
        $map = [
            VodAuditService::STATUS_PENDING => lang('reviewed_not'),
            VodAuditService::STATUS_APPROVED => lang('reviewed'),
            VodAuditService::STATUS_REJECTED => lang('reviewed_reject'),
            self::STATUS_DRAFT => lang('reviewed_draft'),
            self::STATUS_SCHEDULED => lang('reviewed_scheduled'),
        ];
        return $map[$status] ?? lang('unknown');
    }
}
