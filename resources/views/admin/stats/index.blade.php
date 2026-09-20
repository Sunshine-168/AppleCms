@extends('admin.layouts.inner')
@section('title', admin_t('page.stats'))
@section('plain')
    @include('admin.stats._nav', ['active' => 'traffic'])

    @php
        $jsLang = [
            'pv' => 'PV',
            'uv' => 'UV',
            'spider' => admin_t('ui.spider'),
            'human_pv' => admin_t('ui.human_pv'),
            'spider_pv' => admin_t('ui.spider_pv'),
            'no_chart_data' => admin_t('ui.no_chart_data'),
        ];
    @endphp

    <div class="stat-grid">
        <div class="stat-card">
            <div>
                <div class="label">{{ admin_t('ui.today') }} PV</div>
                <div class="value">{{ $overview['today']['pv'] }}</div>
                <div class="delta {{ $changes['pv']['dir'] }}">{{ $changes['pv']['text'] }}</div>
            </div>
            <div class="icon blue"><i class="fas fa-eye"></i></div>
        </div>
        <div class="stat-card">
            <div>
                <div class="label">{{ admin_t('ui.today') }} UV</div>
                <div class="value">{{ $overview['today']['uv'] }}</div>
                <div class="delta {{ $changes['uv']['dir'] }}">{{ $changes['uv']['text'] }}</div>
            </div>
            <div class="icon green"><i class="fas fa-user"></i></div>
        </div>
        <div class="stat-card">
            <div>
                <div class="label">{{ admin_t('ui.today') }}{{ admin_t('ui.unique_ip') }}</div>
                <div class="value">{{ $overview['today']['ip'] }}</div>
                <div class="delta {{ $changes['ip']['dir'] }}">{{ $changes['ip']['text'] }}</div>
            </div>
            <div class="icon purple"><i class="fas fa-network-wired"></i></div>
        </div>
        <div class="stat-card">
            <div>
                <div class="label">{{ admin_t('ui.spider_pv_today') }}</div>
                <div class="value">{{ $overview['today']['spider_pv'] }}</div>
                <div class="delta {{ $changes['spider_pv']['dir'] }}">{{ $changes['spider_pv']['text'] }}</div>
            </div>
            <div class="icon orange"><i class="fas fa-spider"></i></div>
        </div>
    </div>

    <div class="card card-panel" style="margin-bottom:16px">
        <div class="card-header">
            <span><i class="fas fa-balance-scale"></i> {{ admin_t('ui.period_compare') }}</span>
        </div>
        <div class="card-body" style="padding:0">
            <table class="data" style="border:0">
                <thead>
                <tr>
                    <th>{{ admin_t('ui.compare') }}</th>
                    <th>{{ admin_t('ui.this_period') }}</th>
                    <th>{{ admin_t('ui.prev_period') }}</th>
                    <th>PV</th>
                    <th>UV</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>{{ admin_t('ui.week_vs_prev') }}</td>
                    <td>{{ $weekCompare['from'] }} ~ {{ $weekCompare['to'] }}</td>
                    <td>{{ $weekCompare['prev_from'] }} ~ {{ $weekCompare['prev_to'] }}</td>
                    <td>{{ $weekCompare['current']['pv'] }} <span class="delta {{ $weekCompare['changes']['pv']['dir'] }}">{{ $weekCompare['changes']['pv']['text'] }}</span></td>
                    <td>{{ $weekCompare['current']['uv'] }} <span class="delta {{ $weekCompare['changes']['uv']['dir'] }}">{{ $weekCompare['changes']['uv']['text'] }}</span></td>
                </tr>
                <tr>
                    <td>{{ admin_t('ui.range_vs_prev') }}</td>
                    <td>{{ $rangeCompare['from'] }} ~ {{ $rangeCompare['to'] }}</td>
                    <td>{{ $rangeCompare['prev_from'] }} ~ {{ $rangeCompare['prev_to'] }}</td>
                    <td>{{ $rangeCompare['current']['pv'] }} <span class="delta {{ $rangeCompare['changes']['pv']['dir'] }}">{{ $rangeCompare['changes']['pv']['text'] }}</span></td>
                    <td>{{ $rangeCompare['current']['uv'] }} <span class="delta {{ $rangeCompare['changes']['uv']['dir'] }}">{{ $rangeCompare['changes']['uv']['text'] }}</span></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="chart-grid">
        <div class="card card-panel">
            <div class="card-header">
                <span><i class="fas fa-chart-line"></i> {{ admin_t('ui.last_14d_trend') }}</span>
            </div>
            <div class="card-body">
                <div class="chart-box">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
        </div>
        <div class="card card-panel">
            <div class="card-header">
                <span><i class="fas fa-chart-pie"></i> {{ admin_t('ui.visitor_mix_14d') }}</span>
            </div>
            <div class="card-body">
                <div class="chart-box">
                    <canvas id="mixChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="chart-grid two">
        <div class="card card-panel">
            <div class="card-header">
                <span><i class="fas fa-clock"></i> {{ admin_t('ui.hourly_7d') }}</span>
            </div>
            <div class="card-body">
                <div class="chart-box">
                    <canvas id="hourlyChart"></canvas>
                </div>
            </div>
        </div>
        <div class="card card-panel">
            <div class="card-header">
                <span><i class="fas fa-external-link-alt"></i> {{ admin_t('ui.referer_hosts') }}</span>
            </div>
            <div class="card-body">
                <div class="chart-box">
                    <canvas id="refererChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="chart-grid two">
        <div class="card card-panel">
            <div class="card-header">
                <span><i class="fas fa-mobile-alt"></i> {{ admin_t('ui.devices') }}</span>
            </div>
            <div class="card-body">
                <div class="chart-box chart-box-sm">
                    <canvas id="deviceChart"></canvas>
                </div>
            </div>
        </div>
        <div class="card card-panel">
            <div class="card-header">
                <span><i class="fas fa-globe"></i> {{ admin_t('ui.browsers') }}</span>
            </div>
            <div class="card-body">
                <div class="chart-box chart-box-sm">
                    <canvas id="browserChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-panel">
        <div class="card-header">
            <span><i class="fas fa-fire"></i> {{ admin_t('ui.hot_pages') }}</span>
        </div>
        <div class="card-body">
            <div class="chart-box chart-box-sm">
                <canvas id="pagesChart"></canvas>
            </div>
        </div>
    </div>

    <form method="get" class="filter-bar">
        <div class="field">
            <label for="stat-from">{{ admin_t('ui.date_from') }}</label>
            <input id="stat-from" type="date" name="from" value="{{ $from }}">
        </div>
        <div class="field">
            <label for="stat-to">{{ admin_t('ui.date_to') }}</label>
            <input id="stat-to" type="date" name="to" value="{{ $to }}">
        </div>
        <button class="btn" type="submit">{{ admin_t('ui.filter') }}</button>
        <a class="btn btn-muted" href="{{ route('admin.stats.export', ['from' => $from, 'to' => $to]) }}"><i class="fas fa-download"></i> {{ admin_t('ui.export_csv') }}</a>
    </form>

    <div class="chart-grid two">
        <div class="card card-panel">
            <div class="card-header"><span>{{ admin_t('ui.hot_pages_detail') }}</span></div>
            <div class="card-body" style="padding:0">
                <table class="data" style="border:0">
                    <thead><tr><th>{{ admin_t('ui.title_label') }}</th><th>{{ admin_t('ui.path_col') }}</th><th>PV</th><th></th></tr></thead>
                    <tbody>
                    @forelse($topPages as $p)
                        <tr>
                            <td>{{ $p['title'] }}</td>
                            <td class="muted">{{ $p['path'] }}</td>
                            <td>{{ $p['hits'] }}</td>
                            <td><a href="{{ route('admin.stats.logs', ['from' => $from, 'to' => $to, 'path' => $p['path']]) }}">{{ admin_t('ui.detail') }}</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="muted">{{ admin_t('ui.no_period_data') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card card-panel">
            <div class="card-header"><span>{{ admin_t('ui.flink_hits') }}</span></div>
            <div class="card-body" style="padding:0">
                <table class="data" style="border:0">
                    <thead><tr><th>Referer</th><th>{{ admin_t('ui.times') }}</th></tr></thead>
                    <tbody>
                    @forelse($topReferers as $r)
                        <tr><td style="word-break:break-all">{{ $r->referer }}</td><td>{{ $r->hits }}</td></tr>
                    @empty
                        <tr><td colspan="2" class="muted">{{ admin_t('ui.no_period_referer') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card card-panel" style="margin-top:16px">
        <div class="card-header">
            <span>{{ admin_t('ui.recent_404') }}</span>
            <a href="{{ route('admin.stats.logs', ['from' => $from, 'to' => $to, 'status' => '404']) }}">{{ admin_t('ui.all_detail') }}</a>
        </div>
        <div class="card-body" style="padding:0">
            <table class="data" style="border:0">
                <thead><tr><th>{{ admin_t('ui.title_label') }}</th><th>{{ admin_t('ui.path_col') }}</th><th>{{ admin_t('ui.times') }}</th><th></th></tr></thead>
                <tbody>
                @forelse($topErrors as $p)
                    <tr>
                        <td>{{ $p['title'] }}</td>
                        <td class="muted">{{ $p['path'] }}</td>
                        <td>{{ $p['hits'] }}</td>
                        <td><a href="{{ route('admin.stats.logs', ['from' => $from, 'to' => $to, 'status' => '404', 'path' => $p['path']]) }}">{{ admin_t('ui.detail') }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="muted">{{ admin_t('ui.no_period_404') }}</td></tr>
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
    const L = @json($jsLang, JSON_UNESCAPED_UNICODE);
    const trend = @json($trend, JSON_UNESCAPED_UNICODE);
    const hourly = @json($hourly, JSON_UNESCAPED_UNICODE);
    const pages = @json($topPages->take(8)->values());
    const referers = @json($refererHosts, JSON_UNESCAPED_UNICODE);
    const devices = @json($clients['devices']);
    const browsers = @json($clients['browsers']);
    const humanPv = trend.reduce((s, r) => s + Number(r.pv || 0), 0);
    const spiderPv = trend.reduce((s, r) => s + Number(r.spider_pv || 0), 0);
    const colors = ['#5b9cff', '#28a745', '#fd7e14', '#6f42c1', '#17a2b8', '#e83e8c', '#20c997', '#6c757d'];

    const grid = '#e5e6eb';
    const ticks = { color: '#86909c', font: { size: 11 } };
    const legend = { labels: { boxWidth: 10, font: { size: 12 } } };

    function doughnut(id, rows, emptyLabel) {
        const has = rows.some(r => Number(r.hits) > 0);
        new Chart(document.getElementById(id), {
            type: 'doughnut',
            data: {
                labels: has ? rows.map(r => r.name || r.host) : [emptyLabel],
                datasets: [{ data: has ? rows.map(r => r.hits) : [1], backgroundColor: has ? colors : ['#e5e6eb'], borderWidth: 0 }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend } }
        });
    }

    new Chart(document.getElementById('trendChart'), {
        type: 'line',
        data: {
            labels: trend.map(r => String(r.day).slice(5)),
            datasets: [
                { label: L.pv, data: trend.map(r => r.pv), borderColor: '#5b9cff', backgroundColor: 'rgba(91,156,255,.12)', fill: true, tension: .3, pointRadius: 2 },
                { label: L.uv, data: trend.map(r => r.uv), borderColor: '#28a745', backgroundColor: 'rgba(40,167,69,.08)', fill: true, tension: .3, pointRadius: 2 },
                { label: L.spider, data: trend.map(r => r.spider_pv), borderColor: '#fd7e14', backgroundColor: 'transparent', tension: .3, pointRadius: 2, borderDash: [4, 3] }
            ]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend }, scales: { x: { ticks, grid: { color: grid } }, y: { beginAtZero: true, ticks, grid: { color: grid } } } }
    });

    new Chart(document.getElementById('mixChart'), {
        type: 'doughnut',
        data: {
            labels: [L.human_pv, L.spider_pv],
            datasets: [{ data: [humanPv, spiderPv], backgroundColor: ['#5b9cff', '#fd7e14'], borderWidth: 0 }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend }, cutout: '62%' }
    });

    new Chart(document.getElementById('hourlyChart'), {
        type: 'bar',
        data: {
            labels: hourly.map(r => r.hour),
            datasets: [{ label: L.pv, data: hourly.map(r => r.pv), backgroundColor: 'rgba(64,204,146,.72)', borderRadius: 3 }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { ticks, grid: { display: false } }, y: { beginAtZero: true, ticks, grid: { color: grid } } } }
    });

    doughnut('refererChart', referers.map(r => ({ name: r.host, hits: r.hits })), L.no_chart_data);
    doughnut('deviceChart', devices, L.no_chart_data);
    doughnut('browserChart', browsers, L.no_chart_data);

    new Chart(document.getElementById('pagesChart'), {
        type: 'bar',
        data: {
            labels: pages.map(p => p.title || p.path),
            datasets: [{ label: L.pv, data: pages.map(p => p.hits), backgroundColor: 'rgba(111,66,193,.75)', borderRadius: 3 }]
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
