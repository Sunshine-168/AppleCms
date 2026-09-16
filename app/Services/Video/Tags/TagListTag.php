<?php

namespace App\Services\Video\Tags;

use App\Models\Video\VideoTagModel;
use Illuminate\Support\Collection;

class TagListTag
{
    /** @param  array<string, mixed>  $options */
    public function get(array $options = []): Collection
    {
        $num = max(1, (int) ($options['num'] ?? 30));

        return VideoTagModel::query()->where('status', 1)->orderByDesc('sort')->orderByDesc('id')->limit($num)->get();
    }
}
