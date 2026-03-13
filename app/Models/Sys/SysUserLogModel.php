<?php
namespace app\common\model;
use app\common\logic\traits\QueryCacheTrait;
use app\common\logic\traits\QueryTrait;
use think\Model;

/**
 * 系统登入表
 */
class SysUserLogModel extends Model
{
    // 设置当前模型对应的完整数据表名称
    protected $name = 'sys_user_log';


    // 主键
    protected $pk    = 'id';

    use QueryTrait, QueryCacheTrait;

}
