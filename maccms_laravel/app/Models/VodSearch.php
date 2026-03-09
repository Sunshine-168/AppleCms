<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VodSearch extends Model
{
    protected $table = 'mac_vod_search';
    protected $primaryKey = 'search_id';
    public $timestamps = false;
    protected $guarded = [];
    
    public $maxIdCount = 1000;
}
