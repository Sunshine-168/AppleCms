<?php

namespace Plugins\Mall\Models;

use Illuminate\Database\Eloquent\Model;

class MallGood extends Model
{
    protected $table = 'plugin_mall_goods';

    public $timestamps = false;

    protected $guarded = [];
}
