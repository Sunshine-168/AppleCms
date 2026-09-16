@extends('admin.layouts.inner')
@section('title', '数据字典')

@section('plain')
<div class="card card-panel">
    <div class="card-body">
        <form class="filter-bar" onsubmit="return false;">
            <select id="dbdict-table-select"><option value="">请选择表</option></select>
            <button type="button" class="btn btn-sm" id="dbdict-refresh">刷新</button>
        </form>
    </div>
</div>
<div class="card card-panel">
    <div class="card-body"><div id="dbdict-columns-table"></div></div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var tableIns = null;
    function renderColumns(tableName) {
        if (tableIns) {
            tableIns.reload({table: tableName || ''});
            return;
        }
        tableIns = U.table({
            el: '#dbdict-columns-table',
            url: '/admin/system/database/dict/columns',
            where: {table: tableName || ''},
            cols: [
                {key: 'field', title: '字段'},
                {key: 'type', title: '类型'},
                {key: 'null', title: '可空', width: 70},
                {key: 'key', title: '索引', width: 70},
                {key: 'default', title: '默认值', width: 120},
                {key: 'extra', title: '额外', width: 120},
                {key: 'comment', title: '备注'},
                {key: 'collation', title: '排序规则', width: 140}
            ]
        });
    }
    function loadTables(autoSelectFirst) {
        U.get('/admin/system/database/dict/tables').then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '加载失败', 'err'); return; }
            var list = res.data && Array.isArray(res.data.data) ? res.data.data : [];
            var sel = document.getElementById('dbdict-table-select');
            var html = '<option value="">请选择表</option>';
            list.forEach(function (item) {
                var name = item.name || '';
                if (!name) return;
                html += '<option value="' + U.escape(name) + '">' + U.escape(name + (item.comment ? ' - ' + item.comment : '')) + '</option>';
            });
            sel.innerHTML = html;
            if (autoSelectFirst && list[0] && list[0].name) {
                sel.value = list[0].name;
                renderColumns(list[0].name);
            }
        });
    }
    U.on('#dbdict-table-select', 'change', function () {
        renderColumns(this.value);
    });
    U.on('#dbdict-refresh', 'click', function () {
        var current = document.getElementById('dbdict-table-select').value || '';
        loadTables(false);
        if (current) renderColumns(current);
    });
    loadTables(true);
})();
</script>
@endpush
