@extends('admin.layouts.inner')
@section('title', '附件管理')

@section('plain')
<div class="card card-panel">
    <div class="card-body">
        <form class="filter-bar" id="file-search" onsubmit="return false;">
            <input type="text" name="keyword" placeholder="关键字（名称/类型/URL）">
            <button type="button" class="btn btn-sm" id="file-search-btn">查询</button>
            <button type="reset" class="btn btn-muted btn-sm" id="file-reset-btn">重置</button>
        </form>
    </div>
</div>
<div class="card card-panel">
    <div class="card-header">
        <span>附件</span>
        <div>
            <button type="button" class="btn btn-sm" id="upload-btn">上传文件</button>
            <button type="button" class="btn btn-danger btn-sm" id="batch-del-btn">批量删除</button>
            <button type="button" class="btn btn-muted btn-sm" id="file-refresh-btn">刷新</button>
        </div>
    </div>
    <div class="card-body"><div id="file-table"></div></div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var table = U.table({
        el: '#file-table',
        url: '/admin/system/attachments/list',
        cols: [
            {check: true, width: 36},
            {key: 'id', title: 'ID', width: 70},
            {key: 'name', title: '名称'},
            {key: 'mime', title: '类型', width: 140},
            {key: 'size_text', title: '大小', width: 90},
            {key: 'url', title: 'URL'},
            {key: 'create_time', title: '上传时间', width: 160},
            {title: '操作', cls: 'actions', html: function () { return '<a href="#" class="btn-link js-open">打开</a><a href="#" class="btn-link js-del">删除</a>'; }}
        ]
    });
    U.on('#file-search-btn', 'click', function () { table.reload(U.formData('#file-search')); });
    U.on('#file-reset-btn', 'click', function () { setTimeout(function () { table.reload({}); }, 0); });
    U.on('#file-refresh-btn', 'click', function () { table.refresh(); });
    U.on('#upload-btn', 'click', function () {
        U.pickFile('*/*').then(function (file) {
            if (!file) return;
            U.loading(true);
            return U.upload(file).then(function (res) {
                U.loading(false);
                if (res && res.code === 0) { U.toast('上传成功', 'ok'); table.refresh(); }
                else U.toast((res && res.msg) || '上传失败', 'err');
            });
        });
    });
    U.on('#batch-del-btn', 'click', function () {
        var ids = table.selectedIds();
        if (!ids.length) { U.toast('请勾选要删除的文件', 'err'); return; }
        if (!U.confirm('确认删除选中的文件？')) return;
        U.post('/admin/system/attachments/delete', {ids: ids}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
            table.refresh(); U.toast('删除成功', 'ok');
        });
    });
    U.on('#file-table', 'click', function (e) {
        var a = e.target.closest('a'); if (!a) return;
        var row = (table.rows() || [])[e.target.closest('tr').getAttribute('data-idx')];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-open')) {
            if (row.id) window.open('/admin/system/attachments/open?id=' + row.id, '_blank');
            else if (row.url) window.open(row.url, '_blank');
            else U.toast('无可用链接', 'err');
        }
        if (a.classList.contains('js-del') && U.confirm('确认删除该文件？')) {
            U.post('/admin/system/attachments/delete', {ids: [row.id]}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh(); U.toast('删除成功', 'ok');
            });
        }
    });
})();
</script>
@endpush
