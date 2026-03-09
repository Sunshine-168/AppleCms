<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'mac_role';
    protected $primaryKey = 'role_id';
    public $timestamps = false;
    protected $guarded = [];
}
