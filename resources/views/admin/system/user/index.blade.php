<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{conf('name')}}</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="{{ asset('static/admin/layui/css/layui.css') }}" media="all">
    <link rel="stylesheet" href="{{ asset('static/admin/style/admin.css') }}" media="all">
</head>
<body>
<div class="layui-fluid">
    <div class="layui-card">
        <div class="layui-card-body">
            <form class="layui-form" lay-filter="sysuser-search">
                <div class="layui-form-item">
                    <div class="layui-inline">
                        <input type="text" name="username" placeholder="用户名" autocomplete="off" class="layui-input">
                    </div>
                    <div class="layui-inline">
                        <button class="layui-btn" lay-submit lay-filter="sysuser-search-btn">查询</button>
                        <button type="reset" class="layui-btn layui-btn-primary">重置</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="layui-card">
        <div class="layui-card-body">
            <table id="sysuser-table" lay-filter="sysuser-table"></table>
        </div>
    </div>
</div>

<script type="text/html" id="sysuser-toolbar">
    <div class="layui-btn-container">
        <button class="layui-btn layui-btn-sm" lay-event="add">新增管理员</button>
        <button class="layui-btn layui-btn-sm layui-btn-primary" lay-event="refresh">刷新</button>
    </div>
</script>

<script type="text/html" id="sysuser-rowbar">
    <a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>
    <a class="layui-btn layui-btn-xs layui-btn-danger" lay-event="del">删除</a>
</script>

