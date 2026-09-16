<?php

namespace App\Services\Video\Tags;

use App\Models\Video\ActorModel;
use App\Cms\CmsViewContext;
use Illuminate\Support\Collection;

class ActorTag
{
    public function __construct(private readonly CmsViewContext $context) {}

    /** @param  array<string, mixed>  $options */
    public function get(array $options = []): Collection
    {
        $num = max(1, (int) ($options['num'] ?? 20));
        $video = $this->context->video();
        if ($video && empty($options['global'])) {
            return $video->actors()->limit($num)->get();
        }

        return ActorModel::query()->where('status', 1)->orderByDesc('sort')->orderByDesc('id')->limit($num)->get();
    }
}
