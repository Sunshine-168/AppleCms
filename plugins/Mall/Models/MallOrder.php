<?php

namespace Plugins\Mall\Models;

use Illuminate\Database\Eloquent\Model;

class MallOrder extends Model
{
    protected $table = 'plugin_mall_orders';

    public $timestamps = false;

    protected $guarded = [];
}
