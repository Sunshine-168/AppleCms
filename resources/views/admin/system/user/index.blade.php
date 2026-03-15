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

<script src="{{ asset('static/admin/layui/layui.js') }}"></script>
<script>
    layui.use(['table', 'form'], function () {
        var $ = layui.$;
        var table = layui.table;
        var form = layui.form;

        table.render({
            elem: '#sysuser-table',
            id: 'sysuser-table',
            url: '/admin/sysuser/list',
            method: 'get',
            page: true,
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
                {field: 'role', title: '角色', width: 90},
                {field: 'login_ip', title: '登录IP', width: 140},
                {field: 'login_time', title: '登录时间', width: 170}
            ]]
        });

        form.on('submit(sysuser-search-btn)', function (obj) {
            table.reload('sysuser-table', {
                where: obj.field || {},
                page: {curr: 1}
            });
            return false;
        });
    });
</script>
</body>
</html>
