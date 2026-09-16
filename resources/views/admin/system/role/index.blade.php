@extends('admin.layouts.inner')
@section('title', admin_t('page.roles'))

@section('plain')
<div class="card card-panel">
    <div class="card-body">
        <form class="filter-bar" id="role-search" onsubmit="return false;">
            <input type="text" name="name" placeholder="角色名称">
            <input type="text" name="code" placeholder="角色标识">
            <select name="status">
                <option value="">状态</option>
                <option value="1">启用</option>
                <option value="0">禁用</option>
            </select>
            <button type="button" class="btn btn-sm" id="role-search-btn">查询</button>
            <button type="reset" class="btn btn-muted btn-sm" id="role-reset-btn">重置</button>
        </form>
    </div>
</div>
<div class="card card-panel">
    <div class="card-header">
        <span>角色</span>
        <div>
            <button type="button" class="btn btn-sm" id="role-add-btn">新增角色</button>
            <button type="button" class="btn btn-muted btn-sm" id="role-refresh-btn">刷新</button>
        </div>
    </div>
    <div class="card-body"><div id="role-table"></div></div>
</div>
<template id="role-dialog-tpl">
    <form>
        <input type="hidden" name="id">
        <label>名称</label>
        <input type="text" name="name" placeholder="角色名称">
        <label>标识</label>
        <input type="text" name="code" placeholder="唯一标识">
        <label>备注</label>
        <textarea name="remark" placeholder="可选"></textarea>
        <label>状态</label>
        <select name="status"><option value="1">启用</option><option value="0">禁用</option></select>
        <label>排序</label>
        <input type="number" name="sort" value="0">
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    function renderTree(nodes, checkedMap) {
        var html = '';
        (nodes || []).forEach(function (n) {
            var id = n.id != null ? String(n.id) : '';
            var title = n.title || n.name || '';
            html += '<div class="n"><label class="inline"><input type="checkbox" value="' + U.escape(id) + '"' + (checkedMap[id] ? ' checked' : '') + '> ' + U.escape(title) + '</label>';
            if (n.children && n.children.length) html += '<div class="kids">' + renderTree(n.children, checkedMap) + '</div>';
            html += '</div>';
        });
        return html;
    }
    var table = U.table({
        el: '#role-table',
        url: '/admin/system/roles/list',
        cols: [
            {key: 'id', title: 'ID', width: 70},
            {key: 'name', title: '名称'},
            {key: 'code', title: '标识'},
            {title: '状态', width: 80, html: function (d) { return d.status == 1 ? U.status(true, '启用') : U.status(false, '禁用'); }},
            {key: 'sort', title: '排序', width: 70},
            {key: 'remark', title: '备注'},
            {key: 'create_time', title: '创建时间', width: 150},
            {title: '操作', cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-perm">设置权限</a><a href="#" class="btn-link js-edit">编辑</a><a href="#" class="btn-link js-del">删除</a>';
            }}
        ]
    });
    function openForm(data) {
        data = data || {};
        U.dialog({
            title: data.id ? '编辑角色' : '新增角色',
            content: document.getElementById('role-dialog-tpl').innerHTML,
            onOpen: function (body) {
                U.fillForm(body.querySelector('form'), {
                    id: data.id || '',
                    name: data.name || '',
                    code: data.code || '',
                    remark: data.remark || '',
                    status: data.status == 0 ? '0' : '1',
                    sort: data.sort || 0
                });
            },
            onSave: function (body) {
                var payload = U.formData(body.querySelector('form'));
                if (!payload.name) { U.toast('请输入角色名称', 'err'); return false; }
                if (!payload.code) { U.toast('请输入角色标识', 'err'); return false; }
                var url = payload.id ? '/admin/system/roles/update' : '/admin/system/roles/add';
                if (!payload.id) delete payload.id;
                return U.post(url, payload).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return false; }
                    U.toast('保存成功', 'ok');
                    table.refresh();
                });
            }
        });
    }
    function openPerms(role) {
        var roleId = parseInt(role.id || '0', 10);
        if (roleId < 1) { U.toast('缺少角色ID', 'err'); return; }
        U.loading(true);
        Promise.all([
            U.get('/admin/system/roles/perms/ids', {role_id: roleId}),
            U.get('/admin/system/perms/tree')
        ]).then(function (both) {
            U.loading(false);
            var idsRes = both[0], treeRes = both[1];
            if (!idsRes || idsRes.code !== 0) { U.toast((idsRes && idsRes.msg) || '获取角色权限失败', 'err'); return; }
            if (!treeRes || treeRes.code !== 0) { U.toast((treeRes && treeRes.msg) || '获取权限树失败', 'err'); return; }
            var checkedIds = Array.isArray(idsRes.data) ? idsRes.data : [];
            var checkedMap = {};
            checkedIds.forEach(function (id) { checkedMap[String(id)] = true; });
            var treeData = Array.isArray(treeRes.data) ? treeRes.data : [];
            U.dialog({
                title: '设置权限 - ' + (role.name || ''),
                wide: true,
                content: '<div class="toolbar"><button type="button" class="btn btn-muted btn-sm" id="role-perm-checkall">全选</button><button type="button" class="btn btn-muted btn-sm" id="role-perm-uncheckall">全不选</button></div><div class="perm-tree">' + renderTree(treeData, checkedMap) + '</div>',
                onOpen: function (body) {
                    body.querySelector('#role-perm-checkall').addEventListener('click', function () {
                        U.qa('input[type=checkbox]', body).forEach(function (c) { c.checked = true; });
                    });
                    body.querySelector('#role-perm-uncheckall').addEventListener('click', function () {
                        U.qa('input[type=checkbox]', body).forEach(function (c) { c.checked = false; });
                    });
                },
                onSave: function (body) {
                    var permIds = U.qa('input[type=checkbox]:checked', body).map(function (c) { return parseInt(c.value, 10); }).filter(Boolean);
                    return U.post('/admin/system/roles/perms/set', {role_id: roleId, perm_ids: permIds}).then(function (res) {
                        if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return false; }
                        U.toast('保存成功', 'ok');
                    });
                }
            });
        });
    }
    U.on('#role-search-btn', 'click', function () { table.reload(U.formData('#role-search')); });
    U.on('#role-reset-btn', 'click', function () { setTimeout(function () { table.reload({}); }, 0); });
    U.on('#role-refresh-btn', 'click', function () { table.refresh(); });
    U.on('#role-add-btn', 'click', function () { openForm({}); });
    U.on('#role-table', 'click', function (e) {
        var a = e.target.closest('a'); if (!a) return;
        var row = (table.rows() || [])[e.target.closest('tr').getAttribute('data-idx')];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-perm')) openPerms(row);
        if (a.classList.contains('js-edit')) openForm(row);
        if (a.classList.contains('js-del') && U.confirm('确认删除该角色？')) {
            U.post('/admin/system/roles/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh(); U.toast('删除成功', 'ok');
            });
        }
    });
})();
</script>
@endpush
