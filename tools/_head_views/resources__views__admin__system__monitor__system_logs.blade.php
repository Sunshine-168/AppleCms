fatal: path 'resources\views\admin\system\monitor\system_logs.blade.php' exists on disk, but not in 'HEAD'
@extends('admin.layouts.inner')
@section('title', admin_t('page.system_logs'))

@php
    $today = $today ?? now()->toDateString();
    $yesterday = $yesterday ?? now()->subDay()->toDateString();
    $weekFrom = $weekFrom ?? now()->subDays(6)->toDateString();
    $monthFrom = $monthFrom ?? now()->subDays(29)->toDateString();
@endphp

@section('plain')
<div class="card card-panel log-index system-log-index">
    <div class="card-header">
        <span>报错 <em id="system-log-count"></em></span>
    </div>
    <div class="card-body">
        @include('admin.partials.log-tabs', ['tab' => 'error'])
        @include('admin.partials.log-filters', ['kind' => 'error'])
        <p class="muted recycle-lead">程序抛错会记一行。这里改不了报错，也打不开 laravel.log。</p>
        <div id="system-log-table"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var form = document.getElementById('system-log-search');
    var countEl = document.getElementById('system-log-count');
    var ipChip = document.getElementById('system-log-ip-chip');
    var whenSel = document.getElementById('system-log-when');
    var datesWrap = document.getElementById('system-log-dates');
    var TODAY = @json($today);
    var YESTERDAY = @json($yesterday);
    var WEEK_FROM = @json($weekFrom);
    var MONTH_FROM = @json($monthFrom);
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
        var kind = d.kind_text || '报错';
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
        return '<div class="entry-row-title-line">' + badge + ' <span class="entry-row-title">' + U.escape(d.summary || '一次程序报错') + '</span>'
            + ' <a href="#" class="btn-link js-detail">看详情</a></div>'
            + '<div class="entry-row-meta">' + meta.join(' · ') + '</div>'
            + extra;
    }
    function showDetail(row) {
        var blocks = [];
        blocks.push('<p><b>说了什么</b></p><pre class="out">' + U.escape(row.detail_message || row.summary || '') + '</pre>');
        if (row.detail_class) blocks.push('<p><b>异常</b></p><pre class="out">' + U.escape(row.detail_class) + '</pre>');
        if (row.detail_file) blocks.push('<p><b>文件</b></p><pre class="out">' + U.escape(row.detail_file) + '</pre>');
        if (row.detail_url) blocks.push('<p><b>当时打开</b></p><pre class="out">' + U.escape(row.detail_url) + '</pre>');
        if (row.detail_trace) blocks.push('<p><b>调用栈</b></p><pre class="out">' + U.escape(row.detail_trace) + '</pre>');
        U.dialog({
            title: row.kind_text || '报错详情',
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
                return '<div class="list-empty"><p>没有符合条件的记录。</p><p><button type="button" class="btn btn-muted btn-sm" id="system-log-empty-reset">清除筛选</button></p></div>';
            }
            return '<div class="list-empty"><p>还没有程序报错。</p><p class="muted">前台或后台一旦抛错会出现在这里。打开本页不会记。</p></div>';
        },
        onDraw: function (_wrap, list) {
            markExtra();
            var reset = document.getElementById('system-log-empty-reset');
            if (reset) reset.addEventListener('click', resetAll);
        },
        cols: [
            {title: '报错', html: rowHtml}
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