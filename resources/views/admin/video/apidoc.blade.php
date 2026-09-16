@extends('admin.layouts.inner')
@section('title', admin_t('page.apidoc'))

@section('content')
    <p class="hint">资源输出兼容苹果 CMS <code>provide/vod</code>。密钥在「开放 API」里配置，非空时请求需带 <code>key</code>。</p>
    <table class="data">
        <thead><tr><th>接口</th><th>说明</th></tr></thead>
        <tbody>
            <tr><td><code>GET /api.php/provide/vod?ac=list</code></td><td>分类与影片列表</td></tr>
            <tr><td><code>GET /api.php/provide/vod?ac=detail&amp;ids=1,2</code></td><td>详情（含播放地址）</td></tr>
            <tr><td><code>&amp;t=分类ID &amp;wd=关键词 &amp;pg=页码 &amp;h=小时</code></td><td>筛选参数</td></tr>
            <tr><td><code>&amp;at=xml</code></td><td>输出 XML，默认 JSON</td></tr>
            <tr><td><code>GET /api.php/app/vod</code></td><td>APP 列表</td></tr>
            <tr><td><code>POST /api.php/receive/vod</code></td><td>站外入库，需 inbound_key</td></tr>
        </tbody>
    </table>
    <p class="hint">采集对接方把本站 provide 地址填到对方后台即可。</p>
    <div class="toolbar">
        <a class="btn btn-sm" href="/admin/video/config/api">配置密钥</a>
        <a class="btn btn-muted btn-sm" href="/admin/video/config/interface">入库接口</a>
    </div>
@endsection
