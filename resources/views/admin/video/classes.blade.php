@extends('admin.layouts.inner')
@section('title', $title ?? admin_t('page.classes'))

@php
    $queues = $queues ?? ['all' => 0, 'on' => 0, 'off' => 0, 'unused' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
    $extClassJsLang = [
        'label_ext_class' => admin_t('ui.label_ext_class'),
        'sort' => admin_t('ui.sort'),
        'status' => admin_t('ui.status'),
        'actions' => admin_t('ui.actions'),
        'edit' => admin_t('ui.edit'),
        'delete' => admin_t('ui.delete'),
        'enabled' => admin_t('ui.enabled'),
        'disabled' => admin_t('ui.disabled'),
        'fail' => admin_t('ui.fail'),
        'saved' => admin_t('ui.saved'),
        'created' => admin_t('ui.created'),
        'deleted' => admin_t('ui.deleted'),
        'clear_filter' => admin_t('ui.clear_filter'),
        'videos' => admin_t('nav.videos'),
        'add_ext_class' => admin_t('ui.add_ext_class'),
        'edit_ext_class' => admin_t('ui.edit_ext_class'),
        'go_types' => admin_t('ui.go_types'),
        'selected_n' => admin_t('ui.selected_n', ['n' => '__N__']),
        'videos_used_n' => admin_t('ui.videos_used_n', ['n' => '__N__']),
        'ext_class_no_videos' => admin_t('ui.ext_class_no_videos'),
        'empty_ext_classes' => admin_t('ui.empty_ext_classes'),
        'empty_ext_classes_hint' => admin_t('ui.empty_ext_classes_hint'),
        'no_match_ext_classes' => admin_t('ui.no_match_ext_classes'),
        'please_fill_ext_class' => admin_t('ui.please_fill_ext_class'),
        'please_one_word' => admin_t('ui.please_one_word'),
        'please_select_ext_classes' => admin_t('ui.please_select_ext_classes'),
        'confirm_batch_del_ext_classes' => admin_t('ui.confirm_batch_del_ext_classes'),
        'confirm_del_ext_class' => admin_t('ui.confirm_del_ext_class', ['name' => '__NAME__']),
        'op_fail' => admin_t('manga.op_fail'),
        'op_ok' => admin_t('manga.op_ok'),
    ];
@endphp

@section('plain')
<div class="card card-panel extclass-index" id="extclass-index">
    <div class="card-header">
        <span>{{ admin_t('ui.ext_classes') }} <em id="extclass-count"></em></span>
        <div>
            <button type="button" class="btn btn-sm" id="extclass-add-btn">{{ admin_t('ui.add_ext_class') }}</button>
            <a class="btn btn-muted btn-sm" href="/admin/video/types">{{ admin_t('ui.types') }}</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/tags">{{ admin_t('ui.tags') }}</a>
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">{{ admin_t('ui.ext_classes_lead') }}</p>
        <form class="filter-bar" id="extclass-search" onsubmit="return false;">
            <input type="hidden" name="unused">
            <input type="search" name="q" placeholder="{{ admin_t('ui.ph_ext_class') }}" autocomplete="off" aria-label="{{ admin_t('ui.ext_classes') }}">
            <select name="status">
                <option value="">{{ admin_t('ui.status') }}</option>
                <option value="1">{{ admin_t('ui.enabled') }}</option>
                <option value="0">{{ admin_t('ui.disabled') }}</option>
            </select>
            <button type="button" class="btn btn-sm" id="extclass-search-btn">{{ admin_t('ui.search') }}</button>
            <button type="reset" class="btn btn-muted btn-sm" id="extclass-reset-btn">{{ admin_t('ui.reset') }}</button>
        </form>
        <div class="queue-chips" id="extclass-queues">
            <button type="button" class="chip" data-queue="">{{ admin_t('ui.all') }}@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="1">{{ admin_t('ui.enabled') }}@if($q('on') > 0)<em>{{ $q('on') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="0">{{ admin_t('ui.disabled') }}@if($q('off') > 0)<em>{{ $q('off') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="unused" data-value="1">{{ admin_t('ui.unused_videos') }}@if($q('unused') > 0)<em>{{ $q('unused') }}</em>@endif</button>
        </div>
        <p class="muted field-hint">{{ admin_t('ui.ext_classes_hint') }}</p>
        <div class="batch-bar" id="extclass-batch" hidden>
            <strong id="extclass-batch-count">{{ admin_t('ui.selected_n', ['n' => 0]) }}</strong>
            <button type="button" class="btn btn-sm" id="extclass-batch-on">{{ admin_t('ui.enabled') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="extclass-batch-off">{{ admin_t('ui.disabled') }}</button>
            <button type="button" class="btn btn-danger btn-sm" id="extclass-batch-del">{{ admin_t('ui.delete') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="extclass-batch-clear">{{ admin_t('ui.clear_selection') }}</button>
        </div>
        <div id="extclass-table"></div>
    </div>
</div>
<template id="extclass-dialog-tpl">
    <form class="admin-form">
        <input type="hidden" name="id">
        <label>{{ admin_t('ui.label_ext_class') }}</label>
        <input type="text" name="name" placeholder="{{ admin_t('ui.ph_ext_class_name') }}" required>
        <p class="muted field-hint">{{ admin_t('ui.hint_ext_class_name') }}</p>
        <label>{{ admin_t('ui.sort') }}</label>
        <input type="number" name="sort" value="0">
        <label>{{ admin_t('ui.status') }}</label>
        <select name="status">
            <option value="1">{{ admin_t('ui.enabled') }}</option>
            <option value="0">{{ admin_t('ui.disabled') }}</option>
        </select>
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($extClassJsLang, JSON_UNESCAPED_UNICODE);
    var QUEUE_KEYS = ['unused'];
    var form = document.getElementById('extclass-search');
    var batchBar = document.getElementById('extclass-batch');
    var batchCount = document.getElementById('extclass-batch-count');
    var countEl = document.getElementById('extclass-count');

    function cleanWhere(data) {
        var out = {};
        Object.keys(data).forEach(function (k) { if (data[k] !== '') out[k] = data[k]; });
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
        var unused = form.unused.value;
        U.qa('#extclass-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = false;
            if (key === '' && status === '' && unused === '') on = true;
            else if (key === 'status' && unused === '' && status === val) on = true;
            else if (key === 'unused' && status === '' && unused === val) on = true;
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
    function nameHtml(d) {
        var badge = d.is_on ? '' : '<span class="badge badge-off">' + L.disabled + '</span>';
        var used = parseInt(d.used_count, 10) || 0;
        var meta = used > 0
            ? String(L.videos_used_n || '').replace('__N__', String(used))
            : L.ext_class_no_videos;
        return '<div><div class="entry-row-title-line"><a class="entry-row-title js-edit" href="#">' + U.escape(d.name || '') + '</a> ' + badge + '</div>'
            + '<div class="entry-row-meta">' + meta + '</div></div>';
    }

    var table = U.table({
        el: '#extclass-table',
        queueKeys: QUEUE_KEYS,
        countEl: countEl,
        url: '/admin/video/classes/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>' + L.no_match_ext_classes + '</p><p><button type="button" class="btn btn-muted btn-sm" id="extclass-empty-reset">' + L.clear_filter + '</button></p></div>';
            }
            return '<div class="list-empty"><p>' + L.empty_ext_classes + '</p><p class="muted">' + L.empty_ext_classes_hint + '</p><p><button type="button" class="btn btn-primary btn-sm" id="extclass-empty-add">' + L.add_ext_class + '</button> <a class="btn btn-muted btn-sm" href="/admin/video/types">' + L.go_types + '</a></p></div>';
        },
        onDraw: function (_wrap, list) {
            var add = document.getElementById('extclass-empty-add');
            var reset = document.getElementById('extclass-empty-reset');
            if (add) add.addEventListener('click', function () { openDialog('add'); });
            if (reset) reset.addEventListener('click', function () { form.reset(); runSearch(); });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = String(L.selected_n || '').replace('__N__', String(ids.length));
        },
        cols: [
            {check: true, width: 36},
            {title: L.label_ext_class, html: nameHtml},
            {key: 'sort', title: L.sort, width: 64},
            {title: L.status, width: 72, html: function (d) {
                return d.is_on ? U.status(true, L.enabled) : U.status(false, L.disabled);
            }},
            {title: L.actions, cls: 'actions', html: function (d) {
                var used = parseInt(d.used_count, 10) || 0;
                var html = '';
                if (used > 0) html += '<a href="/admin/video" class="btn-link">' + L.videos + '</a>';
                html += '<a href="#" class="btn-link js-edit">' + L.edit + '</a>';
                html += '<a href="#" class="btn-link js-del">' + L.delete + '</a>';
                return html;
            }}
        ]
    });
    markChips();

    function openDialog(mode, row) {
        row = row || {};
        U.dialog({
            title: mode === 'edit' ? L.edit_ext_class : L.add_ext_class,
            content: document.getElementById('extclass-dialog-tpl').innerHTML,
            onOpen: function (body) {
                U.fillForm(body.querySelector('form'), {
                    id: mode === 'edit' ? (row.id || '') : '',
                    name: row.name || '',
                    sort: row.sort == null ? 0 : row.sort,
                    status: row.status == null ? '1' : String(row.status)
                });
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (!data.name) { U.toast(L.please_fill_ext_class, 'err'); return false; }
                if (/[,，]/.test(data.name)) { U.toast(L.please_one_word, 'err'); return false; }
                if (mode !== 'edit') delete data.id; else data.id = row.id;
                return U.post('/admin/video/classes/save', data).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return false; }
                    U.toast(mode === 'edit' ? L.saved : L.created, 'ok');
                    table.refresh();
                });
            }
        });
    }

    function selectedIds() { return table.selectedIds(); }
    function batch(action, value, confirmText) {
        var ids = selectedIds();
        if (!ids.length) { U.toast(L.please_select_ext_classes, 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/classes/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.op_fail, 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || L.op_ok, 'ok');
        });
    }

    U.on('#extclass-search-btn', 'click', runSearch);
    U.on('#extclass-reset-btn', 'click', function () { setTimeout(runSearch, 0); });
    U.on('#extclass-add-btn', 'click', function () { openDialog('add'); });
    document.getElementById('extclass-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#extclass-batch-on', 'click', function () { batch('status', 1); });
    U.on('#extclass-batch-off', 'click', function () { batch('status', 0); });
    U.on('#extclass-batch-del', 'click', function () { batch('delete', '', L.confirm_batch_del_ext_classes); });
    U.on('#extclass-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#extclass-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        if ((a.getAttribute('href') || '').indexOf('/admin/video') === 0 && a.getAttribute('href') !== '#') return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-edit')) openDialog('edit', row);
        if (a.classList.contains('js-del')) {
            if (!U.confirm(String(L.confirm_del_ext_class || '').replace('__NAME__', row.name || ''))) return;
            U.post('/admin/video/classes/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
                table.refresh();
                U.toast(L.deleted, 'ok');
            });
        }
    });
})();
</script>
@endpush
