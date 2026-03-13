<?php
namespace App\Models\Sys;

use App\Traits\QueryCacheTrait;
use App\Traits\QueryTrait;
use App\Traits\UserTrait;
use Illuminate\Database\Eloquent\Model;

class SysUserModel extends Model
{
    protected $table = 'sys_user';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];

    use QueryTrait,
        UserTrait,
        QueryCacheTrait;

}
