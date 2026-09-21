<?php

namespace Plugins\Manga\Tags;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Plugins\Manga\Services\MangaService;

class MangaTypeTag
{
    public function get(array $options = []): Collection
    {
        $svc = app(MangaService::class);
        if (! $svc->ready()) {
            return collect();
        }
        $rows = $svc->listedTypes();
        $kind = (string) ($options['type'] ?? 'top');
        if ($kind === 'top' && Schema::hasColumn('plugin_manga_types', 'parent_id')) {
            $rows = $rows->filter(static fn ($row) => (int) ($row->parent_id ?? 0) === 0)->values();
        }
        $num = (int) ($options['num'] ?? 0);
        if ($num > 0) {
            $rows = $rows->take($num)->values();
        }
        foreach ($rows as $row) {
            $row->setAttribute('url', url('/manga/type/'.$row->id));
        }

        return $rows;
    }
}
