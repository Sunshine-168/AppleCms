@extends('admin.layouts.inner')
@section('title', admin_t('page.config_ai'))

@php
    $s = $site ?? [];
    $hasKey = (bool) ($has_key ?? false);
    $keyTail = (string) ($key_tail ?? '');
    $emptyN = (int) ($empty_n ?? 0);
    $emptySeoN = (int) ($empty_seo_n ?? 0);
    $kind = (string) ($provider_kind ?? '');
    $provider = trim((string) ($s['ai_provider'] ?? ''));
    $model = trim((string) ($s['ai_model'] ?? ''));
    $ready = $hasKey && $provider !== '';
    $jsLang = [
        'hint_openai' => admin_t('ui.ai_hint_openai'),
        'hint_qwen' => admin_t('ui.ai_hint_qwen'),
        'hint_ernie' => admin_t('ui.ai_hint_ernie'),
        'hint_deepseek' => admin_t('ui.ai_hint_deepseek'),
        'hint_other' => admin_t('ui.ai_hint_other'),
        'need_try_title' => admin_t('ui.ai_need_try_title'),
        'finished' => admin_t('ui.finished'),
        'gen_fail' => admin_t('ui.ai_gen_fail'),
        'endpoint_known' => admin_t('ui.ai_endpoint_known'),
        'endpoint_need' => admin_t('ui.ai_endpoint_need'),
        'endpoint_empty' => admin_t('ui.ai_endpoint_hint'),
        'ph_endpoint_known' => admin_t('ui.ph_ai_endpoint_known'),
        'ph_endpoint_need' => admin_t('ui.ph_ai_endpoint'),
    ];
    $defaultEndpoints = [
        'openai' => 'https://api.openai.com/v1/chat/completions',
        'deepseek' => 'https://api.deepseek.com/v1/chat/completions',
        'qwen' => 'https://dashscope.aliyuncs.com/compatible-mode/v1/chat/completions',
        'ernie' => 'https://qianfan.baidubce.com/v2/chat/completions',
    ];
    $defaultUrl = (string) ($defaultEndpoints[$kind] ?? '');
    if ($defaultUrl !== '') {
        $endpointPh = admin_t('ui.ph_ai_endpoint_known', ['url' => $defaultUrl]);
        $endpointHint = admin_t('ui.ai_endpoint_known', ['url' => $defaultUrl]);
    } elseif ($kind === 'other') {
        $endpointPh = admin_t('ui.ph_ai_endpoint');
        $endpointHint = admin_t('ui.ai_endpoint_need');
    } else {
        $endpointPh = admin_t('ui.ph_ai_endpoint');
        $endpointHint = admin_t('ui.ai_endpoint_hint');
    }
@endphp

