<?php

namespace App\Services\Video\Tags;

use App\Models\Video\VideoGuestbook;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class GuestbookTag
{
    /** @param  array<string, mixed>  $options */
    public function get(array $options = []): Collection
    {
        try {
            if (! Schema::hasTable('video_guestbooks')) {
                return collect();
            }
        } catch (\Throwable) {
            return collect();
        }
        $num = max(1, (int) ($options['num'] ?? 20));

        return VideoGuestbook::query()->where('status', 1)->orderByDesc('id')->limit($num)->get();
    }
}
