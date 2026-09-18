<?php

namespace Plugins\PublishPage\Models;

use Illuminate\Database\Eloquent\Model;

class PublishOption extends Model
{
    protected $table = 'plugin_publish_options';

    public $timestamps = false;

    protected $guarded = [];
}
