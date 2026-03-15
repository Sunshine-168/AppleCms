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
            <form class="layui-form" lay-filter="schedule-search">
                <div class="layui-form-item">
                    <div class="layui-inline">
                        <input type="text" name="name" placeholder="任务名称" autocomplete="off" class="layui-input">
                    </div>
                    <div class="layui-inline">
                        <select name="type">
                            <option value="">类型</option>
                            <option value="artisan">artisan</option>
                            <option value="shell">shell</option>
                            <option value="http">http</option>
                        </select>
                    </div>
                    <div class="layui-inline">
                        <select name="status">
                            <option value="">状态</option>
                            <option value="1">启用</option>
                            <option value="0">禁用</option>
                        </select>
                    </div>
                    <div class="layui-inline">
                        <button class="layui-btn" lay-submit lay-filter="schedule-search-btn">查询</button>
                        <button type="reset" class="layui-btn layui-btn-primary" id="schedule-reset-btn">重置</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="layui-card">
        <div class="layui-card-body">
            <table id="schedule-table" lay-filter="schedule-table"></table>
        </div>
    </div>
</div>

<script type="text/html" id="schedule-toolbar">
    <div class="layui-btn-container">
        <button class="layui-btn layui-btn-sm" lay-event="add">新增任务</button>
        <button class="layui-btn layui-btn-sm layui-btn-primary" lay-event="refresh">刷新</button>
    </div>
</script>

