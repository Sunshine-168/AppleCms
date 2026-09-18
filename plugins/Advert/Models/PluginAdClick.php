<?php

namespace Plugins\Advert\Models;

use Illuminate\Database\Eloquent\Model;

class PluginAdClick extends Model
{
    protected $table = 'plugin_ad_clicks';

    public $timestamps = false;

    protected $guarded = [];
}
