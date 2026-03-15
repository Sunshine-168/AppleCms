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
            <div class="layui-form" lay-filter="dbsql-form">
                <div class="layui-form-item layui-form-text">
                    <label class="layui-form-label">SQL</label>
                    <div class="layui-input-block">
                        <textarea id="dbsql-input" placeholder="请输入一条SQL语句，例如：SELECT * FROM users LIMIT 10" class="layui-textarea" style="min-height: 180px;"></textarea>
                    </div>
                </div>
                <div class="layui-form-item">
                    <div class="layui-input-block">
                        <button class="layui-btn" id="dbsql-run">执行</button>
                        <button class="layui-btn layui-btn-primary" id="dbsql-clear">清空</button>
                    </div>
                </div>
                <div class="layui-word-aux">仅支持执行一条SQL语句；执行写操作会直接修改数据库</div>
            </div>
        </div>
    </div>

    <div class="layui-card">
        <div class="layui-card-body">
            <blockquote class="layui-elem-quote" id="dbsql-msg">等待执行</blockquote>
            <div id="dbsql-result-table" style="display:none;">
                <table id="dbsql-table" lay-filter="dbsql-table"></table>
            </div>
            <pre id="dbsql-result-text" style="display:none; white-space: pre-wrap; word-break: break-all; background: #f7f7f7; padding: 12px; border: 1px solid #eee;"></pre>
        </div>
    </div>
</div>

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

        function setMsg(text) {
            $('#dbsql-msg').text(text || '');
        }

        function showTextResult(text) {
            $('#dbsql-result-table').hide();
            $('#dbsql-result-text').show().text(text || '');
        }

        function showTableResult(cols, rows) {
            $('#dbsql-result-text').hide();
            $('#dbsql-result-table').show();

            table.render({
                elem: '#dbsql-table',
                id: 'dbsql-table',
                data: rows || [],
                page: false,
                cols: [cols || []],
                limit: 999999
            });
        }

        $('#dbsql-clear').on('click', function (e) {
            e.preventDefault();
            $('#dbsql-input').val('');
            setMsg('等待执行');
            $('#dbsql-result-table').hide();
            $('#dbsql-result-text').hide().text('');
        });

        $('#dbsql-run').on('click', function (e) {
            e.preventDefault();

            var sql = $.trim($('#dbsql-input').val() || '');
            if (!sql) {
                layer.msg('请输入SQL');
                return;
            }

            var idx = layer.load(1);
            $.post('/admin/system/database/sql/run', {sql: sql}, function (res) {
                layer.close(idx);
                if (!res || res.code !== 0) {
                    setMsg('执行失败');
                    showTextResult(res && res.msg ? res.msg : '执行失败');
                    return;
                }

                var data = res.data || {};
                if (data.type === 'query') {
                    var columns = Array.isArray(data.columns) ? data.columns : [];
                    var cols = columns.map(function (c) {
                        return {field: c, title: c, minWidth: 120};
                    });
                    if (cols.length === 0) {
                        cols = [{field: '_', title: '结果'}];
                    }
                    setMsg('执行成功，返回 ' + (data.count || 0) + ' 行');
                    showTableResult(cols, Array.isArray(data.rows) ? data.rows : []);
                    return;
                }

                if (data.type === 'affecting') {
                    setMsg('执行成功，影响行数 ' + (data.affected || 0));
                    showTextResult('影响行数: ' + (data.affected || 0));
                    return;
                }

                setMsg('执行成功');
                showTextResult(JSON.stringify(data, null, 2));
            }, 'json').fail(function () {
                layer.close(idx);
                setMsg('请求失败');
                showTextResult('请求失败');
            });
        });
    });
</script>
</body>
</html>
