<?php
namespace App\Models\System;

use App\Traits\QueryCacheTrait;
use App\Traits\QueryTrait;
use Illuminate\Database\Eloquent\Model;

class SysFileModel extends Model
{
    protected $table = 'sys_file';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];

    use QueryTrait, QueryCacheTrait;
}
