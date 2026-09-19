@extends('admin.layouts.inner')
@section('title', admin_t('nav.dashboard'))

@section('plain')
    <div class="stat-grid dash dash-kpis">
        @foreach($kpis as $card)
            <a class="stat-card" href="{{ $card['href'] }}">
                <div>
                    <div class="label">{{ $card['label'] }}</div>
                    <div class="value{{ ($card['size'] ?? '') === 'sm' ? ' is-sm' : '' }}">{{ $card['value'] }}</div>
                    @if(! empty($card['delta']))
                        <div class="delta {{ $card['delta']['dir'] }}">{{ $card['delta']['text'] }}</div>
                    @elseif(! empty($card['hint']))
                        <div class="hint">{{ $card['hint'] }}</div>
                    @endif
                </div>
                <div class="icon {{ $card['color'] }}"><i class="fas {{ $card['icon'] }}"></i></div>
            </a>
        @endforeach
    </div>

    <div class="dash-toolbar">
        @foreach($quickNav as $item)
            <a class="btn {{ $item['primary'] ? 'btn-primary' : 'btn-muted' }}" href="{{ $item['url'] }}">
                <i class="{{ $item['icon'] }}"></i> {{ $item['title'] }}
            </a>
        @endforeach
    </div>

    <div class="dash-work">
        <div class="card card-panel" id="dash-todos">
            <div class="card-header">
                <span>{{ admin_t('dash.todos') }}</span>
                @if($todoTotal > 0)
                    <span class="badge badge-warn">{{ $todoTotal }}</span>
                @endif
            </div>
            @forelse($todos as $todo)
                <div class="todo-row">
                    <span class="badge badge-warn">{{ $todo['count'] }}</span>
                    <div class="grow">
                        <div>{{ $todo['title'] }}</div>
                        <div class="muted">{{ $todo['hint'] }}</div>
                    </div>
                    <a class="btn btn-sm" href="{{ $todo['url'] }}">{{ admin_t('dash.handle') }}</a>
                </div>
            @empty
                <div class="card-body">
                    <p class="muted" style="margin:0">{{ admin_t('dash.todos_empty') }}</p>
                </div>
            @endforelse
        </div>

        <div class="dash-side">
            <div class="card card-panel">
                <div class="card-header"><span>{{ admin_t('dash.activity') }}</span></div>
                @forelse($activity as $row)
                    <div class="todo-row">
                        <span class="badge">{{ $row['kind_label'] }}</span>
                        <div class="grow">
                            @if($row['url'])
                                <a href="{{ $row['url'] }}">{{ $row['title'] }}</a>
                            @else
                                {{ $row['title'] }}
                            @endif
                            <div class="muted">{{ $row['time'] }}</div>
                        </div>
                    </div>
                @empty
                    <div class="card-body">
                        <p class="muted" style="margin:0">{{ admin_t('dash.activity_empty') }}</p>
                    </div>
                @endforelse
            </div>

            @if(count($alerts) > 0)
                <div class="card card-panel">
                    <div class="card-header"><span>{{ admin_t('dash.alerts') }}</span></div>
                    @foreach($alerts as $alert)
                        <div class="todo-row">
                            <span class="badge {{ $alert['level'] === 'warn' ? 'badge-warn' : 'badge-off' }}">{{ $alert['level'] === 'warn' ? admin_t('dash.warn') : admin_t('dash.info') }}</span>
                            <div class="grow">
                                @if($alert['url'])
                                    <a href="{{ $alert['url'] }}">{{ $alert['title'] }}</a>
                                @else
                                    {{ $alert['title'] }}
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    @php
        $sysHost = (string) ($server['host'] ?? '');
        $sysPort = (string) ($server['port'] ?? '');
        $sysAddr = $sysHost;
        if ($sysPort !== '' && ! in_array($sysPort, ['80', '443'], true) && $sysHost !== '' && ! str_contains($sysHost, ':')) {
            $sysAddr .= ':'.$sysPort;
        }
        $regMax = max(1, (int) ($reg['max'] ?? 1));
        $regTotal = (int) ($reg['total'] ?? 0);
    @endphp
    <div class="dash-sys">
        <div class="card card-panel">
            <div class="card-header" title="{{ admin_t('dash.sysinfo_hint') }}">
                <span>{{ admin_t('dash.sysinfo') }}</span>
            </div>
            <div class="card-body">
                <div class="dash-sys-meters">
                    <div class="dash-sys-meter">
                        <div class="label">{{ admin_t('dash.php_mem') }}</div>
                        <div class="value">{{ $server['php_memory']['used_text'] }}</div>
                        <div class="muted">{{ $server['php_memory']['limit_text'] }}</div>
                    </div>
                    @if($server['disk']['ok'])
                        <div class="dash-sys-meter is-wide" title="{{ $server['disk']['path'] }}">
                            <div class="label">
                                {{ admin_t('dash.disk') }}
                                <span>{{ number_format($server['disk']['percent'], 1) }}%</span>
                            </div>
                            <div class="dash-disk-bar is-{{ $server['disk']['tone'] }}"><i style="width: {{ min(100, $server['disk']['percent']) }}%"></i></div>
                            <div class="muted">{{ admin_t('dash.disk_short', ['used' => $server['disk']['used_text'], 'total' => $server['disk']['total_text']]) }}</div>
                        </div>
                    @else
                        <div class="dash-sys-meter is-wide">
                            <div class="label">{{ admin_t('dash.disk') }}</div>
                            <p class="muted" style="margin:8px 0 0">{{ admin_t('dash.disk_fail') }}</p>
                        </div>
                    @endif
                    @if($server['ram']['ok'])
                        <div class="dash-sys-meter">
                            <div class="label">{{ admin_t('dash.ram') }}</div>
                            <div class="value">{{ number_format($server['ram']['percent'], 1) }}%</div>
                            <div class="muted">{{ $server['ram']['used_text'] }} / {{ $server['ram']['total_text'] }}</div>
                        </div>
                    @endif
                    @if($server['load']['ok'])
                        <div class="dash-sys-meter">
                            <div class="label">{{ admin_t('dash.load') }}</div>
                            <div class="value">{{ $server['load']['text'] }}</div>
                        </div>
                    @endif
                </div>
                <table class="data info" style="border:0">
                    <tr>
                        <th>{{ admin_t('dash.php') }}</th>
                        <td>{{ $server['php'] }}</td>
                        <th>{{ admin_t('dash.laravel') }}</th>
                        <td>{{ $server['laravel'] }}</td>
                    </tr>
                    <tr>
                        <th>{{ admin_t('dash.app_ver') }}</th>
                        <td>{{ $server['app_name'] }} {{ $server['app_version'] }}</td>
                        <th>{{ admin_t('dash.os') }}</th>
                        <td>{{ $server['os'] }}</td>
                    </tr>
                    <tr>
                        <th>{{ admin_t('dash.host') }}</th>
                        <td>{{ $sysAddr !== '' ? $sysAddr : admin_t('dash.unavailable') }}</td>
                        <th>{{ admin_t('dash.upload') }}</th>
                        <td>{{ $server['upload'] }} · POST {{ $server['post'] }}</td>
                    </tr>
                    <tr>
                        <th>{{ admin_t('dash.now') }}</th>
                        <td colspan="3">{{ $server['now'] }} <span class="muted">{{ $server['timezone'] }}</span></td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="card card-panel dash-week">
            <div class="card-header">
                <span>{{ admin_t('dash.week') }}</span>
                <a class="muted" href="{{ route('admin.stats.index') }}">{{ admin_t('dash.see_stats') }}</a>
            </div>
            <div class="card-body">
                <div class="dash-week-block">
                    <div class="dash-week-k">{{ admin_t('dash.spark') }}</div>
                    @if(($spark['pv'] ?? '') !== '')
                        <a class="dash-spark-link" href="{{ route('admin.stats.index') }}">
                            <svg class="spark" viewBox="0 0 {{ $spark['width'] }} {{ $spark['height'] }}" preserveAspectRatio="none">
                                <polyline points="{{ $spark['pv'] }}" fill="none" stroke="#007bff" stroke-width="2"/>
                                <polyline points="{{ $spark['uv'] }}" fill="none" stroke="#28a745" stroke-width="2"/>
                            </svg>
                        </a>
                        <p class="muted spark-legend"><span class="dot blue"></span> PV <span class="dot green"></span> UV</p>
                    @else
                        <p class="muted dash-week-empty">{{ admin_t('dash.spark_empty') }}</p>
                    @endif
                </div>
                <div class="dash-week-block">
                    <div class="dash-week-k">
                        {{ admin_t('dash.reg7') }}
                        <span>{{ $regTotal }}</span>
                    </div>
                    <div class="dash-reg-bars">
                        @foreach(($reg['days'] ?? []) as $i => $day)
                            @php $n = (int) ($reg['counts'][$i] ?? 0); @endphp
                            <div class="dash-reg-col{{ $n === 0 ? ' is-empty' : '' }}" title="{{ $day }}：{{ $n }}">
                                <i style="height: {{ $n > 0 ? max(10, (int) round(100 * $n / $regMax)) : 4 }}%"></i>
                                <em>{{ $n }}</em>
                                <span>{{ $day }}</span>
                            </div>
                        @endforeach
                    </div>
                    @if($regTotal === 0)
                        <p class="muted dash-week-empty">{{ admin_t('dash.reg7_empty') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
