<?php

namespace Plugins\FriendLink\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Plugins\FriendLink\Models\FriendLink;
use Plugins\FriendLink\Models\FriendLinkClick;
use Plugins\FriendLink\Models\FriendLinkHit;

class FriendLinkStatsService
{
    /** @return array<string, mixed> */
    public function summary(): array
    {
        if (! Schema::hasTable('plugin_friend_links')) {
            return $this->empty();
        }

        $todayStart = strtotime('today');
        $yesterdayStart = strtotime('yesterday');
        $weekStart = strtotime('-6 days', $todayStart);
        $monthStart = strtotime('-29 days', $todayStart);

        return [
            'links' => $this->linkCounts(),
            'today' => $this->window($todayStart, null),
            'yesterday' => $this->window($yesterdayStart, $todayStart),
            'week' => $this->window($weekStart, null),
            'month' => $this->window($monthStart, null),
            'top_hits' => $this->topByHits($monthStart, 10),
            'top_clicks' => $this->topByClicks($monthStart, 10),
            'daily' => $this->dailyTrend(14),
            'hosts' => $this->topHosts($monthStart, 8),
        ];
    }

    /** @return array<string, mixed> */
    private function empty(): array
    {
        $zero = ['hits' => 0, 'clicks' => 0];

        return [
            'links' => ['all' => 0, 'show' => 0, 'pending' => 0, 'reject' => 0, 'freeze' => 0],
            'today' => $zero,
            'yesterday' => $zero,
            'week' => $zero,
            'month' => $zero,
            'top_hits' => [],
            'top_clicks' => [],
            'daily' => [],
            'hosts' => [],
        ];
    }

    /** @return array{all:int,show:int,pending:int,reject:int,freeze:int} */
    private function linkCounts(): array
    {
        $q = FriendLink::query();

        return [
            'all' => (int) (clone $q)->count(),
            'show' => (int) (clone $q)->where('status', 1)->count(),
            'pending' => (int) (clone $q)->where('status', 0)->count(),
            'reject' => (int) (clone $q)->where('status', 2)->count(),
            'freeze' => (int) (clone $q)->where('status', 3)->count(),
        ];
    }

    /**
     * @return array{hits:int,clicks:int}
     */
    private function window(int $from, ?int $to): array
    {
        return [
            'hits' => $this->countBetween('plugin_friend_link_hits', $from, $to),
            'clicks' => $this->countBetween('plugin_friend_link_clicks', $from, $to),
        ];
    }

    private function countBetween(string $table, int $from, ?int $to): int
    {
        if (! Schema::hasTable($table)) {
            return 0;
        }
        $q = DB::table($table)->where('created_at', '>=', $from);
        if ($to !== null) {
            $q->where('created_at', '<', $to);
        }

        return (int) $q->count();
    }

    /** @return list<array{link_id:int,name:string,hits:int,url:string}> */
    private function topByHits(int $from, int $limit): array
    {
        if (! Schema::hasTable('plugin_friend_link_hits')) {
            return [];
        }
        $rows = FriendLinkHit::query()
            ->select('link_id', DB::raw('COUNT(*) as c'))
            ->where('created_at', '>=', $from)
            ->groupBy('link_id')
            ->orderByDesc('c')
            ->limit($limit)
            ->get();

        return $this->decorateTop($rows, 'hits');
    }

    /** @return list<array{link_id:int,name:string,clicks:int,url:string}> */
    private function topByClicks(int $from, int $limit): array
    {
        if (! Schema::hasTable('plugin_friend_link_clicks')) {
            return [];
        }
        $rows = FriendLinkClick::query()
            ->select('link_id', DB::raw('COUNT(*) as c'))
            ->where('created_at', '>=', $from)
            ->groupBy('link_id')
            ->orderByDesc('c')
            ->limit($limit)
            ->get();

        return $this->decorateTop($rows, 'clicks');
    }

    /**
     * @param  \Illuminate\Support\Collection<int, object>  $rows
     * @return list<array<string, mixed>>
     */
    private function decorateTop($rows, string $metric): array
    {
        $ids = [];
        foreach ($rows as $row) {
            $ids[] = (int) ($row->link_id ?? 0);
        }
        $ids = array_values(array_filter(array_unique($ids)));
        $map = [];
        if ($ids !== [] && Schema::hasTable('plugin_friend_links')) {
            foreach (FriendLink::query()->whereIn('id', $ids)->get(['id', 'name', 'url']) as $link) {
                $map[(int) $link->id] = $link;
            }
        }
        $out = [];
        foreach ($rows as $row) {
            $id = (int) ($row->link_id ?? 0);
            $link = $map[$id] ?? null;
            $out[] = [
                'link_id' => $id,
                'name' => $link ? (string) $link->name : ($id > 0 ? '#'.$id : '未知'),
                'url' => $link ? (string) $link->url : '',
                $metric => (int) ($row->c ?? 0),
            ];
        }

        return $out;
    }

    /** @return list<array{day:string,hits:int,clicks:int}> */
    private function dailyTrend(int $days): array
    {
        $days = max(1, min(60, $days));
        $start = strtotime('today') - (($days - 1) * 86400);
        $map = [];
        for ($i = 0; $i < $days; $i++) {
            $day = date('Y-m-d', $start + $i * 86400);
            $map[$day] = ['day' => $day, 'hits' => 0, 'clicks' => 0];
        }

        if (Schema::hasTable('plugin_friend_link_hits')) {
            foreach (FriendLinkHit::query()->where('created_at', '>=', $start)->get(['created_at', 'day_key']) as $row) {
                $ts = (int) ($row->created_at ?? 0);
                $day = $ts > 0 ? date('Y-m-d', $ts) : $this->dayKeyToDate((string) ($row->day_key ?? ''));
                if ($day !== '' && isset($map[$day])) {
                    $map[$day]['hits']++;
                }
            }
        }
        if (Schema::hasTable('plugin_friend_link_clicks')) {
            foreach (FriendLinkClick::query()->where('created_at', '>=', $start)->get(['created_at']) as $row) {
                $ts = (int) ($row->created_at ?? 0);
                if ($ts < $start) {
                    continue;
                }
                $day = date('Y-m-d', $ts);
                if (isset($map[$day])) {
                    $map[$day]['clicks']++;
                }
            }
        }

        return array_values($map);
    }

    /** @return list<array{host:string,hits:int}> */
    private function topHosts(int $from, int $limit): array
    {
        if (! Schema::hasTable('plugin_friend_link_hits')) {
            return [];
        }
        $rows = FriendLinkHit::query()
            ->select('from_host', DB::raw('COUNT(*) as c'))
            ->where('created_at', '>=', $from)
            ->where('from_host', '!=', '')
            ->groupBy('from_host')
            ->orderByDesc('c')
            ->limit($limit)
            ->get();
        $out = [];
        foreach ($rows as $row) {
            $out[] = [
                'host' => (string) ($row->from_host ?? ''),
                'hits' => (int) ($row->c ?? 0),
            ];
        }

        return $out;
    }

    private function dayKeyToDate(string $key): string
    {
        $key = trim($key);
        if (preg_match('/^\d{8}$/', $key) !== 1) {
            return '';
        }

        return substr($key, 0, 4).'-'.substr($key, 4, 2).'-'.substr($key, 6, 2);
    }
}
