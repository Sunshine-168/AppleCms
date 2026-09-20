@extends('admin.layouts.inner')
@section('title', admin_t('nav.roles'))

@php
    $queues = $queues ?? ['all' => 0, 'used' => 0, 'empty' => 0, 'off' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
    $T = [
        'roles' => admin_t('nav.roles'),
        'admins' => admin_t('nav.admins'),
        'add_role' => admin_t('ui.add_role'),
        'edit_role' => admin_t('ui.edit_role'),
        'add' => admin_t('ui.add'),
        'delete' => admin_t('ui.delete'),
        'actions' => admin_t('ui.actions'),
        'tick_perms' => admin_t('ui.tick_perms'),
        'ph_role_name' => admin_t('ui.ph_role_name'),
        'ph_search_role' => admin_t('ui.ph_search_role'),
        'all' => admin_t('ui.all'),
        'in_use' => admin_t('ui.in_use'),
        'nobody_yet' => admin_t('ui.nobody_yet'),
        'deactivated' => admin_t('ui.deactivated'),
        'unnamed' => admin_t('ui.unnamed'),
        'menus_n' => admin_t('ui.menus_n', ['n' => '__N__']),
        'no_menus_ticked' => admin_t('ui.no_menus_ticked'),
        'people_n' => admin_t('ui.people_n', ['n' => '__N__']),
        'no_match_roles' => admin_t('ui.no_match_roles'),
        'clear_filter' => admin_t('ui.clear_filter'),
        'empty_roles' => admin_t('ui.empty_roles'),
        'empty_roles_hint' => admin_t('ui.empty_roles_hint'),
        'need_name' => admin_t('ui.need_name'),
        'fail' => admin_t('ui.fail'),
        'saved' => admin_t('ui.saved'),
        'added' => admin_t('ui.added'),
        'deleted' => admin_t('ui.deleted'),
        'name' => admin_t('ui.name'),
        'remark' => admin_t('ui.remark'),
        'confirm_delete_named' => admin_t('ui.confirm_delete_named', ['name' => '__NAME__']),
        'empty_pages_hint' => admin_t('ui.empty_pages_hint'),
        'disabled' => admin_t('ui.disabled'),
    ];
@endphp

@section('plain')
<div class="card card-panel role-index access-board">
    <div class="card-header">
        <span>{{ admin_t('nav.roles') }} <em id="role-count"></em></span>
    </div>
    <div class="card-body">
        @include('admin.partials.access-chain', ['step' => 'roles'])
        <div class="role-compose">
            <form id="role-compose" autocomplete="off">
                <label class="role-compose-label" for="role-quick-name">{{ admin_t('ui.add_role') }}</label>
                <div class="role-compose-row">
                    <input id="role-quick-name" type="text" name="name" placeholder="{{ admin_t('ui.ph_role_name') }}" aria-label="{{ admin_t('ui.name') }}" required>
                    <button class="btn" type="submit">{{ admin_t('ui.add') }}</button>
                </div>
            </form>
        </div>

        <form class="filter-bar role-find" id="role-search">
            <input type="hidden" name="kind">
            <input type="search" name="q" placeholder="{{ admin_t('ui.ph_search_role') }}" autocomplete="off" aria-label="{{ admin_t('ui.ph_search_role') }}">
            <button type="submit" class="btn btn-sm" id="role-search-btn">{{ admin_t('ui.search') }}</button>
            <button type="reset" class="btn btn-muted btn-sm" id="role-reset-btn">{{ admin_t('ui.reset') }}</button>
        </form>
        <div class="queue-chips" id="role-queues">
            <button type="button" class="chip" data-queue="">{{ admin_t('ui.all') }}@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="kind" data-value="used">{{ admin_t('ui.in_use') }}@if($q('used') > 0)<em>{{ $q('used') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="kind" data-value="empty">{{ admin_t('ui.nobody_yet') }}@if($q('empty') > 0)<em>{{ $q('empty') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="kind" data-value="off">{{ admin_t('ui.deactivated') }}@if($q('off') > 0)<em>{{ $q('off') }}</em>@endif</button>
        </div>
        <div id="role-table" class="access-table"></div>
    </div>
</div>
<template id="role-dialog-tpl">
    <form class="admin-form">
        <input type="hidden" name="id">
        <input type="hidden" name="code">
        <input type="hidden" name="sort">
        <label>{{ admin_t('ui.name') }}</label>
        <input type="text" name="name" placeholder="{{ admin_t('ui.ph_role_name') }}">
        <p class="muted field-hint" id="role-code-hint"></p>
        <label>{{ admin_t('ui.remark') }}</label>
        <input type="text" name="remark" placeholder="{{ admin_t('ui.ph_remark_admin') }}">
        <label>{{ admin_t('ui.status') }}</label>
        <select name="status">
            <option value="1">{{ admin_t('ui.enabled') }}</option>
            <option value="0">{{ admin_t('ui.disabled') }}</option>
        </select>
        <p class="role-perms-label">{{ admin_t('ui.which_menus') }}</p>
        <p class="muted field-hint">{{ admin_t('ui.role_menus_hint') }}</p>
        <div class="html-cache-actions" style="margin:0 0 8px">
            <button type="button" class="btn btn-muted btn-sm" id="role-perm-checkall">{{ admin_t('ui.select_all') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="role-perm-uncheckall">{{ admin_t('ui.select_none') }}</button>
        </div>
        <div class="role-perm-box" id="role-perm-box"></div>
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var T = @json($T, JSON_UNESCAPED_UNICODE);
    var form = document.getElementById('role-search');
    var compose = document.getElementById('role-compose');
    var countEl = document.getElementById('role-count');
    var treeCache = null;

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
    function markChips() {
        var kind = form.kind.value;
        U.qa('#role-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = (key === '' && kind === '') || (key === 'kind' && kind === val);
            chip.classList.toggle('active', on);
        });
    }
    function applyQueue(key, value) {
        form.kind.value = key === 'kind' ? (value || '') : '';
        runSearch();
    }
    function runSearch() {
        table.reload(queryWhere());
        markChips();
    }
    function nameHtml(d) {
        var badges = '';
        if (String(d.status) === '0') badges += '<span class="badge badge-off">' + U.escape(T.deactivated) + '</span>';
        if (d.in_use) badges += '<span class="badge badge-ok">' + U.escape(T.in_use) + '</span>';
        var meta = U.escape(d.code || '');
        meta += (meta ? ' · ' : '') + ((d.perms_count || 0) > 0 ? String(T.menus_n || '').replace('__N__', d.perms_count) : T.no_menus_ticked);
        if (d.remark) meta += ' · ' + U.escape(d.remark);
        return '<div class="entry-row-title-line"><a class="entry-row-title js-edit" href="#">' + U.escape(d.name || T.unnamed) + '</a> ' + badges + '</div>'
            + '<div class="entry-row-meta">' + meta + '</div>';
    }
    function usersHtml(d) {
        var n = parseInt(d.users_count || '0', 10) || 0;
        if (n < 1) return '<span class="muted">' + U.escape(T.nobody_yet) + '</span>';
        return '<a href="/admin/user?role_id=' + encodeURIComponent(d.id) + '">' + U.escape(String(T.people_n || '').replace('__N__', n)) + '</a>';
    }

    var table = U.table({
        el: '#role-table',
        countEl: countEl,
        url: '/admin/system/roles/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>' + U.escape(T.no_match_roles) + '</p><p><button type="button" class="btn btn-muted btn-sm" id="role-empty-reset">' + U.escape(T.clear_filter) + '</button></p></div>';
            }
            return '<div class="list-empty"><p>' + U.escape(T.empty_roles) + '</p><p class="muted">' + U.escape(T.empty_roles_hint) + '</p></div>';
        },
        onDraw: function (_wrap, list) {
            var reset = document.getElementById('role-empty-reset');
            if (reset) reset.addEventListener('click', function () { form.reset(); runSearch(); });
        },
        cols: [
            {title: T.roles, html: nameHtml},
            {title: T.admins, width: 88, html: usersHtml},
            {title: T.actions, cls: 'actions', html: function (d) {
                var html = '<a href="#" class="btn-link js-edit">' + U.escape(T.tick_perms) + '</a>';
                if (d.can_delete) html += '<a href="#" class="btn-link js-del">' + U.escape(T.delete) + '</a>';
                return html;
            }}
        ]
    });
    markChips();

    function renderNode(n, checkedMap) {
        var id = n.id != null ? String(n.id) : '';
        var title = n.title || n.name || '';
        var kids = n.children || [];
        var html = '<label class="inline"><input type="checkbox" value="' + U.escape(id) + '"' + (checkedMap[id] ? ' checked' : '') + '> ' + U.escape(title) + '</label>';
        if (kids.length) {
            html += '<div class="role-perms">' + kids.map(function (c) { return renderNode(c, checkedMap); }).join('') + '</div>';
        }
        return html;
    }
    function renderGroups(nodes, checkedMap) {
        if (!nodes || !nodes.length) {
            return '<p class="muted">' + U.escape(T.empty_pages_hint || T.empty_roles_hint) + '</p>';
        }
        return nodes.map(function (n) {
            return '<div class="role-perm-group">' + renderNode(n, checkedMap) + '</div>';
        }).join('');
    }
    function loadTree(cb) {
        if (treeCache) { cb(treeCache); return; }
        U.get('/admin/system/perms/tree').then(function (res) {
            treeCache = (res && res.code === 0) ? (res.data || []) : [];
            cb(treeCache);
        });
    }

    function openEdit(row) {
        row = row || {};
        var roleId = parseInt(row.id || '0', 10);
        U.loading(true);
        Promise.all([
            roleId ? U.get('/admin/system/roles/perms/ids', {role_id: roleId}) : Promise.resolve({code: 0, data: []}),
            new Promise(function (resolve) { loadTree(resolve); })
        ]).then(function (both) {
            U.loading(false);
            var idsRes = both[0];
            var tree = both[1] || treeCache || [];
            if (idsRes && idsRes.code !== 0) { U.toast((idsRes && idsRes.msg) || T.fail, 'err'); return; }
            var checkedIds = Array.isArray(idsRes && idsRes.data) ? idsRes.data : [];
            var checkedMap = {};
            checkedIds.forEach(function (id) { checkedMap[String(id)] = true; });
            U.dialog({
                title: row.id ? (T.tick_perms + ' · ' + (row.name || '')) : T.edit_role,
                wide: true,
                content: document.getElementById('role-dialog-tpl').innerHTML,
                onOpen: function (body) {
                    U.fillForm(body.querySelector('form'), {
                        id: row.id || '',
                        name: row.name || '',
                        code: row.code || '',
                        remark: row.remark || '',
                        status: String(row.status) === '0' ? '0' : '1',
                        sort: row.sort || 0
                    });
                    var hint = body.querySelector('#role-code-hint');
                    if (hint && row.code) hint.textContent = row.code;
                    var box = body.querySelector('#role-perm-box');
                    if (box) box.innerHTML = renderGroups(tree, checkedMap);
                    var checkAll = body.querySelector('#role-perm-checkall');
                    var uncheck = body.querySelector('#role-perm-uncheckall');
                    if (checkAll) checkAll.addEventListener('click', function () {
                        U.qa('#role-perm-box input[type=checkbox]', body).forEach(function (c) { c.checked = true; });
                    });
                    if (uncheck) uncheck.addEventListener('click', function () {
                        U.qa('#role-perm-box input[type=checkbox]', body).forEach(function (c) { c.checked = false; });
                    });
                    if (box) box.addEventListener('change', function (e) {
                        var input = e.target;
                        if (!input || input.type !== 'checkbox') return;
                        var wrap = input.closest('label');
                        var kids = wrap && wrap.nextElementSibling;
                        if (kids && kids.classList.contains('role-perms')) {
                            kids.querySelectorAll('input[type=checkbox]').forEach(function (c) { c.checked = input.checked; });
                        }
                    });
                },
                onSave: function (body) {
                    var payload = U.formData(body.querySelector('form'));
                    if (!payload.name) { U.toast(T.need_name, 'err'); return false; }
                    var permIds = U.qa('#role-perm-box input[type=checkbox]:checked', body).map(function (c) {
                        return parseInt(c.value, 10);
                    }).filter(Boolean);
                    var url = payload.id ? '/admin/system/roles/update' : '/admin/system/roles/add';
                    if (!payload.id) delete payload.id;
                    return U.post(url, payload).then(function (res) {
                        if (!res || res.code !== 0) { U.toast((res && res.msg) || T.fail, 'err'); return false; }
                        var id = parseInt(payload.id || (res.data && res.data.id) || '0', 10);
                        if (!id) { table.refresh(); return; }
                        return U.post('/admin/system/roles/perms/set', {role_id: id, perm_ids: permIds}).then(function (pres) {
                            if (!pres || pres.code !== 0) { U.toast((pres && pres.msg) || T.fail, 'err'); return false; }
                            U.toast((pres && pres.msg) || T.saved, 'ok');
                            table.refresh();
                        });
                    });
                }
            });
        });
    }

    U.on('#role-search', 'submit', function (e) { e.preventDefault(); runSearch(); });
    U.on('#role-search-btn', 'click', runSearch);
    U.on('#role-reset-btn', 'click', function () { setTimeout(function () { form.kind.value = ''; runSearch(); }, 0); });
    U.on('#role-queues', 'click', function (e) {
        var chip = e.target.closest('.chip');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#role-compose', 'submit', function (e) {
        e.preventDefault();
        var data = U.formData(compose);
        if (!data.name) { U.toast(T.need_name, 'err'); return; }
        U.post('/admin/system/roles/add', data).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || T.fail, 'err'); return; }
            compose.reset();
            table.refresh();
            U.toast((res && res.msg) || T.added, 'ok');
            var id = res.data && res.data.id;
            if (id) openEdit({id: id, name: data.name, status: 1, perms_count: 0});
        });
    });
    U.on('#role-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        var tr = e.target.closest('tr');
        if (!tr) return;
        var row = (table.rows() || [])[tr.getAttribute('data-idx')];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-edit')) openEdit(row);
        if (a.classList.contains('js-del')) {
            if (!row.can_delete) { U.toast(T.in_use, 'err'); return; }
            if (!U.confirm(String(T.confirm_delete_named || T.delete).replace('__NAME__', row.name || ''))) return;
            U.post('/admin/system/roles/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || T.fail, 'err'); return; }
                table.refresh();
                U.toast((res && res.msg) || T.deleted, 'ok');
            });
        }
    });
})();
</script>
@endpush
