<?php

namespace App\Services\Video\Tags;

use App\Models\Video\VideoArt;
use App\Cms\CmsViewContext;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class ArtTag
{
    public function __construct(private readonly CmsViewContext $context) {}

    /** @param  array<string, mixed>  $options */
    public function get(array $options = []): Collection|LengthAwarePaginator
    {
        if (! Schema::hasTable('video_arts')) {
            return collect();
        }
        $num = max(1, (int) ($options['num'] ?? 12));
        $query = VideoArt::query()->where('status', 1)->orderByDesc('id');
        if (! empty($options['page'])) {
            $paginator = $query->paginate($num)->withQueryString();
            $this->context->setPaginator($paginator);

            return $paginator;
        }

        return $query->limit($num)->get();
    }
}