@section('plain')
<div class="card card-panel ai-config-index">
    <div class="card-header">
        <span>{{ admin_t('ui.config_ai') }}</span>
        <div>
            <a class="btn btn-muted btn-sm" href="/admin/video">{{ admin_t('ui.video_list') }}</a>
            <a class="btn btn-muted btn-sm" href="/admin/video?empty_content=1">{{ admin_t('ui.no_intro') }}@if($emptyN > 0) · {{ $emptyN }}@endif</a>
            <a class="btn btn-muted btn-sm" href="/admin/video?empty_seo=1">{{ admin_t('ui.no_seo') }}@if($emptySeoN > 0) · {{ $emptySeoN }}@endif</a>
            <a class="btn btn-muted btn-sm" href="/admin/plugins">{{ admin_t('ui.plugins') }}</a>
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">{{ admin_t('ui.ai_lead') }}</p>

        <div class="ai-stock">
            @if($ready)
                <span class="badge badge-ok">{{ admin_t('ui.ai_key_stored') }}</span>
            @else
                <span class="badge">{{ admin_t('ui.ai_no_key') }}</span>
            @endif
            @if($hasKey && $keyTail !== '')
                <span class="muted">{{ admin_t('ui.ai_key_tail', ['tail' => $keyTail]) }}</span>
            @endif
            @if($provider !== '')
                <span class="muted">{{ $provider }}@if($model !== '') · {{ $model }}@endif</span>
            @endif
        </div>

        <form class="admin-form settings-page ai-config-form" id="site-form">
            <h3>{{ admin_t('ui.ai_provider') }}</h3>
            <input type="hidden" name="ai_provider" id="ai_provider" value="{{ $provider }}">
            <div class="ingest-modes ai-providers" id="ai-providers">
                <button type="button" class="ingest-mode{{ $kind === 'openai' ? ' is-on' : '' }}" data-value="openai">
                    <strong>OpenAI</strong>
                    <span>{{ admin_t('ui.ai_openai_hint') }}</span>
                </button>
                <button type="button" class="ingest-mode{{ $kind === 'deepseek' ? ' is-on' : '' }}" data-value="DeepSeek">
                    <strong>DeepSeek</strong>
                    <span>{{ admin_t('ui.ai_deepseek_hint') }}</span>
                </button>
                <button type="button" class="ingest-mode{{ $kind === 'qwen' ? ' is-on' : '' }}" data-value="通义">
                    <strong>{{ admin_t('ui.ai_qwen') }}</strong>
                    <span>{{ admin_t('ui.ai_qwen_hint') }}</span>
                </button>
                <button type="button" class="ingest-mode{{ $kind === 'ernie' ? ' is-on' : '' }}" data-value="文心">
                    <strong>{{ admin_t('ui.ai_ernie') }}</strong>
                    <span>{{ admin_t('ui.ai_ernie_hint') }}</span>
                </button>
                <button type="button" class="ingest-mode{{ $kind === 'other' ? ' is-on' : '' }}" data-value="other">
                    <strong>{{ admin_t('ui.other') }}</strong>
                    <span>{{ admin_t('ui.ai_other_hint') }}</span>
                </button>
            </div>
            <div id="ai-provider-custom" @if($kind !== 'other') hidden @endif>
                <label for="ai_provider_custom">{{ admin_t('ui.ai_provider_name') }}</label>
                <input id="ai_provider_custom" type="text" value="{{ $kind === 'other' ? $provider : '' }}" placeholder="{{ admin_t('ui.ph_ai_provider') }}" autocomplete="off">
            </div>

            <h3>{{ admin_t('ui.secret_key') }}</h3>
            @if($hasKey)
                <p class="muted field-hint"><span class="badge badge-ok">{{ admin_t('ui.saved') }}</span> {{ admin_t('ui.ai_key_keep_hint') }}</p>
            @else
                <p class="muted field-hint"><span class="badge">{{ admin_t('ui.ai_no_key') }}</span> {{ admin_t('ui.ai_key_miss_hint') }}</p>
            @endif
            <label for="ai_key">API Key</label>
            <input id="ai_key" type="password" name="ai_key" value="" autocomplete="new-password" spellcheck="false" placeholder="{{ $hasKey ? admin_t('ui.ph_ai_key_keep') : admin_t('ui.ph_ai_key_new') }}">

            <h3>{{ admin_t('ui.ai_model_title') }}</h3>
            <label for="ai_model">{{ admin_t('ui.ai_model_name') }}</label>
            <input id="ai_model" type="text" name="ai_model" value="{{ $model }}" placeholder="{{ admin_t('ui.ph_ai_model') }}" autocomplete="off" spellcheck="false">
            <p class="muted field-hint" id="ai-model-hint">{{ admin_t('ui.ai_model_hint') }}</p>

            <h3>{{ admin_t('ui.ai_compat') }}</h3>
            <label for="ai_endpoint">{{ admin_t('ui.label_api_url') }}</label>
            <input id="ai_endpoint" type="text" name="ai_endpoint" value="{{ $s['ai_endpoint'] ?? '' }}" placeholder="{{ $endpointPh }}" autocomplete="off" spellcheck="false">
            <p class="muted field-hint" id="ai-endpoint-hint">{{ $endpointHint }}</p>

            <div class="hub-result ai-result">
                <label for="ai-try-title">{{ admin_t('ui.ai_try_title') }}</label>
                <input id="ai-try-title" type="text" placeholder="{{ admin_t('ui.ph_ai_try_title') }}" autocomplete="off">
                <p>
                    <button type="button" class="btn btn-muted" id="ai-try-btn">{{ admin_t('ui.ai_try_btn') }}</button>
                    <button type="button" class="btn btn-muted" id="ai-try-seo-btn">{{ admin_t('ui.ai_seo_try_btn') }}</button>
                </p>
                <pre class="out" id="ai-try-out" hidden></pre>
                <p class="muted">{{ admin_t('ui.ai_try_note') }}</p>
            </div>

            <div class="form-actions settings-save">
                <button type="button" class="btn" id="site-save">{{ admin_t('ui.save') }}</button>
                <a class="btn btn-muted" href="/admin/video?empty_content=1">{{ admin_t('ui.ai_go_empty') }}</a>
                <a class="btn btn-muted" href="/admin/video?empty_seo=1">{{ admin_t('ui.ai_go_empty_seo') }}</a>
            </div>
        </form>
    </div>
</div>
@endsection

@include('admin.partials.site-save')

