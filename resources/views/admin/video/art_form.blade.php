@extends('admin.layouts.inner')
@section('title', $isEdit ? admin_t('ui.edit_art') : admin_t('ui.write_art'))

@php
    $art = is_array($art ?? null) ? $art : [];
    $isEdit = (bool) ($isEdit ?? false);
    $types = is_array($types ?? null) ? $types : [];
    $title = (string) ($art['title'] ?? '');
    $blurb = (string) ($art['blurb'] ?? '');
    $cover = trim((string) ($art['cover'] ?? ''));
    $content = (string) ($art['content'] ?? '');
    $typeId = (string) ($art['type_id'] ?? '0');
    $status = (string) ($art['status'] ?? '1');
    $hits = (int) ($art['hits'] ?? 0);
    $sort = (int) ($art['sort'] ?? 0);
    $author = (string) ($art['author'] ?? '');
    $source = (string) ($art['source'] ?? '');
    $tag = (string) ($art['tag'] ?? '');
    $tagExtra = (string) ($art['tag_extra'] ?? '');
    $selectedTags = is_array($selectedTags ?? null) ? $selectedTags : [];
    $tagsReady = (bool) ($tagsReady ?? false);
    $seoTitle = (string) ($art['seo_title'] ?? '');
    $seoKey = (string) ($art['seo_key'] ?? '');
    $seoDes = (string) ($art['seo_des'] ?? '');
    $flagSet = array_values(array_filter(array_map('trim', explode(',', (string) ($art['flags'] ?? '')))));
    $publishedAt = (int) ($art['published_at'] ?? 0);
    $publishedLocal = $publishedAt > 0 ? date('Y-m-d\TH:i', $publishedAt) : '';
    $listed = ! empty($art['listed']);
    $frontUrl = trim((string) ($art['url'] ?? ''));
    if ($frontUrl === '' && $isEdit) {
        $frontUrl = '/art/'.(int) ($art['id'] ?? 0);
    }
    $artJsLang = [
        'uploaded' => admin_t('ui.uploaded'),
        'upload_fail' => admin_t('ui.upload_fail'),
        'please_fill_title' => admin_t('ui.please_fill_title'),
        'save_fail' => admin_t('ui.save_fail'),
        'saved' => admin_t('ui.saved'),
        'created' => admin_t('ui.created'),
    ];
@endphp

