<?php

namespace Plugins\Gallery\Tags;

use App\Cms\CmsViewContext;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Plugins\Gallery\Services\GalleryService;

class GalleryTag
{
    public function get(array $options = []): Collection|LengthAwarePaginator
    {
        $items = app(GalleryService::class)->listForTag($options);
        $rows = $items instanceof LengthAwarePaginator ? collect($items->items()) : $items;
        foreach ($rows as $row) {
            $row->setAttribute('url', url('/gallery/'.$row->id));
        }
        if (! empty($options['page']) && $items instanceof LengthAwarePaginator) {
            app(CmsViewContext::class)->setPaginator($items);
        }

        return $items;
    }
}
