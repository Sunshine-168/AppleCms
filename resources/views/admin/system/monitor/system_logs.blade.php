@extends('admin.layouts.inner')
@section('title', '系统日志')

@section('plain')
<div class="card card-panel">
    <div class="card-body">
        <form class="filter-bar" id="system-log-search" onsubmit="return false;">
            <input type="text" name="level" placeholder="级别 info/error...">
            <input type="text" name="channel" placeholder="通道">
            <input type="text" name="module" placeholder="模块">
            <input type="text" name="username" placeholder="用户名">
            <input type="text" name="uid" placeholder="UID">
            <input type="text" name="request_id" placeholder="RequestId">
            <select name="method">
                <option value="">请求方法</option>
                <option>GET</option><option>POST</option><option>PUT</option><option>PATCH</option><option>DELETE</option>
            </select>
            <input type="text" name="url" placeholder="URL">
            <input type="text" name="ip" placeholder="IP">
            <input type="date" name="start_time">
            <input type="date" name="end_time">
            <button type="button" class="btn btn-sm" id="system-log-search-btn">查询</button>
            <button type="reset" class="btn btn-muted btn-sm" id="system-log-reset-btn">重置</button>
            <button type="button" class="btn btn-muted btn-sm" id="system-log-refresh-btn">刷新</button>
        </form>
    </div>
</div>
<div class="card card-panel">
    <div class="card-body"><div id="system-log-table"></div></div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var table = U.table({
        el: '#system-log-table',
        url: '/admin/system/monitor/system-logs/list',
        cols: [
            {key: 'id', title: 'ID', width: 70},
            {key: 'level', title: '级别', width: 80},
            {key: 'channel', title: '通道', width: 90},
            {key: 'module', title: '模块', width: 90},
            {key: 'username', title: '用户名', width: 110},
            {key: 'uid', title: 'UID', width: 70},
            {key: 'request_id', title: 'RequestId', width: 140},
            {key: 'method', title: '方法', width: 70},
            {key: 'ip', title: 'IP', width: 110},
            {key: 'url', title: 'URL'},
            {key: 'exception_class', title: '异常类'},
            {title: '文件', html: function (d) { return d.file ? U.escape(d.file + ':' + (d.line || 0)) : ''; }},
            {key: 'create_time', title: '时间', width: 150},
            {title: '操作', cls: 'actions', html: function () { return '<a href="#" class="btn-link js-detail">查看</a>'; }}
        ]
    });
    U.on('#system-log-search-btn', 'click', function () { table.reload(U.formData('#system-log-search')); });
    U.on('#system-log-reset-btn', 'click', function () { setTimeout(function () { table.reload({}); }, 0); });
    U.on('#system-log-refresh-btn', 'click', function () { table.refresh(); });
    U.on('#system-log-table', 'click', function (e) {
        var a = e.target.closest('a'); if (!a || !a.classList.contains('js-detail')) return;
        var row = (table.rows() || [])[e.target.closest('tr').getAttribute('data-idx')];
        if (!row) return;
        e.preventDefault();
        U.dialog({
            title: '系统日志详情',
            wide: true,
            hideOk: true,
            content: '<p><b>Message</b></p><pre class="out">' + U.escape(row.message || '') + '</pre>'
                + '<p><b>Exception</b></p><pre class="out">' + U.escape(row.exception_message || '') + '</pre>'
                + '<p><b>Context</b></p><pre class="out">' + U.escape(row.context || '') + '</pre>'
                + '<p><b>Extra</b></p><pre class="out">' + U.escape(row.extra || '') + '</pre>'
                + '<p><b>Trace</b></p><pre class="out">' + U.escape(row.trace || '') + '</pre>'
        });
    });
})();
</script>
@endpush
