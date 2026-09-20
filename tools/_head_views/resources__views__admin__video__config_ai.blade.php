fatal: path 'resources\views\admin\video\config_ai.blade.php' exists on disk, but not in 'HEAD'
@extends('admin.layouts.inner')
@section('title', admin_t('page.config_ai'))

@php
    $s = $site ?? [];
    $hasKey = (bool) ($has_key ?? false);
    $keyTail = (string) ($key_tail ?? '');
    $emptyN = (int) ($empty_n ?? 0);
    $kind = (string) ($provider_kind ?? '');
    $provider = trim((string) ($s['ai_provider'] ?? ''));
    $model = trim((string) ($s['ai_model'] ?? ''));
    $ready = $hasKey && $provider !== '';
@endphp

@section('plain')
<div class="card card-panel ai-config-index">
    <div class="card-header">
        <span>AI 写内容</span>
        <div>
            <a class="btn btn-muted btn-sm" href="/admin/video">影片列表</a>
            <a class="btn btn-muted btn-sm" href="/admin/video?empty_content=1">无简介@if($emptyN > 0) · {{ $emptyN }}@endif</a>
            <a class="btn btn-muted btn-sm" href="/admin/plugins">插件</a>
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">填写密钥后保存，可请求模型写简介。试写或到影片里生成只填文本框，没配密钥会失败。</p>

        <div class="ai-stock">
            @if($ready)
                <span class="badge badge-ok">已存密钥</span>
            @else
                <span class="badge">还没密钥</span>
            @endif
            @if($hasKey && $keyTail !== '')
                <span class="muted">尾号 {{ $keyTail }}</span>
            @endif
            @if($provider !== '')
                <span class="muted">{{ $provider }}@if($model !== '') · {{ $model }}@endif</span>
            @endif
        </div>

        <form class="admin-form settings-page ai-config-form" id="site-form">
            <h3>服务商</h3>
            <input type="hidden" name="ai_provider" id="ai_provider" value="{{ $provider }}">
            <div class="ingest-modes ai-providers" id="ai-providers">
                <button type="button" class="ingest-mode{{ $kind === 'openai' ? ' is-on' : '' }}" data-value="openai">
                    <strong>OpenAI</strong>
                    <span>国际接口，模型如 gpt-4.1-mini。</span>
                </button>
                <button type="button" class="ingest-mode{{ $kind === 'qwen' ? ' is-on' : '' }}" data-value="通义">
                    <strong>通义</strong>
                    <span>阿里云百炼 / DashScope，模型如 qwen-plus。</span>
                </button>
                <button type="button" class="ingest-mode{{ $kind === 'ernie' ? ' is-on' : '' }}" data-value="文心">
                    <strong>文心</strong>
                    <span>百度千帆，模型如 ernie-4.0-8k。</span>
                </button>
                <button type="button" class="ingest-mode{{ $kind === 'other' ? ' is-on' : '' }}" data-value="other">
                    <strong>其他</strong>
                    <span>自己写服务商名字，并填写兼容接口。</span>
                </button>
            </div>
            <div id="ai-provider-custom" @if($kind !== 'other') hidden @endif>
                <label for="ai_provider_custom">服务商名称</label>
                <input id="ai_provider_custom" type="text" value="{{ $kind === 'other' ? $provider : '' }}" placeholder="如 DeepSeek、Kimi" autocomplete="off">
            </div>

            <h3>密钥</h3>
            @if($hasKey)
                <p class="muted field-hint"><span class="badge badge-ok">已保存</span> 留空再保存则保持原密钥。填新的会覆盖。</p>
            @else
                <p class="muted field-hint"><span class="badge">还没密钥</span> 没有密钥时生成会直接失败。</p>
            @endif
            <label for="ai_key">API Key</label>
            <input id="ai_key" type="password" name="ai_key" value="" autocomplete="new-password" spellcheck="false" placeholder="{{ $hasKey ? '留空则保留现有密钥' : 'sk-… 只存在本站' }}">

            <h3>模型</h3>
            <label for="ai_model">模型名</label>
            <input id="ai_model" type="text" name="ai_model" value="{{ $model }}" placeholder="如 gpt-4.1-mini" autocomplete="off" spellcheck="false">
            <p class="muted field-hint" id="ai-model-hint">请求模型时会带上这个名字。</p>

            <h3>兼容接口</h3>
            <label for="ai_endpoint">接口地址</label>
            <input id="ai_endpoint" type="text" name="ai_endpoint" value="{{ $s['ai_endpoint'] ?? '' }}" placeholder="可空。其他服务商填 /v1/chat/completions" autocomplete="off" spellcheck="false">
            <p class="muted field-hint">OpenAI / 通义 / 文心有默认地址。其他必须填。</p>

            <div class="hub-result ai-result">
                <label for="ai-try-title">试写标题</label>
                <input id="ai-try-title" type="text" placeholder="如 影片名" autocomplete="off">
                <p><button type="button" class="btn btn-muted" id="ai-try-btn">试写一段</button></p>
                <pre class="out" id="ai-try-out" hidden></pre>
                <p class="muted">试写不会改影片。无简介列表仍可手写核对。</p>
            </div>

            <div class="form-actions settings-save">
                <button type="button" class="btn" id="site-save">{{ admin_t('ui.save') }}</button>
                <a class="btn btn-muted" href="/admin/video?empty_content=1">去无简介列表</a>
            </div>
        </form>
    </div>
</div>
@endsection

@include('admin.partials.site-save')

@push('scripts')
<script>
(function () {
    var hidden = document.getElementById('ai_provider');
    var customWrap = document.getElementById('ai-provider-custom');
    var customInput = document.getElementById('ai_provider_custom');
    var modelHint = document.getElementById('ai-model-hint');
    var hints = {
        openai: 'OpenAI 常见写法：gpt-4.1-mini。',
        '通义': '通义常见写法：qwen-plus。',
        '文心': '文心常见写法：ernie-4.0-8k。',
        other: '按服务商文档填写模型名，并填兼容接口。'
    };
    function currentKind() {
        var v = String((hidden && hidden.value) || '').trim();
        if (!v) return '';
        var low = v.toLowerCase();
        if (low === 'openai' || low.indexOf('openai') >= 0) return 'openai';
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
    var tryBtn = document.getElementById('ai-try-btn');
    var tryOut = document.getElementById('ai-try-out');
    if (tryBtn && typeof AdminUi !== 'undefined') {
        tryBtn.addEventListener('click', function () {
            var title = String((document.getElementById('ai-try-title') || {}).value || '').trim();
            if (!title) { AdminUi.toast('请填写试写标题', 'err'); return; }
            AdminUi.loading(true);
            AdminUi.post('/admin/video/ai/generate', {title: title}).then(function (res) {
                AdminUi.loading(false);
                if (tryOut) {
                    tryOut.hidden = false;
                    tryOut.textContent = (res && res.data && res.data.text) ? res.data.text : ((res && res.msg) || '');
                }
                AdminUi.toast((res && res.msg) || '完成', res && res.code === 0 ? 'ok' : 'err');
            }).catch(function () {
                AdminUi.loading(false);
                AdminUi.toast('生成失败', 'err');
            });
        });
    }
})();
</script>
@endpush