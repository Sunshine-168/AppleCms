<?php

namespace Plugins\Danmaku\Models;

use Illuminate\Database\Eloquent\Model;

class Danmaku extends Model
{
    protected $table = 'video_danmaku';

    public $timestamps = false;

    protected $guarded = [];
}
