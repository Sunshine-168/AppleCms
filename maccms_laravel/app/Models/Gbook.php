<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gbook extends Model
{
    protected $table = 'mac_gbook';
    protected $primaryKey = 'gbook_id';
    public $timestamps = false;
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function getStatusTextAttribute()
    {
        return $this->gbook_status == 1 ? 'Approved' : 'Pending';
    }
}
