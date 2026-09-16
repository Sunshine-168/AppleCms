@extends('admin.layouts.inner')
@section('title', admin_t('page.db_restore'))

@section('header_actions')
    <a class="btn btn-muted btn-sm" href="/admin/system/database/backup">去备份</a>
    <button type="button" class="btn btn-muted btn-sm" id="dbrestore-refresh">刷新列表</button>
@endsection

@section('content')
    <p class="hint">恢复会覆盖当前数据库数据，请谨慎操作。</p>
    <div id="dbrestore-table"></div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var table = U.table({
        el: '#dbrestore-table',
        url: '/admin/system/database/restore/files',
        cols: [
            {key: 'name', title: '文件名'},
            {key: 'size', title: '大小(B)', width: 120},
            {key: 'time', title: '时间', width: 180},
            {title: '操作', cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-restore">恢复</a><a href="#" class="btn-link js-dl">下载</a><a href="#" class="btn-link js-del">删除</a>';
            }}
        ]
    });
    U.on('#dbrestore-refresh', 'click', function () { table.refresh(); });
    U.on('#dbrestore-table', 'click', function (e) {
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
        if (a.classList.contains('js-restore') && U.confirm('恢复会覆盖当前数据库数据，确认恢复？')) {
            U.loading(true);
            U.post('/admin/system/database/restore/run', {file: row.name || ''}).then(function (res) {
                U.loading(false);
                U.toast((res && res.msg) || (res && res.code === 0 ? '恢复成功' : '恢复失败'), res && res.code === 0 ? 'ok' : 'err');
            });
        }
    });
})();
</script>
@endpush
