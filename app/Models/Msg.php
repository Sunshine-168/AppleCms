<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Msg extends Model
{
    protected $table = 'mac_msg';
    protected $primaryKey = 'msg_id';
    public $timestamps = false;
    protected $guarded = [];
}
