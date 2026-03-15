<?php

namespace App\Models\System;

use App\Traits\QueryCacheTrait;
use App\Traits\QueryTrait;
use Illuminate\Database\Eloquent\Model;

class SysOperateLogModel extends Model
{
    protected $table = 'sys_operate_log';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];

    use QueryTrait, QueryCacheTrait;
}

