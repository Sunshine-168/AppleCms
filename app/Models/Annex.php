<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Annex extends Model
{
    protected $table = 'mac_annex';
    protected $primaryKey = 'annex_id';
    public $timestamps = false;
    protected $guarded = [];
}
