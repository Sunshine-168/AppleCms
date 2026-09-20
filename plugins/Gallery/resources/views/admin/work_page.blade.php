@extends('admin.layouts.inner')
@section('title', $isEdit ? admin_t('ui.edit_gallery') : admin_t('ui.add_gallery'))

@php
    $work = is_array($work ?? null) ? $work : [];
    $isEdit = (bool) ($isEdit ?? false);
    $types = $types ?? collect();
    $selectedTags = is_array($selectedTags ?? null) ? $selectedTags : [];
    $selectedAuthors = is_array($selectedAuthors ?? null) ? $selectedAuthors : [];
    $tagsReady = (bool) ($tagsReady ?? true);
    $authorsReady = (bool) ($authorsReady ?? true);
    $title = (string) ($work['title'] ?? '');
    $typeId = (int) ($work['type_id'] ?? 0);
    $cover = trim((string) ($work['cover'] ?? ''));
    $author = (string) ($work['author'] ?? '');
    $tags = (string) ($work['tags'] ?? '');
    $yid = (string) ($work['yid'] ?? '0');
    $status = (string) ($work['status'] ?? '1');
    $remarks = (string) ($work['remarks'] ?? '');
    $content = (string) ($work['content'] ?? '');
    $hits = (int) ($work['hits'] ?? 0);
    $sort = (int) ($work['sort'] ?? 0);
    $workId = (int) ($work['id'] ?? 0);
    $frontUrl = trim((string) ($work['front_url'] ?? ''));
    $back = ((string) $yid === '1' && ! $isEdit) ? '/admin/video/galleries?desk=pending' : '/admin/video/galleries';
@endphp

