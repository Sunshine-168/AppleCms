<?php

namespace Plugins\Novel\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Plugins\Novel\Models\Novel;
use Plugins\Novel\Models\NovelChapter;
use Plugins\Novel\Models\NovelFavor;
use Plugins\Novel\Models\NovelHistory;

class NovelStatsService
{
    /** 汇总小说后台统计。 */
    public function summary(): array
    {
        if (! Schema::hasTable('plugin_novels')) {
            return $this->empty();
        }
        $today = strtotime('today');
        $week = strtotime('-6 days', $today);
        $month = strtotime('-29 days', $today);

        return [
            'works' => $this->workCounts(),
            'today_reads' => $this->historyCount($today),
            'week_reads' => $this->historyCount($week),
            'month_reads' => $this->historyCount($month),
            'week_chapters' => $this->chapterCount($week),
            'month_chapters' => $this->chapterCount($month),
            'top_hits' => $this->topHits(10),
            'top_favors' => $this->topFavors(10),
            'favor_total' => Schema::hasTable('plugin_novel_favors') ? (int) NovelFavor::query()->count() : 0,
        ];
    }

    /** @return array<string, mixed> */
    private function empty(): array
    {
        return [
            'works' => ['all' => 0, 'show' => 0, 'pending' => 0, 'off' => 0],
            'today_reads' => 0, 'week_reads' => 0, 'month_reads' => 0,
            'week_chapters' => 0, 'month_chapters' => 0,
            'top_hits' => [], 'top_favors' => [], 'favor_total' => 0,
        ];
    }

    /** @return array{all:int,show:int,pending:int,off:int} */
    private function workCounts(): array
    {
        $q = Novel::query();

        return [
            'all' => (int) (clone $q)->count(),
            'show' => (int) (clone $q)->where('status', 1)->where('yid', 0)->count(),
            'pending' => (int) (clone $q)->where('yid', 1)->count(),
            'off' => (int) (clone $q)->where('status', 0)->count(),
        ];
    }

    private function historyCount(int $from): int
    {
        return Schema::hasTable('plugin_novel_histories')
            ? (int) NovelHistory::query()->where('updated_at', '>=', $from)->count()
            : 0;
    }

    private function chapterCount(int $from): int
    {
        return Schema::hasTable('plugin_novel_chapters')
            ? (int) NovelChapter::query()->where('created_at', '>=', $from)->count()
            : 0;
    }

    /** @return list<array{id:int,title:string,hits:int}> */
    private function topHits(int $limit): array
    {
        return Novel::query()->orderByDesc('hits')->orderByDesc('id')->limit($limit)
            ->get(['id', 'title', 'hits'])
            ->map(fn ($r) => ['id' => (int) $r->id, 'title' => (string) $r->title, 'hits' => (int) $r->hits])
            ->all();
    }

    /** @return list<array{id:int,title:string,favors:int}> */
    private function topFavors(int $limit): array
    {
        if (! Schema::hasTable('plugin_novel_favors')) {
            return [];
        }
        $rows = NovelFavor::query()->select('novel_id', DB::raw('COUNT(*) as c'))
            ->groupBy('novel_id')->orderByDesc('c')->limit($limit)->get();
        $map = Novel::query()->whereIn('id', $rows->pluck('novel_id'))->pluck('title', 'id');
        $out = [];
        foreach ($rows as $row) {
            $id = (int) $row->novel_id;
            $out[] = ['id' => $id, 'title' => (string) ($map[$id] ?? '#'.$id), 'favors' => (int) $row->c];
        }

        return $out;
    }
}
