<?php
namespace App\Models\System;

use App\Support\QueryCacheTrait;
use App\Support\QueryTrait;
use Illuminate\Database\Eloquent\Model;

class SysLogModel extends Model
{
    protected $table = 'sys_log';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];

    use QueryTrait, QueryCacheTrait;
}
