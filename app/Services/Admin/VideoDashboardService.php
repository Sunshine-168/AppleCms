<?php

namespace App\Services\Admin;

use App\Models\Member\MemberHistory;
use App\Models\Stat\StatHit;
use App\Models\Video\VideoCollectLog;
use App\Models\Video\VideoComment;
use App\Models\Video\VideoGuestbook;
use App\Models\Video\VideoModel;
use App\Models\Video\VideoPlayFail;
use App\Models\Video\VideoReport;
use App\Services\Stats\StatService;
use App\Services\Video\VideoSettingService;
use App\Support\Utils\ServerStats;
use Illuminate\Support\Facades\Schema;

class VideoDashboardService
{
    public function __construct(
        private readonly StatService $stats,
        private readonly VideoSettingService $settings,
    ) {
    }

    /** @return array<string, mixed> */
    public function board(): array
    {
        $counts = $this->counts();
        $overview = $this->traffic();
        $today = $overview['today'];
        $yesterday = $overview['yesterday'];
        $todoTotal = $counts['comment_pending'] + $counts['report_open'] + $counts['playfail_open'] + $counts['gbook_pending'] + $counts['collect_fail'];

        return [
            'kpis' => $this->kpis($counts, $today, $yesterday, $todoTotal),
            'spark' => $overview['spark'],
            'todos' => $this->todos($counts),
            'todoTotal' => $todoTotal,
            'activity' => $this->activity(),
            'alerts' => $this->alerts($counts, $today),
            'quickNav' => $this->quickNav($counts, $todoTotal),
            'health' => $this->health($counts),
            'server' => ServerStats::snapshot(),
        ];
    }

    /** @return array<string, int> */
    public function counts(): array
    {
        $today = strtotime('today');

        return [
            'vod_total' => $this->safe('videos', fn () => VideoModel::query()->count()),
            'vod_today' => $this->safe('videos', fn () => VideoModel::query()->where('created_at', '>=', $today)->count()),
            'comment_pending' => $this->safe('video_comments', fn () => VideoComment::query()->where('status', 0)->count()),
            'report_open' => $this->safe('video_reports', fn () => VideoReport::query()->where('status', 0)->count()),
            'playfail_open' => $this->safe('video_play_fails', fn () => VideoPlayFail::query()->where('status', 0)->count()),
            'gbook_pending' => $this->safe('video_guestbooks', fn () => VideoGuestbook::query()->where('status', 0)->count()),
            'collect_fail' => $this->safe('video_collect_logs', fn () => VideoCollectLog::query()->where('ok', 0)->where('created_at', '>=', $today)->count()),
            'play_today' => $this->safe('member_histories', fn () => MemberHistory::query()->where('updated_at', '>=', $today)->count()),
        ];
    }

    /** @return array{today: array, yesterday: array, spark: array} */
    protected function traffic(): array
    {
        $empty = ['pv' => 0, 'uv' => 0, 'ip' => 0, 'spider_pv' => 0];
        $spark = ['pv' => '', 'uv' => '', 'width' => 220, 'height' => 56];
        if (! Schema::hasTable('stat_hits')) {
            return ['today' => $empty, 'yesterday' => $empty, 'spark' => $spark];
        }
        try {
            $overview = $this->stats->overview();

            return [
                'today' => $overview['today'],
                'yesterday' => $overview['yesterday'],
                'spark' => $this->stats->sparkPoints($this->stats->dailyTrend(7)),
            ];
        } catch (\Throwable) {
            return ['today' => $empty, 'yesterday' => $empty, 'spark' => $spark];
        }
    }

    /**
     * @param  array<string, int>  $counts
     * @return list<array<string, mixed>>
     */
    protected function kpis(array $counts, array $today, array $yesterday, int $todoTotal): array
    {
        return [
            [
                'label' => admin_t('dash.visits_today'),
                'value' => $today['pv'],
                'delta' => $this->stats->change($today['pv'], $yesterday['pv']),
                'hint' => admin_t('dash.uv', ['n' => $today['uv']]),
                'href' => route('admin.stats.index'),
                'icon' => 'fa-eye',
                'color' => 'blue',
            ],
            [
                'label' => admin_t('dash.in_today'),
                'value' => $counts['vod_today'],
                'delta' => null,
                'hint' => admin_t('dash.library', ['n' => $counts['vod_total']]),
                'href' => '/admin/video',
                'icon' => 'fa-plus',
                'color' => 'green',
            ],
            [
                'label' => admin_t('dash.pending'),
                'value' => $todoTotal,
                'delta' => null,
                'hint' => $todoTotal > 0 ? admin_t('dash.pending_hint') : admin_t('dash.pending_none'),
                'href' => '#dash-todos',
                'icon' => 'fa-inbox',
                'color' => $todoTotal > 0 ? 'orange' : 'green',
            ],
            [
                'label' => admin_t('dash.play_today'),
                'value' => $counts['play_today'],
                'delta' => null,
                'hint' => admin_t('dash.play_hint'),
                'href' => '/admin/video/ulogs',
                'icon' => 'fa-play',
                'color' => 'cyan',
            ],
        ];
    }

