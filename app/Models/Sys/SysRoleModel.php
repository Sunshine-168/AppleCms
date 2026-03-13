<?php
namespace app\common\model;
use app\common\logic\traits\QueryCacheTrait;
use app\common\logic\traits\QueryTrait;
use think\Model;

/**
 * 角色模型
 * 对应数据表：sys_role
 * 提供角色基础信息的查询与维护
 */
class SysRoleModel extends Model
{
    protected $name = 'sys_role';
    protected $pk   = 'id';
    use QueryTrait, QueryCacheTrait;
}
