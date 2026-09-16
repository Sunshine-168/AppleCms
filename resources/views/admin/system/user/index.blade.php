@extends('admin.layouts.inner')
@section('title', admin_t('page.admins'))

@section('plain')
<div class="card card-panel">
    <div class="card-body">
        <form class="filter-bar" id="sysuser-search" onsubmit="return false;">
            <input type="text" name="username" placeholder="用户名">
            <button type="button" class="btn btn-sm" id="sysuser-search-btn">查询</button>
            <button type="reset" class="btn btn-muted btn-sm" id="sysuser-reset-btn">重置</button>
        </form>
    </div>
</div>
<div class="card card-panel">
    <div class="card-header">
        <span>管理员</span>
        <div>
            <button type="button" class="btn btn-sm" id="sysuser-add-btn">新增管理员</button>
            <button type="button" class="btn btn-muted btn-sm" id="sysuser-refresh-btn">刷新</button>
        </div>
    </div>
    <div class="card-body"><div id="sysuser-table"></div></div>
</div>
<template id="sysuser-dialog-tpl">
    <form>
        <input type="hidden" name="id">
        <label>用户名</label>
        <input type="text" name="username">
        <label>邮箱</label>
        <input type="text" name="email">
        <label>备注</label>
        <input type="text" name="remark">
        <label>角色名称</label>
        <select name="role_id"></select>
        <label>管理员类型</label>
        <select name="role">
            <option value="0">超级管理员</option>
            <option value="1">普通管理员</option>
        </select>
        <label>密码</label>
        <input type="password" name="password" autocomplete="new-password">
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var roleOptions = null;
    function loadRoles(cb) {
        if (roleOptions) { cb(roleOptions); return; }
        U.get('/admin/system/roles/options').then(function (res) {
            roleOptions = (res && res.code === 0) ? (res.data || []) : [];
            cb(roleOptions);
        });
    }
    function fillRoles(sel, roles, selectedId) {
        var html = '<option value="0">请选择</option>';
        (roles || []).forEach(function (r) {
            var name = r.name || '';
            if (String(r.status) === '0') name += '（禁用）';
            html += '<option value="' + U.escape(r.id) + '">' + U.escape(name) + '</option>';
        });
        sel.innerHTML = html;
        sel.value = String(selectedId || 0);
    }
    var table = U.table({
        el: '#sysuser-table',
        url: '/admin/user/list',
        cols: [
            {key: 'id', title: 'ID', width: 70},
            {key: 'username', title: '用户名'},
            {key: 'email', title: '邮箱'},
            {key: 'remark', title: '备注'},
            {key: 'role_name', title: '角色名称'},
            {title: '管理员类型', width: 120, html: function (d) {
                return String(d.role) === '0' ? '<span class="status status-info">超级管理员</span>' : U.status(false, '普通管理员');
            }},
            {key: 'login_ip', title: '登录IP', width: 120},
            {key: 'login_time', title: '登录时间', width: 160},
            {title: '操作', cls: 'actions', html: function () { return '<a href="#" class="btn-link js-edit">编辑</a><a href="#" class="btn-link js-del">删除</a>'; }}
        ]
    });
    function openUserDialog(mode, row) {
        row = row || {};
        U.dialog({
            title: mode === 'edit' ? '编辑管理员' : '新增管理员',
            content: document.getElementById('sysuser-dialog-tpl').innerHTML,
            onOpen: function (body) {
                U.fillForm(body.querySelector('form'), {
                    id: row.id || '',
                    username: row.username || '',
                    email: row.email || '',
                    remark: row.remark || '',
                    role: row.role == null ? 1 : row.role
                });
                loadRoles(function (roles) {
                    fillRoles(body.querySelector('select[name=role_id]'), roles, row.role_id || 0);
                });
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (!data.username) { U.toast('请输入用户名', 'err'); return false; }
                var url = mode === 'edit' ? '/admin/user/update' : '/admin/user/add';
                if (mode !== 'edit' && !data.password) data.password = '123456';
                if (mode === 'edit' && !data.password) delete data.password;
                return U.post(url, data).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return false; }
                    U.toast(mode === 'edit' ? '保存成功' : '新增成功', 'ok');
                    table.refresh();
                });
            }
        });
    }
    U.on('#sysuser-search-btn', 'click', function () { table.reload(U.formData('#sysuser-search')); });
    U.on('#sysuser-reset-btn', 'click', function () { setTimeout(function () { table.reload({}); }, 0); });
    U.on('#sysuser-refresh-btn', 'click', function () { table.refresh(); });
    U.on('#sysuser-add-btn', 'click', function () { openUserDialog('add'); });
    U.on('#sysuser-table', 'click', function (e) {
        var a = e.target.closest('a'); if (!a) return;
        var row = (table.rows() || [])[e.target.closest('tr').getAttribute('data-idx')];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-edit')) openUserDialog('edit', row);
        if (a.classList.contains('js-del') && U.confirm('确定删除该管理员吗？')) {
            U.post('/admin/user/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh(); U.toast('删除成功', 'ok');
            });
        }
    });
})();
</script>
@endpush
