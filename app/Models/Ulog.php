<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ulog extends Model
{
    protected $table = 'mac_ulog';
    protected $primaryKey = 'ulog_id';
    public $timestamps = false;
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function vod()
    {
        return $this->belongsTo(Vod::class, 'ulog_rid', 'vod_id');
    }

    public function art()
    {
        return $this->belongsTo(Art::class, 'ulog_rid', 'art_id');
    }

    // Accessor to get related content
    public function getDataAttribute()
    {
        if ($this->ulog_mid == 1) {
            return $this->vod;
        } elseif ($this->ulog_mid == 2) {
            return $this->art;
        }
        return null;
    }
}
