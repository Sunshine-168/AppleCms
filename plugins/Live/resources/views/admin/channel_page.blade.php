@extends('admin.layouts.inner')
@section('title', $isEdit ? admin_t('live.page_edit_channel') : admin_t('live.page_new_channel'))

@php
    $channel = is_array($channel ?? null) ? $channel : [];
    $isEdit = (bool) ($isEdit ?? false);
    $categories = $categories ?? collect();
    $hasRecommend = (bool) ($hasRecommend ?? false);
    $title = (string) ($channel['title'] ?? '');
    $sub = (string) ($channel['sub'] ?? '');
    $slug = (string) ($channel['slug'] ?? '');
    $cover = trim((string) ($channel['cover'] ?? ''));
    $urls = (string) ($channel['urls'] ?? '');
    $cateId = (int) ($channel['cate_id'] ?? 0);
    $hits = (int) ($channel['hits'] ?? 0);
    $recommend = (int) ($channel['recommend'] ?? 0);
    $sort = (int) ($channel['sort'] ?? 0);
    $status = (string) ($channel['status'] ?? '1');
    $remarks = (string) ($channel['remarks'] ?? '');
    $content = (string) ($channel['content'] ?? '');
    $id = (int) ($channel['id'] ?? 0);
    $frontUrl = trim((string) ($channel['front_url'] ?? ''));
    $back = ((string) $status === '0' && ! $isEdit) ? '/admin/video/lives?desk=pending' : '/admin/video/lives';
    $pageJsLang = [
        'need_title' => admin_t('live.need_title'),
        'saved' => admin_t('ui.saved'),
        'save_fail' => admin_t('ui.save_fail'),
    ];
@endphp

