<?php

namespace App\Models\Member;

use Illuminate\Database\Eloquent\Model;

class MemberHistory extends Model
{
    protected $table = 'member_histories';
    public $timestamps = false;
    protected $guarded = [];
}
