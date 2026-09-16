@extends('admin.layouts.inner')
@section('title', admin_t('page.db_backup'))

@section('header_actions')
    <button type="button" class="btn btn-sm" id="dbbackup-run">立即备份</button>
    <button type="button" class="btn btn-muted btn-sm" id="dbbackup-refresh">刷新列表</button>
@endsection

@section('content')
    <div id="dbbackup-table"></div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var table = U.table({
        el: '#dbbackup-table',
        url: '/admin/system/database/backup/files',
        cols: [
            {key: 'name', title: '文件名'},
            {key: 'size', title: '大小(B)', width: 120},
            {key: 'time', title: '时间', width: 180},
            {title: '操作', cls: 'actions', html: function () { return '<a href="#" class="btn-link js-dl">下载</a><a href="#" class="btn-link js-del">删除</a>'; }}
        ]
    });
    U.on('#dbbackup-refresh', 'click', function () { table.refresh(); });
    U.on('#dbbackup-run', 'click', function () {
        U.loading(true);
        U.post('/admin/system/database/backup/run', {}).then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '备份失败', 'err'); return; }
            U.toast('备份成功', 'ok');
            table.refresh();
        });
    });
    U.on('#dbbackup-table', 'click', function (e) {
        var a = e.target.closest('a'); if (!a) return;
        var row = (table.rows() || [])[e.target.closest('tr').getAttribute('data-idx')];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-dl')) window.open('/admin/system/database/backup/download?file=' + encodeURIComponent(row.name || ''));
        if (a.classList.contains('js-del') && U.confirm('确认删除该备份文件？')) {
            U.post('/admin/system/database/backup/delete', {file: row.name || ''}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '删除失败', 'err'); return; }
                U.toast('删除成功', 'ok');
                table.refresh();
            });
        }
    });
})();
</script>
@endpush