<script type="text/html" id="schedule-actions">
@verbatim
    <a class="layui-btn layui-btn-primary layui-btn-xs" lay-event="edit">编辑</a>
    <a class="layui-btn layui-btn-warm layui-btn-xs" lay-event="run">立即执行</a>
    {{# if(d.status == 1){ }}
    <a class="layui-btn layui-btn-xs" lay-event="disable">禁用</a>
    {{# } else { }}
    <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="enable">启用</a>
    {{# } }}
    <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="delete">删除</a>
@endverbatim
</script>

<script type="text/html" id="schedule-last-status">
@verbatim
    {{# if(d.last_status == 1){ }}
    <span class="layui-badge layui-bg-green">成功</span>
    {{# } else if(d.last_status == 2){ }}
    <span class="layui-badge">失败</span>
    {{# } else { }}
    <span class="layui-badge layui-bg-gray">未知</span>
    {{# } }}
@endverbatim
</script>

<script src="{{ asset('static/admin/layui/layui.js') }}"></script>
<script>
    layui.use(['table', 'form', 'layer'], function () {
        var $ = layui.$;
        var table = layui.table;
        var form = layui.form;
        var layer = layui.layer;

        var csrfToken = $('meta[name=csrf-token]').attr('content');
        if (csrfToken) {
            $.ajaxSetup({headers: {'X-CSRF-TOKEN': csrfToken}});
        }

        function openForm(data) {
            data = data || {};
            var html = ''
                + '<div style="padding:16px 18px 0 0;">'
                + '  <form class="layui-form" lay-filter="schedule-form">'
                + '    <input type="hidden" name="id" value="' + (data.id || 0) + '">'
                + '    <div class="layui-form-item">'
                + '      <label class="layui-form-label">名称</label>'
                + '      <div class="layui-input-block">'
                + '        <input type="text" name="name" value="' + (data.name || '') + '" required lay-verify="required" placeholder="任务名称" autocomplete="off" class="layui-input">'
                + '      </div>'
                + '    </div>'
                + '    <div class="layui-form-item">'
                + '      <label class="layui-form-label">标识</label>'
                + '      <div class="layui-input-block">'
                + '        <input type="text" name="code" value="' + (data.code || '') + '" placeholder="可选" autocomplete="off" class="layui-input">'
                + '      </div>'
                + '    </div>'
                + '    <div class="layui-form-item">'
                + '      <label class="layui-form-label">类型</label>'
                + '      <div class="layui-input-block">'
                + '        <select name="type" lay-filter="schedule-type">'
                + '          <option value="artisan"' + ((data.type || 'artisan') === 'artisan' ? ' selected' : '') + '>artisan</option>'
                + '          <option value="shell"' + ((data.type || '') === 'shell' ? ' selected' : '') + '>shell</option>'
                + '          <option value="http"' + ((data.type || '') === 'http' ? ' selected' : '') + '>http</option>'
                + '        </select>'
                + '      </div>'
                + '    </div>'
                + '    <div class="layui-form-item layui-form-text">'
                + '      <label class="layui-form-label">执行内容</label>'
                + '      <div class="layui-input-block">'
                + '        <textarea name="command" required lay-verify="required" placeholder="artisan: 例如 cache:clear；shell: 例如 php -v；http: 例如 https://example.com/ping" class="layui-textarea" style="min-height: 90px;">' + (data.command || '') + '</textarea>'
                + '      </div>'
                + '    </div>'
                + '    <div class="layui-form-item layui-form-text">'
                + '      <label class="layui-form-label">参数</label>'
                + '      <div class="layui-input-block">'
                + '        <textarea name="params" placeholder="可选" class="layui-textarea" style="min-height: 70px;">' + (data.params || '') + '</textarea>'
                + '      </div>'
                + '    </div>'
                + '    <div class="layui-form-item">'
                + '      <label class="layui-form-label">cron</label>'
                + '      <div class="layui-input-block">'
                + '        <input type="text" name="cron_expression" value="' + (data.cron_expression || '* * * * *') + '" required lay-verify="required" placeholder="* * * * *" autocomplete="off" class="layui-input">'
                + '      </div>'
                + '    </div>'
                + '    <div class="layui-form-item">'
                + '      <label class="layui-form-label">时区</label>'
                + '      <div class="layui-input-block">'
                + '        <input type="text" name="timezone" value="' + (data.timezone || 'Asia/Shanghai') + '" placeholder="Asia/Shanghai" autocomplete="off" class="layui-input">'
                + '      </div>'
                + '    </div>'
                + '    <div class="layui-form-item">'
                + '      <label class="layui-form-label">状态</label>'
                + '      <div class="layui-input-block">'
                + '        <input type="radio" name="status" value="1" title="启用"' + ((data.status == 1 || data.status === undefined) ? ' checked' : '') + '>'
                + '        <input type="radio" name="status" value="0" title="禁用"' + ((data.status == 0) ? ' checked' : '') + '>'
                + '      </div>'
                + '    </div>'
                + '    <div class="layui-form-item">'
                + '      <label class="layui-form-label">超时(秒)</label>'
                + '      <div class="layui-input-block">'
                + '        <input type="number" name="timeout" value="' + (data.timeout || 0) + '" placeholder="0不限制" autocomplete="off" class="layui-input">'
                + '      </div>'
                + '    </div>'
                + '    <div class="layui-form-item">'
                + '      <label class="layui-form-label">排序</label>'
                + '      <div class="layui-input-block">'
                + '        <input type="number" name="sort" value="' + (data.sort || 0) + '" autocomplete="off" class="layui-input">'
                + '      </div>'
                + '    </div>'
                + '    <div class="layui-form-item layui-form-text">'
                + '      <label class="layui-form-label">备注</label>'
                + '      <div class="layui-input-block">'
                + '        <textarea name="remark" placeholder="可选" class="layui-textarea" style="min-height: 60px;">' + (data.remark || '') + '</textarea>'
                + '      </div>'
                + '    </div>'
                + '    <div class="layui-form-item">'
                + '      <div class="layui-input-block">'
                + '        <button class="layui-btn layui-btn-danger" lay-submit lay-filter="schedule-save">保存</button>'
                + '      </div>'
                + '    </div>'
                + '  </form>'
                + '</div>';

            layer.open({
                type: 1,
                title: (data.id ? '编辑任务' : '新增任务'),
                area: ['720px', '640px'],
                content: html,
                success: function () {
                    form.render();
                }
            });
        }

        table.render({
            elem: '#schedule-table',
            id: 'schedule-table',
            url: '/admin/system/tools/schedule/list',
            method: 'get',
            page: true,
            toolbar: '#schedule-toolbar',
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
                {field: 'id', title: 'ID', width: 80, sort: true},
                {field: 'name', title: '名称', minWidth: 160},
                {field: 'type', title: '类型', width: 90},
                {field: 'cron_expression', title: 'cron', minWidth: 140},
                {field: 'status', title: '状态', width: 90, templet: function (d) { return d.status == 1 ? '<span class="layui-badge layui-bg-green">启用</span>' : '<span class="layui-badge layui-bg-gray">禁用</span>'; }},
                {field: 'last_run_time', title: '上次执行', width: 170},
                {field: 'last_status', title: '上次状态', width: 100, templet: '#schedule-last-status'},
                {field: 'remark', title: '备注', minWidth: 160},
                {field: 'sort', title: '排序', width: 90, sort: true},
                {title: '操作', width: 260, toolbar: '#schedule-actions'}
            ]]
        });

        table.on('toolbar(schedule-table)', function (obj) {
            if (obj.event === 'refresh') {
                table.reload('schedule-table');
                return;
            }
            if (obj.event === 'add') {
                openForm({});
            }
        });

        table.on('tool(schedule-table)', function (obj) {
            var data = obj.data || {};
            if (obj.event === 'edit') {
                openForm(data);
                return;
            }
            if (obj.event === 'delete') {
                layer.confirm('确认删除该任务？', function (index) {
                    layer.close(index);
                    $.post('/admin/system/tools/schedule/delete', {id: data.id}, function (res) {
                        if (!res || res.code !== 0) {
                            layer.msg(res && res.msg ? res.msg : '删除失败');
                            return;
                        }
                        layer.msg('删除成功', {icon: 1});
                        table.reload('schedule-table');
                    }, 'json');
                });
                return;
            }
            if (obj.event === 'enable' || obj.event === 'disable') {
                var status = obj.event === 'enable' ? 1 : 0;
                $.post('/admin/system/tools/schedule/status', {id: data.id, status: status}, function (res) {
                    if (!res || res.code !== 0) {
                        layer.msg(res && res.msg ? res.msg : '更新失败');
                        return;
                    }
                    layer.msg('更新成功', {icon: 1});
                    table.reload('schedule-table');
                }, 'json');
                return;
            }
            if (obj.event === 'run') {
                layer.confirm('确认立即执行该任务？', function (index) {
                    layer.close(index);
                    var idx = layer.load(1);
                    $.post('/admin/system/tools/schedule/run', {id: data.id}, function (res) {
                        layer.close(idx);
                        if (!res || res.code !== 0) {
                            layer.msg(res && res.msg ? res.msg : '执行失败');
                            if (res && res.data && res.data.output) {
                                layer.open({type: 1, title: '输出', area: ['720px', '520px'], content: '<pre style="padding:12px;white-space:pre-wrap;word-break:break-all;">' + $('<div>').text(res.data.output).html() + '</pre>'});
                            }
                            table.reload('schedule-table');
                            return;
                        }
                        layer.msg('执行成功', {icon: 1});
                        if (res.data && res.data.output) {
                            layer.open({type: 1, title: '输出', area: ['720px', '520px'], content: '<pre style="padding:12px;white-space:pre-wrap;word-break:break-all;">' + $('<div>').text(res.data.output).html() + '</pre>'});
                        }
                        table.reload('schedule-table');
                    }, 'json').fail(function () {
                        layer.close(idx);
                        layer.msg('请求失败');
                    });
                });
                return;
            }
        });

        form.on('submit(schedule-search-btn)', function (obj) {
            table.reload('schedule-table', {where: obj.field || {}, page: {curr: 1}});
            return false;
        });

        $('#schedule-reset-btn').on('click', function () {
            table.reload('schedule-table', {where: {}, page: {curr: 1}});
        });

        form.on('submit(schedule-save)', function (obj) {
            var idx = layer.load(1);
            $.post('/admin/system/tools/schedule/save', obj.field || {}, function (res) {
                layer.close(idx);
                if (!res || res.code !== 0) {
                    layer.msg(res && res.msg ? res.msg : '保存失败');
                    return;
                }
                layer.msg('保存成功', {icon: 1});
                layer.closeAll('page');
                table.reload('schedule-table');
            }, 'json').fail(function () {
                layer.close(idx);
                layer.msg('请求失败');
            });
            return false;
        });
    });
</script>
</body>
</html>
