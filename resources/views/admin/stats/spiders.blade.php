@extends('admin.layouts.inner')
@section('title', admin_t('page.stats_spiders'))
@section('plain')
    @include('admin.stats._nav', ['active' => 'spiders'])

    @php
        $visited = collect($kpis['presence'])->where('today', true)->count();
        $risks = collect($kpis['presence'])->whereIn('status', ['absent', 'never']);
    @endphp

    <div class="stat-grid">
        <div class="stat-card">
            <div>
                <div class="label">{{ admin_t('ui.spider_pv_today') }}</div>
                <div class="value">{{ $kpis['pv'] }}</div>
                <div class="delta {{ $kpis['change']['dir'] }}">{{ $kpis['change']['text'] }}</div>
            </div>
            <div class="icon orange"><i class="fas fa-spider"></i></div>
        </div>
        <div class="stat-card">
            <div>
                <div class="label">{{ admin_t('ui.engines_today') }}</div>
                <div class="value">{{ $visited }}/3</div>
                <div class="delta {{ $risks->isEmpty() ? 'up' : 'down' }}">
                    @if($risks->isEmpty())
                        {{ admin_t('ui.engines_ok') }}
                    @else
                        {{ $risks->map(fn ($r) => $r['label'].($r['status'] === 'never' ? admin_t('ui.never_seen') : admin_t('ui.absent_days', ['n' => $r['absent_days'] ?? ''])))->implode(' · ') }}
                    @endif
                </div>
            </div>
            <div class="icon blue"><i class="fas fa-search"></i></div>
        </div>
        <div class="stat-card">
            <div>
                <div class="label">{{ admin_t('ui.bad_crawls') }}</div>
                <div class="value">{{ $kpis['errors'] }}</div>
                <div class="delta {{ $kpis['errors'] > 0 || $kpis['tool_ai_share'] >= 40 ? 'down' : 'flat' }}">
                    {{ admin_t('ui.non_200_tools', ['n' => $kpis['tool_ai_share']]) }}
                </div>
            </div>
            <div class="icon purple"><i class="fas fa-exclamation-triangle"></i></div>
        </div>
        <div class="stat-card">
            <div>
                <div class="label">{{ admin_t('ui.urls_covered') }}</div>
                <div class="value">{{ $kpis['urls'] }}</div>
                <div class="delta {{ $kpis['index_hit'] ? 'up' : 'flat' }}">
                    {{ $kpis['index_hit'] ? admin_t('ui.hit_sitemap') : admin_t('ui.miss_sitemap') }}
                </div>
            </div>
            <div class="icon green"><i class="fas fa-link"></i></div>
        </div>
    </div>

    @if($risks->isNotEmpty())
        <p class="flash warn-line">{{ admin_t('ui.absent_warn', ['names' => $risks->map(fn ($r) => $r['label'])->implode('、')]) }}</p>
    @endif

    <div class="chart-grid">
        <div class="card card-panel">
            <div class="card-header">
                <span><i class="fas fa-chart-line"></i> {{ admin_t('ui.trend_14d') }}</span>
            </div>
            <div class="card-body">
                <div class="chart-box">
                    <canvas id="spiderTrendChart"></canvas>
                </div>
            </div>
        </div>
        <div class="card card-panel">
            <div class="card-header">
                <span><i class="fas fa-chart-pie"></i> {{ admin_t('ui.bot_mix') }}</span>
            </div>
            <div class="card-body">
                <div class="chart-box">
                    <canvas id="spiderMixChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-panel">
        <div class="card-header">
            <span><i class="fas fa-fire"></i> {{ admin_t('ui.hottest_crawled') }}</span>
        </div>
        <div class="card-body">
            <div class="chart-box chart-box-sm">
                <canvas id="spiderPagesChart"></canvas>
            </div>
        </div>
    </div>

    <form method="get" class="filter-bar">
        <div class="field">
            <label for="spider-from">{{ admin_t('ui.date_from') }}</label>
            <input id="spider-from" type="date" name="from" value="{{ $from }}">
        </div>
        <div class="field">
            <label for="spider-to">{{ admin_t('ui.date_to') }}</label>
            <input id="spider-to" type="date" name="to" value="{{ $to }}">
        </div>
        <div class="field">
            <label for="spider-bot">{{ admin_t('ui.spider') }}</label>
            <select id="spider-bot" name="bot">
                <option value="">{{ admin_t('ui.all') }}</option>
                @foreach($spiderNames as $name)
                    <option value="{{ $name }}" @selected($bot === $name)>{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div class="field">
            <label for="spider-status">{{ admin_t('ui.status') }}</label>
            <select id="spider-status" name="status">
                <option value="">{{ admin_t('ui.all') }}</option>
                <option value="200" @selected($status === 200)>200</option>
                <option value="404" @selected($status === 404)>404</option>
            </select>
        </div>
        <button class="btn" type="submit">{{ admin_t('ui.filter') }}</button>
        <a class="btn btn-muted" href="{{ route('admin.stats.spiders.export', ['from' => $from, 'to' => $to]) }}"><i class="fas fa-download"></i> {{ admin_t('ui.export_csv') }}</a>
    </form>

    <div class="chart-grid two">
        <div class="card card-panel">
            <div class="card-header"><span>{{ admin_t('ui.spider_summary') }}</span></div>
            <div class="card-body" style="padding:0">
                <table class="data" style="border:0">
                    <thead><tr><th>{{ admin_t('ui.spider') }}</th><th>{{ admin_t('ui.category') }}</th><th>{{ admin_t('ui.times') }}</th><th>{{ admin_t('ui.unique_ip') }}</th><th>{{ admin_t('ui.share_pct') }}</th></tr></thead>
                    <tbody>
                    @forelse($summary as $row)
                        <tr>
                            <td>{{ $row['spider_name'] }}</td>
                            <td><span class="badge badge-{{ $row['group'] }}">{{ $row['group_label'] }}</span></td>
                            <td>{{ $row['hits'] }}</td>
                            <td>{{ $row['ips'] }}</td>
                            <td>{{ $row['share'] }}%</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="muted">{{ admin_t('ui.no_spider_hits') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card card-panel">
            <div class="card-header"><span>{{ admin_t('ui.hot_crawled') }}</span></div>
            <div class="card-body" style="padding:0">
                <table class="data" style="border:0">
                    <thead><tr><th>{{ admin_t('ui.title_label') }}</th><th>{{ admin_t('ui.path_col') }}</th><th>{{ admin_t('ui.times') }}</th><th></th></tr></thead>
                    <tbody>
                    @forelse($topPages as $p)
                        <tr>
                            <td>{{ $p['title'] }}</td>
                            <td class="muted">{{ $p['path'] }}</td>
                            <td>{{ $p['hits'] }}</td>
                            <td><a href="{{ route('admin.stats.logs', ['from' => $from, 'to' => $to, 'visitor' => 'spider', 'path' => $p['path']]) }}">{{ admin_t('ui.detail') }}</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="muted">{{ admin_t('ui.no_crawl_rows') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card card-panel" style="margin-top:16px">
        <div class="card-header"><span><i class="fas fa-bug"></i> {{ admin_t('ui.non_200_crawls') }}</span></div>
        <div class="card-body" style="padding:0">
            <table class="data" style="border:0">
                <thead><tr><th>{{ admin_t('ui.time') }}</th><th>{{ admin_t('ui.spider') }}</th><th>{{ admin_t('ui.title_label') }}</th><th>{{ admin_t('ui.path_col') }}</th><th>{{ admin_t('ui.status') }}</th><th></th></tr></thead>
                <tbody>
                @forelse($errors as $log)
                    <tr>
                        <td>{{ $log->created_at }}</td>
                        <td>{{ $log->spider_name }}</td>
                        <td>{{ $pathTitles[$log->path] ?? $log->path }}</td>
                        <td class="muted">{{ $log->path }}</td>
                        <td><span class="badge badge-warn">{{ $log->status_code }}</span></td>
                        <td><a href="{{ route('admin.stats.logs', ['from' => $from, 'to' => $to, 'visitor' => 'spider', 'status' => 'other', 'path' => $log->path]) }}">{{ admin_t('ui.detail') }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="muted">{{ admin_t('ui.no_spider_404') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card card-panel" style="margin-top:16px">
        <div class="card-header"><span>{{ admin_t('ui.recent_crawls') }}</span></div>
        <div class="card-body" style="padding:0">
            <table class="data" style="border:0">
                <thead><tr><th>{{ admin_t('ui.time') }}</th><th>{{ admin_t('ui.spider') }}</th><th>{{ admin_t('ui.title_label') }}</th><th>{{ admin_t('ui.path_col') }}</th><th>IP</th><th>{{ admin_t('ui.status') }}</th></tr></thead>
                <tbody>
                @forelse($recent as $log)
                    <tr>
                        <td>{{ $log->created_at }}</td>
                        <td>{{ $log->spider_name }}</td>
                        <td>{{ $pathTitles[$log->path] ?? $log->path }}</td>
                        <td class="muted">{{ $log->path }}</td>
                        <td>{{ $log->ip }}</td>
                        <td>{{ $log->status_code }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="muted">{{ admin_t('ui.no_crawl_rows') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
(function () {
    const L = @json([
        'engine_search' => admin_t('ui.engine_search'),
        'engine_tool' => admin_t('ui.engine_tool'),
        'engine_ai' => admin_t('ui.engine_ai'),
        'engine_other' => admin_t('ui.engine_other'),
        'no_chart_data' => admin_t('ui.no_chart_data'),
        'times' => admin_t('ui.times'),
    ], JSON_UNESCAPED_UNICODE);
    const trend = @json($trend, JSON_UNESCAPED_UNICODE);
    const mix = @json($composition, JSON_UNESCAPED_UNICODE);
    const pages = @json($topPages->take(8)->values());
    const grid = '#e5e6eb';
    const ticks = { color: '#86909c', font: { size: 11 } };
    const legend = { labels: { boxWidth: 10, font: { size: 12 } } };
    const hasMix = mix.some(r => Number(r.hits) > 0);

    new Chart(document.getElementById('spiderTrendChart'), {
        type: 'line',
        data: {
            labels: trend.map(r => String(r.day).slice(5)),
            datasets: [
                { label: L.engine_search, data: trend.map(r => r.search), borderColor: '#5b9cff', backgroundColor: 'rgba(91,156,255,.10)', fill: true, tension: .3, pointRadius: 2 },
                { label: L.engine_tool, data: trend.map(r => r.tool), borderColor: '#fd7e14', backgroundColor: 'transparent', tension: .3, pointRadius: 2 },
                { label: L.engine_ai, data: trend.map(r => r.ai), borderColor: '#6f42c1', backgroundColor: 'transparent', tension: .3, pointRadius: 2, borderDash: [4, 3] },
                { label: L.engine_other, data: trend.map(r => r.other), borderColor: '#6c757d', backgroundColor: 'transparent', tension: .3, pointRadius: 2 }
            ]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend }, scales: { x: { ticks, grid: { color: grid } }, y: { beginAtZero: true, ticks, grid: { color: grid } } } }
    });

    new Chart(document.getElementById('spiderMixChart'), {
        type: 'doughnut',
        data: {
            labels: hasMix ? mix.map(r => r.name) : [L.no_chart_data],
            datasets: [{ data: hasMix ? mix.map(r => r.hits) : [1], backgroundColor: hasMix ? ['#5b9cff', '#fd7e14', '#6f42c1', '#6c757d'] : ['#e5e6eb'], borderWidth: 0 }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend }, cutout: '62%' }
    });

    new Chart(document.getElementById('spiderPagesChart'), {
        type: 'bar',
        data: {
            labels: pages.map(p => p.title || p.path),
            datasets: [{ label: L.times, data: pages.map(p => p.hits), backgroundColor: 'rgba(253,126,20,.75)', borderRadius: 3 }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { x: { beginAtZero: true, ticks, grid: { color: grid } }, y: { ticks, grid: { display: false } } }
        }
    });
})();
</script>
@endpush
