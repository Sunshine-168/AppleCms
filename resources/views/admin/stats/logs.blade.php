@extends('admin.layouts.inner')
@section('title', admin_t('page.stats_logs'))
@section('plain')
    @include('admin.stats._nav', ['active' => 'logs'])

    @php
        $today = now()->toDateString();
        $weekFrom = now()->subDays(6)->toDateString();
        $qs = fn (array $extra = []) => route('admin.stats.logs', array_filter(
            array_merge($filters, $extra),
            fn ($v) => $v !== '' && $v !== null
        ));
    @endphp

    <form method="get" class="filter-bar">
        @foreach(['bot', 'status', 'ip', 'hash', 'probe'] as $keep)
            @if($filters[$keep] !== '')
                <input type="hidden" name="{{ $keep }}" value="{{ $filters[$keep] }}">
            @endif
        @endforeach
        <div class="field">
            <label for="log-from">{{ admin_t('ui.date_from') }}</label>
            <input id="log-from" type="date" name="from" value="{{ $filters['from'] }}">
        </div>
        <div class="field">
            <label for="log-to">{{ admin_t('ui.date_to') }}</label>
            <input id="log-to" type="date" name="to" value="{{ $filters['to'] }}">
        </div>
        <div class="field">
            <label for="log-visitor">{{ admin_t('ui.visitor_kind') }}</label>
            <select id="log-visitor" name="visitor">
                <option value="" @selected($filters['visitor'] === '')>{{ admin_t('ui.all') }}</option>
                <option value="human" @selected($filters['visitor'] === 'human')>{{ admin_t('ui.human') }}</option>
                <option value="spider" @selected($filters['visitor'] === 'spider')>{{ admin_t('ui.spider') }}</option>
            </select>
        </div>
        <div class="field">
            <label for="log-path">{{ admin_t('ui.pages_col') }}</label>
            <input id="log-path" type="text" name="path" value="{{ $filters['path'] }}" placeholder="{{ admin_t('ui.ph_log_path') }}">
        </div>
        <button class="btn" type="submit">{{ admin_t('ui.filter') }}</button>
        <a class="chip {{ $filters['from'] === $today && $filters['to'] === $today ? 'active' : '' }}" href="{{ $qs(['from' => $today, 'to' => $today]) }}">{{ admin_t('ui.today_chip') }}</a>
        <a class="chip {{ $filters['from'] === $weekFrom && $filters['to'] === $today ? 'active' : '' }}" href="{{ $qs(['from' => $weekFrom, 'to' => $today]) }}">{{ admin_t('ui.last_7d') }}</a>
        <a class="chip {{ $filters['status'] === '404' ? 'active' : '' }}" href="{{ $qs(['status' => $filters['status'] === '404' ? '' : '404']) }}">{{ admin_t('ui.only_404') }}</a>
        <a class="chip {{ $filters['probe'] === 'hide' ? 'active' : '' }}" href="{{ $qs(['probe' => $filters['probe'] === 'hide' ? '' : 'hide']) }}">{{ admin_t('ui.hide_local') }}</a>
        <a class="chip" href="{{ route('admin.stats.logs.export', $filters) }}">{{ admin_t('ui.export') }}</a>
        @if($activeFilters > 0)
            <a class="chip" href="{{ route('admin.stats.logs') }}">{{ admin_t('ui.clear_filter') }}</a>
        @endif
    </form>

    <p class="muted" style="margin:-4px 0 12px">
        {{ admin_t('ui.n_rows', ['n' => $logs->total()]) }}
        @if($errorCount > 0)
            · {{ admin_t('ui.n_non_200', ['n' => $errorCount]) }}
        @endif
        @if($filters['ip'] !== '')
            · IP {{ $filters['ip'] }}
        @endif
        @if($filters['bot'] !== '')
            · {{ $filters['bot'] }}
        @endif
    </p>

    @if($trail->isNotEmpty())
        <div class="card card-panel" style="margin-bottom:16px">
            <div class="card-header">
                <span>{{ admin_t('ui.visitor_trail', ['n' => $trail->count()]) }}</span>
                <a class="btn btn-sm btn-muted" href="{{ $qs(['hash' => '']) }}">{{ admin_t('ui.exit_trail') }}</a>
            </div>
            <div class="card-body">
                <p class="trail">
                    @foreach($trail as $i => $step)
                        @if($i > 0)<span class="muted"> → </span>@endif
                        <span @class(['trail-err' => $step['status'] !== 200]) title="{{ $step['path'] }} · {{ $step['at'] }}">{{ $step['title'] }}</span>
                    @endforeach
                </p>
            </div>
        </div>
    @endif

    <div class="card card-panel">
        <div class="card-body" style="padding:0">
            <table class="data" style="border:0">
                <thead>
                <tr>
                    <th>{{ admin_t('ui.col_time') }}</th>
                    <th>{{ admin_t('ui.visitor_col') }}</th>
                    <th>{{ admin_t('ui.pages_col') }}</th>
                    <th>{{ admin_t('ui.referrer') }}</th>
                    <th>{{ admin_t('ui.status') }}</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @forelse($rows as $row)
                    <tr class="{{ $row['status'] !== 200 ? 'log-row-error' : '' }}">
                        <td title="{{ $row['at'] }}">{{ optional($row['at'])->format('m-d H:i') }}</td>
                        <td>
                            <span class="badge {{ $row['visitor_kind'] === 'bot' ? 'badge-tool' : 'badge-search' }}">{{ $row['visitor'] }}</span>
                            @if($row['local'])
                                <span class="badge badge-off">{{ admin_t('ui.local_chip') }}</span>
                            @endif
                            <div class="muted" title="{{ $row['ua'] }}">{{ $row['client'] }} · {{ $row['ip'] }}</div>
                        </td>
                        <td>
                            <a href="{{ $row['front_url'] }}" target="_blank" rel="noopener">{{ $row['title'] }}</a>
                            <div class="muted">{{ $row['path'] }}</div>
                        </td>
                        <td>{{ $row['referer'] }}</td>
                        <td>
                            @if($row['status'] !== 200)
                                <span class="badge badge-warn">{{ $row['status'] }}</span>
                            @else
                                200
                            @endif
                        </td>
                        <td class="log-actions">
                            @if($row['ip'])
                                <a href="{{ $qs(['ip' => $row['ip'], 'hash' => '']) }}">{{ admin_t('ui.same_ip') }}</a>
                            @endif
                            @if($row['hash'])
                                <a href="{{ $qs(['hash' => $row['hash'], 'ip' => '']) }}">{{ admin_t('ui.trail') }}</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="muted">{{ admin_t('ui.empty_access_logs') }}</td></tr>
                @endforelse
                </tbody>
            </table>
            {{ $logs->links() }}
        </div>
    </div>
@endsection
