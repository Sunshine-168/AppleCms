@extends('admin.layouts.inner')
@section('title', admin_t('page.settings'))

@php
    $s = $site ?? [];
    $tab = $tab ?? 'site';
    $tabs = [
        'site' => admin_t('ui.tab_website'),
        'look' => admin_t('ui.look'),
        'member' => admin_t('ui.tab_members'),
        'interact' => admin_t('ui.comments'),
        'play' => admin_t('ui.playback'),
        'mail' => admin_t('ui.mail_out'),
        'seo' => admin_t('ui.tab_titles'),
        'storage' => admin_t('ui.tab_storage'),
        'more' => admin_t('nav.more'),
    ];
    $on = fn (string $k, string $d = '0') => (string) ($s[$k] ?? $d) === '1';
    $logo = trim((string) ($s['theme_logo'] ?? ''));
    $pluginLinks = $pluginLinks ?? [];
    $settingsJsLang = [
        'uploaded' => admin_t('ui.uploaded'),
        'upload_fail' => admin_t('ui.upload_fail'),
    ];
@endphp

@section('plain')
<div class="card card-panel">
    <div class="card-header"><span>{{ admin_t('nav.settings') }}</span></div>
    <div class="card-body">
        <p class="muted recycle-lead">{{ admin_t('ui.settings_lead') }}</p>
        <form class="settings-page" id="site-form">
            <div class="tabs settings-tabs" id="settingsTabs">
                @foreach($tabs as $key => $label)
                    <button type="button" class="{{ $tab === $key ? 'active' : '' }}" data-tab="{{ $key }}">{{ $label }}</button>
                @endforeach
            </div>

            <div class="settings-pane{{ $tab === 'site' ? ' active' : '' }}" data-pane="site">
                <label for="site_title">{{ admin_t('ui.label_site_name') }}</label>
                <input id="site_title" type="text" name="site_title" value="{{ $s['site_title'] ?? ($s['title'] ?? '') }}" placeholder="{{ admin_t('ui.ph_site_title') }}">

                <label for="site_description">{{ admin_t('ui.site_tagline') }}</label>
                <textarea id="site_description" name="site_description" rows="3">{{ $s['site_description'] ?? ($s['description'] ?? '') }}</textarea>
                <p class="muted field-hint">{{ admin_t('ui.site_desc_hint') }}</p>

                <label for="site_keyword">{{ admin_t('ui.search_keywords') }}</label>
                <input id="site_keyword" type="text" name="site_keyword" value="{{ $s['site_keyword'] ?? ($s['keyword'] ?? '') }}" placeholder="{{ admin_t('ui.ph_keywords_csv') }}">
                <p class="muted field-hint">{{ admin_t('ui.seo_kw_hint') }}</p>

                <input type="hidden" name="site_closed" value="0">
                <label class="inline">
                    <input type="checkbox" name="site_closed" value="1" @checked($on('site_closed'))>
                    {{ admin_t('ui.close_site') }}
                </label>
                <label for="site_close_tip">{{ admin_t('ui.close_tip_label') }}</label>
                <input id="site_close_tip" type="text" name="site_close_tip" value="{{ $s['site_close_tip'] ?? admin_t('ui.close_tip_default') }}">

                <label for="icp">{{ admin_t('ui.icp') }}</label>
                <input id="icp" type="text" name="icp" value="{{ $s['icp'] ?? '' }}" placeholder="京ICP备00000000号-1">
            </div>

            <div class="settings-pane{{ $tab === 'look' ? ' active' : '' }}" data-pane="look">
                <p class="muted field-hint">{{ admin_t('ui.settings_look_hint_a') }}<a href="/admin/video/templates">{{ admin_t('ui.theme_via_tpl') }}</a>{{ admin_t('ui.settings_look_hint_b') }}<a href="/admin/video/templates?desk=files">{{ admin_t('ui.theme_via_files') }}</a>{{ admin_t('ui.settings_look_hint_c') }}</p>

                <label for="theme_logo">{{ admin_t('ui.site_logo') }}</label>
                <div class="settings-file-preview" id="logo-preview">
                    <div class="settings-file-thumb{{ $logo === '' ? ' is-empty' : '' }}" id="logo-thumb">
                        <img id="logo-img" src="{{ $logo }}" alt="" @if($logo === '') hidden @endif>
                        <span class="settings-file-empty muted" id="logo-empty" @if($logo !== '') hidden @endif>{{ admin_t('ui.no_logo') }}</span>
                    </div>
                    <input id="theme_logo" type="text" name="theme_logo" value="{{ $logo }}" placeholder="{{ admin_t('ui.ph_image_or_upload') }}">
                    <button type="button" class="btn btn-muted btn-sm" id="logo-upload">{{ admin_t('ui.upload_image') }}</button>
                </div>
                <p class="muted field-hint">{{ admin_t('ui.logo_hint') }}</p>

                <label for="theme_primary">{{ admin_t('ui.primary_color') }}</label>
                <input id="theme_primary" type="text" name="theme_primary" value="{{ $s['theme_primary'] ?? '' }}" placeholder="#1b4f72" maxlength="7">
                <p class="muted field-hint">{{ admin_t('ui.primary_hint') }}</p>

                <label for="watermark_text">{{ admin_t('ui.cover_watermark') }}</label>
                <input id="watermark_text" type="text" name="watermark_text" value="{{ $s['watermark_text'] ?? '' }}" placeholder="{{ admin_t('ui.ph_watermark') }}">
                <p class="muted field-hint">{{ admin_t('ui.watermark_hint') }}</p>
            </div>

            <div class="settings-pane{{ $tab === 'member' ? ' active' : '' }}" data-pane="member">
                <div class="theme-nav-toggles">
                <input type="hidden" name="member_register" value="0">
                <label class="inline">
                    <input type="checkbox" name="member_register" value="1" @checked($on('member_register', '1'))>
                    {{ admin_t('ui.allow_register') }}
                </label>
                <input type="hidden" name="member_invite" value="0">
                <label class="inline">
                    <input type="checkbox" name="member_invite" value="1" @checked($on('member_invite'))>
                    {{ admin_t('ui.require_invite') }}
                </label>
                </div>
                <p class="muted field-hint">{{ admin_t('ui.settings_member_lead') }}</p>

                <label for="member_growth_mode">{{ admin_t('ui.growth_mode') }}</label>
                <select id="member_growth_mode" name="member_growth_mode">
                    <option value="points" @selected(($s['member_growth_mode'] ?? 'points') === 'points')>{{ admin_t('ui.growth_mode_points') }}</option>
                    <option value="vip_days" @selected(($s['member_growth_mode'] ?? '') === 'vip_days')>{{ admin_t('ui.growth_mode_vip') }}</option>
                    <option value="off" @selected(($s['member_growth_mode'] ?? '') === 'off')>{{ admin_t('ui.growth_mode_off') }}</option>
                </select>
                <p class="muted field-hint">{{ admin_t('ui.growth_mode_hint') }}</p>

                <label for="member_trial_days">{{ admin_t('ui.trial_days') }}</label>
                <input id="member_trial_days" type="number" name="member_trial_days" min="0" value="{{ $s['member_trial_days'] ?? 7 }}">
                <p class="muted field-hint">{{ admin_t('ui.trial_days_hint') }}</p>

                <label for="member_invite_reward_days">{{ admin_t('ui.invite_reward_days') }}</label>
                <input id="member_invite_reward_days" type="number" name="member_invite_reward_days" min="0" value="{{ $s['member_invite_reward_days'] ?? 30 }}">

                <label for="member_invite_month_cap">{{ admin_t('ui.invite_month_cap') }}</label>
                <input id="member_invite_month_cap" type="number" name="member_invite_month_cap" min="0" value="{{ $s['member_invite_month_cap'] ?? 5 }}">

                <label for="member_invite_ip_daily_cap">{{ admin_t('ui.invite_ip_daily_cap') }}</label>
                <input id="member_invite_ip_daily_cap" type="number" name="member_invite_ip_daily_cap" min="0" value="{{ $s['member_invite_ip_daily_cap'] ?? 1 }}">

                <label for="member_invite_l2_days">{{ admin_t('ui.invite_l2_days') }}</label>
                <input id="member_invite_l2_days" type="number" name="member_invite_l2_days" min="0" value="{{ $s['member_invite_l2_days'] ?? 0 }}">
                <p class="muted field-hint">{{ admin_t('ui.invite_l2_hint') }}</p>

                <label for="member_invite_l3_days">{{ admin_t('ui.invite_l3_days') }}</label>
                <input id="member_invite_l3_days" type="number" name="member_invite_l3_days" min="0" value="{{ $s['member_invite_l3_days'] ?? 0 }}">
                <p class="muted field-hint">{{ admin_t('ui.invite_l3_hint') }}</p>

                <label for="member_trial_group_id">{{ admin_t('ui.trial_group_id') }}</label>
                <select id="member_trial_group_id" name="member_trial_group_id">
                    <option value="0" @selected((int) ($s['member_trial_group_id'] ?? 0) === 0)>{{ admin_t('ui.trial_group_auto') }}</option>
                    @foreach($groups ?? [] as $group)
                        <option value="{{ $group->id }}" @selected((int) ($s['member_trial_group_id'] ?? 0) === (int) $group->id)>{{ $group->name }} (#{{ $group->id }})</option>
                    @endforeach
                </select>
                <p class="muted field-hint">{{ admin_t('ui.trial_group_hint') }}</p>
                <p class="muted field-hint"><a href="/admin/video/invites">{{ admin_t('ui.invites') }}</a></p>
            </div>

            <div class="settings-pane{{ $tab === 'interact' ? ' active' : '' }}" data-pane="interact">
                <div class="theme-nav-toggles">
                <input type="hidden" name="member_comment_login" value="0">
                <label class="inline">
                    <input type="checkbox" name="member_comment_login" value="1" @checked($on('member_comment_login'))>
                    {{ admin_t('ui.comment_need_login') }}
                </label>
                <input type="hidden" name="comment_audit" value="0">
                <label class="inline">
                    <input type="checkbox" name="comment_audit" value="1" @checked($on('comment_audit'))>
                    {{ admin_t('ui.comment_need_audit') }}
                </label>
                <input type="hidden" name="gbook_audit" value="0">
                <label class="inline">
                    <input type="checkbox" name="gbook_audit" value="1" @checked($on('gbook_audit'))>
                    {{ admin_t('ui.gbook_audit') }}
                </label>
                </div>

                <label for="banned_words">{{ admin_t('ui.banned_words') }}</label>
                <textarea id="banned_words" name="banned_words" rows="4" placeholder="{{ admin_t('ui.ph_csv_or_nl') }}">{{ $s['banned_words'] ?? '' }}</textarea>
                <p class="muted field-hint">{{ admin_t('ui.banned_hint') }}</p>
            </div>

            <div class="settings-pane{{ $tab === 'play' ? ' active' : '' }}" data-pane="play">
                <label for="play_buffer">{{ admin_t('ui.play_buffer_sec') }}</label>
                <input id="play_buffer" type="number" name="play_buffer" min="0" value="{{ $s['play_buffer'] ?? 5 }}">
                <input type="hidden" name="play_encrypt" value="0">
                <label class="inline">
                    <input type="checkbox" name="play_encrypt" value="1" @checked($on('play_encrypt'))>
                    {{ admin_t('ui.play_encrypt') }}
                </label>
                <p class="muted field-hint">{{ admin_t('ui.play_encrypt_hint') }}</p>

                <label for="trysee_seconds">{{ admin_t('ui.trysee_seconds') }}</label>
                <input id="trysee_seconds" type="number" name="trysee_seconds" min="0" value="{{ $s['trysee_seconds'] ?? 0 }}">
                <p class="muted field-hint">{{ admin_t('ui.trysee_hint') }}</p>
            </div>

            <div class="settings-pane{{ $tab === 'mail' ? ' active' : '' }}" data-pane="mail">
                <p class="muted field-hint">{{ admin_t('ui.mail_hint') }}</p>
                <label for="smtp_host">{{ admin_t('ui.mail_server') }}</label>
                <input id="smtp_host" type="text" name="smtp_host" value="{{ $s['smtp_host'] ?? '' }}" placeholder="smtp.example.com">
                <div class="settings-two">
                    <div>
                        <label for="smtp_port">{{ admin_t('ui.port') }}</label>
                        <input id="smtp_port" type="number" name="smtp_port" value="{{ $s['smtp_port'] ?? 465 }}">
                    </div>
                </div>
                <p class="muted field-hint">{{ admin_t('ui.smtp_port_hint') }}</p>
                <label for="smtp_user">{{ admin_t('ui.login_account') }}</label>
                <input id="smtp_user" type="text" name="smtp_user" value="{{ $s['smtp_user'] ?? '' }}">
                <label for="smtp_pass">{{ admin_t('ui.password') }}</label>
                <input id="smtp_pass" type="password" name="smtp_pass" value="" autocomplete="new-password" placeholder="{{ trim((string) ($s['smtp_pass'] ?? '')) !== '' ? admin_t('ui.saved_blank') : '' }}">
                <label for="smtp_from">{{ admin_t('ui.from_email') }}</label>
                <input id="smtp_from" type="email" name="smtp_from" value="{{ $s['smtp_from'] ?? '' }}" placeholder="noreply@example.com">
                <label for="test-mail-to">{{ admin_t('ui.test_email') }}</label>
                <div class="field-inline">
                    <input type="email" id="test-mail-to" placeholder="{{ admin_t('ui.ph_mail_to') }}">
                    <button type="button" class="btn btn-muted" id="site-test-mail">{{ admin_t('ui.send_test_mail') }}</button>
                </div>
            </div>

            <div class="settings-pane{{ $tab === 'seo' ? ' active' : '' }}" data-pane="seo">
                <label for="seo_title_vod">{{ admin_t('ui.seo_vod_page') }}</label>
                <input id="seo_title_vod" type="text" name="seo_title_vod" value="{{ $s['seo_title_vod'] ?? '' }}" placeholder="{name} - {site}">
                <label for="seo_title_type">{{ admin_t('ui.seo_type_page') }}</label>
                <input id="seo_title_type" type="text" name="seo_title_type" value="{{ $s['seo_title_type'] ?? '' }}" placeholder="{type} - {site}">
                <label for="seo_title_play">{{ admin_t('ui.seo_play_page') }}</label>
                <input id="seo_title_play" type="text" name="seo_title_play" value="{{ $s['seo_title_play'] ?? '' }}" placeholder="{{ admin_t('ui.ph_seo_play') }}">
                <p class="muted field-hint">{{ admin_t('ui.seo_tokens_pre') }} <code>{name}</code> <code>{type}</code> <code>{site}</code>{{ admin_t('ui.seo_tokens_end') }}</p>

                <h3>{{ admin_t('item.config_analytics') }}</h3>
                <textarea id="analytics_code" name="analytics_code" rows="4" placeholder="{{ admin_t('ui.ph_analytics') }}">{{ $s['analytics_code'] ?? '' }}</textarea>
                <p class="muted field-hint">{{ admin_t('ui.analytics_hint') }}</p>
            </div>

            <div class="settings-pane{{ $tab === 'storage' ? ' active' : '' }}" data-pane="storage">
                <label for="storage_disk">{{ admin_t('ui.store_where') }}</label>
                <select id="storage_disk" name="storage_disk">
                    <option value="local" @selected(($s['storage_disk'] ?? 'local') === 'local')>{{ admin_t('ui.local_disk') }}</option>
                    <option value="s3" @selected(($s['storage_disk'] ?? '') === 's3')>{{ admin_t('ui.object_storage') }}</option>
                </select>
                <details class="settings-details">
                    <summary>{{ admin_t('ui.s3_params') }}</summary>
                    <label for="s3_key">Access Key</label>
                    <input id="s3_key" type="text" name="s3_key" value="{{ $s['s3_key'] ?? '' }}">
                    <label for="s3_secret">Secret</label>
                    <input id="s3_secret" type="password" name="s3_secret" value="" autocomplete="new-password" placeholder="{{ trim((string) ($s['s3_secret'] ?? '')) !== '' ? admin_t('ui.saved_blank') : '' }}">
                    <label for="s3_region">Region</label>
                    <input id="s3_region" type="text" name="s3_region" value="{{ $s['s3_region'] ?? '' }}">
                    <label for="s3_bucket">Bucket</label>
                    <input id="s3_bucket" type="text" name="s3_bucket" value="{{ $s['s3_bucket'] ?? '' }}">
                    <label for="s3_endpoint">{{ admin_t('ui.s3_endpoint') }}</label>
                    <input id="s3_endpoint" type="text" name="s3_endpoint" value="{{ $s['s3_endpoint'] ?? '' }}" placeholder="{{ admin_t('ui.ph_s3_endpoint') }}">
                    <label for="s3_url">{{ admin_t('ui.public_domain') }}</label>
                    <input id="s3_url" type="url" name="s3_url" value="{{ $s['s3_url'] ?? '' }}" placeholder="https://cdn.example.com">
                    <p class="muted field-hint">{{ admin_t('ui.s3_url_hint') }}</p>
                </details>

                <h3>{{ admin_t('ui.upload') }}</h3>
                <label for="upload_ext">{{ admin_t('ui.allowed_ext') }}</label>
                <input id="upload_ext" type="text" name="upload_ext" value="{{ $s['upload_ext'] ?? '' }}">
                <label for="upload_max_mb">{{ admin_t('ui.max_mb') }}</label>
                <input id="upload_max_mb" type="number" name="upload_max_mb" min="1" value="{{ $s['upload_max_mb'] ?? 8 }}">
                <p class="muted field-hint">{{ admin_t('ui.upload_ip_hint_a') }}<a href="/admin/video/config/ip">{{ admin_t('page.config_ip') }}</a>{{ admin_t('ui.upload_ip_hint_b') }}</p>
            </div>

            <div class="settings-pane{{ $tab === 'more' ? ' active' : '' }}" data-pane="more">
                <p class="muted field-hint">{{ admin_t('ui.settings_more_lead') }}</p>

                <h3>{{ admin_t('ui.vod_urls') }}</h3>
                <label for="rewrite_mode">{{ admin_t('ui.rewrite_how') }}</label>
                <select id="rewrite_mode" name="rewrite_mode">
                    <option value="laravel" @selected(($s['rewrite_mode'] ?? 'laravel') === 'laravel')>{{ admin_t('ui.rewrite_laravel') }}</option>
                    <option value="mac" @selected(($s['rewrite_mode'] ?? '') === 'mac')>{{ admin_t('ui.rewrite_mac') }}</option>
                </select>
                <label for="rewrite_suffix">{{ admin_t('ui.rewrite_suffix') }}</label>
                <input id="rewrite_suffix" type="text" name="rewrite_suffix" value="{{ $s['rewrite_suffix'] ?? '.html' }}">
                <p class="muted field-hint">{{ admin_t('ui.rewrite_suffix_hint_a') }}<code>/vod/123</code>{{ admin_t('ui.rewrite_suffix_hint_b') }}<a href="/admin/help?topic=rewrite">{{ admin_t('page.rewrite') }}</a>{{ admin_t('ui.rewrite_suffix_hint_c') }}</p>

                <h3>{{ admin_t('ui.page_cache') }}</h3>
                <p class="muted field-hint">{{ admin_t('ui.page_cache_hint_a') }}<a href="/admin/video/make">{{ admin_t('nav.make') }}</a>{{ admin_t('ui.page_cache_hint_b') }}</p>

                <h3>{{ admin_t('ui.collect_ingest') }}</h3>
                <p class="muted field-hint">{{ admin_t('ui.collect_ingest_hint_a') }}<a href="/admin/video/config/collect">{{ admin_t('page.config_collect') }}</a>{{ admin_t('ui.collect_ingest_hint_b') }}</p>

                <h3>{{ admin_t('ui.front_filters') }}</h3>
                <label for="filter_area">{{ admin_t('ui.area') }}</label>
                <input id="filter_area" type="text" name="filter_area" value="{{ $s['filter_area'] ?? '' }}">
                <label for="filter_lang">{{ admin_t('ui.lang_label') }}</label>
                <input id="filter_lang" type="text" name="filter_lang" value="{{ $s['filter_lang'] ?? '' }}">
                <label for="filter_year">{{ admin_t('ui.era') }}</label>
                <input id="filter_year" type="text" name="filter_year" value="{{ $s['filter_year'] ?? '' }}">
                <p class="muted field-hint">{{ admin_t('ui.filter_csv_hint') }}<a href="/admin/system/dicts">{{ admin_t('ui.go_dict_items') }}</a>{{ admin_t('ui.seo_tokens_end') }}</p>

                <details class="settings-details">
                    <summary>{{ admin_t('ui.ingest_api') }}</summary>
                    <p class="muted field-hint">{{ admin_t('ui.ingest_api_hint_a') }}<a href="/admin/video/config/api">{{ admin_t('page.config_api') }}</a>{{ admin_t('ui.ingest_api_hint_b') }}<a href="/admin/video/config/interface">{{ admin_t('page.config_interface') }}</a>{{ admin_t('ui.ingest_api_hint_c') }}<a href="/admin/video/collects">{{ admin_t('ui.collects') }}</a>{{ admin_t('ui.ingest_api_hint_d') }}</p>
                </details>

                <details class="settings-details">
                    <summary>{{ admin_t('ui.engine_push') }}</summary>
                    <p class="muted field-hint">{{ admin_t('ui.engine_push_hint_a') }}<a href="/admin/video/push">{{ admin_t('page.push') }}</a>{{ admin_t('ui.engine_push_hint_b') }}</p>
                </details>

                @if($pluginLinks !== [])
                    <h3>{{ admin_t('ui.plugin_params') }}</h3>
                    <p class="muted field-hint">{{ admin_t('ui.plugin_params_hint') }}</p>
                    <div class="toolbar">
                        @foreach($pluginLinks as $link)
                            <a class="btn btn-muted btn-sm" href="{{ $link['url'] }}">{{ admin_t($link['label']) }}</a>
                        @endforeach
                        <a class="btn btn-muted btn-sm" href="/admin/plugins">{{ admin_t('nav.plugins') }}</a>
                    </div>
                @endif
            </div>

            <div class="form-actions settings-save">
                <button type="button" class="btn" id="site-save">{{ admin_t('ui.save_settings') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection

@include('admin.partials.site-save')

@push('scripts')
<script>
(function () {
    var L = @json($settingsJsLang, JSON_UNESCAPED_UNICODE);
    var tabs = document.getElementById('settingsTabs');
    if (tabs) {
        tabs.querySelectorAll('[data-tab]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var tab = btn.getAttribute('data-tab') || 'site';
                tabs.querySelectorAll('[data-tab]').forEach(function (item) {
                    item.classList.toggle('active', item === btn);
                });
                document.querySelectorAll('.settings-pane').forEach(function (pane) {
                    pane.classList.toggle('active', pane.getAttribute('data-pane') === tab);
                });
                var url = new URL(window.location.href);
                if (tab === 'site') url.searchParams.delete('tab');
                else url.searchParams.set('tab', tab);
                history.replaceState(null, '', url);
            });
        });
    }

    var input = document.getElementById('theme_logo');
    var img = document.getElementById('logo-img');
    var empty = document.getElementById('logo-empty');
    var thumb = document.getElementById('logo-thumb');
    function showLogo(url) {
        url = String(url || '').trim();
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
    if (input) {
        input.addEventListener('input', function () { showLogo(input.value); });
    }
    var up = document.getElementById('logo-upload');
    if (up && window.AdminUi) {
        up.addEventListener('click', function () {
            AdminUi.pickFile('image/*').then(function (file) {
                if (!file) return;
                AdminUi.loading(true);
                return AdminUi.upload(file).then(function (res) {
                    AdminUi.loading(false);
                    if (res && res.code === 0 && res.data && res.data.url) {
                        input.value = res.data.url;
                        showLogo(res.data.url);
                        AdminUi.toast(L.uploaded, 'ok');
                    } else {
                        AdminUi.toast((res && res.msg) || L.upload_fail, 'err');
                    }
                });
            });
        });
    }
})();
</script>
@endpush
