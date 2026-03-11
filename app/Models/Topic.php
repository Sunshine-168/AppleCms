<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    protected $table = 'mac_topic';
    protected $primaryKey = 'topic_id';
    public $timestamps = false;
    protected $guarded = [];

    public function getStatusTextAttribute()
    {
        return $this->topic_status == 1 ? 'Enabled' : 'Disabled';
    }
}
