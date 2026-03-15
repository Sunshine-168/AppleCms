<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{conf('name')}}</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('static/admin/layui/css/layui.css') }}" media="all">
    <link rel="stylesheet" href="{{ asset('static/admin/style/admin.css') }}" media="all">
</head>
<body>
<div class="layui-fluid">
    <div class="layui-card">
        <div class="layui-card-body">
            <div class="layui-btn-container">
                <a class="layui-btn layui-btn-primary" href="/admin/system/database/backup">去备份</a>
                <button class="layui-btn layui-btn-primary" id="dbrestore-refresh">刷新列表</button>
            </div>
            <div class="layui-word-aux">恢复会覆盖当前数据库数据，请谨慎操作</div>
        </div>
    </div>

    <div class="layui-card">
        <div class="layui-card-body">
            <table id="dbrestore-table" lay-filter="dbrestore-table"></table>
        </div>
    </div>
</div>

<script type="text/html" id="dbrestore-actions">
    <a class="layui-btn layui-btn-warm layui-btn-xs" lay-event="restore">恢复</a>
    <a class="layui-btn layui-btn-xs" lay-event="download">下载</a>
    <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="delete">删除</a>
</script>

<script src="{{ asset('static/admin/layui/layui.js') }}"></script>
<script>
    layui.use(['table', 'layer'], function () {
        var $ = layui.$;
        var table = layui.table;
        var layer = layui.layer;

        var csrfToken = $('meta[name=csrf-token]').attr('content');
        if (csrfToken) {
            $.ajaxSetup({headers: {'X-CSRF-TOKEN': csrfToken}});
        }

        function renderTable() {
            table.render({
                elem: '#dbrestore-table',
                id: 'dbrestore-table',
                url: '/admin/system/database/restore/files',
                method: 'get',
                page: false,
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
                    {field: 'name', title: '文件名', minWidth: 260},
                    {field: 'size', title: '大小(B)', width: 120},
                    {field: 'time', title: '时间', width: 180},
                    {title: '操作', width: 200, fixed: 'right', toolbar: '#dbrestore-actions'}
                ]]
            });
        }

        renderTable();

        $('#dbrestore-refresh').on('click', function () {
            table.reload('dbrestore-table');
        });

        table.on('tool(dbrestore-table)', function (obj) {
            var data = obj.data || {};
            if (obj.event === 'download') {
                var url = '/admin/system/database/backup/download?file=' + encodeURIComponent(data.name || '');
                window.open(url);
                return;
            }
            if (obj.event === 'delete') {
                layer.confirm('确认删除该备份文件？', function (index) {
                    layer.close(index);
                    $.post('/admin/system/database/backup/delete', {file: data.name || ''}, function (res) {
                        if (!res || res.code !== 0) {
                            layer.msg(res && res.msg ? res.msg : '删除失败');
                            return;
                        }
                        layer.msg('删除成功');
                        table.reload('dbrestore-table');
                    });
                });
                return;
            }
            if (obj.event === 'restore') {
                layer.confirm('恢复会覆盖当前数据库数据，确认恢复？', function (index) {
                    layer.close(index);
                    var idx = layer.load(1);
                    $.post('/admin/system/database/restore/run', {file: data.name || ''}, function (res) {
                        layer.close(idx);
                        if (!res || res.code !== 0) {
                            layer.msg(res && res.msg ? res.msg : '恢复失败');
                            return;
                        }
                        layer.msg('恢复成功');
                    }).fail(function () {
                        layer.close(idx);
                        layer.msg('请求失败');
                    });
                });
            }
        });
    });
</script>
</body>
</html>
