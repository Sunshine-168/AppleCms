@extends('admin.layouts.inner')
@section('title', $isEdit ? admin_t('ui.edit_work') : admin_t('ui.add_work'))

@php
    $work = is_array($work ?? null) ? $work : [];
    $isEdit = (bool) ($isEdit ?? false);
    $types = is_array($types ?? null) ? $types : [];
    $selectedTags = is_array($selectedTags ?? null) ? $selectedTags : [];
    $selectedAuthors = is_array($selectedAuthors ?? null) ? $selectedAuthors : [];
    $tagsReady = (bool) ($tagsReady ?? true);
    $authorsReady = (bool) ($authorsReady ?? true);
    $title = (string) ($work['title'] ?? '');
    $typeId = (int) ($work['type_id'] ?? 0);
    $cover = trim((string) ($work['cover'] ?? ''));
    $serialize = (string) ($work['serialize'] ?? '0');
    $recommend = (string) ($work['recommend'] ?? '0');
    $yid = (string) ($work['yid'] ?? '0');
    $status = (string) ($work['status'] ?? '1');
    $remarks = (string) ($work['remarks'] ?? '');
    $content = (string) ($work['content'] ?? '');
    $hits = (int) ($work['hits'] ?? 0);
    $sort = (int) ($work['sort'] ?? 0);
    $frontUrl = trim((string) ($work['front_url'] ?? ''));
    $workId = (int) ($work['id'] ?? 0);
    $back = ((string) ($yid) === '1' && ! $isEdit) ? '/admin/video/mangas?desk=pending' : '/admin/video/mangas';
@endphp

@section('plain')
<div class="card card-panel">
    <div class="card-header">
        <span>{{ $isEdit ? admin_t('ui.edit_work') : admin_t('ui.add_work') }}@if($isEdit && $title !== '') <em>{{ $title }}</em>@endif</span>
        <div>
            @if($isEdit && $workId > 0)
                <a class="btn btn-muted btn-sm" href="/admin/video/mangas?desk=work&manga_id={{ $workId }}">{{ admin_t('ui.manage_chapters') }}</a>
            @endif
            <a class="btn btn-muted btn-sm" href="{{ $back }}">{{ admin_t('ui.back_works') }}</a>
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">{{ admin_t('manga.work_form_lead') }}</p>
        <form class="admin-form tag-form" id="manga-work-form">
            <input type="hidden" name="id" value="{{ $isEdit ? $workId : '' }}">

            <h3>{{ admin_t('ui.section_basic') }}</h3>
            <label for="work-title">{{ admin_t('ui.name') }}</label>
            <input id="work-title" class="entry-title" type="text" name="title" value="{{ $title }}" required autofocus>

            <label for="work-type">{{ admin_t('ui.types') }}</label>
            <select id="work-type" name="type_id">
                <option value="0">{{ admin_t('ui.uncategorized') }}</option>
                @foreach($types as $type)
                    <option value="{{ $type['id'] }}" @selected($typeId === (int) $type['id'])>{{ $type['label'] ?? $type['name'] }}</option>
                @endforeach
            </select>
            <p class="muted field-hint">{{ admin_t('ui.type_one_hint') }}</p>

            <label>{{ admin_t('ui.authors') }}</label>
            <div class="pick-field" id="work-author-pick"
                 data-ready="{{ $authorsReady ? '1' : '0' }}"
                 data-search="/admin/video/manga-authors/list"
                 data-create="/admin/video/manga-authors/save"
                 data-browse="1"
                 data-placeholder="{{ admin_t('ui.pick_author_ph') }}"
                 data-empty="{{ admin_t('ui.pick_authors_empty_pre') }}<a href=&quot;/admin/video/manga-authors&quot; target=&quot;_blank&quot; rel=&quot;noopener&quot;>{{ admin_t('ui.authors_desk') }}</a>{{ admin_t('ui.period_end') }}"
                 data-selected='@json($selectedAuthors, JSON_UNESCAPED_UNICODE)'></div>
            <p class="muted field-hint">{{ admin_t('ui.no_spread_pick') }}</p>

            <label for="work-cover">{{ admin_t('ui.cover') }}</label>
            <div class="field-inline">
                <input id="work-cover" type="text" name="cover" value="{{ $cover }}" placeholder="{{ admin_t('ui.ph_image_url') }}">
                <button type="button" class="btn btn-sm" id="work-cover-pick">{{ admin_t('ui.upload') }}</button>
            </div>
            <img class="img-preview" id="work-cover-preview" alt="" @if($cover === '') style="display:none" @else src="{{ $cover }}" @endif>

            <label for="work-serialize">{{ admin_t('ui.serialize') }}</label>
            <select id="work-serialize" name="serialize">
                <option value="0" @selected($serialize === '0')>{{ admin_t('ui.serialize_ongoing') }}</option>
                <option value="1" @selected($serialize === '1')>{{ admin_t('ui.serialize_done') }}</option>
            </select>

            <h3>{{ admin_t('ui.section_tags_recommend') }}</h3>
            <label>{{ admin_t('ui.tags') }}</label>
            <div class="pick-field" id="work-tag-pick"
                 data-ready="{{ $tagsReady ? '1' : '0' }}"
                 data-search="/admin/video/manga-tags/list"
                 data-create="/admin/video/manga-tags/save"
                 data-browse="1"
                 data-placeholder="{{ admin_t('ui.pick_tag_ph') }}"
                 data-empty="{{ admin_t('ui.pick_tags_empty_pre') }}<a href=&quot;/admin/video/manga-tags&quot; target=&quot;_blank&quot; rel=&quot;noopener&quot;>{{ admin_t('ui.tags_desk') }}</a>{{ admin_t('ui.period_end') }}"
                 data-selected='@json($selectedTags, JSON_UNESCAPED_UNICODE)'></div>
            <p class="muted field-hint">{{ admin_t('ui.manga_tags_not_art') }}</p>

            <label for="work-recommend">{{ admin_t('ui.recommend') }}</label>
            <select id="work-recommend" name="recommend">
                <option value="0" @selected($recommend === '0')>{{ admin_t('ui.no') }}</option>
                <option value="1" @selected($recommend === '1')>{{ admin_t('ui.yes') }}</option>
            </select>
            <p class="muted field-hint">{{ admin_t('ui.recommend_home_hint') }}</p>

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
                <button class="btn" type="submit">{{ $isEdit ? admin_t('ui.save') : admin_t('ui.create_work') }}</button>
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
    var form = document.getElementById('manga-work-form');
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
        if (!String(data.title || '').trim()) {
            U.toast(@json(admin_t('ui.please_fill_name')), 'err');
            form.querySelector('[name=title]').focus();
            return;
        }
        data.author_ids = authorPick.ids();
        data.author_extra = '';
        data.tag_ids = tagPick.ids();
        data.tag_extra = '';
        delete data['author_ids[]'];
        delete data['tag_ids[]'];
        U.loading(true);
        U.post('/admin/video/mangas/save', data).then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) {
                U.toast((res && res.msg) || @json(admin_t('ui.save_fail')), 'err');
                return;
            }
            var id = (res.data && res.data.id) || data.id;
            U.toast(isEdit ? @json(admin_t('ui.saved')) : @json(admin_t('ui.created')), 'ok');
            if (!isEdit && id) {
                location.href = '/admin/video/mangas/' + encodeURIComponent(id) + '/edit';
            }
        }).catch(function () {
            U.loading(false);
            U.toast(@json(admin_t('ui.save_fail')), 'err');
        });
    });
})();
</script>
@endpush
