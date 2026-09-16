<?php

namespace App\Models\Member;

use Illuminate\Foundation\Auth\User as Authenticatable;

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
}
