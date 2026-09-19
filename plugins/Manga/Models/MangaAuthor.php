<?php

namespace Plugins\Manga\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MangaAuthor extends Model
{
    protected $table = 'plugin_manga_authors';

    public $timestamps = false;

    protected $guarded = [];

    public function mangas(): BelongsToMany
    {
        return $this->belongsToMany(Manga::class, 'plugin_manga_author_rel', 'author_id', 'manga_id');
    }
}
