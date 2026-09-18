<?php

namespace App\Services\Video\Tags;

use App\Models\Video\VideoComment;
use App\Cms\CmsViewContext;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

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
        $mid = (int) ($options['mid'] ?? 1) === 2 ? 2 : 1;
        $q = VideoComment::query()
            ->where('video_id', $videoId)
            ->where('status', 1);
        if (Schema::hasColumn('video_comments', 'mid')) {
            if ($mid === 2) {
                $q->where('mid', 2);
            } else {
                $q->where(function ($inner) {
                    $inner->where('mid', 1)->orWhereNull('mid')->orWhere('mid', 0);
                });
            }
        } elseif ($mid === 2) {
            return collect();
        }

        return $q
            ->orderByDesc('id')
            ->limit($num)
            ->get();
    }
}
