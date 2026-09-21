<?php

namespace Plugins\Novel\Tags;

use App\Cms\CmsViewContext;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Plugins\Novel\Services\NovelService;

class NovelTag
{
    public function get(array $options = []): Collection|LengthAwarePaginator
    {
        $items = app(NovelService::class)->listForTag($options);
        $rows = $items instanceof LengthAwarePaginator ? collect($items->items()) : $items;
        foreach ($rows as $row) {
            $row->setAttribute('url', url('/novel/'.$row->id));
        }
        if (! empty($options['page']) && $items instanceof LengthAwarePaginator) {
            app(CmsViewContext::class)->setPaginator($items);
        }

        return $items;
    }
}
