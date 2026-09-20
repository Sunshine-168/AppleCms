@extends('admin.layouts.inner')
@php
    $type = is_array($type ?? null) ? $type : [];
    $isEdit = (bool) ($isEdit ?? false);
    $scope = in_array(($scope ?? 'vod'), ['art', 'website'], true) ? $scope : 'vod';
    $isArt = $scope === 'art';
    $isWebsite = $scope === 'website';
    $parents = is_array($parents ?? null) ? $parents : [];
    $parent = is_array($parent ?? null) ? $parent : null;
    $name = (string) ($type['name'] ?? '');
    $parentId = (string) ($type['parent_id'] ?? '0');
    $status = (string) ($type['status'] ?? '1');
    $parentName = (string) ($parent['name'] ?? '');
    $base = $isArt ? '/admin/video/art-types' : ($isWebsite ? '/admin/video/website-types' : '/admin/video/types');
    $mid = $isArt ? 2 : ($isWebsite ? 3 : 1);
    $title = $isEdit
        ? ($isArt ? admin_t('ui.edit_column') : admin_t('ui.edit_type'))
        : ($parentName !== ''
            ? ($isArt ? admin_t('ui.add_child_column') : admin_t('ui.add_child'))
            : ($isArt ? admin_t('ui.add_column') : ($isWebsite ? admin_t('ui.add_nav_type') : admin_t('ui.add_type'))));
    $jsLang = [
        'need_name' => admin_t('ui.need_name'),
        'save_fail' => admin_t('ui.save_fail'),
        'saved' => admin_t('ui.saved'),
        'uploaded' => admin_t('ui.uploaded'),
        'upload_fail' => admin_t('ui.upload_fail'),
        'kind_list' => admin_t('ui.kind_list_hint'),
        'kind_hub' => admin_t('ui.kind_hub_hint'),
        'kind_single' => admin_t('ui.kind_single_hint'),
        'kind_link' => admin_t('ui.kind_link_hint'),
    ];
@endphp
@section('title', $title)

