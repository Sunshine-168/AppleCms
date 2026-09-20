@extends('admin.layouts.inner')
@section('title', admin_t('nav.templates'))

@php
    $s = $site ?? [];
    $tab = $tab ?? 'base';
    $desk = 'look';
    $hasPlayView = (bool) ($hasPlayView ?? false);
    $tabs = [
        'base' => admin_t('ui.basic_settings'),
        'home' => admin_t('ui.home_config'),
        'page' => admin_t('ui.page_config'),
        'nav' => admin_t('ui.nav_menu'),
        'other' => admin_t('ui.other_settings'),
        'seo' => admin_t('ui.seo_settings'),
        'ads' => admin_t('ui.ad_settings'),
    ];
    if (! $hasPlayView) {
        unset($tabs['page']);
    }
    $on = fn (string $k, string $d = '1') => (string) ($s[$k] ?? $d) === '1';
    $assets = [
        ['name' => 'theme_logo', 'label' => admin_t('ui.logo_top'), 'empty' => admin_t('ui.no_logo'), 'kind' => 'logo', 'hint' => admin_t('ui.logo_hint')],
        ['name' => 'theme_logo_foot', 'label' => admin_t('ui.logo_foot'), 'empty' => admin_t('ui.no_logo_foot'), 'kind' => 'logo', 'hint' => admin_t('ui.logo_foot_hint')],
        ['name' => 'theme_favicon', 'label' => admin_t('ui.site_icon'), 'empty' => admin_t('ui.no_icon'), 'kind' => 'favicon', 'hint' => admin_t('ui.favicon_hint')],
        ['name' => 'theme_webapp', 'label' => admin_t('ui.webapp_icon'), 'empty' => admin_t('ui.no_webapp_icon'), 'kind' => 'favicon', 'hint' => admin_t('ui.webapp_hint')],
        ['name' => 'theme_lazy', 'label' => admin_t('ui.lazy_placeholder'), 'empty' => admin_t('ui.no_placeholder'), 'kind' => 'logo', 'hint' => admin_t('ui.lazy_hint')],
    ];
    $navToggles = [
        ['name' => 'theme_nav_latest', 'label' => admin_t('ui.nav_latest')],
        ['name' => 'theme_nav_topic', 'label' => admin_t('ui.topics')],
        ['name' => 'theme_nav_actor', 'label' => admin_t('ui.actors')],
        ['name' => 'theme_nav_role', 'label' => admin_t('ui.cast')],
        ['name' => 'theme_nav_art', 'label' => admin_t('ui.nav_news')],
        ['name' => 'theme_nav_website', 'label' => admin_t('ui.websites_nav')],
    ];
    $themeJsLang = [
        'uploaded' => admin_t('ui.uploaded'),
        'upload_fail' => admin_t('ui.upload_fail'),
        'finished' => admin_t('ui.finished'),
    ];
@endphp

