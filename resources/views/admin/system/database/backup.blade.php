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
                <button class="layui-btn" id="dbbackup-run">立即备份</button>
                <button class="layui-btn layui-btn-primary" id="dbbackup-refresh">刷新列表</button>
            </div>
        </div>
    </div>

    <div class="layui-card">
        <div class="layui-card-body">
            <table id="dbbackup-table" lay-filter="dbbackup-table"></table>
        </div>
    </div>
</div>

<script type="text/html" id="dbbackup-actions">
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
                elem: '#dbbackup-table',
                id: 'dbbackup-table',
                url: '/admin/system/database/backup/files',
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
                    {title: '操作', width: 140, fixed: 'right', toolbar: '#dbbackup-actions'}
                ]]
            });
        }

        renderTable();

        $('#dbbackup-refresh').on('click', function () {
            table.reload('dbbackup-table');
        });

        $('#dbbackup-run').on('click', function () {
            var idx = layer.load(1);
            $.post('/admin/system/database/backup/run', {}, function (res) {
                layer.close(idx);
                if (!res || res.code !== 0) {
                    layer.msg(res && res.msg ? res.msg : '备份失败');
                    return;
                }
                layer.msg('备份成功');
                table.reload('dbbackup-table');
            }).fail(function () {
                layer.close(idx);
                layer.msg('请求失败');
            });
        });

        table.on('tool(dbbackup-table)', function (obj) {
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
                        table.reload('dbbackup-table');
                    });
                });
            }
        });
    });
</script>
</body>
</html>
