@extends('admin.layouts.inner')
@section('title', admin_t('page.config_interface'))

@php
    $s = $site ?? [];
    $key = trim((string) ($s['inbound_key'] ?? ''));
    $hasKey = $key !== '';
    $receiveUrl = (string) ($receiveUrl ?? url('/api/receive/vod'));
@endphp

@section('plain')
<div class="card card-panel interface-config-index">
    <div class="card-header">
        <span>入库接口</span>
        <div>
            <a class="btn btn-muted btn-sm" href="/admin/video/config/collect">内容接入</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/config/api">开放 API</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/apidoc">接口说明</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/collect_temps">待审入库</a>
        </div>
    </div>
    <div class="card-body">
            <p class="muted recycle-lead">给别的程序 POST 片子或漫画进本站。去资源站拉片用「<a href="/admin/video/collects">采集源</a>」。影片是否先待审，在「<a href="/admin/video/config/collect">内容接入</a>」改。</p>
        <form class="admin-form settings-page interface-config-form" id="site-form">
            <h3>接收地址</h3>
            <p class="muted field-hint">只接受 POST。影片 <code>/api/receive/vod</code>@if($mangaReady ?? false)，漫画 <code>/api/receive/manga</code>@endif。密钥共用。</p>
            <label for="inbound_receive_url">影片地址</label>
            <div class="field-inline">
                <input id="inbound_receive_url" type="text" value="{{ $receiveUrl }}" readonly>
                <button type="button" class="btn btn-muted" id="inbound-copy-url">复制</button>
            </div>
            @if($mangaReady ?? false)
                <label for="inbound_receive_manga_url">漫画地址</label>
                <div class="field-inline">
                    <input id="inbound_receive_manga_url" type="text" value="{{ $receiveMangaUrl ?? url('/api/receive/manga') }}" readonly>
                    <button type="button" class="btn btn-muted" id="inbound-copy-manga-url">复制</button>
                </div>
            @endif

            <h3>入库密钥</h3>
            @if($hasKey)
                <p class="muted field-hint"><span class="badge badge-ok">已设置</span> 请求必须带同一把密钥，否则返回「入库密钥无效」。</p>
            @else
                <p class="muted field-hint"><span class="badge badge-warn">还没设密钥</span> 现在推送一律会被拒绝。生成一把再保存。</p>
            @endif
            <label for="inbound_key">密钥</label>
            <div class="field-inline">
                <input id="inbound_key" type="text" name="inbound_key" value="{{ $s['inbound_key'] ?? '' }}" autocomplete="off" spellcheck="false" placeholder="空着则拒绝站外推送">
                <button type="button" class="btn btn-muted" id="inbound-gen-key">生成</button>
            </div>
            <p class="muted field-hint">放在参数 <code>key</code>，或请求头 <code>X-Inbound-Key</code>。保存后立即生效。</p>

            <h3>怎么推</h3>
            <div class="interface-howto">
                <p>POST 到上面的地址，至少带片名：</p>
                <ul>
                    <li><code>key</code> 密钥</li>
                    <li><code>vod_name</code> 片名（必填）</li>
                    <li><code>vod_pic</code> 封面</li>
                    <li><code>type_id</code> 本站分类 ID</li>
                    <li>也可把整条片子放在 <code>data</code> 里</li>
                </ul>
                @if($mangaReady ?? false)
                    <p>漫画 POST 到漫画地址，至少带作品名。章节图片用换行或 <code>###</code>，阅读页链接不会再抓。</p>
                    <ul>
                        <li><code>manga_name</code> 作品名（必填）</li>
                        <li><code>manga_id</code> 对方站作品 ID（可选，用来续更）</li>
                        <li><code>type_id</code> 本站漫画分类 ID</li>
                        <li><code>manga_pic</code> / <code>manga_author</code> / <code>manga_content</code></li>
                        <li><code>chapters</code> 数组：<code>name</code>、<code>pics</code></li>
                        <li>或 <code>chapter_name</code> + <code>images</code>（HTML 里的 img 也可）</li>
                    </ul>
                @endif
                <p class="muted">成功返回 <code>code: 0</code>。缺片名或密钥不对会失败。影片是否先停在待审入库，跟内容接入的开关相同。漫画直接进插件库。</p>
            </div>

            <div class="form-actions settings-save">
                <button type="button" class="btn" id="site-save">保存</button>
            </div>
        </form>
    </div>
</div>
@endsection

@include('admin.partials.site-save')

@push('scripts')
<script>
(function () {
    var U = window.AdminUi;
    function copyText(text, okMsg) {
        text = String(text || '');
        if (!text) { U && U.toast('没有可复制的内容', 'err'); return; }
        var done = function () { U && U.toast(okMsg || '已复制', 'ok'); };
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(done).catch(function () { fallbackCopy(text, done); });
            return;
        }
        fallbackCopy(text, done);
    }
    function fallbackCopy(text, done) {
        var ta = document.createElement('textarea');
        ta.value = text;
        document.body.appendChild(ta);
        ta.select();
        try { document.execCommand('copy'); done(); } catch (err) { U && U.toast('复制失败', 'err'); }
        ta.remove();
    }
    function bindCopy(btnId, inputId, msg) {
        var btn = document.getElementById(btnId);
        var input = document.getElementById(inputId);
        if (!btn || !input) return;
        btn.addEventListener('click', function () { copyText(input.value, msg); });
    }
    bindCopy('inbound-copy-url', 'inbound_receive_url', '已复制地址');
    bindCopy('inbound-copy-manga-url', 'inbound_receive_manga_url', '已复制地址');
    var genBtn = document.getElementById('inbound-gen-key');
    var keyInput = document.getElementById('inbound_key');
    if (genBtn && keyInput) {
        genBtn.addEventListener('click', function () {
            var chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
            var out = '';
            for (var i = 0; i < 24; i++) out += chars.charAt(Math.floor(Math.random() * chars.length));
            keyInput.value = out;
            U && U.toast('已生成，保存后才生效', 'ok');
        });
    }
})();
</script>
@endpush
