<?php

namespace Plugins\Manga\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MangaPic extends Model
{
    protected $table = 'plugin_manga_pics';

    public $timestamps = false;

    protected $guarded = [];

    public function manga(): BelongsTo
    {
        return $this->belongsTo(Manga::class, 'manga_id');
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(MangaChapter::class, 'chapter_id');
    }
}
