@extends('admin.layouts.inner')
@section('title', admin_t('page.config_api'))

@php
    $s = $site ?? [];
    $key = trim((string) ($s['provide_key'] ?? ''));
    $appKey = trim((string) ($s['app_key'] ?? ''));
    $hasKey = (bool) ($has_key ?? ($key !== ''));
    $appKeySet = (bool) ($app_key_set ?? ($appKey !== ''));
    $videoCount = (int) ($video_count ?? 0);
    $provideUrl = (string) ($provide_url ?? url('/api.php/provide/vod'));
    $altUrl = (string) ($provide_alt ?? url('/api/provide/vod'));
    $appUrl = (string) ($app_url ?? url('/api.php/app/vod'));
@endphp

@section('plain')
<div class="card card-panel api-config-index">
    <div class="card-header">
        <span>开放 API</span>
        <div>
            <a class="btn btn-muted btn-sm" href="/admin/video/apidoc">接口说明</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/config/interface">入库接口</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/collects">采集源</a>
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">给<strong>别人</strong>来拉本站已发布的片子，兼容苹果 CMS <code>provide/vod</code>。你去拉别人用「<a href="/admin/video/collects">采集源</a>」。别人 POST 进来用「<a href="/admin/video/config/interface">入库接口</a>」。</p>

        <div class="ai-stock">
            @if($hasKey)
                <span class="badge badge-ok">要带密钥</span>
            @else
                <span class="badge badge-warn">任何人可拉</span>
            @endif
            <span class="muted">已发布 {{ $videoCount }} 部会从接口出去。详情里带播放地址。</span>
        </div>

        <form class="settings-page api-config-form" id="site-form">
            <h3>谁能拉</h3>
            <div class="ingest-modes" id="api-modes">
                <button type="button" class="ingest-mode{{ $hasKey ? '' : ' is-on' }}" data-mode="open">
                    <strong>公开</strong>
                    <span>不设密钥。外站填地址就能拉列表和播放地址。</span>
                </button>
                <button type="button" class="ingest-mode{{ $hasKey ? ' is-on' : '' }}" data-mode="key">
                    <strong>要带密钥</strong>
                    <span>请求必须带同一把 <code>key</code>，否则返回「密钥无效」。</span>
                </button>
            </div>

            <div id="api-key-wrap" @if(! $hasKey) hidden @endif>
                <label for="provide_key">密钥</label>
                <div class="field-inline">
                    <input id="provide_key" type="text" name="provide_key" value="{{ $key }}" autocomplete="off" spellcheck="false" placeholder="生成一把再保存" @disabled(! $hasKey)>
                    <button type="button" class="btn btn-muted" id="api-gen-key">生成</button>
                </div>
                <p class="muted field-hint">放在参数 <code>key</code>，或请求头 <code>X-Provide-Key</code>。空着保存等于公开。保存后立即生效。</p>
            </div>

            <h3>给对方的地址</h3>
            <p class="muted field-hint">采集源填这一条。有密钥时，复制会把当前输入框里的 <code>key</code> 拼上去。</p>
            <label for="api_provide_url">主地址</label>
            <div class="field-inline">
                <input id="api_provide_url" type="text" value="{{ $provideUrl }}" readonly>
                <button type="button" class="btn btn-muted" id="api-copy-url">复制</button>
            </div>
            <label for="api_provide_alt">备用</label>
            <div class="field-inline">
                <input id="api_provide_alt" type="text" value="{{ $altUrl }}" readonly>
                <button type="button" class="btn btn-muted" id="api-copy-alt">复制</button>
            </div>

            <h3>对方怎么调</h3>
            <p class="muted field-hint">数字 1 只是举例。只出已发布的影片，没有文章 / 演员单独接口。</p>
            <div class="rewrite-examples api-examples">
                <div class="rewrite-ex">
                    <span>分类列表</span>
                    <code class="js-api-ex" data-query="ac=list"></code>
                    <button type="button" class="btn-link js-api-copy-ex">复制</button>
                </div>
                <div class="rewrite-ex">
                    <span>某部详情</span>
                    <code class="js-api-ex" data-query="ac=detail&amp;ids=1"></code>
                    <button type="button" class="btn-link js-api-copy-ex">复制</button>
                </div>
                <div class="rewrite-ex">
                    <span>XML</span>
                    <code class="js-api-ex" data-query="ac=list&amp;at=xml"></code>
                    <button type="button" class="btn-link js-api-copy-ex">复制</button>
                </div>
            </div>
            <div class="interface-howto">
                <p>常用参数：</p>
                <ul>
                    <li><code>ac=list</code> 分类 + 列表；<code>ac=detail&amp;ids=1,2</code> 含播放地址</li>
                    <li><code>t</code> 分类 ID，<code>wd</code> 片名，<code>pg</code> 页码，<code>h</code> 最近几小时</li>
                    <li><code>at=xml</code> 输出 XML，默认 JSON</li>
                </ul>
                <p class="muted">通了会返回 <code>code: 1</code>。密钥不对是 403「密钥无效」。完整对照在「<a href="/admin/video/apidoc">接口说明</a>」。</p>
            </div>

            <details class="settings-details">
                <summary>APP 接口另用一把钥匙</summary>
                <p class="muted field-hint">地址 <code>{{ $appUrl }}</code>。空着则跟上面同一把。@if($appKeySet) 现在单独设过。@endif</p>
                <label for="app_key">APP 密钥</label>
                <input id="app_key" type="text" name="app_key" value="{{ $appKey }}" autocomplete="off" spellcheck="false" placeholder="空着则用上面那把">
            </details>

            <div class="form-actions settings-save">
                <button type="button" class="btn" id="site-save">保存</button>
                <button type="button" class="btn btn-muted" id="api-probe">试拉一把</button>
            </div>
            <p class="muted field-hint">试拉走网上正在用的规则，改了密钥要先保存。不会改片库。</p>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = window.AdminUi;
    var provideUrl = @json($provideUrl);
    var altUrl = @json($altUrl);
    var originalKey = @json($key);
    var originalApp = @json($appKey);
    var keyInput = document.getElementById('provide_key');
    var appInput = document.getElementById('app_key');
    var wrap = document.getElementById('api-key-wrap');
    var modes = document.getElementById('api-modes');
    var modeKey = {{ $hasKey ? 'true' : 'false' }};

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
    function currentKey() {
        return modeKey && keyInput ? String(keyInput.value || '').trim() : '';
    }
    function withKey(base) {
        var k = currentKey();
        if (!k) return base;
        return base + (base.indexOf('?') >= 0 ? '&' : '?') + 'key=' + encodeURIComponent(k);
    }
    function fillExamples() {
        document.querySelectorAll('.js-api-ex').forEach(function (el) {
            var q = el.getAttribute('data-query') || '';
            var url = provideUrl + (q ? '?' + q : '');
            el.textContent = withKey(url);
        });
    }
    function genKey() {
        var chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
        var out = '';
        for (var i = 0; i < 24; i++) out += chars.charAt(Math.floor(Math.random() * chars.length));
        return out;
    }
    function setMode(on) {
        modeKey = !!on;
        if (modes) {
            modes.querySelectorAll('.ingest-mode').forEach(function (btn) {
                btn.classList.toggle('is-on', (btn.getAttribute('data-mode') === 'key') === modeKey);
            });
        }
        if (wrap) wrap.hidden = !modeKey;
        if (keyInput) keyInput.disabled = !modeKey;
        if (modeKey && keyInput && !String(keyInput.value || '').trim()) {
            keyInput.value = genKey();
            U && U.toast('已生成，保存后才生效', 'ok');
        }
        fillExamples();
    }
    if (modes) {
        modes.querySelectorAll('.ingest-mode').forEach(function (btn) {
            btn.addEventListener('click', function () { setMode(btn.getAttribute('data-mode') === 'key'); });
        });
    }
    var genBtn = document.getElementById('api-gen-key');
    if (genBtn) {
        genBtn.addEventListener('click', function () {
            setMode(true);
            if (keyInput) keyInput.value = genKey();
            fillExamples();
            U && U.toast('已生成，保存后才生效', 'ok');
        });
    }
    if (keyInput) keyInput.addEventListener('input', fillExamples);
    fillExamples();

    document.getElementById('api-copy-url') && document.getElementById('api-copy-url').addEventListener('click', function () {
        copyText(withKey(provideUrl), currentKey() && currentKey() !== originalKey ? '已复制（密钥还没保存）' : '已复制地址');
    });
    document.getElementById('api-copy-alt') && document.getElementById('api-copy-alt').addEventListener('click', function () {
        copyText(withKey(altUrl), '已复制地址');
    });
    document.querySelectorAll('.js-api-copy-ex').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var code = btn.parentNode ? btn.parentNode.querySelector('.js-api-ex') : null;
            copyText(code ? code.textContent : '');
        });
    });

    var save = document.getElementById('site-save');
    var form = document.getElementById('site-form');
    function payload() {
        return {
            provide_key: currentKey(),
            app_key: appInput ? String(appInput.value || '').trim() : ''
        };
    }
    function doSave() {
        var data = payload();
        if (data.provide_key === originalKey && data.app_key === originalApp) {
            U && U.toast('没有改动，不用保存', 'ok');
            return;
        }
        U.post('/admin/video/settings', data).then(function (res) {
            U.toast((res && res.msg) || '完成', res && res.code === 0 ? 'ok' : 'err');
            if (res && res.code === 0) {
                originalKey = data.provide_key;
                originalApp = data.app_key;
            }
        });
    }
    if (save) save.addEventListener('click', doSave);
    if (form) form.addEventListener('submit', function (e) { e.preventDefault(); doSave(); });

    var probe = document.getElementById('api-probe');
    if (probe) {
        probe.addEventListener('click', function () {
            var data = payload();
            if (data.provide_key !== originalKey) {
                U && U.toast('改动还没保存，试拉的是网上正在用的规则', 'ok');
            }
            var url = provideUrl + '?ac=list';
            if (originalKey) url += '&key=' + encodeURIComponent(originalKey);
            U.loading(true);
            fetch(url, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } }).then(function (r) {
                return r.text().then(function (text) {
                    var json = null;
                    try { json = JSON.parse(text); } catch (e) {}
                    return { http: r.status, json: json, text: text };
                });
            }).then(function (res) {
                U.loading(false);
                var json = res.json || {};
                if (json.code === 1) {
                    var n = json.total != null ? json.total : 0;
                    U.toast(n > 0 ? ('通了，接口里有 ' + n + ' 部') : '接口通了，还没有已发布的片子', 'ok');
                    return;
                }
                U.toast(json.msg || ('HTTP ' + res.http), 'err');
            }).catch(function () {
                U.loading(false);
                U.toast('试拉失败，请看接口说明里的地址', 'err');
            });
        });
    }
})();
</script>
@endpush
