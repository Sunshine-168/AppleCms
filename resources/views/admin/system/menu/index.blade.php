@extends('admin.layouts.inner')
@section('title', admin_t('nav.menus'))

@php
    $T = [
        'unnamed' => admin_t('ui.unnamed'),
        'pages' => admin_t('ui.pages_col'),
        'actions' => admin_t('ui.actions'),
        'edit' => admin_t('ui.edit'),
        'delete' => admin_t('ui.delete'),
        'no_match' => admin_t('ui.no_match_pages'),
        'clear_filter' => admin_t('ui.clear_filter'),
        'empty' => admin_t('ui.empty_pages'),
        'empty_hint' => admin_t('ui.empty_pages_hint'),
        'edit_page' => admin_t('ui.edit_menu_page'),
        'add_page' => admin_t('ui.add_menu_page'),
        'top' => admin_t('ui.top_level'),
        'fail' => admin_t('ui.fail'),
        'save_fail' => admin_t('ui.save_fail'),
        'sync_fail' => admin_t('ui.sync_fail'),
        'synced_ok' => admin_t('ui.synced_ok'),
        'confirm_del' => admin_t('ui.confirm_del_menu'),
    ];
@endphp

@section('plain')
<div class="card card-panel menu-index access-board">
    <div class="card-header">
        <span>{{ admin_t('access.step_menus') }} <em id="menu-count"></em></span>
    </div>
    <div class="card-body">
        @include('admin.partials.access-chain', ['step' => 'menus'])
        <div class="menu-compose">
            <div class="menu-compose-row">
                <button type="button" class="btn" id="menu-sync-btn">{{ admin_t('access.sync') }}</button>
                <button type="button" class="btn btn-muted" id="menu-add-btn">{{ admin_t('ui.add_menu_page') }}</button>
            </div>
        </div>
        <form class="filter-bar menu-find" id="menu-search">
            <input type="search" name="name" placeholder="{{ admin_t('ui.ph_search_name') }}" autocomplete="off" aria-label="{{ admin_t('ui.ph_search_name') }}">
            <input type="search" name="api" placeholder="{{ admin_t('ui.ph_search_url') }}" autocomplete="off" aria-label="{{ admin_t('ui.ph_search_url') }}">
            <button type="submit" class="btn btn-sm" id="menu-search-btn">{{ admin_t('ui.search') }}</button>
            <button type="reset" class="btn btn-muted btn-sm" id="menu-reset-btn">{{ admin_t('ui.reset') }}</button>
        </form>
        <div id="menu-table" class="access-table"></div>
    </div>
