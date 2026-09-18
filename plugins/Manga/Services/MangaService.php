<?php

namespace Plugins\Manga\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Plugins\Manga\Models\Manga;
use Plugins\Manga\Models\MangaChapter;
use Plugins\Manga\Models\MangaType;

class MangaService
{
    public function ready(): bool
    {
        return Schema::hasTable('plugin_mangas') && Schema::hasTable('plugin_manga_chapters');
    }

    public function paginate(int $perPage = 24, ?int $typeId = null): LengthAwarePaginator
    {
        $q = Manga::query()->published()->orderByDesc('sort')->orderByDesc('id');
        if ($typeId && $typeId > 0 && Schema::hasColumn('plugin_mangas', 'type_id')) {
            $q->where('type_id', $typeId);
        }

        return $q->paginate($perPage)->withQueryString();
    }

    public function published(int $id): ?Manga
    {
        if (! $this->ready()) {
            return null;
        }

        return Manga::query()->published()->find($id);
    }

    public function chapter(Manga $manga, int $chapterId): ?MangaChapter
    {
        $q = MangaChapter::query()->where('manga_id', $manga->id)->where('id', $chapterId);
        if (Schema::hasTable('plugin_manga_pics')) {
            $q->with('pics');
        }

        return $q->first();
    }

    public function bumpHits(Manga $manga): void
    {
        Manga::query()->where('id', $manga->id)->increment('hits');
    }

    /** @return Collection<int, MangaType> */
    public function listedTypes(): Collection
    {
        if (! Schema::hasTable('plugin_manga_types')) {
            return collect();
        }

        return MangaType::query()->where('status', 1)->orderBy('sort')->orderBy('id')->get();
    }

    /** @return list<array{id:int,name:string,parent_id:int,status:int}> */
    public function adminTypes(): array
    {
        if (! Schema::hasTable('plugin_manga_types')) {
            return [];
        }

        return MangaType::query()
            ->orderBy('sort')
            ->orderBy('id')
            ->get(['id', 'name', 'parent_id', 'status'])
            ->map(static fn (MangaType $row): array => [
                'id' => (int) $row->id,
                'name' => (string) $row->name,
                'parent_id' => (int) $row->parent_id,
                'status' => (int) $row->status,
            ])
            ->all();
    }
}
