<?php

namespace Plugins\Sms\Models;

use Illuminate\Database\Eloquent\Model;

class SmsCode extends Model
{
    protected $table = 'plugin_sms_codes';

    public $timestamps = false;

    protected $guarded = [];
}
