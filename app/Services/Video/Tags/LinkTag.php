<?php

namespace App\Services\Video\Tags;

use App\Models\Video\FriendLink;
use Illuminate\Support\Collection;

class LinkTag
{
    /** @param  array<string, mixed>  $options */
    public function get(array $options = []): Collection
    {
        $num = max(1, (int) ($options['num'] ?? 30));

        return FriendLink::query()->where('status', 1)->orderByDesc('sort')->orderBy('id')->limit($num)->get();
    }
}
