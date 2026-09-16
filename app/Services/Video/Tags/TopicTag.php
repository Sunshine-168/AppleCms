<?php

namespace App\Services\Video\Tags;

use App\Models\Video\VideoTopicModel;
use Illuminate\Support\Collection;

class TopicTag
{
    /** @param  array<string, mixed>  $options */
    public function get(array $options = []): Collection
    {
        $num = max(1, (int) ($options['num'] ?? 10));

        return VideoTopicModel::query()->where('status', 1)->orderByDesc('sort')->orderByDesc('id')->limit($num)->get();
    }
}
