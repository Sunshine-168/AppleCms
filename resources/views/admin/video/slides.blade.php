@extends('admin.layouts.inner')
@section('title', $title)

@php
    $queues = $queues ?? ['all' => 0, 'home' => 0, 'play' => 0, 'hidden' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
    $slideJsLang = [
        'status' => admin_t('ui.status'),
        'actions' => admin_t('ui.actions'),
        'edit' => admin_t('ui.edit'),
        'delete' => admin_t('ui.delete'),
        'sort' => admin_t('ui.sort'),
        'show' => admin_t('ui.show'),
        'hide' => admin_t('ui.hide'),
        'hidden' => admin_t('ui.hidden'),
        'fail' => admin_t('ui.fail'),
        'saved' => admin_t('ui.saved'),
        'created' => admin_t('ui.created'),
        'deleted' => admin_t('ui.deleted'),
        'clear_filter' => admin_t('ui.clear_filter'),
        'selected_pics' => admin_t('ui.selected_pics', ['n' => '__N__']),
        'op_fail' => admin_t('manga.op_fail'),
        'op_ok' => admin_t('manga.op_ok'),
        'unnamed' => admin_t('ui.unnamed'),
        'please_fill_name' => admin_t('ui.please_fill_name'),
        'links_short' => admin_t('ui.links_short'),
        'no_pic' => admin_t('ui.no_pic'),
        'col_slide' => admin_t('ui.col_slide'),
        'add_slide' => admin_t('ui.add_slide'),
        'edit_slide' => admin_t('ui.edit_slide'),
        'empty_slides' => admin_t('ui.empty_slides'),
        'empty_slides_hint' => admin_t('ui.empty_slides_hint'),
        'no_match_slides' => admin_t('ui.no_match_slides'),
        'please_upload_pic' => admin_t('ui.please_upload_pic'),
        'please_select_slides' => admin_t('ui.please_select_slides'),
        'please_pick_slot' => admin_t('ui.please_pick_slot'),
        'confirm_batch_del_slides' => admin_t('ui.confirm_batch_del_slides'),
        'confirm_del_slide' => admin_t('ui.confirm_del_slide', ['name' => '__NAME__']),
    ];
@endphp

@section('plain')
<div class="card card-panel slide-index">
    <div class="card-header">
        <span>{{ admin_t('ui.slides') }} <em id="slide-count"></em></span>
        <button type="button" class="btn btn-sm" id="slide-add-btn">{{ admin_t('ui.add_slide') }}</button>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="slide-search" onsubmit="return false;">
            <input type="hidden" name="slot">
            <input type="text" name="name" placeholder="{{ admin_t('ui.ph_name') }}" autocomplete="off">
            <select name="status">
                <option value="">{{ admin_t('ui.status') }}</option>
                <option value="1">{{ admin_t('ui.show') }}</option>
                <option value="0">{{ admin_t('ui.hide') }}</option>
            </select>
            <button type="button" class="btn btn-sm" id="slide-search-btn">{{ admin_t('ui.search') }}</button>
            <button type="reset" class="btn btn-muted btn-sm" id="slide-reset-btn">{{ admin_t('ui.reset') }}</button>
        </form>
        <div class="queue-chips" id="slide-queues">
            <button type="button" class="chip" data-queue="">{{ admin_t('ui.all') }}@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="slot" data-value="home">{{ admin_t('ui.slot_home') }}@if($q('home') > 0)<em>{{ $q('home') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="slot" data-value="play">{{ admin_t('ui.slot_play') }}@if($q('play') > 0)<em>{{ $q('play') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="0">{{ admin_t('ui.hidden') }}@if($q('hidden') > 0)<em>{{ $q('hidden') }}</em>@endif</button>
        </div>
        <p class="muted recycle-lead">{{ admin_t('ui.slides_lead') }}</p>
        <div class="batch-bar" id="slide-batch" hidden>
            <strong id="slide-batch-count">{{ admin_t('ui.selected_pics', ['n' => 0]) }}</strong>
            <button type="button" class="btn btn-sm" id="slide-batch-on">{{ admin_t('ui.show') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="slide-batch-off">{{ admin_t('ui.hide') }}</button>
            <select id="slide-batch-slot" class="batch-select" aria-label="{{ admin_t('ui.move_slot') }}">
                <option value="">{{ admin_t('ui.move_slot') }}</option>
                <option value="home">{{ admin_t('ui.slot_home') }}</option>
                <option value="play">{{ admin_t('ui.slot_play') }}</option>
            </select>
            <button type="button" class="btn btn-muted btn-sm" id="slide-batch-move">{{ admin_t('ui.move') }}</button>
            <button type="button" class="btn btn-danger btn-sm" id="slide-batch-del">{{ admin_t('ui.delete') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="slide-batch-clear">{{ admin_t('ui.clear_selection') }}</button>
        </div>
        <div id="slide-table"></div>
    </div>
</div>
<template id="slide-dialog-tpl">
    <form class="admin-form">
        <input type="hidden" name="id">
        <label>{{ admin_t('ui.name') }}</label>
        <input type="text" name="name" placeholder="{{ admin_t('ui.ph_slide_name') }}">
        <p class="muted field-hint">{{ admin_t('ui.hint_slide_name') }}</p>
        <label>{{ admin_t('ui.image') }}</label>
        <div class="field-inline">
            <input type="text" name="pic" placeholder="{{ admin_t('ui.ph_pic') }}">
            <button type="button" class="btn btn-muted slide-pic-upload-btn">{{ admin_t('ui.upload') }}</button>
        </div>
        <img class="img-preview slide-pic-preview" alt="">
        <label>{{ admin_t('ui.links_short') }}</label>
        <input type="text" name="url" placeholder="{{ admin_t('ui.ph_slide_url') }}">
        <label>{{ admin_t('ui.label_slot') }}</label>
        <select name="slot">
            <option value="home">{{ admin_t('ui.slot_home') }}</option>
            <option value="play">{{ admin_t('ui.slot_play') }}</option>
        </select>
        <p class="muted field-hint">{{ admin_t('ui.hint_slide_slot') }}</p>
        <div class="admin-dialog-grid">
            <div>
                <label>{{ admin_t('ui.sort') }}</label>
                <input type="number" name="sort" value="0">
            </div>
            <div>
                <label>{{ admin_t('ui.status') }}</label>
                <select name="status">
                    <option value="1">{{ admin_t('ui.show') }}</option>
                    <option value="0">{{ admin_t('ui.hide') }}</option>
                </select>
            </div>
        </div>
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($slideJsLang);
    var QUEUE_KEYS = ['slot'];
    var form = document.getElementById('slide-search');
    var batchBar = document.getElementById('slide-batch');
    var batchCount = document.getElementById('slide-batch-count');
    var countEl = document.getElementById('slide-count');

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
        var slot = form.slot.value;
        U.qa('#slide-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = false;
            if (key === '' && status === '' && slot === '') on = true;
            else if (key === 'slot' && status === '' && slot === val) on = true;
            else if (key === 'status' && slot === '' && status === val) on = true;
            chip.classList.toggle('active', on);
        });
    }
    function applyQueue(key, value) {
        QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
        if (key === 'status') form.status.value = value || '';
        else {
            form.status.value = '';
            if (key && form[key]) form[key].value = value || '';
        }
        runSearch();
    }
    function runSearch() {
        table.reload(queryWhere());
        markChips();
    }
    function nameHtml(d) {
        var pic = String(d.pic || '').trim();
        var thumb = pic
            ? '<img class="slide-thumb" src="' + U.escape(pic) + '" alt="">'
            : '<span class="slide-thumb is-empty">' + U.escape(L.no_pic) + '</span>';
        var badge = String(d.status) === '1' ? '' : '<span class="badge badge-off">' + U.escape(L.hidden) + '</span>';
        var meta = U.escape(d.slot_label || d.slot || '');
        if (d.url) meta += ' · ' + U.escape(d.url);
        return '<div class="vod-cell">' + thumb + '<div><div class="entry-row-title-line"><a class="entry-row-title js-edit" href="#">' + U.escape(d.name || L.unnamed) + '</a> ' + badge + '</div>'
            + '<div class="entry-row-meta">' + meta + '</div></div></div>';
    }

    var table = U.table({
        el: '#slide-table',
        queueKeys: QUEUE_KEYS,
        countEl: countEl,
        url: '/admin/video/slides/list',
        where: queryWhere(),
        pager: false,
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>' + L.no_match_slides + '</p><p><button type="button" class="btn btn-muted btn-sm" id="slide-empty-reset">' + L.clear_filter + '</button></p></div>';
            }
            return '<div class="list-empty"><p>' + L.empty_slides + '</p><p class="muted">' + L.empty_slides_hint + '</p><p><button type="button" class="btn btn-primary btn-sm" id="slide-empty-add">' + L.add_slide + '</button></p></div>';
        },
        onDraw: function (_wrap, list) {
            var add = document.getElementById('slide-empty-add');
            var reset = document.getElementById('slide-empty-reset');
            if (add) add.addEventListener('click', function () { openDialog('add'); });
            if (reset) reset.addEventListener('click', function () { form.reset(); runSearch(); });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = String(L.selected_pics || '').replace('__N__', String(ids.length));
        },
        cols: [
            {check: true, width: 36},
            {title: L.col_slide, html: nameHtml},
            {key: 'sort', title: L.sort, width: 64},
            {title: L.status, width: 72, html: function (d) {
                return String(d.status) === '1' ? U.status(true, L.show) : U.status(false, L.hide);
            }},
            {title: L.actions, cls: 'actions', html: function (d) {
                var html = '';
                if (d.url) html += '<a href="' + U.escape(d.url) + '" target="_blank" rel="noopener" class="btn-link">' + L.links_short + '</a>';
                html += '<a href="#" class="btn-link js-edit">' + L.edit + '</a>';
                html += '<a href="#" class="btn-link js-del">' + L.delete + '</a>';
                return html;
            }}
        ]
    });
    markChips();

    function bindPic(formEl) {
        U.bindImageField(formEl, {
            input: 'input[name=pic]',
            btn: '.slide-pic-upload-btn',
            preview: '.slide-pic-preview'
        });
    }

    function openDialog(mode, row) {
        row = row || {};
        U.dialog({
            wide: true,
            title: mode === 'edit' ? L.edit_slide : L.add_slide,
            content: document.getElementById('slide-dialog-tpl').innerHTML,
            onOpen: function (body) {
                var formEl = body.querySelector('form');
                U.fillForm(formEl, {
                    id: mode === 'edit' ? (row.id || '') : '',
                    name: row.name || '',
                    pic: row.pic || '',
                    url: row.url || '',
                    slot: row.slot || 'home',
                    sort: row.sort == null ? 0 : row.sort,
                    status: row.status == null ? '1' : String(row.status)
                });
                bindPic(formEl);
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (!data.name) { U.toast(L.please_fill_name, 'err'); return false; }
                if (!data.pic) { U.toast(L.please_upload_pic, 'err'); return false; }
                if (mode !== 'edit') delete data.id; else data.id = row.id;
                return U.post('/admin/video/slides/save', data).then(function (res) {
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
        if (!ids.length) { U.toast(L.please_select_slides, 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/slides/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.op_fail, 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || L.op_ok, 'ok');
        });
    }

    U.on('#slide-search-btn', 'click', runSearch);
    U.on('#slide-reset-btn', 'click', function () { setTimeout(runSearch, 0); });
    U.on('#slide-add-btn', 'click', function () { openDialog('add'); });
    document.getElementById('slide-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#slide-batch-on', 'click', function () { batch('status', 1); });
    U.on('#slide-batch-off', 'click', function () { batch('status', 0); });
    U.on('#slide-batch-move', 'click', function () {
        var val = document.getElementById('slide-batch-slot').value;
        if (!val) { U.toast(L.please_pick_slot, 'err'); return; }
        batch('slot', val);
    });
    U.on('#slide-batch-del', 'click', function () { batch('delete', '', L.confirm_batch_del_slides); });
    U.on('#slide-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#slide-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        if (a.target === '_blank') return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-edit')) openDialog('edit', row);
        if (a.classList.contains('js-del')) {
            if (!U.confirm(String(L.confirm_del_slide || '').replace('__NAME__', row.name || ''))) return;
            U.post('/admin/video/slides/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
                table.refresh();
                U.toast(L.deleted, 'ok');
            });
        }
    });
})();
</script>
@endpush
