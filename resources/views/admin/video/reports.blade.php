@extends('admin.layouts.inner')
@section('title', $title)

@php
    $queues = $queues ?? ['all' => 0, 'open' => 0, 'done' => 0, 'today' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
    $reportJsLang = [
        'status' => admin_t('ui.status'),
        'actions' => admin_t('ui.actions'),
        'delete' => admin_t('ui.delete'),
        'fail' => admin_t('ui.fail'),
        'deleted' => admin_t('ui.deleted'),
        'clear_filter' => admin_t('ui.clear_filter'),
        'selected_rows' => admin_t('ui.selected_rows', ['n' => '__N__']),
        'op_fail' => admin_t('manga.op_fail'),
        'op_ok' => admin_t('manga.op_ok'),
        'report_open' => admin_t('ui.report_open'),
        'report_done' => admin_t('ui.report_done'),
        'mark_done' => admin_t('ui.mark_done'),
        'mark_open' => admin_t('ui.mark_open'),
        'col_report' => admin_t('ui.col_report'),
        'edit_video_link' => admin_t('ui.edit_video_link'),
        'video_gone' => admin_t('ui.video_gone'),
        'front' => admin_t('ui.front'),
        'guest' => admin_t('ui.guest'),
        'video_hash' => admin_t('ui.video_hash', ['id' => '__ID__']),
        'member_hash' => admin_t('ui.member_hash', ['id' => '__ID__']),
        'empty_reports' => admin_t('ui.empty_reports'),
        'empty_reports_hint' => admin_t('ui.empty_reports_hint'),
        'no_match_reports' => admin_t('ui.no_match_reports'),
        'please_select_reports' => admin_t('ui.please_select_reports'),
        'report_reopened' => admin_t('ui.report_reopened'),
        'confirm_batch_del_reports' => admin_t('ui.confirm_batch_del_reports'),
        'confirm_del_report' => admin_t('ui.confirm_del_report'),
        'go_videos' => admin_t('ui.go_videos'),
    ];
@endphp

@section('plain')
<div class="card card-panel report-index list-desk">
    <div class="card-header">
        <span>{{ admin_t('ui.reports') }}@if($q('open') > 0) <em>{{ admin_t('ui.header_open_n', ['n' => $q('open')]) }}</em>@endif</span>
        <div>
            <a class="btn btn-muted btn-sm" href="/admin/video/playfails">{{ admin_t('ui.playfails') }}</a>
            <a class="btn btn-muted btn-sm" href="/admin/video">{{ admin_t('ui.video_list') }}</a>
        </div>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="report-search" onsubmit="return false;">
            <input type="hidden" name="status">
            <input type="hidden" name="today">
            <input type="search" name="q" placeholder="{{ admin_t('ui.ph_report') }}" autocomplete="off" aria-label="{{ admin_t('ui.reports') }}">
            <button type="button" class="btn btn-sm" id="report-search-btn">{{ admin_t('ui.search') }}</button>
            <button type="reset" class="btn btn-muted btn-sm" id="report-reset-btn">{{ admin_t('ui.reset') }}</button>
        </form>
        <div class="queue-chips" id="report-queues">
            <button type="button" class="chip" data-queue="">{{ admin_t('ui.all') }}@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="0">{{ admin_t('ui.report_open') }}@if($q('open') > 0)<em>{{ $q('open') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="1">{{ admin_t('ui.report_done') }}@if($q('done') > 0)<em>{{ $q('done') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="today" data-value="1">{{ admin_t('ui.today') }}@if($q('today') > 0)<em>{{ $q('today') }}</em>@endif</button>
        </div>
        <p class="muted recycle-lead">{{ admin_t('ui.reports_lead') }}</p>
        <div class="batch-bar" id="report-batch" hidden>
            <strong id="report-batch-count">{{ admin_t('ui.selected_rows', ['n' => 0]) }}</strong>
            <button type="button" class="btn btn-sm" id="report-batch-done">{{ admin_t('ui.mark_done') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="report-batch-open">{{ admin_t('ui.mark_open') }}</button>
            <button type="button" class="btn btn-danger btn-sm" id="report-batch-del">{{ admin_t('ui.delete') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="report-batch-clear">{{ admin_t('ui.clear_selection') }}</button>
        </div>
        <div id="report-table"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($reportJsLang);
    var form = document.getElementById('report-search');
    var batchBar = document.getElementById('report-batch');
    var batchCount = document.getElementById('report-batch-count');
    var QUEUE_KEYS = ['today'];

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
        var today = form.today.value;
        U.qa('#report-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = false;
            if (key === '' && status === '' && today === '') on = true;
            else if (key === 'status' && today === '' && status === val) on = true;
            else if (key === 'today' && today === val) on = true;
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
        if (d.video_id) {
            return U.escape(String(L.video_hash || '').replace('__ID__', String(d.video_id)));
        }
        return U.escape(L.video_gone);
    }
    function contentHtml(d) {
        var who;
        if (d.member_name) {
            who = U.escape(d.member_name);
        } else if (parseInt(d.member_id, 10) > 0) {
            who = U.escape(String(L.member_hash || '').replace('__ID__', String(d.member_id)));
        } else {
            who = U.escape(L.guest);
        }
        var meta = who;
        if (d.created_at_text) meta += ' · ' + U.escape(d.created_at_text);
        if (d.ip) meta += ' · ' + U.escape(d.ip);
        return '<div class="comment-cell"><div class="comment-body">' + U.escape(d.content || '') + '</div>'
            + '<div class="muted">' + meta + ' · ' + filmHtml(d) + '</div></div>';
    }

    var table = U.table({
        el: '#report-table',
        queueKeys: QUEUE_KEYS,
        url: '/admin/video/reports/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>' + L.no_match_reports + '</p><p><button type="button" class="btn btn-muted btn-sm" id="report-empty-reset">' + L.clear_filter + '</button></p></div>';
            }
            return '<div class="list-empty"><p>' + L.empty_reports + '</p><p class="muted">' + L.empty_reports_hint + '</p><p><a class="btn btn-muted btn-sm" href="/admin/video">' + L.go_videos + '</a></p></div>';
        },
        onDraw: function (_wrap, rows) {
            var reset = document.getElementById('report-empty-reset');
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
            {title: L.col_report, html: contentHtml},
            {title: L.status, width: 88, html: function (d) {
                return parseInt(d.status, 10) === 1 ? U.status(true, L.report_done) : U.status(false, L.report_open);
            }},
            {title: L.actions, cls: 'actions', html: function (d) {
                var html = '';
                if (parseInt(d.status, 10) === 1) html += '<a href="#" class="btn-link js-open">' + L.mark_open + '</a>';
                else html += '<a href="#" class="btn-link js-done">' + L.mark_done + '</a>';
                if (parseInt(d.video_id, 10) > 0) {
                    html += '<a class="btn-link" href="/admin/video/' + encodeURIComponent(d.video_id) + '/edit">' + L.edit_video_link + '</a>';
                    html += '<a class="btn-link" href="/vod/' + encodeURIComponent(d.video_id) + '" target="_blank" rel="noopener">' + L.front + '</a>';
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
        if (!ids.length) { U.toast(L.please_select_reports, 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/reports/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.op_fail, 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || L.op_ok, 'ok');
        });
    }
    function setStatus(row, status) {
        U.post('/admin/video/reports/save', {id: row.id, status: status}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
            table.refresh();
            U.toast(status === 1 ? L.report_done : L.report_reopened, 'ok');
        });
    }

    U.on('#report-search-btn', 'click', runSearch);
    U.on('#report-reset-btn', 'click', function () {
        setTimeout(function () {
            QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
            form.status.value = '';
            runSearch();
        }, 0);
    });
    document.getElementById('report-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#report-batch-done', 'click', function () { batch('status', 1); });
    U.on('#report-batch-open', 'click', function () { batch('status', 0); });
    U.on('#report-batch-del', 'click', function () { batch('delete', '', L.confirm_batch_del_reports); });
    U.on('#report-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#report-table', 'click', function (e) {
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
        if (a.classList.contains('js-del')) {
            if (!U.confirm(L.confirm_del_report)) return;
            U.post('/admin/video/reports/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
                table.refresh();
                U.toast(L.deleted, 'ok');
            });
        }
    });
})();
</script>
@endpush
