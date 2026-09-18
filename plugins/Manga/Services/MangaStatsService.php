<?php

namespace Plugins\Manga\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Plugins\Manga\Models\Manga;
use Plugins\Manga\Models\MangaChapter;
use Plugins\Manga\Models\MangaFavor;
use Plugins\Manga\Models\MangaHistory;

class MangaStatsService
{
    /** @return array<string, mixed> */
    public function summary(): array
    {
        if (! Schema::hasTable('plugin_mangas')) {
            return $this->empty();
        }

        $todayStart = strtotime('today');
        $weekStart = strtotime('-6 days', $todayStart);
        $monthStart = strtotime('-29 days', $todayStart);

        return [
            'works' => $this->workCounts(),
            'today_reads' => $this->historyCount($todayStart, null),
            'week_reads' => $this->historyCount($weekStart, null),
            'month_reads' => $this->historyCount($monthStart, null),
            'week_chapters' => $this->chapterCount($weekStart, null),
            'month_chapters' => $this->chapterCount($monthStart, null),
            'top_hits' => $this->topHits(10),
            'top_favors' => $this->topFavors(10),
            'daily' => $this->dailyTrend(14),
        ];
    }

    /** @return array<string, mixed> */
    private function empty(): array
    {
        return [
            'works' => ['all' => 0, 'show' => 0, 'pending' => 0, 'off' => 0],
            'today_reads' => 0,
            'week_reads' => 0,
            'month_reads' => 0,
            'week_chapters' => 0,
            'month_chapters' => 0,
            'top_hits' => [],
            'top_favors' => [],
            'daily' => [],
        ];
    }

    /** @return array{all:int,show:int,pending:int,off:int} */
    private function workCounts(): array
    {
        $q = Manga::query();

        return [
            'all' => (int) (clone $q)->count(),
            'show' => (int) (clone $q)->where('status', 1)->where(function ($inner) {
                if (Schema::hasColumn('plugin_mangas', 'yid')) {
                    $inner->where('yid', 0);
                }
            })->count(),
            'pending' => Schema::hasColumn('plugin_mangas', 'yid')
                ? (int) (clone $q)->where('yid', 1)->count()
                : 0,
            'off' => (int) (clone $q)->where('status', 0)->count(),
        ];
    }

    private function historyCount(int $from, ?int $to): int
    {
        if (! Schema::hasTable('plugin_manga_histories')) {
            return 0;
        }
        $q = MangaHistory::query()->where('updated_at', '>=', $from);
        if ($to !== null) {
            $q->where('updated_at', '<', $to);
        }

        return (int) $q->count();
    }

    private function chapterCount(int $from, ?int $to): int
    {
        if (! Schema::hasTable('plugin_manga_chapters')) {
            return 0;
        }
        $col = Schema::hasColumn('plugin_manga_chapters', 'created_at') ? 'created_at' : null;
        if ($col === null) {
            return 0;
        }
        $q = MangaChapter::query()->where($col, '>=', $from);
        if ($to !== null) {
            $q->where($col, '<', $to);
        }

        return (int) $q->count();
    }

    /** @return list<array{id:int,title:string,hits:int}> */
    private function topHits(int $limit): array
    {
        $rows = Manga::query()->orderByDesc('hits')->orderByDesc('id')->limit($limit)->get(['id', 'title', 'hits']);
        $out = [];
        foreach ($rows as $row) {
            $out[] = [
                'id' => (int) $row->id,
                'title' => (string) $row->title,
                'hits' => (int) $row->hits,
            ];
        }

        return $out;
    }

    /** @return list<array{id:int,title:string,favors:int}> */
    private function topFavors(int $limit): array
    {
        if (! Schema::hasTable('plugin_manga_favors')) {
            return [];
        }
        $rows = MangaFavor::query()
            ->select('manga_id', DB::raw('COUNT(*) as c'))
            ->groupBy('manga_id')
            ->orderByDesc('c')
            ->limit($limit)
            ->get();
        $ids = $rows->pluck('manga_id')->map(static fn ($id) => (int) $id)->all();
        $map = [];
        if ($ids !== []) {
            foreach (Manga::query()->whereIn('id', $ids)->get(['id', 'title']) as $m) {
                $map[(int) $m->id] = (string) $m->title;
            }
        }
        $out = [];
        foreach ($rows as $row) {
            $id = (int) $row->manga_id;
            $out[] = [
                'id' => $id,
                'title' => $map[$id] ?? ('#'.$id),
                'favors' => (int) ($row->c ?? 0),
            ];
        }

        return $out;
    }

    /** @return list<array{day:string,reads:int,chapters:int}> */
    private function dailyTrend(int $days): array
    {
        $days = max(1, min(60, $days));
        $start = strtotime('today') - (($days - 1) * 86400);
        $map = [];
        for ($i = 0; $i < $days; $i++) {
            $day = date('Y-m-d', $start + $i * 86400);
            $map[$day] = ['day' => $day, 'reads' => 0, 'chapters' => 0];
        }

        if (Schema::hasTable('plugin_manga_histories')) {
            foreach (MangaHistory::query()->where('updated_at', '>=', $start)->get(['updated_at']) as $row) {
                $ts = (int) ($row->updated_at ?? 0);
                if ($ts < $start) {
                    continue;
                }
                $day = date('Y-m-d', $ts);
                if (isset($map[$day])) {
                    $map[$day]['reads']++;
                }
            }
        }
        if (Schema::hasTable('plugin_manga_chapters') && Schema::hasColumn('plugin_manga_chapters', 'created_at')) {
            foreach (MangaChapter::query()->where('created_at', '>=', $start)->get(['created_at']) as $row) {
                $ts = (int) ($row->created_at ?? 0);
                if ($ts < $start) {
                    continue;
                }
                $day = date('Y-m-d', $ts);
                if (isset($map[$day])) {
                    $map[$day]['chapters']++;
                }
            }
        }

        return array_values($map);
    }
}
