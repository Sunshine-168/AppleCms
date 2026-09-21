<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Stat\StatHit;
use App\Services\Stats\PageTitleResolver;
use App\Services\Stats\StatService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StatController extends Controller
{
    public function __construct(private readonly StatService $stats)
    {
    }

    /** 流量概览 */
    public function index(Request $request): View
    {
        [$from, $to] = $this->range($request);
        $overview = $this->stats->overview();
        $today = $overview['today'];
        $yesterday = $overview['yesterday'];

        return view('admin.stats.index', [
            'overview' => $overview,
            'changes' => [
                'pv' => $this->stats->change($today['pv'], $yesterday['pv']),
                'uv' => $this->stats->change($today['uv'], $yesterday['uv']),
                'ip' => $this->stats->change($today['ip'], $yesterday['ip']),
                'spider_pv' => $this->stats->change($today['spider_pv'], $yesterday['spider_pv']),
            ],
            'weekCompare' => $this->stats->last7VersusPrev7(),
            'rangeCompare' => $this->stats->compareRange($from, $to),
            'clients' => $this->stats->clientBreakdown($from, $to),
            'trend' => $this->stats->dailyTrend(14),
            'hourly' => $this->stats->hourlyDistribution(7),
            'topPages' => $this->stats->topPages($from, $to),
            'topErrors' => $this->stats->topErrorPages($from, $to),
            'topReferers' => $this->stats->topReferers($from, $to),
            'refererHosts' => $this->stats->topRefererHosts($from, $to),
            'from' => $from,
            'to' => $to,
        ]);
    }

    /** 导出当前筛选的流量摘要 */
    public function export(Request $request): StreamedResponse
    {
        [$from, $to] = $this->range($request);
        $overview = $this->stats->overview();
        $range = $this->stats->compareRange($from, $to);
        $week = $this->stats->last7VersusPrev7();
        $pages = $this->stats->topPages($from, $to);
        $hosts = $this->stats->topRefererHosts($from, $to, 20);
        $clients = $this->stats->clientBreakdown($from, $to);

        $filename = 'stats-'.$from.'-'.$to.'.csv';

        return response()->streamDownload(function () use ($overview, $range, $week, $pages, $hosts, $clients) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            $write = function (array $row) use ($out) {
                fputcsv($out, $row);
            };

            $write([admin_t('ui.stats_overview')]);
            $write([admin_t('ui.csv_period'), 'PV', 'UV', admin_t('ui.unique_ip'), admin_t('ui.spider_pv')]);
            foreach (['today' => admin_t('ui.today'), 'yesterday' => admin_t('ui.yesterday'), 'days7' => admin_t('ui.last_7d'), 'days30' => admin_t('ui.csv_days30')] as $key => $label) {
                $row = $overview[$key];
                $write([$label, $row['pv'], $row['uv'], $row['ip'], $row['spider_pv']]);
            }
            $write([]);
            $write([admin_t('ui.week_vs_prev'), $week['from'].' ~ '.$week['to'], admin_t('ui.compare'), $week['prev_from'].' ~ '.$week['prev_to']]);
            $write([admin_t('ui.metric'), admin_t('ui.this_period'), admin_t('ui.prev_period'), admin_t('ui.csv_change')]);
            foreach (['pv' => 'PV', 'uv' => 'UV'] as $key => $label) {
                $write([$label, $week['current'][$key], $week['previous'][$key], $week['changes'][$key]['text']]);
            }
            $write([]);
            $write([admin_t('ui.range_vs_prev'), $range['from'].' ~ '.$range['to'], admin_t('ui.prev_period'), $range['prev_from'].' ~ '.$range['prev_to']]);
            $write([admin_t('ui.metric'), admin_t('ui.this_period'), admin_t('ui.prev_period'), admin_t('ui.csv_change')]);
            foreach (['pv' => 'PV', 'uv' => 'UV', 'ip' => admin_t('ui.unique_ip'), 'spider_pv' => admin_t('ui.spider_pv')] as $key => $label) {
                $write([$label, $range['current'][$key], $range['previous'][$key], $range['changes'][$key]['text']]);
            }
            $write([]);
            $write([admin_t('ui.hot_pages')]);
            $write([admin_t('ui.title_label'), admin_t('ui.path_col'), 'PV']);
            foreach ($pages as $page) {
                $write([$page['title'], $page['path'], $page['hits']]);
            }
            $write([]);
            $write([admin_t('ui.csv_referer_hosts')]);
            $write([admin_t('ui.csv_source'), admin_t('ui.times')]);
            foreach ($hosts as $host) {
                $write([$host['host'], $host['hits']]);
            }
            $write([]);
            $write([admin_t('ui.devices')]);
            $write([admin_t('ui.csv_type'), admin_t('ui.times')]);
            foreach ($clients['devices'] as $row) {
                $write([$row['name'], $row['hits']]);
            }
            $write([]);
            $write([admin_t('ui.browsers')]);
            $write([admin_t('ui.csv_name'), admin_t('ui.times')]);
            foreach ($clients['browsers'] as $row) {
                $write([$row['name'], $row['hits']]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** 蜘蛛统计 */
    public function spiders(Request $request): View
    {
        [$from, $to] = $this->range($request);
        $spider = $request->string('bot')->toString() ?: null;
        $status = $request->filled('status') ? $request->integer('status') : null;
        $errors = $this->stats->spiderErrors($from, $to);
        $recent = $this->stats->spiderRecent($from, $to, $spider, $status);

        return view('admin.stats.spiders', [
            'kpis' => $this->stats->spiderKpis($from, $to),
            'composition' => $this->stats->spiderComposition($from, $to),
            'trend' => $this->stats->spiderGroupTrend(14),
            'summary' => $this->stats->spiderSummary($from, $to),
            'topPages' => $this->stats->topSpiderPages($from, $to),
            'errors' => $errors,
            'recent' => $recent,
            'pathTitles' => app(PageTitleResolver::class)->forPaths($recent->pluck('path')->merge($errors->pluck('path'))),
            'spiderNames' => $this->stats->spiderNames($from, $to),
            'from' => $from,
            'to' => $to,
            'bot' => $spider,
            'status' => $status,
        ]);
    }

    public function exportSpiders(Request $request): StreamedResponse
    {
        [$from, $to] = $this->range($request);
        $kpis = $this->stats->spiderKpis($from, $to);
        $summary = $this->stats->spiderSummary($from, $to);
        $pages = $this->stats->topSpiderPages($from, $to);
        $errors = $this->stats->spiderErrors($from, $to, 200);

        $filename = 'spiders-'.$from.'-'.$to.'.csv';

        return response()->streamDownload(function () use ($kpis, $summary, $pages, $errors, $from, $to) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            $write = fn (array $row) => fputcsv($out, $row);
            $write([admin_t('ui.csv_period'), $from.' ~ '.$to]);
            $write([admin_t('ui.csv_today_spider_pv'), $kpis['pv'], $kpis['change']['text']]);
            $write([admin_t('ui.csv_period_urls'), $kpis['urls']]);
            $write([admin_t('ui.csv_non_200_n'), $kpis['errors']]);
            $write([admin_t('ui.csv_tool_ai_share'), $kpis['tool_ai_share'].'%']);
            $write([]);
            $write([admin_t('ui.csv_engine_visit')]);
            $write([admin_t('ui.csv_engine'), admin_t('ui.today'), admin_t('ui.status'), admin_t('ui.csv_last_seen_days')]);
            foreach ($kpis['presence'] as $row) {
                $write([$row['label'], $row['today'] ? admin_t('ui.yes') : admin_t('ui.no'), $row['status'], $row['absent_days'] ?? '']);
            }
            $write([]);
            $write([admin_t('ui.csv_spider_summary')]);
            $write([admin_t('ui.csv_spider'), admin_t('ui.page_type'), admin_t('ui.times'), admin_t('ui.unique_ip'), admin_t('ui.csv_share_pct')]);
            foreach ($summary as $row) {
                $write([$row['spider_name'], $row['group_label'], $row['hits'], $row['ips'], $row['share']]);
            }
            $write([]);
            $write([admin_t('ui.csv_spider_pages')]);
            $write([admin_t('ui.title_label'), admin_t('ui.path_col'), admin_t('ui.times')]);
            foreach ($pages as $page) {
                $write([$page['title'], $page['path'], $page['hits']]);
            }
            $write([]);
            $write([admin_t('ui.csv_non_200')]);
            $write([admin_t('ui.col_time'), admin_t('ui.csv_spider'), admin_t('ui.path_col'), admin_t('ui.status')]);
            foreach ($errors as $row) {
                $write([(string) $row->created_at, $row->spider_name, $row->path, $row->status_code]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** 明细日志 */
    public function logs(Request $request): View
    {
        $filters = $this->logFilters($request);
        $page = $this->stats->filteredLogs($filters)->paginate(20)->withQueryString();
        $titles = app(PageTitleResolver::class)->forPaths($page->getCollection()->pluck('path'));
        $rows = $page->getCollection()->map(
            fn (StatHit $hit) => $this->stats->presentLog($hit, $titles[$hit->path] ?? null)
        );

        $trail = $filters['hash'] !== ''
            ? $this->stats->visitorTrail($filters['hash'], $filters['from'], $filters['to'])
            : collect();
        $errorCount = $this->stats->filteredLogs($filters)->where('status_code', '!=', 200)->count();

        return view('admin.stats.logs', [
            'logs' => $page,
            'rows' => $rows,
            'filters' => $filters,
            'activeFilters' => $this->activeLogFilters($filters),
            'trail' => $trail,
            'errorCount' => $errorCount,
        ]);
    }

    public function exportLogs(Request $request): StreamedResponse
    {
        $filters = $this->logFilters($request);
        $hits = $this->stats->filteredLogs($filters)->limit(5000)->get();
        $titles = app(PageTitleResolver::class)->forPaths($hits->pluck('path'));
        $filename = 'logs-'.$filters['from'].'-'.$filters['to'].'.csv';

        return response()->streamDownload(function () use ($hits, $titles) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, [
                admin_t('ui.col_time'),
                admin_t('ui.visitor_col'),
                admin_t('ui.title_label'),
                admin_t('ui.path_col'),
                'IP',
                admin_t('ui.referrer'),
                admin_t('ui.client_col'),
                admin_t('ui.status'),
                admin_t('ui.local_chip'),
            ]);
            foreach ($hits as $hit) {
                $row = $this->stats->presentLog($hit, $titles[$hit->path] ?? null);
                fputcsv($out, [
                    (string) $row['at'],
                    $row['visitor'],
                    $row['title'],
                    $row['path'],
                    $row['ip'],
                    $row['referer'],
                    $row['client'],
                    $row['status'],
                    $row['local'] ? admin_t('ui.yes') : '',
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** @return array{from:string,to:string,visitor:string,bot:string,path:string,status:string,ip:string,hash:string,probe:string} */
    protected function logFilters(Request $request): array
    {
        [$from, $to] = $this->range($request);
        $visitor = (string) $request->input('visitor', '');
        if ($visitor === '' && $request->filled('spider')) {
            $visitor = $request->boolean('spider') ? 'spider' : 'human';
        }
        if (! in_array($visitor, ['', 'human', 'spider'], true)) {
            $visitor = '';
        }
        $status = (string) $request->input('status', '');
        if (! in_array($status, ['', '200', '404', 'other'], true)) {
            $status = '';
        }

        return [
            'from' => $from,
            'to' => $to,
            'visitor' => $visitor,
            'bot' => $request->string('bot')->toString(),
            'path' => $request->string('path')->toString(),
            'status' => $status,
            'ip' => $request->string('ip')->toString(),
            'hash' => $request->string('hash')->toString(),
            'probe' => $request->string('probe')->toString() === 'hide' ? 'hide' : '',
        ];
    }

    /** @param  array{from:string,to:string,visitor:string,bot:string,path:string,status:string,ip:string,hash:string,probe:string}  $filters */
    protected function activeLogFilters(array $filters): int
    {
        $count = 0;
        foreach (['visitor', 'bot', 'path', 'status', 'ip', 'hash', 'probe'] as $key) {
            if ($filters[$key] !== '') {
                $count++;
            }
        }

        return $count;
    }

    /** @return array{0:string,1:string} */
    protected function range(Request $request): array
    {
        $to = $request->input('to', now()->toDateString());
        $from = $request->input('from', now()->subDays(6)->toDateString());
        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }

        return [$from, $to];
    }
}
