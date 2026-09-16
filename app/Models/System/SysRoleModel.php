<?php
namespace App\Models\System;
use App\Models\Concerns\QueryCacheTrait;
use App\Models\Concerns\QueryTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * 角色模型
 * 对应数据表：sys_role
 * 提供角色基础信息的查询与维护
 */
class SysRoleModel extends Model
{
    protected $table = 'sys_role';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];

    use QueryTrait, QueryCacheTrait;
}
