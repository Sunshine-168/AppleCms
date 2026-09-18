<?php

namespace App\Models\Monitor;

use Illuminate\Database\Eloquent\Model;

class VideoMonitorHour extends Model
{
    protected $table = 'video_monitor_hour';

    public $timestamps = false;

    public $incrementing = false;

    protected $guarded = [];
}
