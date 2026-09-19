@extends('admin.layouts.inner')
@section('title', $title ?? admin_t('page.downloaders'))

@php
    $queues = $queues ?? ['all' => 0, 'on' => 0, 'off' => 0, 'tpl' => 0, 'prefix' => 0, 'empty' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
    $downerJsLang = [
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
        'selected_n' => admin_t('ui.selected_n', ['n' => '__N__']),
        'op_fail' => admin_t('manga.op_fail'),
        'op_ok' => admin_t('manga.op_ok'),
        'name' => admin_t('ui.name'),
        'slug' => admin_t('ui.slug'),
        'add_downloader' => admin_t('ui.add_downloader'),
        'edit_downloader' => admin_t('ui.edit_downloader'),
        'no_sources_yet' => admin_t('ui.no_sources_yet'),
        'sources_n' => admin_t('ui.sources_n', ['n' => '__N__']),
        'empty_downloaders' => admin_t('ui.empty_downloaders'),
        'empty_downloaders_hint' => admin_t('ui.empty_downloaders_hint'),
        'go_players' => admin_t('ui.go_players'),
        'no_match_downloaders' => admin_t('ui.no_match_downloaders'),
        'please_fill_name' => admin_t('ui.please_fill_name'),
        'please_fill_code' => admin_t('ui.please_fill_code'),
        'please_select_downloaders' => admin_t('ui.please_select_downloaders'),
        'confirm_batch_del_downloaders' => admin_t('ui.confirm_batch_del_downloaders'),
        'confirm_del_downloader' => admin_t('ui.confirm_del_downloader', ['name' => '__NAME__']),
        'please_fill_try_url' => admin_t('ui.please_fill_try_url'),
        'col_usage' => admin_t('ui.col_usage'),
    ];
@endphp

@section('plain')
<div class="card card-panel downer-index" id="downer-index">
    <div class="card-header">
        <span>{{ admin_t('ui.downloaders') }} <em id="downer-count"></em></span>
        <div>
            <button type="button" class="btn btn-sm" id="downer-add-btn">{{ admin_t('ui.add_downloader') }}</button>
            <a class="btn btn-muted btn-sm" href="/admin/video/players">{{ admin_t('ui.players') }}</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/servers">{{ admin_t('ui.servers') }}</a>
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">{{ admin_t('ui.downloaders_lead') }}</p>
        <form class="filter-bar" id="downer-search" onsubmit="return false;">
            <input type="hidden" name="kind">
            <input type="search" name="q" placeholder="{{ admin_t('ui.ph_downloader') }}" autocomplete="off" aria-label="{{ admin_t('ui.downloaders') }}">
            <select name="status">
                <option value="">{{ admin_t('ui.status') }}</option>
                <option value="1">{{ admin_t('ui.enabled') }}</option>
                <option value="0">{{ admin_t('ui.disabled') }}</option>
            </select>
            <button type="button" class="btn btn-sm" id="downer-search-btn">{{ admin_t('ui.search') }}</button>
            <button type="reset" class="btn btn-muted btn-sm" id="downer-reset-btn">{{ admin_t('ui.reset') }}</button>
        </form>
        <div class="queue-chips" id="downer-queues">
            <button type="button" class="chip" data-queue="">{{ admin_t('ui.all') }}@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="1">{{ admin_t('ui.enabled') }}@if($q('on') > 0)<em>{{ $q('on') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="0">{{ admin_t('ui.disabled') }}@if($q('off') > 0)<em>{{ $q('off') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="kind" data-value="tpl">{{ admin_t('ui.tpl') }}@if($q('tpl') > 0)<em>{{ $q('tpl') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="kind" data-value="prefix">{{ admin_t('ui.prefix') }}@if($q('prefix') > 0)<em>{{ $q('prefix') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="kind" data-value="empty">{{ admin_t('ui.empty_tpl') }}@if($q('empty') > 0)<em>{{ $q('empty') }}</em>@endif</button>
        </div>
        <form class="filter-bar downer-try-bar" id="downer-try" onsubmit="return false;">
            <input type="text" name="code" placeholder="{{ admin_t('ui.ph_try_downer_code') }}" autocomplete="off" aria-label="{{ admin_t('ui.try_downer_code') }}">
            <input type="text" name="url" placeholder="{{ admin_t('ui.ph_try_downer_url') }}" autocomplete="off" aria-label="{{ admin_t('ui.try_down_url') }}">
            <input type="number" name="video_id" placeholder="{{ admin_t('ui.label_video_id') }}" min="0" aria-label="{{ admin_t('ui.label_video_id') }}">
            <button type="button" class="btn btn-muted btn-sm" id="downer-try-btn">{{ admin_t('ui.try_once') }}</button>
            <span class="muted" id="downer-try-out"></span>
        </form>
        <p class="muted field-hint">{{ admin_t('ui.hint_downer_try') }}</p>
        <div class="batch-bar" id="downer-batch" hidden>
            <strong id="downer-batch-count">{{ admin_t('ui.selected_n', ['n' => 0]) }}</strong>
            <button type="button" class="btn btn-sm" id="downer-batch-on">{{ admin_t('ui.enabled') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="downer-batch-off">{{ admin_t('ui.disabled') }}</button>
            <button type="button" class="btn btn-danger btn-sm" id="downer-batch-del">{{ admin_t('ui.delete') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="downer-batch-clear">{{ admin_t('ui.clear_selection') }}</button>
        </div>
        <div id="downer-table"></div>
    </div>
</div>
<template id="downer-dialog-tpl">
    <form>
        <input type="hidden" name="id">
        <label>{{ admin_t('ui.name') }}</label>
        <input class="entry-title" type="text" name="name" placeholder="{{ admin_t('ui.ph_downer_name') }}" required autofocus>
        <label>{{ admin_t('ui.slug') }}</label>
        <input type="text" name="code" placeholder="{{ admin_t('ui.ph_downer_code') }}" required>
        <p class="muted field-hint">{{ admin_t('ui.hint_downer_code') }}</p>
        <label>{{ admin_t('ui.tpl') }}</label>
        <textarea name="parse" rows="4" placeholder="{{ admin_t('ui.ph_downer_parse') }}"></textarea>
        <p class="muted field-hint">{{ admin_t('ui.hint_downer_parse') }}</p>
        <div class="admin-dialog-grid">
            <div>
                <label>{{ admin_t('ui.sort') }}</label>
                <input type="number" name="sort" value="0">
            </div>
            <div>
                <label>{{ admin_t('ui.status') }}</label>
                <select name="status">
                    <option value="1">{{ admin_t('ui.enabled') }}</option>
                    <option value="0">{{ admin_t('ui.disabled') }}</option>
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
    var L = @json($downerJsLang);
    var QUEUE_KEYS = ['kind'];
    var form = document.getElementById('downer-search');
    var tryForm = document.getElementById('downer-try');
    var tryOut = document.getElementById('downer-try-out');
    var batchBar = document.getElementById('downer-batch');
    var batchCount = document.getElementById('downer-batch-count');
    var countEl = document.getElementById('downer-count');

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
        var kind = form.kind.value;
        U.qa('#downer-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = false;
            if (key === '' && status === '' && kind === '') on = true;
            else if (key === 'status' && kind === '' && status === val) on = true;
            else if (key === 'kind' && status === '' && kind === val) on = true;
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
        var badge = d.is_on ? '' : '<span class="badge badge-off">' + L.disabled + '</span>';
        var kind = U.escape(d.parse_kind_label || '');
        var used = parseInt(d.source_count, 10) || 0;
        var meta = [kind, used > 0 ? String(L.sources_n || '').replace('__N__', String(used)) : L.no_sources_yet];
        if (d.parse_preview) meta.push(U.escape(d.parse_preview));
        return '<div><div class="entry-row-title-line"><a class="entry-row-title js-edit" href="#">' + U.escape(d.name || '') + '</a> ' + badge + '</div>'
            + '<div class="entry-row-meta">' + meta.join(' · ') + '</div></div>';
    }

    var table = U.table({
        el: '#downer-table',
        queueKeys: QUEUE_KEYS,
        countEl: countEl,
        url: '/admin/video/downloaders/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>' + L.no_match_downloaders + '</p><p><button type="button" class="btn btn-muted btn-sm" id="downer-empty-reset">' + L.clear_filter + '</button></p></div>';
            }
            return '<div class="list-empty"><p>' + L.empty_downloaders + '</p><p class="muted">' + L.empty_downloaders_hint + '</p><p><button type="button" class="btn btn-primary btn-sm" id="downer-empty-add">' + L.add_downloader + '</button> <a class="btn btn-muted btn-sm" href="/admin/video/players">' + L.go_players + '</a></p></div>';
        },
        onDraw: function (_wrap, list) {
            var add = document.getElementById('downer-empty-add');
            var reset = document.getElementById('downer-empty-reset');
            if (add) add.addEventListener('click', function () { openDialog('add'); });
            if (reset) reset.addEventListener('click', function () { form.reset(); runSearch(); });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = String(L.selected_n || '').replace('__N__', String(ids.length));
        },
        cols: [
            {check: true, width: 36},
            {title: L.name, html: nameHtml},
            {title: L.slug, width: 100, html: function (d) { return U.escape(d.code || ''); }},
            {title: L.col_usage, width: 72, html: function (d) { return U.escape(d.parse_kind_label || ''); }},
            {title: L.status, width: 72, html: function (d) {
                return d.is_on ? U.status(true, L.enabled) : U.status(false, L.disabled);
            }},
            {title: L.actions, cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-edit">' + L.edit + '</a><a href="#" class="btn-link js-del">' + L.delete + '</a>';
            }}
        ]
    });
    markChips();

    function openDialog(mode, row) {
        row = row || {};
        U.dialog({
            wide: true,
            title: mode === 'edit' ? L.edit_downloader : L.add_downloader,
            content: document.getElementById('downer-dialog-tpl').innerHTML,
            onOpen: function (body) {
                U.fillForm(body.querySelector('form'), {
                    id: mode === 'edit' ? (row.id || '') : '',
                    name: row.name || '',
                    code: row.code || '',
                    parse: row.parse || '',
                    sort: row.sort == null ? 0 : row.sort,
                    status: row.status == null ? '1' : String(row.status)
                });
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (!data.name) { U.toast(L.please_fill_name, 'err'); return false; }
                if (!data.code) { U.toast(L.please_fill_code, 'err'); return false; }
                if (mode !== 'edit') delete data.id; else data.id = row.id;
                return U.post('/admin/video/downloaders/save', data).then(function (res) {
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
        if (!ids.length) { U.toast(L.please_select_downloaders, 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/downloaders/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.op_fail, 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || L.op_ok, 'ok');
        });
    }

    U.on('#downer-search-btn', 'click', runSearch);
    U.on('#downer-reset-btn', 'click', function () { setTimeout(runSearch, 0); });
    U.on('#downer-add-btn', 'click', function () { openDialog('add'); });
    U.on('#downer-try-btn', 'click', function () {
        var data = U.formData(tryForm);
        if (!data.url) { U.toast(L.please_fill_try_url, 'err'); return; }
        tryOut.textContent = '…';
        U.post('/admin/video/downloaders/try', data).then(function (res) {
            tryOut.textContent = (res && res.msg) || L.fail;
            if (!res || res.code !== 0) U.toast((res && res.msg) || L.fail, 'err');
        });
    });
    document.getElementById('downer-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#downer-batch-on', 'click', function () { batch('status', 1); });
    U.on('#downer-batch-off', 'click', function () { batch('status', 0); });
    U.on('#downer-batch-del', 'click', function () { batch('delete', '', L.confirm_batch_del_downloaders); });
    U.on('#downer-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#downer-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-edit')) openDialog('edit', row);
        if (a.classList.contains('js-del')) {
            if (!U.confirm(String(L.confirm_del_downloader || '').replace('__NAME__', row.name || row.code || ''))) return;
            U.post('/admin/video/downloaders/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
                table.refresh();
                U.toast(L.deleted, 'ok');
            });
        }
    });
})();
</script>
@endpush
