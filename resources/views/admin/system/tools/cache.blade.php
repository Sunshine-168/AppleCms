@extends('admin.layouts.inner')
@section('title', admin_t('page.cache'))

@php
    $driverLabel = (string) ($driver_label ?? '');
    $driverHint = (string) ($driver_hint ?? '');
    $data = $data ?? ['detail' => ''];
    $views = $views ?? ['detail' => ''];
    $configDetail = (string) ($config_detail ?? '');
    $packed = (bool) ($packed ?? false);
    $htmlOn = (bool) ($html_cache_on ?? false);
    $jsLang = [
        'unknown' => admin_t('ui.unknown'),
        'packed' => admin_t('ui.cache_packed'),
        'fresh' => admin_t('ui.cache_fresh'),
        'packed_hint' => admin_t('ui.cache_packed_hint'),
        'fresh_hint' => admin_t('ui.cache_fresh_hint'),
        'confirm_all' => admin_t('ui.cache_confirm_all'),
        'run_fail' => admin_t('ui.run_fail'),
        'finished' => admin_t('ui.completed'),
    ];
@endphp

@section('plain')
<div class="card card-panel cache-index">
    <div class="card-header">
        <span>{{ admin_t('ui.cache_title') }}</span>
        <div>
            <a class="btn btn-muted btn-sm" href="/admin/video/templates">{{ admin_t('ui.tpl') }}</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/make">{{ admin_t('ui.static_make') }}</a>
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">{{ admin_t('ui.cache_lead_before') }}<a href="/admin/video/make">{{ admin_t('ui.static_make') }}</a>{{ admin_t('ui.cache_lead_after') }}</p>
        <p class="cache-note{{ $htmlOn ? '' : ' is-off' }}" id="cache-html-note">{{ admin_t('ui.cache_html_note') }}</p>

        <div class="cache-block">
            <div class="cache-block-head">
                <h3>{{ admin_t('ui.cache_data') }}</h3>
                <span class="badge" id="cache-driver-badge">{{ $driverLabel !== '' ? $driverLabel : admin_t('ui.unknown') }}</span>
            </div>
            <p class="muted cache-block-detail" id="cache-data-detail">{{ $data['detail'] ?? '' }}</p>
            <p class="muted field-hint">{{ admin_t('ui.cache_data_hint') }}{{ $driverHint }}</p>
            <button type="button" class="btn btn-muted btn-sm" data-kind="data">{{ admin_t('ui.cache_clear') }}</button>
        </div>

        <div class="cache-block">
            <div class="cache-block-head">
                <h3>{{ admin_t('ui.tpl') }}</h3>
            </div>
            <p class="muted cache-block-detail" id="cache-views-detail">{{ $views['detail'] ?? '' }}</p>
            <p class="muted field-hint">{{ admin_t('ui.cache_views_hint_before') }}<a href="/admin/video/templates">{{ admin_t('ui.tpl') }}</a>{{ admin_t('ui.cache_views_hint_after') }}</p>
            <button type="button" class="btn btn-muted btn-sm" data-kind="views">{{ admin_t('ui.cache_clear') }}</button>
        </div>

        <div class="cache-block">
            <div class="cache-block-head">
                <h3>{{ admin_t('ui.cache_config') }}</h3>
                <span class="badge{{ $packed ? ' badge-off' : ' badge-ok' }}" id="cache-packed-badge">{{ $packed ? admin_t('ui.cache_packed') : admin_t('ui.cache_fresh') }}</span>
            </div>
            <p class="muted cache-block-detail" id="cache-config-detail">{{ $configDetail }}</p>
            <p class="muted field-hint" id="cache-config-hint">{{ $packed ? admin_t('ui.cache_packed_hint') : admin_t('ui.cache_fresh_hint') }}</p>
            <button type="button" class="btn btn-muted btn-sm" data-kind="config">{{ admin_t('ui.cache_unpack') }}</button>
        </div>

        <div class="cache-block cache-block-all">
            <div class="cache-block-head">
                <h3>{{ admin_t('ui.cache_stuck') }}</h3>
            </div>
            <p class="muted field-hint">{{ admin_t('ui.cache_stuck_hint') }}</p>
            <button type="button" class="btn" data-kind="all" id="cache-clear-all">{{ admin_t('ui.cache_clear_all') }}</button>
        </div>

        <details class="settings-details cache-advanced">
            <summary>{{ admin_t('ui.cache_pack_title') }}</summary>
            <p class="muted field-hint">{{ admin_t('ui.cache_pack_hint') }}</p>
            <div class="cache-pack-actions">
                <button type="button" class="btn btn-muted btn-sm" data-kind="pack-config" data-confirm="{{ admin_t('ui.cache_pack_config_confirm') }}">{{ admin_t('ui.cache_pack_config') }}</button>
                <button type="button" class="btn btn-muted btn-sm" data-kind="pack-routes" data-confirm="{{ admin_t('ui.cache_pack_routes_confirm') }}">{{ admin_t('ui.cache_pack_routes') }}</button>
                <button type="button" class="btn btn-muted btn-sm" data-kind="pack-views" data-confirm="{{ admin_t('ui.cache_pack_views_confirm') }}">{{ admin_t('ui.cache_pack_views') }}</button>
            </div>
        </details>
        <p class="muted" id="cache-flash" hidden></p>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($jsLang, JSON_UNESCAPED_UNICODE);
    var CONFIRMS = {
        all: L.confirm_all
    };
    function applyBoard(d) {
        if (!d) return;
        var driver = document.getElementById('cache-driver-badge');
        if (driver) driver.textContent = d.driver_label || L.unknown;
        var data = document.getElementById('cache-data-detail');
        if (data) data.textContent = (d.data && d.data.detail) || '';
        var views = document.getElementById('cache-views-detail');
        if (views) views.textContent = (d.views && d.views.detail) || '';
        var config = document.getElementById('cache-config-detail');
        if (config) config.textContent = d.config_detail || '';
        var badge = document.getElementById('cache-packed-badge');
        if (badge) {
            badge.textContent = d.packed ? L.packed : L.fresh;
            badge.classList.toggle('badge-off', !!d.packed);
            badge.classList.toggle('badge-ok', !d.packed);
        }
        var hint = document.getElementById('cache-config-hint');
        if (hint) hint.textContent = d.packed ? L.packed_hint : L.fresh_hint;
        var note = document.getElementById('cache-html-note');
        if (note) note.classList.toggle('is-off', !d.html_cache_on);
    }
    function runKind(kind, confirmMsg) {
        if (confirmMsg && !U.confirm(confirmMsg)) return;
        U.loading(true);
        U.post('/admin/system/tools/cache/clear', {kind: kind}).then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.run_fail, 'err'); return; }
            U.toast((res && res.msg) || L.finished, 'ok');
            applyBoard(res.data || {});
            var flash = document.getElementById('cache-flash');
            if (flash) {
                flash.hidden = false;
                flash.textContent = (res && res.msg) || '';
            }
        }).catch(function () {
            U.loading(false);
            U.toast(L.run_fail, 'err');
        });
    }
    U.qa('[data-kind]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var kind = btn.getAttribute('data-kind') || '';
            var msg = btn.getAttribute('data-confirm') || CONFIRMS[kind] || '';
            runKind(kind, msg);
        });
    });
})();
</script>
@endpush
