<?php

namespace Plugins\Coupon\Models;

use Illuminate\Database\Eloquent\Model;

class PluginCoupon extends Model
{
    protected $table = 'plugin_coupons';

    public $timestamps = false;

    protected $guarded = [];
}
