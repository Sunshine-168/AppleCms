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
            <form class="layui-form" lay-filter="dbdict-form">
                <div class="layui-form-item">
                    <div class="layui-inline" style="min-width: 320px;">
                        <select name="table" id="dbdict-table-select" lay-filter="dbdict-table-select">
                            <option value="">请选择表</option>
                        </select>
                    </div>
                    <div class="layui-inline">
                        <button class="layui-btn" type="button" id="dbdict-refresh">刷新</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="layui-card">
        <div class="layui-card-body">
            <table id="dbdict-columns-table" lay-filter="dbdict-columns-table"></table>
        </div>
    </div>
</div>

<script src="{{ asset('static/admin/layui/layui.js') }}"></script>
<script>
    layui.use(['table', 'form', 'layer'], function () {
        var $ = layui.$;
        var table = layui.table;
        var form = layui.form;
        var layer = layui.layer;

        function renderColumns(tableName) {
            table.render({
                elem: '#dbdict-columns-table',
                id: 'dbdict-columns-table',
                url: '/admin/system/database/dict/columns',
                method: 'get',
                page: false,
                where: {table: tableName},
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
                    {field: 'field', title: '字段', width: 220},
                    {field: 'type', title: '类型', width: 220},
                    {field: 'null', title: '可空', width: 90},
                    {field: 'key', title: '索引', width: 90},
                    {field: 'default', title: '默认值', width: 140},
                    {field: 'extra', title: '额外', width: 160},
                    {field: 'comment', title: '备注', minWidth: 220},
                    {field: 'collation', title: '排序规则', width: 160}
                ]]
            });
        }

        function loadTables(autoSelectFirst) {
            $.get('/admin/system/database/dict/tables', function (res) {
                if (!res || res.code !== 0) {
                    layer.msg(res && res.msg ? res.msg : '加载失败');
                    return;
                }

                var list = res.data && Array.isArray(res.data.data) ? res.data.data : [];
                var $select = $('#dbdict-table-select');
                $select.empty();
                $select.append('<option value="">请选择表</option>');

                for (var i = 0; i < list.length; i++) {
                    var item = list[i] || {};
                    var name = item.name || '';
                    if (!name) continue;
                    var label = name;
                    if (item.comment) label = label + ' - ' + item.comment;
                    $select.append('<option value="' + name + '">' + label + '</option>');
                }

                form.render('select');

                if (autoSelectFirst && list.length > 0) {
                    var first = list[0] && list[0].name ? list[0].name : '';
                    if (first) {
                        $select.val(first);
                        form.render('select');
                        renderColumns(first);
                    }
                }
            });
        }

        form.on('select(dbdict-table-select)', function (data) {
            var tableName = data && data.value ? data.value : '';
            if (!tableName) {
                table.reload('dbdict-columns-table', {data: []});
                return;
            }
            renderColumns(tableName);
        });

        $('#dbdict-refresh').on('click', function () {
            var current = $('#dbdict-table-select').val() || '';
            loadTables(false);
            if (current) {
                renderColumns(current);
            }
        });

        loadTables(true);
    });
</script>
</body>
</html>
