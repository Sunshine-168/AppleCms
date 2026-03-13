<?php
namespace App\Models\Sys;

use App\Traits\QueryCacheTrait;
use App\Traits\QueryTrait;
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
