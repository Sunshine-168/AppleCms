@extends('admin.layouts.inner')
@section('title', '采集源管理')

@section('plain')
<div class="card card-panel">
    <div class="card-body">
        <form class="filter-bar" id="collect-source-search" onsubmit="return false;">
            <input type="text" name="name" placeholder="采集源名称">
            <select name="status">
                <option value="">全部状态</option>
                <option value="1">启用</option>
                <option value="0">禁用</option>
            </select>
            <button type="button" class="btn btn-sm" id="collect-source-search-btn">查询</button>
            <button type="reset" class="btn btn-muted btn-sm" id="collect-source-reset-btn">重置</button>
        </form>
    </div>
</div>
<div class="card card-panel">
    <div class="card-header">
        <span>采集源</span>
        <div>
            <button type="button" class="btn btn-sm" id="collect-source-add-btn">新增采集源</button>
            <button type="button" class="btn btn-muted btn-sm" id="collect-source-refresh-btn">刷新</button>
            <a class="btn btn-muted btn-sm" href="/admin/video/collect_logs">采集日志</a>
        </div>
    </div>
    <div class="card-body"><div id="collect-source-table"></div></div>
</div>
<template id="collect-source-dialog-tpl">
    <form>
        <input type="hidden" name="id">
        <label>名称</label>
        <input type="text" name="name">
        <label>接口地址</label>
        <input type="text" name="api_url" placeholder="https://xxx/api.php/provide/vod/">
        <label>格式</label>
        <select name="api_type">
            <option value="auto">自动</option>
            <option value="json">JSON</option>
            <option value="xml">XML</option>
        </select>
        <label>附加参数</label>
        <input type="text" name="param" placeholder="ac=list 以外的固定参数">
        <label>状态</label>
        <select name="status"><option value="1">启用</option><option value="0">禁用</option></select>
        <label>排序</label>
        <input type="number" name="sort" value="0">
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var table = U.table({
        el: '#collect-source-table',
        url: '/admin/video/collects/list',
        cols: [
            {key: 'id', title: 'ID', width: 70},
            {key: 'name', title: '名称'},
            {key: 'api_url', title: '接口'},
            {key: 'api_type', title: '格式', width: 70},
            {title: '状态', width: 80, html: function (d) { return String(d.status) === '1' ? U.status(true, '启用') : U.status(false, '禁用'); }},
            {key: 'sort', title: '排序', width: 70},
            {key: 'last_page', title: '断点页', width: 80},
            {key: 'last_error', title: '失败'},
            {key: 'updated_at_text', title: '更新时间', width: 150},
            {title: '操作', cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-edit">编辑</a><a href="#" class="btn-link js-bind">绑定</a><a href="#" class="btn-link js-run">采集</a><a href="#" class="btn-link js-resume">续采</a><a href="#" class="btn-link js-retry">重试</a><a href="#" class="btn-link js-suggest">自动绑定</a><a href="#" class="btn-link js-del">删除</a>';
            }}
        ]
    });
    function openDialog(mode, row) {
        row = row || {};
        U.dialog({
            title: mode === 'edit' ? '编辑采集源' : '新增采集源',
            content: document.getElementById('collect-source-dialog-tpl').innerHTML,
            onOpen: function (body) {
                U.fillForm(body.querySelector('form'), {
                    id: row.id || '',
                    name: row.name || '',
                    api_url: row.api_url || '',
                    api_type: row.api_type || 'auto',
                    param: row.param || '',
                    status: row.status == null ? '1' : String(row.status),
                    sort: row.sort == null ? 0 : row.sort
                });
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (mode !== 'edit') delete data.id; else data.id = row.id;
                return U.post('/admin/video/collects/save', data).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return false; }
                    U.toast('保存成功', 'ok');
                    table.refresh();
                });
            }
        });
    }
    U.on('#collect-source-search-btn', 'click', function () { table.reload(U.formData('#collect-source-search')); });
    U.on('#collect-source-reset-btn', 'click', function () { setTimeout(function () { table.reload({}); }, 0); });
    U.on('#collect-source-refresh-btn', 'click', function () { table.refresh(); });
    U.on('#collect-source-add-btn', 'click', function () { openDialog('add', {}); });
    U.on('#collect-source-table', 'click', function (e) {
        var a = e.target.closest('a'); if (!a) return;
        var row = (table.rows() || [])[e.target.closest('tr').getAttribute('data-idx')];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-edit')) openDialog('edit', row);
        if (a.classList.contains('js-bind')) {
            U.get('/admin/video/collects/classes', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                var data = res.data || {};
                var types = data.types || [];
                var locals = data.local_types || [];
                var html = '<table class="data"><thead><tr><th>资源分类</th><th>绑定本地</th></tr></thead><tbody>';
                types.forEach(function (t) {
                    html += '<tr><td>' + U.escape(t.name) + ' (' + U.escape(t.remote_id) + ')</td><td><select data-remote="' + U.escape(t.remote_id) + '"><option value="0">不采集</option>';
                    locals.forEach(function (l) {
                        html += '<option value="' + U.escape(l.id) + '"' + (String(l.id) === String(t.local_id) ? ' selected' : '') + '>' + U.escape(l.name) + '</option>';
                    });
                    html += '</select></td></tr>';
                });
                html += '</tbody></table>';
                U.dialog({
                    title: '绑定分类 - ' + (row.name || ''),
                    content: html,
                    onSave: function (body) {
                        var bind = {};
                        U.qa('select[data-remote]', body).forEach(function (sel) {
                            bind[sel.getAttribute('data-remote')] = sel.value;
                        });
                        return U.post('/admin/video/collects/bind', {id: row.id, bind: JSON.stringify(bind)}).then(function (r) {
                            if (!r || r.code !== 0) { U.toast((r && r.msg) || '失败', 'err'); return false; }
                            U.toast('绑定已保存', 'ok');
                        });
                    }
                });
            });
        }
        if (a.classList.contains('js-run')) {
            var val = U.prompt('起始页,采集页数,小时(0全部)', '1,1,24');
            if (val == null) return;
            var parts = String(val).split(/[,，\s]+/);
            U.loading(true);
            U.post('/admin/video/collects/run', {id: row.id, page: parts[0] || 1, pages: parts[1] || 1, hours: parts[2] || 0}).then(function (res) {
                U.loading(false);
                table.refresh();
                U.toast((res && res.msg) || '采集完成', res && res.code === 0 ? 'ok' : 'err');
            });
        }
        if (a.classList.contains('js-resume')) {
            U.loading(true);
            U.post('/admin/video/collects/resume', {id: row.id, pages: 1, hours: 24}).then(function (res) {
                U.loading(false);
                table.refresh();
                U.toast((res && res.msg) || '续采完成', res && res.code === 0 ? 'ok' : 'err');
            });
        }
        if (a.classList.contains('js-retry')) {
            U.loading(true);
            U.post('/admin/video/collects/retry', {id: row.id}).then(function (res) {
                U.loading(false);
                table.refresh();
                U.toast((res && res.msg) || '重试完成', res && res.code === 0 ? 'ok' : 'err');
            });
        }
        if (a.classList.contains('js-suggest')) {
            U.post('/admin/video/collects/suggest', {id: row.id}).then(function (res) {
                U.toast((res && res.msg) || '已按同名分类绑定', res && res.code === 0 ? 'ok' : 'err');
            });
        }
        if (a.classList.contains('js-del') && U.confirm('确认删除该采集源？')) {
            U.post('/admin/video/collects/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh(); U.toast('删除成功', 'ok');
            });
        }
    });
})();
</script>
@endpush
