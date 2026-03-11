<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Manga extends Model
{
    protected $table = 'mac_manga';
    protected $primaryKey = 'manga_id';
    public $timestamps = false;
    protected $guarded = [];
}
