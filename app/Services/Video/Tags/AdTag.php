<?php

namespace App\Services\Video\Tags;

use App\Models\Video\VideoAd;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class AdTag
{
    /** @param  array<string, mixed>  $options */
    public function get(array $options = []): Collection
    {
        try {
            if (! Schema::hasTable('video_ads')) {
                return collect();
            }
        } catch (\Throwable) {
            return collect();
        }
        $q = VideoAd::query()->where('status', 1);
        $slot = trim((string) ($options['slot'] ?? ''));
        if ($slot !== '') {
            $q->where('slot', $slot);
        }
        $num = max(1, (int) ($options['num'] ?? 10));

        return $q->orderByDesc('sort')->orderBy('id')->limit($num)->get();
    }
}
