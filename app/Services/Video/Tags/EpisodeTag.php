<?php

namespace App\Services\Video\Tags;

use App\Cms\CmsViewContext;
use Illuminate\Support\Collection;

class EpisodeTag
{
    public function __construct(private readonly CmsViewContext $context) {}

    /** @param  array<string, mixed>  $options */
    public function get(array $options = []): Collection
    {
        $source = $this->context->source();
        $sid = (int) ($options['sid'] ?? $options['source_id'] ?? 0);
        if ($sid > 0) {
            $source = $this->context->video()?->sources->firstWhere('id', $sid) ?? $source;
        }
        if (! $source) {
            $video = $this->context->video();

            return $video ? $video->episodes()->where('status', 1)->get() : collect();
        }

        return $source->episodes()->where('status', 1)->get();
    }
}
