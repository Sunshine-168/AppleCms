@extends('admin.layouts.inner')
@section('title', admin_t('page.apidoc'))

@section('plain')
<div class="card card-panel api-config-index">
    <div class="card-header">
        <span>接口说明</span>
        <div>
            <a class="btn btn-muted btn-sm" href="/admin/video/config/api">开放 API</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/config/interface">入库接口</a>
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">接口字段对照。密钥、试拉和复制地址在「<a href="/admin/video/config/api">开放 API</a>」。只出已发布影片。</p>
        <table class="data">
            <thead><tr><th>接口</th><th>说明</th></tr></thead>
            <tbody>
                <tr><td><code>GET /api/provide/vod?ac=list</code></td><td>分类与影片列表</td></tr>
                <tr><td><code>GET /api/provide/vod?ac=detail&amp;ids=1,2</code></td><td>详情（含播放地址）</td></tr>
                <tr><td><code>&amp;t=分类ID &amp;wd=关键词 &amp;pg=页码 &amp;h=小时</code></td><td>筛选参数</td></tr>
                <tr><td><code>&amp;at=xml</code></td><td>输出 XML，默认 JSON</td></tr>
                <tr><td><code>GET /api/app/vod</code></td><td>APP 列表，可另设密钥</td></tr>
                <tr><td><code>POST /api/receive/vod</code></td><td>站外入库影片，在入库接口里配密钥</td></tr>
                <tr><td><code>POST /api/receive/manga</code></td><td>站外入库漫画（插件开启时），密钥同上</td></tr>
            </tbody>
        </table>
        <p class="muted field-hint">对方后台采集源填本站 provide 地址。漫画采集走采集源写入到「漫画」，或 POST 入库接口。没有演员单独接口。</p>
    </div>
</div>
@endsection
