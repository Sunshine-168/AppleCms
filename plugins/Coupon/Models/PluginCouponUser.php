<?php

namespace Plugins\Coupon\Models;

use Illuminate\Database\Eloquent\Model;

class PluginCouponUser extends Model
{
    protected $table = 'plugin_coupon_users';

    public $timestamps = false;

    protected $guarded = [];
}
