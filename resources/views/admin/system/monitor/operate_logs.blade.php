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
            <form class="layui-form" lay-filter="operate-log-search">
                <div class="layui-form-item">
                    <div class="layui-inline">
                        <input type="text" name="username" placeholder="用户名" autocomplete="off" class="layui-input">
                    </div>
                    <div class="layui-inline">
                        <input type="text" name="login_ip" placeholder="IP" autocomplete="off" class="layui-input">
                    </div>
                    <div class="layui-inline">
                        <input type="text" name="route" placeholder="路由" autocomplete="off" class="layui-input">
                    </div>
                    <div class="layui-inline">
                        <input type="text" name="url" placeholder="URL" autocomplete="off" class="layui-input">
                    </div>
                    <div class="layui-inline">
                        <select name="method">
                            <option value="">请求方法</option>
                            <option value="GET">GET</option>
                            <option value="POST">POST</option>
                            <option value="PUT">PUT</option>
                            <option value="PATCH">PATCH</option>
                            <option value="DELETE">DELETE</option>
                        </select>
                    </div>
                    <div class="layui-inline">
                        <select name="status">
                            <option value="">状态</option>
                            <option value="1">成功</option>
                            <option value="0">失败</option>
                        </select>
                    </div>
                    <div class="layui-inline">
                        <input type="text" name="start_time" id="operate-log-start" placeholder="开始日期" autocomplete="off" class="layui-input">
                    </div>
                    <div class="layui-inline">
                        <input type="text" name="end_time" id="operate-log-end" placeholder="结束日期" autocomplete="off" class="layui-input">
                    </div>
                    <div class="layui-inline">
                        <button class="layui-btn" lay-submit lay-filter="operate-log-search-btn">查询</button>
                        <button type="reset" class="layui-btn layui-btn-primary" id="operate-log-reset-btn">重置</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="layui-card">
        <div class="layui-card-body">
            <table id="operate-log-table" lay-filter="operate-log-table"></table>
        </div>
    </div>
</div>

<script type="text/html" id="operate-log-toolbar">
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

        laydate.render({elem: '#operate-log-start', type: 'date'});
        laydate.render({elem: '#operate-log-end', type: 'date'});

        table.render({
            elem: '#operate-log-table',
            id: 'operate-log-table',
            url: '/admin/system/monitor/operate-logs/list',
            method: 'get',
            page: true,
            toolbar: '#operate-log-toolbar',
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
                {field: 'username', title: '用户名', width: 140},
                {field: 'status', title: '状态', width: 90, templet: function (d) {
                    var v = typeof d.status === 'number' ? d.status : parseInt(d.status, 10);
                    return v === 1 ? '<span class="layui-badge layui-bg-green">成功</span>' : '<span class="layui-badge">失败</span>';
                }},
                {field: 'method', title: '方法', width: 90},
                {field: 'route', title: '路由', minWidth: 180},
                {field: 'url', title: 'URL', minWidth: 220},
                {field: 'login_ip', title: 'IP', width: 140},
                {field: 'ip_address', title: '归属地', width: 160},
                {field: 'duration_ms', title: '耗时ms', width: 110},
                {field: 'create_time', title: '时间', width: 180}
            ]]
        });

        form.on('submit(operate-log-search-btn)', function (obj) {
            table.reload('operate-log-table', {
                where: obj.field || {},
                page: {curr: 1}
            });
            return false;
        });

        $('#operate-log-reset-btn').on('click', function () {
            table.reload('operate-log-table', {
                where: {},
                page: {curr: 1}
            });
        });

        table.on('toolbar(operate-log-table)', function (obj) {
            if (obj.event === 'refresh') {
                table.reload('operate-log-table');
            }
        });
    });
</script>
</body>
</html>

