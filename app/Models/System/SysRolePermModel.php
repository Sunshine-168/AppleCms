<?php
namespace App\Models\System;
use App\Support\QueryCacheTrait;
use App\Support\QueryTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * 角色-权限关系模型
 * 对应数据表：sys_role_perm
 * 用于维护角色与权限的绑定关系
 */
class SysRolePermModel extends Model
{
    protected $table = 'sys_role_perm';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];

    use QueryTrait, QueryCacheTrait;
}
