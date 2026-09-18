<?php

namespace App\Models\Monitor;

use Illuminate\Database\Eloquent\Model;

class VideoMonitorRule extends Model
{
    protected $table = 'video_monitor_rules';

    public $timestamps = false;

    protected $guarded = [];
}
