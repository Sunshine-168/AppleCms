@extends('admin.layouts.inner')
@section('title', $title)

@php
    $queues = $queues ?? ['all' => 0, 'on' => 0, 'off' => 0, 'logo' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
    $linkJsLang = [
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
        'selected_rows' => admin_t('ui.selected_rows', ['n' => '__N__']),
        'op_fail' => admin_t('manga.op_fail'),
        'op_ok' => admin_t('manga.op_ok'),
        'unnamed' => admin_t('ui.unnamed'),
        'col_link' => admin_t('ui.col_link'),
        'kind_text' => admin_t('ui.kind_text'),
        'open_link' => admin_t('ui.open_link'),
        'add_friend_link' => admin_t('ui.add_friend_link'),
        'edit_friend_link' => admin_t('ui.edit_friend_link'),
        'empty_links' => admin_t('ui.empty_links'),
        'empty_links_hint' => admin_t('ui.empty_links_hint'),
        'no_match_links' => admin_t('ui.no_match_links'),
        'please_fill_site_name' => admin_t('ui.please_fill_site_name'),
        'please_fill_url' => admin_t('ui.please_fill_url'),
        'please_select_links' => admin_t('ui.please_select_links'),
        'confirm_batch_del_links' => admin_t('ui.confirm_batch_del_links'),
        'confirm_del_link' => admin_t('ui.confirm_del_link', ['name' => '__NAME__']),
    ];
@endphp

@section('plain')
<div class="card card-panel link-index">
    <div class="card-header">
        <span>{{ admin_t('ui.friend_links') }} <em id="link-count"></em></span>
        <div>
            <button type="button" class="btn btn-sm" id="link-add-btn">{{ admin_t('ui.add_friend_link') }}</button>
            <a class="btn btn-muted btn-sm" href="/admin/video/websites">{{ admin_t('ui.websites_nav') }}</a>
        </div>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="link-search" onsubmit="return false;">
            <input type="hidden" name="logo">
            <input type="text" name="name" placeholder="{{ admin_t('ui.ph_link') }}" autocomplete="off">
            <select name="status">
                <option value="">{{ admin_t('ui.status') }}</option>
                <option value="1">{{ admin_t('ui.show') }}</option>
                <option value="0">{{ admin_t('ui.hide') }}</option>
            </select>
            <button type="button" class="btn btn-sm" id="link-search-btn">{{ admin_t('ui.search') }}</button>
            <button type="reset" class="btn btn-muted btn-sm" id="link-reset-btn">{{ admin_t('ui.reset') }}</button>
        </form>
        <div class="queue-chips" id="link-queues">
            <button type="button" class="chip" data-queue="">{{ admin_t('ui.all') }}@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="1">{{ admin_t('ui.showing') }}@if($q('on') > 0)<em>{{ $q('on') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="0">{{ admin_t('ui.hidden') }}@if($q('off') > 0)<em>{{ $q('off') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="logo" data-value="1">{{ admin_t('ui.has_logo') }}@if($q('logo') > 0)<em>{{ $q('logo') }}</em>@endif</button>
        </div>
        <p class="muted recycle-lead">{{ admin_t('ui.links_lead') }}</p>
        <div class="batch-bar" id="link-batch" hidden>
            <strong id="link-batch-count">{{ admin_t('ui.selected_rows', ['n' => 0]) }}</strong>
            <button type="button" class="btn btn-sm" id="link-batch-on">{{ admin_t('ui.show') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="link-batch-off">{{ admin_t('ui.hide') }}</button>
            <button type="button" class="btn btn-danger btn-sm" id="link-batch-del">{{ admin_t('ui.delete') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="link-batch-clear">{{ admin_t('ui.clear_selection') }}</button>
        </div>
        <div id="link-table"></div>
    </div>
</div>
<template id="link-dialog-tpl">
    <form>
        <input type="hidden" name="id">
        <label>{{ admin_t('ui.label_site_name') }}</label>
        <input type="text" name="name" placeholder="{{ admin_t('ui.ph_link_name') }}">
        <p class="muted field-hint">{{ admin_t('ui.hint_link_name') }}</p>
        <label>{{ admin_t('ui.label_url') }}</label>
        <input type="text" name="url" placeholder="{{ admin_t('ui.ph_https') }}">
        <p class="muted field-hint">{{ admin_t('ui.hint_link_url') }}</p>
        <label>Logo</label>
        <div class="field-inline">
            <input type="text" name="logo" placeholder="{{ admin_t('ui.ph_link_logo') }}">
            <button type="button" class="btn btn-muted link-logo-upload-btn">{{ admin_t('ui.upload') }}</button>
        </div>
        <img class="img-preview link-logo-preview" alt="">
        <p class="muted field-hint">{{ admin_t('ui.hint_link_logo') }}</p>
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
    var L = @json($linkJsLang);
    var QUEUE_KEYS = ['logo'];
    var form = document.getElementById('link-search');
    var batchBar = document.getElementById('link-batch');
    var batchCount = document.getElementById('link-batch-count');
    var countEl = document.getElementById('link-count');

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
        var logo = form.logo.value;
        U.qa('#link-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = false;
            if (key === '' && status === '' && logo === '') on = true;
            else if (key === 'status' && logo === '' && status === val) on = true;
            else if (key === 'logo' && status === '' && logo === val) on = true;
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
        var logo = String(d.logo || '').trim();
        var letter = String(d.name || '?').slice(0, 1);
        var thumb = logo
            ? '<img class="link-thumb" src="' + U.escape(logo) + '" alt="">'
            : '<span class="link-thumb is-empty">' + U.escape(letter) + '</span>';
        var badge = String(d.status) === '1' ? '' : '<span class="badge badge-off">' + U.escape(L.hidden) + '</span>';
        var kind = U.escape(d.kind_label || L.kind_text);
        var url = String(d.url || '');
        return '<div class="vod-cell">' + thumb + '<div><div class="entry-row-title-line"><a class="entry-row-title js-edit" href="#">' + U.escape(d.name || L.unnamed) + '</a> ' + badge + '</div>'
            + '<div class="entry-row-meta">' + kind + (url ? ' · ' + U.escape(url) : '') + '</div></div></div>';
    }

    var table = U.table({
        el: '#link-table',
        queueKeys: QUEUE_KEYS,
        countEl: countEl,
        url: '/admin/video/links/list',
        where: queryWhere(),
        pager: false,
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>' + L.no_match_links + '</p><p><button type="button" class="btn btn-muted btn-sm" id="link-empty-reset">' + L.clear_filter + '</button></p></div>';
            }
            return '<div class="list-empty"><p>' + L.empty_links + '</p><p class="muted">' + L.empty_links_hint + '</p><p><button type="button" class="btn btn-primary btn-sm" id="link-empty-add">' + L.add_friend_link + '</button></p></div>';
        },
        onDraw: function (_wrap, list) {
            var add = document.getElementById('link-empty-add');
            var reset = document.getElementById('link-empty-reset');
            if (add) add.addEventListener('click', function () { openDialog('add'); });
            if (reset) reset.addEventListener('click', function () { form.reset(); runSearch(); });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = String(L.selected_rows || '').replace('__N__', String(ids.length));
        },
        cols: [
            {check: true, width: 36},
            {title: L.col_link, html: nameHtml},
            {key: 'sort', title: L.sort, width: 64},
            {title: L.status, width: 72, html: function (d) {
                return String(d.status) === '1' ? U.status(true, L.show) : U.status(false, L.hide);
            }},
            {title: L.actions, cls: 'actions', html: function (d) {
                var html = '';
                if (d.url) html += '<a href="' + U.escape(d.url) + '" target="_blank" rel="noopener noreferrer" class="btn-link">' + L.open_link + '</a>';
                html += '<a href="#" class="btn-link js-edit">' + L.edit + '</a>';
                html += '<a href="#" class="btn-link js-del">' + L.delete + '</a>';
                return html;
            }}
        ]
    });
    markChips();

    function bindLogo(formEl) {
        U.bindImageField(formEl, {
            input: 'input[name=logo]',
            btn: '.link-logo-upload-btn',
            preview: '.link-logo-preview'
        });
    }

    function openDialog(mode, row) {
        row = row || {};
        U.dialog({
            wide: true,
            title: mode === 'edit' ? L.edit_friend_link : L.add_friend_link,
            content: document.getElementById('link-dialog-tpl').innerHTML,
            onOpen: function (body) {
                var formEl = body.querySelector('form');
                U.fillForm(formEl, {
                    id: mode === 'edit' ? (row.id || '') : '',
                    name: row.name || '',
                    url: row.url || '',
                    logo: row.logo || '',
                    sort: row.sort == null ? 0 : row.sort,
                    status: row.status == null ? '1' : String(row.status)
                });
                bindLogo(formEl);
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (!data.name) { U.toast(L.please_fill_site_name, 'err'); return false; }
                if (!data.url) { U.toast(L.please_fill_url, 'err'); return false; }
                if (mode !== 'edit') delete data.id; else data.id = row.id;
                return U.post('/admin/video/links/save', data).then(function (res) {
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
        if (!ids.length) { U.toast(L.please_select_links, 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/links/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.op_fail, 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || L.op_ok, 'ok');
        });
    }

    U.on('#link-search-btn', 'click', runSearch);
    U.on('#link-reset-btn', 'click', function () { setTimeout(runSearch, 0); });
    U.on('#link-add-btn', 'click', function () { openDialog('add'); });
    document.getElementById('link-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#link-batch-on', 'click', function () { batch('status', 1); });
    U.on('#link-batch-off', 'click', function () { batch('status', 0); });
    U.on('#link-batch-del', 'click', function () { batch('delete', '', L.confirm_batch_del_links); });
    U.on('#link-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#link-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        if (a.target === '_blank') return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-edit')) openDialog('edit', row);
        if (a.classList.contains('js-del')) {
            if (!U.confirm(String(L.confirm_del_link || '').replace('__NAME__', row.name || ''))) return;
            U.post('/admin/video/links/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
                table.refresh();
                U.toast(L.deleted, 'ok');
            });
        }
    });
})();
</script>
@endpush
