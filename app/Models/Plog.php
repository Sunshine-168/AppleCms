<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plog extends Model
{
    protected $table = 'mac_plog';
    protected $primaryKey = 'plog_id';
    public $timestamps = false;
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
