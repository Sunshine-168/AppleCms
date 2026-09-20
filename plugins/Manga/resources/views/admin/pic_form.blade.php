@extends('admin.layouts.inner')
@section('title', $isEdit ? admin_t('ui.edit_pic') : admin_t('ui.add_pic'))

@php
    $pic = is_array($pic ?? null) ? $pic : [];
    $isEdit = (bool) ($isEdit ?? false);
    $works = is_array($works ?? null) ? $works : [];
    $mangaId = (int) ($pic['manga_id'] ?? 0);
    $chapterId = (int) ($pic['chapter_id'] ?? 0);
    $url = (string) ($pic['url'] ?? '');
    $sort = (int) ($pic['sort'] ?? 0);
    $id = (int) ($pic['id'] ?? 0);
    $back = $mangaId > 0
        ? '/admin/video/mangas?desk=pics&manga_id='.$mangaId
        : '/admin/video/mangas?desk=pics';
    $picJsLang = [
        'please_pick_work' => admin_t('ui.please_pick_work'),
        'please_fill_chapter_id' => admin_t('ui.please_fill_chapter_id'),
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
        <form class="admin-form tag-form" id="manga-pic-form">
            <input type="hidden" name="id" value="{{ $isEdit ? $id : '' }}">
            <label for="pic-manga">{{ admin_t('ui.works') }}</label>
            <select id="pic-manga" name="manga_id" required>
                <option value="">{{ admin_t('manga.select_work') }}</option>
                @foreach($works as $work)
                    <option value="{{ $work['id'] }}" @selected($mangaId === (int) $work['id'])>{{ $work['title'] }} (#{{ $work['id'] }})</option>
                @endforeach
            </select>
            <label for="pic-chapter">{{ admin_t('ui.chapter_id') }}</label>
            <input id="pic-chapter" type="number" name="chapter_id" value="{{ $chapterId > 0 ? $chapterId : '' }}" required>
            <p class="muted field-hint">{{ admin_t('ui.chapter_id_hint') }}</p>
            <label for="pic-url">{{ admin_t('ui.ph_image_url') }}</label>
            <div class="field-inline">
                <input id="pic-url" type="text" name="url" value="{{ $url }}" required placeholder="{{ admin_t('ui.ph_url_or_path') }}">
                <button type="button" class="btn btn-sm" id="pic-upload">{{ admin_t('ui.upload') }}</button>
            </div>
            <img class="img-preview" id="pic-preview" alt="" @if($url === '') style="display:none" @else src="{{ $url }}" @endif>
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
    var form = document.getElementById('manga-pic-form');
    var isEdit = !!String(form.querySelector('input[name="id"]').value || '').trim();
    U.bindImageField(form, { input: '[name=url]', btn: '#pic-upload', preview: '#pic-preview' });
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var data = U.formData(form);
        if (!data.manga_id) { U.toast(L.please_pick_work, 'err'); return; }
        if (!data.chapter_id) { U.toast(L.please_fill_chapter_id, 'err'); return; }
        if (!String(data.url || '').trim()) { U.toast(L.please_fill_image_url, 'err'); return; }
        U.loading(true);
        U.post('/admin/video/manga_pics/save', data).then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.save_fail, 'err'); return; }
            var id = (res.data && res.data.id) || data.id;
            U.toast(isEdit ? L.saved : L.added, 'ok');
            if (!isEdit && id) location.href = '/admin/video/manga-pics/' + encodeURIComponent(id) + '/edit';
        }).catch(function () { U.loading(false); U.toast(L.save_fail, 'err'); });
    });
})();
</script>
@endpush
