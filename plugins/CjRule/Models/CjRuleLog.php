<?php

namespace Plugins\CjRule\Models;

use Illuminate\Database\Eloquent\Model;

class CjRuleLog extends Model
{
    protected $table = 'video_cj_rule_logs';

    public $timestamps = false;

    protected $guarded = [];
}
