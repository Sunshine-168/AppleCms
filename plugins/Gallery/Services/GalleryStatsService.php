<?php

namespace Plugins\Gallery\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Plugins\Gallery\Models\Gallery;
use Plugins\Gallery\Models\GalleryFavor;
use Plugins\Gallery\Models\GalleryPic;

class GalleryStatsService
{
    /** 汇总图集后台统计。 */
    public function summary(): array
    {
        if (! Schema::hasTable('plugin_galleries')) {
            return $this->empty();
        }
        $today = strtotime('today');
        $week = strtotime('-6 days', $today);
        $month = strtotime('-29 days', $today);

        return [
            'works' => $this->workCounts(),
            'week_pics' => $this->picCount($week),
            'month_pics' => $this->picCount($month),
            'top_hits' => $this->topHits(10),
            'top_favors' => $this->topFavors(10),
            'favor_total' => Schema::hasTable('plugin_gallery_favors') ? (int) GalleryFavor::query()->count() : 0,
            'pic_total' => Schema::hasTable('plugin_gallery_pics') ? (int) GalleryPic::query()->count() : 0,
            'hit_total' => (int) Gallery::query()->sum('hits'),
        ];
    }

    /** @return array<string, mixed> */
    private function empty(): array
    {
        return [
            'works' => ['all' => 0, 'show' => 0, 'pending' => 0, 'off' => 0],
            'week_pics' => 0, 'month_pics' => 0, 'top_hits' => [], 'top_favors' => [],
            'favor_total' => 0, 'pic_total' => 0, 'hit_total' => 0,
        ];
    }

    /** @return array{all:int,show:int,pending:int,off:int} */
    private function workCounts(): array
    {
        $q = Gallery::query();

        return [
            'all' => (int) (clone $q)->count(),
            'show' => (int) (clone $q)->where('status', 1)->where('yid', 0)->count(),
            'pending' => (int) (clone $q)->where('yid', 1)->count(),
            'off' => (int) (clone $q)->where('status', 0)->count(),
        ];
    }

    private function picCount(int $from): int
    {
        return Schema::hasTable('plugin_gallery_pics')
            ? (int) GalleryPic::query()->where('created_at', '>=', $from)->count()
            : 0;
    }

    /** @return list<array{id:int,title:string,hits:int}> */
    private function topHits(int $limit): array
    {
        return Gallery::query()->orderByDesc('hits')->orderByDesc('id')->limit($limit)
            ->get(['id', 'title', 'hits'])
            ->map(fn ($r) => ['id' => (int) $r->id, 'title' => (string) $r->title, 'hits' => (int) $r->hits])
            ->all();
    }

    /** @return list<array{id:int,title:string,favors:int}> */
    private function topFavors(int $limit): array
    {
        if (! Schema::hasTable('plugin_gallery_favors')) {
            return [];
        }
        $rows = GalleryFavor::query()->select('gallery_id', DB::raw('COUNT(*) as c'))
            ->groupBy('gallery_id')->orderByDesc('c')->limit($limit)->get();
        $map = Gallery::query()->whereIn('id', $rows->pluck('gallery_id'))->pluck('title', 'id');
        $out = [];
        foreach ($rows as $row) {
            $id = (int) $row->gallery_id;
            $out[] = ['id' => $id, 'title' => (string) ($map[$id] ?? '#'.$id), 'favors' => (int) $row->c];
        }

        return $out;
    }
}
