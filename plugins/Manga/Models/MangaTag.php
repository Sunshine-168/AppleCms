<?php

namespace Plugins\Manga\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MangaTag extends Model
{
    protected $table = 'plugin_manga_tags';

    public $timestamps = false;

    protected $guarded = [];

    public function mangas(): BelongsToMany
    {
        return $this->belongsToMany(Manga::class, 'plugin_manga_tag_rel', 'tag_id', 'manga_id');
    }
}
