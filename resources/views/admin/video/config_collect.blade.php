@extends('admin.layouts.inner')
@section('title', admin_t('page.config_collect'))

@php
    $s = $site ?? [];
    $on = fn (string $k, string $d = '0') => (string) ($s[$k] ?? $d) === '1';
    $toTemp = $on('collect_to_temp');
@endphp

@section('plain')
<div class="card card-panel collect-config-index">
    <div class="card-header">
        <span>{{ admin_t('ui.config_collect') }}</span>
        <div>
            <a class="btn btn-muted btn-sm" href="/admin/video/collects">{{ admin_t('ui.collects') }}</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/collect_temps">{{ admin_t('ui.collect_temps') }}</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/audits">{{ admin_t('ui.audits') }}</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/config/interface">{{ admin_t('ui.config_interface') }}</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/tools/hub">{{ admin_t('ui.try_api') }}</a>
        </div>
    </div>
    <div class="card-body">
            <p class="muted recycle-lead">{{ admin_t('ui.collect_cfg_lead_before') }}<strong>{{ admin_t('ui.collect_cfg_lead_strong') }}</strong>{{ admin_t('ui.collect_cfg_lead_after') }}</p>
        <form class="admin-form settings-page collect-config-form" id="site-form">
            <h3>{{ admin_t('ui.collect_ingest_title') }}</h3>
            <input type="hidden" name="collect_to_temp" id="collect_to_temp" value="{{ $toTemp ? '1' : '0' }}">
            <div class="ingest-modes" id="ingest-modes">
                <button type="button" class="ingest-mode{{ $toTemp ? '' : ' is-on' }}" data-value="0">
                    <strong>{{ admin_t('ui.collect_direct') }}</strong>
                    <span>{{ admin_t('ui.collect_direct_hint') }}</span>
                </button>
                <button type="button" class="ingest-mode{{ $toTemp ? ' is-on' : '' }}" data-value="1">
                    <strong>{{ admin_t('ui.collect_temp_first') }}</strong>
                    <span>{{ admin_t('ui.collect_temp_hint') }}</span>
                </button>
            </div>
            <p class="muted field-hint ingest-temp-hint" id="ingest-temp-hint" @if(! $toTemp) hidden @endif>{{ admin_t('ui.collect_temp_go_before') }}<a href="/admin/video/collect_temps">{{ admin_t('ui.collect_temps') }}</a>{{ admin_t('ui.collect_temp_go_after') }}</p>

            <h3>{{ admin_t('ui.collect_after_title') }}</h3>
            <div class="theme-nav-toggles">
            <input type="hidden" name="collect_in_status" value="0">
            <label class="inline">
                <input type="checkbox" name="collect_in_status" value="1" @checked($on('collect_in_status', '1'))>
                {{ admin_t('ui.collect_list_on') }}
            </label>
            <input type="hidden" name="collect_sync_pic" value="0">
            <label class="inline">
                <input type="checkbox" name="collect_sync_pic" value="1" @checked($on('collect_sync_pic', '1'))>
                {{ admin_t('ui.collect_sync_pic') }}
            </label>
            <input type="hidden" name="collect_pic_local" value="0">
            <label class="inline">
                <input type="checkbox" name="collect_pic_local" value="1" @checked($on('collect_pic_local'))>
                {{ admin_t('ui.collect_pic_local') }}
            </label>
            </div>
            <p class="muted field-hint">{{ admin_t('ui.collect_after_hint_before') }}<a href="/admin/video/tools/images">{{ admin_t('ui.remote_images') }}</a>{{ admin_t('ui.collect_after_hint_after') }}</p>
            <div class="settings-two">
                <div>
                    <label for="collect_hits_min">{{ admin_t('ui.collect_hits_min') }}</label>
                    <input id="collect_hits_min" type="number" name="collect_hits_min" min="0" value="{{ $s['collect_hits_min'] ?? 0 }}">
                </div>
                <div>
                    <label for="collect_hits_max">{{ admin_t('ui.collect_hits_max') }}</label>
                    <input id="collect_hits_max" type="number" name="collect_hits_max" min="0" value="{{ $s['collect_hits_max'] ?? 0 }}">
                </div>
            </div>
            <p class="muted field-hint">{{ admin_t('ui.collect_hits_hint') }}</p>

            <h3>{{ admin_t('ui.collect_map_title') }}</h3>
            <p class="muted field-hint">{{ admin_t('ui.collect_map_hint_before') }}<code>{{ admin_t('ui.ph_area_map') }}</code>{{ admin_t('ui.collect_map_hint_or') }}<code>{{ admin_t('ui.ph_area_map_csv') }}</code>{{ admin_t('ui.collect_map_hint_mid') }}<a href="/admin/video/settings?tab=more">{{ admin_t('ui.collect_map_settings') }}</a>{{ admin_t('ui.collect_map_hint_after') }}</p>
            <label for="collect_areawords">{{ admin_t('ui.area') }}</label>
            <textarea id="collect_areawords" name="collect_areawords" rows="5" placeholder="{{ admin_t('ui.ph_area_map') }}">{{ $s['collect_areawords'] ?? '' }}</textarea>
            <label for="collect_langwords">{{ admin_t('ui.lang_label') }}</label>
            <textarea id="collect_langwords" name="collect_langwords" rows="5" placeholder="{{ admin_t('ui.ph_lang_map') }}">{{ $s['collect_langwords'] ?? '' }}</textarea>

            <h3>{{ admin_t('ui.collect_push_title') }}</h3>
            <p class="muted field-hint">{{ admin_t('ui.collect_push_hint_before') }}<a href="/admin/video/config/interface">{{ admin_t('ui.config_interface') }}</a>{{ admin_t('ui.collect_push_hint_after') }}</p>

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
    var hidden = document.getElementById('collect_to_temp');
    var hint = document.getElementById('ingest-temp-hint');
    var wrap = document.getElementById('ingest-modes');
    if (wrap && hidden) {
        wrap.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-value]');
            if (!btn) return;
            hidden.value = btn.getAttribute('data-value') || '0';
            wrap.querySelectorAll('.ingest-mode').forEach(function (item) {
                item.classList.toggle('is-on', item === btn);
            });
            if (hint) hint.hidden = hidden.value !== '1';
        });
    }
})();
</script>
@endpush
