<?php
namespace App\Models\System;
use app\common\logic\traits\QueryCacheTrait;
use app\common\logic\traits\QueryTrait;
use think\Model;

/**
 * 权限模型
 * 对应数据表：sys_perm
 * 提供权限点的基础查询能力
 */
class SysPermModel extends Model
{
    protected $name = 'sys_perm';
    protected $pk   = 'id';
    use QueryTrait, QueryCacheTrait;
}
