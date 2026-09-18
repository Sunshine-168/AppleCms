<?php

namespace Plugins\Manga\Models;

use Illuminate\Database\Eloquent\Model;

class MangaHistory extends Model
{
    protected $table = 'plugin_manga_histories';

    public $timestamps = false;

    protected $guarded = [];
}
