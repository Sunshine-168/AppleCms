<?php
namespace app\common\model;
use app\common\logic\traits\QueryCacheTrait;
use app\common\logic\traits\QueryTrait;
use think\Model;

/**
 * 角色-权限关系模型
 * 对应数据表：sys_role_perm
 * 用于维护角色与权限的绑定关系
 */
class SysRolePermModel extends Model
{
    protected $name = 'sys_role_perm';
    protected $pk   = 'id';
    use QueryTrait, QueryCacheTrait;
}