@section('plain')
<div class="card card-panel">
    <div class="card-header">
        <span>{{ $isEdit ? admin_t('live.page_edit_channel') : admin_t('live.page_new_channel') }}@if($isEdit && $title !== '') <em>{{ $title }}</em>@endif</span>
        <div>
            @if($isEdit && $frontUrl !== '')
                <a class="btn btn-muted btn-sm" href="{{ $frontUrl }}" target="_blank" rel="noopener">{{ admin_t('ui.front') }}</a>
            @endif
            <a class="btn btn-muted btn-sm" href="{{ $back }}">{{ admin_t('live.back_channels') }}</a>
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">{{ admin_t('live.channel_page_lead') }}</p>
        <form class="admin-form tag-form" id="live-channel-form">
            <input type="hidden" name="id" value="{{ $isEdit ? $id : '' }}">
            <input type="hidden" name="desk" value="channels">
            <input type="hidden" name="play_from" value="hls">

            <h3>{{ admin_t('ui.basic') }}</h3>
            <div class="form-field">
                <label for="ch-title">{{ admin_t('live.channel_name') }}</label>
                <input id="ch-title" class="entry-title" type="text" name="title" value="{{ $title }}" required autofocus placeholder="{{ admin_t('live.ph_channel_title') }}">
            </div>
            <div class="form-field">
                <label for="ch-sub">{{ admin_t('ui.subtitle') }}</label>
                <input id="ch-sub" type="text" name="sub" value="{{ $sub }}" placeholder="{{ admin_t('live.ph_subtitle') }}">
            </div>
            <div class="form-field">
                <label for="ch-cate">{{ admin_t('ui.types') }}</label>
                <select id="ch-cate" name="cate_id">
                    <option value="0">{{ admin_t('ui.uncategorized') }}</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected($cateId === (int) $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                <p class="muted field-hint">{!! str_replace(':link', '<a href="/admin/video/live-categories/create" target="_blank" rel="noopener">'.e(admin_t('live.new_category')).'</a>', e(admin_t('live.new_category_hint'))) !!}</p>
            </div>

            <div class="form-field">
                <label for="ch-cover">{{ admin_t('ui.cover') }}</label>
                <div class="field-inline">
                    <input id="ch-cover" type="text" name="cover" value="{{ $cover }}" placeholder="{{ admin_t('live.ph_cover') }}">
                    <button type="button" class="btn btn-sm" id="ch-cover-pick">{{ admin_t('ui.upload') }}</button>
                </div>
                <img class="img-preview" id="ch-cover-preview" alt="" @if($cover === '') style="display:none" @else src="{{ $cover }}" @endif>
                <p class="muted field-hint">{{ admin_t('live.cover_hint') }}</p>
            </div>

            <h3>{{ admin_t('ui.playback') }}</h3>
            <div class="form-field">
                <label for="ch-urls">{{ admin_t('live.play_urls') }}</label>
                <textarea id="ch-urls" name="urls" rows="6" placeholder="{{ admin_t('live.ph_urls_example') }}">{{ $urls }}</textarea>
                <p class="muted field-hint">{{ admin_t('live.play_urls_hint') }}</p>
            </div>

            <h3>{{ admin_t('ui.display') }}</h3>
            @if($hasRecommend)
                <div class="form-field">
                    <label for="ch-rec">{{ admin_t('ui.recommend_level') }}</label>
                    <input id="ch-rec" type="number" name="recommend" min="0" max="9" value="{{ $recommend }}">
                    <p class="muted field-hint">{{ admin_t('live.recommend_hint') }}</p>
                </div>
            @endif
            <div class="live-dialog-grid">
                <div class="form-field">
                    <label for="ch-sort">{{ admin_t('ui.sort') }}</label>
                    <input id="ch-sort" type="number" name="sort" value="{{ $sort }}">
                </div>
                <div class="form-field">
                    <label for="ch-status">{{ admin_t('ui.status') }}</label>
                    <select id="ch-status" name="status">
                        <option value="1" @selected($status === '1')>{{ admin_t('ui.on') }}</option>
                        <option value="0" @selected($status === '0')>{{ admin_t('live.pending_or_off') }}</option>
                    </select>
                </div>
            </div>
            <p class="muted field-hint">{{ admin_t('live.sort_hint') }}</p>
            <div class="form-field">
                <label for="ch-hits">{{ admin_t('ui.hits') }}</label>
                <input id="ch-hits" type="number" name="hits" min="0" value="{{ $hits }}">
                <p class="muted field-hint">{{ admin_t('live.hits_hint') }}</p>
            </div>
            <div class="form-field">
                <label for="ch-slug">{{ admin_t('live.url_slug') }}</label>
                <input id="ch-slug" type="text" name="slug" value="{{ $slug }}" placeholder="{{ admin_t('live.ph_slug') }}">
            </div>
            <div class="form-field">
                <label for="ch-remarks">{{ admin_t('ui.remarks') }}</label>
                <input id="ch-remarks" type="text" name="remarks" value="{{ $remarks }}" placeholder="{{ admin_t('live.ph_remarks') }}">
            </div>
            <div class="form-field">
                <label for="ch-content">{{ admin_t('ui.intro') }}</label>
                <textarea id="ch-content" name="content" rows="4" placeholder="{{ admin_t('ui.optional') }}">{{ $content }}</textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn" id="ch-save">{{ admin_t('ui.save') }}</button>
                <a class="btn btn-muted" href="{{ $back }}">{{ admin_t('ui.cancel') }}</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($pageJsLang, JSON_UNESCAPED_UNICODE);
    var form = document.getElementById('live-channel-form');
    var isEdit = !!String(form.id.value || '').trim();
    U.bindImageField(form, { input: '#ch-cover', btn: '#ch-cover-pick', preview: '#ch-cover-preview' });
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var data = U.formData(form);
        if (!String(data.title || '').trim()) { U.toast(L.need_title, 'err'); return; }
        if (!isEdit) delete data.id;
        U.loading(true);
        U.post('/admin/video/lives/save', data).then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.save_fail, 'err'); return; }
            U.toast(L.saved, 'ok');
            var id = (res.data && res.data.id) || data.id;
            if (!isEdit && id) location.href = '/admin/video/live-channels/' + encodeURIComponent(id) + '/edit';
            else location.href = '/admin/video/lives';
        }).catch(function () { U.loading(false); U.toast(L.save_fail, 'err'); });
    });
})();
</script>
@endpush
