<?php

namespace Plugins\Live\Services;

use Illuminate\Support\Facades\Schema;
use Plugins\Live\Models\LiveCategory;
use Plugins\Live\Models\LiveChannel;

class LiveStatsService
{
    /** @return array<string, mixed> */
    public function summary(): array
    {
        if (! Schema::hasTable('plugin_live_channels')) {
            return $this->empty();
        }
        $all = (int) LiveChannel::query()->count();
        $on = (int) LiveChannel::query()->where('status', 1)->count();
        $pending = (int) LiveChannel::query()->where('status', 0)->count();
        $cateTotal = Schema::hasTable('plugin_live_categories')
            ? (int) LiveCategory::query()->count()
            : 0;
        $byCate = [];
        if (Schema::hasTable('plugin_live_categories')) {
            $counts = LiveChannel::query()
                ->selectRaw('cate_id, COUNT(*) as c')
                ->groupBy('cate_id')
                ->pluck('c', 'cate_id')
                ->all();
            foreach (LiveCategory::query()->orderByDesc('sort')->orderBy('id')->get(['id', 'name']) as $cate) {
                $byCate[] = [
                    'id' => (int) $cate->id,
                    'name' => (string) $cate->name,
                    'count' => (int) ($counts[(int) $cate->id] ?? 0),
                ];
            }
            $orphan = (int) ($counts[0] ?? 0);
            if ($orphan > 0) {
                $byCate[] = ['id' => 0, 'name' => '未分类', 'count' => $orphan];
            }
        }
        $topHits = LiveChannel::query()
            ->orderByDesc('hits')
            ->orderByDesc('id')
            ->limit(10)
            ->get(['id', 'title', 'hits'])
            ->map(fn (LiveChannel $row) => [
                'id' => (int) $row->id,
                'title' => (string) $row->title,
                'hits' => (int) $row->hits,
            ])
            ->all();

        return [
            'channels' => ['all' => $all, 'on' => $on, 'pending' => $pending],
            'cate_total' => $cateTotal,
            'by_cate' => $byCate,
            'top_hits' => $topHits,
            'hit_total' => (int) LiveChannel::query()->sum('hits'),
        ];
    }

    /** @return array<string, mixed> */
    private function empty(): array
    {
        return [
            'channels' => ['all' => 0, 'on' => 0, 'pending' => 0],
            'cate_total' => 0,
            'by_cate' => [],
            'top_hits' => [],
            'hit_total' => 0,
        ];
    }
}
