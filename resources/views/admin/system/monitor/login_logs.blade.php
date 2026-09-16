@extends('admin.layouts.inner')
@section('title', admin_t('page.login_logs'))

@section('plain')
<div class="card card-panel">
    <div class="card-body">
        <form class="filter-bar" id="sysuserlog-search" onsubmit="return false;">
            <input type="text" name="username" placeholder="用户名">
            <input type="text" name="login_ip" placeholder="登录IP">
            <input type="date" name="start_time">
            <input type="date" name="end_time">
            <button type="button" class="btn btn-sm" id="sysuserlog-search-btn">查询</button>
            <button type="reset" class="btn btn-muted btn-sm" id="sysuserlog-reset-btn">重置</button>
            <button type="button" class="btn btn-muted btn-sm" id="sysuserlog-refresh-btn">刷新</button>
        </form>
    </div>
</div>
<div class="card card-panel">
    <div class="card-body"><div id="sysuserlog-table"></div></div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var table = U.table({
        el: '#sysuserlog-table',
        url: '/admin/system/monitor/login-logs/list',
        cols: [
            {key: 'id', title: 'ID', width: 80},
            {key: 'username', title: '用户名', width: 140},
            {key: 'login_ip', title: '登录IP', width: 130},
            {key: 'ip_address', title: '归属地'},
            {key: 'login_agent', title: 'UA请求头'},
            {key: 'create_time', title: '时间', width: 160}
        ]
    });
    U.on('#sysuserlog-search-btn', 'click', function () { table.reload(U.formData('#sysuserlog-search')); });
    U.on('#sysuserlog-reset-btn', 'click', function () { setTimeout(function () { table.reload({}); }, 0); });
    U.on('#sysuserlog-refresh-btn', 'click', function () { table.refresh(); });
})();
</script>
@endpush
