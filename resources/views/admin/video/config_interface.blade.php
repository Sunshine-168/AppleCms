@extends('admin.layouts.inner')
@section('title', admin_t('page.config_interface'))

@php
    $s = $site ?? [];
    $key = trim((string) ($s['inbound_key'] ?? ''));
    $hasKey = $key !== '';
    $receiveUrl = (string) ($receiveUrl ?? url('/api.php/receive/vod'));
    $altUrl = url('/api/receive/vod');
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
        <p class="muted recycle-lead">给<strong>别的程序</strong>把片子 POST 进本站用。去资源站拉片请用「<a href="/admin/video/collects">采集源</a>」。进库后是否先待审，在「<a href="/admin/video/config/collect">内容接入</a>」里改。</p>
        <form class="settings-page interface-config-form" id="site-form">
            <h3>接收地址</h3>
            <p class="muted field-hint">只接受 POST。两个地址一样，兼容苹果 CMS 写法。</p>
            <label for="inbound_receive_url">主地址</label>
            <div class="field-inline">
                <input id="inbound_receive_url" type="text" value="{{ $receiveUrl }}" readonly>
                <button type="button" class="btn btn-muted" id="inbound-copy-url">复制</button>
            </div>
            <label for="inbound_receive_alt">备用</label>
            <div class="field-inline">
                <input id="inbound_receive_alt" type="text" value="{{ $altUrl }}" readonly>
                <button type="button" class="btn btn-muted" id="inbound-copy-alt">复制</button>
            </div>

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
                <p class="muted">成功返回 <code>code: 0</code>。缺片名或密钥不对会失败。是否先停在待审入库，跟内容接入的开关相同。</p>
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
    bindCopy('inbound-copy-alt', 'inbound_receive_alt', '已复制地址');
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
