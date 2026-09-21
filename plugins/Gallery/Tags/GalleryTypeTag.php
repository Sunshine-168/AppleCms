<?php

namespace Plugins\Gallery\Tags;

use Illuminate\Support\Collection;
use Plugins\Gallery\Services\GalleryService;

class GalleryTypeTag
{
    public function get(array $options = []): Collection
    {
        $svc = app(GalleryService::class);
        if (! $svc->ready()) {
            return collect();
        }
        $rows = $svc->types();
        $num = (int) ($options['num'] ?? 0);
        if ($num > 0) {
            $rows = $rows->take($num)->values();
        }
        foreach ($rows as $row) {
            $row->setAttribute('url', url('/gallery/type/'.$row->id));
        }

        return $rows;
    }
}
