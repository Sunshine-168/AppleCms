<?php

namespace Plugins\Manga\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use Plugins\Manga\Models\Manga;
use Plugins\Manga\Models\MangaChapter;

class MangaService
{
    public function ready(): bool
    {
        return Schema::hasTable('plugin_mangas') && Schema::hasTable('plugin_manga_chapters');
    }

    public function paginate(int $perPage = 24): LengthAwarePaginator
    {
        return Manga::query()
            ->where('status', 1)
            ->orderByDesc('sort')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function published(int $id): ?Manga
    {
        if (! $this->ready()) {
            return null;
        }

        return Manga::query()->where('status', 1)->find($id);
    }

    public function chapter(Manga $manga, int $chapterId): ?MangaChapter
    {
        return MangaChapter::query()
            ->where('manga_id', $manga->id)
            ->where('id', $chapterId)
            ->first();
    }

    public function bumpHits(Manga $manga): void
    {
        Manga::query()->where('id', $manga->id)->increment('hits');
    }
}
