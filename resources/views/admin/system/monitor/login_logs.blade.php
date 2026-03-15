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
            <form class="layui-form" lay-filter="sysuserlog-search">
                <div class="layui-form-item">
                    <div class="layui-inline">
                        <input type="text" name="username" placeholder="用户名" autocomplete="off" class="layui-input">
                    </div>
                    <div class="layui-inline">
                        <input type="text" name="login_ip" placeholder="登录IP" autocomplete="off" class="layui-input">
                    </div>
                    <div class="layui-inline">
                        <input type="text" name="start_time" id="sysuserlog-start" placeholder="开始日期" autocomplete="off" class="layui-input">
                    </div>
                    <div class="layui-inline">
                        <input type="text" name="end_time" id="sysuserlog-end" placeholder="结束日期" autocomplete="off" class="layui-input">
                    </div>
                    <div class="layui-inline">
                        <button class="layui-btn" lay-submit lay-filter="sysuserlog-search-btn">查询</button>
                        <button type="reset" class="layui-btn layui-btn-primary" id="sysuserlog-reset-btn">重置</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="layui-card">
        <div class="layui-card-body">
            <table id="sysuserlog-table" lay-filter="sysuserlog-table"></table>
        </div>
    </div>
</div>

<script type="text/html" id="sysuserlog-toolbar">
    <div class="layui-btn-container">
        <button class="layui-btn layui-btn-sm layui-btn-primary" lay-event="refresh">刷新</button>
    </div>
</script>

<script src="{{ asset('static/admin/layui/layui.js') }}"></script>
<script>
    layui.use(['table', 'form', 'layer', 'laydate'], function () {
        var $ = layui.$;
        var table = layui.table;
        var form = layui.form;
        var laydate = layui.laydate;

        laydate.render({elem: '#sysuserlog-start', type: 'date'});
        laydate.render({elem: '#sysuserlog-end', type: 'date'});

        table.render({
            elem: '#sysuserlog-table',
            id: 'sysuserlog-table',
            url: '/admin/system/monitor/login-logs/list',
            method: 'get',
            page: true,
            toolbar: '#sysuserlog-toolbar',
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
                {field: 'username', title: '用户名', width: 160},
                {field: 'login_ip', title: '登录IP', width: 150},
                {field: 'ip_address', title: '归属地', minWidth: 180},
                {field: 'login_agent', title: 'UA请求头', minWidth: 260},
                {field: 'create_time', title: '时间', width: 180}
            ]]
        });

        form.on('submit(sysuserlog-search-btn)', function (obj) {
            table.reload('sysuserlog-table', {
                where: obj.field || {},
                page: {curr: 1}
            });
            return false;
        });

        $('#sysuserlog-reset-btn').on('click', function () {
            table.reload('sysuserlog-table', {
                where: {},
                page: {curr: 1}
            });
        });

        table.on('toolbar(sysuserlog-table)', function (obj) {
            if (obj.event === 'refresh') {
                table.reload('sysuserlog-table');
            }
        });
    });
</script>
</body>
</html>
