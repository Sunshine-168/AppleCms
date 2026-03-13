<?php
namespace App\Models\Sys;

use App\Traits\QueryCacheTrait;
use App\Traits\QueryTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * 用户-角色关系模型
 * 对应数据表：sys_user_role
 * 用于维护后台管理员与角色的绑定关系
 */
class SysUserRoleModel extends Model
{
    protected $table = 'sys_user_role';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
    use QueryTrait, QueryCacheTrait;
}
