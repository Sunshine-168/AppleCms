<?php

namespace Plugins\Novel\Services;

use Illuminate\Support\Facades\Schema;
use Plugins\Novel\Models\NovelComment;

class NovelCommentAdminService
{
    /** @return array{all:int,pending:int,pass:int} */
    public function queues(): array
    {
        $zero = ['all' => 0, 'pending' => 0, 'pass' => 0];
        try {
            if (! Schema::hasTable('plugin_novel_comments')) {
                return $zero;
            }

            return [
                'all' => (int) NovelComment::query()->count(),
                'pending' => (int) NovelComment::query()->where('status', 0)->count(),
                'pass' => (int) NovelComment::query()->where('status', 1)->count(),
            ];
        } catch (\Throwable) {
            return $zero;
        }
    }
}
