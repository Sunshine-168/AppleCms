@extends('admin.layouts.inner')
@section('title', admin_t('page.operate_logs'))

@section('plain')
<div class="card card-panel">
    <div class="card-body">
        <form class="filter-bar" id="operate-log-search" onsubmit="return false;">
            <input type="text" name="username" placeholder="用户名">
            <input type="text" name="login_ip" placeholder="IP">
            <input type="text" name="route" placeholder="路由">
            <input type="text" name="url" placeholder="URL">
            <select name="method">
                <option value="">请求方法</option>
                <option>GET</option><option>POST</option><option>PUT</option><option>PATCH</option><option>DELETE</option>
            </select>
            <select name="status">
                <option value="">状态</option>
                <option value="1">成功</option>
                <option value="0">失败</option>
            </select>
            <input type="date" name="start_time">
            <input type="date" name="end_time">
            <button type="button" class="btn btn-sm" id="operate-log-search-btn">查询</button>
            <button type="reset" class="btn btn-muted btn-sm" id="operate-log-reset-btn">重置</button>
            <button type="button" class="btn btn-muted btn-sm" id="operate-log-refresh-btn">刷新</button>
        </form>
    </div>
</div>
<div class="card card-panel">
    <div class="card-body"><div id="operate-log-table"></div></div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var table = U.table({
        el: '#operate-log-table',
        url: '/admin/system/monitor/operate-logs/list',
        cols: [
            {key: 'id', title: 'ID', width: 80},
            {key: 'username', title: '用户名', width: 120},
            {title: '状态', width: 80, html: function (d) {
                return parseInt(d.status, 10) === 1 ? U.status(true, '成功') : U.status(false, '失败');
            }},
            {key: 'method', title: '方法', width: 80},
            {key: 'route', title: '路由'},
            {key: 'url', title: 'URL'},
            {key: 'login_ip', title: 'IP', width: 120},
            {key: 'ip_address', title: '归属地', width: 140},
            {key: 'duration_ms', title: '耗时ms', width: 90},
            {key: 'create_time', title: '时间', width: 160}
        ]
    });
    U.on('#operate-log-search-btn', 'click', function () { table.reload(U.formData('#operate-log-search')); });
    U.on('#operate-log-reset-btn', 'click', function () { setTimeout(function () { table.reload({}); }, 0); });
    U.on('#operate-log-refresh-btn', 'click', function () { table.refresh(); });
})();
</script>
@endpush
