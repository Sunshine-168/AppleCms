@extends('admin.layouts.inner')
@section('title', admin_t('page.system_logs'))

@php
    $today = $today ?? now()->toDateString();
    $yesterday = $yesterday ?? now()->subDay()->toDateString();
    $weekFrom = $weekFrom ?? now()->subDays(6)->toDateString();
    $monthFrom = $monthFrom ?? now()->subDays(29)->toDateString();
    $jsLang = [
        'error_log_title' => admin_t('ui.tab_error'),
        'error_once' => admin_t('ui.error_once'),
        'view_detail' => admin_t('ui.view_detail'),
        'said_what' => admin_t('ui.said_what'),
        'exc_class' => admin_t('ui.exc_class'),
        'kind_file' => admin_t('ui.kind_file'),
        'opened_then' => admin_t('ui.opened_then'),
        'stack_trace' => admin_t('ui.stack_trace'),
        'error_detail' => admin_t('ui.error_detail'),
        'no_match' => admin_t('ui.no_match'),
        'clear_filter' => admin_t('ui.clear_filter'),
        'empty_sys_error' => admin_t('ui.empty_sys_error'),
        'empty_sys_error_hint' => admin_t('ui.empty_sys_error_hint'),
    ];
@endphp

@section('plain')
<div class="card card-panel log-index system-log-index">
    <div class="card-header">
        <span>{{ admin_t('ui.tab_error') }} <em id="system-log-count"></em></span>
    </div>
    <div class="card-body">
        @include('admin.partials.log-tabs', ['tab' => 'error'])
        @include('admin.partials.log-filters', ['kind' => 'error'])
        <p class="muted recycle-lead">{{ admin_t('ui.error_lead') }}</p>
        <div id="system-log-table"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($jsLang, JSON_UNESCAPED_UNICODE);
    var form = document.getElementById('system-log-search');
    var countEl = document.getElementById('system-log-count');
    var ipChip = document.getElementById('system-log-ip-chip');
    var whenSel = document.getElementById('system-log-when');
    var datesWrap = document.getElementById('system-log-dates');
    var TODAY = @json($today, JSON_UNESCAPED_UNICODE);
    var YESTERDAY = @json($yesterday, JSON_UNESCAPED_UNICODE);
    var WEEK_FROM = @json($weekFrom, JSON_UNESCAPED_UNICODE);
    var MONTH_FROM = @json($monthFrom, JSON_UNESCAPED_UNICODE);
    var typing = 0;

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
    function setDates(from, to) {
        form.start_time.value = from || '';
        form.end_time.value = to || '';
    }
    function syncWhenUi() {
        var from = form.start_time.value;
        var to = form.end_time.value;
        var key = '';
        if (from === TODAY && to === TODAY) key = 'today';
        else if (from === YESTERDAY && to === YESTERDAY) key = 'yesterday';
        else if (from === WEEK_FROM && to === TODAY) key = 'week';
        else if (from === MONTH_FROM && to === TODAY) key = 'month';
        else if (from || to) key = 'custom';
        whenSel.value = key;
        datesWrap.hidden = key !== 'custom';
    }
    function applyWhen() {
        var key = whenSel.value;
        if (key === 'custom') {
            datesWrap.hidden = false;
            if (!form.start_time.value) form.start_time.value = TODAY;
            if (!form.end_time.value) form.end_time.value = TODAY;
            runSearch();
            return;
        }
        datesWrap.hidden = true;
        setDates('', '');
        if (key === 'today') setDates(TODAY, TODAY);
        else if (key === 'yesterday') setDates(YESTERDAY, YESTERDAY);
        else if (key === 'week') setDates(WEEK_FROM, TODAY);
        else if (key === 'month') setDates(MONTH_FROM, TODAY);
        runSearch();
    }
    function markExtra() {
        var ip = form.ip.value;
        if (ipChip) {
            ipChip.hidden = ip === '';
            ipChip.textContent = ip ? 'IP ' + ip : '';
        }
    }
    function runSearch() {
        table.reload(queryWhere());
        syncWhenUi();
        markExtra();
    }
    function resetAll() {
        form.reset();
        form.ip.value = '';
        setDates('', '');
        whenSel.value = '';
        datesWrap.hidden = true;
        runSearch();
    }
    function rowHtml(d) {
        var kind = d.kind_text || L.error_log_title;
        var badge = '<span class="badge' + (d.is_error ? ' badge-warn' : '') + '">' + U.escape(kind) + '</span>';
        var meta = [];
        if (d.who_text) meta.push(U.escape(d.who_text));
        if (d.area_text) meta.push(U.escape(d.area_text));
        if (d.time_text) meta.push(U.escape(d.time_text));
        if (d.path_text) meta.push(U.escape(d.path_text));
        if (d.ip) {
            meta.push('<a href="#" class="log-ip js-ip" data-ip="' + U.escape(d.ip) + '">' + U.escape(d.ip) + '</a>');
        }
        if (d.file_text) meta.push(U.escape(d.file_text));
        var extra = d.extra_text ? '<div class="muted">' + U.escape(d.extra_text) + '</div>' : '';
        return '<div class="entry-row-title-line">' + badge + ' <span class="entry-row-title">' + U.escape(d.summary || L.error_once) + '</span>'
            + ' <a href="#" class="btn-link js-detail">' + U.escape(L.view_detail) + '</a></div>'
            + '<div class="entry-row-meta">' + meta.join(' · ') + '</div>'
            + extra;
    }
    function showDetail(row) {
        var blocks = [];
        blocks.push('<p><b>' + U.escape(L.said_what) + '</b></p><pre class="out">' + U.escape(row.detail_message || row.summary || '') + '</pre>');
        if (row.detail_class) blocks.push('<p><b>' + U.escape(L.exc_class) + '</b></p><pre class="out">' + U.escape(row.detail_class) + '</pre>');
        if (row.detail_file) blocks.push('<p><b>' + U.escape(L.kind_file) + '</b></p><pre class="out">' + U.escape(row.detail_file) + '</pre>');
        if (row.detail_url) blocks.push('<p><b>' + U.escape(L.opened_then) + '</b></p><pre class="out">' + U.escape(row.detail_url) + '</pre>');
        if (row.detail_trace) blocks.push('<p><b>' + U.escape(L.stack_trace) + '</b></p><pre class="out">' + U.escape(row.detail_trace) + '</pre>');
        U.dialog({
            title: row.kind_text || L.error_detail,
            wide: true,
            hideOk: true,
            content: blocks.join('')
        });
    }

    var table = U.table({
        el: '#system-log-table',
        countEl: countEl,
        url: '/admin/system/monitor/system-logs/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>' + U.escape(L.no_match) + '</p><p><button type="button" class="btn btn-muted btn-sm" id="system-log-empty-reset">' + U.escape(L.clear_filter) + '</button></p></div>';
            }
            return '<div class="list-empty"><p>' + U.escape(L.empty_sys_error) + '</p><p class="muted">' + U.escape(L.empty_sys_error_hint) + '</p></div>';
        },
        onDraw: function (_wrap, list) {
            markExtra();
            var reset = document.getElementById('system-log-empty-reset');
            if (reset) reset.addEventListener('click', resetAll);
        },
        cols: [
            {title: L.error_log_title, html: rowHtml}
        ]
    });
    syncWhenUi();
    markExtra();

    whenSel.addEventListener('change', applyWhen);
    form.start_time.addEventListener('change', runSearch);
    form.end_time.addEventListener('change', runSearch);
    form.level.addEventListener('change', runSearch);
    form.area.addEventListener('change', runSearch);
    form.q.addEventListener('input', function () {
        clearTimeout(typing);
        typing = setTimeout(runSearch, 400);
    });
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        clearTimeout(typing);
        runSearch();
    });
    U.on('#system-log-reset-btn', 'click', resetAll);
    U.on('#system-log-ip-chip', 'click', function () {
        form.ip.value = '';
        runSearch();
    });
    U.on('#system-log-table', 'click', function (e) {
        var ip = e.target.closest('a.js-ip');
        if (ip) {
            e.preventDefault();
            form.ip.value = ip.getAttribute('data-ip') || '';
            runSearch();
            return;
        }
        var a = e.target.closest('a.js-detail');
        if (!a) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        showDetail(row);
    });
})();
</script>
@endpush
