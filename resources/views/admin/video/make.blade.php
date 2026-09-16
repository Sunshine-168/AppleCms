@extends('admin.layouts.inner')
@section('title', '写出静态 HTML 到 public/html')

@section('content')
    <p class="hint">会请求前台页面并把结果写成文件：首页、分类、详情。可配合 Web 服务器把 html 目录当静态根。</p>
    <div class="toolbar">
        <button type="button" class="btn btn-sm" data-scope="index">生成首页</button>
        <button type="button" class="btn btn-muted btn-sm" data-scope="type">生成分类</button>
        <button type="button" class="btn btn-muted btn-sm" data-scope="detail">生成详情</button>
        <button type="button" class="btn btn-muted btn-sm" data-scope="actor">生成演员</button>
        <button type="button" class="btn btn-muted btn-sm" data-scope="topic">生成专题</button>
        <button type="button" class="btn btn-muted btn-sm" data-scope="tag">生成标签</button>
        <button type="button" class="btn btn-muted btn-sm" data-scope="art">生成文章</button>
        <button type="button" class="btn btn-muted btn-sm" data-scope="website">生成网址</button>
        <button type="button" class="btn btn-danger btn-sm" data-scope="all">全部生成</button>
        <button type="button" class="btn btn-sm" data-map="sitemap">生成地图</button>
        <button type="button" class="btn btn-muted btn-sm" data-map="rss">生成RSS</button>
        <button type="button" class="btn btn-muted btn-sm" id="hits-reset">重置日人气</button>
    </div>
    <pre id="make-result" class="out"></pre>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var out = document.getElementById('make-result');
    U.on('#hits-reset', 'click', function () {
        U.post('/admin/video/hits-reset', {}).then(function (res) {
            U.toast((res && res.msg) || '完成', res && res.code === 0 ? 'ok' : 'err');
        });
    });
    document.querySelectorAll('[data-scope]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            U.loading(true);
            U.post('/admin/video/make/run', {scope: btn.getAttribute('data-scope')}).then(function (res) {
                U.loading(false);
                out.textContent = JSON.stringify(res, null, 2);
                U.toast((res && res.msg) || '完成', res && res.code === 0 ? 'ok' : 'err');
            });
        });
    });
    document.querySelectorAll('[data-map]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            U.loading(true);
            U.post('/admin/video/make/map', {scope: btn.getAttribute('data-map')}).then(function (res) {
                U.loading(false);
                out.textContent = JSON.stringify(res, null, 2);
                U.toast((res && res.msg) || '完成', res && res.code === 0 ? 'ok' : 'err');
            });
        });
    });
})();
</script>
@endpush
