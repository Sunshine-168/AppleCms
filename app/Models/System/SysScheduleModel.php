<?php
namespace App\Models\System;

use App\Models\Concerns\QueryCacheTrait;
use App\Models\Concerns\QueryTrait;
use Illuminate\Database\Eloquent\Model;

class SysScheduleModel extends Model
{
    protected $table = 'sys_schedule';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];

    use QueryTrait, QueryCacheTrait;
}

