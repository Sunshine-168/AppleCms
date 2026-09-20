@extends('admin.layouts.inner')
@php
    $type = is_array($type ?? null) ? $type : [];
    $isEdit = (bool) ($isEdit ?? false);
    $parents = is_array($parents ?? null) ? $parents : [];
    $parent = is_array($parent ?? null) ? $parent : null;
    $name = (string) ($type['name'] ?? '');
    $parentId = (string) ($type['parent_id'] ?? '0');
    $status = (string) ($type['status'] ?? '1');
    $parentName = (string) ($parent['name'] ?? '');
    $base = '/admin/video/manga-types';
    $title = $isEdit ? admin_t('ui.edit_type') : ($parentName !== '' ? admin_t('ui.add_child') : admin_t('ui.add_type'));
    $jsLang = [
        'need_name' => admin_t('ui.need_name'),
        'save_fail' => admin_t('ui.save_fail'),
        'saved' => admin_t('ui.saved'),
        'uploaded' => admin_t('ui.uploaded'),
        'upload_fail' => admin_t('ui.upload_fail'),
    ];
@endphp
@section('title', $title)

@section('plain')
<div class="card card-panel type-form-page">
    <div class="card-header">
        <span>{{ $title }}@if($isEdit && $name !== '') <em>{{ $name }}</em>@endif</span>
        <a class="btn btn-muted btn-sm" href="{{ $base }}">{{ admin_t('ui.back_types') }}</a>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">
            @if($isEdit)
                {{ admin_t('ui.type_form_edit_manga') }}
            @elseif($parentName !== '')
                {{ admin_t('ui.type_form_child_lead', ['name' => $parentName]) }}
            @else
                {{ admin_t('ui.type_form_new_manga') }}
            @endif
        </p>

        <form class="admin-form type-form" id="manga-type-form">
            <input type="hidden" name="id" value="{{ $isEdit ? (int) ($type['id'] ?? 0) : '' }}">

            <h3>{{ admin_t('ui.this_type') }}</h3>
            <label for="type-name">{{ admin_t('ui.name') }}</label>
            <input id="type-name" type="text" name="name" value="{{ $name }}" placeholder="{{ admin_t('ui.ph_type_manga') }}" required>
            <label for="type-slug">{{ admin_t('ui.url_alias') }}</label>
            <input id="type-slug" type="text" name="slug" value="{{ $type['slug'] ?? '' }}" placeholder="{{ admin_t('ui.ph_type_slug_ex', ['slug' => 'shonen']) }}">
            <p class="muted field-hint">{{ admin_t('ui.slug_in_filter') }}</p>

            <label for="type-parent">{{ admin_t('ui.parent') }}</label>
            <select id="type-parent" name="parent_id">
                <option value="0" @selected($parentId === '0' || $parentId === '')>{{ admin_t('ui.top_unattached') }}</option>
                @foreach($parents as $item)
                    <option value="{{ $item['id'] }}" @selected($parentId === (string) $item['id'])>
                        {{ str_repeat('└ ', max((int) ($item['depth'] ?? 0), 0)) }}{{ $item['name'] }}
                    </option>
                @endforeach
            </select>
            <p class="muted field-hint">{{ admin_t('ui.parent_nest_hint') }}</p>

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

            <h3>{{ admin_t('ui.display') }}</h3>
            <label for="type-sort">{{ admin_t('ui.sort') }}</label>
            <input id="type-sort" type="number" name="sort" value="{{ $type['sort'] ?? 0 }}">
            <p class="muted field-hint">{{ admin_t('ui.sort_sibling_hint') }}</p>
            <label for="type-page-size">{{ admin_t('ui.page_size') }}</label>
            <input id="type-page-size" type="number" name="page_size" min="0" max="100" value="{{ (int) ($type['page_size'] ?? 0) }}">
            <p class="muted field-hint">{{ admin_t('ui.page_size_manga_hint') }}</p>
            <input type="hidden" name="status" value="0">
            <label class="inline">
                <input type="checkbox" name="status" value="1" @checked($status === '1')>
                {{ admin_t('ui.show_on_front') }}
            </label>
            <p class="muted field-hint">{{ admin_t('ui.hide_menu_works_stay') }}</p>

            <details class="settings-details" @if(trim((string) ($type['seo_title'] ?? '').($type['seo_keywords'] ?? '').($type['seo_description'] ?? '')) !== '') open @endif>
                <summary>{{ admin_t('ui.seo_optional') }}</summary>
                <p class="muted field-hint">{{ admin_t('ui.seo_engine_hint') }}</p>
                <label for="type-seo-title">{{ admin_t('ui.title_label') }}</label>
                <input id="type-seo-title" type="text" name="seo_title" value="{{ $type['seo_title'] ?? '' }}" placeholder="{type} - {site}">
                <label for="type-seo-keywords">{{ admin_t('ui.seo_key') }}</label>
                <input id="type-seo-keywords" type="text" name="seo_keywords" value="{{ $type['seo_keywords'] ?? '' }}">
                <label for="type-seo-description">{{ admin_t('ui.seo_des') }}</label>
                <textarea id="type-seo-description" name="seo_description" rows="3">{{ $type['seo_description'] ?? '' }}</textarea>
            </details>

            <div class="form-actions">
                <button type="submit" class="btn" id="type-save">{{ admin_t('ui.save') }}</button>
                <button type="button" class="btn btn-muted" id="type-save-child">{{ admin_t('ui.save_and_child') }}</button>
                <button type="button" class="btn btn-muted" id="type-save-work">{{ admin_t('ui.save_and_work') }}</button>
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
    var form = document.getElementById('manga-type-form');
    var isEdit = !!String(form.id.value || '').trim();
    var base = @json($base);
    var api = '/admin/video/manga_types';

    function save(next) {
        var data = U.formData(form);
        if (!String(data.name || '').trim()) {
            U.toast(L.need_name, 'err');
            document.getElementById('type-name').focus();
            return;
        }
        if (!isEdit) delete data.id;
        U.loading(true);
        U.post(api + '/save', data).then(function (res) {
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
            if (next === 'work' && id) {
                location.href = '/admin/video/mangas?type_id=' + encodeURIComponent(id);
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
    document.getElementById('type-save-work').addEventListener('click', function () { save('work'); });

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
