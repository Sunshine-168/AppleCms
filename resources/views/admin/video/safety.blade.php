@extends('admin.layouts.inner')
@section('title', admin_t('page.safety'))

@php
    $ui = $ui ?? [];
    $needles = $needles ?? [];
    $dirs = $dirs ?? [];
    $dirsApp = $dirs_app ?? [];
    $last = $last ?? null;
    $groups = is_array($last['groups'] ?? null) ? $last['groups'] : ['other' => [], 'known' => []];
    $hasLast = is_array($last) && trim((string) ($last['scanned_at'] ?? '')) !== '';
@endphp

@section('plain')
<div class="card card-panel safety-index" id="safety-index">
    <div class="card-header">
        <span>{{ $ui['title'] ?? '' }}</span>
        <div>
            <a class="btn btn-muted btn-sm" href="/admin/video/config/ip">{{ $ui['ip'] ?? '' }}</a>
            <a class="btn btn-muted btn-sm" href="/admin/plugins">{{ $ui['plugins'] ?? '' }}</a>
            <a class="btn btn-muted btn-sm" href="/admin/system/attachments">{{ $ui['files'] ?? '' }}</a>
            <a class="btn btn-muted btn-sm" href="/admin/system/monitor/operate-logs">{{ $ui['logs'] ?? '' }}</a>
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">{{ $ui['lead'] ?? '' }}</p>
        <p class="safety-note">{{ $ui['note'] ?? '' }}</p>

        <section class="safety-block">
            <h3>{{ $ui['needles'] ?? '' }}</h3>
            <div class="safety-needles">
                @foreach($needles as $needle)
                    <span class="safety-needle" title="{{ $needle['hint'] ?? '' }}"><code>{{ $needle['code'] ?? '' }}</code>{{ $needle['label'] ?? '' }}</span>
                @endforeach
            </div>
        </section>

        <section class="safety-block">
            <h3>{{ $ui['where'] ?? '' }}</h3>
            <div class="safety-dirs">
                @foreach($dirs as $dir)
                    <article class="safety-dir{{ empty($dir['exists']) ? ' is-missing' : '' }}">
                        <strong>{{ $dir['label'] ?? '' }}</strong>
                        <code>{{ $dir['path'] ?? '' }}</code>
                        <p>{{ $dir['hint'] ?? '' }}</p>
                        @if(empty($dir['exists']))
                            <span class="muted">{{ $ui['missing'] ?? '' }}</span>
                        @endif
                    </article>
                @endforeach
            </div>
            <p class="muted field-hint">{{ $ui['where_app'] ?? '' }}：{{ $ui['where_app_hint'] ?? '' }}</p>
            <div class="safety-dirs safety-dirs-app">
                @foreach($dirsApp as $dir)
                    <article class="safety-dir is-app{{ empty($dir['exists']) ? ' is-missing' : '' }}">
                        <strong>{{ $dir['label'] ?? '' }}</strong>
                        <code>{{ $dir['path'] ?? '' }}</code>
                    </article>
                @endforeach
            </div>
            <div class="safety-actions">
                <button type="button" class="btn" id="safety-scan" data-app="0">{{ $ui['scan'] ?? '' }}</button>
                <button type="button" class="btn btn-muted" id="safety-scan-app" data-app="1">{{ $ui['scan_app'] ?? '' }}</button>
            </div>
            <div class="safety-progress" id="safety-progress" hidden>
                <div class="safety-progress-track" id="safety-progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" aria-label="扫描进度">
                    <i id="safety-progress-fill"></i>
                </div>
                <div class="safety-progress-row">
                    <p class="safety-progress-text" id="safety-progress-text">{{ $ui['listing'] ?? '' }}</p>
                    <button type="button" class="btn-link" id="safety-progress-stop">{{ $ui['stop'] ?? '' }}</button>
                </div>
            </div>
        </section>

        <section class="safety-block" id="safety-result-block">
            <div class="safety-block-head">
                <h3>{{ $ui['result'] ?? '' }}</h3>
                <span class="badge" id="safety-meta">@if($hasLast){{ $last['scanned_at'] ?? '' }} · {{ (int) ($last['files_scanned'] ?? 0) }} 个文件 · {{ (int) ($last['duration_ms'] ?? 0) }} ms @endif</span>
            </div>
            <p class="safety-stale" id="safety-stale" @if(! $hasLast) hidden @endif>{{ $ui['stale'] ?? '' }}</p>
            <div class="safety-summary" id="safety-summary" @if(! $hasLast) hidden @endif>
                <span class="badge{{ (int) ($last['other_count'] ?? 0) > 0 ? ' badge-warn' : ' badge-ok' }}" id="safety-other-badge">要人工看 {{ (int) ($last['other_count'] ?? 0) }}</span>
                <span class="badge" id="safety-known-badge">本站已知 {{ (int) ($last['known_count'] ?? 0) }}</span>
            </div>
            <p class="safety-msg" id="safety-msg" @if(! $hasLast) hidden @endif>{{ $hasLast ? (string) ($last['summary'] ?? '') : '' }}</p>
            <div class="safety-empty" id="safety-empty" @if($hasLast) hidden @endif>
                <p>{{ $ui['empty'] ?? '' }}</p>
                <p class="muted">{{ $ui['empty_hint'] ?? '' }}</p>
            </div>
            <div id="safety-hits" @if(! $hasLast) hidden @endif>
                @include('admin.video._safety_hits', ['groups' => $groups, 'ui' => $ui, 'last' => $last])
            </div>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var ui = @json($ui);
    var last = @json($last);

    function hitCard(group, known) {
        var html = '<article class="safety-hit' + (known ? ' is-known' : ' is-other') + '">';
        html += '<div class="safety-hit-head"><strong>' + U.escape(group.file || '') + '</strong>';
        html += '<span class="badge' + (known ? '' : ' badge-warn') + '">' + (known ? U.escape(ui.known || '') : U.escape(ui.other || '')) + '</span></div>';
        (group.hits || []).forEach(function (hit) {
            html += '<div class="safety-hit-line"><code>' + U.escape(String(hit.line || '')) + '</code>';
            html += '<span class="safety-hit-fn">' + U.escape(hit.needle_label || hit.needle || '') + '</span>';
            if (hit.known_hint) html += '<span class="muted">' + U.escape(hit.known_hint) + '</span>';
            html += '</div>';
            if (hit.snippet) html += '<pre class="safety-snippet">' + U.escape(hit.snippet) + '</pre>';
        });
        html += '</article>';
        return html;
    }

    function render(data) {
        var empty = document.getElementById('safety-empty');
        var hits = document.getElementById('safety-hits');
        var summary = document.getElementById('safety-summary');
        var stale = document.getElementById('safety-stale');
        var meta = document.getElementById('safety-meta');
        var msg = document.getElementById('safety-msg');
        var otherBadge = document.getElementById('safety-other-badge');
        var knownBadge = document.getElementById('safety-known-badge');
        if (!data) {
            if (empty) empty.hidden = false;
            if (hits) hits.hidden = true;
            if (summary) summary.hidden = true;
            if (stale) stale.hidden = true;
            if (msg) msg.hidden = true;
            if (meta) meta.textContent = '';
            return;
        }
        if (empty) empty.hidden = true;
        if (hits) hits.hidden = false;
        if (summary) summary.hidden = false;
        if (stale) stale.hidden = false;
        if (meta) {
            meta.textContent = (data.scanned_at || '') + ' · ' + (data.files_scanned || 0) + ' 个文件 · ' + (data.duration_ms || 0) + ' ms';
        }
        if (otherBadge) {
            otherBadge.textContent = '要人工看 ' + (data.other_count || 0);
            otherBadge.classList.toggle('badge-warn', (data.other_count || 0) > 0);
            otherBadge.classList.toggle('badge-ok', (data.other_count || 0) < 1);
        }
        if (knownBadge) knownBadge.textContent = '本站已知 ' + (data.known_count || 0);
        if (msg) {
            msg.hidden = false;
            msg.textContent = data.summary || '';
        }
        var groups = data.groups || {other: [], known: []};
        var html = '';
        if ((groups.other || []).length) {
            html += '<h4>' + U.escape(ui.other || '') + '</h4>';
            (groups.other || []).forEach(function (g) { html += hitCard(g, false); });
        } else if (data.scanned_at) {
            html += '<p class="muted">' + U.escape(ui.none_other || '') + '</p>';
        }
        if ((groups.known || []).length) {
            html += '<h4>' + U.escape(ui.known || '') + '</h4>';
            (groups.known || []).forEach(function (g) { html += hitCard(g, true); });
        }
        if (hits) hits.innerHTML = html;
    }

    var running = false;
    var cancelled = false;
    var token = '';
    var ticks = 0;
    var scanBtn = document.getElementById('safety-scan');
    var scanAppBtn = document.getElementById('safety-scan-app');
    var progressBox = document.getElementById('safety-progress');
    var progressBar = document.getElementById('safety-progress-bar');
    var progressFill = document.getElementById('safety-progress-fill');
    var progressText = document.getElementById('safety-progress-text');

    function setBusy(on) {
        running = on;
        if (scanBtn) scanBtn.disabled = on;
        if (scanAppBtn) scanAppBtn.disabled = on;
    }
    function showProgress(d, wait) {
        if (!progressBox) return;
        progressBox.hidden = false;
        progressBox.classList.toggle('is-wait', !!wait);
        var pct = wait ? 0 : Math.max(0, Math.min(100, parseInt(d.percent, 10) || 0));
        if (progressFill) progressFill.style.width = (wait ? 28 : pct) + '%';
        if (progressBar) progressBar.setAttribute('aria-valuenow', String(pct));
        if (progressText) progressText.textContent = d.msg || ((ui.progress || '') + ' ' + (d.scanned || 0) + ' / ' + (d.total || 0));
    }
    function hideProgress() {
        if (!progressBox) return;
        progressBox.hidden = true;
        progressBox.classList.remove('is-wait');
        if (progressFill) progressFill.style.width = '0%';
    }
    function failScan(text) {
        setBusy(false);
        hideProgress();
        U.toast(text || '没能扫描', 'err');
    }
    function tick(payload) {
        if (cancelled) return;
        U.post('/admin/video/safety/scan', payload).then(function (res) {
            if (cancelled) return;
            if (!res || res.code !== 0) {
                failScan((res && res.msg) || '没能扫描');
                return;
            }
            var data = res.data || {};
            if (!data.done) {
                ticks += 1;
                showProgress({
                    percent: data.percent,
                    scanned: data.scanned,
                    total: data.total,
                    msg: res.msg || data.msg || ''
                }, false);
                if (!data.token || ticks > 400) {
                    failScan(data.token ? '扫得太久，停了。再点扫一遍。' : '扫描中断了，请再点扫一遍');
                    return;
                }
                token = data.token;
                tick({token: token});
                return;
            }
            setBusy(false);
            hideProgress();
            data.summary = data.summary || res.msg || '';
            render(data);
            U.toast(res.msg || '已扫完', 'ok');
        }).catch(function () {
            if (cancelled) return;
            failScan('没连上，扫到一半停了。再点扫一遍。');
        });
    }
    function scan(withApp) {
        if (running) return;
        if (withApp && !U.confirm(ui.confirm_app || '')) return;
        cancelled = false;
        token = '';
        ticks = 0;
        setBusy(true);
        showProgress({percent: 0, scanned: 0, total: 0, msg: ui.listing || ''}, true);
        tick({with_app: withApp ? 1 : 0});
    }

    U.on('#safety-scan', 'click', function () { scan(false); });
    U.on('#safety-scan-app', 'click', function () { scan(true); });
    U.on('#safety-progress-stop', 'click', function () {
        if (!running) return;
        cancelled = true;
        var t = token;
        setBusy(false);
        hideProgress();
        if (t) U.post('/admin/video/safety/scan', {token: t, cancel: 1});
        U.toast('已停', 'ok');
    });
    if (last && last.scanned_at) {
        last.summary = last.summary || '';
        var msg = document.getElementById('safety-msg');
        if (msg && !msg.textContent) msg.textContent = '';
    }
})();
</script>
@endpush
