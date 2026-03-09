<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Actor extends Model
{
    protected $table = 'mac_actor';
    protected $primaryKey = 'actor_id';
    public $timestamps = false;
    protected $guarded = [];

    // Relationship with Type
    public function type()
    {
        return $this->belongsTo(Type::class, 'type_id', 'type_id');
    }

    // Helper for status
    public function getStatusTextAttribute()
    {
        return $this->actor_status == 1 ? 'Enabled' : 'Disabled';
    }
}
