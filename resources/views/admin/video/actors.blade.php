@extends('admin.layouts.inner')
@section('title', '演员管理')

@section('plain')
<div class="card card-panel">
    <div class="card-body">
        <form class="filter-bar" id="actor-search" onsubmit="return false;">
            <input type="text" name="name" placeholder="演员名称">
            <button type="button" class="btn btn-sm" id="actor-search-btn">查询</button>
            <button type="reset" class="btn btn-muted btn-sm" id="actor-reset-btn">重置</button>
        </form>
    </div>
</div>
<div class="card card-panel">
    <div class="card-header">
        <span>演员列表</span>
        <div>
            <button type="button" class="btn btn-sm" id="actor-add-btn">新增演员</button>
            <button type="button" class="btn btn-muted btn-sm" id="actor-refresh-btn">刷新</button>
        </div>
    </div>
    <div class="card-body"><div id="actor-table"></div></div>
</div>
<template id="actor-dialog-tpl">
    <form>
        <input type="hidden" name="id">
        <label>名称</label>
        <input type="text" name="name">
        <label>头像</label>
        <div class="field-inline">
            <input type="text" name="avatar" placeholder="图片URL">
            <button type="button" class="btn btn-muted actor-avatar-upload-btn">上传</button>
        </div>
        <img class="img-preview actor-avatar-preview" alt="">
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var table = U.table({
        el: '#actor-table',
        url: '/admin/video/actors/list',
        cols: [
            {key: 'id', title: 'ID', width: 70},
            {key: 'name', title: '名称'},
            {title: '头像', width: 80, html: function (d) {
                return d.avatar ? '<img src="' + U.escape(d.avatar) + '" style="width:32px;height:32px;border-radius:50%;object-fit:cover;">' : '-';
            }},
            {key: 'created_at_text', title: '创建时间', width: 160},
            {title: '操作', cls: 'actions', html: function () { return '<a href="#" class="btn-link js-edit">编辑</a><a href="#" class="btn-link js-del">删除</a>'; }}
        ]
    });
    function openDialog(mode, row) {
        row = row || {};
        U.dialog({
            title: mode === 'edit' ? '编辑演员' : '新增演员',
            content: document.getElementById('actor-dialog-tpl').innerHTML,
            onOpen: function (body) {
                var form = body.querySelector('form');
                U.fillForm(form, {id: row.id || '', name: row.name || '', avatar: row.avatar || ''});
                var input = form.querySelector('input[name=avatar]');
                var preview = body.querySelector('.actor-avatar-preview');
                function sync(url) {
                    url = String(url || '').trim();
                    if (url) { preview.src = url; preview.style.display = 'block'; }
                    else { preview.removeAttribute('src'); preview.style.display = 'none'; }
                }
                sync(input.value);
                input.addEventListener('input', function () { sync(input.value); });
                body.querySelector('.actor-avatar-upload-btn').addEventListener('click', function () {
                    U.pickFile('image/*').then(function (file) {
                        if (!file) return;
                        U.loading(true);
                        return U.upload(file).then(function (res) {
                            U.loading(false);
                            if (res && res.code === 0 && res.data && res.data.url) {
                                input.value = res.data.url; sync(res.data.url); U.toast('上传成功', 'ok');
                            } else U.toast((res && res.msg) || '上传失败', 'err');
                        });
                    });
                });
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (mode !== 'edit') delete data.id; else data.id = row.id;
                return U.post('/admin/video/actors/save', data).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return false; }
                    U.toast('保存成功', 'ok');
                    table.refresh();
                });
            }
        });
    }
    U.on('#actor-search-btn', 'click', function () { table.reload(U.formData('#actor-search')); });
    U.on('#actor-reset-btn', 'click', function () { setTimeout(function () { table.reload({}); }, 0); });
    U.on('#actor-refresh-btn', 'click', function () { table.refresh(); });
    U.on('#actor-add-btn', 'click', function () { openDialog('add', {}); });
    U.on('#actor-table', 'click', function (e) {
        var a = e.target.closest('a'); if (!a) return;
        var row = (table.rows() || [])[e.target.closest('tr').getAttribute('data-idx')];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-edit')) openDialog('edit', row);
        if (a.classList.contains('js-del') && U.confirm('确认删除该演员？')) {
            U.post('/admin/video/actors/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh(); U.toast('删除成功', 'ok');
            });
        }
    });
})();
</script>
@endpush
