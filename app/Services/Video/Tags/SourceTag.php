<?php

namespace App\Services\Video\Tags;

use App\Cms\CmsViewContext;
use Illuminate\Support\Collection;

class SourceTag
{
    public function __construct(private readonly CmsViewContext $context) {}

    /** @param  array<string, mixed>  $options */
    public function get(array $options = []): Collection
    {
        $video = $this->context->video();
        if (! $video) {
            return collect();
        }

        return $video->sources()->where('status', 1)
            ->when(($options['type'] ?? '') === 'down', fn ($q) => $q->where('type', 'down'))
            ->when(($options['type'] ?? '') === 'play', fn ($q) => $q->where(function ($w) {
                $w->where('type', 'play')->orWhere('type', 'm3u8')->orWhere('type', '')->orWhereNull('type');
            }))
            ->get();
    }
}
