@extends('admin.layouts.inner')
@section('title', admin_t('nav.roles'))

@php
    $queues = $queues ?? ['all' => 0, 'used' => 0, 'empty' => 0, 'off' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
@endphp

@section('plain')
<div class="card card-panel role-index access-board">
    <div class="card-header">
        <span>角色 <em id="role-count"></em></span>
    </div>
    <div class="card-body">
        @include('admin.partials.access-chain', ['step' => 'roles'])
        <div class="role-compose">
            <form id="role-compose" autocomplete="off">
                <label class="role-compose-label" for="role-quick-name">新增角色</label>
                <div class="role-compose-row">
                    <input id="role-quick-name" type="text" name="name" placeholder="名称，如 审核员" aria-label="角色名称" required>
                    <button class="btn" type="submit">添加</button>
                </div>
            </form>
        </div>

        <form class="filter-bar role-find" id="role-search">
            <input type="hidden" name="kind">
            <input type="search" name="q" placeholder="搜名称或备注" autocomplete="off" aria-label="搜索角色">
            <button type="submit" class="btn btn-sm" id="role-search-btn">{{ admin_t('ui.search') }}</button>
            <button type="reset" class="btn btn-muted btn-sm" id="role-reset-btn">{{ admin_t('ui.reset') }}</button>
        </form>
        <div class="queue-chips" id="role-queues">
            <button type="button" class="chip" data-queue="">全部@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="kind" data-value="used">使用中@if($q('used') > 0)<em>{{ $q('used') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="kind" data-value="empty">还没人@if($q('empty') > 0)<em>{{ $q('empty') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="kind" data-value="off">已停用@if($q('off') > 0)<em>{{ $q('off') }}</em>@endif</button>
        </div>
        <div id="role-table" class="access-table"></div>
    </div>
</div>
<template id="role-dialog-tpl">
    <form class="admin-form">
        <input type="hidden" name="id">
        <input type="hidden" name="code">
        <input type="hidden" name="sort">
        <label>名称</label>
        <input type="text" name="name" placeholder="给管理员分角色时看到的名字">
        <p class="muted field-hint" id="role-code-hint">标识码会按名称自动生成，一般不用改。</p>
        <label>备注</label>
        <input type="text" name="remark" placeholder="给自己看，可空">
        <label>状态</label>
        <select name="status">
            <option value="1">启用</option>
            <option value="0">停用</option>
        </select>
        <p class="role-perms-label">能进哪些菜单</p>
        <p class="muted field-hint">不勾的工作区页，这个角色进后台看不到也打不开。工作台、全部功能、插件不用勾。1 号创始人不用勾。</p>
        <div class="html-cache-actions" style="margin:0 0 8px">
            <button type="button" class="btn btn-muted btn-sm" id="role-perm-checkall">全选</button>
            <button type="button" class="btn btn-muted btn-sm" id="role-perm-uncheckall">全不选</button>
        </div>
        <div class="role-perm-box" id="role-perm-box"></div>
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
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
        if (String(d.status) === '0') badges += '<span class="badge badge-off">停用</span>';
        if (d.in_use) badges += '<span class="badge badge-ok">使用中</span>';
        var meta = U.escape(d.code || '');
        meta += (meta ? ' · ' : '') + ((d.perms_count || 0) > 0 ? (d.perms_count + ' 项菜单') : '还没勾菜单');
        if (d.remark) meta += ' · ' + U.escape(d.remark);
        return '<div class="entry-row-title-line"><a class="entry-row-title js-edit" href="#">' + U.escape(d.name || '未命名') + '</a> ' + badges + '</div>'
            + '<div class="entry-row-meta">' + meta + '</div>';
    }
    function usersHtml(d) {
        var n = parseInt(d.users_count || '0', 10) || 0;
        if (n < 1) return '<span class="muted">还没人</span>';
        return '<a href="/admin/user?role_id=' + encodeURIComponent(d.id) + '">' + n + ' 人</a>';
    }

    var table = U.table({
        el: '#role-table',
        countEl: countEl,
        url: '/admin/system/roles/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>没有符合条件的角色。</p><p><button type="button" class="btn btn-muted btn-sm" id="role-empty-reset">清除筛选</button></p></div>';
            }
            return '<div class="list-empty"><p>还没有角色。</p><p class="muted">在上方填写名称即可添加。点名称去勾能进哪些页，再给管理员套上。</p></div>';
        },
        onDraw: function (_wrap, list) {
            var reset = document.getElementById('role-empty-reset');
            if (reset) reset.addEventListener('click', function () { form.reset(); runSearch(); });
        },
        cols: [
            {title: '角色', html: nameHtml},
            {title: '管理员', width: 88, html: usersHtml},
            {title: '操作', cls: 'actions', html: function (d) {
                var html = '<a href="#" class="btn-link js-edit">勾权限</a>';
                if (d.can_delete) html += '<a href="#" class="btn-link js-del">删除</a>';
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
            return '<p class="muted">还没有可勾的页。先到「<a href="/admin/system/menus">菜单</a>」点对齐当前工作区。</p>';
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
            if (idsRes && idsRes.code !== 0) { U.toast((idsRes && idsRes.msg) || '没能读取权限', 'err'); return; }
            var checkedIds = Array.isArray(idsRes && idsRes.data) ? idsRes.data : [];
            var checkedMap = {};
            checkedIds.forEach(function (id) { checkedMap[String(id)] = true; });
            U.dialog({
                title: row.id ? ('勾权限 · ' + (row.name || '')) : '编辑角色',
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
                    if (hint && row.code) hint.textContent = '标识码 ' + row.code + '，建好后不用改。';
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
                    if (!payload.name) { U.toast('请填写名称', 'err'); return false; }
                    var permIds = U.qa('#role-perm-box input[type=checkbox]:checked', body).map(function (c) {
                        return parseInt(c.value, 10);
                    }).filter(Boolean);
                    var url = payload.id ? '/admin/system/roles/update' : '/admin/system/roles/add';
                    if (!payload.id) delete payload.id;
                    return U.post(url, payload).then(function (res) {
                        if (!res || res.code !== 0) { U.toast((res && res.msg) || '没能保存', 'err'); return false; }
                        var id = parseInt(payload.id || (res.data && res.data.id) || '0', 10);
                        if (!id) { table.refresh(); return; }
                        return U.post('/admin/system/roles/perms/set', {role_id: id, perm_ids: permIds}).then(function (pres) {
                            if (!pres || pres.code !== 0) { U.toast((pres && pres.msg) || '资料已存，菜单没勾上', 'err'); return false; }
                            U.toast((pres && pres.msg) || '已保存', 'ok');
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
        if (!data.name) { U.toast('请填写名称', 'err'); return; }
        U.post('/admin/system/roles/add', data).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '没能添加', 'err'); return; }
            compose.reset();
            table.refresh();
            U.toast((res && res.msg) || '已添加', 'ok');
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
            if (!row.can_delete) { U.toast('有人在用，先换人再删', 'err'); return; }
            if (!U.confirm('确定删除「' + (row.name || '') + '」？')) return;
            U.post('/admin/system/roles/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '没能删除', 'err'); return; }
                table.refresh();
                U.toast((res && res.msg) || '已删除', 'ok');
            });
        }
    });
})();
</script>
@endpush
