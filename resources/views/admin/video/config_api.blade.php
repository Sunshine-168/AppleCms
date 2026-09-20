@extends('admin.layouts.inner')
@section('title', admin_t('page.config_api'))

@php
    $s = $site ?? [];
    $key = trim((string) ($s['provide_key'] ?? ''));
    $appKey = trim((string) ($s['app_key'] ?? ''));
    $hasKey = (bool) ($has_key ?? ($key !== ''));
    $appKeySet = (bool) ($app_key_set ?? ($appKey !== ''));
    $videoCount = (int) ($video_count ?? 0);
    $provideUrl = (string) ($provide_url ?? url('/api/provide/vod'));
    $appUrl = (string) ($app_url ?? url('/api/app/vod'));
    $jsLang = [
        'nothing_to_copy' => admin_t('ui.nothing_to_copy'),
        'copied' => admin_t('ui.copied'),
        'copy_failed' => admin_t('ui.copy_failed'),
        'copied_url' => admin_t('ui.copied_url'),
        'copied_key_unsaved' => admin_t('ui.copied_key_unsaved'),
        'gen_save' => admin_t('ui.gen_save'),
        'no_change_save' => admin_t('ui.no_change_save'),
        'finished' => admin_t('ui.finished'),
        'probe_unsaved' => admin_t('ui.api_probe_unsaved'),
        'probe_ok_n' => admin_t('ui.api_probe_ok_n'),
        'probe_ok_empty' => admin_t('ui.api_probe_ok_empty'),
        'probe_fail' => admin_t('ui.api_probe_fail'),
    ];
@endphp

