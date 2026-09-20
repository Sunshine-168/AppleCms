@extends('admin.layouts.inner')
@section('title', $isEdit ? admin_t('live.page_edit_category') : admin_t('live.page_new_category'))

@php
    $category = is_array($category ?? null) ? $category : [];
    $isEdit = (bool) ($isEdit ?? false);
    $name = (string) ($category['name'] ?? '');
    $slug = (string) ($category['slug'] ?? '');
    $pic = trim((string) ($category['pic'] ?? ''));
    $sort = (int) ($category['sort'] ?? 0);
    $status = (string) ($category['status'] ?? '1');
    $id = (int) ($category['id'] ?? 0);
    $count = (int) ($category['channel_count'] ?? 0);
    $back = '/admin/video/lives?desk=categories';
    $pageJsLang = [
        'need_name' => admin_t('live.need_name'),
        'saved' => admin_t('ui.saved'),
        'save_fail' => admin_t('ui.save_fail'),
    ];
@endphp

@section('plain')
<div class="card card-panel">
    <div class="card-header">
        <span>{{ $isEdit ? admin_t('live.page_edit_category') : admin_t('live.page_new_category') }}@if($isEdit && $name !== '') <em>{{ $name }}</em>@endif</span>
        <a class="btn btn-muted btn-sm" href="{{ $back }}">{{ admin_t('live.back_categories') }}</a>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">{{ admin_t('live.cate_page_lead') }}</p>
        @if($isEdit && $count > 0)
            <p class="muted field-hint">{!! str_replace(':link', '<a href="/admin/video/lives?cate_id='.$id.'">'.e(admin_t('live.channels_n', ['n' => $count])).'</a>', e(admin_t('live.cate_linked'))) !!}</p>
        @endif
        <form class="admin-form tag-form" id="live-category-form">
            <input type="hidden" name="id" value="{{ $isEdit ? $id : '' }}">
            <input type="hidden" name="desk" value="categories">

            <div class="form-field">
                <label for="cate-name">{{ admin_t('live.category_name') }}</label>
                <input id="cate-name" class="entry-title" type="text" name="name" value="{{ $name }}" required autofocus placeholder="{{ admin_t('live.ph_category_name') }}">
                <p class="muted field-hint">{{ admin_t('live.category_hint') }}</p>
            </div>

            <div class="form-field">
                <label for="cate-slug">{{ admin_t('ui.slug') }}</label>
                <input id="cate-slug" type="text" name="slug" value="{{ $slug }}" placeholder="{{ admin_t('live.ph_slug') }}">
                <p class="muted field-hint">{{ admin_t('live.slug_hint') }}</p>
            </div>

            <div class="form-field">
                <label for="cate-pic">{{ admin_t('ui.image') }}</label>
                <div class="field-inline">
                    <input id="cate-pic" type="text" name="pic" value="{{ $pic }}" placeholder="{{ admin_t('live.ph_cover') }}">
                    <button type="button" class="btn btn-sm" id="cate-pic-pick">{{ admin_t('ui.upload') }}</button>
                </div>
                <img class="img-preview" id="cate-pic-preview" alt="" @if($pic === '') style="display:none" @else src="{{ $pic }}" @endif>
                <p class="muted field-hint">{{ admin_t('live.pic_hint') }}</p>
            </div>

            <div class="live-dialog-grid">
                <div class="form-field">
                    <label for="cate-sort">{{ admin_t('ui.sort') }}</label>
                    <input id="cate-sort" type="number" name="sort" value="{{ $sort }}">
                </div>
                <div class="form-field">
                    <label for="cate-status">{{ admin_t('ui.status') }}</label>
                    <select id="cate-status" name="status">
                        <option value="1" @selected($status === '1')>{{ admin_t('ui.enabled') }}</option>
                        <option value="0" @selected($status === '0')>{{ admin_t('ui.disabled') }}</option>
                    </select>
                </div>
            </div>
            <p class="muted field-hint">{{ admin_t('live.sort_front_hint') }}</p>

            <div class="form-actions">
                <button type="submit" class="btn">{{ admin_t('ui.save') }}</button>
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
    var form = document.getElementById('live-category-form');
    var isEdit = !!String(form.id.value || '').trim();
    U.bindImageField(form, { input: '#cate-pic', btn: '#cate-pic-pick', preview: '#cate-pic-preview' });
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var data = U.formData(form);
        if (!String(data.name || '').trim()) { U.toast(L.need_name, 'err'); return; }
        if (!isEdit) delete data.id;
        U.loading(true);
        U.post('/admin/video/lives/save', data).then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.save_fail, 'err'); return; }
            U.toast(L.saved, 'ok');
            var id = (res.data && res.data.id) || data.id;
            if (!isEdit && id) location.href = '/admin/video/live-categories/' + encodeURIComponent(id) + '/edit';
            else location.href = '/admin/video/lives?desk=categories';
        }).catch(function () { U.loading(false); U.toast(L.save_fail, 'err'); });
    });
})();
</script>
@endpush
