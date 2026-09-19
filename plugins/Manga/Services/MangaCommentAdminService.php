<?php

namespace Plugins\Manga\Services;

use Illuminate\Support\Facades\Schema;
use Plugins\Manga\Models\MangaComment;

class MangaCommentAdminService
{
    /** @return array{all:int,pending:int,pass:int} */
    public function queues(): array
    {
        $zero = ['all' => 0, 'pending' => 0, 'pass' => 0];
        try {
            if (! Schema::hasTable('plugin_manga_comments')) {
                return $zero;
            }

            return [
                'all' => (int) MangaComment::query()->count(),
                'pending' => (int) MangaComment::query()->where('status', 0)->count(),
                'pass' => (int) MangaComment::query()->where('status', 1)->count(),
            ];
        } catch (\Throwable) {
            return $zero;
        }
    }
}
