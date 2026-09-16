<?php
namespace App\Models\System;

use App\Support\QueryCacheTrait;
use App\Support\QueryTrait;
use App\Support\UserTrait;
use Illuminate\Database\Eloquent\Model;

class SysUserModel extends Model
{
    protected $table = 'sys_user';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];

    use QueryTrait,
        UserTrait,
        QueryCacheTrait;

}
