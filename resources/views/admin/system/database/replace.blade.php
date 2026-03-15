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
            <div class="layui-form" lay-filter="dbreplace-form">
                <div class="layui-form-item">
                    <label class="layui-form-label">选择表</label>
                    <div class="layui-input-inline" style="width: 320px;">
                        <select id="dbreplace-table" lay-filter="dbreplace-table">
                            <option value="">请选择表</option>
                        </select>
                    </div>
                    <div class="layui-form-mid layui-word-aux">选中表后会加载字段</div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">字段</label>
                    <div class="layui-input-block">
                        <div id="dbreplace-fields" class="layui-btn-container"></div>
                        <div class="layui-word-aux">点击字段加入“已选字段”</div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">已选字段</label>
                    <div class="layui-input-block">
                        <table id="dbreplace-selected" lay-filter="dbreplace-selected"></table>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">被替换内容</label>
                    <div class="layui-input-block">
                        <input type="text" id="dbreplace-from" placeholder="例如：old_text" autocomplete="off" class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">替换为</label>
                    <div class="layui-input-block">
                        <input type="text" id="dbreplace-to" placeholder="例如：new_text" autocomplete="off" class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item layui-form-text">
                    <label class="layui-form-label">替换条件</label>
                    <div class="layui-input-block">
                        <textarea id="dbreplace-where" placeholder="可选，例如：id &gt; 100 AND status = 1" class="layui-textarea" style="min-height: 90px;"></textarea>
                        <div class="layui-word-aux">留空则对整张表执行</div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <div class="layui-input-block">
                        <button class="layui-btn layui-btn-danger" id="dbreplace-run">执行替换</button>
                        <button class="layui-btn layui-btn-primary" id="dbreplace-reset">重置</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/html" id="dbreplace-selected-actions">
    <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="remove">移除</a>
</script>

<script src="{{ asset('static/admin/layui/layui.js') }}"></script>
<script>
    layui.use(['form', 'table', 'layer'], function () {
        var $ = layui.$;
        var form = layui.form;
        var table = layui.table;
        var layer = layui.layer;

        var csrfToken = $('meta[name=csrf-token]').attr('content');
        if (csrfToken) {
            $.ajaxSetup({headers: {'X-CSRF-TOKEN': csrfToken}});
        }

        var columnsCache = {};
        var selectedFields = [];

        function renderSelectedTable() {
            table.render({
                elem: '#dbreplace-selected',
                id: 'dbreplace-selected',
                data: selectedFields.map(function (f) { return {field: f}; }),
                page: false,
                cols: [[
                    {field: 'field', title: '字段名', minWidth: 260},
                    {title: '操作', width: 100, toolbar: '#dbreplace-selected-actions'}
                ]],
                limit: 999999
            });
        }

        function addSelectedField(field) {
            field = $.trim(field || '');
            if (!field) return;
            if (selectedFields.indexOf(field) !== -1) return;
            selectedFields.push(field);
            renderSelectedTable();
        }

        function removeSelectedField(field) {
            var idx = selectedFields.indexOf(field);
            if (idx === -1) return;
            selectedFields.splice(idx, 1);
            renderSelectedTable();
        }

        table.on('tool(dbreplace-selected)', function (obj) {
            var data = obj.data || {};
            if (obj.event === 'remove') {
                removeSelectedField(data.field || '');
            }
        });

        function renderFieldsButtons(tableName) {
            var rows = columnsCache[tableName] || [];
            var html = '';
            rows.forEach(function (r) {
                var field = r.field || '';
                var comment = r.comment || '';
                var title = field + (comment ? ('（' + comment + '）') : '');
                html += '<button type="button" class="layui-btn layui-btn-primary layui-btn-sm dbreplace-field-btn" data-field="' + encodeURIComponent(field) + '" style="margin: 0 8px 8px 0;">' + title + '</button>';
            });
            $('#dbreplace-fields').html(html);
        }

        $(document).on('click', '.dbreplace-field-btn', function () {
            var field = decodeURIComponent($(this).data('field') || '');
            addSelectedField(field);
        });

        function loadTables() {
            $.get('/admin/system/database/dict/tables', {}, function (res) {
                if (!res || res.code !== 0) {
                    layer.msg(res && res.msg ? res.msg : '加载表失败');
                    return;
                }
                var data = res.data && Array.isArray(res.data.data) ? res.data.data : [];
                var options = '<option value="">请选择表</option>';
                data.forEach(function (item) {
                    options += '<option value="' + (item.name || '') + '">' + (item.name || '') + '</option>';
                });
                $('#dbreplace-table').html(options);
                form.render('select');
            }, 'json');
        }

        function loadColumns(tableName) {
            if (!tableName) {
                $('#dbreplace-fields').empty();
                selectedFields = [];
                renderSelectedTable();
                return;
            }

            if (columnsCache[tableName]) {
                renderFieldsButtons(tableName);
                selectedFields = [];
                renderSelectedTable();
                return;
            }

            var idx = layer.load(1);
            $.get('/admin/system/database/dict/columns', {table: tableName}, function (res) {
                layer.close(idx);
                if (!res || res.code !== 0) {
                    layer.msg(res && res.msg ? res.msg : '加载字段失败');
                    return;
                }
                var rows = res.data && Array.isArray(res.data.data) ? res.data.data : [];
                columnsCache[tableName] = rows;
                renderFieldsButtons(tableName);
                selectedFields = [];
                renderSelectedTable();
            }, 'json').fail(function () {
                layer.close(idx);
                layer.msg('请求失败');
            });
        }

        form.on('select(dbreplace-table)', function (data) {
            loadColumns(data.value || '');
        });

        $('#dbreplace-reset').on('click', function (e) {
            e.preventDefault();
            $('#dbreplace-table').val('');
            form.render('select');
            $('#dbreplace-fields').empty();
            selectedFields = [];
            renderSelectedTable();
            $('#dbreplace-from').val('');
            $('#dbreplace-to').val('');
            $('#dbreplace-where').val('');
        });

        $('#dbreplace-run').on('click', function (e) {
            e.preventDefault();
            var tableName = $.trim($('#dbreplace-table').val() || '');
            var from = $('#dbreplace-from').val() || '';
            var to = $('#dbreplace-to').val() || '';
            var where = $('#dbreplace-where').val() || '';

            if (!tableName) {
                layer.msg('请选择表');
                return;
            }
            if (!selectedFields.length) {
                layer.msg('请选择字段');
                return;
            }
            if (from === '') {
                layer.msg('请输入被替换内容');
                return;
            }

            layer.confirm('确认执行批量替换？该操作会直接修改数据库数据', function (index) {
                layer.close(index);
                var idx = layer.load(1);
                $.post('/admin/system/database/replace/run', {
                    table: tableName,
                    fields: selectedFields,
                    from: from,
                    to: to,
                    where: where
                }, function (res) {
                    layer.close(idx);
                    if (!res || res.code !== 0) {
                        layer.msg(res && res.msg ? res.msg : '替换失败');
                        return;
                    }
                    var affected = res.data && typeof res.data.affected === 'number' ? res.data.affected : 0;
                    layer.msg('替换成功，影响行数：' + affected);
                }, 'json').fail(function () {
                    layer.close(idx);
                    layer.msg('请求失败');
                });
            });
        });

        renderSelectedTable();
        loadTables();
    });
</script>
</body>
</html>
