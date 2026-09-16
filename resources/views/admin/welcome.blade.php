@extends('admin.layouts.inner')
@section('title', admin_t('nav.dashboard'))

@section('plain')
    <div class="dash-hero">
        <div class="stat-grid dash dash-kpis">
            @foreach($kpis as $card)
                <a class="stat-card" href="{{ $card['href'] }}">
                    <div>
                        <div class="label">{{ $card['label'] }}</div>
                        <div class="value">{{ $card['value'] }}</div>
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
        <a class="card card-panel dash-spark" href="{{ route('admin.stats.index') }}">
            <div class="card-header"><span>{{ admin_t('dash.spark') }}</span></div>
            <div class="card-body">
                @if(($spark['pv'] ?? '') !== '')
                    <svg class="spark" viewBox="0 0 {{ $spark['width'] }} {{ $spark['height'] }}" preserveAspectRatio="none">
                        <polyline points="{{ $spark['pv'] }}" fill="none" stroke="#40cc92" stroke-width="2"/>
                        <polyline points="{{ $spark['uv'] }}" fill="none" stroke="#28a745" stroke-width="2"/>
                    </svg>
                    <p class="muted spark-legend"><span class="dot blue"></span> {{ admin_t('dash.spark_legend') }}</p>
                @else
                    <p class="muted" style="margin:0">{{ admin_t('dash.spark_empty') }}</p>
                @endif
            </div>
        </a>
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

            <div class="card card-panel">
                <div class="card-header"><span>{{ admin_t('dash.alerts') }}</span></div>
                @forelse($alerts as $alert)
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
                @empty
                    <div class="card-body">
                        <p class="muted" style="margin:0">{{ admin_t('dash.alerts_empty') }}</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="card card-panel">
        <div class="card-header"><span>{{ admin_t('dash.next') }}</span></div>
        <div class="card-body">
            <div class="dash-actions">
                @foreach($quickNav as $item)
                    <a class="btn {{ $item['primary'] ? 'btn-primary' : 'btn-muted' }}" href="{{ $item['url'] }}">
                        <i class="{{ $item['icon'] }}"></i> {{ $item['title'] }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    <details class="dash-health" @if($health['attention']) open @endif>
        <summary>
            <span>{{ admin_t('dash.health') }}</span>
            <span class="muted" style="font-weight:400">
                {{ admin_t('dash.library_n', ['n' => $health['vod_total']]) }}
                · {{ $health['closed'] ? admin_t('dash.closed') : admin_t('dash.open') }}
                · {{ admin_t('dash.cache') }} {{ $health['cache'] ? admin_t('dash.on') : admin_t('dash.off') }}
                @if($health['last_collect'])
                    · {{ admin_t('dash.last_collect') }} {{ $health['last_collect'] }}
                @endif
            </span>
            @if($health['attention'])
                <span class="badge badge-warn">{{ admin_t('dash.need_look') }}</span>
            @else
                <span class="badge badge-ok">{{ admin_t('dash.normal') }}</span>
            @endif
        </summary>
        <table class="data info" style="border:0">
            <tr>
                <th>{{ admin_t('dash.theme') }}</th>
                <td>{{ $health['theme'] }}</td>
                <th>{{ admin_t('dash.html_cache') }}</th>
                <td>
                    @if($health['cache'])
                        <span class="badge badge-ok">{{ admin_t('dash.enabled') }}</span>
                    @else
                        <span class="badge badge-off">{{ admin_t('dash.disabled') }}</span>
                    @endif
                </td>
            </tr>
            <tr>
                <th>{{ admin_t('dash.front') }}</th>
                <td>
                    @if($health['closed'])
                        <span class="badge badge-warn">{{ admin_t('dash.front_closed') }}</span>
                    @else
                        <span class="badge badge-ok">{{ admin_t('dash.front_ok') }}</span>
                    @endif
                </td>
                <th>{{ admin_t('dash.last_collect') }}</th>
                <td>
                    @if($health['last_collect'])
                        {{ $health['last_collect'] }}
                        @if($health['last_ok'])
                            <span class="badge badge-ok">{{ admin_t('dash.ok') }}</span>
                        @else
                            <span class="badge badge-warn">{{ admin_t('dash.fail') }}</span>
                        @endif
                    @else
                        <span class="muted">{{ admin_t('dash.no_collect') }}</span>
                    @endif
                </td>
            </tr>
        </table>
    </details>
@endsection
