@extends('admin.layouts.inner')
@section('title', '执行 SQL')

@section('plain')
<div class="card card-panel">
    <div class="card-header"><span>执行 SQL</span></div>
    <div class="card-body">
        <label>SQL</label>
        <textarea id="dbsql-input" placeholder="请输入一条SQL语句，例如：SELECT * FROM users LIMIT 10"></textarea>
        <p class="hint">仅支持执行一条 SQL 语句；执行写操作会直接修改数据库。</p>
        <div class="form-actions">
            <button type="button" class="btn" id="dbsql-run">执行</button>
            <button type="button" class="btn btn-muted" id="dbsql-clear">清空</button>
        </div>
    </div>
</div>
<div class="card card-panel">
    <div class="card-header"><span id="dbsql-msg">等待执行</span></div>
    <div class="card-body">
        <div id="dbsql-result-table" style="display:none;"></div>
        <pre id="dbsql-result-text" class="out" style="display:none;"></pre>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    function setMsg(text) { document.getElementById('dbsql-msg').textContent = text || ''; }
    function showText(text) {
        document.getElementById('dbsql-result-table').style.display = 'none';
        var el = document.getElementById('dbsql-result-text');
        el.style.display = 'block';
        el.textContent = text || '';
    }
    U.on('#dbsql-clear', 'click', function () {
        document.getElementById('dbsql-input').value = '';
        setMsg('等待执行');
        document.getElementById('dbsql-result-table').style.display = 'none';
        document.getElementById('dbsql-result-text').style.display = 'none';
    });
    U.on('#dbsql-run', 'click', function () {
        var sql = (document.getElementById('dbsql-input').value || '').trim();
        if (!sql) { U.toast('请输入SQL', 'err'); return; }
        U.loading(true);
        U.post('/admin/system/database/sql/run', {sql: sql}).then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) {
                setMsg('执行失败');
                showText((res && res.msg) || '执行失败');
                return;
            }
            var data = res.data || {};
            if (data.type === 'query') {
                var columns = Array.isArray(data.columns) ? data.columns : [];
                var rows = Array.isArray(data.rows) ? data.rows : [];
                setMsg('执行成功，返回 ' + (data.count || 0) + ' 行');
                document.getElementById('dbsql-result-text').style.display = 'none';
                var wrap = document.getElementById('dbsql-result-table');
                wrap.style.display = 'block';
                var html = '<div class="ui-table-wrap"><table class="data"><thead><tr>';
                (columns.length ? columns : ['_']).forEach(function (c) { html += '<th>' + U.escape(c) + '</th>'; });
                html += '</tr></thead><tbody>';
                if (!rows.length) html += '<tr><td colspan="' + (columns.length || 1) + '"><div class="list-empty"><p>暂无数据</p></div></td></tr>';
                rows.forEach(function (row) {
                    html += '<tr>';
                    (columns.length ? columns : ['_']).forEach(function (c) { html += '<td>' + U.escape(row[c] == null ? '' : row[c]) + '</td>'; });
                    html += '</tr>';
                });
                html += '</tbody></table></div>';
                wrap.innerHTML = html;
                return;
            }
            if (data.type === 'affecting') {
                setMsg('执行成功，影响行数 ' + (data.affected || 0));
                showText('影响行数: ' + (data.affected || 0));
                return;
            }
            setMsg('执行成功');
            showText(JSON.stringify(data, null, 2));
        });
    });
})();
</script>
@endpush