@section('plain')
<form class="admin-form entry-form art-form-page" id="art-form">
    <input type="hidden" name="id" value="{{ $isEdit ? (int) ($art['id'] ?? 0) : '' }}">
    <div class="entry-layout">
        <div class="entry-main">
            <div class="card card-panel">
                <div class="card-header">
                    <span>{{ $isEdit ? admin_t('ui.edit_art') : admin_t('ui.write_art') }}</span>
                </div>
                <div class="card-body">
                    <label for="art-title">{{ admin_t('ui.title_label') }}</label>
                    <input id="art-title" type="text" name="title" class="entry-title" value="{{ $title }}" placeholder="{{ admin_t('ui.ph_reader_title') }}" required>
                    <label for="art-blurb">{{ admin_t('ui.blurb') }}</label>
                    <textarea id="art-blurb" name="blurb" rows="3" placeholder="{{ admin_t('ui.ph_art_blurb') }}">{{ $blurb }}</textarea>
                    <label for="art-content">{{ admin_t('ui.body_text') }}</label>
                    <textarea id="art-content" name="content" class="cms-editor art-content" placeholder="{{ admin_t('ui.body_text') }}">{{ $content }}</textarea>
                    <details class="entry-seo">
                        <summary>{{ admin_t('ui.seo_pack') }}</summary>
                        <label for="art-seo-title">{{ admin_t('ui.seo_title') }}</label>
                        <input id="art-seo-title" type="text" name="seo_title" value="{{ $seoTitle }}" placeholder="{{ admin_t('ui.ph_seo_title') }}">
                        <label for="art-seo-key">{{ admin_t('ui.keywords') }}</label>
                        <input id="art-seo-key" type="text" name="seo_key" value="{{ $seoKey }}" placeholder="{{ admin_t('ui.ph_seo_key') }}">
                        <label for="art-seo-des">{{ admin_t('ui.description') }}</label>
                        <textarea id="art-seo-des" name="seo_des" rows="2" placeholder="{{ admin_t('ui.ph_seo_des') }}">{{ $seoDes }}</textarea>
                    </details>
                </div>
            </div>
        </div>
        <aside class="entry-aside">
            <div class="card card-panel">
                <div class="card-header"><span>{{ admin_t('ui.publish') }}</span></div>
                <div class="card-body">
                    <label for="art-status">{{ admin_t('ui.status') }}</label>
                    <select id="art-status" name="status">
                        <option value="1" @selected($status === '1')>{{ admin_t('ui.publish') }}</option>
                        <option value="0" @selected($status === '0')>{{ admin_t('ui.draft') }}</option>
                    </select>
                    <p class="muted field-hint">{{ admin_t('ui.draft_front_hint') }}</p>
                    <label for="art-published-at">{{ admin_t('ui.scheduled_publish') }}</label>
                    <input id="art-published-at" type="datetime-local" name="published_at" value="{{ $publishedLocal }}">
                    <p class="muted field-hint">{{ admin_t('ui.published_at_hint') }}</p>
                    <label for="art-hits">{{ admin_t('ui.hits') }}</label>
                    <input id="art-hits" type="number" name="hits" min="0" value="{{ $hits }}">
                    <p class="muted field-hint">{{ admin_t('ui.hits_auto_hint') }}</p>
                    <div class="entry-save">
                        <button class="btn" type="submit" id="art-save">{{ admin_t('ui.save') }}</button>
                        <a class="btn btn-muted" href="/admin/video/arts">{{ admin_t('ui.back_arts') }}</a>
                    </div>
                    @if($isEdit && $listed && $frontUrl !== '')
                        <p class="muted field-hint"><a href="{{ $frontUrl }}" target="_blank" rel="noopener">{{ admin_t('ui.front') }}</a></p>
                    @endif
                </div>
            </div>
            <div class="card card-panel">
                <div class="card-header"><span>{{ admin_t('ui.section_column_show') }}</span></div>
                <div class="card-body">
                    <label for="art-type">{{ admin_t('ui.column') }}</label>
                    <select id="art-type" name="type_id">
                        <option value="0">{{ admin_t('ui.loose_column') }}</option>
                        @foreach($types as $type)
                            <option value="{{ $type['id'] }}" @selected($typeId === (string) $type['id'])>{{ $type['name'] }}@if(!empty($type['kind_label']) && ($type['kind'] ?? 'list') !== 'list') · {{ $type['kind_label'] }}@endif</option>
                        @endforeach
                    </select>
                    @if($types === [])
                        <p class="muted field-hint">{!! str_replace(':link', '<a href="/admin/video/art-types/create">'.e(admin_t('ui.go_create_columns')).'</a>', e(admin_t('ui.empty_art_types_cta'))) !!}</p>
                    @else
                        <p class="muted field-hint">{{ admin_t('ui.art_type_pick_hint') }}</p>
                    @endif
                    <label for="art-cover">{{ admin_t('ui.cover') }}</label>
                    <div class="media-field">
                        <div class="media-preview" id="art-cover-preview" @if($cover === '') hidden @endif>
                            <img id="art-cover-img" src="{{ $cover }}" alt="{{ admin_t('ui.cover_preview') }}">
                            <button type="button" class="media-preview-clear" id="art-cover-clear" title="{{ admin_t('ui.remove_cover') }}">&times;</button>
                        </div>
                        <div class="cover-row">
                            <input id="art-cover" type="text" name="cover" value="{{ $cover }}" placeholder="{{ admin_t('ui.ph_image_url') }}">
                            <button type="button" class="btn btn-muted" id="art-cover-upload">{{ admin_t('ui.upload') }}</button>
                        </div>
                    </div>
                    <label for="art-author">{{ admin_t('ui.byline') }}</label>
                    <input id="art-author" type="text" name="author" value="{{ $author }}" placeholder="{{ admin_t('ui.ph_byline') }}">
                    <label for="art-source">{{ admin_t('ui.source_label') }}</label>
                    <input id="art-source" type="text" name="source" value="{{ $source }}" placeholder="{{ admin_t('ui.ph_source') }}">
                    <label>{{ admin_t('ui.tags') }}</label>
                    <div class="pick-field" id="art-tag-pick"
                         data-ready="{{ $tagsReady ? '1' : '0' }}"
                         data-search="/admin/video/art-tags/list"
                         data-create="/admin/video/art-tags/save"
                         data-browse="1"
                         data-placeholder="{{ admin_t('ui.pick_tag_ph') }}"
                         data-empty="{{ admin_t('ui.pick_tags_empty_pre') }}<a href=&quot;/admin/video/art-tags&quot; target=&quot;_blank&quot; rel=&quot;noopener&quot;>{{ admin_t('ui.tags_desk') }}</a>{{ admin_t('ui.period_end') }}"
                         data-selected='@json($selectedTags, JSON_UNESCAPED_UNICODE)'></div>
                    <p class="muted field-hint">{{ admin_t('ui.no_spread_tags') }}</p>
                </div>
            </div>
            <details class="card card-panel entry-aside-more">
                <summary>{{ admin_t('ui.more') }}</summary>
                <div class="card-body">
                    <label>{{ admin_t('ui.flag_attrs') }}</label>
                    <div class="choice-grid">
                        <label class="inline"><input type="checkbox" class="js-art-flag" name="flag_list[]" value="top" @checked(in_array('top', $flagSet, true))> {{ admin_t('ui.flag_top') }}</label>
                        <label class="inline"><input type="checkbox" class="js-art-flag" name="flag_list[]" value="recommend" @checked(in_array('recommend', $flagSet, true))> {{ admin_t('ui.recommend') }}</label>
                        <label class="inline"><input type="checkbox" class="js-art-flag" name="flag_list[]" value="hot" @checked(in_array('hot', $flagSet, true))> {{ admin_t('ui.flag_hot') }}</label>
                    </div>
                    <label for="art-sort">{{ admin_t('ui.sort') }}</label>
                    <input id="art-sort" type="number" name="sort" min="0" value="{{ $sort }}">
                    <p class="muted field-hint">{{ admin_t('ui.sort_id_hint') }}</p>
                </div>
            </details>
        </aside>
    </div>