@section('plain')
<div class="card card-panel">
    <div class="card-header">
        <span>{{ $isEdit ? admin_t('ui.edit_gallery') : admin_t('ui.add_gallery') }}@if($isEdit && $title !== '') <em>{{ $title }}</em>@endif</span>
        <div>
            @if($isEdit && $workId > 0)
                <a class="btn btn-muted btn-sm" href="/admin/video/galleries?desk=pics">{{ admin_t('ui.manage_pics') }}</a>
            @endif
            <a class="btn btn-muted btn-sm" href="{{ $back }}">{{ admin_t('ui.back_galleries') }}</a>
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">{{ admin_t('ui.gallery_work_lead') }}</p>
        <form class="admin-form tag-form" id="gallery-work-form">
            <input type="hidden" name="id" value="{{ $isEdit ? $workId : '' }}">
            <input type="hidden" name="desk" value="works">

            <h3>{{ admin_t('ui.section_basic') }}</h3>
            <label for="work-title">{{ admin_t('ui.name') }}</label>
            <input id="work-title" class="entry-title" type="text" name="title" value="{{ $title }}" required autofocus>

            <label>{{ admin_t('ui.author_model') }}</label>
            <div class="pick-field" id="work-author-pick"
                 data-ready="{{ $authorsReady ? '1' : '0' }}"
                 data-search="/admin/video/gallery-authors/list"
                 data-create="/admin/video/gallery-authors/save"
                 data-browse="1"
                 data-placeholder="{{ admin_t('ui.pick_author_model_ph') }}"
                 data-empty="{{ admin_t('ui.pick_authors_empty_pre') }}<a href=&quot;/admin/video/gallery-authors&quot; target=&quot;_blank&quot; rel=&quot;noopener&quot;>{{ admin_t('ui.authors_desk') }}</a>{{ admin_t('ui.period_end') }}"
                 data-selected='@json($selectedAuthors, JSON_UNESCAPED_UNICODE)'></div>

            <label for="work-type">{{ admin_t('ui.types') }}</label>
            <select id="work-type" name="type_id">
                <option value="0">{{ admin_t('ui.uncategorized') }}</option>
                @foreach($types as $type)
                    <option value="{{ $type->id }}" @selected($typeId === (int) $type->id)>{{ $type->name }}</option>
                @endforeach
            </select>

            <label for="work-cover">{{ admin_t('ui.cover') }}</label>
            <div class="field-inline">
                <input id="work-cover" type="text" name="cover" value="{{ $cover }}" placeholder="{{ admin_t('ui.ph_image_url') }}">
                <button type="button" class="btn btn-sm" id="work-cover-pick">{{ admin_t('ui.upload') }}</button>
            </div>
            <img class="img-preview" id="work-cover-preview" alt="" @if($cover === '') style="display:none" @else src="{{ $cover }}" @endif>

            <h3>{{ admin_t('ui.section_tags') }}</h3>
            <label>{{ admin_t('ui.tags') }}</label>
            <div class="pick-field" id="work-tag-pick"
                 data-ready="{{ $tagsReady ? '1' : '0' }}"
                 data-search="/admin/video/gallery-tags/list"
                 data-create="/admin/video/gallery-tags/save"
                 data-browse="1"
                 data-placeholder="{{ admin_t('ui.pick_tag_ph_short') }}"
                 data-empty="{{ admin_t('ui.pick_tags_empty_pre') }}<a href=&quot;/admin/video/gallery-tags&quot; target=&quot;_blank&quot; rel=&quot;noopener&quot;>{{ admin_t('ui.tags_desk') }}</a>{{ admin_t('ui.period_end') }}"
                 data-selected='@json($selectedTags, JSON_UNESCAPED_UNICODE)'></div>
            <input type="hidden" name="tags" id="work-tags" value="{{ $tags }}">
            <p class="muted field-hint">{{ admin_t('ui.tags_sync_hint_short') }}</p>

            <h3>{{ admin_t('ui.publish') }}</h3>
            <label for="work-yid">{{ admin_t('ui.audit') }}</label>
            <select id="work-yid" name="yid">
                <option value="0" @selected($yid === '0')>{{ admin_t('ui.audited') }}</option>
                <option value="1" @selected($yid === '1')>{{ admin_t('ui.pending') }}</option>
            </select>
            <label for="work-status">{{ admin_t('ui.status') }}</label>
            <select id="work-status" name="status">
                <option value="1" @selected($status === '1')>{{ admin_t('ui.on') }}</option>
                <option value="0" @selected($status === '0')>{{ admin_t('ui.off') }}</option>
            </select>
            <label for="work-remarks">{{ admin_t('ui.remarks') }}</label>
            <input id="work-remarks" type="text" name="remarks" value="{{ $remarks }}">
            <label for="work-content">{{ admin_t('ui.intro') }}</label>
            <textarea id="work-content" name="content" rows="6">{{ $content }}</textarea>
            <label for="work-hits">{{ admin_t('ui.hits_views') }}</label>
            <input id="work-hits" type="number" name="hits" min="0" value="{{ $hits }}">
            <p class="muted field-hint">{{ admin_t('ui.hits_auto_hint') }}</p>
            <label for="work-sort">{{ admin_t('ui.sort') }}</label>
            <input id="work-sort" type="number" name="sort" value="{{ $sort }}">

            <div class="entry-save">
                <button class="btn" type="submit">{{ $isEdit ? admin_t('ui.save') : admin_t('ui.create_gallery') }}</button>
                <a class="btn btn-muted" href="{{ $back }}">{{ admin_t('ui.cancel') }}</a>
                @if($isEdit && $frontUrl !== '')
                    <a class="btn btn-muted" href="{{ $frontUrl }}" target="_blank" rel="noopener">{{ admin_t('ui.front') }}</a>
                @endif
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var form = document.getElementById('gallery-work-form');
    if (!U || !form) return;
    var isEdit = !!String(form.querySelector('input[name="id"]').value || '').trim();
    U.bindImageField(form, {
        input: '[name=cover]',
        btn: '#work-cover-pick',
        preview: '#work-cover-preview'
    });
    var authorPick = U.bindPickField(document.getElementById('work-author-pick'));
    var tagPick = U.bindPickField(document.getElementById('work-tag-pick'));
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var data = U.formData(form);
        data.author_ids = authorPick.ids();
        data.tag_ids = tagPick.ids();
        delete data['author_ids[]'];
        delete data['tag_ids[]'];
        if (!String(data.title || '').trim()) {
            U.toast(@json(admin_t('ui.please_fill_name'), JSON_UNESCAPED_UNICODE), 'err');
            form.querySelector('[name=title]').focus();
            return;
        }
        data.desk = 'works';
        U.loading(true);
        U.post('/admin/video/galleries/save', data).then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) {
                U.toast((res && res.msg) || @json(admin_t('ui.save_fail'), JSON_UNESCAPED_UNICODE), 'err');
                return;
            }
            var id = (res.data && res.data.id) || data.id;
            U.toast(isEdit ? @json(admin_t('ui.saved'), JSON_UNESCAPED_UNICODE) : @json(admin_t('ui.created'), JSON_UNESCAPED_UNICODE), 'ok');
            if (!isEdit && id) {
                location.href = '/admin/video/galleries/' + encodeURIComponent(id) + '/edit';
            }
        }).catch(function () {
            U.loading(false);
            U.toast(@json(admin_t('ui.save_fail'), JSON_UNESCAPED_UNICODE), 'err');
        });
    });
})();
</script>
@endpush
