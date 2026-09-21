<?php

namespace App\Services\Stats;

use App\Models\Stat\StatHit;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StatService
{
    /** 概览：今日/昨日/7天/30天 */
    public function overview(): array
    {
        return [
            'today' => $this->periodStats(now()->toDateString(), now()->toDateString()),
            'yesterday' => $this->periodStats(now()->subDay()->toDateString(), now()->subDay()->toDateString()),
            'days7' => $this->periodStats(now()->subDays(6)->toDateString(), now()->toDateString()),
            'days30' => $this->periodStats(now()->subDays(29)->toDateString(), now()->toDateString()),
        ];
    }

    /** @return array{pv:int,uv:int,ip:int,spider_pv:int} */
    public function periodStats(string $from, string $to): array
    {
        $base = StatHit::query()->betweenDates($from, $to);

        return [
            'pv' => (clone $base)->human()->count(),
            'uv' => (clone $base)->human()->distinct('visitor_hash')->count('visitor_hash'),
            'ip' => (clone $base)->human()->whereNotNull('ip')->distinct('ip')->count('ip'),
            'spider_pv' => (clone $base)->spider()->count(),
        ];
    }

    /**
     * 播放页访问：路径 /play/{id}，不含 iframe /player。
     *
     * @return array{pv:int,uv:int}
     */
    public function playPageStats(string $from, string $to): array
    {
        $base = StatHit::query()->betweenDates($from, $to)->human()->where(function ($q) {
            $q->where('path', '/play')
                ->orWhere('path', 'like', '/play/%')
                ->orWhere('path', 'like', '/index.php/vod/play/%');
        });

        return [
            'pv' => (clone $base)->count(),
            'uv' => (clone $base)->distinct('visitor_hash')->count('visitor_hash'),
        ];
    }

    /** 按日趋势 */
    public function dailyTrend(int $days = 14): Collection
    {
        $from = now()->subDays($days - 1)->startOfDay();
        $driver = DB::connection()->getDriverName();
        $dayExpr = match ($driver) {
            'sqlite' => "strftime('%Y-%m-%d', created_at)",
            'pgsql' => "to_char(created_at, 'YYYY-MM-DD')",
            default => 'DATE(created_at)',
        };

        $rows = StatHit::query()
            ->where('created_at', '>=', $from)
            ->selectRaw("{$dayExpr} as day")
            ->selectRaw('SUM(CASE WHEN is_spider = 0 THEN 1 ELSE 0 END) as pv')
            ->selectRaw('SUM(CASE WHEN is_spider = 1 THEN 1 ELSE 0 END) as spider_pv')
            ->selectRaw('COUNT(DISTINCT CASE WHEN is_spider = 0 THEN visitor_hash END) as uv')
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        $out = collect();
        for ($i = 0; $i < $days; $i++) {
            $day = $from->copy()->addDays($i)->toDateString();
            $row = $rows->get($day);
            $out->push([
                'day' => $day,
                'pv' => (int) ($row->pv ?? 0),
                'uv' => (int) ($row->uv ?? 0),
                'spider_pv' => (int) ($row->spider_pv ?? 0),
            ]);
        }

        return $out;
    }

    /** 近 N 日按小时分布（人类访问），看高峰时段 */
    public function hourlyDistribution(int $days = 7): Collection
    {
        $from = now()->subDays($days - 1)->startOfDay();
        $driver = DB::connection()->getDriverName();
        $hourExpr = match ($driver) {
            'sqlite' => "CAST(strftime('%H', created_at) AS INTEGER)",
            'pgsql' => 'EXTRACT(HOUR FROM created_at)::int',
            default => 'HOUR(created_at)',
        };

        $rows = StatHit::query()
            ->where('created_at', '>=', $from)
            ->human()
            ->selectRaw("{$hourExpr} as hour, COUNT(*) as pv, COUNT(DISTINCT visitor_hash) as uv")
            ->groupByRaw($hourExpr)
            ->get()
            ->keyBy(fn ($row) => (int) $row->hour);

        $out = collect();
        for ($hour = 0; $hour < 24; $hour++) {
            $row = $rows->get($hour);
            $out->push([
                'hour' => sprintf('%02d:00', $hour),
                'pv' => (int) ($row->pv ?? 0),
                'uv' => (int) ($row->uv ?? 0),
            ]);
        }

        return $out;
    }

    /**
     * 来路按域名聚合（含直接访问 / 站内跳转）
     *
     * @return Collection<int, array{host:string,hits:int}>
     */
    public function topRefererHosts(string $from, string $to, int $limit = 8): Collection
    {
        $appHost = strtolower((string) parse_url((string) config('app.url'), PHP_URL_HOST));
        $grouped = [];

        StatHit::query()
            ->betweenDates($from, $to)
            ->human()
            ->select('referer', DB::raw('COUNT(*) as hits'))
            ->groupBy('referer')
            ->get()
            ->each(function ($row) use (&$grouped, $appHost) {
                $ref = trim((string) $row->referer);
                $hits = (int) $row->hits;
                if ($ref === '') {
                    $host = admin_t('ui.direct_visit');
                } else {
                    $parsed = strtolower((string) (parse_url($ref, PHP_URL_HOST) ?: ''));
                    $host = $parsed === '' ? admin_t('ui.other') : ($appHost !== '' && $parsed === $appHost ? admin_t('ui.internal_jump') : $parsed);
                }
                $grouped[$host] = ($grouped[$host] ?? 0) + $hits;
            });

        arsort($grouped);

        return collect($grouped)
            ->take($limit)
            ->map(fn (int $hits, string $host) => ['host' => $host, 'hits' => $hits])
            ->values();
    }

    /** @return array{text:string,dir:string,pct:float} */
    public function change(int $current, int $previous, string $label = ''): array
    {
        $label = $label !== '' ? $label : admin_t('dash.vs_yesterday');
        if ($previous === 0 && $current === 0) {
            return ['text' => admin_t('dash.vs_flat', ['label' => $label]), 'dir' => 'flat', 'pct' => 0.0];
        }
        if ($previous === 0) {
            return ['text' => admin_t('dash.vs_new', ['label' => $label]), 'dir' => 'up', 'pct' => 100.0];
        }

        $pct = round(($current - $previous) / $previous * 100, 1);
        $dir = $pct > 0 ? 'up' : ($pct < 0 ? 'down' : 'flat');
        $sign = $pct > 0 ? '+' : '';

        return ['text' => admin_t('dash.vs_pct', ['label' => $label, 'pct' => $sign.$pct]), 'dir' => $dir, 'pct' => $pct];
    }

    /**
     * 等长周期对比（所选时段 vs 再往前一段）
     *
     * @return array{from:string,to:string,prev_from:string,prev_to:string,current:array,previous:array,changes:array}
     */
    public function compareRange(string $from, string $to): array
    {
        $start = Carbon::parse($from)->startOfDay();
        $end = Carbon::parse($to)->startOfDay();
        if ($start->gt($end)) {
            [$start, $end] = [$end, $start];
        }
        $days = $start->diffInDays($end) + 1;
        $prevTo = $start->copy()->subDay()->toDateString();
        $prevFrom = $start->copy()->subDays($days)->toDateString();
        $current = $this->periodStats($start->toDateString(), $end->toDateString());
        $previous = $this->periodStats($prevFrom, $prevTo);

        return [
            'from' => $start->toDateString(),
            'to' => $end->toDateString(),
            'prev_from' => $prevFrom,
            'prev_to' => $prevTo,
            'current' => $current,
            'previous' => $previous,
            'changes' => [
                'pv' => $this->change($current['pv'], $previous['pv'], admin_t('dash.vs_prev')),
                'uv' => $this->change($current['uv'], $previous['uv'], admin_t('dash.vs_prev')),
                'ip' => $this->change($current['ip'], $previous['ip'], admin_t('dash.vs_prev')),
                'spider_pv' => $this->change($current['spider_pv'], $previous['spider_pv'], admin_t('dash.vs_prev')),
            ],
        ];
    }

    /** 近 7 日 vs 再往前 7 日 */
    public function last7VersusPrev7(): array
    {
        return $this->compareRange(now()->subDays(6)->toDateString(), now()->toDateString());
    }

    /**
     * @return array{devices: list<array{name:string,hits:int}>, browsers: list<array{name:string,hits:int}>}
     */
    public function clientBreakdown(string $from, string $to): array
    {
        $rows = StatHit::query()
            ->betweenDates($from, $to)
            ->human()
            ->select('user_agent', DB::raw('COUNT(*) as hits'))
            ->groupBy('user_agent')
            ->get();

        $devices = [
            admin_t('ui.device_desktop') => 0,
            admin_t('ui.device_mobile') => 0,
            admin_t('ui.device_tablet') => 0,
            admin_t('ui.unknown') => 0,
        ];
        $browsers = [];
        foreach ($rows as $row) {
            $hits = (int) $row->hits;
            $ua = (string) $row->user_agent;
            $devices[$this->guessDevice($ua)] += $hits;
            $browser = $this->guessBrowser($ua);
            $browsers[$browser] = ($browsers[$browser] ?? 0) + $hits;
        }
        arsort($browsers);

        return [
            'devices' => collect($devices)
                ->map(fn (int $hits, string $name) => ['name' => $name, 'hits' => $hits])
                ->values()
                ->all(),
            'browsers' => collect($browsers)
                ->take(6)
                ->map(fn (int $hits, string $name) => ['name' => $name, 'hits' => $hits])
                ->values()
                ->all(),
        ];
    }

    public function refererLabel(?string $referer): string
    {
        $ref = trim((string) $referer);
        if ($ref === '') {
            return admin_t('ui.direct_visit');
        }
        $host = strtolower((string) (parse_url($ref, PHP_URL_HOST) ?: ''));
        $appHost = strtolower((string) parse_url((string) config('app.url'), PHP_URL_HOST));
        if ($host === '') {
            return admin_t('ui.other');
        }
        if ($appHost !== '' && $host === $appHost) {
            return admin_t('ui.internal_jump');
        }

        return $host;
    }

    public function clientLabel(?string $ua): string
    {
        $ua = (string) $ua;
        if ($ua === '') {
            return admin_t('ui.unknown');
        }
        if (preg_match('/^curl\//i', $ua)) {
            return 'curl';
        }
        $browser = $this->guessBrowser($ua);
        $device = $this->guessDevice($ua);

        return $browser.' · '.$device;
    }

    public function isLocalProbe(?string $ip, ?string $ua): bool
    {
        $ip = (string) $ip;
        $ua = (string) $ua;
        if (preg_match('/^curl\//i', $ua)) {
            return true;
        }

        return $ip === '127.0.0.1' || $ip === '::1'
            || str_starts_with($ip, '10.')
            || str_starts_with($ip, '192.168.')
            || (bool) preg_match('/^172\.(1[6-9]|2\d|3[0-1])\./', $ip);
    }

    /**
     * @return array{at:mixed,title:string,path:string,query:?string,ip:?string,visitor:string,visitor_kind:string,referer:string,client:string,ua:string,status:int,local:bool,front_url:string}
     */
    public function presentLog(StatHit $hit, ?string $title = null): array
    {
        $ua = (string) $hit->user_agent;

        return [
            'at' => $hit->created_at,
            'title' => $title ?: (string) $hit->path,
            'path' => (string) $hit->path,
            'query' => $hit->query,
            'ip' => $hit->ip,
            'hash' => (string) $hit->visitor_hash,
            'visitor' => $hit->is_spider ? ((string) ($hit->spider_name ?: 'Bot')) : admin_t('ui.human'),
            'visitor_kind' => $hit->is_spider ? 'bot' : 'human',
            'referer' => $this->refererLabel($hit->referer),
            'client' => $this->clientLabel($ua),
            'ua' => $ua,
            'status' => (int) $hit->status_code,
            'local' => $this->isLocalProbe($hit->ip, $ua),
            'front_url' => url((string) $hit->path),
        ];
    }

    public function filteredLogs(array $filters)
    {
        $q = StatHit::query()
            ->betweenDates($filters['from'], $filters['to'])
            ->orderByDesc('id');

        if (($filters['visitor'] ?? '') === 'human') {
            $q->human();
        } elseif (($filters['visitor'] ?? '') === 'spider') {
            $q->spider();
        }
        if (! empty($filters['bot'])) {
            $q->where('spider_name', $filters['bot']);
        }
        if (! empty($filters['path'])) {
            $q->where('path', 'like', '%'.$filters['path'].'%');
        }
        if (($filters['status'] ?? '') === '200') {
            $q->where('status_code', 200);
        } elseif (($filters['status'] ?? '') === '404') {
            $q->where('status_code', 404);
        } elseif (($filters['status'] ?? '') === 'other') {
            $q->where('status_code', '!=', 200);
        }
        if (! empty($filters['ip'])) {
            $q->where('ip', $filters['ip']);
        }
        if (! empty($filters['hash'])) {
            $q->where('visitor_hash', $filters['hash']);
        }
        if (($filters['probe'] ?? '') === 'hide') {
            $q->where(function ($inner) {
                $inner->where(function ($ua) {
                    $ua->whereNull('user_agent')->orWhere('user_agent', 'not like', 'curl/%');
                })->where(function ($ip) {
                    $ip->whereNull('ip')
                        ->orWhere(function ($public) {
                            $public->where('ip', '!=', '127.0.0.1')
                                ->where('ip', '!=', '::1')
                                ->where('ip', 'not like', '10.%')
                                ->where('ip', 'not like', '192.168.%')
                                ->where('ip', 'not like', '172.16.%')
                                ->where('ip', 'not like', '172.17.%')
                                ->where('ip', 'not like', '172.18.%')
                                ->where('ip', 'not like', '172.19.%')
                                ->where('ip', 'not like', '172.20.%')
                                ->where('ip', 'not like', '172.21.%')
                                ->where('ip', 'not like', '172.22.%')
                                ->where('ip', 'not like', '172.23.%')
                                ->where('ip', 'not like', '172.24.%')
                                ->where('ip', 'not like', '172.25.%')
                                ->where('ip', 'not like', '172.26.%')
                                ->where('ip', 'not like', '172.27.%')
                                ->where('ip', 'not like', '172.28.%')
                                ->where('ip', 'not like', '172.29.%')
                                ->where('ip', 'not like', '172.30.%')
                                ->where('ip', 'not like', '172.31.%');
                        });
                });
            });
        }

        return $q;
    }

    /**
     * 同一访客在时段内的访问顺序（用 visitor_hash，近似轨迹）
     *
     * @return Collection<int, array{title:string,path:string,status:int,at:mixed}>
     */
    public function visitorTrail(string $hash, string $from, string $to, int $limit = 20): Collection
    {
        $hits = StatHit::query()
            ->betweenDates($from, $to)
            ->where('visitor_hash', $hash)
            ->orderBy('id')
            ->limit($limit)
            ->get();
        $titles = app(PageTitleResolver::class)->forPaths($hits->pluck('path'));

        return $hits->map(fn (StatHit $hit) => [
            'title' => $titles[$hit->path] ?? $hit->path,
            'path' => (string) $hit->path,
            'status' => (int) $hit->status_code,
            'at' => $hit->created_at,
        ])->values();
    }

    protected function guessDevice(string $ua): string
    {
        if ($ua === '') {
            return admin_t('ui.unknown');
        }
        if (preg_match('/iPad|Tablet|PlayBook/i', $ua)) {
            return admin_t('ui.device_tablet');
        }
        if (preg_match('/Mobile|Android|iPhone|webOS|Opera Mini/i', $ua)) {
            return admin_t('ui.device_mobile');
        }

        return admin_t('ui.device_desktop');
    }

    protected function guessBrowser(string $ua): string
    {
        if ($ua === '') {
            return admin_t('ui.unknown');
        }
        if (preg_match('/MicroMessenger/i', $ua)) {
            return admin_t('ui.browser_wechat');
        }
        if (preg_match('/Edg\//i', $ua)) {
            return 'Edge';
        }
        if (preg_match('/Firefox\//i', $ua)) {
            return 'Firefox';
        }
        if (preg_match('/Chrome\//i', $ua) && ! preg_match('/Edg\//i', $ua)) {
            return 'Chrome';
        }
        if (preg_match('/Safari\//i', $ua)) {
            return 'Safari';
        }

        return admin_t('ui.other');
    }

    /**
     * 热门页面（带栏目标题 / 文档标题）
     *
     * @return Collection<int, array{path:string,title:string,hits:int}>
     */
    public function topPages(string $from, string $to, int $limit = 20, bool $humanOnly = true): Collection
    {
        $q = StatHit::query()->betweenDates($from, $to);
        if ($humanOnly) {
            $q->human();
        }

        $pages = $q->select('path', DB::raw('COUNT(*) as hits'))
            ->groupBy('path')
            ->orderByDesc('hits')
            ->limit($limit)
            ->get();

        $titles = app(PageTitleResolver::class)->forPaths($pages->pluck('path'));

        return $pages->map(fn ($row) => [
            'path' => (string) $row->path,
            'title' => $titles[(string) $row->path] ?? (string) $row->path,
            'hits' => (int) $row->hits,
        ])->values();
    }

    /**
     * 被打到 404 最多的路径
     *
     * @return Collection<int, array{path:string,title:string,hits:int}>
     */
    public function topErrorPages(string $from, string $to, int $limit = 10): Collection
    {
        $pages = StatHit::query()
            ->betweenDates($from, $to)
            ->where('status_code', 404)
            ->select('path', DB::raw('COUNT(*) as hits'))
            ->groupBy('path')
            ->orderByDesc('hits')
            ->limit($limit)
            ->get();
        $titles = app(PageTitleResolver::class)->forPaths($pages->pluck('path'));

        return $pages->map(fn ($row) => [
            'path' => (string) $row->path,
            'title' => $titles[(string) $row->path] ?? (string) $row->path,
            'hits' => (int) $row->hits,
        ])->values();
    }

    /** 来路 */
    public function topReferers(string $from, string $to, int $limit = 20): Collection
    {
        return StatHit::query()
            ->betweenDates($from, $to)
            ->human()
            ->whereNotNull('referer')
            ->where('referer', '!=', '')
            ->select('referer', DB::raw('COUNT(*) as hits'))
            ->groupBy('referer')
            ->orderByDesc('hits')
            ->limit($limit)
            ->get();
    }

    /**
     * 蜘蛛汇总（含分类与占比）
     *
     * @return Collection<int, array{spider_name:string,hits:int,ips:int,group:string,group_label:string,share:float}>
     */
    public function spiderSummary(string $from, string $to): Collection
    {
        $detector = app(SpiderDetector::class);
        $rows = StatHit::query()
            ->betweenDates($from, $to)
            ->spider()
            ->select('spider_name', DB::raw('COUNT(*) as hits'), DB::raw('COUNT(DISTINCT ip) as ips'))
            ->groupBy('spider_name')
            ->orderByDesc('hits')
            ->get();
        $total = max(1, (int) $rows->sum('hits'));

        return $rows->map(function ($row) use ($detector, $total) {
            $group = $detector->group($row->spider_name);

            return [
                'spider_name' => (string) ($row->spider_name ?: 'Unknown'),
                'hits' => (int) $row->hits,
                'ips' => (int) $row->ips,
                'group' => $group,
                'group_label' => $detector->groupLabel($group),
                'share' => round(((int) $row->hits) / $total * 100, 1),
            ];
        })->values();
    }

    /** @return list<array{name:string,hits:int}> */
    public function spiderComposition(string $from, string $to): array
    {
        $buckets = ['search' => 0, 'tool' => 0, 'ai' => 0, 'other' => 0];
        foreach ($this->spiderSummary($from, $to) as $row) {
            $buckets[$row['group']] = ($buckets[$row['group']] ?? 0) + $row['hits'];
        }
        $detector = app(SpiderDetector::class);

        return collect($buckets)
            ->map(fn (int $hits, string $group) => ['name' => $detector->groupLabel($group), 'hits' => $hits, 'group' => $group])
            ->values()
            ->all();
    }

    /** 近 N 日按分类趋势 */
    public function spiderGroupTrend(int $days = 14): Collection
    {
        $from = now()->subDays($days - 1)->toDateString();
        $to = now()->toDateString();
        $detector = app(SpiderDetector::class);
        $byDay = [];
        foreach ($this->spiderDaily($from, $to) as $row) {
            $group = $detector->group($row->spider_name);
            $byDay[(string) $row->day][$group] = ($byDay[(string) $row->day][$group] ?? 0) + (int) $row->hits;
        }

        $out = collect();
        $start = now()->subDays($days - 1)->startOfDay();
        for ($i = 0; $i < $days; $i++) {
            $day = $start->copy()->addDays($i)->toDateString();
            $out->push([
                'day' => $day,
                'search' => (int) ($byDay[$day]['search'] ?? 0),
                'tool' => (int) ($byDay[$day]['tool'] ?? 0),
                'ai' => (int) ($byDay[$day]['ai'] ?? 0),
                'other' => (int) ($byDay[$day]['other'] ?? 0),
            ]);
        }

        return $out;
    }

    /**
     * @return Collection<int, array{path:string,title:string,hits:int}>
     */
    public function topSpiderPages(string $from, string $to, int $limit = 20): Collection
    {
        $pages = StatHit::query()
            ->betweenDates($from, $to)
            ->spider()
            ->select('path', DB::raw('COUNT(*) as hits'))
            ->groupBy('path')
            ->orderByDesc('hits')
            ->limit($limit)
            ->get();
        $titles = app(PageTitleResolver::class)->forPaths($pages->pluck('path'));

        return $pages->map(fn ($row) => [
            'path' => (string) $row->path,
            'title' => $titles[(string) $row->path] ?? (string) $row->path,
            'hits' => (int) $row->hits,
        ])->values();
    }

    public function spiderErrors(string $from, string $to, int $limit = 30): Collection
    {
        return StatHit::query()
            ->betweenDates($from, $to)
            ->spider()
            ->where('status_code', '!=', 200)
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    public function spiderRecent(string $from, string $to, ?string $name = null, ?int $status = null, int $limit = 50): Collection
    {
        $q = StatHit::query()
            ->betweenDates($from, $to)
            ->spider()
            ->orderByDesc('id');
        if ($name) {
            $q->where('spider_name', $name);
        }
        if ($status) {
            $q->where('status_code', $status);
        }

        return $q->limit($limit)->get();
    }

    /** @return list<string> */
    public function spiderNames(string $from, string $to): array
    {
        return StatHit::query()
            ->betweenDates($from, $to)
            ->spider()
            ->whereNotNull('spider_name')
            ->distinct()
            ->orderBy('spider_name')
            ->pluck('spider_name')
            ->all();
    }

    /**
     * 核心搜索引擎到访 / 连续缺席
     *
     * @return list<array{name:string,label:string,today:bool,absent_days:?int,status:string}>
     */
    public function searchEnginePresence(int $absentAfter = 3): array
    {
        $detector = app(SpiderDetector::class);
        $today = now()->toDateString();
        $todayNames = StatHit::query()
            ->betweenDates($today, $today)
            ->spider()
            ->whereIn('spider_name', $detector->watchEngines())
            ->distinct()
            ->pluck('spider_name')
            ->all();

        $out = [];
        foreach ($detector->watchEngines() as $name) {
            $last = StatHit::query()
                ->spider()
                ->where('spider_name', $name)
                ->orderByDesc('created_at')
                ->value('created_at');
            $lastAt = $last ? Carbon::parse($last) : null;
            $absentDays = $lastAt ? (int) $lastAt->copy()->startOfDay()->diffInDays(now()->startOfDay()) : null;
            $todayHit = in_array($name, $todayNames, true);
            if ($todayHit) {
                $status = 'visited';
            } elseif ($lastAt === null) {
                $status = 'never';
            } elseif ($absentDays >= $absentAfter) {
                $status = 'absent';
            } else {
                $status = 'recent';
            }
            $out[] = [
                'name' => $name,
                'label' => $detector->watchLabel($name),
                'today' => $todayHit,
                'absent_days' => $absentDays,
                'status' => $status,
            ];
        }

        return $out;
    }

    /** @return array{pv:int,change:array,presence:list,errors:int,urls:int,tool_ai_share:float,index_hit:bool} */
    public function spiderKpis(string $from, string $to): array
    {
        $overview = $this->overview();
        $composition = $this->spiderComposition($from, $to);
        $total = max(1, collect($composition)->sum('hits'));
        $toolAi = collect($composition)->whereIn('group', ['tool', 'ai'])->sum('hits');
        $errors = StatHit::query()->betweenDates($from, $to)->spider()->where('status_code', '!=', 200)->count();
        $urls = (int) StatHit::query()->betweenDates($from, $to)->spider()->selectRaw('COUNT(DISTINCT path) as c')->value('c');
        $indexHit = StatHit::query()
            ->betweenDates($from, $to)
            ->spider()
            ->where(function ($q) {
                $q->where('path', 'like', '%sitemap.xml')->orWhere('path', 'like', '%rss.xml');
            })
            ->exists();

        return [
            'pv' => $overview['today']['spider_pv'],
            'change' => $this->change($overview['today']['spider_pv'], $overview['yesterday']['spider_pv']),
            'presence' => $this->searchEnginePresence(),
            'errors' => $errors,
            'urls' => $urls,
            'tool_ai_share' => round($toolAi / $total * 100, 1),
            'index_hit' => $indexHit,
        ];
    }

    /** 蜘蛛按日 */
    public function spiderDaily(string $from, string $to): Collection
    {
        $driver = DB::connection()->getDriverName();
        $dayExpr = match ($driver) {
            'sqlite' => "strftime('%Y-%m-%d', created_at)",
            'pgsql' => "to_char(created_at, 'YYYY-MM-DD')",
            default => 'DATE(created_at)',
        };

        return StatHit::query()
            ->betweenDates($from, $to)
            ->spider()
            ->selectRaw("{$dayExpr} as day, spider_name, COUNT(*) as hits")
            ->groupBy('day', 'spider_name')
            ->orderBy('day')
            ->get();
    }

    public function prune(int $keepDays = 90): int
    {
        $before = Carbon::now()->subDays(max(1, $keepDays));

        return StatHit::query()->where('created_at', '<', $before)->delete();
    }

    /** @return array{pv:string,uv:string,width:int,height:int} */
    public function sparkPoints(Collection $trend, int $width = 220, int $height = 56): array
    {
        return [
            'pv' => $this->polyline($trend->pluck('pv')->all(), $width, $height),
            'uv' => $this->polyline($trend->pluck('uv')->all(), $width, $height),
            'width' => $width,
            'height' => $height,
        ];
    }

    /** @param  list<int|float>  $values */
    protected function polyline(array $values, int $width, int $height): string
    {
        $n = count($values);
        if ($n < 2) {
            return '';
        }
        $max = max($values);
        $min = min($values);
        $range = max(1, $max - $min);
        $pts = [];
        foreach ($values as $i => $v) {
            $x = $i / ($n - 1) * $width;
            $y = $height - (($v - $min) / $range) * ($height - 6) - 3;
            $pts[] = round($x, 1).','.round($y, 1);
        }

        return implode(' ', $pts);
    }
}
