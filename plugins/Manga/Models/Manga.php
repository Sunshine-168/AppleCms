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

    /** @return list<string> */
    public function tagNames(): array
    {
        $raw = trim((string) ($this->tags ?? ''));
        if ($raw === '') {
            return [];
        }
        $parts = preg_split('/[,，|｜]+/u', $raw) ?: [];
        $out = [];
        foreach ($parts as $part) {
            $name = trim((string) $part);
            if ($name !== '') {
                $out[$name] = $name;
            }
        }

        return array_values($out);
    }

    public function serializeLabel(): string
    {
        return ((int) ($this->serialize ?? 0) === 1) ? '完结' : '连载';
    }

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
