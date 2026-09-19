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
@endphp

@section('plain')
<div class="card card-panel admin-user-index access-board">
    <div class="card-header">
        <span>管理员 <em id="admin-user-count"></em></span>
    </div>
    <div class="card-body">
        @include('admin.partials.access-chain', ['step' => 'admins'])
        <div class="admin-user-compose">
            <form id="admin-user-compose" autocomplete="off">
                <label class="admin-user-compose-label" for="admin-user-quick-name">新增管理员</label>
                <div class="admin-user-compose-row">
                    <input id="admin-user-quick-name" type="text" name="username" placeholder="登录名" aria-label="登录名" required>
                    <input type="password" name="password" placeholder="密码，至少 6 位" aria-label="密码" required minlength="6" autocomplete="new-password">
                    <select name="role_id" aria-label="角色">
                        <option value="0">暂不选角色</option>
                        @foreach($roles as $role)
                            <option value="{{ $role['id'] }}" @selected($defaultRoleId === (int) $role['id'])>{{ $role['name'] }}{{ (int) ($role['status'] ?? 1) === 1 ? '' : '（停用）' }}</option>
                        @endforeach
                    </select>
                    <button class="btn" type="submit" id="admin-user-compose-btn">添加</button>
                </div>
                @if($roles === [])
                    <p class="muted field-hint">还没有角色，先去「<a href="/admin/system/roles">角色</a>」加一个。</p>
                @endif
            </form>
        </div>

        <form class="filter-bar admin-user-find" id="admin-user-search">
            <input type="hidden" name="kind">
            <input type="hidden" name="role_id">
            <input type="search" name="q" placeholder="搜登录名或邮箱" autocomplete="off" aria-label="搜索管理员">
            <button type="submit" class="btn btn-sm" id="admin-user-search-btn">查询</button>
            <button type="reset" class="btn btn-muted btn-sm" id="admin-user-reset-btn">重置</button>
        </form>
        <div class="queue-chips" id="admin-user-queues">
            <button type="button" class="chip" data-queue="">全部@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            @foreach($roles as $role)
                <button type="button" class="chip" data-queue="role_id" data-value="{{ $role['id'] }}">{{ $role['name'] }}@if(($role['count'] ?? 0) > 0)<em>{{ $role['count'] }}</em>@endif</button>
            @endforeach
            <button type="button" class="chip" data-queue="kind" data-value="never">从未登录@if($q('never') > 0)<em>{{ $q('never') }}</em>@endif</button>
        </div>
        <div id="admin-user-table" class="access-table"></div>
    </div>
</div>
<template id="admin-user-dialog-tpl">
    <form>
        <input type="hidden" name="id">
        <label>登录名</label>
        <input type="text" name="username" placeholder="用来登录后台">
        <p class="muted field-hint">前台会员进不了这里。改自己的登录名后，顶栏显示会跟着变。</p>
        <label>邮箱</label>
        <input type="email" name="email" placeholder="选填，方便找到人">
        <label>角色</label>
        <select name="role_id" id="admin-user-role-select">
            <option value="0">暂不选角色</option>
            @foreach($roles as $role)
                <option value="{{ $role['id'] }}">{{ $role['name'] }}{{ (int) ($role['status'] ?? 1) === 1 ? '' : '（停用）' }}</option>
            @endforeach
        </select>
        <p class="muted field-hint" id="admin-user-role-hint">没有角色的人，进不了菜单里登记过的页。要收权限先到角色里勾。</p>
        <label>备注</label>
        <input type="text" name="remark" placeholder="选填，只在后台看到">
        <label>密码</label>
        <input type="password" name="password" autocomplete="new-password" placeholder="留空表示不改">
        <p class="muted field-hint">新建至少 6 位。编辑留空则保持原密码。</p>
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
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
        if (d.is_self) badges += '<span class="badge badge-ok">当前账号</span>';
        if (d.is_founder) badges += '<span class="badge badge-ok">创始人</span>';
        else if (d.never_login) badges += '<span class="badge badge-off">从未登录</span>';
        var meta = U.escape(d.email || '');
        var role = d.kind_label || d.role_name || '';
        if (role) meta += (meta ? ' · ' : '') + U.escape(role);
        if (d.remark) meta += (meta ? ' · ' : '') + U.escape(d.remark);
        if (d.login_text) meta += (meta ? ' · ' : '') + (d.never_login ? U.escape(d.login_text) : ('上次登录 ' + U.escape(d.login_text)));
        if (d.login_ip) meta += (meta ? ' · ' : '') + U.escape(d.login_ip);
        if (d.ip_address) meta += ' ' + U.escape(d.ip_address);
        return '<div class="entry-row-title-line"><a class="entry-row-title js-edit" href="#">' + U.escape(d.username || '未命名') + '</a> ' + badges + '</div>'
            + '<div class="entry-row-meta">' + meta + '</div>';
    }

    var table = U.table({
        el: '#admin-user-table',
        countEl: countEl,
        url: '/admin/user/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>没有符合条件的管理员。</p><p><button type="button" class="btn btn-muted btn-sm" id="admin-user-empty-reset">清除筛选</button></p></div>';
            }
            return '<div class="list-empty"><p>还没有管理员。</p><p class="muted">在上方填写登录名和密码即可添加。他们登录的是后台，不是网站。不能删自己，创始人不能删。</p></div>';
        },
        onDraw: function (_wrap, list) {
            var reset = document.getElementById('admin-user-empty-reset');
            if (reset) reset.addEventListener('click', function () { form.reset(); runSearch(); });
        },
        cols: [
            {title: '管理员', html: nameHtml},
            {title: '操作', cls: 'actions', html: function (d) {
                var html = '<a href="#" class="btn-link js-edit">编辑</a>';
                if (d.can_delete) html += '<a href="#" class="btn-link js-del">删除</a>';
                return html;
            }}
        ]
    });
    markChips();

    function openEdit(row) {
        row = row || {};
        var founder = !!row.is_founder;
        U.dialog({
            title: '编辑管理员',
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
                    if (hint) hint.textContent = '这是创始人，能进所有菜单，不用选角色。';
                }
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (!data.username) { U.toast('请填写登录名', 'err'); return false; }
                data.id = row.id;
                if (!data.password) delete data.password;
                return U.post('/admin/user/update', data).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return false; }
                    U.toast((res && res.msg) || '已保存', 'ok');
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
        if (!data.username) { U.toast('请填写登录名', 'err'); return; }
        if (!data.password) { U.toast('请填写密码', 'err'); return; }
        if (data.password.length < 6) { U.toast('密码至少 6 位', 'err'); return; }
        U.post('/admin/user/add', data).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '没能添加', 'err'); return; }
            U.toast((res && res.msg) || '已添加', 'ok');
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
            if (!row.can_delete) { U.toast('这个账号不能删', 'err'); return; }
            if (!U.confirm('确定删除「' + (row.username || '') + '」？不能再登录后台。')) return;
            U.post('/admin/user/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '没能删除', 'err'); return; }
                table.refresh();
                U.toast((res && res.msg) || '已删除', 'ok');
            });
        }
    });
})();
</script>
@endpush
