<?php

namespace Plugins\Pay\Models;

use Illuminate\Database\Eloquent\Model;

class PayChannel extends Model
{
    protected $table = 'plugin_pay_channels';

    public $timestamps = false;

    protected $guarded = [];
}
