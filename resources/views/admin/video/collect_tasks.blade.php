@extends('admin.layouts.inner')
@section('title', $title)

@php
    $queues = $queues ?? ['all' => 0, 'on' => 0, 'off' => 0, 'never' => 0, 'fail' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
    $sourceId = (int) ($sourceId ?? 0);
    $sourceName = trim((string) ($sourceName ?? ''));
    $sourceChip = $sourceName !== '' ? $sourceName : ($sourceId > 0 ? admin_t('ui.collect_source_n', ['id' => $sourceId]) : '');
    $ctaskJsLang = [
        'enabled' => admin_t('ui.enabled'),
        'disabled' => admin_t('ui.disabled'),
        'edit' => admin_t('ui.edit'),
        'delete' => admin_t('ui.delete'),
        'actions' => admin_t('ui.actions'),
        'fail' => admin_t('ui.fail'),
        'saved' => admin_t('ui.saved'),
        'deleted' => admin_t('ui.deleted'),
        'clear_filter' => admin_t('ui.clear_filter'),
        'clear_selection' => admin_t('ui.clear_selection'),
        'selected_rows' => admin_t('ui.selected_rows', ['n' => '__N__']),
        'op_fail' => admin_t('manga.op_fail'),
        'op_ok' => admin_t('manga.op_ok'),
        'collect_done' => admin_t('ui.collect_done'),
        'add_collect_task' => admin_t('ui.add_collect_task'),
        'chip_never_run' => admin_t('ui.chip_never_run'),
        'chip_last_fail' => admin_t('ui.chip_last_fail'),
        'collect_source_n' => admin_t('ui.collect_source_n', ['id' => '__ID__']),
        'unnamed_task' => admin_t('ui.unnamed_task'),
        'source_deleted' => admin_t('ui.source_deleted'),
        'source_off' => admin_t('ui.source_off'),
        'pages_n' => admin_t('ui.pages_n', ['n' => '__N__']),
        'next_run' => admin_t('ui.next_run', ['time' => '__TIME__']),
        'col_task' => admin_t('ui.col_task'),
        'col_last_run' => admin_t('ui.col_last_run'),
        'run_now' => admin_t('ui.run_now'),
        'today_at' => admin_t('ui.today_at', ['time' => '__TIME__']),
        'yesterday_at' => admin_t('ui.yesterday_at', ['time' => '__TIME__']),
        'empty_collect_tasks' => admin_t('ui.empty_collect_tasks'),
        'empty_collect_tasks_hint' => admin_t('ui.empty_collect_tasks_hint'),
        'no_match_collect_tasks' => admin_t('ui.no_match_collect_tasks'),
        'please_select_tasks' => admin_t('ui.please_select_tasks'),
        'confirm_batch_del_tasks' => admin_t('ui.confirm_batch_del_tasks'),
        'confirm_del_collect_task' => admin_t('ui.confirm_del_collect_task'),
        'due_checked' => admin_t('ui.due_checked'),
    ];
@endphp

@section('plain')
<div class="card card-panel collect-task-index desk-board">
    <div class="card-header">
        <span>{{ admin_t('ui.collect_tasks') }} <em id="ctask-count"></em></span>
        <div>
            <a class="btn btn-sm" href="/admin/video/collect_tasks/create">{{ admin_t('ui.add_collect_task') }}</a>
            <button type="button" class="btn btn-muted btn-sm" id="ctask-due-btn" hidden>{{ admin_t('ui.run_due_tasks') }}</button>
        </div>
    </div>
    <div class="card-body">
        @include('admin.partials.schedule-kind-tabs', ['tab' => 'collect'])
        <form class="filter-bar" id="ctask-search" onsubmit="return false;">
            <input type="hidden" name="status">
            <input type="hidden" name="never">
            <input type="hidden" name="failed">
            <input type="hidden" name="collect_source_id" value="{{ $sourceId > 0 ? $sourceId : '' }}">
            <input type="search" name="q" placeholder="{{ admin_t('ui.ph_collect_task') }}" autocomplete="off" aria-label="{{ admin_t('ui.collect_tasks') }}">
            <button type="button" class="btn btn-sm" id="ctask-search-btn">{{ admin_t('ui.search') }}</button>
            <button type="reset" class="btn btn-muted btn-sm" id="ctask-reset-btn">{{ admin_t('ui.reset') }}</button>
        </form>
        <div class="queue-chips" id="ctask-queues">
            <button type="button" class="chip" data-queue="">{{ admin_t('ui.all') }}@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="1">{{ admin_t('ui.enabled') }}@if($q('on') > 0)<em>{{ $q('on') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="0">{{ admin_t('ui.disabled') }}@if($q('off') > 0)<em>{{ $q('off') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="never" data-value="1">{{ admin_t('ui.chip_never_run') }}@if($q('never') > 0)<em>{{ $q('never') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="failed" data-value="1">{{ admin_t('ui.chip_last_fail') }}@if($q('fail') > 0)<em>{{ $q('fail') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="source" id="ctask-source-chip" @if($sourceId < 1) hidden @endif>{{ $sourceChip }}</button>
        </div>
        <p class="muted recycle-lead">
            {{ admin_t('ui.collect_tasks_lead_before') }}<a href="/admin/video/collects">{{ admin_t('ui.collect_tasks_lead_mid') }}</a>{{ admin_t('ui.collect_tasks_lead_after') }}
        </p>
        <div class="batch-bar" id="ctask-batch" hidden>
            <strong id="ctask-batch-count">{{ admin_t('ui.selected_rows', ['n' => 0]) }}</strong>
            <button type="button" class="btn btn-sm" id="ctask-batch-on">{{ admin_t('ui.enabled') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="ctask-batch-off">{{ admin_t('ui.disabled') }}</button>
            <button type="button" class="btn btn-danger btn-sm" id="ctask-batch-del">{{ admin_t('ui.delete') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="ctask-batch-clear">{{ admin_t('ui.clear_selection') }}</button>
        </div>
        <div id="ctask-table" class="desk-table"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($ctaskJsLang, JSON_UNESCAPED_UNICODE);
    var form = document.getElementById('ctask-search');
    var batchBar = document.getElementById('ctask-batch');
    var batchCount = document.getElementById('ctask-batch-count');
    var countEl = document.getElementById('ctask-count');
    var sourceChip = document.getElementById('ctask-source-chip');

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
        var status = form.status.value;
        var never = form.never.value;
        var failed = form.failed.value;
        var source = form.collect_source_id.value;
        U.qa('#ctask-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = false;
            if (key === 'source') on = !!source;
            else if (key === '' && status === '' && never === '' && failed === '') on = true;
            else if (key === 'status' && never === '' && failed === '' && status === val) on = true;
            else if (key === 'never' && never === '1' && status === '' && failed === '') on = true;
            else if (key === 'failed' && failed === '1' && status === '' && never === '') on = true;
            chip.classList.toggle('active', on);
        });
    }
    function applyQueue(key, value) {
        if (key === 'source') {
            setSource('', '');
        } else {
            form.status.value = '';
            form.never.value = '';
            form.failed.value = '';
            if (key === 'status') form.status.value = value || '';
            if (key === 'never') form.never.value = '1';
            if (key === 'failed') form.failed.value = '1';
        }
        runSearch();
    }
    function runSearch() {
        table.reload(queryWhere());
        markChips();
    }
    function fmtTime(ts) {
        ts = parseInt(ts, 10) || 0;
        if (!ts) return L.chip_never_run || '';
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
        var name = d.name || L.unnamed_task;
        var badges = [];
        if (parseInt(d.status, 10) !== 1) badges.push('<span class="badge badge-off">' + U.escape(L.disabled) + '</span>');
        else badges.push('<span class="badge badge-ok">' + U.escape(L.enabled) + '</span>');
        if (parseInt(d.source_missing, 10) === 1) badges.push('<span class="badge badge-warn">' + U.escape(L.source_deleted) + '</span>');
        else if (parseInt(d.source_off, 10) === 1) badges.push('<span class="badge badge-off">' + U.escape(L.source_off) + '</span>');
        if (parseInt(d.never, 10) === 1) badges.push('<span class="badge badge-search">' + U.escape(L.chip_never_run) + '</span>');
        else if (parseInt(d.last_ok, 10) !== 1) badges.push('<span class="badge badge-warn">' + U.escape(L.chip_last_fail) + '</span>');
        var meta = [];
        if (d.source_name) meta.push('<a href="#" class="js-source">' + U.escape(d.source_name) + '</a>');
        if (d.cron_label) meta.push(U.escape(d.cron_label));
        if (d.hours_label) meta.push(U.escape(d.hours_label));
        meta.push(U.escape(String(L.pages_n || '').replace('__N__', String(d.pages || 1))));
        if (d.next_run_text && parseInt(d.status, 10) === 1) meta.push(U.escape(String(L.next_run || '').replace('__TIME__', d.next_run_text)));
        if (d.last_msg) meta.push(U.escape(d.last_msg));
        return '<div class="entry-row-title-line"><a class="entry-row-title" href="/admin/video/collect_tasks/' + encodeURIComponent(d.id || '') + '/edit">' + U.escape(name) + '</a> ' + badges.join(' ') + '</div>'
            + '<div class="entry-row-meta">' + (meta.join(' · ') || '—') + '</div>';
    }

    var table = U.table({
        el: '#ctask-table',
        countEl: countEl,
        url: '/admin/video/collect_tasks/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>' + L.no_match_collect_tasks + '</p><p><button type="button" class="btn btn-muted btn-sm" id="ctask-empty-reset">' + L.clear_filter + '</button></p></div>';
            }
            return '<div class="list-empty"><p>' + L.empty_collect_tasks + '</p><p class="muted">' + L.empty_collect_tasks_hint + '</p><p><a class="btn btn-primary btn-sm" href="/admin/video/collect_tasks/create">' + L.add_collect_task + '</a></p></div>';
        },
        onDraw: function (wrap, list) {
            var dueBtn = document.getElementById('ctask-due-btn');
            if (dueBtn) dueBtn.hidden = !list.length;
            U.qa('tbody tr[data-idx]', wrap).forEach(function (tr) {
                var d = list[parseInt(tr.getAttribute('data-idx'), 10)];
                if (!d) return;
                if (parseInt(d.status, 10) !== 1) tr.classList.add('is-off');
                if (parseInt(d.never, 10) !== 1 && parseInt(d.last_ok, 10) !== 1) tr.classList.add('is-fail');
            });
            var reset = document.getElementById('ctask-empty-reset');
            if (reset) reset.addEventListener('click', function () {
                form.reset();
                form.status.value = '';
                form.never.value = '';
                form.failed.value = '';
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
            {title: L.col_task, html: titleHtml},
            {title: L.col_last_run, width: 120, html: function (d) { return fmtTime(d.last_run_at); }},
            {title: L.actions, cls: 'actions', html: function (d) {
                return '<a href="#" class="btn-link js-run">' + L.run_now + '</a>'
                    + '<a class="btn-link" href="/admin/video/collect_tasks/' + encodeURIComponent(d.id || '') + '/edit">' + L.edit + '</a>'
                    + '<a href="#" class="btn-link js-del">' + L.delete + '</a>';
            }}
        ]
    });
    markChips();

    function selectedIds() { return table.selectedIds(); }
    function batch(action, value, confirmText) {
        var ids = selectedIds();
        if (!ids.length) { U.toast(L.please_select_tasks, 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/collect_tasks/batch', {ids: ids.join(','), action: action, value: value || ''}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.op_fail, 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || L.op_ok, 'ok');
        });
    }

    U.on('#ctask-search-btn', 'click', runSearch);
    U.on('#ctask-reset-btn', 'click', function () {
        setTimeout(function () {
            form.status.value = '';
            form.never.value = '';
            form.failed.value = '';
            setSource('', '');
            runSearch();
        }, 0);
    });
    U.on('#ctask-due-btn', 'click', function () {
        U.loading(true);
        U.post('/admin/video/collect-due', {}).then(function (res) {
            U.loading(false);
            table.refresh();
            U.toast((res && res.msg) || L.due_checked, res && res.code === 0 ? 'ok' : 'err');
        });
    });
    document.getElementById('ctask-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#ctask-batch-on', 'click', function () { batch('status', 1); });
    U.on('#ctask-batch-off', 'click', function () { batch('status', 0); });
    U.on('#ctask-batch-del', 'click', function () { batch('delete', '', L.confirm_batch_del_tasks); });
    U.on('#ctask-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#ctask-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        var href = a.getAttribute('href') || '';
        if (href.indexOf('/admin/video/collect_tasks/') === 0 || href.indexOf('/admin/video/collects') === 0) return;
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
        if (a.classList.contains('js-run')) {
            U.loading(true);
            U.post('/admin/video/collect_tasks/run', {id: row.id}).then(function (res) {
                U.loading(false);
                table.refresh();
                U.toast((res && res.msg) || L.collect_done, res && res.code === 0 ? 'ok' : 'err');
            });
            return;
        }
        if (a.classList.contains('js-del')) {
            if (!U.confirm(L.confirm_del_collect_task)) return;
            U.post('/admin/video/collect_tasks/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
                table.refresh();
                U.toast(L.deleted, 'ok');
            });
        }
    });
})();
</script>
@endpush
