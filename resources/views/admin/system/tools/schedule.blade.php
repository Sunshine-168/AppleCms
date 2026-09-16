@extends('admin.layouts.inner')
@section('title', '计划任务')

@section('plain')
<div class="card card-panel">
    <div class="card-body">
        <form class="filter-bar" id="schedule-search" onsubmit="return false;">
            <input type="text" name="name" placeholder="任务名称">
            <select name="type">
                <option value="">类型</option>
                <option value="artisan">artisan</option>
                <option value="shell">shell</option>
                <option value="http">http</option>
            </select>
            <select name="status">
                <option value="">状态</option>
                <option value="1">启用</option>
                <option value="0">禁用</option>
            </select>
            <button type="button" class="btn btn-sm" id="schedule-search-btn">查询</button>
            <button type="reset" class="btn btn-muted btn-sm" id="schedule-reset-btn">重置</button>
        </form>
    </div>
</div>
<div class="card card-panel">
    <div class="card-header">
        <span>任务</span>
        <div>
            <button type="button" class="btn btn-sm" id="schedule-add-btn">新增任务</button>
            <button type="button" class="btn btn-muted btn-sm" id="schedule-refresh-btn">刷新</button>
        </div>
    </div>
    <div class="card-body"><div id="schedule-table"></div></div>
</div>
<template id="schedule-dialog-tpl">
    <form>
        <input type="hidden" name="id">
        <label>名称</label>
        <input type="text" name="name" placeholder="任务名称">
        <label>标识</label>
        <input type="text" name="code" placeholder="可选">
        <label>类型</label>
        <select name="type">
            <option value="artisan">artisan</option>
            <option value="shell">shell</option>
            <option value="http">http</option>
        </select>
        <label>执行内容</label>
        <textarea name="command" placeholder="artisan: cache:clear；shell: php -v；http: https://example.com/ping"></textarea>
        <label>参数</label>
        <textarea name="params" placeholder="可选"></textarea>
        <label>cron</label>
        <input type="text" name="cron_expression" value="* * * * *">
        <label>时区</label>
        <input type="text" name="timezone" value="Asia/Shanghai">
        <label>状态</label>
        <select name="status"><option value="1">启用</option><option value="0">禁用</option></select>
        <label>超时(秒)</label>
        <input type="number" name="timeout" value="0" placeholder="0不限制">
        <label>排序</label>
        <input type="number" name="sort" value="0">
        <label>备注</label>
        <textarea name="remark" placeholder="可选"></textarea>
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var table = U.table({
        el: '#schedule-table',
        url: '/admin/system/tools/schedule/list',
        cols: [
            {key: 'id', title: 'ID', width: 70},
            {key: 'name', title: '名称'},
            {key: 'type', title: '类型', width: 80},
            {key: 'cron_expression', title: 'cron'},
            {title: '状态', width: 80, html: function (d) { return d.status == 1 ? U.status(true, '启用') : U.status(false, '禁用'); }},
            {key: 'last_run_time', title: '上次执行', width: 150},
            {title: '上次状态', width: 90, html: function (d) {
                if (d.last_status == 1) return U.status(true, '成功');
                if (d.last_status == 2) return U.status(false, '失败');
                return '<span class="status status-off">未知</span>';
            }},
            {key: 'remark', title: '备注'},
            {key: 'sort', title: '排序', width: 70},
            {title: '操作', cls: 'actions', html: function (d) {
                var html = '<a href="#" class="btn-link js-edit">编辑</a><a href="#" class="btn-link js-run">立即执行</a>';
                html += d.status == 1 ? '<a href="#" class="btn-link js-off">禁用</a>' : '<a href="#" class="btn-link js-on">启用</a>';
                html += '<a href="#" class="btn-link js-del">删除</a>';
                return html;
            }}
        ]
    });
    function openForm(data) {
        data = data || {};
        U.dialog({
            title: data.id ? '编辑任务' : '新增任务',
            wide: true,
            content: document.getElementById('schedule-dialog-tpl').innerHTML,
            onOpen: function (body) {
                U.fillForm(body.querySelector('form'), {
                    id: data.id || 0,
                    name: data.name || '',
                    code: data.code || '',
                    type: data.type || 'artisan',
                    command: data.command || '',
                    params: data.params || '',
                    cron_expression: data.cron_expression || '* * * * *',
                    timezone: data.timezone || 'Asia/Shanghai',
                    status: (data.status == 0) ? '0' : '1',
                    timeout: data.timeout || 0,
                    sort: data.sort || 0,
                    remark: data.remark || ''
                });
            },
            onSave: function (body) {
                var payload = U.formData(body.querySelector('form'));
                if (!payload.name) { U.toast('请输入任务名称', 'err'); return false; }
                if (!payload.command) { U.toast('请输入执行内容', 'err'); return false; }
                return U.post('/admin/system/tools/schedule/save', payload).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || '保存失败', 'err'); return false; }
                    U.toast('保存成功', 'ok');
                    table.refresh();
                });
            }
        });
    }
    U.on('#schedule-search-btn', 'click', function () { table.reload(U.formData('#schedule-search')); });
    U.on('#schedule-reset-btn', 'click', function () { setTimeout(function () { table.reload({}); }, 0); });
    U.on('#schedule-refresh-btn', 'click', function () { table.refresh(); });
    U.on('#schedule-add-btn', 'click', function () { openForm({}); });
    U.on('#schedule-table', 'click', function (e) {
        var a = e.target.closest('a'); if (!a) return;
        var row = (table.rows() || [])[e.target.closest('tr').getAttribute('data-idx')];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-edit')) openForm(row);
        if (a.classList.contains('js-del') && U.confirm('确认删除该任务？')) {
            U.post('/admin/system/tools/schedule/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '删除失败', 'err'); return; }
                U.toast('删除成功', 'ok'); table.refresh();
            });
        }
        if (a.classList.contains('js-on') || a.classList.contains('js-off')) {
            U.post('/admin/system/tools/schedule/status', {id: row.id, status: a.classList.contains('js-on') ? 1 : 0}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '更新失败', 'err'); return; }
                U.toast('更新成功', 'ok'); table.refresh();
            });
        }
        if (a.classList.contains('js-run') && U.confirm('确认立即执行该任务？')) {
            U.loading(true);
            U.post('/admin/system/tools/schedule/run', {id: row.id}).then(function (res) {
                U.loading(false);
                table.refresh();
                if (res && res.data && res.data.output) {
                    U.dialog({ title: '输出', wide: true, hideOk: true, content: '<pre class="out">' + U.escape(res.data.output) + '</pre>' });
                }
                U.toast((res && res.msg) || (res && res.code === 0 ? '执行成功' : '执行失败'), res && res.code === 0 ? 'ok' : 'err');
            });
        }
    });
})();
</script>
@endpush
