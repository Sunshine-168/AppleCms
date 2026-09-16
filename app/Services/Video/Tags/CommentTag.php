<?php

namespace App\Services\Video\Tags;

use App\Models\Video\VideoComment;
use App\Cms\CmsViewContext;
use Illuminate\Support\Collection;

class CommentTag
{
    public function __construct(private readonly CmsViewContext $context) {}

    /** @param  array<string, mixed>  $options */
    public function get(array $options = []): Collection
    {
        $videoId = (int) ($options['id'] ?? $this->context->video()?->id ?? 0);
        if ($videoId < 1) {
            return collect();
        }
        $num = max(1, (int) ($options['num'] ?? 30));

        return VideoComment::query()
            ->where('video_id', $videoId)
            ->where('status', 1)
            ->orderByDesc('id')
            ->limit($num)
            ->get();
    }
}
