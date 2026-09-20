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
                <div class="label">今日蜘蛛 PV</div>
                <div class="value">{{ $kpis['pv'] }}</div>
                <div class="delta {{ $kpis['change']['dir'] }}">{{ $kpis['change']['text'] }}</div>
            </div>
            <div class="icon orange"><i class="fas fa-spider"></i></div>
        </div>
        <div class="stat-card">
            <div>
                <div class="label">搜索引擎今日到访</div>
                <div class="value">{{ $visited }}/3</div>
                <div class="delta {{ $risks->isEmpty() ? 'up' : 'down' }}">
                    @if($risks->isEmpty())
                        百度 / Google / Bing 均已到或近期到过
                    @else
                        {{ $risks->map(fn ($r) => $r['label'].($r['status'] === 'never' ? '未见' : '缺席'.($r['absent_days'] ?? '').'日'))->implode(' · ') }}
                    @endif
                </div>
            </div>
            <div class="icon blue"><i class="fas fa-search"></i></div>
        </div>
        <div class="stat-card">
            <div>
                <div class="label">异常抓取</div>
                <div class="value">{{ $kpis['errors'] }}</div>
                <div class="delta {{ $kpis['errors'] > 0 || $kpis['tool_ai_share'] >= 40 ? 'down' : 'flat' }}">
                    非 200 · 工具+AI {{ $kpis['tool_ai_share'] }}%
                </div>
            </div>
            <div class="icon purple"><i class="fas fa-exclamation-triangle"></i></div>
        </div>
        <div class="stat-card">
            <div>
                <div class="label">本期覆盖 URL</div>
                <div class="value">{{ $kpis['urls'] }}</div>
                <div class="delta {{ $kpis['index_hit'] ? 'up' : 'flat' }}">
                    {{ $kpis['index_hit'] ? '已碰到 sitemap / RSS' : '未见 sitemap / RSS' }}
                </div>
            </div>
            <div class="icon green"><i class="fas fa-link"></i></div>
        </div>
    </div>

    @if($risks->isNotEmpty())
        <p class="flash warn-line">连续缺席：{{ $risks->map(fn ($r) => $r['label'])->implode('、') }}。搜索引擎超过 3 天未到，收录可能受影响。</p>
    @endif

    <div class="chart-grid">
        <div class="card card-panel">
            <div class="card-header">
                <span><i class="fas fa-chart-line"></i> 近 14 日抓取趋势</span>
            </div>
            <div class="card-body">
                <div class="chart-box">
                    <canvas id="spiderTrendChart"></canvas>
                </div>
            </div>
        </div>
        <div class="card card-panel">
            <div class="card-header">
                <span><i class="fas fa-chart-pie"></i> 爬虫构成</span>
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
            <span><i class="fas fa-fire"></i> 被爬最多的页</span>
        </div>
        <div class="card-body">
            <div class="chart-box chart-box-sm">
                <canvas id="spiderPagesChart"></canvas>
            </div>
        </div>
    </div>

    <form method="get" class="filter-bar">
        <div class="field">
            <label for="spider-from">从</label>
            <input id="spider-from" type="date" name="from" value="{{ $from }}">
        </div>
        <div class="field">
            <label for="spider-to">到</label>
            <input id="spider-to" type="date" name="to" value="{{ $to }}">
        </div>
        <div class="field">
            <label for="spider-bot">蜘蛛</label>
            <select id="spider-bot" name="bot">
                <option value="">{{ admin_t('ui.all') }}</option>
                @foreach($spiderNames as $name)
                    <option value="{{ $name }}" @selected($bot === $name)>{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div class="field">
            <label for="spider-status">状态</label>
            <select id="spider-status" name="status">
                <option value="">{{ admin_t('ui.all') }}</option>
                <option value="200" @selected($status === 200)>200</option>
                <option value="404" @selected($status === 404)>404</option>
            </select>
        </div>
        <button class="btn" type="submit">筛选</button>
        <a class="btn btn-muted" href="{{ route('admin.stats.spiders.export', ['from' => $from, 'to' => $to]) }}"><i class="fas fa-download"></i> 导出 CSV</a>
    </form>

    <div class="chart-grid two">
        <div class="card card-panel">
            <div class="card-header"><span>蜘蛛汇总</span></div>
            <div class="card-body" style="padding:0">
                <table class="data" style="border:0">
                    <thead><tr><th>蜘蛛</th><th>分类</th><th>次数</th><th>独立 IP</th><th>占比</th></tr></thead>
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
                        <tr><td colspan="5" class="muted">该时段暂无蜘蛛访问</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card card-panel">
            <div class="card-header"><span>热门被爬页</span></div>
            <div class="card-body" style="padding:0">
                <table class="data" style="border:0">
                    <thead><tr><th>标题</th><th>路径</th><th>次数</th><th></th></tr></thead>
                    <tbody>
                    @forelse($topPages as $p)
                        <tr>
                            <td>{{ $p['title'] }}</td>
                            <td class="muted">{{ $p['path'] }}</td>
                            <td>{{ $p['hits'] }}</td>
                            <td><a href="{{ route('admin.stats.logs', ['from' => $from, 'to' => $to, 'visitor' => 'spider', 'path' => $p['path']]) }}">明细</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="muted">该时段没有抓取记录</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card card-panel" style="margin-top:16px">
        <div class="card-header"><span><i class="fas fa-bug"></i> 非 200 抓取</span></div>
        <div class="card-body" style="padding:0">
            <table class="data" style="border:0">
                <thead><tr><th>时间</th><th>蜘蛛</th><th>标题</th><th>路径</th><th>状态</th><th></th></tr></thead>
                <tbody>
                @forelse($errors as $log)
                    <tr>
                        <td>{{ $log->created_at }}</td>
                        <td>{{ $log->spider_name }}</td>
                        <td>{{ $pathTitles[$log->path] ?? $log->path }}</td>
                        <td class="muted">{{ $log->path }}</td>
                        <td><span class="badge badge-warn">{{ $log->status_code }}</span></td>
                        <td><a href="{{ route('admin.stats.logs', ['from' => $from, 'to' => $to, 'visitor' => 'spider', 'status' => 'other', 'path' => $log->path]) }}">明细</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="muted">该时段没有蜘蛛 404 / 非 200 记录</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card card-panel" style="margin-top:16px">
        <div class="card-header"><span>最近抓取</span></div>
        <div class="card-body" style="padding:0">
            <table class="data" style="border:0">
                <thead><tr><th>时间</th><th>蜘蛛</th><th>标题</th><th>路径</th><th>IP</th><th>状态</th></tr></thead>
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
                    <tr><td colspan="6" class="muted">该时段没有抓取记录</td></tr>
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
    const trend = @json($trend);
    const mix = @json($composition);
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
                { label: '搜索引擎', data: trend.map(r => r.search), borderColor: '#5b9cff', backgroundColor: 'rgba(91,156,255,.10)', fill: true, tension: .3, pointRadius: 2 },
                { label: 'SEO 工具', data: trend.map(r => r.tool), borderColor: '#fd7e14', backgroundColor: 'transparent', tension: .3, pointRadius: 2 },
                { label: 'AI 爬虫', data: trend.map(r => r.ai), borderColor: '#6f42c1', backgroundColor: 'transparent', tension: .3, pointRadius: 2, borderDash: [4, 3] },
                { label: '其他', data: trend.map(r => r.other), borderColor: '#6c757d', backgroundColor: 'transparent', tension: .3, pointRadius: 2 }
            ]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend }, scales: { x: { ticks, grid: { color: grid } }, y: { beginAtZero: true, ticks, grid: { color: grid } } } }
    });

    new Chart(document.getElementById('spiderMixChart'), {
        type: 'doughnut',
        data: {
            labels: hasMix ? mix.map(r => r.name) : ['暂无数据'],
            datasets: [{ data: hasMix ? mix.map(r => r.hits) : [1], backgroundColor: hasMix ? ['#5b9cff', '#fd7e14', '#6f42c1', '#6c757d'] : ['#e5e6eb'], borderWidth: 0 }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend }, cutout: '62%' }
    });

    new Chart(document.getElementById('spiderPagesChart'), {
        type: 'bar',
        data: {
            labels: pages.map(p => p.title || p.path),
            datasets: [{ label: '次数', data: pages.map(p => p.hits), backgroundColor: 'rgba(253,126,20,.75)', borderRadius: 3 }]
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