<script src="{{ asset('static/admin/layui/layui.js') }}"></script>
<script>
    layui.use(['table', 'form', 'layer'], function () {
        var $ = layui.$;
        var table = layui.table;
        var form = layui.form;
        var layer = layui.layer;

        var csrfToken = '{{ csrf_token() }}';
        if (csrfToken) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            });
        }

        function escapeHtml(value) {
            return String(value || '').replace(/[&<>"']/g, function (s) {
                return {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;'
                }[s];
            });
        }

        function apiPost(url, data, onOk) {
            $.ajax({
                url: url,
                type: 'post',
                dataType: 'json',
                data: data || {},
                success: function (res) {
                    if (res && res.code === 0) {
                        if (typeof onOk === 'function') {
                            onOk(res);
                        }
                        return;
                    }
                    layer.msg(res && res.msg ? res.msg : '操作失败', {icon: 2});
                },
                error: function () {
                    layer.msg('网络错误', {icon: 2});
                }
            });
        }

        function openUserDialog(mode, row) {
            var isEdit = mode === 'edit';
            row = row || {};
            var roleValue = typeof row.role === 'number' ? row.role : parseInt(row.role, 10);
            if (isNaN(roleValue)) {
                roleValue = 1;
            }

            var title = isEdit ? '编辑管理员' : '新增管理员';
            var html = [
                '<div style="padding: 16px 18px 0 18px;">',
                '<div class="layui-form layui-form-pane">',
                '<input type="hidden" name="id" value="' + escapeHtml(row.id) + '">',
                '<div class="layui-form-item">',
                '<label class="layui-form-label">用户名</label>',
                '<div class="layui-input-block">',
                '<input type="text" name="username" autocomplete="off" placeholder="请输入用户名" class="layui-input" value="' + escapeHtml(row.username) + '">',
                '</div>',
                '</div>',
                '<div class="layui-form-item">',
                '<label class="layui-form-label">邮箱</label>',
                '<div class="layui-input-block">',
                '<input type="text" name="email" autocomplete="off" placeholder="请输入邮箱" class="layui-input" value="' + escapeHtml(row.email) + '">',
                '</div>',
                '</div>',
                '<div class="layui-form-item">',
                '<label class="layui-form-label">备注</label>',
                '<div class="layui-input-block">',
                '<input type="text" name="remark" autocomplete="off" placeholder="请输入备注" class="layui-input" value="' + escapeHtml(row.remark) + '">',
                '</div>',
                '</div>',
                '<div class="layui-form-item">',
                '<label class="layui-form-label">角色</label>',
                '<div class="layui-input-block">',
                '<select name="role">',
                '<option value="0"' + (roleValue === 0 ? ' selected' : '') + '>超级管理员</option>',
                '<option value="1"' + (roleValue === 1 ? ' selected' : '') + '>普通管理员</option>',
                '</select>',
                '</div>',
                '</div>',
                '<div class="layui-form-item">',
                '<label class="layui-form-label">密码</label>',
                '<div class="layui-input-block">',
                '<input type="password" name="password" autocomplete="new-password" placeholder="' + (isEdit ? '留空表示不修改' : '留空默认 123456') + '" class="layui-input">',
                '</div>',
                '</div>',
                '</div>',
                '</div>'
            ].join('');

            layer.open({
                type: 1,
                title: title,
                area: ['480px', '420px'],
                content: html,
                btn: ['保存', '取消'],
                yes: function (index, layero) {
                    var id = layero.find('input[name=id]').val();
                    var username = $.trim(layero.find('input[name=username]').val());
                    var password = $.trim(layero.find('input[name=password]').val());
                    var email = $.trim(layero.find('input[name=email]').val());
                    var remark = $.trim(layero.find('input[name=remark]').val());
                    var role = layero.find('select[name=role]').val();

                    if (!username) {
                        layer.msg('请输入用户名', {icon: 2});
                        return;
                    }

                    if (isEdit) {
                        var payload = {id: id, username: username, email: email, remark: remark, role: role};
                        if (password) {
                            payload.password = password;
                        }
                        apiPost('/admin/user/update', payload, function () {
                            layer.close(index);
                            table.reload('sysuser-table');
                            layer.msg('保存成功', {icon: 1});
                        });
                        return;
                    }

                    apiPost('/admin/user/add', {username: username, password: password, email: email, remark: remark, role: role}, function () {
                        layer.close(index);
                        table.reload('sysuser-table');
                        layer.msg('添加成功', {icon: 1});
                    });
                },
                success: function () {
                    form.render();
                }
            });
        }

        table.render({
            elem: '#sysuser-table',
            id: 'sysuser-table',
            url: '/admin/user/list',
            method: 'get',
            page: true,
            toolbar: '#sysuser-toolbar',
            defaultToolbar: [],
            parseData: function (res) {
                var data = res && res.data ? res.data : {};
                return {
                    code: res && typeof res.code === 'number' ? res.code : 1,
                    msg: res && typeof res.msg === 'string' ? res.msg : '',
                    count: data && typeof data.total === 'number' ? data.total : 0,
                    data: data && Array.isArray(data.data) ? data.data : []
                };
            },
            cols: [[
                {field: 'id', title: 'ID', width: 90, sort: true},
                {field: 'username', title: '用户名', minWidth: 160},
                {field: 'email', title: '邮箱', minWidth: 200},
                {field: 'remark', title: '备注', minWidth: 200},
                {field: 'role', title: '角色', width: 120, templet: function (d) {
                    var role = typeof d.role === 'number' ? d.role : parseInt(d.role, 10);
                    if (role === 0) {
                        return '<span class="layui-badge layui-bg-blue">超级管理员</span>';
                    }
                    if (role === 1) {
                        return '<span class="layui-badge layui-bg-gray">普通管理员</span>';
                    }
                    return '<span class="layui-badge-rim">未知</span>';
                }},
                {field: 'login_ip', title: '登录IP', width: 140},
                {field: 'login_time', title: '登录时间', width: 170},
                {title: '操作', fixed: 'right', align: 'center', width: 140, toolbar: '#sysuser-rowbar'}
            ]]
        });

        form.on('submit(sysuser-search-btn)', function (obj) {
            table.reload('sysuser-table', {
                where: obj.field || {},
                page: {curr: 1}
            });
            return false;
        });

        table.on('toolbar(sysuser-table)', function (obj) {
            if (obj.event === 'add') {
                openUserDialog('add');
                return;
            }
            if (obj.event === 'refresh') {
                table.reload('sysuser-table');
            }
        });

        table.on('tool(sysuser-table)', function (obj) {
            if (obj.event === 'edit') {
                openUserDialog('edit', obj.data);
                return;
            }
            if (obj.event === 'del') {
                layer.confirm('确定删除该管理员吗？', function (index) {
                    apiPost('/admin/user/delete', {id: obj.data.id}, function () {
                        layer.close(index);
                        table.reload('sysuser-table');
                        layer.msg('删除成功', {icon: 1});
                    });
                });
            }
        });
    });
</script>
</body>
</html>
