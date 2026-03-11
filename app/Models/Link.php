<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    protected $table = 'mac_link';
    protected $primaryKey = 'link_id';
    public $timestamps = false;
    protected $guarded = [];
}
