@extends('admin.layouts.inner')
@section('title', $title ?? admin_t('page.servers'))

@php
    $queues = $queues ?? ['all' => 0, 'on' => 0, 'off' => 0, 'empty' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
    $onServers = $onServers ?? [];
    $serverJsLang = [
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
        'add_server' => admin_t('ui.add_server'),
        'edit_server' => admin_t('ui.edit_server'),
        'has_prefix' => admin_t('ui.has_prefix'),
        'empty_prefix' => admin_t('ui.empty_prefix'),
        'no_sources_yet' => admin_t('ui.no_sources_yet'),
        'sources_n' => admin_t('ui.sources_n', ['n' => '__N__']),
        'sources_count' => admin_t('ui.sources_count', ['n' => '__N__']),
        'empty_cell' => admin_t('ui.empty_cell'),
        'empty_servers' => admin_t('ui.empty_servers'),
        'empty_servers_hint' => admin_t('ui.empty_servers_hint'),
        'go_downloaders' => admin_t('ui.go_downloaders'),
        'no_match_servers' => admin_t('ui.no_match_servers'),
        'please_fill_name' => admin_t('ui.please_fill_name'),
        'please_select_servers' => admin_t('ui.please_select_servers'),
        'confirm_batch_del_servers' => admin_t('ui.confirm_batch_del_servers'),
        'confirm_del_server' => admin_t('ui.confirm_del_server', ['name' => '__NAME__']),
        'please_fill_try_path' => admin_t('ui.please_fill_try_path'),
        'col_lines' => admin_t('ui.col_lines'),
        'prefix' => admin_t('ui.prefix'),
    ];
@endphp

@section('plain')
<div class="card card-panel server-index" id="server-index">
    <div class="card-header">
        <span>{{ admin_t('ui.servers') }} <em id="server-count"></em></span>
        <div>
            <button type="button" class="btn btn-sm" id="server-add-btn">{{ admin_t('ui.add_server') }}</button>
            <a class="btn btn-muted btn-sm" href="/admin/video/players">{{ admin_t('ui.players') }}</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/downloaders">{{ admin_t('ui.downloaders') }}</a>
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">{{ admin_t('ui.servers_lead') }}</p>
        <form class="filter-bar" id="server-search" onsubmit="return false;">
            <input type="hidden" name="empty_url">
            <input type="search" name="q" placeholder="{{ admin_t('ui.ph_server') }}" autocomplete="off" aria-label="{{ admin_t('ui.servers') }}">
            <select name="status">
                <option value="">{{ admin_t('ui.status') }}</option>
                <option value="1">{{ admin_t('ui.enabled') }}</option>
                <option value="0">{{ admin_t('ui.disabled') }}</option>
            </select>
            <button type="button" class="btn btn-sm" id="server-search-btn">{{ admin_t('ui.search') }}</button>
            <button type="reset" class="btn btn-muted btn-sm" id="server-reset-btn">{{ admin_t('ui.reset') }}</button>
        </form>
        <div class="queue-chips" id="server-queues">
            <button type="button" class="chip" data-queue="">{{ admin_t('ui.all') }}@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="1">{{ admin_t('ui.enabled') }}@if($q('on') > 0)<em>{{ $q('on') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="0">{{ admin_t('ui.disabled') }}@if($q('off') > 0)<em>{{ $q('off') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="empty_url" data-value="1">{{ admin_t('ui.empty_prefix') }}@if($q('empty') > 0)<em>{{ $q('empty') }}</em>@endif</button>
        </div>
        <form class="filter-bar server-try-bar" id="server-try" onsubmit="return false;">
            <select name="server_id" aria-label="{{ admin_t('ui.try_server_group') }}">
                <option value="0">{{ admin_t('ui.no_group') }}</option>
                @foreach($onServers as $s)
                    <option value="{{ (int) ($s['id'] ?? 0) }}">{{ $s['name'] ?? '' }}</option>
                @endforeach
            </select>
            <input type="text" name="url" placeholder="{{ admin_t('ui.ph_try_rel_path') }}" autocomplete="off" aria-label="{{ admin_t('ui.try_play_url') }}">
            <button type="button" class="btn btn-muted btn-sm" id="server-try-btn">{{ admin_t('ui.try_once') }}</button>
            <span class="muted" id="server-try-out"></span>
        </form>
        <p class="muted field-hint">{{ admin_t('ui.hint_server_try') }}</p>
        <div class="batch-bar" id="server-batch" hidden>
            <strong id="server-batch-count">{{ admin_t('ui.selected_n', ['n' => 0]) }}</strong>
            <button type="button" class="btn btn-sm" id="server-batch-on">{{ admin_t('ui.enabled') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="server-batch-off">{{ admin_t('ui.disabled') }}</button>
            <button type="button" class="btn btn-danger btn-sm" id="server-batch-del">{{ admin_t('ui.delete') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="server-batch-clear">{{ admin_t('ui.clear_selection') }}</button>
        </div>
        <div id="server-table"></div>
    </div>
</div>
<template id="server-dialog-tpl">
    <form class="admin-form">
        <input type="hidden" name="id">
        <label>{{ admin_t('ui.name') }}</label>
        <input class="entry-title" type="text" name="name" placeholder="{{ admin_t('ui.ph_server_name') }}" required autofocus>
        <p class="muted field-hint">{{ admin_t('ui.hint_server_name') }}</p>
        <label>{{ admin_t('ui.label_url_prefix') }}</label>
        <input type="text" name="url" placeholder="{{ admin_t('ui.ph_server_url') }}">
        <p class="muted field-hint">{{ admin_t('ui.hint_server_url') }}</p>
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
        <p class="muted field-hint">{{ admin_t('ui.hint_server_status') }}</p>
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($serverJsLang, JSON_UNESCAPED_UNICODE);
    var QUEUE_KEYS = ['empty_url'];
    var form = document.getElementById('server-search');
    var tryForm = document.getElementById('server-try');
    var tryOut = document.getElementById('server-try-out');
    var batchBar = document.getElementById('server-batch');
    var batchCount = document.getElementById('server-batch-count');
    var countEl = document.getElementById('server-count');

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
        var emptyUrl = form.empty_url.value;
        U.qa('#server-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = false;
            if (key === '' && status === '' && emptyUrl === '') on = true;
            else if (key === 'status' && emptyUrl === '' && status === val) on = true;
            else if (key === 'empty_url' && status === '' && emptyUrl === val) on = true;
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
    function fillTryGroups(list) {
        var sel = tryForm.server_id;
        if (!sel) return;
        var seen = {};
        Array.prototype.forEach.call(sel.options, function (opt) {
            if (opt.value && opt.value !== '0') seen[opt.value] = opt;
        });
        (list || []).forEach(function (row) {
            var id = String(row.id);
            if (row.is_on) {
                if (!seen[id]) {
                    var opt = document.createElement('option');
                    opt.value = id;
                    opt.textContent = row.name || ('#' + id);
                    sel.appendChild(opt);
                    seen[id] = opt;
                } else {
                    seen[id].textContent = row.name || ('#' + id);
                }
            } else if (seen[id]) {
                seen[id].remove();
                delete seen[id];
            }
        });
    }
    function nameHtml(d) {
        var badge = d.is_on ? '' : '<span class="badge badge-off">' + L.disabled + '</span>';
        var used = parseInt(d.source_count, 10) || 0;
        var meta = [d.has_url ? L.has_prefix : L.empty_prefix, used > 0 ? String(L.sources_n || '').replace('__N__', String(used)) : L.no_sources_yet];
        if (d.url_preview) meta.push(U.escape(d.url_preview));
        return '<div><div class="entry-row-title-line"><a class="entry-row-title js-edit" href="#">' + U.escape(d.name || '') + '</a> ' + badge + '</div>'
            + '<div class="entry-row-meta">' + meta.join(' · ') + '</div></div>';
    }

    var table = U.table({
        el: '#server-table',
        queueKeys: QUEUE_KEYS,
        countEl: countEl,
        url: '/admin/video/servers/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>' + L.no_match_servers + '</p><p><button type="button" class="btn btn-muted btn-sm" id="server-empty-reset">' + L.clear_filter + '</button></p></div>';
            }
            return '<div class="list-empty"><p>' + L.empty_servers + '</p><p class="muted">' + L.empty_servers_hint + '</p><p><button type="button" class="btn btn-primary btn-sm" id="server-empty-add">' + L.add_server + '</button> <a class="btn btn-muted btn-sm" href="/admin/video/downloaders">' + L.go_downloaders + '</a></p></div>';
        },
        onDraw: function (_wrap, list) {
            fillTryGroups(list);
            var add = document.getElementById('server-empty-add');
            var reset = document.getElementById('server-empty-reset');
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
            {title: L.prefix, html: function (d) { return d.has_url ? U.escape(d.url_preview || '') : '<span class="muted">' + L.empty_cell + '</span>'; }},
            {title: L.col_lines, width: 80, html: function (d) {
                var n = parseInt(d.source_count, 10) || 0;
                return n > 0 ? String(L.sources_count || '').replace('__N__', String(n)) : '—';
            }},
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
            title: mode === 'edit' ? L.edit_server : L.add_server,
            content: document.getElementById('server-dialog-tpl').innerHTML,
            onOpen: function (body) {
                U.fillForm(body.querySelector('form'), {
                    id: mode === 'edit' ? (row.id || '') : '',
                    name: row.name || '',
                    url: row.url || '',
                    sort: row.sort == null ? 0 : row.sort,
                    status: row.status == null ? '1' : String(row.status)
                });
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (!data.name) { U.toast(L.please_fill_name, 'err'); return false; }
                if (mode !== 'edit') delete data.id; else data.id = row.id;
                return U.post('/admin/video/servers/save', data).then(function (res) {
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
        if (!ids.length) { U.toast(L.please_select_servers, 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/servers/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.op_fail, 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || L.op_ok, 'ok');
        });
    }

    U.on('#server-search-btn', 'click', runSearch);
    U.on('#server-reset-btn', 'click', function () { setTimeout(runSearch, 0); });
    U.on('#server-add-btn', 'click', function () { openDialog('add'); });
    U.on('#server-try-btn', 'click', function () {
        var data = U.formData(tryForm);
        if (!data.url) { U.toast(L.please_fill_try_path, 'err'); return; }
        tryOut.textContent = '…';
        U.post('/admin/video/servers/try', data).then(function (res) {
            tryOut.textContent = (res && res.msg) || L.fail;
            if (!res || res.code !== 0) U.toast((res && res.msg) || L.fail, 'err');
        });
    });
    document.getElementById('server-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#server-batch-on', 'click', function () { batch('status', 1); });
    U.on('#server-batch-off', 'click', function () { batch('status', 0); });
    U.on('#server-batch-del', 'click', function () { batch('delete', '', L.confirm_batch_del_servers); });
    U.on('#server-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#server-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-edit')) openDialog('edit', row);
        if (a.classList.contains('js-del')) {
            if (!U.confirm(String(L.confirm_del_server || '').replace('__NAME__', row.name || ''))) return;
            U.post('/admin/video/servers/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
                table.refresh();
                U.toast(L.deleted, 'ok');
            });
        }
    });
})();
</script>
@endpush
