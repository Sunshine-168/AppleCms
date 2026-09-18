<?php

namespace Plugins\Manga\Models;

use Illuminate\Database\Eloquent\Model;

class MangaType extends Model
{
    protected $table = 'plugin_manga_types';

    public $timestamps = false;

    protected $guarded = [];
}
