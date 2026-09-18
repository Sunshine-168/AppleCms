<?php

namespace Plugins\Manga\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class Manga extends Model
{
    protected $table = 'plugin_mangas';

    public $timestamps = false;

    protected $guarded = [];

    public function type(): BelongsTo
    {
        return $this->belongsTo(MangaType::class, 'type_id');
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(MangaChapter::class, 'manga_id')->orderBy('sort')->orderBy('id');
    }

    public function scopePublished(Builder $query): Builder
    {
        $query->where('status', 1);
        if (Schema::hasColumn($this->getTable(), 'yid')) {
            $query->where('yid', 0);
        }

        return $query;
    }
}
