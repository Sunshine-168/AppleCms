@extends('admin.layouts.inner')
@section('title', $title ?? admin_t('page.websites'))

@php
    $queues = $queues ?? ['all' => 0, 'on' => 0, 'off' => 0, 'no_type' => 0, 'logo' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
    $types = is_array($types ?? null) ? $types : [];
    $typeId = (int) ($typeId ?? 0);
    $typeUrl = '/admin/video/website-types';
    $websiteJsLang = [
        'unnamed' => admin_t('ui.unnamed'),
        'hide' => admin_t('ui.hide'),
        'show' => admin_t('ui.show'),
        'sites' => admin_t('ui.sites'),
        'types' => admin_t('ui.types'),
        'hits' => admin_t('ui.hits'),
        'sort' => admin_t('ui.sort'),
        'status' => admin_t('ui.status'),
        'actions' => admin_t('ui.actions'),
        'front' => admin_t('ui.front'),
        'open_link' => admin_t('ui.open_link'),
        'edit' => admin_t('ui.edit'),
        'delete' => admin_t('ui.delete'),
        'fail' => admin_t('ui.fail'),
        'saved' => admin_t('ui.saved'),
        'created' => admin_t('ui.created'),
        'deleted' => admin_t('ui.deleted'),
        'op_fail' => admin_t('ui.op_fail'),
        'op_ok' => admin_t('ui.op_ok'),
        'clear_filter' => admin_t('ui.clear_filter'),
        'add_website' => admin_t('ui.add_website'),
        'edit_website' => admin_t('ui.edit_website'),
        'add_nav_type' => admin_t('ui.add_nav_type'),
        'empty_websites' => admin_t('ui.empty_websites'),
        'websites_empty_hint' => admin_t('ui.websites_empty_hint'),
        'no_match_sites' => admin_t('ui.no_match_sites'),
        'please_fill_site_name' => admin_t('ui.please_fill_site_name'),
        'please_fill_url' => admin_t('ui.please_fill_url'),
        'please_select_sites' => admin_t('ui.please_select_sites'),
        'confirm_del_sites' => admin_t('ui.confirm_del_sites'),
        'confirm_del_site' => admin_t('ui.confirm_del_site', ['name' => '__NAME__']),
        'selected_n' => admin_t('ui.selected_n', ['n' => '__N__']),
        'ungrouped_row' => admin_t('ui.ungrouped_row'),
        'type_deleted_n' => admin_t('ui.type_deleted_n', ['id' => '__ID__']),
        'not_nav_type' => admin_t('ui.not_nav_type'),
        'type_hash' => admin_t('ui.types').' #__ID__',
    ];
@endphp

@section('plain')
<div class="card card-panel website-index" id="website-index">
    <div class="card-header">
        <span>{{ admin_t('ui.websites_nav') }} <em id="website-count"></em></span>
        <div>
            <button type="button" class="btn btn-sm" id="website-add-btn">{{ admin_t('ui.add_website') }}</button>
            <a class="btn btn-muted btn-sm" href="{{ $typeUrl }}">{{ admin_t('ui.types') }}</a>
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">{{ admin_t('ui.websites_lead') }}</p>
        @if($types === [])
            <p class="muted field-hint">{{ admin_t('ui.no_nav_types_hint') }} <a href="{{ $typeUrl }}/create">{{ admin_t('ui.add_nav_type') }}</a></p>
        @endif
        <form class="filter-bar" id="website-search" onsubmit="return false;">
            <input type="hidden" name="empty_type">
            <input type="hidden" name="logo">
            <input type="search" name="q" placeholder="{{ admin_t('ui.ph_search_website') }}" autocomplete="off" aria-label="{{ admin_t('ui.websites_nav') }}">
            @if($types !== [])
                <select name="type_id">
                    <option value="">{{ admin_t('ui.types') }}</option>
                    @foreach($types as $type)
                        <option value="{{ (int) $type['id'] }}" @selected($typeId === (int) $type['id'])>{{ $type['name'] }}</option>
                    @endforeach
                </select>
            @else
                <input type="hidden" name="type_id" value="{{ $typeId > 0 ? $typeId : '' }}">
            @endif
            <select name="status">
                <option value="">{{ admin_t('ui.status') }}</option>
                <option value="1">{{ admin_t('ui.show') }}</option>
                <option value="0">{{ admin_t('ui.hide') }}</option>
            </select>
            <button type="button" class="btn btn-sm" id="website-search-btn">{{ admin_t('ui.search') }}</button>
            <button type="reset" class="btn btn-muted btn-sm" id="website-reset-btn">{{ admin_t('ui.reset') }}</button>
        </form>
        <div class="queue-chips" id="website-queues">
            <button type="button" class="chip" data-queue="">{{ admin_t('ui.all') }}@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="1">{{ admin_t('ui.showing') }}@if($q('on') > 0)<em>{{ $q('on') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="0">{{ admin_t('ui.hidden') }}@if($q('off') > 0)<em>{{ $q('off') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="empty_type" data-value="1">{{ admin_t('ui.no_type_chip') }}@if($q('no_type') > 0)<em>{{ $q('no_type') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="logo" data-value="1">{{ admin_t('ui.has_logo') }}@if($q('logo') > 0)<em>{{ $q('logo') }}</em>@endif</button>
        </div>
        <div class="batch-bar" id="website-batch" hidden>
            <strong id="website-batch-count">{{ admin_t('ui.selected_n', ['n' => 0]) }}</strong>
            <button type="button" class="btn btn-sm" id="website-batch-on">{{ admin_t('ui.show') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="website-batch-off">{{ admin_t('ui.hide') }}</button>
            <button type="button" class="btn btn-danger btn-sm" id="website-batch-del">{{ admin_t('ui.delete') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="website-batch-clear">{{ admin_t('ui.clear_selection') }}</button>
        </div>
        <div id="website-table"></div>
    </div>
</div>
<template id="website-dialog-tpl">
    <form class="admin-form">
        <input type="hidden" name="id">
        <label>{{ admin_t('ui.site_name') }}</label>
        <input class="entry-title" type="text" name="name" placeholder="{{ admin_t('ui.ph_site_name') }}" required autofocus>
        <p class="muted field-hint">{{ admin_t('ui.site_show_hint') }}</p>
        <label>{{ admin_t('ui.label_url') }}</label>
        <input type="text" name="url" placeholder="{{ admin_t('ui.ph_https') }}" required>
        <p class="muted field-hint">{{ admin_t('ui.site_url_hint') }}</p>
        @if($types !== [])
            <label>{{ admin_t('ui.types') }}</label>
            <select name="type_id">
                <option value="0">{{ admin_t('ui.ungrouped') }}</option>
                @foreach($types as $type)
                    <option value="{{ (int) $type['id'] }}">{{ $type['name'] }}</option>
                @endforeach
            </select>
            <p class="muted field-hint">{{ admin_t('ui.nav_type_pick_hint') }}<a href="{{ $typeUrl }}/create" target="_blank" rel="noopener">{{ admin_t('ui.add') }}</a>。</p>
        @else
            <input type="hidden" name="type_id" value="0">
        @endif
        <label>{{ admin_t('ui.label_logo') }}</label>
        <div class="field-inline">
            <input type="text" name="logo" placeholder="{{ admin_t('ui.ph_logo_opt') }}">
            <button type="button" class="btn btn-muted website-logo-upload-btn">{{ admin_t('ui.upload') }}</button>
        </div>
        <img class="img-preview link-logo-preview" alt="">
        <label>{{ admin_t('ui.label_blurb') }}</label>
        <input type="text" name="blurb" placeholder="{{ admin_t('ui.ph_blurb') }}">
        <div class="admin-dialog-grid">
            <div>
                <label>{{ admin_t('ui.sort') }}</label>
                <input type="number" name="sort" value="0">
            </div>
            <div>
                <label>{{ admin_t('ui.hits') }}</label>
                <input type="number" name="hits" value="0" min="0">
            </div>
        </div>
        <p class="muted field-hint">{{ admin_t('ui.hits_open_hint') }}</p>
        <label>{{ admin_t('ui.status') }}</label>
        <select name="status">
            <option value="1">{{ admin_t('ui.show') }}</option>
            <option value="0">{{ admin_t('ui.hide') }}</option>
        </select>
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($websiteJsLang, JSON_UNESCAPED_UNICODE);
    var QUEUE_KEYS = ['empty_type', 'logo'];
    var form = document.getElementById('website-search');
    var batchBar = document.getElementById('website-batch');
    var batchCount = document.getElementById('website-batch-count');
    var countEl = document.getElementById('website-count');
    var prefillType = @json($typeId > 0 ? $typeId : 0);
    var typeUrl = @json($typeUrl, JSON_UNESCAPED_UNICODE);

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
        var active = '';
        QUEUE_KEYS.forEach(function (k) {
            if (form[k] && form[k].value === '1') active = k;
        });
        U.qa('#website-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = false;
            if (key === '' && !active && status === '') on = true;
            else if (key === 'status' && !active && status === val) on = true;
            else if (key && key !== 'status' && active === key) on = true;
            chip.classList.toggle('active', on);
        });
    }
    function applyQueue(key, value) {
        QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
        if (key === '') {
            form.status.value = '';
            if (form.type_id) form.type_id.value = prefillType ? String(prefillType) : '';
        } else if (key === 'status') {
            form.status.value = value || '';
        } else {
            form.status.value = '';
            if (key === 'empty_type' && form.type_id) form.type_id.value = '';
            if (key && form[key]) form[key].value = value || '1';
        }
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
        var badge = String(d.status) === '1' ? '' : '<span class="badge badge-off">' + U.escape(L.hide) + '</span>';
        var meta = d.blurb ? U.escape(d.blurb) : U.escape(d.url || '');
        return '<div class="vod-cell">' + thumb + '<div><div class="entry-row-title-line"><a class="entry-row-title js-edit" href="#">' + U.escape(d.name || AdminUi.t('unnamed')) + '</a> ' + badge + '</div>'
            + '<div class="entry-row-meta">' + meta + '</div></div></div>';
    }
    function typeHtml(d) {
        var tid = parseInt(d.type_id, 10) || 0;
        if (tid < 1) return '<span class="muted">' + U.escape(L.ungrouped_row) + '</span>';
        if (d.type_missing) return '<span class="muted">' + U.escape(String(L.type_deleted_n || '').replace('__ID__', String(tid))) + '</span>';
        if (d.type_wrong) return '<span class="muted">' + U.escape(L.not_nav_type) + '</span>';
        return U.escape(d.type_name || String(L.type_hash || '').replace('__ID__', String(tid)));
    }

    var table = U.table({
        el: '#website-table',
        queueKeys: QUEUE_KEYS,
        countEl: countEl,
        url: '/admin/video/websites/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>' + U.escape(L.no_match_sites) + '</p><p><button type="button" class="btn btn-muted btn-sm" id="website-empty-reset">' + U.escape(L.clear_filter) + '</button></p></div>';
            }
            return '<div class="list-empty"><p>' + U.escape(L.empty_websites) + '</p><p class="muted">' + U.escape(L.websites_empty_hint) + '</p><p><button type="button" class="btn btn-primary btn-sm" id="website-empty-add">' + U.escape(L.add_website) + '</button> <a class="btn btn-muted btn-sm" href="' + typeUrl + '/create">' + U.escape(L.add_nav_type) + '</a></p></div>';
        },
        onDraw: function (_wrap, list) {
            var add = document.getElementById('website-empty-add');
            var reset = document.getElementById('website-empty-reset');
            if (add) add.addEventListener('click', function () { openDialog('add'); });
            if (reset) reset.addEventListener('click', function () {
                form.reset();
                if (prefillType && form.type_id) form.type_id.value = String(prefillType);
                runSearch();
            });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = String(L.selected_n || '').replace('__N__', String(ids.length));
        },
        cols: [
            {check: true, width: 36},
            {title: AdminUi.t('sites'), html: nameHtml},
            {title: AdminUi.t('types'), width: 140, html: typeHtml},
            {key: 'hits', title: AdminUi.t('hits'), width: 72, html: function (d) { return U.escape(String(d.hits == null ? 0 : d.hits)); }},
            {key: 'sort', title: AdminUi.t('sort'), width: 64},
            {title: AdminUi.t('status'), width: 72, html: function (d) {
                return String(d.status) === '1' ? U.status(true, AdminUi.t('show')) : U.status(false, AdminUi.t('hide'));
            }},
            {title: AdminUi.t('actions'), cls: 'actions', html: function (d) {
                var html = '';
                if (d.front_url) html += '<a href="' + U.escape(d.front_url) + '" target="_blank" rel="noopener" class="btn-link">' + AdminUi.t('front') + '</a>';
                if (d.url) html += '<a href="' + U.escape(d.url) + '" target="_blank" rel="noopener noreferrer" class="btn-link">' + AdminUi.t('open_link') + '</a>';
                html += '<a href="#" class="btn-link js-edit">' + AdminUi.t('edit') + '</a>';
                html += '<a href="#" class="btn-link js-del">' + AdminUi.t('delete') + '</a>';
                return html;
            }}
        ]
    });
    markChips();

    function bindLogo(formEl) {
        U.bindImageField(formEl, {
            input: 'input[name=logo]',
            btn: '.website-logo-upload-btn',
            preview: '.link-logo-preview'
        });
    }

    function openDialog(mode, row) {
        row = row || {};
        U.dialog({
            wide: true,
            title: mode === 'edit' ? L.edit_website : L.add_website,
            content: document.getElementById('website-dialog-tpl').innerHTML,
            onOpen: function (body) {
                var formEl = body.querySelector('form');
                var typeVal = mode === 'edit' ? (row.type_id || 0) : (row.type_id || prefillType || 0);
                U.fillForm(formEl, {
                    id: mode === 'edit' ? (row.id || '') : '',
                    name: row.name || '',
                    url: row.url || '',
                    type_id: String(typeVal || 0),
                    logo: row.logo || '',
                    blurb: row.blurb || '',
                    sort: row.sort == null ? 0 : row.sort,
                    hits: row.hits == null ? 0 : row.hits,
                    status: row.status == null ? '1' : String(row.status)
                });
                bindLogo(formEl);
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (!data.name) { U.toast(L.please_fill_site_name, 'err'); return false; }
                if (!data.url) { U.toast(L.please_fill_url, 'err'); return false; }
                if (mode !== 'edit') delete data.id; else data.id = row.id;
                return U.post('/admin/video/websites/save', data).then(function (res) {
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
        if (!ids.length) { U.toast(L.please_select_sites, 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/websites/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.op_fail, 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || L.op_ok, 'ok');
        });
    }

    U.on('#website-search-btn', 'click', runSearch);
    U.on('#website-reset-btn', 'click', function () {
        setTimeout(function () {
            if (prefillType && form.type_id) form.type_id.value = String(prefillType);
            runSearch();
        }, 0);
    });
    U.on('#website-add-btn', 'click', function () { openDialog('add'); });
    document.getElementById('website-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#website-batch-on', 'click', function () { batch('status', 1); });
    U.on('#website-batch-off', 'click', function () { batch('status', 0); });
    U.on('#website-batch-del', 'click', function () { batch('delete', '', L.confirm_del_sites); });
    U.on('#website-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#website-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        if (a.target === '_blank') return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-edit')) openDialog('edit', row);
        if (a.classList.contains('js-del')) {
            if (!U.confirm(String(L.confirm_del_site || '').replace('__NAME__', row.name || ''))) return;
            U.post('/admin/video/websites/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
                table.refresh();
                U.toast(L.deleted, 'ok');
            });
        }
    });
})();
</script>
@endpush
