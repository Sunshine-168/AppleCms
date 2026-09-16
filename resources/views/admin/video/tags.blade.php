@extends('admin.layouts.inner')
@section('title', '标签管理')

@section('plain')
<div class="card card-panel">
    <div class="card-body">
        <form class="filter-bar" id="video-tag-search" onsubmit="return false;">
            <input type="text" name="name" placeholder="标签名称">
            <button type="button" class="btn btn-sm" id="video-tag-search-btn">查询</button>
            <button type="reset" class="btn btn-muted btn-sm" id="video-tag-reset-btn">重置</button>
        </form>
    </div>
</div>
<div class="card card-panel">
    <div class="card-header">
        <span>标签列表</span>
        <div>
            <button type="button" class="btn btn-sm" id="video-tag-add-btn">新增标签</button>
            <button type="button" class="btn btn-muted btn-sm" id="video-tag-refresh-btn">刷新</button>
        </div>
    </div>
    <div class="card-body"><div id="video-tag-table"></div></div>
</div>
<template id="video-tag-dialog-tpl">
    <form>
        <input type="hidden" name="id">
        <label>名称</label>
        <input type="text" name="name">
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
        el: '#video-tag-table',
        url: '/admin/video/tags/list',
        cols: [
            {key: 'id', title: 'ID', width: 70},
            {key: 'name', title: '名称'},
            {title: '状态', width: 80, html: function (d) { return String(d.status) === '1' ? U.status(true, '启用') : U.status(false, '禁用'); }},
            {key: 'sort', title: '排序', width: 70},
            {key: 'created_at_text', title: '创建时间', width: 160},
            {title: '操作', cls: 'actions', html: function () { return '<a href="#" class="btn-link js-edit">编辑</a><a href="#" class="btn-link js-del">删除</a>'; }}
        ]
    });
    function openDialog(mode, row) {
        row = row || {};
        U.dialog({
            title: mode === 'edit' ? '编辑标签' : '新增标签',
            content: document.getElementById('video-tag-dialog-tpl').innerHTML,
            onOpen: function (body) {
                U.fillForm(body.querySelector('form'), {
                    id: row.id || '',
                    name: row.name || '',
                    status: row.status == null ? '1' : String(row.status),
                    sort: row.sort == null ? 0 : row.sort
                });
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (mode !== 'edit') delete data.id; else data.id = row.id;
                return U.post('/admin/video/tags/save', data).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return false; }
                    U.toast('保存成功', 'ok');
                    table.refresh();
                });
            }
        });
    }
    U.on('#video-tag-search-btn', 'click', function () { table.reload(U.formData('#video-tag-search')); });
    U.on('#video-tag-reset-btn', 'click', function () { setTimeout(function () { table.reload({}); }, 0); });
    U.on('#video-tag-refresh-btn', 'click', function () { table.refresh(); });
    U.on('#video-tag-add-btn', 'click', function () { openDialog('add', {}); });
    U.on('#video-tag-table', 'click', function (e) {
        var a = e.target.closest('a'); if (!a) return;
        var row = (table.rows() || [])[e.target.closest('tr').getAttribute('data-idx')];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-edit')) openDialog('edit', row);
        if (a.classList.contains('js-del') && U.confirm('确认删除该标签？')) {
            U.post('/admin/video/tags/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh(); U.toast('删除成功', 'ok');
            });
        }
    });
})();
</script>
@endpush
