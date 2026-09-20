@extends('admin.layouts.inner')
@section('title', $isEdit ? admin_t('ui.edit_pic') : admin_t('ui.add_pic'))

@php
    $pic = is_array($pic ?? null) ? $pic : [];
    $isEdit = (bool) ($isEdit ?? false);
    $works = $works ?? collect();
    $galleryId = (int) ($pic['gallery_id'] ?? 0);
    $url = (string) ($pic['url'] ?? '');
    $title = (string) ($pic['title'] ?? '');
    $sort = (int) ($pic['sort'] ?? 0);
    $id = (int) ($pic['id'] ?? 0);
    $back = '/admin/video/galleries?desk=pics';
    $picJsLang = [
        'please_pick_gallery' => admin_t('ui.please_pick_gallery'),
        'please_fill_image_url' => admin_t('ui.please_fill_image_url'),
        'save_fail' => admin_t('ui.save_fail'),
        'saved' => admin_t('ui.saved'),
        'added' => admin_t('ui.added'),
    ];
@endphp

@section('plain')
<div class="card card-panel">
    <div class="card-header">
        <span>{{ $isEdit ? admin_t('ui.edit_pic') : admin_t('ui.add_pic') }}</span>
        <a class="btn btn-muted btn-sm" href="{{ $back }}">{{ admin_t('ui.back') }}</a>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">{{ admin_t('ui.pic_page_lead') }}</p>
        <form class="admin-form tag-form" id="gallery-pic-form">
            <input type="hidden" name="id" value="{{ $isEdit ? $id : '' }}">
            <input type="hidden" name="desk" value="pics">
            <label for="pic-gallery">{{ admin_t('gallery.title') }}</label>
            <select id="pic-gallery" name="gallery_id" required>
                <option value="">{{ admin_t('gallery.select_gallery') }}</option>
                @foreach($works as $work)
                    <option value="{{ $work->id }}" @selected($galleryId === (int) $work->id)>{{ $work->title }} (#{{ $work->id }})</option>
                @endforeach
            </select>
            <label for="pic-url">{{ admin_t('ui.ph_pic') }}</label>
            <div class="field-inline">
                <input id="pic-url" type="text" name="url" value="{{ $url }}" required placeholder="{{ admin_t('ui.ph_url_or_path') }}">
                <button type="button" class="btn btn-sm" id="pic-upload">{{ admin_t('ui.upload') }}</button>
            </div>
            <img class="img-preview" id="pic-preview" alt="" @if($url === '') style="display:none" @else src="{{ $url }}" @endif>
            <label for="pic-title">{{ admin_t('ui.title_label') }}</label>
            <input id="pic-title" type="text" name="title" value="{{ $title }}" placeholder="{{ admin_t('ui.optional') }}">
            <label for="pic-sort">{{ admin_t('ui.sort') }}</label>
            <input id="pic-sort" type="number" name="sort" value="{{ $sort }}">
            <div class="entry-save">
                <button class="btn" type="submit">{{ $isEdit ? admin_t('ui.save') : admin_t('ui.add_pic') }}</button>
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
    var L = @json($picJsLang, JSON_UNESCAPED_UNICODE);
    var form = document.getElementById('gallery-pic-form');
    if (!U || !form) return;
    var isEdit = !!String(form.querySelector('input[name="id"]').value || '').trim();
    U.bindImageField(form, {
        input: '[name=url]',
        btn: '#pic-upload',
        preview: '#pic-preview'
    });
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var data = U.formData(form);
        if (!data.gallery_id) { U.toast(L.please_pick_gallery, 'err'); return; }
        if (!String(data.url || '').trim()) { U.toast(L.please_fill_image_url, 'err'); return; }
        data.desk = 'pics';
        U.loading(true);
        U.post('/admin/video/galleries/save', data).then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) {
                U.toast((res && res.msg) || L.save_fail, 'err');
                return;
            }
            var id = (res.data && res.data.id) || data.id;
            U.toast(isEdit ? L.saved : L.added, 'ok');
            if (!isEdit && id) {
                location.href = '/admin/video/gallery-pics/' + encodeURIComponent(id) + '/edit';
            }
        }).catch(function () {
            U.loading(false);
            U.toast(L.save_fail, 'err');
        });
    });
})();
</script>
@endpush
