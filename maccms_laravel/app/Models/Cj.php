<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cj extends Model
{
    protected $table = 'mac_cj_node';
    protected $primaryKey = 'nodeid';
    public $timestamps = false;
    protected $guarded = [];
}
