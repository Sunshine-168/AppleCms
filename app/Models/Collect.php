<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Collect extends Model
{
    protected $table = 'mac_collect';
    protected $primaryKey = 'collect_id';
    public $timestamps = false;
    protected $guarded = [];
}
