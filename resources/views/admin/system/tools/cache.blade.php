@extends('admin.layouts.inner')
@section('title', admin_t('page.cache'))

@section('plain')
<div class="card card-panel">
    <div class="card-header"><span>缓存信息</span></div>
    <div class="card-body">
        <table class="data">
            <tbody>
                <tr><td width="180">默认 Store</td><td id="cache-default-store">-</td></tr>
                <tr><td>驱动</td><td id="cache-driver">-</td></tr>
                <tr><td>前缀</td><td id="cache-prefix">-</td></tr>
                <tr><td>数据库表</td><td id="cache-db-table">-</td></tr>
                <tr><td>数据库缓存条数</td><td id="cache-db-count">-</td></tr>
            </tbody>
        </table>
        <div class="toolbar" style="margin-top:12px;">
            <button type="button" class="btn btn-muted btn-sm" id="cache-refresh-btn">刷新</button>
            <button type="button" class="btn btn-danger btn-sm" id="cache-flush-btn">清空缓存</button>
        </div>
    </div>
</div>
<div class="card card-panel">
    <div class="card-header"><span>框架缓存命令</span></div>
    <div class="card-body">
        <div class="toolbar">
            <button type="button" class="btn btn-sm" data-cmd="optimize:clear">optimize:clear</button>
            <button type="button" class="btn btn-muted btn-sm" data-cmd="cache:clear">cache:clear</button>
            <button type="button" class="btn btn-muted btn-sm" data-cmd="config:clear">config:clear</button>
            <button type="button" class="btn btn-muted btn-sm" data-cmd="route:clear">route:clear</button>
            <button type="button" class="btn btn-muted btn-sm" data-cmd="view:clear">view:clear</button>
            <button type="button" class="btn btn-muted btn-sm" data-cmd="event:clear">event:clear</button>
        </div>
        <div class="toolbar">
            <button type="button" class="btn btn-muted btn-sm" data-cmd="config:cache">config:cache</button>
            <button type="button" class="btn btn-muted btn-sm" data-cmd="route:cache">route:cache</button>
            <button type="button" class="btn btn-muted btn-sm" data-cmd="view:cache">view:cache</button>
            <button type="button" class="btn btn-muted btn-sm" data-cmd="event:cache">event:cache</button>
        </div>
        <pre id="cache-cmd-output" class="out"></pre>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    function setText(id, val) {
        document.getElementById(id).textContent = (val === null || val === undefined || val === '') ? '-' : String(val);
    }
    function refreshInfo() {
        U.get('/admin/system/tools/cache/info').then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '获取失败', 'err'); return; }
            var d = res.data || {};
            setText('cache-default-store', d.default_store);
            setText('cache-driver', d.driver);
            setText('cache-prefix', d.prefix);
            setText('cache-db-table', d.database_table);
            setText('cache-db-count', d.database_count);
        });
    }
    U.on('#cache-refresh-btn', 'click', refreshInfo);
    U.on('#cache-flush-btn', 'click', function () {
        if (!U.confirm('确认清空缓存？')) return;
        U.loading(true);
        U.post('/admin/system/tools/cache/flush', {}).then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '清空失败', 'err'); return; }
            U.toast('清空成功', 'ok');
            refreshInfo();
        });
    });
    U.qa('[data-cmd]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            U.loading(true);
            document.getElementById('cache-cmd-output').textContent = '';
            U.post('/admin/system/tools/cache/run', {command: btn.getAttribute('data-cmd')}).then(function (res) {
                U.loading(false);
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '执行失败', 'err'); return; }
                document.getElementById('cache-cmd-output').textContent = (res.data && res.data.output) || '';
                U.toast('执行成功', 'ok');
                refreshInfo();
            });
        });
    });
    refreshInfo();
})();
</script>
@endpush
