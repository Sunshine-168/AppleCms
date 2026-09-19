<?php

namespace Plugins\Gallery\Services;

use Illuminate\Support\Facades\Schema;
use Plugins\Gallery\Models\GalleryComment;

class GalleryCommentAdminService
{
    /** @return array{all:int,pending:int,pass:int} */
    public function queues(): array
    {
        $zero = ['all' => 0, 'pending' => 0, 'pass' => 0];
        try {
            if (! Schema::hasTable('plugin_gallery_comments')) {
                return $zero;
            }

            return [
                'all' => (int) GalleryComment::query()->count(),
                'pending' => (int) GalleryComment::query()->where('status', 0)->count(),
                'pass' => (int) GalleryComment::query()->where('status', 1)->count(),
            ];
        } catch (\Throwable) {
            return $zero;
        }
    }
}
