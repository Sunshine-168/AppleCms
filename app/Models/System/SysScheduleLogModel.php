<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;

class SysScheduleLogModel extends Model
{
    protected $table = 'sys_schedule_log';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $guarded = [];
}
