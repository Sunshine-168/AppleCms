<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vod extends Model
{
    // Table name with prefix
    protected $table = 'mac_vod';
    
    // Primary key
    protected $primaryKey = 'vod_id';
    
    // Timestamps are likely integers in maccms, so disable automatic timestamps
    public $timestamps = false;
    
    // Allow mass assignment for now to simplify migration
    protected $guarded = [];

    // Helper to get formatted status
    public function getStatusTextAttribute()
    {
        return $this->vod_status == 1 ? 'Enabled' : 'Disabled';
    }

    public function type()
    {
        return $this->belongsTo(Type::class, 'type_id', 'type_id');
    }

    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id', 'group_id');
    }
}
