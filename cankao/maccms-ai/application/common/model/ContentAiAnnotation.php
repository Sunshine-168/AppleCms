<?php
namespace app\common\model;

class ContentAiAnnotation extends Base
{
    protected $name = 'content_ai_annotation';

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
            'ai_tags' => (string)$data['ai_tags'],
            'ai_summary' => (string)$data['ai_summary'],
            'ai_type_id' => intval($data['ai_type_id']),
            'ai_confidence' => floatval($data['ai_confidence']),
            'source_hash' => (string)$data['source_hash'],
            'status' => intval($data['status']),
            'provider' => (string)$data['provider'],
            'model' => (string)$data['model'],
            'error_msg' => (string)$data['error_msg'],
            'time_update' => $now,
        ];

        return $this->upsertByUnique($row, ['time_add' => $now]);
    }
}
