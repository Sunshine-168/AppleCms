<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'mac_user';
    protected $primaryKey = 'user_id';
    public $timestamps = false;
    
    protected $guarded = [];

    protected $hidden = [
        'user_pwd',
    ];

    public function getAuthPassword()
    {
        return $this->user_pwd;
    }

    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id', 'group_id');
    }

    public function reward(int $feePoints = 0): array
    {
        if ($feePoints <= 0 || (string) config('maccms.user.reward_status', '0') !== '1') {
            return ['code' => 1, 'msg' => __('model/user/reward_ok')];
        }

        $levels = [
            ['column' => 'user_pid', 'ratio' => (float) config('maccms.user.reward_ratio', 0), 'plog_type' => 4],
            ['column' => 'user_pid_2', 'ratio' => (float) config('maccms.user.reward_ratio_2', 0), 'plog_type' => 5],
            ['column' => 'user_pid_3', 'ratio' => (float) config('maccms.user.reward_ratio_3', 0), 'plog_type' => 6],
        ];

        foreach ($levels as $level) {
            $parentId = (int) ($this->{$level['column']} ?? 0);
            if ($parentId < 1 || $level['ratio'] <= 0) {
                continue;
            }

            $points = (int) floor($feePoints * $level['ratio'] / 100);
            if ($points < 1) {
                continue;
            }

            DB::transaction(function () use ($parentId, $points, $feePoints, $level) {
                User::query()->where('user_id', $parentId)->increment('user_points', $points);
                Plog::query()->create([
                    'user_id' => $parentId,
                    'plog_type' => $level['plog_type'],
                    'plog_points' => $points,
                    'plog_time' => time(),
                    'plog_remarks' => __('model/user/reward_tip', [
                        $this->user_id,
                        $this->user_name,
                        $feePoints,
                        $points,
                    ]),
                ]);
            });
        }

        return ['code' => 1, 'msg' => __('model/user/reward_ok')];
    }

    public function normalizeMembership(): self
    {
        $groupIds = array_filter(array_map('intval', explode(',', (string) $this->group_id)));
        if ($groupIds !== [] && max($groupIds) > 2 && (int) $this->user_end_time < time()) {
            $this->group_id = '2';
            $this->save();
        }

        return $this->refresh();
    }

    public function refreshLoginMeta(string $ip): void
    {
        $this->forceFill([
            'group_id' => $this->resolveLoginGroupId(),
            'user_random' => md5((string) random_int(10000000, 99999999)),
            'user_last_login_time' => (int) $this->user_login_time,
            'user_last_login_ip' => (int) $this->user_login_ip,
            'user_login_time' => time(),
            'user_login_ip' => ip2long($ip),
            'user_login_num' => (int) $this->user_login_num + 1,
        ])->save();
    }

    public function queueLegacyCookies(): void
    {
        $this->loadMissing('group');
        $minutes = 60 * 24 * 30;
        $primaryGroupId = (int) (array_filter(array_map('intval', explode(',', (string) $this->group_id)))[0] ?? 1);
        $groupName = (string) ($this->group?->group_name ?? Group::query()->where('group_id', $primaryGroupId)->value('group_name') ?? '游客');
        $portrait = $this->resolveLegacyPortraitUrl();
        $check = md5((string) $this->user_random . '-' . (string) $this->user_name . '-' . (string) $this->user_id . '-');

        foreach ([
            'user_id' => (string) $this->user_id,
            'user_name' => (string) $this->user_name,
            'group_id' => (string) $primaryGroupId,
            'group_name' => $groupName,
            'user_check' => $check,
            'user_portrait' => $portrait,
        ] as $key => $value) {
            Cookie::queue($key, $value, $minutes);
        }
    }

    public static function forgetLegacyCookies(): void
    {
        foreach (['user_id', 'user_name', 'group_id', 'group_name', 'user_check', 'user_portrait'] as $key) {
            Cookie::queue(Cookie::forget($key));
        }
    }

    protected function resolveLoginGroupId(): string
    {
        $groupIds = array_filter(array_map('intval', explode(',', (string) $this->group_id)));
        if ($groupIds !== [] && max($groupIds) > 2 && (int) $this->user_end_time < time()) {
            return '2';
        }

        return (string) $this->group_id;
    }

    protected function resolveLegacyPortraitUrl(): string
    {
        $portrait = trim((string) $this->user_portrait);
        if ($portrait === '') {
            return url('/static_new/images/touxiang.png');
        }
        if (str_starts_with($portrait, 'http://') || str_starts_with($portrait, 'https://')) {
            return $portrait;
        }

        return url('/' . ltrim($portrait, '/'));
    }
}
