<?php

namespace App\Models\Member;

use Illuminate\Database\Eloquent\Model;

class MemberFavorite extends Model
{
    protected $table = 'member_favorites';
    public $timestamps = false;
    protected $guarded = [];
}
