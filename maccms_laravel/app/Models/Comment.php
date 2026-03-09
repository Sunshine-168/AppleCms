<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $table = 'mac_comment';
    protected $primaryKey = 'comment_id';
    public $timestamps = false;
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    // Helper to get formatted status
    public function getStatusTextAttribute()
    {
        return $this->comment_status == 1 ? 'Approved' : 'Pending';
    }
}
