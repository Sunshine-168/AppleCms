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
        <p class="muted recycle-lead">这里只存服务商和密钥。<strong>现在还没有接通模型</strong>，保存后也不会改影片简介或标题。缺简介请到影片编辑里手写。</p>
        <ol class="hub-steps">
            <li class="is-on"><em>1</em><span>填服务商和密钥</span></li>
            <li><em>2</em><span>保存</span></li>
            <li><em>3</em><span>简介去影片里手写</span></li>
        </ol>

        <div class="ai-stock">
            @if($ready)
                <span class="badge badge-ok">已存密钥</span>
            @else
                <span class="badge">还没接通</span>
            @endif
            @if($hasKey && $keyTail !== '')
                <span class="muted">尾号 {{ $keyTail }}</span>
            @endif
            @if($provider !== '')
                <span class="muted">{{ $provider }}@if($model !== '') · {{ $model }}@endif</span>
            @endif
        </div>

        <form class="settings-page ai-config-form" id="site-form">
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
                    <span>自己写服务商名字，同样只是存下来。</span>
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
                <p class="muted field-hint"><span class="badge">还没密钥</span> 现在填了也不会去请求模型，更不会改片子。</p>
            @endif
            <label for="ai_key">API Key</label>
            <input id="ai_key" type="password" name="ai_key" value="" autocomplete="new-password" spellcheck="false" placeholder="{{ $hasKey ? '留空则保留现有密钥' : 'sk-… 只存在本站' }}">

            <h3>模型</h3>
            <label for="ai_model">模型名</label>
            <input id="ai_model" type="text" name="ai_model" value="{{ $model }}" placeholder="如 gpt-4.1-mini" autocomplete="off" spellcheck="false">
            <p class="muted field-hint" id="ai-model-hint">以后接通时会用这个名字。现在填了也不调用。</p>

            <div class="hub-result ai-result">
                <div class="hub-empty">
                    <p>还不会写简介。</p>
                    <p class="muted">没有「生成」「改写」按钮。无简介的片子请去影片列表手写，或先把采集来的简介核对一遍。</p>
                </div>
            </div>

            <div class="form-actions settings-save">
                <button type="button" class="btn" id="site-save">保存</button>
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
        openai: 'OpenAI 常见写法：gpt-4.1-mini。现在填了也不调用。',
        '通义': '通义常见写法：qwen-plus。现在填了也不调用。',
        '文心': '文心常见写法：ernie-4.0-8k。现在填了也不调用。',
        other: '按服务商文档填写模型名。现在填了也不调用。'
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
})();
</script>
@endpush
