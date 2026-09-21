@extends('admin.layouts.inner')
@section('title', admin_t('page.db_restore'))

@php
    $ui = $ui ?? [];
    $files = $files ?? [];
    $usable = [];
    $blocked = [];
    foreach ($files as $row) {
        if (! empty($row['can_restore'])) {
            $usable[] = $row;
        } else {
            $blocked[] = $row;
        }
    }
    $unavailable = trim((string) ($restore_unavailable ?? ''));
    $canSnapshot = (bool) ($can_snapshot ?? false);
@endphp

@section('plain')
<div class="card card-panel restore-index db-index" id="restore-index">
    <div class="card-header">
        <span>{{ $ui['restore_title'] ?? '' }}</span>
    </div>
    <div class="card-body">
        @include('admin.partials.db-tabs', ['tab' => 'restore'])
        <p class="muted recycle-lead">{{ $ui['restore_lead'] ?? '' }}{{ admin_t('ui.restore_cache_after') }}「<a href="/admin/system/tools/cache">{{ $ui['restore_cache'] ?? admin_t('page.cache') }}</a>」{{ admin_t('ui.restore_cache_clear') }}</p>
        <p class="restore-note">{{ $ui['restore_note'] ?? '' }}</p>

        <section class="cache-block">
            <div class="cache-block-head">
                <h3>{{ $ui['restore_now'] ?? '' }}</h3>
                <span class="badge">{{ $driver_label ?? '' }}</span>
            </div>
            <p class="muted cache-block-detail">{{ $dir_text ?? '' }}</p>
            <p class="muted field-hint">{{ $driver_hint ?? '' }}</p>
            @if($unavailable !== '')
                <p class="schedule-idle-hint">{{ $unavailable }}</p>
            @endif
        </section>

        <section class="backup-files" id="restore-usable-wrap">
            <h3>{{ $ui['restore_files'] ?? '' }}</h3>
            <div id="restore-file-list">
                @if($usable === [])
                    <div class="list-empty">
                        <p>{{ $ui['restore_empty'] ?? '' }}</p>
                        <p class="muted">{{ $ui['restore_empty_hint'] ?? '' }}</p>
                        <p><a class="btn btn-sm" href="/admin/system/database/backup">{{ $ui['restore_go'] ?? '' }}</a></p>
                    </div>
                @else
                    @foreach($usable as $row)
                        <article class="backup-file" data-name="{{ $row['name'] }}" data-can="1">
                            <div class="backup-file-main">
                                <strong>{{ $row['name'] }}</strong>
                                <p class="muted">{{ $row['size_text'] ?? '' }} · {{ $row['time'] ?? '' }} · {{ $row['kind_label'] ?? '' }}</p>
                            </div>
                            <div class="backup-file-side">
                                <a class="btn btn-muted btn-sm" href="/admin/system/database/backup/download?file={{ urlencode($row['name']) }}">{{ $ui['download'] ?? '' }}</a>
                                <button type="button" class="btn btn-sm js-restore">{{ $ui['restore_run'] ?? '' }}</button>
                                <button type="button" class="btn btn-muted btn-sm js-del">{{ $ui['delete'] ?? '' }}</button>
                            </div>
                        </article>
                    @endforeach
                @endif
            </div>
        </section>

        <section class="backup-files" id="restore-blocked-wrap" @if($blocked === []) hidden @endif>
            <h3>{{ $ui['restore_blocked'] ?? '' }}</h3>
            <div id="restore-blocked-list">
                @foreach($blocked as $row)
                    <article class="backup-file is-blocked" data-name="{{ $row['name'] }}" data-can="0">
                        <div class="backup-file-main">
                            <strong>{{ $row['name'] }}</strong>
                            <p class="muted">{{ $row['size_text'] ?? '' }} · {{ $row['time'] ?? '' }} · {{ $row['kind_label'] ?? '' }}</p>
                            <p class="schedule-err">{{ $row['restore_hint'] ?? ($ui['restore_mismatch'] ?? '') }}</p>
                        </div>
                        <div class="backup-file-side">
                            <a class="btn btn-muted btn-sm" href="/admin/system/database/backup/download?file={{ urlencode($row['name']) }}">{{ $ui['download'] ?? '' }}</a>
                            <button type="button" class="btn btn-muted btn-sm js-del">{{ $ui['delete'] ?? '' }}</button>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
        <p class="muted" id="restore-flash" hidden></p>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var root = document.getElementById('restore-index');
    if (!root || !U) return;
    var UI = @json($ui, JSON_UNESCAPED_UNICODE);
    var L = {!! json_encode([
        'delete_fail' => admin_t('ui.delete_fail'),
        'restore_typed_wrong' => admin_t('ui.restore_typed_wrong', ['word' => admin_t('ui.restore_word')]),
        'restore_fail' => admin_t('ui.restore_fail'),
        'restore_word' => admin_t('ui.restore_word'),
    ], JSON_UNESCAPED_UNICODE) !!};
    var canSnapshot = @json($canSnapshot, JSON_UNESCAPED_UNICODE);

    function cardHtml(row, can) {
        var name = row.name || '';
        var html = '<article class="backup-file' + (can ? '' : ' is-blocked') + '" data-name="' + U.escape(name) + '" data-can="' + (can ? '1' : '0') + '">';
        html += '<div class="backup-file-main"><strong>' + U.escape(name) + '</strong>';
        html += '<p class="muted">' + U.escape((row.size_text || '') + ' · ' + (row.time || '') + ' · ' + (row.kind_label || '')) + '</p>';
        if (!can) html += '<p class="schedule-err">' + U.escape(row.restore_hint || UI.restore_mismatch || '') + '</p>';
        html += '</div><div class="backup-file-side">';
        html += '<a class="btn btn-muted btn-sm" href="/admin/system/database/backup/download?file=' + encodeURIComponent(name) + '">' + U.escape(UI.download || '') + '</a>';
        if (can) html += '<button type="button" class="btn btn-sm js-restore">' + U.escape(UI.restore_run || '') + '</button>';
        html += '<button type="button" class="btn btn-muted btn-sm js-del">' + U.escape(UI.delete || '') + '</button></div></article>';
        return html;
    }

    function renderFiles(rows) {
        var box = document.getElementById('restore-file-list');
        var blockedBox = document.getElementById('restore-blocked-list');
        var blockedWrap = document.getElementById('restore-blocked-wrap');
        rows = rows || [];
        var usable = [];
        var blocked = [];
        rows.forEach(function (row) {
            if (row.can_restore) usable.push(row);
            else blocked.push(row);
        });
        if (box) {
            if (!usable.length) {
                box.innerHTML = '<div class="list-empty"><p>' + U.escape(UI.restore_empty || '') + '</p><p class="muted">' + U.escape(UI.restore_empty_hint || '') + '</p><p><a class="btn btn-sm" href="/admin/system/database/backup">' + U.escape(UI.restore_go || '') + '</a></p></div>';
            } else {
                box.innerHTML = usable.map(function (row) { return cardHtml(row, true); }).join('');
            }
        }
        if (blockedBox) blockedBox.innerHTML = blocked.map(function (row) { return cardHtml(row, false); }).join('');
        if (blockedWrap) blockedWrap.hidden = blocked.length === 0;
    }

    function reloadFiles() {
        return U.get('/admin/system/database/restore/files').then(function (res) {
            if (!res || res.code !== 0) return;
            renderFiles((res.data && res.data.data) || res.data || []);
        });
    }

    U.on('#restore-file-list', 'click', onFileClick);
    U.on('#restore-blocked-list', 'click', onFileClick);

    function onFileClick(e) {
        var card = e.target.closest('.backup-file');
        if (!card) return;
        var name = card.getAttribute('data-name') || '';
        if (!name) return;
        if (e.target.closest('.js-del')) {
            if (!U.confirm(UI.del_confirm || '')) return;
            U.post('/admin/system/database/backup/delete', {file: name}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.delete_fail, 'err'); return; }
                U.toast(res.msg || '', 'ok');
                reloadFiles();
            });
            return;
        }
        if (e.target.closest('.js-restore')) {
            var warn = canSnapshot ? (UI.restore_confirm_save || '') : (UI.restore_confirm_nosave || '');
            if (!U.confirm(warn + '\n' + name)) return;
            var typed = U.prompt(UI.restore_type || '', '');
            if (typed === null) return;
            if (String(typed).trim() !== String(UI.restore_word || L.restore_word)) {
                U.toast(UI.restore_type_err || L.restore_typed_wrong, 'err');
                return;
            }
            U.loading(true);
            U.post('/admin/system/database/restore/run', {file: name, snapshot: canSnapshot ? 1 : 0}).then(function (res) {
                U.loading(false);
                var ok = res && res.code === 0;
                U.toast((res && res.msg) || '', ok ? 'ok' : 'err');
                var flash = document.getElementById('restore-flash');
                if (flash) {
                    flash.hidden = false;
                    flash.textContent = (res && res.msg) || '';
                }
                if (ok) reloadFiles();
            }).catch(function () {
                U.loading(false);
                U.toast(L.restore_fail, 'err');
            });
        }
    }
})();
</script>
@endpush
