<?php

namespace Plugins\Manga\Services;

use App\Models\Member\Member;
use Illuminate\Support\Facades\Schema;
use Plugins\Manga\Models\Manga;
use Plugins\Manga\Models\MangaFavor;

class MangaFavorAdminService
{
    /** @return array{all:int,today:int,missing:int} */
    public function queues(): array
    {
        $zero = ['all' => 0, 'today' => 0, 'missing' => 0];
        try {
            if (! Schema::hasTable('plugin_manga_favors')) {
                return $zero;
            }
            $missing = 0;
            if (Schema::hasTable('plugin_mangas')) {
                $missing = (int) MangaFavor::query()
                    ->whereNotIn('manga_id', Manga::query()->select('id'))
                    ->count();
            }

            return [
                'all' => (int) MangaFavor::query()->count(),
                'today' => (int) MangaFavor::query()->where('created_at', '>=', strtotime('today'))->count(),
                'missing' => $missing,
            ];
        } catch (\Throwable) {
            return $zero;
        }
    }

    /** @return array{member_name:string,manga_title:string} */
    public function focus(int $memberId, int $mangaId): array
    {
        $name = '';
        $title = '';
        try {
            if ($memberId > 0 && Schema::hasTable('members')) {
                $name = trim((string) (Member::query()->where('id', $memberId)->value('name') ?? ''));
            }
            if ($mangaId > 0 && Schema::hasTable('plugin_mangas')) {
                $title = trim((string) (Manga::query()->where('id', $mangaId)->value('title') ?? ''));
            }
        } catch (\Throwable) {
        }

        return ['member_name' => $name, 'manga_title' => $title];
    }
}
