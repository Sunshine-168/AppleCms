<?php

namespace Plugins\Manga\Tags;

use App\Cms\CmsViewContext;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Plugins\Manga\Services\MangaService;

class MangaTag
{
    public function get(array $options = []): Collection|LengthAwarePaginator
    {
        $items = app(MangaService::class)->listForTag($options);
        $rows = $items instanceof LengthAwarePaginator ? collect($items->items()) : $items;
        foreach ($rows as $row) {
            $row->setAttribute('url', url('/manga/'.$row->id));
        }
        if (! empty($options['page']) && $items instanceof LengthAwarePaginator) {
            app(CmsViewContext::class)->setPaginator($items);
        }

        return $items;
    }
}
