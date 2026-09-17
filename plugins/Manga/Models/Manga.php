<?php

namespace Plugins\Manga\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Manga extends Model
{
    protected $table = 'plugin_mangas';

    public $timestamps = false;

    protected $guarded = [];

    public function chapters(): HasMany
    {
        return $this->hasMany(MangaChapter::class, 'manga_id')->orderBy('sort')->orderBy('id');
    }
}
