<?php
namespace app\common\model;

class ContentQuality extends Base
{
    protected $name = 'content_quality';

    public function getByObject($mid, $contentId)
    {
        $mid = intval($mid);
        $contentId = intval($contentId);
        if ($mid < 1 || $contentId < 1) {
            return null;
        }
        $row = $this->where(['mid' => $mid, 'content_id' => $contentId])->find();
        return empty($row) ? null : $row;
    }

    public function saveByObject($mid, $contentId, $data)
    {
        $mid = intval($mid);
        $contentId = intval($contentId);
        if ($mid < 1 || $contentId < 1) {
            return false;
        }
        $now = time();
        $row = [
            'mid' => $mid,
            'content_id' => $contentId,
            'type_id' => intval($data['type_id']),
            'score_total' => floatval($data['score_total']),
            'score_behavior' => floatval($data['score_behavior']),
            'score_interact' => floatval($data['score_interact']),
            'score_complete' => floatval($data['score_complete']),
            'score_fresh' => floatval($data['score_fresh']),
            'is_cold_start' => intval($data['is_cold_start']),
            'calc_date' => (string)$data['calc_date'],
            'time_update' => $now,
        ];

        return $this->upsertByUnique($row, ['time_add' => $now]);
    }
}
