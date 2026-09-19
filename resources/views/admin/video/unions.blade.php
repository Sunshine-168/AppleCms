@extends('admin.layouts.inner')
@section('title', $title ?? admin_t('ui.unions'))

@php
    $queues = $queues ?? ['all' => 0, 'on' => 0, 'off' => 0, 'pending' => 0, 'adopted' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
    $unionJsLang = [
        'show' => admin_t('ui.show'),
        'hide' => admin_t('ui.hide'),
        'hidden' => admin_t('ui.hidden'),
        'sort' => admin_t('ui.sort'),
        'status' => admin_t('ui.status'),
        'actions' => admin_t('ui.actions'),
        'edit' => admin_t('ui.edit'),
        'delete' => admin_t('ui.delete'),
        'deleted' => admin_t('ui.deleted'),
        'fail' => admin_t('ui.fail'),
        'clear_filter' => admin_t('ui.clear_filter'),
        'selected_rows' => admin_t('ui.selected_rows', ['n' => '__N__']),
        'op_fail' => admin_t('manga.op_fail'),
        'op_ok' => admin_t('manga.op_ok'),
        'col_union' => admin_t('ui.col_union'),
        'adopted' => admin_t('ui.adopted'),
        'adopt_collect' => admin_t('ui.adopt_collect'),
        'go_collect' => admin_t('ui.go_collect'),
        'add_union' => admin_t('ui.add_union'),
        'try_api' => admin_t('ui.try_api'),
        'empty_unions' => admin_t('ui.empty_unions'),
        'empty_unions_hint' => admin_t('ui.empty_unions_hint'),
        'no_match_unions' => admin_t('ui.no_match_unions'),
        'unnamed' => admin_t('ui.unnamed'),
        'no_api_yet' => admin_t('ui.no_api_yet'),
        'please_select_unions' => admin_t('ui.please_select_unions'),
        'adopt_fail' => admin_t('ui.adopt_fail'),
        'adopt_ok' => admin_t('ui.adopt_ok'),
        'confirm_batch_del_unions' => admin_t('ui.confirm_batch_del_unions'),
        'confirm_del_union' => admin_t('ui.confirm_del_union'),
    ];
@endphp

@section('plain')
<div class="card card-panel union-index list-desk">
    <div class="card-header">
        <span>{{ admin_t('ui.unions') }} <em id="union-count"></em></span>
        <div>
            <a class="btn btn-sm" href="/admin/video/unions/create">{{ admin_t('ui.add_union') }}</a>
        </div>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="union-search" onsubmit="return false;">
            <input type="hidden" name="adopted">
            <input type="text" name="name" placeholder="{{ admin_t('ui.ph_union') }}" autocomplete="off">
            <select name="status">
                <option value="">{{ admin_t('ui.status') }}</option>
                <option value="1">{{ admin_t('ui.show') }}</option>
                <option value="0">{{ admin_t('ui.hide') }}</option>
            </select>
            <button type="button" class="btn btn-sm" id="union-search-btn">{{ admin_t('ui.search') }}</button>
            <button type="reset" class="btn btn-muted btn-sm" id="union-reset-btn">{{ admin_t('ui.reset') }}</button>
        </form>
        <div class="queue-chips" id="union-queues">
            <button type="button" class="chip" data-queue="">{{ admin_t('ui.all') }}@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="1">{{ admin_t('ui.showing') }}@if($q('on') > 0)<em>{{ $q('on') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="0">{{ admin_t('ui.hidden') }}@if($q('off') > 0)<em>{{ $q('off') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="adopted" data-value="0">{{ admin_t('ui.not_adopted') }}@if($q('pending') > 0)<em>{{ $q('pending') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="adopted" data-value="1">{{ admin_t('ui.adopted') }}@if($q('adopted') > 0)<em>{{ $q('adopted') }}</em>@endif</button>
        </div>
        <p class="muted recycle-lead">{{ admin_t('ui.unions_lead') }}</p>
        <div class="batch-bar" id="union-batch" hidden>
            <strong id="union-batch-count">{{ admin_t('ui.selected_rows', ['n' => 0]) }}</strong>
            <button type="button" class="btn btn-sm" id="union-batch-adopt">{{ admin_t('ui.adopt_collect') }}</button>
            <button type="button" class="btn btn-sm" id="union-batch-on">{{ admin_t('ui.show') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="union-batch-off">{{ admin_t('ui.hide') }}</button>
            <button type="button" class="btn btn-danger btn-sm" id="union-batch-del">{{ admin_t('ui.delete') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="union-batch-clear">{{ admin_t('ui.clear_selection') }}</button>
        </div>
        <div id="union-table"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($unionJsLang);
    var QUEUE_KEYS = ['adopted'];
    var form = document.getElementById('union-search');
    var batchBar = document.getElementById('union-batch');
    var batchCount = document.getElementById('union-batch-count');
    var countEl = document.getElementById('union-count');

    function cleanWhere(data) {
        var out = {};
        Object.keys(data).forEach(function (k) { if (data[k] !== '') out[k] = data[k]; });
        return out;
    }
    function queryWhere() {
        return Object.assign({limit: 30}, cleanWhere(U.formData(form)));
    }
    function isFiltered(where) {
        return Object.keys(where || {}).some(function (k) { return k !== 'limit' && where[k] !== ''; });
    }
    function markChips() {
        var status = form.status.value;
        var adopted = form.adopted.value;
        U.qa('#union-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = false;
            if (key === '' && status === '' && adopted === '') on = true;
            else if (key === 'status' && adopted === '' && status === val) on = true;
            else if (key === 'adopted' && status === '' && adopted === val) on = true;
            chip.classList.toggle('active', on);
        });
    }
    function applyQueue(key, value) {
        QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
        form.status.value = '';
        if (key === 'status') form.status.value = value || '';
        else if (key && form[key]) form[key].value = value || '';
        runSearch();
    }
    function runSearch() {
        table.reload(queryWhere());
        markChips();
    }
    function nameHtml(d) {
        var letter = String(d.name || d.host || '?').slice(0, 1);
        var thumb = '<span class="link-thumb is-empty">' + U.escape(letter) + '</span>';
        var badge = String(d.status) === '1' ? '' : '<span class="badge badge-off">' + L.hidden + '</span>';
        var adopted = String(d.adopted) === '1' ? '<span class="badge">' + L.adopted + '</span>' : '';
        var meta = [];
        if (d.host) meta.push(d.host);
        if (d.note) meta.push(d.note);
        return '<div class="vod-cell">' + thumb + '<div><div class="entry-row-title-line"><a class="entry-row-title" href="/admin/video/unions/' + d.id + '/edit">' + U.escape(d.name || L.unnamed) + '</a> ' + badge + ' ' + adopted + '</div>'
            + '<div class="entry-row-meta">' + U.escape(meta.join(' · ') || (d.api_url || L.no_api_yet)) + '</div></div></div>';
    }

    var table = U.table({
        el: '#union-table',
        queueKeys: QUEUE_KEYS,
        countEl: countEl,
        url: '/admin/video/unions/list',
        where: queryWhere(),
        pager: false,
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>' + L.no_match_unions + '</p><p><button type="button" class="btn btn-muted btn-sm" id="union-empty-reset">' + L.clear_filter + '</button></p></div>';
            }
            return '<div class="list-empty"><p>' + L.empty_unions + '</p><p class="muted">' + L.empty_unions_hint + '</p><p><a class="btn btn-primary btn-sm" href="/admin/video/unions/create">' + L.add_union + '</a> <a class="btn btn-muted btn-sm" href="/admin/video/tools/hub">' + L.try_api + '</a></p></div>';
        },
        onDraw: function (_wrap, list) {
            var reset = document.getElementById('union-empty-reset');
            if (reset) reset.addEventListener('click', function () { form.reset(); runSearch(); });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = String(L.selected_rows || '').replace('__N__', String(ids.length));
        },
        cols: [
            {check: true, width: 36},
            {title: L.col_union, html: nameHtml},
            {key: 'sort', title: L.sort, width: 64},
            {title: L.status, width: 72, html: function (d) {
                return String(d.status) === '1' ? U.status(true, L.show) : U.status(false, L.hide);
            }},
            {title: L.actions, cls: 'actions', html: function (d) {
                var html = '';
                if (String(d.adopted) === '1') {
                    html += '<a href="/admin/video/collects" class="btn-link">' + L.go_collect + '</a>';
                } else {
                    html += '<a href="#" class="btn-link js-adopt">' + L.adopt_collect + '</a>';
                }
                html += '<a href="/admin/video/unions/' + d.id + '/edit" class="btn-link">' + L.edit + '</a>';
                html += '<a href="#" class="btn-link js-del">' + L.delete + '</a>';
                return html;
            }}
        ]
    });
    markChips();

    function selectedIds() { return table.selectedIds(); }
    function batch(action, value, confirmText) {
        var ids = selectedIds();
        if (!ids.length) { U.toast(L.please_select_unions, 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/unions/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.op_fail, 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || L.op_ok, 'ok');
        });
    }
    function adopt(ids) {
        if (!ids.length) { U.toast(L.please_select_unions, 'err'); return; }
        U.post('/admin/video/unions/adopt', {ids: ids.join(',')}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.adopt_fail, 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || L.adopt_ok, 'ok');
        });
    }

    U.on('#union-search-btn', 'click', runSearch);
    U.on('#union-reset-btn', 'click', function () { setTimeout(runSearch, 0); });
    document.getElementById('union-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#union-batch-adopt', 'click', function () { adopt(selectedIds()); });
    U.on('#union-batch-on', 'click', function () { batch('status', 1); });
    U.on('#union-batch-off', 'click', function () { batch('status', 0); });
    U.on('#union-batch-del', 'click', function () { batch('delete', '', L.confirm_batch_del_unions); });
    U.on('#union-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#union-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (a.classList.contains('js-adopt')) {
            e.preventDefault();
            if (!row) return;
            adopt([row.id]);
            return;
        }
        if (a.classList.contains('js-del')) {
            e.preventDefault();
            if (!row) return;
            if (!U.confirm(String(L.confirm_del_union || '').replace(':name', row.name || ''))) return;
            U.post('/admin/video/unions/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
                table.refresh();
                U.toast(L.deleted, 'ok');
            });
        }
    });
})();
</script>
@endpush
