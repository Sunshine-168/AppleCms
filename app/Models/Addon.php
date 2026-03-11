<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Addon extends Model
{
    protected $table = 'mac_addon';
    protected $primaryKey = 'addon_id';
    public $timestamps = false;
    protected $guarded = [];
}
