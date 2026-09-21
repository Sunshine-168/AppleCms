<?php

namespace Plugins\Live\Tags;

use App\Cms\CmsViewContext;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Plugins\Live\Services\LiveService;

class LiveTag
{
    public function get(array $options = []): Collection|LengthAwarePaginator
    {
        $items = app(LiveService::class)->listForTag($options);
        $rows = $items instanceof LengthAwarePaginator ? collect($items->items()) : $items;
        foreach ($rows as $row) {
            $row->setAttribute('url', (string) ($row->front_url ?: url('/live/'.$row->id)));
        }
        if (! empty($options['page']) && $items instanceof LengthAwarePaginator) {
            app(CmsViewContext::class)->setPaginator($items);
        }

        return $items;
    }
}
