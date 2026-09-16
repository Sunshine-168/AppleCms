@extends('admin.layouts.inner')
@section('title', '分类管理')

@section('plain')
<div class="card card-panel">
    <div class="card-body">
        <form class="filter-bar" id="video-type-search" onsubmit="return false;">
            <input type="text" name="name" placeholder="分类名称">
            <select name="parent_id" id="video-type-parent-search">
                <option value="">全部父级</option>
                <option value="0">顶级</option>
            </select>
            <button type="button" class="btn btn-sm" id="video-type-search-btn">查询</button>
            <button type="reset" class="btn btn-muted btn-sm" id="video-type-reset-btn">重置</button>
        </form>
    </div>
</div>
<div class="card card-panel">
    <div class="card-header">
        <span>分类列表</span>
        <div>
            <button type="button" class="btn btn-sm" id="video-type-add-btn">新增分类</button>
            <button type="button" class="btn btn-muted btn-sm" id="video-type-refresh-btn">刷新</button>
        </div>
    </div>
    <div class="card-body">
        <div id="video-type-table"></div>
    </div>
</div>
<template id="video-type-dialog-tpl">
    <form>
        <input type="hidden" name="id">
        <label>名称</label>
        <input type="text" name="name">
        <label>父级</label>
        <select name="parent_id" id="video-type-parent-form"><option value="0">顶级</option></select>
        <label>模型</label>
        <select name="mid">
            <option value="1">视频</option>
            <option value="2">文章</option>
            <option value="3">网址</option>
        </select>
        <label>排序</label>
        <input type="number" name="sort" value="0">
        <label>状态</label>
        <select name="status">
            <option value="1">启用</option>
            <option value="0">禁用</option>
        </select>
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    function fillParents(list) {
        var opts = '<option value="0">顶级</option>';
        (list || []).forEach(function (r) {
            if (!r.id) return;
            opts += '<option value="' + U.escape(r.id) + '">' + U.escape(r.name || '') + '</option>';
        });
        var formSel = document.getElementById('video-type-parent-form');
        var searchSel = document.getElementById('video-type-parent-search');
        if (formSel) formSel.innerHTML = opts;
        if (searchSel) searchSel.innerHTML = '<option value="">全部父级</option>' + opts;
    }
    function loadParents(cb) {
        U.get('/admin/video/types/options').then(function (res) {
            var list = (res && res.code === 0 && Array.isArray(res.data)) ? res.data : [];
            fillParents(list);
            cb && cb(list);
        });
    }
    var table = U.table({
        el: '#video-type-table',
        url: '/admin/video/types/list',
        cols: [
            {key: 'id', title: 'ID', width: 70},
            {key: 'name', title: '名称'},
            {key: 'parent_name', title: '父级'},
            {title: '模型', width: 80, html: function (d) {
                return String(d.mid) === '2' ? '文章' : (String(d.mid) === '3' ? '网址' : '视频');
            }},
            {key: 'sort', title: '排序', width: 70},
            {title: '状态', width: 80, html: function (d) {
                return String(d.status) === '1' ? U.status(true, '启用') : U.status(false, '禁用');
            }},
            {key: 'created_at_text', title: '创建时间', width: 160},
            {key: 'updated_at_text', title: '更新时间', width: 160},
            {title: '操作', cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-edit">编辑</a><a href="#" class="btn-link js-del">删除</a>';
            }}
        ]
    });
    function openDialog(mode, row) {
        row = row || {};
        U.dialog({
            title: mode === 'edit' ? '编辑分类' : '新增分类',
            content: document.getElementById('video-type-dialog-tpl').innerHTML,
            onOpen: function (body) {
                loadParents(function () {
                    var sel = body.querySelector('select[name=parent_id]');
                    sel.innerHTML = document.getElementById('video-type-parent-search').innerHTML.replace('<option value="">全部父级</option>', '');
                    U.fillForm(body.querySelector('form'), {
                        id: row.id || '',
                        name: row.name || '',
                        parent_id: row.parent_id == null ? '0' : String(row.parent_id),
                        mid: row.mid == null ? '1' : String(row.mid),
                        sort: row.sort == null ? 0 : row.sort,
                        status: row.status == null ? '1' : String(row.status)
                    });
                });
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (mode !== 'edit') delete data.id;
                else data.id = row.id;
                return U.post('/admin/video/types/save', data).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return false; }
                    U.toast('保存成功', 'ok');
                    table.refresh();
                    loadParents();
                });
            }
        });
    }
    U.on('#video-type-search-btn', 'click', function () {
        table.reload(U.formData('#video-type-search'));
    });
    U.on('#video-type-reset-btn', 'click', function () {
        setTimeout(function () { table.reload({}); }, 0);
    });
    U.on('#video-type-refresh-btn', 'click', function () { table.refresh(); });
    U.on('#video-type-add-btn', 'click', function () { openDialog('add', {}); });
    U.on('#video-type-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-edit')) openDialog('edit', row);
        if (a.classList.contains('js-del')) {
            if (!U.confirm('确认删除该分类？')) return;
            U.post('/admin/video/types/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh();
                U.toast('删除成功', 'ok');
            });
        }
    });
    loadParents();
})();
</script>
@endpush