@section('plain')
<div class="card card-panel type-form-page">
    <div class="card-header">
        <span>{{ $title }}@if($isEdit && $name !== '') <em>{{ $name }}</em>@endif</span>
        <a class="btn btn-muted btn-sm" href="{{ $base }}">{{ $isArt ? admin_t('ui.back_columns') : admin_t('ui.back_types') }}</a>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">
            @if($isEdit)
                @if($isArt)
                    {{ admin_t('ui.type_form_edit_art') }}
                @elseif($isWebsite)
                    {{ admin_t('ui.type_form_edit_web') }}
                @else
                    {{ admin_t('ui.type_form_edit_vod') }}
                @endif
            @elseif($parentName !== '')
                {{ admin_t('ui.type_form_child_lead', ['name' => $parentName]) }}
            @elseif($isArt)
                {{ admin_t('ui.type_form_new_art') }}
            @elseif($isWebsite)
                {{ admin_t('ui.type_form_new_web') }}
            @else
                {{ admin_t('ui.type_form_new_vod') }}
            @endif
        </p>

        <form class="admin-form type-form" id="type-form">
            <input type="hidden" name="id" value="{{ $isEdit ? (int) ($type['id'] ?? 0) : '' }}">
            <input type="hidden" name="mid" value="{{ $mid }}">

            <h3>{{ $isArt ? admin_t('ui.this_section') : admin_t('ui.this_type') }}</h3>
            <label for="type-name">{{ admin_t('ui.name') }}</label>
            <input id="type-name" type="text" name="name" value="{{ $name }}" placeholder="{{ $isArt ? admin_t('ui.ph_type_art') : ($isWebsite ? admin_t('ui.ph_type_web') : admin_t('ui.ph_type_vod')) }}" required>
            <label for="type-slug">{{ admin_t('ui.url_alias') }}</label>
            <input id="type-slug" type="text" name="slug" value="{{ $type['slug'] ?? '' }}" placeholder="{{ admin_t('ui.ph_type_slug_ex', ['slug' => $isArt ? 'news' : ($isWebsite ? 'tools' : 'movie')]) }}">
            <p class="muted field-hint">{{ $isArt ? admin_t('ui.slug_in_section_url') : admin_t('ui.slug_in_type_url') }}</p>

            <label for="type-parent">{{ admin_t('ui.parent') }}</label>
            <select id="type-parent" name="parent_id">
                <option value="0" @selected($parentId === '0' || $parentId === '')>{{ $isArt ? admin_t('ui.top_unattached_section') : admin_t('ui.top_unattached') }}</option>
                @foreach($parents as $item)
                    <option value="{{ $item['id'] }}" @selected($parentId === (string) $item['id'])>
                        {{ str_repeat('└ ', max((int) ($item['depth'] ?? 0), 0)) }}{{ $item['name'] }}
                    </option>
                @endforeach
            </select>
            <p class="muted field-hint">{{ admin_t('ui.parent_nest_hint') }}</p>

            @if($isArt)
                @php $kind = \App\Models\Video\VideoTypeModel::normalizeKind($type['kind'] ?? 'list'); @endphp
                <label for="type-kind">{{ admin_t('ui.col_type') }}</label>
                <select id="type-kind" name="kind">
                    <option value="list" @selected($kind === 'list')>{{ admin_t('ui.kind_list') }}</option>
                    <option value="hub" @selected($kind === 'hub')>{{ admin_t('ui.kind_hub') }}</option>
                    <option value="single" @selected($kind === 'single')>{{ admin_t('ui.kind_single') }}</option>
                    <option value="link" @selected($kind === 'link')>{{ admin_t('ui.kind_link') }}</option>
                </select>
                <p class="muted field-hint" id="type-kind-hint"></p>
                <div id="type-jump-wrap" hidden>
                    <label for="type-jump">{{ admin_t('ui.jump_url') }}</label>
                    <input id="type-jump" type="text" name="jump_url" value="{{ $type['jump_url'] ?? '' }}" placeholder="https:// 或 /arts">
                    <p class="muted field-hint">{{ admin_t('ui.jump_hint') }}</p>
                </div>
                <label for="type-pic">{{ admin_t('ui.cover') }}</label>
                <div class="media-field">
                    <div class="media-preview" id="type-pic-preview" @if(trim((string) ($type['pic'] ?? '')) === '') hidden @endif>
                        <img id="type-pic-img" src="{{ $type['pic'] ?? '' }}" alt="{{ admin_t('ui.cover') }}">
                        <button type="button" class="media-preview-clear" id="type-pic-clear" title="{{ admin_t('ui.remove_cover') }}">&times;</button>
                    </div>
                    <div class="cover-row">
                        <input id="type-pic" type="text" name="pic" value="{{ $type['pic'] ?? '' }}" placeholder="{{ admin_t('ui.ph_image_url_opt') }}">
                        <button type="button" class="btn btn-muted" id="type-pic-upload">{{ admin_t('ui.upload') }}</button>
                    </div>
                </div>
            @endif

            <h3>{{ admin_t('ui.display') }}</h3>
            <label for="type-sort">{{ admin_t('ui.sort') }}</label>
            <input id="type-sort" type="number" name="sort" value="{{ $type['sort'] ?? 0 }}">
            <p class="muted field-hint">{{ admin_t('ui.sort_sibling_hint') }}</p>
            @if($isArt)
                <div id="type-page-wrap">
                    <label for="type-page-size">{{ admin_t('ui.page_size') }}</label>
                    <input id="type-page-size" type="number" name="page_size" min="0" max="100" value="{{ (int) ($type['page_size'] ?? 0) }}">
                    <p class="muted field-hint">{{ admin_t('ui.page_size_art_hint') }}</p>
                </div>
            @endif
            <input type="hidden" name="status" value="0">
            <label class="inline">
                <input type="checkbox" name="status" value="1" @checked($status === '1')>
                {{ admin_t('ui.show_on_front') }}
            </label>
            <p class="muted field-hint">{{ $isArt ? admin_t('ui.hide_menu_arts_stay') : ($isWebsite ? admin_t('ui.hide_menu_sites_stay') : admin_t('ui.hide_menu_videos_stay')) }}</p>

            <details class="settings-details" @if(trim((string) ($type['seo_title'] ?? '').($type['seo_keywords'] ?? '').($type['seo_description'] ?? '')) !== '') open @endif>
                <summary>{{ admin_t('ui.seo_optional') }}</summary>
                <p class="muted field-hint">{{ $isArt ? admin_t('ui.seo_engine_section_hint') : admin_t('ui.seo_engine_tpl_hint') }}</p>
                <label for="type-seo-title">{{ admin_t('ui.title_label') }}</label>
                <input id="type-seo-title" type="text" name="seo_title" value="{{ $type['seo_title'] ?? '' }}" placeholder="{type} - {site}">
                <label for="type-seo-keywords">{{ admin_t('ui.seo_key') }}</label>
                <input id="type-seo-keywords" type="text" name="seo_keywords" value="{{ $type['seo_keywords'] ?? '' }}">
                <label for="type-seo-description">{{ admin_t('ui.seo_des') }}</label>
                <textarea id="type-seo-description" name="seo_description" rows="3">{{ $type['seo_description'] ?? '' }}</textarea>
            </details>

            @if($isArt)
                <details class="settings-details" @if(trim((string) ($type['tpl_list'] ?? '').($type['tpl_detail'] ?? '')) !== '') open @endif>
                    <summary>{{ admin_t('ui.tpl_optional') }}</summary>
                    <p class="muted field-hint">{{ admin_t('ui.tpl_hint') }}</p>
                    <div id="type-tpl-list-wrap">
                        <label for="type-tpl-list">{{ admin_t('ui.tpl_list') }}</label>
                        <input id="type-tpl-list" type="text" name="tpl_list" value="{{ $type['tpl_list'] ?? '' }}" placeholder="{{ admin_t('ui.ph_tpl_empty') }}">
                    </div>
                    <label for="type-tpl-detail">{{ admin_t('ui.tpl_detail') }}</label>
                    <input id="type-tpl-detail" type="text" name="tpl_detail" value="{{ $type['tpl_detail'] ?? '' }}" placeholder="{{ admin_t('ui.ph_tpl_empty') }}">
                </details>
            @endif

            <div class="form-actions">
                <button type="submit" class="btn" id="type-save">{{ admin_t('ui.save') }}</button>
                <button type="button" class="btn btn-muted" id="type-save-child">{{ admin_t('ui.save_and_child') }}</button>
                @if($isArt)
                    <button type="button" class="btn btn-muted" id="type-save-art">{{ admin_t('ui.save_and_art') }}</button>
                @elseif($isWebsite)
                    <a class="btn btn-muted" href="/admin/video/websites">{{ admin_t('ui.go_websites') }}</a>
                @endif
                <a class="btn btn-muted" href="{{ $base }}">{{ admin_t('ui.cancel') }}</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($jsLang, JSON_UNESCAPED_UNICODE);
    var form = document.getElementById('type-form');
    var isEdit = !!String(form.id.value || '').trim();
    var base = @json($base);

    function save(next) {
        var data = U.formData(form);
        if (!String(data.name || '').trim()) {
            U.toast(L.need_name, 'err');
            document.getElementById('type-name').focus();
            return;
        }
        if (!isEdit) delete data.id;
        U.loading(true);
        U.post(base + '/save', data).then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) {
                U.toast((res && res.msg) || L.save_fail, 'err');
                return;
            }
            var id = (res.data && res.data.id) || data.id;
            U.toast(L.saved, 'ok');
            if (next === 'child' && id) {
                location.href = base + '/create?parent_id=' + encodeURIComponent(id);
                return;
            }
            if (next === 'write' && id) {
                location.href = '/admin/video/arts/create?type_id=' + encodeURIComponent(id);
                return;
            }
            location.href = base;
        }).catch(function () {
            U.loading(false);
            U.toast(L.save_fail, 'err');
        });
    }
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        save('');
    });
    document.getElementById('type-save-child').addEventListener('click', function () { save('child'); });
    var writeBtn = document.getElementById('type-save-art');
    if (writeBtn) writeBtn.addEventListener('click', function () { save('write'); });

    var kindSel = document.getElementById('type-kind');
    var hints = { list: L.kind_list, hub: L.kind_hub, single: L.kind_single, link: L.kind_link };
    function syncKind() {
        var kind = kindSel ? kindSel.value : 'list';
        var jump = document.getElementById('type-jump-wrap');
        var page = document.getElementById('type-page-wrap');
        var tplList = document.getElementById('type-tpl-list-wrap');
        var hint = document.getElementById('type-kind-hint');
        if (jump) jump.hidden = kind !== 'link';
        if (page) page.hidden = kind !== 'list';
        if (tplList) tplList.hidden = kind === 'link' || kind === 'single';
        if (hint) hint.textContent = hints[kind] || '';
        if (writeBtn) writeBtn.hidden = kind === 'hub' || kind === 'link';
    }
    if (kindSel) {
        kindSel.addEventListener('change', syncKind);
        syncKind();
    }
    var picInput = document.getElementById('type-pic');
    function syncPic(url) {
        var img = document.getElementById('type-pic-img');
        var preview = document.getElementById('type-pic-preview');
        url = String(url || '').trim();
        if (!img || !preview) return;
        if (!url) {
            img.removeAttribute('src');
            preview.hidden = true;
            return;
        }
        img.onload = function () { preview.hidden = false; };
        img.onerror = function () { preview.hidden = true; };
        if (img.getAttribute('src') !== url) img.src = url;
        else preview.hidden = false;
    }
    if (picInput) picInput.addEventListener('input', function () { syncPic(picInput.value); });
    U.on('#type-pic-clear', 'click', function () {
        if (!picInput) return;
        picInput.value = '';
        syncPic('');
    });
    U.on('#type-pic-upload', 'click', function () {
        U.pickFile('image/*').then(function (file) {
            if (!file) return;
            U.loading(true);
            return U.upload(file).then(function (res) {
                U.loading(false);
                if (res && res.code === 0 && res.data && res.data.url) {
                    picInput.value = res.data.url;
                    syncPic(res.data.url);
                    U.toast(L.uploaded, 'ok');
                } else U.toast((res && res.msg) || L.upload_fail, 'err');
            });
        });
    });
})();
</script>
@endpush
