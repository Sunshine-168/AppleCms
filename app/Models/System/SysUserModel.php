<?php
namespace App\Models\System;

use App\Models\Concerns\QueryCacheTrait;
use App\Models\Concerns\QueryTrait;
use App\Models\Concerns\UserTrait;
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
