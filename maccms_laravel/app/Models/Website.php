<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Website extends Model
{
    protected $table = 'mac_website';
    protected $primaryKey = 'website_id';
    public $timestamps = false;
    protected $guarded = [];
}
