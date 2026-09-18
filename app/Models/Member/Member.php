<?php

namespace App\Models\Member;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Schema;

class Member extends Authenticatable
{
    protected $table = 'members';

    public $timestamps = false;

    protected $guarded = [];

    protected $hidden = ['password', 'remember_token'];

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
}
