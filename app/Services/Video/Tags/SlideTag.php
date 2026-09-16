<?php

namespace App\Services\Video\Tags;

use App\Models\Video\VideoSlide;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class SlideTag
{
    /** @param  array<string, mixed>  $options */
    public function get(array $options = []): Collection
    {
        try {
            if (! Schema::hasTable('video_slides')) {
                return collect();
            }
        } catch (\Throwable) {
            return collect();
        }
        $q = VideoSlide::query()->where('status', 1);
        $slot = trim((string) ($options['slot'] ?? ''));
        if ($slot !== '') {
            $q->where('slot', $slot);
        }
        $num = max(1, (int) ($options['num'] ?? 8));

        return $q->orderByDesc('sort')->orderBy('id')->limit($num)->get();
    }
}
