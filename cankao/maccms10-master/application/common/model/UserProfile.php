<?php
namespace app\common\model;

class UserProfile extends Base
{
    protected $name = 'user_profile';

    public function getByUser($uid)
    {
        $uid = intval($uid);
        if ($uid < 1) {
            return null;
        }
        $row = $this->where(['user_id' => $uid])->find();
        return empty($row) ? null : $row;
    }

    public function saveByUser($uid, array $data)
    {
        $uid = intval($uid);
        if ($uid < 1) {
            return false;
        }
        $now = time();
        $row = [
            'user_id' => $uid,
            'prefer_types' => (string)$data['prefer_types'],
            'prefer_tags' => (string)$data['prefer_tags'],
            'avg_completion_rate' => floatval($data['avg_completion_rate']),
            'watch_cnt_window' => intval($data['watch_cnt_window']),
            'pay_amount_window' => floatval($data['pay_amount_window']),
            'activity_level' => intval($data['activity_level']),
            'last_active_time' => intval($data['last_active_time']),
            'calc_date' => (string)$data['calc_date'],
            'time_update' => $now,
        ];

        return $this->upsertByUnique($row, ['time_add' => $now]);
    }
}
