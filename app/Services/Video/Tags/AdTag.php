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
        $now = time();
        if (Schema::hasColumn('video_ads', 'expire_at')) {
            $q->where(function ($w) use ($now) {
                $w->where('expire_at', 0)->orWhere('expire_at', '>', $now)->orWhereNull('expire_at');
            });
        }
        $slot = trim((string) ($options['slot'] ?? ''));
        if ($slot !== '') {
            $q->where('slot', $slot);
        }
        $typeId = (int) ($options['type_id'] ?? 0);
        if ($typeId > 0 && Schema::hasColumn('video_ads', 'type_id')) {
            $q->where(function ($w) use ($typeId) {
                $w->where('type_id', 0)->orWhere('type_id', $typeId);
            });
        }
        $num = max(1, (int) ($options['num'] ?? 10));

        return $q->orderByDesc('sort')->orderBy('id')->limit($num)->get();
    }
}