    /**
     * @param  array<string, int>  $counts
     * @return list<array{kind:string,title:string,hint:string,count:int,url:string}>
     */
    protected function todos(array $counts): array
    {
        $items = [
            ['kind' => 'comment', 'title' => admin_t('dash.todo_comment'), 'hint' => admin_t('dash.todo_comment_hint'), 'count' => $counts['comment_pending'], 'url' => '/admin/video/comments'],
            ['kind' => 'report', 'title' => admin_t('dash.todo_report'), 'hint' => admin_t('dash.todo_report_hint'), 'count' => $counts['report_open'], 'url' => '/admin/video/reports'],
            ['kind' => 'playfail', 'title' => admin_t('dash.todo_playfail'), 'hint' => admin_t('dash.todo_playfail_hint'), 'count' => $counts['playfail_open'], 'url' => '/admin/video/playfails'],
            ['kind' => 'gbook', 'title' => admin_t('dash.todo_gbook'), 'hint' => admin_t('dash.todo_gbook_hint'), 'count' => $counts['gbook_pending'], 'url' => '/admin/video/guestbooks'],
            ['kind' => 'collect', 'title' => admin_t('dash.todo_collect'), 'hint' => admin_t('dash.todo_collect_hint'), 'count' => $counts['collect_fail'], 'url' => '/admin/video/collect_logs?ok=0'],
        ];

        return array_values(array_filter($items, fn ($row) => $row['count'] > 0));
    }

    /** @return list<array{kind_label:string,title:string,time:string,url:?string}> */
    protected function activity(): array
    {
        $items = [];
        if (Schema::hasTable('videos')) {
            foreach (VideoModel::query()->orderByDesc('id')->limit(5)->get(['id', 'title', 'created_at']) as $video) {
                $items[] = [
                    'kind_label' => admin_t('dash.in'),
                    'title' => (string) ($video->title ?: admin_t('dash.untitled')),
                    'time' => $this->ago((int) $video->created_at),
                    'url' => '/admin/video',
                    'sort' => (int) $video->created_at,
                ];
            }
        }
        if (Schema::hasTable('video_collect_logs')) {
            foreach (VideoCollectLog::query()->orderByDesc('id')->limit(3)->get() as $log) {
                $n = (int) $log->created_n + (int) $log->updated_n;
                $items[] = [
                    'kind_label' => ((int) $log->ok) === 1 ? admin_t('dash.collect') : admin_t('dash.collect_fail'),
                    'title' => $n > 0 ? admin_t('dash.wrote', ['n' => $n]).($log->msg ? ' · '.$log->msg : '') : (string) ($log->msg ?: admin_t('dash.collect_done')),
                    'time' => $this->ago((int) $log->created_at),
                    'url' => '/admin/video/collect_logs',
                    'sort' => (int) $log->created_at,
                ];
            }
        }
        usort($items, fn ($a, $b) => ($b['sort'] ?? 0) <=> ($a['sort'] ?? 0));

        return array_map(function ($row) {
            unset($row['sort']);

            return $row;
        }, array_slice($items, 0, 8));
    }

