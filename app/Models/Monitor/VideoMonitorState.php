<?php

namespace App\Models\Monitor;

use Illuminate\Database\Eloquent\Model;

class VideoMonitorState extends Model
{
    protected $table = 'video_monitor_state';

    protected $primaryKey = 'state_key';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $guarded = [];
}