</form>
@endsection

@include('admin.partials.editor-assets')

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($artJsLang, JSON_UNESCAPED_UNICODE);
    var form = document.getElementById('art-form');
    var idInput = form.querySelector('input[name="id"]');
    var isEdit = !!String(idInput && idInput.value || '').trim();
    var coverInput = document.getElementById('art-cover');
    var tagPick = U.bindPickField(document.getElementById('art-tag-pick'));

    function syncCover(url) {
        var img = document.getElementById('art-cover-img');
        var preview = document.getElementById('art-cover-preview');
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
    if (coverInput) coverInput.addEventListener('input', function () { syncCover(coverInput.value); });
    U.on('#art-cover-clear', 'click', function () {
        coverInput.value = '';
        syncCover('');
    });
    U.on('#art-cover-upload', 'click', function () {
        U.pickFile('image/*').then(function (file) {
            if (!file) return;
            U.loading(true);
            return U.upload(file).then(function (res) {
                U.loading(false);
                if (res && res.code === 0 && res.data && res.data.url) {
                    coverInput.value = res.data.url;
                    syncCover(res.data.url);
                    U.toast(L.uploaded, 'ok');
                } else U.toast((res && res.msg) || L.upload_fail, 'err');
            });
        });
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (window.tinymce) tinymce.triggerSave();
        var data = U.formData(form);
        if (!String(data.title || '').trim()) {
            U.toast(L.please_fill_title, 'err');
            document.getElementById('art-title').focus();
            return;
        }
        var flags = [];
        form.querySelectorAll('.js-art-flag:checked').forEach(function (el) {
            flags.push(el.value);
        });
        data.flags = flags.join(',');
        delete data['flag_list[]'];
        data.tag_ids = tagPick.ids();
        data.tag_extra = '';
        delete data['tag_ids[]'];
        if (!String(data.published_at || '').trim()) {
            data.published_at = 0;
        }
        U.loading(true);
        U.post('/admin/video/arts/save', data).then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) {
                U.toast((res && res.msg) || L.save_fail, 'err');
                return;
            }
            var id = (res.data && res.data.id) || data.id;
            U.toast(isEdit ? L.saved : L.created, 'ok');
            if (!isEdit && id) {
                location.href = '/admin/video/arts/' + encodeURIComponent(id) + '/edit';
            }
        }).catch(function () {
            U.loading(false);
            U.toast(L.save_fail, 'err');
        });
    });
})();
</script>
@endpush
