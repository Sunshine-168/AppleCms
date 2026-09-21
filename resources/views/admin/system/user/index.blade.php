@extends('admin.layouts.inner')
@section('title', admin_t('nav.admins'))

@php
    $roles = $roles ?? [];
    $queues = $queues ?? ['all' => 0, 'founder' => 0, 'staff' => 0, 'never' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
    $currentId = (int) ($currentId ?? 0);
    $defaultRoleId = 0;
    foreach ($roles as $role) {
        if ((int) ($role['status'] ?? 1) === 1) {
            $defaultRoleId = (int) $role['id'];
            break;
        }
    }
    if ($defaultRoleId === 0 && $roles !== []) {
        $defaultRoleId = (int) ($roles[0]['id'] ?? 0);
    }
    $offSuffix = admin_t('ui.group_disabled_suffix');
    $T = [
        'admins' => admin_t('nav.admins'),
        'add_admin' => admin_t('ui.add_admin'),
        'edit_admin' => admin_t('ui.edit_admin'),
        'add' => admin_t('ui.add'),
        'edit' => admin_t('ui.edit'),
        'delete' => admin_t('ui.delete'),
        'actions' => admin_t('ui.actions'),
        'login_name' => admin_t('ui.login_name'),
        'ph_login_name' => admin_t('ui.ph_login_name'),
        'ph_password_min' => admin_t('ui.ph_password_min'),
        'password' => admin_t('ui.password'),
        'role' => admin_t('ui.role'),
        'no_role_yet' => admin_t('ui.no_role_yet'),
        'need_role_first' => admin_t('ui.need_role_first', ['link' => admin_t('nav.roles')]),
        'ph_search_admin' => admin_t('ui.ph_search_admin'),
        'all' => admin_t('ui.all'),
        'never_logged_in' => admin_t('ui.never_logged_in'),
        'current_account' => admin_t('ui.current_account'),
        'founder' => admin_t('ui.founder'),
        'last_login_at' => admin_t('ui.last_login_at', ['time' => '__TIME__']),
        'unnamed' => admin_t('ui.unnamed'),
        'no_match_admins' => admin_t('ui.no_match_admins'),
        'clear_filter' => admin_t('ui.clear_filter'),
        'empty_admins' => admin_t('ui.empty_admins'),
        'empty_admins_hint' => admin_t('ui.empty_admins_hint'),
        'need_username' => admin_t('ui.need_username'),
        'need_password' => admin_t('ui.need_password'),
        'password_min6' => admin_t('ui.password_min6'),
        'fail' => admin_t('ui.fail'),
        'saved' => admin_t('ui.saved'),
        'added' => admin_t('ui.added'),
        'ph_admin_username' => admin_t('ui.ph_admin_username'),
        'admin_not_member' => admin_t('ui.admin_not_member'),
        'email' => admin_t('ui.email'),
        'ph_email_find' => admin_t('ui.ph_email_find'),
        'role_pages_hint' => admin_t('ui.role_pages_hint'),
        'founder_all_menus' => admin_t('ui.founder_all_menus'),
        'remark' => admin_t('ui.remark'),
        'ph_remark_admin' => admin_t('ui.ph_remark_admin'),
        'ph_password_keep' => admin_t('ui.ph_password_keep'),
        'password_edit_hint' => admin_t('ui.password_edit_hint'),
        'cannot_delete_account' => admin_t('ui.cannot_delete_account'),
        'confirm_delete_named' => admin_t('ui.confirm_delete_named', ['name' => '__NAME__']),
        'deleted' => admin_t('ui.deleted'),
    ];
@endphp

@section('plain')
<div class="card card-panel admin-user-index access-board">
    <div class="card-header">
        <span>{{ admin_t('nav.admins') }} <em id="admin-user-count"></em></span>
    </div>
    <div class="card-body">
        @include('admin.partials.access-chain', ['step' => 'admins'])
        <div class="admin-user-compose">
            <form id="admin-user-compose" autocomplete="off">
                <label class="admin-user-compose-label" for="admin-user-quick-name">{{ admin_t('ui.add_admin') }}</label>
                <div class="admin-user-compose-row">
                    <input id="admin-user-quick-name" type="text" name="username" placeholder="{{ admin_t('ui.ph_login_name') }}" aria-label="{{ admin_t('ui.login_name') }}" required>
                    <input type="password" name="password" placeholder="{{ admin_t('ui.ph_password_min') }}" aria-label="{{ admin_t('ui.password') }}" required minlength="6" autocomplete="new-password">
                    <select name="role_id" aria-label="{{ admin_t('ui.role') }}">
                        <option value="0">{{ admin_t('ui.no_role_yet') }}</option>
                        @foreach($roles as $role)
                            <option value="{{ $role['id'] }}" @selected($defaultRoleId === (int) $role['id'])>{{ $role['name'] }}{{ (int) ($role['status'] ?? 1) === 1 ? '' : $offSuffix }}</option>
                        @endforeach
                    </select>
                    <button class="btn" type="submit" id="admin-user-compose-btn">{{ admin_t('ui.add') }}</button>
                </div>
                @if($roles === [])
                    <p class="muted field-hint">{!! str_replace(':link', '<a href="/admin/system/roles">'.e(admin_t('nav.roles')).'</a>', e(admin_t('ui.need_role_first', ['link' => ':link']))) !!}</p>
                @endif
            </form>
        </div>

        <form class="filter-bar admin-user-find" id="admin-user-search">
            <input type="hidden" name="kind">
            <input type="hidden" name="role_id">
            <input type="search" name="q" placeholder="{{ admin_t('ui.ph_search_admin') }}" autocomplete="off" aria-label="{{ admin_t('ui.ph_search_admin') }}">
            <button type="submit" class="btn btn-sm" id="admin-user-search-btn">{{ admin_t('ui.search') }}</button>
            <button type="reset" class="btn btn-muted btn-sm" id="admin-user-reset-btn">{{ admin_t('ui.reset') }}</button>
        </form>
        <div class="queue-chips" id="admin-user-queues">
            <button type="button" class="chip" data-queue="">{{ admin_t('ui.all') }}@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            @foreach($roles as $role)
                <button type="button" class="chip" data-queue="role_id" data-value="{{ $role['id'] }}">{{ $role['name'] }}@if(($role['count'] ?? 0) > 0)<em>{{ $role['count'] }}</em>@endif</button>
            @endforeach
            <button type="button" class="chip" data-queue="kind" data-value="never">{{ admin_t('ui.never_logged_in') }}@if($q('never') > 0)<em>{{ $q('never') }}</em>@endif</button>
        </div>
        <div id="admin-user-table" class="access-table"></div>
    </div>
</div>
<template id="admin-user-dialog-tpl">
    <form class="admin-form">
        <input type="hidden" name="id">
        <label>{{ admin_t('ui.login_name') }}</label>
        <input type="text" name="username" placeholder="{{ admin_t('ui.ph_admin_username') }}">
        <p class="muted field-hint">{{ admin_t('ui.admin_not_member') }}</p>
        <label>{{ admin_t('ui.email') }}</label>
        <input type="email" name="email" placeholder="{{ admin_t('ui.ph_email_find') }}">
        <label>{{ admin_t('ui.role') }}</label>
        <select name="role_id" id="admin-user-role-select">
            <option value="0">{{ admin_t('ui.no_role_yet') }}</option>
            @foreach($roles as $role)
                <option value="{{ $role['id'] }}">{{ $role['name'] }}{{ (int) ($role['status'] ?? 1) === 1 ? '' : $offSuffix }}</option>
            @endforeach
        </select>
        <p class="muted field-hint" id="admin-user-role-hint">{{ admin_t('ui.role_pages_hint') }}</p>
        <label>{{ admin_t('ui.remark') }}</label>
        <input type="text" name="remark" placeholder="{{ admin_t('ui.ph_remark_admin') }}">
        <label>{{ admin_t('ui.password') }}</label>
        <input type="password" name="password" autocomplete="new-password" placeholder="{{ admin_t('ui.ph_password_keep') }}">
        <p class="muted field-hint">{{ admin_t('ui.password_edit_hint') }}</p>
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var T = @json($T, JSON_UNESCAPED_UNICODE);
    var form = document.getElementById('admin-user-search');
    var compose = document.getElementById('admin-user-compose');
    var countEl = document.getElementById('admin-user-count');
    var qs = new URLSearchParams(location.search);
    if (qs.get('role_id')) form.role_id.value = qs.get('role_id');

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
        var roleId = form.role_id.value;
        U.qa('#admin-user-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = false;
            if (key === '' && kind === '' && roleId === '') on = true;
            else if (key === 'kind' && roleId === '' && kind === val) on = true;
            else if (key === 'role_id' && kind === '' && roleId === val) on = true;
            chip.classList.toggle('active', on);
        });
    }
    function applyQueue(key, value) {
        form.kind.value = '';
        form.role_id.value = '';
        if (key === 'kind') form.kind.value = value || '';
        else if (key === 'role_id') form.role_id.value = value || '';
        runSearch();
    }
    function runSearch() {
        table.reload(queryWhere());
        markChips();
    }
    function nameHtml(d) {
        var badges = '';
        if (d.is_self) badges += '<span class="badge badge-ok">' + U.escape(T.current_account) + '</span>';
        if (d.is_founder) badges += '<span class="badge badge-ok">' + U.escape(T.founder) + '</span>';
        else if (d.never_login) badges += '<span class="badge badge-off">' + U.escape(T.never_logged_in) + '</span>';
        var meta = U.escape(d.email || '');
        if (!d.is_founder) {
            var role = d.kind_label || d.role_name || '';
            if (role) meta += (meta ? ' · ' : '') + U.escape(role);
        }
        var remark = d.remark_label || d.remark || '';
        if (remark) meta += (meta ? ' · ' : '') + U.escape(remark);
        if (d.login_text) meta += (meta ? ' · ' : '') + (d.never_login ? U.escape(d.login_text) : U.escape(String(T.last_login_at || '').replace('__TIME__', d.login_text)));
        if (d.login_ip) meta += (meta ? ' · ' : '') + U.escape(d.login_ip);
        var place = d.place_text || '';
        if (place) meta += ' ' + U.escape(place);
        return '<div class="entry-row-title-line"><a class="entry-row-title js-edit" href="#">' + U.escape(d.username || T.unnamed) + '</a> ' + badges + '</div>'
            + '<div class="entry-row-meta">' + meta + '</div>';
    }

    var table = U.table({
        el: '#admin-user-table',
        countEl: countEl,
        url: '/admin/user/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>' + U.escape(T.no_match_admins) + '</p><p><button type="button" class="btn btn-muted btn-sm" id="admin-user-empty-reset">' + U.escape(T.clear_filter) + '</button></p></div>';
            }
            return '<div class="list-empty"><p>' + U.escape(T.empty_admins) + '</p><p class="muted">' + U.escape(T.empty_admins_hint) + '</p></div>';
        },
        onDraw: function (_wrap, list) {
            var reset = document.getElementById('admin-user-empty-reset');
            if (reset) reset.addEventListener('click', function () { form.reset(); runSearch(); });
        },
        cols: [
            {title: T.admins, html: nameHtml},
            {title: T.actions, cls: 'actions', html: function (d) {
                var html = '<a href="#" class="btn-link js-edit">' + U.escape(T.edit) + '</a>';
                if (d.can_delete) html += '<a href="#" class="btn-link js-del">' + U.escape(T.delete) + '</a>';
                return html;
            }}
        ]
    });
    markChips();

    function openEdit(row) {
        row = row || {};
        var founder = !!row.is_founder;
        U.dialog({
            title: T.edit_admin,
            content: document.getElementById('admin-user-dialog-tpl').innerHTML,
            onOpen: function (body) {
                var formEl = body.querySelector('form');
                U.fillForm(formEl, {
                    id: row.id || '',
                    username: row.username || '',
                    email: row.email || '',
                    remark: row.remark || '',
                    role_id: row.role_id == null ? '0' : String(row.role_id),
                    password: ''
                });
                var sel = body.querySelector('select[name=role_id]');
                var hint = body.querySelector('#admin-user-role-hint');
                if (founder && sel) {
                    sel.disabled = true;
                    if (hint) hint.textContent = T.founder_all_menus;
                }
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (!data.username) { U.toast(T.need_username, 'err'); return false; }
                data.id = row.id;
                if (!data.password) delete data.password;
                return U.post('/admin/user/update', data).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || T.fail, 'err'); return false; }
                    U.toast((res && res.msg) || T.saved, 'ok');
                    table.refresh();
                });
            }
        });
    }

    U.on('#admin-user-search', 'submit', function (e) { e.preventDefault(); runSearch(); });
    U.on('#admin-user-search-btn', 'click', runSearch);
    U.on('#admin-user-reset-btn', 'click', function () { setTimeout(function () { form.kind.value = ''; form.role_id.value = ''; runSearch(); }, 0); });
    U.on('#admin-user-queues', 'click', function (e) {
        var chip = e.target.closest('.chip');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#admin-user-compose', 'submit', function (e) {
        e.preventDefault();
        var data = U.formData(compose);
        if (!data.username) { U.toast(T.need_username, 'err'); return; }
        if (!data.password) { U.toast(T.need_password, 'err'); return; }
        if (data.password.length < 6) { U.toast(T.password_min6, 'err'); return; }
        U.post('/admin/user/add', data).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || T.fail, 'err'); return; }
            U.toast((res && res.msg) || T.added, 'ok');
            compose.reset();
            table.refresh();
        });
    });
    U.on('#admin-user-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        var tr = e.target.closest('tr');
        if (!tr) return;
        var row = (table.rows() || [])[tr.getAttribute('data-idx')];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-edit')) openEdit(row);
        if (a.classList.contains('js-del')) {
            if (!row.can_delete) { U.toast(T.cannot_delete_account, 'err'); return; }
            if (!U.confirm(String(T.confirm_delete_named || '').replace('__NAME__', row.username || ''))) return;
            U.post('/admin/user/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || T.fail, 'err'); return; }
                table.refresh();
                U.toast((res && res.msg) || T.deleted, 'ok');
            });
        }
    });
})();
</script>
@endpush
