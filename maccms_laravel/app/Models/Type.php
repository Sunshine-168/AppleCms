<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    protected $table = 'mac_type';
    protected $primaryKey = 'type_id';
    public $timestamps = false;
    protected $guarded = [];

    // Relationship with Vod
    public function vods()
    {
        return $this->hasMany(Vod::class, 'type_id', 'type_id');
    }
}