    /**
     * @param  array<string, int>  $counts
     * @return list<array{level:string,title:string,url:?string}>
     */
    protected function alerts(array $counts, array $today): array
    {
        $alerts = [];
        $site = $this->settings->all();
        if ((int) ($site['site_closed'] ?? 0) === 1) {
            $alerts[] = ['level' => 'warn', 'title' => admin_t('dash.alert_closed'), 'url' => '/admin/video/settings'];
        }
        if ($counts['vod_total'] === 0) {
            $alerts[] = ['level' => 'warn', 'title' => admin_t('dash.alert_empty'), 'url' => '/admin/video/collects'];
        }
        if ($counts['collect_fail'] > 0) {
            $alerts[] = ['level' => 'warn', 'title' => admin_t('dash.alert_collect_fail', ['n' => $counts['collect_fail']]), 'url' => '/admin/video/collect_logs?ok=0'];
        }
        if ($counts['playfail_open'] > 0) {
            $alerts[] = ['level' => 'warn', 'title' => admin_t('dash.alert_playfail', ['n' => $counts['playfail_open']]), 'url' => '/admin/video/playfails'];
        }
        if (Schema::hasTable('stat_hits')) {
            try {
                $day = now()->toDateString();
                $n404 = StatHit::query()->betweenDates($day, $day)->where('status_code', 404)->count();
                if ($n404 >= 5) {
                    $alerts[] = ['level' => 'warn', 'title' => admin_t('dash.alert_404', ['n' => $n404]), 'url' => route('admin.stats.logs', ['status' => '404', 'from' => $day, 'to' => $day])];
                }
            } catch (\Throwable) {
            }
        }
        if ($counts['vod_total'] > 0 && $today['pv'] === 0) {
            $alerts[] = ['level' => 'info', 'title' => admin_t('dash.alert_no_pv'), 'url' => url('/')];
        }

        return $alerts;
    }

    /**
     * @param  array<string, int>  $counts
     * @return list<array{title:string,icon:string,url:string,primary:bool}>
     */
    protected function quickNav(array $counts, int $todoTotal): array
    {
        if ($counts['vod_total'] === 0) {
            return [
                ['title' => admin_t('dash.go_collect'), 'icon' => 'fas fa-cloud-download-alt', 'url' => '/admin/video/collects', 'primary' => true],
                ['title' => admin_t('dash.add_video'), 'icon' => 'fas fa-plus', 'url' => '/admin/video', 'primary' => false],
                ['title' => admin_t('dash.site_settings'), 'icon' => 'fas fa-cog', 'url' => '/admin/video/settings', 'primary' => false],
            ];
        }
        if ($todoTotal > 0) {
            return [
                ['title' => admin_t('dash.do_todos'), 'icon' => 'fas fa-inbox', 'url' => '#dash-todos', 'primary' => true],
                ['title' => admin_t('dash.keep_collect'), 'icon' => 'fas fa-cloud-download-alt', 'url' => '/admin/video/collects', 'primary' => false],
                ['title' => admin_t('dash.see_stats'), 'icon' => 'fas fa-chart-line', 'url' => route('admin.stats.index'), 'primary' => false],
            ];
        }

        return [
            ['title' => admin_t('dash.collect_update'), 'icon' => 'fas fa-cloud-download-alt', 'url' => '/admin/video/collects', 'primary' => true],
            ['title' => admin_t('dash.see_stats'), 'icon' => 'fas fa-chart-line', 'url' => route('admin.stats.index'), 'primary' => false],
            ['title' => admin_t('dash.site_settings'), 'icon' => 'fas fa-cog', 'url' => '/admin/video/settings', 'primary' => false],
        ];
    }

    /** @param  array<string, int>  $counts */
    protected function health(array $counts): array
    {
        $site = $this->settings->all();
        $closed = (int) ($site['site_closed'] ?? 0) === 1;
        $cache = (int) ($site['html_cache_enabled'] ?? 0) === 1;
        $last = null;
        $lastOk = true;
        if (Schema::hasTable('video_collect_logs')) {
            $row = VideoCollectLog::query()->orderByDesc('id')->first();
            if ($row) {
                $last = $this->ago((int) $row->created_at);
                $lastOk = (int) $row->ok === 1;
            }
        }

        return [
            'vod_total' => $counts['vod_total'],
            'theme' => (string) config('video.theme', 'default'),
            'cache' => $cache,
            'closed' => $closed,
            'last_collect' => $last,
            'last_ok' => $lastOk,
            'attention' => $closed || ! $lastOk || $counts['vod_total'] === 0,
        ];
    }

    protected function ago(int $ts): string
    {
        if ($ts <= 0) {
            return '';
        }
        $diff = time() - $ts;
        if ($diff < 60) {
            return admin_t('dash.just_now');
        }
        if ($diff < 3600) {
            return admin_t('dash.minutes_ago', ['n' => (int) floor($diff / 60)]);
        }
        if ($diff < 86400) {
            return admin_t('dash.hours_ago', ['n' => (int) floor($diff / 3600)]);
        }

        return date('m-d H:i', $ts);
    }

    protected function safe(string $table, callable $fn): int
    {
        try {
            if (! Schema::hasTable($table)) {
                return 0;
            }

            return (int) $fn();
        } catch (\Throwable) {
            return 0;
        }
    }
}
