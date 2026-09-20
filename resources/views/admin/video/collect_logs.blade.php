@extends('admin.layouts.inner')
@section('title', $title)

@php
    $queues = $queues ?? ['all' => 0, 'ok' => 0, 'fail' => 0, 'today' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
    $sourceId = (int) ($sourceId ?? 0);
    $sourceName = trim((string) ($sourceName ?? ''));
    $sourceChip = $sourceName !== '' ? $sourceName : ($sourceId > 0 ? admin_t('ui.collect_source_n', ['id' => $sourceId]) : '');
    $okPrefill = (string) ($okPrefill ?? '');
    $todayPrefill = (string) ($todayPrefill ?? '');
    $clogJsLang = [
        'delete' => admin_t('ui.delete'),
        'fail' => admin_t('ui.fail'),
        'deleted' => admin_t('ui.deleted'),
        'clear_filter' => admin_t('ui.clear_filter'),
        'clear_selection' => admin_t('ui.clear_selection'),
        'selected_rows' => admin_t('ui.selected_rows', ['n' => '__N__']),
        'op_fail' => admin_t('manga.op_fail'),
        'op_ok' => admin_t('manga.op_ok'),
        'success' => admin_t('ui.success'),
        'collect_source_n' => admin_t('ui.collect_source_n', ['id' => '__ID__']),
        'col_stat' => admin_t('ui.col_stat'),
        'col_time' => admin_t('ui.col_time'),
        'actions' => admin_t('ui.actions'),
        'stat_new' => admin_t('ui.stat_new'),
        'stat_upd' => admin_t('ui.stat_upd'),
        'stat_skip' => admin_t('ui.stat_skip'),
        'today_at' => admin_t('ui.today_at', ['time' => '__TIME__']),
        'yesterday_at' => admin_t('ui.yesterday_at', ['time' => '__TIME__']),
        'empty_collect_logs' => admin_t('ui.empty_collect_logs'),
        'empty_collect_logs_hint' => admin_t('ui.empty_collect_logs_hint'),
        'no_match_collect_logs' => admin_t('ui.no_match_collect_logs'),
        'go_collects' => admin_t('ui.go_collects'),
        'please_select_logs' => admin_t('ui.please_select_logs'),
        'confirm_batch_del_logs' => admin_t('ui.confirm_batch_del_logs'),
        'confirm_del_collect_log' => admin_t('ui.confirm_del_collect_log'),
        'delete_records' => admin_t('ui.delete_records'),
        'collects' => admin_t('ui.collects'),
    ];
@endphp

@section('plain')
<div class="card card-panel collect-log-index desk-board">
    <div class="card-header">
        <span>{{ admin_t('ui.collect_logs') }} <em id="clog-count"></em></span>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="clog-search" onsubmit="return false;">
            <input type="hidden" name="ok" value="{{ $okPrefill }}">
            <input type="hidden" name="today" value="{{ $todayPrefill }}">
            <input type="hidden" name="collect_source_id" value="{{ $sourceId > 0 ? $sourceId : '' }}">
            <input type="search" name="q" placeholder="{{ admin_t('ui.ph_collect_log') }}" autocomplete="off" aria-label="{{ admin_t('ui.collect_logs') }}">
            <button type="button" class="btn btn-sm" id="clog-search-btn">{{ admin_t('ui.search') }}</button>
            <button type="reset" class="btn btn-muted btn-sm" id="clog-reset-btn">{{ admin_t('ui.reset') }}</button>
        </form>
        <div class="queue-chips" id="clog-queues">
            <button type="button" class="chip" data-queue="">{{ admin_t('ui.all') }}@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="ok" data-value="1">{{ admin_t('ui.success') }}@if($q('ok') > 0)<em>{{ $q('ok') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="ok" data-value="0">{{ admin_t('ui.fail') }}@if($q('fail') > 0)<em>{{ $q('fail') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="today" data-value="1">{{ admin_t('ui.chip_today') }}@if($q('today') > 0)<em>{{ $q('today') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="source" id="clog-source-chip" @if($sourceId < 1) hidden @endif>{{ $sourceChip }}</button>
        </div>
        <p class="muted recycle-lead">{{ admin_t('ui.collect_logs_lead') }}</p>
        <div class="batch-bar" id="clog-batch" hidden>
            <strong id="clog-batch-count">{{ admin_t('ui.selected_rows', ['n' => 0]) }}</strong>
            <button type="button" class="btn btn-danger btn-sm" id="clog-batch-del">{{ admin_t('ui.delete_records') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="clog-batch-clear">{{ admin_t('ui.clear_selection') }}</button>
        </div>
        <div id="clog-table" class="desk-table"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($clogJsLang, JSON_UNESCAPED_UNICODE);
    var form = document.getElementById('clog-search');
    var batchBar = document.getElementById('clog-batch');
    var batchCount = document.getElementById('clog-batch-count');
    var countEl = document.getElementById('clog-count');
    var sourceChip = document.getElementById('clog-source-chip');

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
    function setSource(id, name) {
        form.collect_source_id.value = id ? String(id) : '';
        if (!sourceChip) return;
        sourceChip.textContent = name || (id ? String(L.collect_source_n || '').replace('__ID__', id) : '');
        sourceChip.hidden = !form.collect_source_id.value;
    }
    function markChips() {
        var ok = form.ok.value;
        var today = form.today.value;
        var source = form.collect_source_id.value;
        U.qa('#clog-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = false;
            if (key === 'source') on = !!source;
            else if (key === '' && ok === '' && today === '') on = true;
            else if (key === 'ok' && ok === val && today === '') on = true;
            else if (key === 'today' && today === '1' && ok === '') on = true;
            chip.classList.toggle('active', on);
        });
    }
    function applyQueue(key, value) {
        if (key === 'source') {
            setSource('', '');
        } else {
            form.ok.value = '';
            form.today.value = '';
            if (key === 'ok') form.ok.value = value || '';
            if (key === 'today') form.today.value = '1';
        }
        runSearch();
    }
    function runSearch() {
        table.reload(queryWhere());
        markChips();
    }
    function fmtTime(ts) {
        ts = parseInt(ts, 10) || 0;
        if (!ts) return '—';
        var d = new Date(ts * 1000);
        var now = new Date();
        var pad = function (n) { return n < 10 ? '0' + n : '' + n; };
        var hm = pad(d.getHours()) + ':' + pad(d.getMinutes());
        if (d.toDateString() === now.toDateString()) return String(L.today_at || '').replace('__TIME__', hm);
        var y = new Date(now);
        y.setDate(now.getDate() - 1);
        if (d.toDateString() === y.toDateString()) return String(L.yesterday_at || '').replace('__TIME__', hm);
        if (d.getFullYear() === now.getFullYear()) return pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + ' ' + hm;
        return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
    }
    function titleHtml(d) {
        var name = d.source_name || String(L.collect_source_n || '').replace('__ID__', String(d.collect_source_id || 0));
        var ok = parseInt(d.ok, 10) === 1;
        var okLabel = d.ok_label || (ok ? L.success : L.fail);
        var badge = '<span class="badge ' + (ok ? 'badge-ok' : 'badge-warn') + '">' + U.escape(okLabel) + '</span>';
        var meta = [];
        if (d.page_text) meta.push(U.escape(d.page_text));
        if (d.stat_text) meta.push(U.escape(d.stat_text));
        if (d.msg) meta.push(U.escape(d.msg));
        return '<div class="entry-row-title-line"><a class="entry-row-title js-source" href="#">' + U.escape(name) + '</a> ' + badge + '</div>'
            + '<div class="entry-row-meta">' + (meta.join(' · ') || '—') + '</div>';
    }
    function statHtml(d) {
        return '<span class="clog-stat"><strong>' + U.escape(String(d.created_n || 0)) + '</strong> ' + U.escape(L.stat_new)
            + ' · <strong>' + U.escape(String(d.updated_n || 0)) + '</strong> ' + U.escape(L.stat_upd)
            + ' · <strong>' + U.escape(String(d.skipped_n || 0)) + '</strong> ' + U.escape(L.stat_skip) + '</span>';
    }

    var table = U.table({
        el: '#clog-table',
        countEl: countEl,
        url: '/admin/video/collect_logs/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>' + L.no_match_collect_logs + '</p><p><button type="button" class="btn btn-muted btn-sm" id="clog-empty-reset">' + L.clear_filter + '</button></p></div>';
            }
            return '<div class="list-empty"><p>' + L.empty_collect_logs + '</p><p class="muted">' + L.empty_collect_logs_hint + '</p><p><a class="btn btn-primary btn-sm" href="/admin/video/collects">' + L.go_collects + '</a></p></div>';
        },
        onDraw: function (wrap, list) {
            U.qa('tbody tr[data-idx]', wrap).forEach(function (tr) {
                var d = list[parseInt(tr.getAttribute('data-idx'), 10)];
                if (d && parseInt(d.ok, 10) !== 1) tr.classList.add('is-fail');
            });
            var reset = document.getElementById('clog-empty-reset');
            if (reset) reset.addEventListener('click', function () {
                form.reset();
                form.ok.value = '';
                form.today.value = '';
                setSource('', '');
                runSearch();
            });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = String(L.selected_rows || '').replace('__N__', ids.length);
        },
        cols: [
            {check: true, width: 36},
            {title: L.collects, html: titleHtml},
            {title: L.col_stat, width: 140, html: statHtml},
            {title: L.col_time, width: 120, html: function (d) { return fmtTime(d.created_at); }},
            {title: L.actions, cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-del">' + L.delete + '</a>';
            }}
        ]
    });
    markChips();

    function selectedIds() { return table.selectedIds(); }
    function batchDel() {
        var ids = selectedIds();
        if (!ids.length) { U.toast(L.please_select_logs, 'err'); return; }
        if (!U.confirm(L.confirm_batch_del_logs)) return;
        U.post('/admin/video/collect_logs/batch', {ids: ids.join(','), action: 'delete'}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.op_fail, 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || L.op_ok, 'ok');
        });
    }

    U.on('#clog-search-btn', 'click', runSearch);
    U.on('#clog-reset-btn', 'click', function () {
        setTimeout(function () {
            form.ok.value = '';
            form.today.value = '';
            setSource('', '');
            runSearch();
        }, 0);
    });
    document.getElementById('clog-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#clog-batch-del', 'click', batchDel);
    U.on('#clog-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#clog-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        if (a.getAttribute('href') && a.getAttribute('href').indexOf('/admin/video/collects') === 0) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        e.preventDefault();
        if (a.classList.contains('js-source')) {
            if (!row) return;
            setSource(row.collect_source_id || '', row.source_name || '');
            runSearch();
            return;
        }
        if (!row) return;
        if (a.classList.contains('js-del')) {
            if (!U.confirm(L.confirm_del_collect_log)) return;
            U.post('/admin/video/collect_logs/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
                table.refresh();
                U.toast(L.deleted, 'ok');
            });
        }
    });
})();
</script>
@endpush
