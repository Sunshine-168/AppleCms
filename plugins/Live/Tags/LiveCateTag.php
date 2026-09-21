<?php

namespace Plugins\Live\Tags;

use Illuminate\Support\Collection;
use Plugins\Live\Services\LiveService;

class LiveCateTag
{
    public function get(array $options = []): Collection
    {
        $svc = app(LiveService::class);
        if (! $svc->ready()) {
            return collect();
        }
        $rows = $svc->publishedCategories();
        $num = (int) ($options['num'] ?? 0);
        if ($num > 0) {
            $rows = $rows->take($num)->values();
        }
        foreach ($rows as $row) {
            $row->setAttribute('url', url('/live/cate/'.$row->id));
        }

        return $rows;
    }
}
