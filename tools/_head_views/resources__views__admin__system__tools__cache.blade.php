fatal: path 'resources\views\admin\system\tools\cache.blade.php' exists on disk, but not in 'HEAD'
@extends('admin.layouts.inner')
@section('title', admin_t('page.cache'))

@php
    $driverLabel = (string) ($driver_label ?? '');
    $driverHint = (string) ($driver_hint ?? '');
    $data = $data ?? ['detail' => ''];
    $views = $views ?? ['detail' => ''];
    $configDetail = (string) ($config_detail ?? '');
    $packed = (bool) ($packed ?? false);
    $htmlOn = (bool) ($html_cache_on ?? false);
@endphp

@section('plain')
<div class="card card-panel cache-index">
    <div class="card-header">
        <span>缓存</span>
        <div>
            <a class="btn btn-muted btn-sm" href="/admin/video/templates">模板</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/make">静态生成</a>
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">改了设置或模板后清缓存。整页静态在「<a href="/admin/video/make">静态生成</a>」。</p>
        <p class="cache-note{{ $htmlOn ? '' : ' is-off' }}" id="cache-html-note">全页缓存开着。整页还是旧的，去静态生成清，点这里清不到。</p>

        <div class="cache-block">
            <div class="cache-block-head">
                <h3>数据缓存</h3>
                <span class="badge" id="cache-driver-badge">{{ $driverLabel !== '' ? $driverLabel : '未知' }}</span>
            </div>
            <p class="muted cache-block-detail" id="cache-data-detail">{{ $data['detail'] ?? '' }}</p>
            <p class="muted field-hint">字典、查询结果、后台记的临时数据。{{ $driverHint }}</p>
            <button type="button" class="btn btn-muted btn-sm" data-kind="data">清掉</button>
        </div>

        <div class="cache-block">
            <div class="cache-block-head">
                <h3>模板</h3>
            </div>
            <p class="muted cache-block-detail" id="cache-views-detail">{{ $views['detail'] ?? '' }}</p>
            <p class="muted field-hint">改过主题或后台页面还显示旧样子时清。先确认「<a href="/admin/video/templates">模板</a>」里已经保存。</p>
            <button type="button" class="btn btn-muted btn-sm" data-kind="views">清掉</button>
        </div>

        <div class="cache-block">
            <div class="cache-block-head">
                <h3>配置和路由</h3>
                <span class="badge{{ $packed ? ' badge-off' : ' badge-ok' }}" id="cache-packed-badge">{{ $packed ? '已打包' : '每次读最新' }}</span>
            </div>
            <p class="muted cache-block-detail" id="cache-config-detail">{{ $configDetail }}</p>
            <p class="muted field-hint" id="cache-config-hint">{{ $packed ? '打包后改程序配置不会马上生效，先解开。' : '现在没有打包，改配置会马上读到。' }}</p>
            <button type="button" class="btn btn-muted btn-sm" data-kind="config">解开打包</button>
        </div>

        <div class="cache-block cache-block-all">
            <div class="cache-block-head">
                <h3>改完还不生效</h3>
            </div>
            <p class="muted field-hint">一次清掉数据、模板、配置和路由。前台第一次打开会稍慢。</p>
            <button type="button" class="btn" data-kind="all" id="cache-clear-all">全部清掉</button>
        </div>

        <details class="settings-details cache-advanced">
            <summary>打包加速（上线后）</summary>
            <p class="muted field-hint">把配置、路由、模板做成文件，少读磁盘。打包后改配置文件不会立刻生效，本地开发不要点。</p>
            <div class="cache-pack-actions">
                <button type="button" class="btn btn-muted btn-sm" data-kind="pack-config" data-confirm="打包后改配置文件不会立刻生效。本地开发不要点。确定？">打包配置</button>
                <button type="button" class="btn btn-muted btn-sm" data-kind="pack-routes" data-confirm="打包后改路由不会立刻生效。确定？">打包路由</button>
                <button type="button" class="btn btn-muted btn-sm" data-kind="pack-views" data-confirm="预编译后改模板要先清掉才会用新文件。确定？">预编译模板</button>
            </div>
        </details>
        <p class="muted" id="cache-flash" hidden></p>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var CONFIRMS = {
        all: '全部清掉后，前台第一次打开会稍慢。确定？'
    };
    function applyBoard(d) {
        if (!d) return;
        var driver = document.getElementById('cache-driver-badge');
        if (driver) driver.textContent = d.driver_label || '未知';
        var data = document.getElementById('cache-data-detail');
        if (data) data.textContent = (d.data && d.data.detail) || '';
        var views = document.getElementById('cache-views-detail');
        if (views) views.textContent = (d.views && d.views.detail) || '';
        var config = document.getElementById('cache-config-detail');
        if (config) config.textContent = d.config_detail || '';
        var badge = document.getElementById('cache-packed-badge');
        if (badge) {
            badge.textContent = d.packed ? '已打包' : '每次读最新';
            badge.classList.toggle('badge-off', !!d.packed);
            badge.classList.toggle('badge-ok', !d.packed);
        }
        var hint = document.getElementById('cache-config-hint');
        if (hint) hint.textContent = d.packed ? '打包后改程序配置不会马上生效，先解开。' : '现在没有打包，改配置会马上读到。';
        var note = document.getElementById('cache-html-note');
        if (note) note.classList.toggle('is-off', !d.html_cache_on);
    }
    function runKind(kind, confirmMsg) {
        if (confirmMsg && !U.confirm(confirmMsg)) return;
        U.loading(true);
        U.post('/admin/system/tools/cache/clear', {kind: kind}).then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '没能执行', 'err'); return; }
            U.toast((res && res.msg) || '已完成', 'ok');
            applyBoard(res.data || {});
            var flash = document.getElementById('cache-flash');
            if (flash) {
                flash.hidden = false;
                flash.textContent = (res && res.msg) || '';
            }
        }).catch(function () {
            U.loading(false);
            U.toast('没能执行', 'err');
        });
    }
    U.qa('[data-kind]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var kind = btn.getAttribute('data-kind') || '';
            var msg = btn.getAttribute('data-confirm') || CONFIRMS[kind] || '';
            runKind(kind, msg);
        });
    });
})();
</script>
@endpush