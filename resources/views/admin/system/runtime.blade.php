@extends('admin.layouts.inner')
@section('title', admin_t('nav.runtime'))

@php
    $desk = in_array((string) ($desk ?? ''), ['perf', 'live', 'settings', 'rules', 'events', 'access'], true)
        ? (string) $desk
        : 'perf';
    $opts = $opts ?? [];
    $rules = $rules ?? [];
    $events = $events ?? [];
    $access = $access ?? [];
    $firing_count = (int) ($firing_count ?? 0);
    $event_status = (int) ($event_status ?? 0);
    $access_high = (int) ($access_high ?? 0);
    $access_cc = (int) ($access_cc ?? 120);
    $access_only = (bool) ($access_only ?? true);
    $access_view = in_array((string) ($access_view ?? ''), ['high', 'all', 'logs'], true)
        ? (string) $access_view
        : ($access_only ? 'high' : 'all');
    $access_ip = trim((string) ($access_ip ?? ''));
    $accesslog_queues = $accesslog_queues ?? ['all' => 0, 'today' => 0, 'people' => 0, 'bot' => 0];
    $runtimeJsLang = [
        'copied' => admin_t('ui.copied'),
        'copy_cmd' => admin_t('ui.copy_cmd'),
        'empty_chart' => admin_t('ui.empty_chart'),
        'gran_hour' => admin_t('ui.gran_hour'),
        'gran_min' => admin_t('ui.gran_min'),
        'reading_samples' => admin_t('ui.reading_samples'),
        'updated_at_clock' => admin_t('ui.updated_at_clock'),
        'pause' => admin_t('ui.pause'),
        'resume' => admin_t('ui.resume'),
        'saved' => admin_t('ui.saved'),
        'changed_ok' => admin_t('ui.changed_ok'),
        'edit_threshold' => admin_t('ui.edit_threshold'),
        'edit_threshold_named' => admin_t('ui.edit_threshold_named'),
        'please_fill_threshold' => admin_t('ui.please_fill_threshold'),
        'no_result' => admin_t('ui.no_result'),
        'ack_ok' => admin_t('ui.ack_ok'),
    ];
@endphp

