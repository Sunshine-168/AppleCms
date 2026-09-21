<?php

namespace App\Models\Member;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Schema;

class Member extends Authenticatable
{
    protected $table = 'members';

    public $timestamps = false;

    protected $guarded = [];

    protected $hidden = ['password', 'remember_token', 'api_token'];

    public function getAuthPassword(): string
    {
        return (string) $this->password;
    }

    /** Active group id; 0 when expired (group_expire_at > 0 and past). */
    public function effectiveGroupId(): int
    {
        $gid = (int) ($this->group_id ?? 0);
        if ($gid < 1) {
            return 0;
        }
        if (! Schema::hasColumn('members', 'group_expire_at')) {
            return $gid;
        }
        $expire = (int) ($this->group_expire_at ?? 0);
        if ($expire > 0 && $expire < time()) {
            return 0;
        }

        return $gid;
    }

    public function groupExpireLabel(): string
    {
        if ($this->effectiveGroupId() < 1) {
            return '';
        }
        if (! Schema::hasColumn('members', 'group_expire_at')) {
            return '长期';
        }
        $expire = (int) ($this->group_expire_at ?? 0);
        if ($expire < 1) {
            return '长期';
        }

        return date('Y-m-d H:i', $expire);
    }

    /** Extend current timed group, or assign $fallbackGroupId from now. Permanent groups (expire 0) stay untouched. */
    public function grantTimedGroupDays(int $days, int $fallbackGroupId): bool
    {
        $days = max(0, $days);
        if ($days < 1 || ! Schema::hasColumn($this->getTable(), 'group_expire_at')) {
            return false;
        }
        $gid = (int) ($this->group_id ?? 0);
        $expire = (int) ($this->group_expire_at ?? 0);
        $now = time();
        if ($gid > 0 && $expire === 0) {
            return false;
        }
        if ($gid > 0 && $expire > $now) {
            $this->group_expire_at = $expire + ($days * 86400);
            $this->save();

            return true;
        }
        $target = $gid > 0 ? $gid : $fallbackGroupId;
        if ($target < 1) {
            return false;
        }
        $this->group_id = $target;
        $this->group_expire_at = $now + ($days * 86400);
        $this->save();

        return true;
    }
}
