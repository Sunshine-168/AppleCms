<?php
namespace App\Models\System;
use App\Support\QueryCacheTrait;
use App\Support\QueryTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * 权限模型
 * 对应数据表：sys_perm
 * 提供权限点的基础查询能力
 */
class SysPermModel extends Model
{
    protected $table = 'sys_perm';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];

    use QueryTrait, QueryCacheTrait;
}
