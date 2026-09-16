<?php
namespace App\Models\System;

use App\Models\Concerns\QueryCacheTrait;
use App\Models\Concerns\QueryTrait;
use Illuminate\Database\Eloquent\Model;

class SysLogModel extends Model
{
    protected $table = 'sys_log';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];

    use QueryTrait, QueryCacheTrait;
}
