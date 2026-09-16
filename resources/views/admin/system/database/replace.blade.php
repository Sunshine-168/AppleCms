@extends('admin.layouts.inner')
@section('title', '批量替换')

@section('content')
    <label>选择表</label>
    <select id="dbreplace-table"><option value="">请选择表</option></select>
    <p class="hint">选中表后会加载字段。点击字段加入已选。</p>
    <label>字段</label>
    <div class="toolbar" id="dbreplace-fields"></div>
    <label>已选字段</label>
    <div id="dbreplace-selected"></div>
    <label>被替换内容</label>
    <input type="text" id="dbreplace-from" placeholder="例如：old_text">
    <label>替换为</label>
    <input type="text" id="dbreplace-to" placeholder="例如：new_text">
    <label>替换条件</label>
    <textarea id="dbreplace-where" placeholder="可选，例如：id > 100 AND status = 1"></textarea>
    <p class="hint">留空则对整张表执行。</p>
    <div class="form-actions">
        <button type="button" class="btn btn-danger" id="dbreplace-run">执行替换</button>
        <button type="button" class="btn btn-muted" id="dbreplace-reset">重置</button>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var columnsCache = {};
    var selectedFields = [];
    function renderSelected() {
        var html = '<table class="data"><thead><tr><th>字段名</th><th></th></tr></thead><tbody>';
        if (!selectedFields.length) html += '<tr><td colspan="2"><div class="list-empty"><p>尚未选择</p></div></td></tr>';
        selectedFields.forEach(function (f) {
            html += '<tr><td>' + U.escape(f) + '</td><td class="actions"><a href="#" class="btn-link js-rm" data-f="' + U.escape(f) + '">移除</a></td></tr>';
        });
        html += '</tbody></table>';
        document.getElementById('dbreplace-selected').innerHTML = html;
    }
    function addField(field) {
        field = String(field || '').trim();
        if (!field || selectedFields.indexOf(field) >= 0) return;
        selectedFields.push(field);
        renderSelected();
    }
    function renderButtons(tableName) {
        var rows = columnsCache[tableName] || [];
        var wrap = document.getElementById('dbreplace-fields');
        wrap.innerHTML = '';
        rows.forEach(function (r) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn btn-muted btn-sm';
            btn.textContent = r.field + (r.comment ? '（' + r.comment + '）' : '');
            btn.addEventListener('click', function () { addField(r.field); });
            wrap.appendChild(btn);
        });
    }
    function loadTables() {
        U.get('/admin/system/database/dict/tables').then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '加载表失败', 'err'); return; }
            var data = res.data && Array.isArray(res.data.data) ? res.data.data : [];
            var html = '<option value="">请选择表</option>';
            data.forEach(function (item) { html += '<option value="' + U.escape(item.name || '') + '">' + U.escape(item.name || '') + '</option>'; });
            document.getElementById('dbreplace-table').innerHTML = html;
        });
    }
    function loadColumns(tableName) {
        if (!tableName) {
            document.getElementById('dbreplace-fields').innerHTML = '';
            selectedFields = [];
            renderSelected();
            return;
        }
        if (columnsCache[tableName]) {
            renderButtons(tableName);
            selectedFields = [];
            renderSelected();
            return;
        }
        U.loading(true);
        U.get('/admin/system/database/dict/columns', {table: tableName}).then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '加载字段失败', 'err'); return; }
            columnsCache[tableName] = res.data && Array.isArray(res.data.data) ? res.data.data : [];
            renderButtons(tableName);
            selectedFields = [];
            renderSelected();
        });
    }
    U.on('#dbreplace-table', 'change', function () { loadColumns(this.value); });
    U.on('#dbreplace-selected', 'click', function (e) {
        var a = e.target.closest('a.js-rm'); if (!a) return;
        e.preventDefault();
        var f = a.getAttribute('data-f');
        selectedFields = selectedFields.filter(function (x) { return x !== f; });
        renderSelected();
    });
    U.on('#dbreplace-reset', 'click', function () {
        document.getElementById('dbreplace-table').value = '';
        document.getElementById('dbreplace-fields').innerHTML = '';
        selectedFields = [];
        renderSelected();
        document.getElementById('dbreplace-from').value = '';
        document.getElementById('dbreplace-to').value = '';
        document.getElementById('dbreplace-where').value = '';
    });
    U.on('#dbreplace-run', 'click', function () {
        var tableName = document.getElementById('dbreplace-table').value;
        var from = document.getElementById('dbreplace-from').value;
        if (!tableName) { U.toast('请选择表', 'err'); return; }
        if (!selectedFields.length) { U.toast('请选择字段', 'err'); return; }
        if (from === '') { U.toast('请输入被替换内容', 'err'); return; }
        if (!U.confirm('确认执行批量替换？该操作会直接修改数据库数据')) return;
        U.loading(true);
        U.post('/admin/system/database/replace/run', {
            table: tableName,
            fields: selectedFields,
            from: from,
            to: document.getElementById('dbreplace-to').value,
            where: document.getElementById('dbreplace-where').value
        }).then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '替换失败', 'err'); return; }
            U.toast('替换成功，影响行数：' + ((res.data && res.data.affected) || 0), 'ok');
        });
    });
    renderSelected();
    loadTables();
})();
</script>
@endpush
