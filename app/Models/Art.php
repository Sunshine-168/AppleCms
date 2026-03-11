<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Art extends Model
{
    protected $table = 'mac_art';
    protected $primaryKey = 'art_id';
    public $timestamps = false;
    protected $guarded = [];

    public function type()
    {
        return $this->belongsTo(Type::class, 'type_id', 'type_id');
    }
}
