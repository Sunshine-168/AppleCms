@extends('admin.layouts.inner')
@section('title', admin_t('page.apidoc'))

@section('plain')
<div class="card card-panel api-config-index">
    <div class="card-header">
        <span>{{ admin_t('ui.api_notes_title') }}</span>
        <div>
            <a class="btn btn-muted btn-sm" href="/admin/video/config/api">{{ admin_t('page.config_api') }}</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/config/interface">{{ admin_t('page.config_interface') }}</a>
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">{{ admin_t('ui.apidoc_lead_before') }}「<a href="/admin/video/config/api">{{ admin_t('page.config_api') }}</a>」。{{ admin_t('ui.apidoc_lead_after') }}</p>
        <table class="data">
            <thead><tr><th>{{ admin_t('ui.col_api') }}</th><th>{{ admin_t('ui.col_explain') }}</th></tr></thead>
            <tbody>
                <tr><td><code>GET /api/provide/vod?ac=list</code></td><td>{{ admin_t('ui.api_list') }}</td></tr>
                <tr><td><code>GET /api/provide/vod?ac=detail&amp;ids=1,2</code></td><td>{{ admin_t('ui.api_detail') }}</td></tr>
                <tr><td><code>GET /api/provide/manga?ac=list</code></td><td>{{ admin_t('ui.api_manga_list') }}</td></tr>
                <tr><td><code>GET /api/provide/manga?ac=detail&amp;ids=1,2</code></td><td>{{ admin_t('ui.api_manga_detail') }}</td></tr>
                <tr><td><code>&amp;t=分类ID &amp;wd=关键词 &amp;pg=页码 &amp;h=小时</code></td><td>{{ admin_t('ui.api_filters') }}</td></tr>
                <tr><td><code>&amp;at=xml</code></td><td>{{ admin_t('ui.api_xml') }}</td></tr>
                <tr><td><code>GET /api/app/vod</code></td><td>{{ admin_t('ui.api_app') }}</td></tr>
                <tr><td><code>GET /api/app/home</code></td><td>{{ admin_t('ui.api_app_front') }}</td></tr>
                <tr><td><code>GET /api/app/videos/{id}</code> · <code>/play/{id}</code></td><td>{{ admin_t('ui.api_app_play') }}</td></tr>
                <tr><td><code>POST /api/app/member/login</code></td><td>{{ admin_t('ui.api_app_member') }}</td></tr>
                <tr><td><code>POST /api/receive/vod</code></td><td>{{ admin_t('ui.api_recv_vod') }}</td></tr>
                <tr><td><code>POST /api/receive/manga</code></td><td>{{ admin_t('ui.api_recv_manga') }}</td></tr>
            </tbody>
        </table>
        <p class="muted field-hint">{{ admin_t('ui.apidoc_hint') }}</p>
    </div>
</div>
@endsection
