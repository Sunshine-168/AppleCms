@extends('admin.layouts.inner')
@section('title', '搜索推送')

@section('content')
    <p class="hint">Token 在站点设置中填写。增量 sitemap：<a href="/sitemap.xml?inc=1" target="_blank">/sitemap.xml?inc=1</a>（最近 48 小时）。</p>
    <div class="toolbar">
        <button type="button" class="btn btn-sm" data-engine="baidu">百度 50 条</button>
        <button type="button" class="btn btn-muted btn-sm" data-engine="shenma">神马 50 条</button>
        <button type="button" class="btn btn-muted btn-sm" data-engine="bing">必应 50 条</button>
    </div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('[data-engine]').forEach(function (btn) {
    btn.addEventListener('click', function () {
        AdminUi.post('/admin/video/push/run', {engine: btn.getAttribute('data-engine'), limit: 50}).then(function (res) {
            AdminUi.toast((res && res.msg) || '完成', res && res.code === 0 ? 'ok' : 'err');
        });
    });
});
</script>
@endpush
