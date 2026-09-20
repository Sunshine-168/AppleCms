@extends('admin.layouts.inner')
@section('title', admin_t('ui.full_text_search'))

@php
    $status = $status ?? [];
    $options = $options ?? [];
    $scoutJsLang = [
        'save_fail' => admin_t('ui.save_fail'),
        'saved' => admin_t('ui.saved'),
        'confirm_rebuild' => admin_t('ui.confirm_rebuild'),
        'rebuild_fail' => admin_t('ui.rebuild_fail'),
        'rebuilt' => admin_t('ui.rebuilt'),
        'rebuilt_counts' => admin_t('ui.rebuilt_counts'),
    ];
@endphp

@section('plain')
<div class="card card-panel desk-board" id="scout-board">
    <div class="card-header">
        <span>{{ admin_t('ui.full_text_search') }} <em>Laravel Scout</em></span>
        <div>
            <a class="btn btn-muted btn-sm" href="/admin/video/config/scout">{{ admin_t('ui.config_page') }}</a>
            <button type="button" class="btn btn-sm" id="scout-sync-btn">{{ admin_t('ui.rebuild_index') }}</button>
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">{{ admin_t('ui.scout_lead') }}</p>

        <div class="stat-grid dash" style="margin:0 0 16px">
            <div class="stat-card">
                <div class="stat-label">{{ admin_t('ui.status') }}</div>
                <div class="stat-value">{{ !empty($status['search_enabled']) ? admin_t('ui.already_on') : admin_t('ui.not_enabled') }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">{{ admin_t('ui.driver') }}</div>
                <div class="stat-value">{{ $status['driver'] ?? 'database' }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">{{ admin_t('ui.videos') }}</div>
                <div class="stat-value">{{ (int) ($status['video_count'] ?? 0) }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">{{ admin_t('nav.arts') }}</div>
                <div class="stat-value">{{ (int) ($status['art_count'] ?? 0) }}</div>
            </div>
        </div>

        <form class="admin-form settings-page" id="scout-form" style="max-width:560px">
            <input type="hidden" name="desk" value="settings">
            <label>{{ admin_t('ui.enable_scout') }}</label>
            <select name="scout_search_enabled">
                <option value="0" @selected(($options['scout_search_enabled'] ?? '1') === '0')>{{ admin_t('ui.no_like') }}</option>
                <option value="1" @selected(($options['scout_search_enabled'] ?? '1') === '1')>{{ admin_t('ui.yes') }}</option>
            </select>
            <label>{{ admin_t('ui.driver') }}</label>
            <select name="scout_driver">
                <option value="database" @selected(($options['scout_driver'] ?? '') === 'database')>{{ admin_t('ui.driver_db') }}</option>
                <option value="collection" @selected(($options['scout_driver'] ?? '') === 'collection')>{{ admin_t('ui.driver_mem') }}</option>
                <option value="meilisearch" @selected(($options['scout_driver'] ?? '') === 'meilisearch')>meilisearch</option>
            </select>
            <label>{{ admin_t('ui.meili_host') }}</label>
            <input type="text" name="scout_meili_host" value="{{ $options['scout_meili_host'] ?? '' }}" placeholder="http://127.0.0.1:7700">
            <label>{{ admin_t('ui.meili_key') }}</label>
            <input type="text" name="scout_meili_key" value="" placeholder="{{ !empty($options['scout_meili_key_set']) ? admin_t('ui.saved_blank') : admin_t('ui.optional') }}">
            <p class="muted field-hint">{{ admin_t('ui.scout_sync_hint') }} <code>php artisan scout:site-sync</code></p>
            <p><button type="button" class="btn btn-sm" id="scout-save-btn">{{ admin_t('ui.save') }}</button></p>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($scoutJsLang, JSON_UNESCAPED_UNICODE);
    U.on('#scout-save-btn', 'click', function () {
        var data = U.formData(document.getElementById('scout-form'));
        data.action = 'settings';
        U.loading(true);
        U.post('/admin/video/scout/save', data).then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.save_fail, 'err'); return; }
            U.toast(L.saved, 'ok');
        }).catch(function () { U.loading(false); U.toast(L.save_fail, 'err'); });
    });
    U.on('#scout-sync-btn', 'click', function () {
        if (!U.confirm(L.confirm_rebuild)) return;
        U.loading(true);
        U.post('/admin/video/scout/sync', {}).then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.rebuild_fail, 'err'); return; }
            var extra = '';
            if (res.data) {
                extra = ' · ' + String(L.rebuilt_counts || '')
                    .replace(':videos', String(res.data.videos || 0))
                    .replace(':arts', String(res.data.arts || 0));
            }
            U.toast((res.msg || L.rebuilt) + extra, 'ok');
        }).catch(function () { U.loading(false); U.toast(L.rebuild_fail, 'err'); });
    });
})();
</script>
@endpush