</div>
<template id="menu-dialog-tpl">
    <form class="admin-form">
        <input type="hidden" name="id">
        <label>{{ admin_t('ui.col_type') }}</label>
        <select name="type">
            <option value="1">{{ admin_t('ui.kind_menu') }}</option>
            <option value="2">{{ admin_t('ui.kind_button') }}</option>
            <option value="3">{{ admin_t('ui.kind_api') }}</option>
        </select>
        <label>{{ admin_t('ui.parent') }}</label>
        <select name="pid" id="menu-pid-select"><option value="0">{{ admin_t('ui.top_level') }}</option></select>
        <label>{{ admin_t('ui.name') }}</label>
        <input type="text" name="name" placeholder="{{ admin_t('ui.ph_menu_name') }}">
        <label>{{ admin_t('ui.label_address') }}</label>
        <input type="text" name="api" placeholder="/admin/system/menus">
        <p class="muted field-hint">{{ admin_t('ui.menu_url_hint') }}</p>
        <label>{{ admin_t('ui.method') }}</label>
        <input type="text" name="method" placeholder="{{ admin_t('ui.ph_method_any') }}">
        <label>{{ admin_t('ui.identifier') }}</label>
        <input type="text" name="code" placeholder="{{ admin_t('ui.ph_code_from_url') }}">
        <label>{{ admin_t('ui.icon') }}</label>
        <input type="text" name="icon" placeholder="user-cog">
        <label>{{ admin_t('ui.sort') }}</label>
        <input type="number" name="sort" value="0">
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var T = @json($T, JSON_UNESCAPED_UNICODE);
    var form = document.getElementById('menu-search');
    var countEl = document.getElementById('menu-count');
    function codeFromApi(api) {
        api = String(api || '').trim();
        if (!api) return '';
        if (api.indexOf('route:') === 0) return api;
        api = api.replace(/^https?:\/\/[^/]+/i, '').replace(/^\/+/, '');
        return api ? api.replace(/\/+/g, '/').replace(/\//g, '.') : '';
    }
    function cleanWhere(data) {
        var out = {};
        Object.keys(data || {}).forEach(function (k) { if (data[k] !== '') out[k] = data[k]; });
        return out;
    }
    function queryWhere() {
        return cleanWhere(U.formData(form));
    }
    function isFiltered(where) {
        return Object.keys(where || {}).some(function (k) { return where[k] !== ''; });
    }
    function nameHtml(d) {
        var p = '';
        for (var i = 0; i < (parseInt(d.level, 10) || 0); i++) p += '— ';
        var meta = U.escape(d.api || d.code || '');
        if (d.parent_name) meta = U.escape(d.parent_name) + (meta ? ' · ' + meta : '');
        return '<div class="entry-row-title-line"><a class="entry-row-title js-edit" href="#">' + U.escape(p + (d.name || T.unnamed)) + '</a></div>'
            + (meta ? '<div class="entry-row-meta">' + meta + '</div>' : '');
    }
    var table = U.table({
        el: '#menu-table',
        countEl: countEl,
        url: '/admin/system/menus/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>' + T.no_match + '</p><p><button type="button" class="btn btn-muted btn-sm" id="menu-empty-reset">' + T.clear_filter + '</button></p></div>';
            }
            return '<div class="list-empty"><p>' + T.empty + '</p><p class="muted">' + T.empty_hint + '</p></div>';
        },
        onDraw: function (_wrap, list) {
            var reset = document.getElementById('menu-empty-reset');
            if (reset) reset.addEventListener('click', function () { form.reset(); table.reload({}); });
        },
        cols: [
            {title: T.pages, html: nameHtml},
            {title: T.actions, cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-edit">' + T.edit + '</a><a href="#" class="btn-link js-del">' + T.delete + '</a>';
            }}
        ]
    });
    function openForm(data) {
        data = data || {};
        var isEdit = !!data.id;
        U.dialog({
            title: isEdit ? T.edit_page : T.add_page,
            content: document.getElementById('menu-dialog-tpl').innerHTML,
            onOpen: function (body) {
                var formEl = body.querySelector('form');
                U.fillForm(formEl, {
                    id: data.id || '',
                    type: data.type != null ? String(data.type) : '1',
                    pid: data.pid != null ? String(data.pid) : '0',
                    name: data.name || '',
                    api: data.api || '',
                    method: data.method || '',
                    code: data.code || '',
                    icon: data.icon || '',
                    sort: data.sort != null ? String(data.sort) : '0'
                });
                U.get('/admin/system/menus/parents', {type: 1}).then(function (res) {
                    var opts = (res && res.code === 0 && res.data) ? res.data : [{id: 0, name: T.top}];
                    var html = '';
                    opts.forEach(function (o) {
                        html += '<option value="' + U.escape(o.id != null ? o.id : 0) + '">' + U.escape(o.name || '') + '</option>';
                    });
                    var sel = body.querySelector('#menu-pid-select');
                    sel.innerHTML = html;
                    sel.value = data.pid != null ? String(data.pid) : '0';
                });
                body.querySelector('input[name=api]').addEventListener('blur', function () {
                    var code = body.querySelector('input[name=code]');
                    if (!code.value) code.value = codeFromApi(this.value);
                });
            },
            onSave: function (body) {
                var field = U.formData(body.querySelector('form'));
                field.pid = parseInt(field.pid || '0', 10);
                field.type = parseInt(field.type || '1', 10);
                field.sort = parseInt(field.sort || '0', 10);
                if (field.method) field.method = String(field.method).toUpperCase();
                if (!field.code && field.api) field.code = codeFromApi(field.api);
                var url = isEdit ? '/admin/system/menus/update' : '/admin/system/menus/add';
                return U.post(url, field).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || T.save_fail, 'err'); return false; }
                    table.refresh();
                });
            }
        });
    }
    U.on('#menu-search', 'submit', function (e) { e.preventDefault(); table.reload(queryWhere()); });
    U.on('#menu-search-btn', 'click', function () { table.reload(queryWhere()); });
    U.on('#menu-reset-btn', 'click', function () { setTimeout(function () { table.reload({}); }, 0); });
    U.on('#menu-add-btn', 'click', function () { openForm({}); });
    U.on('#menu-sync-btn', 'click', function () {
        U.post('/admin/system/menus/sync', {}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || T.sync_fail, 'err'); return; }
            U.toast((res && res.msg) || T.synced_ok, 'ok');
            table.refresh();
        });
    });
    U.on('#menu-table', 'click', function (e) {
        var a = e.target.closest ? e.target.closest('a') : null; if (!a) return;
        var row = U.rowFromClick(e, table);
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-edit')) openForm(row);
        if (a.classList.contains('js-del') && U.confirm(T.confirm_del)) {
            U.post('/admin/system/menus/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || T.fail, 'err'); return; }
                table.refresh();
            });
        }
    });
})();
</script>
@endpush
