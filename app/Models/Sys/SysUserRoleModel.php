<?php
namespace app\common\model;
use app\common\logic\traits\QueryCacheTrait;
use app\common\logic\traits\QueryTrait;
use think\Model;

/**
 * 用户-角色关系模型
 * 对应数据表：sys_user_role
 * 用于维护后台管理员与角色的绑定关系
 */
class SysUserRoleModel extends Model
{
    protected $name = 'sys_user_role';
    protected $pk   = 'id';
    use QueryTrait, QueryCacheTrait;
}
