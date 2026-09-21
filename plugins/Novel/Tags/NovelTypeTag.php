<?php

namespace Plugins\Novel\Tags;

use Illuminate\Support\Collection;
use Plugins\Novel\Services\NovelService;

class NovelTypeTag
{
    public function get(array $options = []): Collection
    {
        $svc = app(NovelService::class);
        if (! $svc->ready()) {
            return collect();
        }
        $rows = $svc->types();
        $num = (int) ($options['num'] ?? 0);
        if ($num > 0) {
            $rows = $rows->take($num)->values();
        }
        foreach ($rows as $row) {
            $row->setAttribute('url', url('/novel/type/'.$row->id));
        }

        return $rows;
    }
}
