<?php

namespace App\Models\Monitor;

use Illuminate\Database\Eloquent\Model;

class VideoMonitorMin extends Model
{
    protected $table = 'video_monitor_min';

    public $timestamps = false;

    public $incrementing = false;

    protected $guarded = [];
}
