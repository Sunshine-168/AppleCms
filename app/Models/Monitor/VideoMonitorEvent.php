<?php

namespace App\Models\Monitor;

use Illuminate\Database\Eloquent\Model;

class VideoMonitorEvent extends Model
{
    protected $table = 'video_monitor_events';

    public $timestamps = false;

    protected $guarded = [];
}
