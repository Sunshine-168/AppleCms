<?php

namespace Plugins\Manga\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MangaComment extends Model
{
    protected $table = 'plugin_manga_comments';

    public $timestamps = false;

    protected $guarded = [];

    public function manga(): BelongsTo
    {
        return $this->belongsTo(Manga::class, 'manga_id');
    }
}
