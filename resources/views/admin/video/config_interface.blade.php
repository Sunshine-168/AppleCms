@extends('admin.layouts.inner')
@section('title', admin_t('page.config_interface'))

@php
    $s = $site ?? [];
    $key = trim((string) ($s['inbound_key'] ?? ''));
    $hasKey = $key !== '';
    $receiveUrl = (string) ($receiveUrl ?? url('/api/receive/vod'));
    $jsLang = [
        'nothing_to_copy' => admin_t('ui.nothing_to_copy'),
        'copied' => admin_t('ui.copied'),
        'copy_failed' => admin_t('ui.copy_failed'),
        'copied_url' => admin_t('ui.copied_url'),
        'gen_save' => admin_t('ui.gen_save'),
    ];
@endphp

@section('plain')
<div class="card card-panel interface-config-index">
    <div class="card-header">
        <span>{{ admin_t('ui.config_interface') }}</span>
        <div>
            <a class="btn btn-muted btn-sm" href="/admin/video/config/collect">{{ admin_t('ui.config_collect') }}</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/config/api">{{ admin_t('ui.config_api') }}</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/apidoc">{{ admin_t('ui.apidoc_short') }}</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/collect_temps">{{ admin_t('ui.collect_temps') }}</a>
        </div>
    </div>
    <div class="card-body">
            <p class="muted recycle-lead">{{ admin_t('ui.inbound_lead_before') }}<a href="/admin/video/collects">{{ admin_t('ui.collects') }}</a>{{ admin_t('ui.inbound_lead_mid') }}<a href="/admin/video/config/collect">{{ admin_t('ui.config_collect') }}</a>{{ admin_t('ui.inbound_lead_after') }}</p>
        <form class="admin-form settings-page interface-config-form" id="site-form">
            <h3>{{ admin_t('ui.inbound_url_title') }}</h3>
            <p class="muted field-hint">{{ admin_t('ui.inbound_url_hint_before') }}<code>/api/receive/vod</code>@if($mangaReady ?? false){{ admin_t('ui.inbound_url_hint_manga') }}<code>/api/receive/manga</code>@endif{{ admin_t('ui.inbound_url_hint_after') }}</p>
            <label for="inbound_receive_url">{{ admin_t('ui.inbound_vod_url') }}</label>
            <div class="field-inline">
                <input id="inbound_receive_url" type="text" value="{{ $receiveUrl }}" readonly>
                <button type="button" class="btn btn-muted" id="inbound-copy-url">{{ admin_t('ui.copy') }}</button>
            </div>
            @if($mangaReady ?? false)
                <label for="inbound_receive_manga_url">{{ admin_t('ui.inbound_manga_url') }}</label>
                <div class="field-inline">
                    <input id="inbound_receive_manga_url" type="text" value="{{ $receiveMangaUrl ?? url('/api/receive/manga') }}" readonly>
                    <button type="button" class="btn btn-muted" id="inbound-copy-manga-url">{{ admin_t('ui.copy') }}</button>
                </div>
            @endif

            <h3>{{ admin_t('ui.inbound_key_title') }}</h3>
            @if($hasKey)
                <p class="muted field-hint"><span class="badge badge-ok">{{ admin_t('ui.key_set') }}</span> {{ admin_t('ui.inbound_key_set_hint') }}</p>
            @else
                <p class="muted field-hint"><span class="badge badge-warn">{{ admin_t('ui.key_missing') }}</span> {{ admin_t('ui.inbound_key_miss_hint') }}</p>
            @endif
            <label for="inbound_key">{{ admin_t('ui.secret_key') }}</label>
            <div class="field-inline">
                <input id="inbound_key" type="text" name="inbound_key" value="{{ $s['inbound_key'] ?? '' }}" autocomplete="off" spellcheck="false" placeholder="{{ admin_t('ui.ph_inbound_key') }}">
                <button type="button" class="btn btn-muted" id="inbound-gen-key">{{ admin_t('ui.generate') }}</button>
            </div>
            <p class="muted field-hint">{{ admin_t('ui.inbound_key_where_before') }}<code>key</code>{{ admin_t('ui.inbound_key_where_mid') }}<code>X-Inbound-Key</code>{{ admin_t('ui.inbound_key_where_after') }}</p>

            <h3>{{ admin_t('ui.inbound_how') }}</h3>
            <div class="interface-howto">
                <p>{{ admin_t('ui.inbound_how_lead') }}</p>
                <ul>
                    <li><code>key</code> {{ admin_t('ui.secret_key') }}</li>
                    <li><code>vod_name</code> {{ admin_t('ui.inbound_vod_name') }}</li>
                    <li><code>vod_pic</code> {{ admin_t('ui.cover') }}</li>
                    <li><code>type_id</code> {{ admin_t('ui.inbound_type_id') }}</li>
                    <li>{{ admin_t('ui.inbound_data_before') }}<code>data</code>{{ admin_t('ui.inbound_data_after') }}</li>
                </ul>
                @if($mangaReady ?? false)
                    <p>{{ admin_t('ui.inbound_manga_how_before') }}<code>###</code>{{ admin_t('ui.inbound_manga_how_after') }}</p>
                    <ul>
                        <li><code>manga_name</code> {{ admin_t('ui.inbound_work_name') }}</li>
                        <li><code>manga_id</code> {{ admin_t('ui.inbound_manga_id') }}</li>
                        <li><code>type_id</code> {{ admin_t('ui.inbound_manga_type_id') }}</li>
                        <li><code>manga_pic</code> / <code>manga_author</code> / <code>manga_content</code></li>
                        <li><code>chapters</code> {{ admin_t('ui.inbound_chapters') }}<code>name</code>{{ admin_t('ui.list_sep') }}<code>pics</code></li>
                        <li>{{ admin_t('ui.inbound_or') }} <code>chapter_name</code> + <code>images</code>{{ admin_t('ui.inbound_img_ok') }}</li>
                    </ul>
                @endif
                <p class="muted">{{ admin_t('ui.inbound_result_before') }}<code>code: 0</code>{{ admin_t('ui.inbound_result_after') }}</p>
            </div>

            <div class="form-actions settings-save">
                <button type="button" class="btn" id="site-save">{{ admin_t('ui.save') }}</button>
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
    var L = @json($jsLang, JSON_UNESCAPED_UNICODE);
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
    function bindCopy(btnId, inputId, msg) {
        var btn = document.getElementById(btnId);
        var input = document.getElementById(inputId);
        if (!btn || !input) return;
        btn.addEventListener('click', function () { copyText(input.value, msg); });
    }
    bindCopy('inbound-copy-url', 'inbound_receive_url', L.copied_url);
    bindCopy('inbound-copy-manga-url', 'inbound_receive_manga_url', L.copied_url);
    var genBtn = document.getElementById('inbound-gen-key');
    var keyInput = document.getElementById('inbound_key');
    if (genBtn && keyInput) {
        genBtn.addEventListener('click', function () {
            var chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
            var out = '';
            for (var i = 0; i < 24; i++) out += chars.charAt(Math.floor(Math.random() * chars.length));
            keyInput.value = out;
            U && U.toast(L.gen_save, 'ok');
        });
    }
})();
</script>
@endpush