@section('plain')
<div class="card card-panel desk-board" id="theme-index">
    <div class="card-header">
        <span>{{ admin_t('nav.templates') }}</span>
        <div>
            @include('admin.video.partials.theme-desks', ['desk' => 'look'])
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">{{ admin_t('ui.theme_lead') }}</p>
        <div class="tabs settings-tabs" id="themeTabs">
            @foreach($tabs as $key => $label)
                <button type="button" class="{{ $tab === $key ? 'active' : '' }}" data-tab="{{ $key }}">{{ $label }}</button>
            @endforeach
        </div>
        <form class="settings-page" id="theme-form">
            <input type="hidden" name="tab" value="theme">

            <div class="settings-pane{{ $tab === 'base' ? ' active' : '' }}" data-pane="base">
                <p class="muted field-hint">{{ admin_t('ui.theme_lottie_hint') }}</p>
                @foreach($assets as $field)
                    @php
                        $val = trim((string) ($s[$field['name']] ?? ''));
                    @endphp
                    <label for="{{ $field['name'] }}">{{ $field['label'] }}</label>
                    <div class="settings-file-preview{{ $field['kind'] === 'favicon' ? ' settings-file-preview--favicon' : '' }}" data-preview="{{ $field['name'] }}">
                        <div class="settings-file-thumb{{ $val === '' ? ' is-empty' : '' }}" data-thumb>
                            <img data-img src="{{ $val }}" alt="" @if($val === '') hidden @endif>
                            <span class="settings-file-empty muted" data-empty @if($val !== '') hidden @endif>{{ $field['empty'] }}</span>
                        </div>
                        <div class="field-inline">
                            <input id="{{ $field['name'] }}" type="text" name="{{ $field['name'] }}" value="{{ $val }}" placeholder="{{ admin_t('ui.ph_image_or_upload') }}" data-url>
                            <button type="button" class="btn btn-muted btn-sm" data-upload>{{ admin_t('ui.upload_image') }}</button>
                        </div>
                    </div>
                    <p class="muted field-hint">{{ $field['hint'] }}</p>
                @endforeach

                <label for="theme_head_code">{{ admin_t('ui.head_code') }}</label>
                <textarea id="theme_head_code" name="theme_head_code" rows="5" placeholder="{{ admin_t('ui.ph_head_code') }}">{{ $s['theme_head_code'] ?? '' }}</textarea>
                <p class="muted field-hint">{{ admin_t('ui.head_code_hint') }}</p>

                <label for="theme_foot_code">{{ admin_t('ui.foot_note') }}</label>
                <textarea id="theme_foot_code" name="theme_foot_code" rows="4" placeholder="{{ admin_t('ui.ph_foot_code') }}">{{ $s['theme_foot_code'] ?? '' }}</textarea>
                <p class="muted field-hint">{{ admin_t('ui.foot_code_hint') }}</p>
            </div>

            <div class="settings-pane{{ $tab === 'home' ? ' active' : '' }}" data-pane="home">
                <p class="muted field-hint">{{ admin_t('ui.theme_home_hint_a') }}<code>home</code>{{ admin_t('ui.theme_home_hint_b') }}<a href="/admin/video/slides">{{ admin_t('nav.slides') }}</a>{{ admin_t('ui.theme_home_hint_c') }}</p>
                <label for="theme_home_rec_num">{{ admin_t('ui.home_rec_num') }}</label>
                <input id="theme_home_rec_num" type="number" name="theme_home_rec_num" min="1" max="100" value="{{ $s['theme_home_rec_num'] ?? 12 }}">
                <p class="muted field-hint">{{ admin_t('ui.home_rec_hint_a') }}<code>@@vod</code>{{ admin_t('ui.home_rec_hint_b') }}</p>
            </div>

            @if($hasPlayView)
            <div class="settings-pane{{ $tab === 'page' ? ' active' : '' }}" data-pane="page">
                <label for="theme_play_notice">{{ admin_t('ui.play_notice') }}</label>
                <textarea id="theme_play_notice" name="theme_play_notice" rows="3" placeholder="{{ admin_t('ui.ph_play_notice') }}">{{ $s['theme_play_notice'] ?? '' }}</textarea>
                <p class="muted field-hint">{{ admin_t('ui.play_notice_hint') }}</p>
            </div>
            @endif

            <div class="settings-pane{{ $tab === 'nav' ? ' active' : '' }}" data-pane="nav">
                <p class="muted field-hint">{{ admin_t('ui.theme_nav_hint') }}</p>
                <div class="theme-nav-toggles">
                    @foreach($navToggles as $nav)
                        <input type="hidden" name="{{ $nav['name'] }}" value="0">
                        <label class="inline">
                            <input type="checkbox" name="{{ $nav['name'] }}" value="1" @checked($on($nav['name']))>
                            {{ $nav['label'] }}
                        </label>
                    @endforeach
                </div>
                <h3>{{ admin_t('ui.custom_links') }}</h3>
                @for($i = 1; $i <= 4; $i++)
                    <div class="settings-two">
                        <div>
                            <label for="theme_nav_name{{ $i }}">{{ admin_t('ui.name') }} {{ $i }}</label>
                            <input id="theme_nav_name{{ $i }}" type="text" name="theme_nav_name{{ $i }}" value="{{ $s['theme_nav_name'.$i] ?? '' }}" maxlength="40">
                        </div>
                        <div>
                            <label for="theme_nav_url{{ $i }}">{{ admin_t('ui.label_address') }} {{ $i }}</label>
                            <input id="theme_nav_url{{ $i }}" type="text" name="theme_nav_url{{ $i }}" value="{{ $s['theme_nav_url'.$i] ?? '' }}" placeholder="{{ admin_t('ui.ph_http_or_path') }}">
                        </div>
                    </div>
                @endfor
                <p class="muted field-hint">{{ admin_t('ui.custom_nav_hint') }}</p>
            </div>

            <div class="settings-pane{{ $tab === 'other' ? ' active' : '' }}" data-pane="other">
                <label for="theme_primary">{{ admin_t('ui.primary_color') }}</label>
                <input id="theme_primary" type="text" name="theme_primary" value="{{ $s['theme_primary'] ?? '' }}" placeholder="#1b4f72" maxlength="7">
                <p class="muted field-hint">{{ admin_t('ui.primary_hint') }}{{ admin_t('ui.watermark_in_settings_a') }}<a href="/admin/video/settings?tab=look">{{ admin_t('nav.settings') }}</a>{{ admin_t('ui.watermark_in_settings_b') }}</p>
            </div>

            <div class="settings-pane{{ $tab === 'seo' ? ' active' : '' }}" data-pane="seo">
                <p class="muted field-hint">{{ admin_t('ui.theme_seo_hint_a') }}{{ admin_t('nav.settings') }}{{ admin_t('ui.theme_seo_hint_mid') }}{{ admin_t('nav.more') }}{{ admin_t('ui.theme_seo_hint_b') }}<code>@@vodSeo</code>{{ admin_t('ui.theme_seo_hint_c') }}</p>
                <label for="seo_title_vod">{{ admin_t('ui.seo_vod_page') }}</label>
                <input id="seo_title_vod" type="text" name="seo_title_vod" value="{{ $s['seo_title_vod'] ?? '' }}" placeholder="{name} - {site}">
                <label for="seo_title_type">{{ admin_t('ui.seo_type_page') }}</label>
                <input id="seo_title_type" type="text" name="seo_title_type" value="{{ $s['seo_title_type'] ?? '' }}" placeholder="{type} - {site}">
                <label for="seo_title_play">{{ admin_t('ui.seo_play_page') }}</label>
                <input id="seo_title_play" type="text" name="seo_title_play" value="{{ $s['seo_title_play'] ?? '' }}" placeholder="{{ admin_t('ui.ph_seo_play') }}">
                <p class="muted field-hint">{{ admin_t('ui.seo_tokens_pre') }} <code>{name}</code> <code>{type}</code> <code>{site}</code>{{ admin_t('ui.seo_tokens_end') }}</p>
            </div>

            <div class="settings-pane{{ $tab === 'ads' ? ' active' : '' }}" data-pane="ads">
                <p class="muted recycle-lead">{{ admin_t('ui.theme_ads_lead_a') }}<code>@@vodAd</code>{{ admin_t('ui.theme_ads_lead_b') }}<code>header</code>{{ admin_t('ui.list_sep') }}<code>footer</code>{{ admin_t('ui.list_sep') }}<code>play</code>{{ admin_t('ui.seo_tokens_end') }}</p>
                <p><a class="btn btn-muted btn-sm" href="/admin/video/ads">{{ admin_t('ui.ads') }}</a></p>
            </div>

            <div class="form-actions settings-save">
                <button type="button" class="btn" id="theme-save">{{ admin_t('ui.save') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var L = @json($themeJsLang, JSON_UNESCAPED_UNICODE);
    var tabs = document.getElementById('themeTabs');
    if (tabs) {
        tabs.querySelectorAll('[data-tab]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var tab = btn.getAttribute('data-tab') || 'base';
                tabs.querySelectorAll('[data-tab]').forEach(function (item) {
                    item.classList.toggle('active', item === btn);
                });
                document.querySelectorAll('.settings-pane').forEach(function (pane) {
                    pane.classList.toggle('active', pane.getAttribute('data-pane') === tab);
                });
                var url = new URL(window.location.href);
                if (tab === 'base') url.searchParams.delete('tab');
                else url.searchParams.set('tab', tab);
                history.replaceState(null, '', url);
            });
        });
    }

    function showPreview(box, url) {
        url = String(url || '').trim();
        var img = box.querySelector('[data-img]');
        var empty = box.querySelector('[data-empty]');
        var thumb = box.querySelector('[data-thumb]');
        if (!img || !empty || !thumb) return;
        if (!url) {
            img.removeAttribute('src');
            img.hidden = true;
            empty.hidden = false;
            thumb.classList.add('is-empty');
            return;
        }
        img.hidden = false;
        empty.hidden = true;
        thumb.classList.remove('is-empty');
        if (img.getAttribute('src') !== url) img.src = url;
    }

    document.querySelectorAll('[data-preview]').forEach(function (box) {
        var input = box.querySelector('[data-url]');
        if (input) {
            input.addEventListener('input', function () { showPreview(box, input.value); });
        }
        var up = box.querySelector('[data-upload]');
        if (up && window.AdminUi) {
            up.addEventListener('click', function () {
                AdminUi.pickFile('image/*').then(function (file) {
                    if (!file) return;
                    AdminUi.loading(true);
                    return AdminUi.upload(file).then(function (res) {
                        AdminUi.loading(false);
                        if (res && res.code === 0 && res.data && res.data.url) {
                            if (input) input.value = res.data.url;
                            showPreview(box, res.data.url);
                            AdminUi.toast(L.uploaded, 'ok');
                        } else {
                            AdminUi.toast((res && res.msg) || L.upload_fail, 'err');
                        }
                    });
                });
            });
        }
    });

    var save = document.getElementById('theme-save');
    var form = document.getElementById('theme-form');
    if (save && form) {
        var doSave = function () {
            AdminUi.post('/admin/video/theme', AdminUi.formData(form)).then(function (res) {
                AdminUi.toast((res && res.msg) || L.finished, res && res.code === 0 ? 'ok' : 'err');
            });
        };
        save.addEventListener('click', doSave);
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            doSave();
        });
    }
})();
</script>
@endpush