@section('plain')
<div class="card card-panel runtime-board" id="runtime-board">
    <div class="card-header">
        <span>{{ admin_t('nav.runtime') }}@if($desk === 'access' && $access_view === 'logs') <em id="accesslog-count"></em>@endif</span>
        @if($desk === 'access' || $firing_count > 0)
            <div class="runtime-header-actions">
                @if($desk === 'access')
                    <div class="runtime-access-links">
                        <a class="btn btn-muted btn-sm" href="/admin/video/botlogs">{{ admin_t('ui.botlogs') }}</a>
                        <a class="btn btn-muted btn-sm" href="/admin/stats/logs">{{ admin_t('ui.visit_detail') }}</a>
                        <a class="btn btn-muted btn-sm" href="/admin/stats/spiders">{{ admin_t('ui.spider_stats') }}</a>
                        <a class="btn btn-muted btn-sm" href="/admin/video/config/ip">{{ admin_t('nav.config_ip') }}</a>
                    </div>
                @endif
                @if($firing_count > 0)
                    <a class="runtime-fire-count" href="/admin/system/runtime?desk=events&status=1">{{ admin_t('ui.firing_n', ['n' => $firing_count]) }}</a>
                @endif
            </div>
        @endif
    </div>
    <div class="card-body">
        <div class="queue-chips" id="runtime-desks">
            <a class="chip{{ $desk === 'perf' ? ' active' : '' }}" href="/admin/system/runtime">{{ admin_t('nav.runtime_perf') }}</a>
            <a class="chip{{ $desk === 'live' ? ' active' : '' }}" href="/admin/system/runtime?desk=live">{{ admin_t('nav.runtime_live') }}</a>
            <a class="chip{{ $desk === 'settings' ? ' active' : '' }}" href="/admin/system/runtime?desk=settings">{{ admin_t('nav.runtime_settings') }}</a>
            <a class="chip{{ $desk === 'rules' ? ' active' : '' }}" href="/admin/system/runtime?desk=rules">{{ admin_t('nav.runtime_rules') }}</a>
            <a class="chip{{ $desk === 'events' ? ' active' : '' }}" href="/admin/system/runtime?desk=events">{{ admin_t('nav.runtime_events') }}</a>
            <a class="chip{{ $desk === 'access' ? ' active' : '' }}" href="/admin/system/runtime?desk=access">{{ admin_t('nav.runtime_access') }}</a>
        </div>

        @if(!empty($heartbeat_dead) && in_array($desk, ['perf', 'live', 'settings'], true))
            <div class="runtime-dead">
                <p>{{ admin_t('ui.cron_dead_before') }}<a href="/admin/system/tools/schedule">{{ admin_t('page.schedule') }}</a>{{ admin_t('ui.cron_dead_after') }}<code>schedule:run</code>{{ admin_t('ui.cron_dead_end') }}</p>
                <div class="runtime-cron">
                    <code class="js-runtime-cron" data-cron="{{ $cron_line ?? '' }}">{{ $cron_line ?? '' }}</code>
                    <button type="button" class="btn btn-muted js-copy-cron">{{ admin_t('ui.copy_cmd') }}</button>
                </div>
            </div>
        @endif

        @if($desk === 'perf')
            <p class="muted recycle-lead">{{ admin_t('ui.runtime_same_cron') }}</p>
            <div class="runtime-range-row">
                <div class="queue-chips runtime-range-chips" id="runtime-ranges">
                    <button type="button" class="chip active" data-range="1h">{{ admin_t('ui.range_1h') }}</button>
                    <button type="button" class="chip" data-range="6h">{{ admin_t('ui.range_6h') }}</button>
                    <button type="button" class="chip" data-range="24h">{{ admin_t('ui.range_24h') }}</button>
                    <button type="button" class="chip" data-range="72h">{{ admin_t('ui.range_72h') }}</button>
                    <button type="button" class="chip" data-range="7d">{{ admin_t('ui.range_7d') }}</button>
                    <button type="button" class="chip" data-range="30d">{{ admin_t('ui.range_30d') }}</button>
                </div>
                <p class="muted field-hint" id="runtime-granularity"></p>
            </div>
            @if(!empty($snapshot))
            <div class="runtime-snap">
              @foreach($snapshot as $item)
                <article @class(['is-dead' => ($item['k'] ?? '') === 'heartbeat' && !empty($heartbeat_dead)])><span>{{ $item['label'] ?? $item['k'] }}</span><strong>{{ $item['v'] }}</strong></article>
              @endforeach
            </div>
            @endif
            <div id="runtime-charts" class="runtime-charts"><div class="list-empty"><p>{{ admin_t('ui.reading_samples') }}</p></div></div>
        @elseif($desk === 'live')
            <div class="runtime-live-head">
                <div>
                    <p class="muted field-hint">{{ admin_t('ui.last_5min_hint') }}</p>
                    <p class="muted field-hint" id="live-stamp"></p>
                </div>
                <button type="button" class="btn btn-muted btn-sm" id="runtime-pause">{{ admin_t('ui.pause') }}</button>
            </div>
            <div class="runtime-kpis" id="runtime-kpis">
                <article class="is-empty"><strong id="live-qps">—</strong><span>QPS</span></article>
                <article class="is-empty"><strong id="live-req">—</strong><span>{{ admin_t('ui.req_5min') }}</span></article>
                <article class="is-empty"><strong id="live-err">—</strong><span>{{ admin_t('ui.err_5min') }}</span></article>
                <article class="is-empty"><strong id="live-slow">—</strong><span>{{ admin_t('ui.slow_5min') }}</span></article>
                <article class="is-empty"><strong id="live-load">—</strong><span>{{ admin_t('ui.load_label') }}</span></article>
                <article class="is-empty"><strong id="live-mem">—</strong><span>{{ admin_t('ui.mem_pct') }}</span></article>
                <article class="is-empty"><strong id="live-disk">—</strong><span>{{ admin_t('ui.disk_pct') }}</span></article>
                <article class="is-empty"><strong id="live-php">—</strong><span>{{ admin_t('ui.php_mem_pct') }}</span></article>
            </div>
        @elseif($desk === 'settings')
            <form id="runtime-settings" class="runtime-form">
                @csrf
                <div class="runtime-form-enable">
                    <label><input type="checkbox" name="monitor_enabled" value="1" @checked((int)($opts['monitor_enabled'] ?? 1) === 1)> {{ admin_t('ui.enable_collect') }}</label>
                </div>
                <div class="runtime-form-grid">
                    <div>
                        <label>{{ admin_t('ui.slow_threshold_ms') }}</label>
                        <input type="number" name="monitor_slow_ms" min="100" max="60000" value="{{ (int) ($opts['monitor_slow_ms'] ?? 1000) }}">
                        <p class="muted field-hint">{{ admin_t('ui.slow_threshold_hint') }}</p>
                    </div>
                    <div>
                        <label>{{ admin_t('ui.keep_min_days') }}</label>
                        <input type="number" name="monitor_retain_min_days" min="1" max="14" value="{{ (int) ($opts['monitor_retain_min_days'] ?? 3) }}">
                    </div>
                    <div>
                        <label>{{ admin_t('ui.keep_hour_days') }}</label>
                        <input type="number" name="monitor_retain_hour_days" min="7" max="730" value="{{ (int) ($opts['monitor_retain_hour_days'] ?? 90) }}">
                    </div>
                    <div>
                        <label>{{ admin_t('ui.unusual_hit_threshold') }}</label>
                        <input type="number" name="monitor_access_cc" min="10" max="10000" value="{{ (int) ($opts['monitor_access_cc'] ?? 120) }}">
                        <p class="muted field-hint">{{ admin_t('ui.unusual_hit_hint') }}</p>
                    </div>
                </div>
                <p class="muted field-hint">{{ admin_t('ui.last_heartbeat', ['t' => $heartbeat_text ?? admin_t('ui.never_ran')]) }}</p>
                <div class="runtime-cron">
                    <code class="js-runtime-cron" data-cron="{{ $cron_line ?? '' }}">{{ $cron_line ?? '' }}</code>
                    <div class="runtime-cron-actions">
                        <button type="button" class="btn btn-muted js-copy-cron">{{ admin_t('ui.copy_cmd') }}</button>
                        <button type="submit" class="btn">{{ admin_t('ui.save') }}</button>
                    </div>
                </div>
            </form>
        @elseif($desk === 'rules')
            <p class="muted field-hint">{{ admin_t('ui.rules_default_off') }}</p>
            <div class="ui-table-wrap">
                <table class="data">
                    <thead>
                        <tr>
                            <th>{{ admin_t('ui.name') }}</th>
                            <th>{{ admin_t('ui.metric') }}</th>
                            <th>{{ admin_t('ui.condition') }}</th>
                            <th>{{ admin_t('ui.status') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rules as $row)
                            @php
                                $ruleCond = ($row['agg_text'] ?? '').' '.admin_t('ui.n_minutes', ['n' => $row['window_min'] ?? 0]).' '.($row['op_text'] ?? '').' '.($row['threshold'] ?? '');
                            @endphp
                            <tr data-id="{{ $row['id'] }}">
                                <td>
                                    {{ $row['name'] }}
                                    @if(trim((string) ($row['hint'] ?? '')) !== '')
                                        <p class="muted field-hint">{{ $row['hint'] }}</p>
                                    @endif
                                </td>
                                <td>
                                    {{ $row['metric_label'] ?? $row['metric_key'] }}
                                    <code hidden>{{ $row['metric_key'] }}</code>
                                </td>
                                <td>{{ $ruleCond }}</td>
                                <td><span class="runtime-badge {{ (int) $row['status'] === 1 ? 'is-on' : 'is-off' }}">{{ $row['status_text'] }}</span></td>
                                <td class="runtime-actions">
                                    <button type="button" class="btn btn-muted btn-sm js-rule-on" data-on="{{ (int) $row['status'] === 1 ? 0 : 1 }}">{{ (int) $row['status'] === 1 ? admin_t('ui.disabled') : admin_t('ui.enabled') }}</button>
                                    <button type="button" class="btn btn-muted btn-sm js-rule-edit" data-threshold="{{ $row['threshold'] }}" data-name="{{ $row['name'] }}" data-metric="{{ $row['metric_key'] }}" data-cond="{{ $ruleCond }}">{{ admin_t('ui.edit_threshold') }}</button>
                                    <button type="button" class="btn btn-muted btn-sm js-rule-test">{{ admin_t('ui.try_run') }}</button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><div class="list-empty"><p>{{ admin_t('ui.empty_rules') }}</p></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <template id="runtime-rule-tpl">
                <form class="admin-form">
                    <label>{{ admin_t('ui.threshold') }}</label>
                    <input type="number" name="threshold" step="any">
                </form>
            </template>
        @elseif($desk === 'events')
            <p class="muted field-hint">{{ admin_t('ui.events_local_only') }}</p>
            <div class="queue-chips">
                <a class="chip{{ $event_status === 0 ? ' active' : '' }}" href="/admin/system/runtime?desk=events">{{ admin_t('ui.all') }}</a>
                <a class="chip{{ $event_status === 1 ? ' active' : '' }}" href="/admin/system/runtime?desk=events&status=1">{{ admin_t('ui.firing') }}</a>
                <a class="chip{{ $event_status === 2 ? ' active' : '' }}" href="/admin/system/runtime?desk=events&status=2">{{ admin_t('ui.recovered') }}</a>
                <a class="chip{{ $event_status === 3 ? ' active' : '' }}" href="/admin/system/runtime?desk=events&status=3">{{ admin_t('ui.acknowledged') }}</a>
            </div>
            <div class="ui-table-wrap">
                <table class="data">
                    <thead>
                        <tr>
                            <th>{{ admin_t('ui.rule') }}</th>
                            <th>{{ admin_t('ui.status') }}</th>
                            <th>{{ admin_t('ui.note_col') }}</th>
                            <th>{{ admin_t('ui.time') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($events as $row)
                            @php
                                $st = (int) ($row['status'] ?? 0);
                                $badge = $st === 2 ? 'is-ok' : ($st === 3 ? 'is-off' : 'is-fire');
                            @endphp
                            <tr data-id="{{ $row['id'] }}">
                                <td>{{ $row['rule_name'] }}</td>
                                <td><span class="runtime-badge {{ $badge }}">{{ $row['status_text'] }}</span></td>
                                <td>{{ $row['message'] }}</td>
                                <td>{{ $row['opened_text'] }}</td>
                                <td>
                                    @if($st === 1)
                                        <button type="button" class="btn btn-muted btn-sm js-event-ack">{{ admin_t('ui.confirm') }}</button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><div class="list-empty"><p>{{ in_array($event_status, [1, 2, 3], true) ? admin_t('ui.empty_events_status') : admin_t('ui.empty_events') }}</p></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @else
            <p class="muted recycle-lead">{{ admin_t('ui.runtime_access_lead_before') }}<a href="/admin/video/config/ip">{{ admin_t('nav.config_ip') }}</a>{{ admin_t('ui.runtime_access_lead_after') }}@if($access_view !== 'logs'){{ admin_t('ui.runtime_access_lead_ip') }}@endif</p>
            <div class="queue-chips runtime-access-chips">
                <a class="chip{{ $access_view === 'high' ? ' active' : '' }}" href="/admin/system/runtime?desk=access">{{ admin_t('ui.high_count') }}@if($access_high > 0)<em>{{ $access_high }}</em>@endif</a>
                <a class="chip{{ $access_view === 'all' ? ' active' : '' }}" href="/admin/system/runtime?desk=access&only=all">{{ admin_t('ui.all') }}</a>
                <a class="chip{{ $access_view === 'logs' ? ' active' : '' }}" href="/admin/system/runtime?desk=access&view=logs">{{ admin_t('ui.trail_log') }}@if(($accesslog_queues['all'] ?? 0) > 0)<em>{{ (int) $accesslog_queues['all'] }}</em>@endif</a>
            </div>
            @if($access_view === 'logs')
                <div class="accesslog-index runtime-accesslog">
                    @include('admin.video.partials.accesslog_board', [
                        'queues' => $accesslog_queues,
                        'access_ip' => $access_ip,
                        'show_header_links' => false,
                        'compact' => true,
                    ])
                </div>
            @else
                <p class="muted field-hint">{{ admin_t('ui.last_24h_cc', ['n' => $access_cc]) }}</p>
                <div class="ui-table-wrap">
                    <table class="data">
                        <thead>
                            <tr>
                                <th>IP</th>
                                <th>{{ admin_t('ui.times_col') }}</th>
                                <th>4xx</th>
                                <th>5xx</th>
                                <th>{{ admin_t('ui.flag_col') }}</th>
                                <th>{{ admin_t('ui.path_col') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($access as $row)
                                <tr class="{{ !empty($row['high']) ? 'runtime-row-high' : '' }}">
                                    <td>
                                        <a class="log-ip" href="/admin/system/runtime?desk=access&view=logs&ip={{ urlencode((string) $row['ip']) }}" title="{{ admin_t('ui.view_ip_trail') }}">{{ $row['ip'] }}</a>
                                    </td>
                                    <td>{{ $row['hits'] }}</td>
                                    <td>{{ $row['e4'] }}</td>
                                    <td>{{ $row['e5'] }}</td>
                                    <td>
                                        @if((int) ($row['scan'] ?? 0) > 0)
                                            <span class="runtime-badge is-fire">{{ $row['flag'] }}</span>
                                        @elseif(!empty($row['high']))
                                            <span class="runtime-badge is-off">{{ $row['flag'] }}</span>
                                        @else
                                            {{ $row['flag'] ?? '' }}
                                        @endif
                                    </td>
                                    <td class="runtime-path">{{ \Illuminate\Support\Str::limit((string) $row['path'], 60) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6"><div class="list-empty"><p>{{ $access_only ? admin_t('ui.empty_high_ips') : admin_t('ui.empty_access_ips') }}</p></div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var desk = @json($desk);
    var U = window.AdminUi;
    var L = @json($runtimeJsLang ?? [], JSON_UNESCAPED_UNICODE);
    var COLORS = ['#3b82c4', '#e67e22', '#27ae60', '#8e44ad'];

    function copyText(text) {
        text = String(text || '');
        function done() {
            if (U) U.toast(L.copied || '');
        }
        function fallback() {
            var ta = document.createElement('textarea');
            ta.value = text;
            ta.setAttribute('readonly', '');
            ta.style.position = 'fixed';
            ta.style.left = '-9999px';
            document.body.appendChild(ta);
            ta.select();
            try { document.execCommand('copy'); done(); } catch (e) {}
            document.body.removeChild(ta);
        }
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(done).catch(fallback);
            return;
        }
        fallback();
    }
    function copyCron(btn) {
        var row = (btn && btn.closest) ? btn.closest('.runtime-cron, .schedule-cron-row') : null;
        var el = row ? row.querySelector('.js-runtime-cron') : document.querySelector('.js-runtime-cron');
        if (!el) return;
        var text = (el.getAttribute('data-cron') || el.textContent || '').trim();
        copyText(text);
        var old = btn.getAttribute('data-label') || btn.textContent;
        btn.setAttribute('data-label', old);
        btn.textContent = L.copied || '';
        btn.disabled = true;
        setTimeout(function () {
            btn.textContent = old;
            btn.disabled = false;
        }, 1400);
    }
    document.querySelectorAll('.js-copy-cron').forEach(function (btn) {
        btn.addEventListener('click', function () { copyCron(btn); });
    });

    if (!U) return;

    function pad2(n) { return (n < 10 ? '0' : '') + n; }
    function fmtTime(ts, span) {
        var d = new Date(ts * 1000);
        if (span < 172800) return pad2(d.getHours()) + ':' + pad2(d.getMinutes());
        return pad2(d.getMonth() + 1) + '-' + pad2(d.getDate());
    }
    function fmtLast(v) {
        if (v === null || v === undefined || v === '') return '—';
        var n = Number(v);
        if (!isFinite(n)) return '—';
        if (Math.abs(n) >= 100) return String(Math.round(n));
        if (Math.abs(n) >= 10) return n.toFixed(1);
        return n.toFixed(2);
    }

    function colorAlpha(hex, a) {
        var h = String(hex || '').replace('#', '');
        if (h.length === 3) h = h[0] + h[0] + h[1] + h[1] + h[2] + h[2];
        var n = parseInt(h, 16);
        if (!isFinite(n)) return 'rgba(59,130,196,' + a + ')';
        return 'rgba(' + ((n >> 16) & 255) + ',' + ((n >> 8) & 255) + ',' + (n & 255) + ',' + a + ')';
    }
    function tipClock(ts, span) {
        var d = new Date(ts * 1000);
        var clock = pad2(d.getHours()) + ':' + pad2(d.getMinutes());
        if (span < 172800) return clock;
        return pad2(d.getMonth() + 1) + '-' + pad2(d.getDate()) + ' ' + clock;
    }
    function emptyCharts(wrap) {
        wrap.innerHTML = '<div class="list-empty"><p>' + (L.empty_chart || '') + '</p></div>';
    }
    function drawGroup(canvas, seriesList, hoverX) {
        var ctx = canvas.getContext('2d');
        if (!ctx) return;
        var dpr = window.devicePixelRatio || 1;
        var cssW = canvas.clientWidth || 640;
        var cssH = 180;
        var pxW = Math.max(1, Math.round(cssW * dpr));
        var pxH = Math.max(1, Math.round(cssH * dpr));
        if (canvas.width !== pxW) canvas.width = pxW;
        if (canvas.height !== pxH) canvas.height = pxH;
        canvas.style.height = cssH + 'px';
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        ctx.clearRect(0, 0, cssW, cssH);
        var all = [];
        (seriesList || []).forEach(function (s) {
            (s.points || []).forEach(function (p) { all.push(p); });
        });
        if (!all.length) return;
        var minV = all[0][1], maxV = all[0][1], minT = all[0][0], maxT = all[0][0];
        all.forEach(function (p) {
            if (p[1] < minV) minV = p[1];
            if (p[1] > maxV) maxV = p[1];
            if (p[0] < minT) minT = p[0];
            if (p[0] > maxT) maxT = p[0];
        });
        if (maxV === minV) { maxV += 1; minV -= 1; }
        if (maxT === minT) maxT += 1;
        var span = maxT - minT;
        var left = 46, right = 8, top = 8, bottom = 22;
        var plotW = Math.max(1, cssW - left - right);
        var plotH = Math.max(1, cssH - top - bottom);
        function xOf(t) { return left + plotW * (t - minT) / (maxT - minT); }
        function yOf(v) { return top + plotH * (1 - (v - minV) / (maxV - minV)); }
        ctx.strokeStyle = '#e5e9ee';
        ctx.fillStyle = '#8a96a3';
        ctx.font = '11px sans-serif';
        ctx.lineWidth = 1;
        var ticks = 4;
        for (var i = 0; i <= ticks; i++) {
            var v = minV + (maxV - minV) * (1 - i / ticks);
            var y = top + plotH * (i / ticks);
            ctx.beginPath();
            ctx.moveTo(left, y);
            ctx.lineTo(cssW - right, y);
            ctx.stroke();
            var ylab = Math.abs(v) >= 10 ? String(Math.round(v)) : v.toFixed(1);
            ctx.fillText(ylab, 2, y + 3);
        }
        var xTicks = 4;
        for (var j = 0; j <= xTicks; j++) {
            var t = minT + span * (j / xTicks);
            ctx.fillText(fmtTime(t, span), Math.max(2, xOf(t) - 16), cssH - 6);
        }
        var baseY = top + plotH;
        (seriesList || []).forEach(function (s, idx) {
            var pts = s.points || [];
            if (!pts.length) return;
            var color = COLORS[idx % COLORS.length];
            ctx.beginPath();
            pts.forEach(function (p, n) {
                var x = xOf(p[0]), yy = yOf(p[1]);
                if (n === 0) ctx.moveTo(x, yy); else ctx.lineTo(x, yy);
            });
            ctx.lineTo(xOf(pts[pts.length - 1][0]), baseY);
            ctx.lineTo(xOf(pts[0][0]), baseY);
            ctx.closePath();
            ctx.fillStyle = colorAlpha(color, 0.12);
            ctx.fill();
            ctx.beginPath();
            pts.forEach(function (p, n) {
                var x = xOf(p[0]), yy = yOf(p[1]);
                if (n === 0) ctx.moveTo(x, yy); else ctx.lineTo(x, yy);
            });
            ctx.strokeStyle = color;
            ctx.lineWidth = 1.6;
            ctx.stroke();
        });
        if (typeof hoverX === 'number' && isFinite(hoverX)) {
            ctx.save();
            ctx.strokeStyle = '#9aa3ad';
            ctx.lineWidth = 1;
            ctx.setLineDash([3, 3]);
            ctx.beginPath();
            ctx.moveTo(hoverX, top);
            ctx.lineTo(hoverX, top + plotH);
            ctx.stroke();
            ctx.restore();
        }
        canvas._xOf = xOf;
        canvas._span = span;
    }
    function nearestByX(seriesList, xOf, mx) {
        var best = null, bestD = 1e12;
        (seriesList || []).forEach(function (s) {
            (s.points || []).forEach(function (p) {
                var d = Math.abs(xOf(p[0]) - mx);
                if (d < bestD) {
                    bestD = d;
                    best = { s: s, p: p };
                }
            });
        });
        return best;
    }
    function bindCanvasHover(canvas, seriesList, tip) {
        canvas.addEventListener('mousemove', function (ev) {
            if (!canvas._xOf) return;
            var rect = canvas.getBoundingClientRect();
            var mx = ev.clientX - rect.left;
            var best = nearestByX(seriesList, canvas._xOf, mx);
            if (!best) {
                tip.classList.remove('is-on');
                drawGroup(canvas, seriesList);
                return;
            }
            var hx = canvas._xOf(best.p[0]);
            drawGroup(canvas, seriesList, hx);
            tip.textContent = tipClock(best.p[0], canvas._span) + '  ' + (best.s.label || best.s.key) + ' ' + fmtLast(best.p[1]);
            var card = canvas.parentNode;
            var cardRect = card.getBoundingClientRect();
            var left = ev.clientX - cardRect.left + 12;
            var top = ev.clientY - cardRect.top + 12;
            tip.style.left = left + 'px';
            tip.style.top = top + 'px';
            tip.classList.add('is-on');
        });
        canvas.addEventListener('mouseleave', function () {
            tip.classList.remove('is-on');
            drawGroup(canvas, seriesList);
        });
    }
    function groupsFrom(data) {
        var groups = data.groups;
        if (!Array.isArray(groups) || !groups.length) {
            groups = (data.series || []).map(function (s) {
                return { id: s.key, label: s.label || s.key, series: [s] };
            });
        }
        return groups;
    }
    function renderSeries(data) {
        var wrap = document.getElementById('runtime-charts');
        if (!wrap) return;
        if (!data) {
            emptyCharts(wrap);
            return;
        }
        var groups = groupsFrom(data);
        var hint = document.getElementById('runtime-granularity');
        if (hint) hint.textContent = data.granularity === 'hour' ? (L.gran_hour || '') : (data.granularity ? (L.gran_min || '') : '');
        wrap.innerHTML = '';
        if (!groups.length) {
            emptyCharts(wrap);
            return;
        }
        groups.forEach(function (group) {
            var list = (group.series || []).filter(function (s) { return s.points && s.points.length; });
            var card = document.createElement('article');
            card.className = list.length ? 'runtime-chart' : 'runtime-chart is-empty';
            var head = document.createElement('div');
            head.className = 'runtime-chart-head';
            var title = document.createElement('h3');
            title.textContent = group.label || group.id || '';
            head.appendChild(title);
            card.appendChild(head);
            if (!list.length) {
                var empty = document.createElement('p');
                empty.className = 'runtime-chart-empty';
                empty.textContent = group.empty_reason || '这个时间范围内还没有点，曲线空着。';
                card.appendChild(empty);
                wrap.appendChild(card);
                return;
            }
            var legend = document.createElement('div');
            legend.className = 'runtime-chart-legend';
            list.forEach(function (s, idx) {
                var item = document.createElement('span');
                var dot = document.createElement('i');
                dot.style.background = COLORS[idx % COLORS.length];
                item.appendChild(dot);
                item.appendChild(document.createTextNode((s.label || s.key) + ' ' + fmtLast(s.last)));
                legend.appendChild(item);
            });
            var canvas = document.createElement('canvas');
            var tip = document.createElement('div');
            tip.className = 'runtime-chart-tip';
            card.appendChild(legend);
            card.appendChild(canvas);
            card.appendChild(tip);
            wrap.appendChild(card);
            drawGroup(canvas, list);
            bindCanvasHover(canvas, list, tip);
        });
    }

    var lastPayload = null;
    function loadSeries(range) {
        var wrap = document.getElementById('runtime-charts');
        if (!wrap) return;
        wrap.innerHTML = '<div class="list-empty"><p>正在读样本…</p></div>';
        U.get('/admin/system/runtime/series', { range: range }).then(function (res) {
            if (!res || res.code !== 0) {
                lastPayload = null;
                emptyCharts(wrap);
                return;
            }
            lastPayload = res.data || {};
            renderSeries(lastPayload);
        }).catch(function () {
            lastPayload = null;
            emptyCharts(wrap);
        });
    }

    if (desk === 'perf') {
        var rangeBox = document.getElementById('runtime-ranges');
        var currentRange = '1h';
        var resizeTimer = 0;
        loadSeries(currentRange);
        if (rangeBox) {
            rangeBox.addEventListener('click', function (e) {
                var btn = e.target.closest('[data-range]');
                if (!btn) return;
                rangeBox.querySelectorAll('.chip').forEach(function (c) { c.classList.remove('active'); });
                btn.classList.add('active');
                currentRange = btn.getAttribute('data-range') || '1h';
                loadSeries(currentRange);
            });
        }
        window.addEventListener('resize', function () {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function () {
                if (lastPayload) renderSeries(lastPayload);
            }, 160);
        });
    }

    function fillNum(id, v, kind) {
        var el = document.getElementById(id);
        if (!el) return;
        var card = el.closest ? el.closest('article') : el.parentNode;
        function showEmpty() {
            el.textContent = '—';
            if (card && card.classList) card.classList.add('is-empty');
        }
        function showVal(text) {
            el.textContent = text;
            if (card && card.classList) card.classList.remove('is-empty');
        }
        if (v === null || v === undefined || v === '') {
            showEmpty();
            return;
        }
        var n = Number(v);
        if (!isFinite(n)) {
            showEmpty();
            return;
        }
        if (kind === 'qps' || kind === 'load') showVal(n.toFixed(2));
        else if (kind === 'pct') showVal(n.toFixed(1));
        else showVal(String(Math.round(n)));
    }

    var liveTimer = 0;
    var paused = false;
    function loadLive() {
        U.get('/admin/system/runtime/live').then(function (res) {
            var d = res.data || {};
            fillNum('live-qps', d.qps, 'qps');
            fillNum('live-req', d.req5, 'int');
            fillNum('live-err', d.err5, 'int');
            fillNum('live-slow', d.slow5, 'int');
            fillNum('live-load', d.load, 'load');
            fillNum('live-mem', d.mem, 'pct');
            fillNum('live-disk', d.disk, 'pct');
            fillNum('live-php', d.php_mem, 'pct');
            var stamp = document.getElementById('live-stamp');
            if (stamp && d.ts) {
                var t = new Date(Number(d.ts) * 1000);
                stamp.textContent = '更新于 ' + pad2(t.getHours()) + ':' + pad2(t.getMinutes()) + ':' + pad2(t.getSeconds());
            }
        });
    }
    if (desk === 'live') {
        loadLive();
        liveTimer = setInterval(function () { if (!paused) loadLive(); }, 5000);
        var pauseBtn = document.getElementById('runtime-pause');
        if (pauseBtn) {
            pauseBtn.addEventListener('click', function () {
                paused = !paused;
                pauseBtn.textContent = paused ? '继续' : '暂停';
            });
        }
    }

    var settings = document.getElementById('runtime-settings');
    if (settings) {
        settings.addEventListener('submit', function (e) {
            e.preventDefault();
            U.post('/admin/system/runtime/settings', U.formData(settings)).then(function (res) {
                U.toast(U.pickMsg(res, '已保存'));
            });
        });
    }

    document.querySelectorAll('.js-rule-on').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = btn.closest('tr').getAttribute('data-id');
            U.post('/admin/system/runtime/rules/status', { id: id, status: btn.getAttribute('data-on') }).then(function (res) {
                U.toast(U.pickMsg(res, '已改'));
                if (res.code === 0) location.reload();
            });
        });
    });
    document.querySelectorAll('.js-rule-edit').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var tpl = document.getElementById('runtime-rule-tpl');
            if (!tpl) return;
            var id = btn.closest('tr').getAttribute('data-id');
            var name = btn.getAttribute('data-name') || '';
            var cond = btn.getAttribute('data-cond') || '';
            U.dialog({
                title: name ? ('改阈值 · ' + name) : '改阈值',
                content: tpl.innerHTML,
                onOpen: function (body) {
                    var input = body.querySelector('[name=threshold]');
                    if (input) input.value = btn.getAttribute('data-threshold') || '0';
                    if (cond) {
                        var hint = document.createElement('p');
                        hint.className = 'muted field-hint';
                        hint.textContent = cond;
                        body.appendChild(hint);
                    }
                },
                onSave: function (body) {
                    var input = body.querySelector('[name=threshold]');
                    var next = input ? input.value : '';
                    if (next === '') { U.toast('请填写阈值', 'err'); return false; }
                    return U.post('/admin/system/runtime/rules/save', { id: id, threshold: next }).then(function (res) {
                        U.toast(U.pickMsg(res, '已保存'));
                        if (!res || res.code !== 0) return false;
                        location.reload();
                    });
                }
            });
        });
    });
    document.querySelectorAll('.js-rule-test').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = btn.closest('tr').getAttribute('data-id');
            U.post('/admin/system/runtime/rules/test', { id: id }).then(function (res) {
                U.toast(U.pickMsg(res, '没有结果'));
            });
        });
    });
    document.querySelectorAll('.js-event-ack').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = btn.closest('tr').getAttribute('data-id');
            U.post('/admin/system/runtime/events/ack', { id: id }).then(function (res) {
                U.toast(U.pickMsg(res, '已确认'));
                if (res.code === 0) location.reload();
            });
        });
    });
})();
</script>
@endpush

@if(($access_view ?? '') === 'logs')
@push('scripts')
@include('admin.video.partials.accesslog_board_scripts')
@endpush
@endif