@section('plain')
<div class="card card-panel api-config-index">
    <div class="card-header">
        <span>{{ admin_t('ui.config_api') }}</span>
        <div>
            <a class="btn btn-muted btn-sm" href="/admin/video/apidoc">{{ admin_t('ui.apidoc_short') }}</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/config/interface">{{ admin_t('ui.config_interface') }}</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/collects">{{ admin_t('ui.collects') }}</a>
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">{{ admin_t('ui.api_lead_before') }}<code>/api/provide/vod</code>{{ admin_t('ui.api_lead_mid') }}<a href="/admin/video/collects">{{ admin_t('ui.collects') }}</a>{{ admin_t('ui.api_lead_mid2') }}<a href="/admin/video/config/interface">{{ admin_t('ui.config_interface') }}</a>{{ admin_t('ui.api_lead_after') }}</p>

        <div class="ai-stock">
            @if($hasKey)
                <span class="badge badge-ok">{{ admin_t('ui.api_need_key') }}</span>
            @else
                <span class="badge badge-warn">{{ admin_t('ui.api_anyone') }}</span>
            @endif
            <span class="muted">{{ admin_t('ui.api_out_n', ['n' => $videoCount]) }}</span>
        </div>

        <form class="admin-form settings-page api-config-form" id="site-form">
            <h3>{{ admin_t('ui.api_who') }}</h3>
            <div class="ingest-modes" id="api-modes">
                <button type="button" class="ingest-mode{{ $hasKey ? '' : ' is-on' }}" data-mode="open">
                    <strong>{{ admin_t('ui.api_public') }}</strong>
                    <span>{{ admin_t('ui.api_public_hint') }}</span>
                </button>
                <button type="button" class="ingest-mode{{ $hasKey ? ' is-on' : '' }}" data-mode="key">
                    <strong>{{ admin_t('ui.api_need_key') }}</strong>
                    <span>{{ admin_t('ui.api_need_key_before') }}<code>key</code>{{ admin_t('ui.api_need_key_after') }}</span>
                </button>
            </div>

            <div id="api-key-wrap" @if(! $hasKey) hidden @endif>
                <label for="provide_key">{{ admin_t('ui.secret_key') }}</label>
                <div class="field-inline">
                    <input id="provide_key" type="text" name="provide_key" value="{{ $key }}" autocomplete="off" spellcheck="false" placeholder="{{ admin_t('ui.ph_gen_then_save') }}" @disabled(! $hasKey)>
                    <button type="button" class="btn btn-muted" id="api-gen-key">{{ admin_t('ui.generate') }}</button>
                </div>
                <p class="muted field-hint">{{ admin_t('ui.provide_key_where_before') }}<code>key</code>{{ admin_t('ui.provide_key_where_mid') }}<code>X-Provide-Key</code>{{ admin_t('ui.provide_key_where_after') }}</p>
            </div>

            <h3>{{ admin_t('ui.api_give_url') }}</h3>
            <p class="muted field-hint">{{ admin_t('ui.api_give_hint_before') }}<code>key</code>{{ admin_t('ui.api_give_hint_after') }}</p>
            <label for="api_provide_url">{{ admin_t('ui.label_api_url') }}</label>
            <div class="field-inline">
                <input id="api_provide_url" type="text" value="{{ $provideUrl }}" readonly>
                <button type="button" class="btn btn-muted" id="api-copy-url">{{ admin_t('ui.copy') }}</button>
            </div>

            <h3>{{ admin_t('ui.api_how') }}</h3>
            <p class="muted field-hint">{{ admin_t('ui.api_how_hint') }}</p>
            <div class="rewrite-examples api-examples">
                <div class="rewrite-ex">
                    <span>{{ admin_t('ui.api_ex_list') }}</span>
                    <code class="js-api-ex" data-query="ac=list"></code>
                    <button type="button" class="btn-link js-api-copy-ex">{{ admin_t('ui.copy') }}</button>
                </div>
                <div class="rewrite-ex">
                    <span>{{ admin_t('ui.api_ex_detail') }}</span>
                    <code class="js-api-ex" data-query="ac=detail&amp;ids=1"></code>
                    <button type="button" class="btn-link js-api-copy-ex">{{ admin_t('ui.copy') }}</button>
                </div>
                <div class="rewrite-ex">
                    <span>XML</span>
                    <code class="js-api-ex" data-query="ac=list&amp;at=xml"></code>
                    <button type="button" class="btn-link js-api-copy-ex">{{ admin_t('ui.copy') }}</button>
                </div>
            </div>
            <div class="interface-howto">
                <p>{{ admin_t('ui.api_params_title') }}</p>
                <ul>
                    <li><code>ac=list</code> {{ admin_t('ui.api_param_list') }}<code>ac=detail&amp;ids=1,2</code> {{ admin_t('ui.api_param_detail') }}</li>
                    <li><code>t</code> {{ admin_t('ui.api_param_t') }}<code>wd</code> {{ admin_t('ui.api_param_wd') }}<code>pg</code> {{ admin_t('ui.api_param_pg') }}<code>h</code> {{ admin_t('ui.api_param_h') }}</li>
                    <li><code>at=xml</code> {{ admin_t('ui.api_param_xml') }}</li>
                </ul>
                <p class="muted">{{ admin_t('ui.api_ok_before') }}<code>code: 1</code>{{ admin_t('ui.api_ok_mid') }}<a href="/admin/video/apidoc">{{ admin_t('ui.apidoc_short') }}</a>{{ admin_t('ui.api_ok_after') }}</p>
            </div>

            <details class="settings-details">
                <summary>{{ admin_t('ui.api_app_key_title') }}</summary>
                <p class="muted field-hint">{{ admin_t('ui.api_app_key_hint_before') }}<code>{{ $appUrl }}</code>{{ admin_t('ui.api_app_key_hint_after') }}@if($appKeySet) {{ admin_t('ui.api_app_key_set') }}@endif</p>
                <label for="app_key">{{ admin_t('ui.api_app_key') }}</label>
                <input id="app_key" type="text" name="app_key" value="{{ $appKey }}" autocomplete="off" spellcheck="false" placeholder="{{ admin_t('ui.ph_app_key') }}">
            </details>

            <div class="form-actions settings-save">
                <button type="button" class="btn" id="site-save">{{ admin_t('ui.save') }}</button>
                <button type="button" class="btn btn-muted" id="api-probe">{{ admin_t('ui.api_probe') }}</button>
            </div>
            <p class="muted field-hint">{{ admin_t('ui.api_probe_hint') }}</p>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = window.AdminUi;
    var L = @json($jsLang, JSON_UNESCAPED_UNICODE);
    var provideUrl = @json($provideUrl, JSON_UNESCAPED_UNICODE);
    var originalKey = @json($key, JSON_UNESCAPED_UNICODE);
    var originalApp = @json($appKey, JSON_UNESCAPED_UNICODE);
    var keyInput = document.getElementById('provide_key');
    var appInput = document.getElementById('app_key');
    var wrap = document.getElementById('api-key-wrap');
    var modes = document.getElementById('api-modes');
    var modeKey = {{ $hasKey ? 'true' : 'false' }};

    function copyText(text, okMsg) {
        text = String(text || '');
        if (!text) { U && U.toast(L.nothing_to_copy, 'err'); return; }
        var done = function () { U && U.toast(okMsg || L.copied, 'ok'); };
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
        try { document.execCommand('copy'); done(); } catch (err) { U && U.toast(L.copy_failed, 'err'); }
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
            U && U.toast(L.gen_save, 'ok');
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
            U && U.toast(L.gen_save, 'ok');
        });
    }
    if (keyInput) keyInput.addEventListener('input', fillExamples);
    fillExamples();

    document.getElementById('api-copy-url') && document.getElementById('api-copy-url').addEventListener('click', function () {
        copyText(withKey(provideUrl), currentKey() && currentKey() !== originalKey ? L.copied_key_unsaved : L.copied_url);
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
            U && U.toast(L.no_change_save, 'ok');
            return;
        }
        U.post('/admin/video/settings', data).then(function (res) {
            U.toast((res && res.msg) || L.finished, res && res.code === 0 ? 'ok' : 'err');
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
                U && U.toast(L.probe_unsaved, 'ok');
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
                    U.toast(n > 0 ? String(L.probe_ok_n || '').replace(':n', String(n)) : L.probe_ok_empty, 'ok');
                    return;
                }
                U.toast(json.msg || ('HTTP ' + res.http), 'err');
            }).catch(function () {
                U.loading(false);
                U.toast(L.probe_fail, 'err');
            });
        });
    }
})();
</script>
@endpush
