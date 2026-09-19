@extends('admin.layouts.inner')
@section('title', $title ?? admin_t('ui.playfails'))

@php
    $queues = $queues ?? ['all' => 0, 'open' => 0, 'done' => 0, 'offline' => 0, 'today' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
    $failJsLang = [
        'status' => admin_t('ui.status'),
        'actions' => admin_t('ui.actions'),
        'delete' => admin_t('ui.delete'),
        'deleted' => admin_t('ui.deleted'),
        'fail' => admin_t('ui.fail'),
        'front' => admin_t('ui.front'),
        'clear_filter' => admin_t('ui.clear_filter'),
        'selected_rows' => admin_t('ui.selected_rows', ['n' => '__N__']),
        'op_fail' => admin_t('manga.op_fail'),
        'op_ok' => admin_t('manga.op_ok'),
        'report_open' => admin_t('ui.report_open'),
        'report_done' => admin_t('ui.report_done'),
        'mark_done' => admin_t('ui.mark_done'),
        'mark_open' => admin_t('ui.mark_open'),
        'batch_offline_line' => admin_t('ui.batch_offline_line'),
        'col_fail' => admin_t('ui.col_fail'),
        'video_hash' => admin_t('ui.video_hash', ['id' => '__ID__']),
        'video_gone' => admin_t('ui.video_gone'),
        'line_hash' => admin_t('ui.line_hash', ['id' => '__ID__']),
        'no_line_linked' => admin_t('ui.no_line_linked'),
        'source_offline' => admin_t('ui.source_offline'),
        'play_fail_default' => admin_t('ui.play_fail_default'),
        'empty_playfails' => admin_t('ui.empty_playfails'),
        'empty_playfails_hint' => admin_t('ui.empty_playfails_hint'),
        'no_match_playfails' => admin_t('ui.no_match_playfails'),
        'go_players' => admin_t('ui.go_players'),
        'please_select_playfails' => admin_t('ui.please_select_playfails'),
        'confirm_batch_offline' => admin_t('ui.confirm_batch_offline'),
        'confirm_batch_del_playfails' => admin_t('ui.confirm_batch_del_playfails'),
        'confirm_offline_line' => admin_t('ui.confirm_offline_line'),
        'confirm_del_playfail' => admin_t('ui.confirm_del_playfail'),
        'offlined' => admin_t('ui.offlined'),
        'edit_video_link' => admin_t('ui.edit_video_link'),
        'report_reopened' => admin_t('ui.report_reopened'),
    ];
@endphp

@section('plain')
<div class="card card-panel playfail-index list-desk">
    <div class="card-header">
        <span>{{ admin_t('ui.playfails') }}@if($q('open') > 0) <em>{{ admin_t('ui.header_open_n', ['n' => $q('open')]) }}</em>@endif</span>
        <div>
            <a class="btn btn-muted btn-sm" href="/admin/video/reports">{{ admin_t('ui.reports') }}</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/tools/players">{{ admin_t('ui.batch_players') }}</a>
            <a class="btn btn-muted btn-sm" href="/admin/video">{{ admin_t('ui.video_list') }}</a>
        </div>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="fail-search" onsubmit="return false;">
            <input type="hidden" name="status">
            <input type="hidden" name="offline">
            <input type="hidden" name="today">
            <input type="search" name="q" placeholder="{{ admin_t('ui.ph_playfail') }}" autocomplete="off" aria-label="{{ admin_t('ui.playfails') }}">
            <button type="button" class="btn btn-sm" id="fail-search-btn">{{ admin_t('ui.search') }}</button>
            <button type="reset" class="btn btn-muted btn-sm" id="fail-reset-btn">{{ admin_t('ui.reset') }}</button>
        </form>
        <div class="queue-chips" id="fail-queues">
            <button type="button" class="chip" data-queue="">{{ admin_t('ui.all') }}@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="0">{{ admin_t('ui.report_open') }}@if($q('open') > 0)<em>{{ $q('open') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="1">{{ admin_t('ui.report_done') }}@if($q('done') > 0)<em>{{ $q('done') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="offline" data-value="1">{{ admin_t('ui.chip_can_offline') }}@if($q('offline') > 0)<em>{{ $q('offline') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="today" data-value="1">{{ admin_t('ui.chip_today') }}@if($q('today') > 0)<em>{{ $q('today') }}</em>@endif</button>
        </div>
        <p class="muted recycle-lead">{{ admin_t('ui.playfails_lead_before') }}<strong>{{ admin_t('ui.playfails_lead_strong') }}</strong>{{ admin_t('ui.playfails_lead_after') }}</p>
        <div class="batch-bar" id="fail-batch" hidden>
            <strong id="fail-batch-count">{{ admin_t('ui.selected_rows', ['n' => 0]) }}</strong>
            <button type="button" class="btn btn-sm" id="fail-batch-done">{{ admin_t('ui.mark_done') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="fail-batch-open">{{ admin_t('ui.mark_open') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="fail-batch-off">{{ admin_t('ui.batch_offline_line') }}</button>
            <button type="button" class="btn btn-danger btn-sm" id="fail-batch-del">{{ admin_t('ui.delete') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="fail-batch-clear">{{ admin_t('ui.clear_selection') }}</button>
        </div>
        <div id="fail-table"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($failJsLang);
    var form = document.getElementById('fail-search');
    var batchBar = document.getElementById('fail-batch');
    var batchCount = document.getElementById('fail-batch-count');
    var QUEUE_KEYS = ['offline', 'today'];

    function cleanWhere(data) {
        var out = {};
        Object.keys(data || {}).forEach(function (k) { if (data[k] !== '') out[k] = data[k]; });
        return out;
    }
    function queryWhere() {
        return Object.assign({limit: 20}, cleanWhere(U.formData(form)));
    }
    function isFiltered(where) {
        return Object.keys(where || {}).some(function (k) { return k !== 'limit' && where[k] !== ''; });
    }
    function markChips() {
        var status = form.status.value;
        var offline = form.offline.value;
        var today = form.today.value;
        U.qa('#fail-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = false;
            if (key === '' && status === '' && offline === '' && today === '') on = true;
            else if (key === 'status' && offline === '' && today === '' && status === val) on = true;
            else if (key === 'offline' && status === '' && today === '' && offline === val) on = true;
            else if (key === 'today' && status === '' && offline === '' && today === val) on = true;
            chip.classList.toggle('active', on);
        });
    }
    function applyQueue(key, value) {
        QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
        form.status.value = '';
        if (key === 'status') form.status.value = value || '';
        else if (key && form[key]) form[key].value = value || '1';
        runSearch();
    }
    function runSearch() {
        table.reload(queryWhere());
        markChips();
    }
    function filmHtml(d) {
        if (d.video_title) {
            return '<a href="/admin/video/' + encodeURIComponent(d.video_id) + '/edit">' + U.escape(d.video_title) + '</a>';
        }
        return d.video_id ? String(L.video_hash || '').replace('__ID__', U.escape(String(d.video_id))) : L.video_gone;
    }
    function lineHtml(d) {
        var parts = [];
        if (d.source_name) parts.push(U.escape(d.source_name));
        else if (d.source_id) parts.push(String(L.line_hash || '').replace('__ID__', U.escape(String(d.source_id))));
        if (d.episode_label) parts.push(U.escape(d.episode_label));
        if (d.source_player) parts.push(U.escape(d.source_player));
        return parts.join(' · ') || L.no_line_linked;
    }
    function contentHtml(d) {
        var meta = filmHtml(d) + ' · ' + lineHtml(d);
        if (d.created_at_text) meta += ' · ' + U.escape(d.created_at_text);
        if (d.ip) meta += ' · ' + U.escape(d.ip);
        var url = d.url ? '<div class="muted fail-url">' + U.escape(d.url) + '</div>' : '';
        var badges = [];
        if (parseInt(d.source_id, 10) > 0 && parseInt(d.source_status, 10) === 0) {
            badges.push('<span class="badge badge-off">' + L.source_offline + '</span>');
        }
        return '<div class="comment-cell"><div class="comment-body">' + U.escape(d.content || L.play_fail_default) + '</div>'
            + url
            + '<div class="muted">' + meta + '</div>'
            + (badges.length ? '<div class="vod-badges">' + badges.join('') + '</div>' : '')
            + '</div>';
    }

    var table = U.table({
        el: '#fail-table',
        queueKeys: QUEUE_KEYS,
        url: '/admin/video/playfails/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>' + L.no_match_playfails + '</p><p><button type="button" class="btn btn-muted btn-sm" id="fail-empty-reset">' + L.clear_filter + '</button></p></div>';
            }
            return '<div class="list-empty"><p>' + L.empty_playfails + '</p><p class="muted">' + L.empty_playfails_hint + '</p><p><a class="btn btn-muted btn-sm" href="/admin/video/players">' + L.go_players + '</a></p></div>';
        },
        onDraw: function () {
            var reset = document.getElementById('fail-empty-reset');
            if (reset) reset.addEventListener('click', function () {
                form.reset();
                QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
                form.status.value = '';
                runSearch();
            });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = String(L.selected_rows || '').replace('__N__', String(ids.length));
        },
        cols: [
            {check: true, width: 36},
            {title: L.col_fail, html: contentHtml},
            {title: L.status, width: 88, html: function (d) {
                return parseInt(d.status, 10) === 1 ? U.status(true, L.report_done) : U.status(false, L.report_open);
            }},
            {title: L.actions, cls: 'actions', html: function (d) {
                var html = '';
                if (parseInt(d.status, 10) === 1) html += '<a href="#" class="btn-link js-open">' + L.mark_open + '</a>';
                else html += '<a href="#" class="btn-link js-done">' + L.mark_done + '</a>';
                if (parseInt(d.can_offline, 10) === 1) html += '<a href="#" class="btn-link js-off">' + L.batch_offline_line + '</a>';
                if (parseInt(d.video_id, 10) > 0) {
                    html += '<a class="btn-link" href="/admin/video/' + encodeURIComponent(d.video_id) + '/edit">' + L.edit_video_link + '</a>';
                    html += '<a class="btn-link" href="' + U.escape(d.play_url || ('/vod/' + d.video_id)) + '" target="_blank" rel="noopener">' + L.front + '</a>';
                }
                html += '<a href="#" class="btn-link js-del">' + L.delete + '</a>';
                return html;
            }}
        ]
    });
    markChips();

    function selectedIds() { return table.selectedIds(); }
    function batch(action, value, confirmText) {
        var ids = selectedIds();
        if (!ids.length) { U.toast(L.please_select_playfails, 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/playfails/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.op_fail, 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || L.op_ok, 'ok');
        });
    }
    function setStatus(row, status) {
        U.post('/admin/video/playfails/save', {id: row.id, status: status}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
            table.refresh();
            U.toast(status === 1 ? L.report_done : L.report_reopened, 'ok');
        });
    }
    function offline(row) {
        if (!U.confirm(L.confirm_offline_line)) return;
        U.post('/admin/video/playfails/offline', {id: row.id}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || L.offlined, 'ok');
        });
    }

    U.on('#fail-search-btn', 'click', runSearch);
    U.on('#fail-reset-btn', 'click', function () {
        setTimeout(function () {
            QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
            form.status.value = '';
            runSearch();
        }, 0);
    });
    document.getElementById('fail-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#fail-batch-done', 'click', function () { batch('status', 1); });
    U.on('#fail-batch-open', 'click', function () { batch('status', 0); });
    U.on('#fail-batch-off', 'click', function () { batch('offline', '', L.confirm_batch_offline); });
    U.on('#fail-batch-del', 'click', function () { batch('delete', '', L.confirm_batch_del_playfails); });
    U.on('#fail-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#fail-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        if (a.target === '_blank') return;
        if (a.getAttribute('href') && a.getAttribute('href').indexOf('/admin/video/') === 0) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-done')) setStatus(row, 1);
        if (a.classList.contains('js-open')) setStatus(row, 0);
        if (a.classList.contains('js-off')) offline(row);
        if (a.classList.contains('js-del')) {
            if (!U.confirm(L.confirm_del_playfail)) return;
            U.post('/admin/video/playfails/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
                table.refresh();
                U.toast(L.deleted, 'ok');
            });
        }
    });
})();
</script>
@endpush
