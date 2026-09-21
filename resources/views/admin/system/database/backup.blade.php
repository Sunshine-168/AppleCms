@extends('admin.layouts.inner')
@section('title', admin_t('page.db_backup'))

@php
    $ui = $ui ?? [];
    $schedule = $schedule ?? [];
    $files = $files ?? [];
    $cronPresets = $cron_presets ?? [];
    $canBackup = !empty($can_backup);
@endphp

@section('plain')
<div class="card card-panel backup-index db-index" id="backup-index">
    <div class="card-header">
        <span>{{ $ui['title'] ?? '' }}</span>
    </div>
    <div class="card-body">
        @include('admin.partials.db-tabs', ['tab' => 'backup'])
        <p class="muted recycle-lead">{{ $ui['lead'] ?? '' }}「<a href="/admin/system/tools/schedule">{{ $ui['schedule'] ?? admin_t('page.schedule') }}</a>」{{ admin_t('ui.backup_see_ran') }}</p>

        <div class="cache-block">
            <div class="cache-block-head">
                <h3>{{ $ui['now'] ?? '' }}</h3>
                <span class="badge">{{ $driver_label ?? '' }}</span>
            </div>
            <p class="muted cache-block-detail">{{ $dir_text ?? '' }}@if(($last_text ?? '') !== '') · {{ $last_text }}@endif</p>
            <p class="muted field-hint">{{ $driver_hint ?? '' }} {{ $ui['now_hint'] ?? '' }}</p>
            @if(! $canBackup)
                <p class="schedule-idle-hint">{{ $cannot_reason ?? ($ui['memory'] ?? '') }}</p>
            @endif
            <button type="button" class="btn btn-sm" id="backup-run" @if(! $canBackup) disabled @endif>{{ $ui['now'] ?? '' }}</button>
        </div>

        <div class="cache-block">
            <div class="cache-block-head">
                <h3>{{ $ui['timer'] ?? '' }}</h3>
                <span class="badge{{ !empty($schedule['on']) ? ' badge-ok' : ' badge-off' }}" id="backup-timer-badge">{{ $schedule['last_text'] ?? '' }}</span>
            </div>
            <p class="muted field-hint">{{ $ui['timer_hint'] ?? '' }}</p>
            <form id="backup-timer-form" autocomplete="off" onsubmit="return false;">
                <label class="inline"><input type="checkbox" name="on" value="1" @if(!empty($schedule['on'])) checked @endif @if(! $canBackup) disabled @endif> {{ $ui['on'] ?? '' }}</label>
                <label>{{ $ui['when'] ?? '' }}
                    <select name="cron" id="backup-cron">
                        @foreach($cronPresets as $expr => $label)
                            <option value="{{ $expr }}" @if($expr === ($schedule['cron'] ?? '0 3 * * *')) selected @endif>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label>{{ $ui['keep'] ?? '' }}
                    <input type="number" name="keep" id="backup-keep" min="1" max="30" value="{{ (int) ($keep ?? 7) }}">
                </label>
                <button type="button" class="btn btn-muted btn-sm" id="backup-timer-save" @if(! $canBackup) disabled @endif>{{ $ui['save_timer'] ?? '' }}</button>
            </form>
            @if(!empty($schedule['idle']))
                <p class="schedule-idle-hint">{{ $ui['install_hint'] ?? '' }}</p>
            @endif
        </div>

        <section class="schedule-install">
            <div class="schedule-install-head">
                <h3>{{ $ui['install'] ?? '' }}</h3>
            </div>
            <p class="muted field-hint">{{ $ui['install_hint'] ?? '' }}</p>
            <div class="schedule-cron-row">
                <code id="backup-cron-line">{{ $cron_line ?? '' }}</code>
                <button type="button" class="btn btn-muted" id="backup-copy-cron">{{ $ui['copy'] ?? '' }}</button>
            </div>
        </section>

        <section class="backup-files">
            <h3>{{ $ui['files'] ?? '' }}</h3>
            <div id="backup-file-list">
                @if($files === [])
                    <div class="list-empty" id="backup-empty">
                        <p>{{ $ui['empty'] ?? '' }}</p>
                        <p class="muted">{{ $ui['empty_hint'] ?? '' }}</p>
                    </div>
                @else
                    @foreach($files as $row)
                        <article class="backup-file" data-name="{{ $row['name'] }}">
                            <div class="backup-file-main">
                                <strong>{{ $row['name'] }}</strong>
                                <p class="muted">{{ $row['size_text'] ?? '' }} · {{ $row['time'] ?? '' }} · {{ $row['kind_label'] ?? '' }}</p>
                            </div>
                            <div class="backup-file-side">
                                <a class="btn btn-muted btn-sm" href="/admin/system/database/backup/download?file={{ urlencode($row['name']) }}">{{ $ui['download'] ?? '' }}</a>
                                <a class="btn btn-muted btn-sm" href="/admin/system/database/restore">{{ $ui['restore_one'] ?? '' }}</a>
                                <button type="button" class="btn btn-muted btn-sm js-del">{{ $ui['delete'] ?? '' }}</button>
                            </div>
                        </article>
                    @endforeach
                @endif
            </div>
        </section>
        <p class="muted" id="backup-flash" hidden></p>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var root = document.getElementById('backup-index');
    if (!root || !U) return;
    var UI = @json($ui, JSON_UNESCAPED_UNICODE);
    var L = {!! json_encode([
        'backup_fail' => admin_t('ui.backup_fail'),
        'save_fail' => admin_t('ui.save_fail'),
        'delete_fail' => admin_t('ui.delete_fail'),
    ], JSON_UNESCAPED_UNICODE) !!};
    var canBackup = @json($canBackup, JSON_UNESCAPED_UNICODE);

    function renderFiles(rows) {
        var box = document.getElementById('backup-file-list');
        if (!box) return;
        rows = rows || [];
        if (!rows.length) {
            box.innerHTML = '<div class="list-empty" id="backup-empty"><p>' + (UI.empty || '') + '</p><p class="muted">' + (UI.empty_hint || '') + '</p></div>';
            return;
        }
        var html = '';
        rows.forEach(function (row) {
            var name = row.name || '';
            html += '<article class="backup-file" data-name="' + String(name).replace(/"/g, '') + '">';
            html += '<div class="backup-file-main"><strong>' + String(name).replace(/</g, '') + '</strong>';
            html += '<p class="muted">' + (row.size_text || '') + ' · ' + (row.time || '') + ' · ' + (row.kind_label || '') + '</p></div>';
            html += '<div class="backup-file-side">';
            html += '<a class="btn btn-muted btn-sm" href="/admin/system/database/backup/download?file=' + encodeURIComponent(name) + '">' + (UI.download || '') + '</a>';
            html += '<a class="btn btn-muted btn-sm" href="/admin/system/database/restore">' + (UI.restore_one || '') + '</a>';
            html += '<button type="button" class="btn btn-muted btn-sm js-del">' + (UI.delete || '') + '</button></div></article>';
        });
        box.innerHTML = html;
    }

    function reloadFiles() {
        return U.get('/admin/system/database/backup/files').then(function (res) {
            if (!res || res.code !== 0) return;
            renderFiles((res.data && res.data.data) || res.data || []);
        });
    }

    U.on('#backup-run', 'click', function () {
        if (!canBackup) return;
        U.loading(true);
        var keep = parseInt(document.getElementById('backup-keep').value, 10) || 7;
        U.post('/admin/system/database/backup/run', {keep: keep}).then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.backup_fail, 'err'); return; }
            U.toast((res.msg) || UI.ok || '', 'ok');
            reloadFiles();
        });
    });

    U.on('#backup-timer-save', 'click', function () {
        var form = document.getElementById('backup-timer-form');
        U.post('/admin/system/database/backup/schedule', {
            on: form.querySelector('[name=on]').checked ? 1 : 0,
            cron: document.getElementById('backup-cron').value,
            keep: parseInt(document.getElementById('backup-keep').value, 10) || 7
        }).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.save_fail, 'err'); return; }
            U.toast(res.msg || '', 'ok');
            location.reload();
        });
    });

    U.on('#backup-copy-cron', 'click', function () {
        var line = document.getElementById('backup-cron-line');
        if (!line) return;
        var text = line.textContent || '';
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(function () { U.toast(UI.copied || '', 'ok'); });
            return;
        }
        U.toast(text, 'ok');
    });

    U.on('#backup-file-list', 'click', function (e) {
        var btn = e.target.closest('.js-del');
        if (!btn) return;
        var card = btn.closest('.backup-file');
        var name = card ? card.getAttribute('data-name') : '';
        if (!name) return;
        if (!U.confirm(UI.del_confirm || '')) return;
        U.post('/admin/system/database/backup/delete', {file: name}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.delete_fail, 'err'); return; }
            U.toast(res.msg || '', 'ok');
            reloadFiles();
        });
    });
})();
</script>
@endpush
