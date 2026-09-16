<?php
namespace App\Models\System;

use App\Models\Concerns\QueryCacheTrait;
use App\Models\Concerns\QueryTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * 系统登入表
 */
class SysUserLogModel extends Model
{
    protected $table = 'sys_user_log';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];

    use QueryTrait, QueryCacheTrait;

}