@push('scripts')
<script>
(function () {
    var L = @json($jsLang, JSON_UNESCAPED_UNICODE);
    var hidden = document.getElementById('ai_provider');
    var customWrap = document.getElementById('ai-provider-custom');
    var customInput = document.getElementById('ai_provider_custom');
    var modelHint = document.getElementById('ai-model-hint');
    var endpointInput = document.getElementById('ai_endpoint');
    var endpointHint = document.getElementById('ai-endpoint-hint');
    var endpoints = {
        openai: 'https://api.openai.com/v1/chat/completions',
        DeepSeek: 'https://api.deepseek.com/v1/chat/completions',
        '通义': 'https://dashscope.aliyuncs.com/compatible-mode/v1/chat/completions',
        '文心': 'https://qianfan.baidubce.com/v2/chat/completions',
        other: ''
    };
    function fillUrl(s, url) {
        return String(s || '').replace(':url', url || '');
    }
    function syncEndpoint(kind) {
        var url = endpoints[kind] || '';
        if (endpointInput) {
            endpointInput.placeholder = url
                ? fillUrl(L.ph_endpoint_known, url)
                : (L.ph_endpoint_need || '');
        }
        if (endpointHint) {
            if (url) {
                endpointHint.textContent = fillUrl(L.endpoint_known, url);
            } else if (kind === 'other') {
                endpointHint.textContent = L.endpoint_need || '';
            } else {
                endpointHint.textContent = L.endpoint_empty || L.endpoint_need || '';
            }
        }
    }
    var hints = {
        openai: L.hint_openai,
        DeepSeek: L.hint_deepseek,
        '通义': L.hint_qwen,
        '文心': L.hint_ernie,
        other: L.hint_other
    };
    function currentKind() {
        var v = String((hidden && hidden.value) || '').trim();
        if (!v) return '';
        var low = v.toLowerCase();
        if (low === 'openai' || low.indexOf('openai') >= 0) return 'openai';
        if (low === 'deepseek' || low.indexOf('deepseek') >= 0) return 'DeepSeek';
        if (v.indexOf('通义') >= 0 || low === 'qwen' || low === 'tongyi') return '通义';
        if (v.indexOf('文心') >= 0 || low === 'ernie' || low === 'wenxin') return '文心';
        return 'other';
    }
    function mark(value) {
        var kind = value || currentKind() || '';
        document.querySelectorAll('#ai-providers .ingest-mode').forEach(function (btn) {
            var v = btn.getAttribute('data-value') || '';
            btn.classList.toggle('is-on', v === kind || (kind === 'other' && v === 'other'));
        });
        if (customWrap) customWrap.hidden = kind !== 'other';
        if (modelHint) modelHint.textContent = hints[kind] || hints.other;
        syncEndpoint(kind);
    }
    function setProvider(value) {
        if (value === 'other') {
            if (customWrap) customWrap.hidden = false;
            if (customInput && !String(customInput.value || '').trim()) customInput.focus();
            if (hidden) hidden.value = String((customInput && customInput.value) || '').trim();
            document.querySelectorAll('#ai-providers .ingest-mode').forEach(function (btn) {
                btn.classList.toggle('is-on', btn.getAttribute('data-value') === 'other');
            });
            if (modelHint) modelHint.textContent = hints.other;
            syncEndpoint('other');
            return;
        }
        if (hidden) hidden.value = value;
        if (customWrap) customWrap.hidden = true;
        mark(value);
    }
    document.querySelectorAll('#ai-providers .ingest-mode').forEach(function (btn) {
        btn.addEventListener('click', function () {
            setProvider(btn.getAttribute('data-value') || '');
        });
    });
    if (customInput) {
        customInput.addEventListener('input', function () {
            if (hidden) hidden.value = String(customInput.value || '').trim();
        });
    }
    mark(currentKind() || '');
    var tryOut = document.getElementById('ai-try-out');
    function tryWrite(url, asSeo) {
        var title = String((document.getElementById('ai-try-title') || {}).value || '').trim();
        if (!title) { AdminUi.toast(L.need_try_title, 'err'); return; }
        AdminUi.loading(true);
        AdminUi.post(url, {title: title}).then(function (res) {
            AdminUi.loading(false);
            if (tryOut) {
                tryOut.hidden = false;
                if (asSeo && res && res.data) {
                    tryOut.textContent = [res.data.title, res.data.keywords, res.data.description].filter(Boolean).join('\n') || ((res && res.msg) || '');
                } else {
                    tryOut.textContent = (res && res.data && res.data.text) ? res.data.text : ((res && res.msg) || '');
                }
            }
            AdminUi.toast((res && res.msg) || L.finished, res && res.code === 0 ? 'ok' : 'err');
        }).catch(function () {
            AdminUi.loading(false);
            AdminUi.toast(L.gen_fail, 'err');
        });
    }
    if (typeof AdminUi !== 'undefined') {
        var tryBtn = document.getElementById('ai-try-btn');
        var trySeo = document.getElementById('ai-try-seo-btn');
        if (tryBtn) tryBtn.addEventListener('click', function () { tryWrite('/admin/video/ai/generate', false); });
        if (trySeo) trySeo.addEventListener('click', function () { tryWrite('/admin/video/ai/seo', true); });
    }
})();
</script>
@endpush
