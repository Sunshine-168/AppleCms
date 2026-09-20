@extends('admin.layouts.inner')
@section('title', $isEdit ? admin_t('ui.edit_chapter') : admin_t('ui.add_chapter'))

@php
    $chapter = is_array($chapter ?? null) ? $chapter : [];
    $isEdit = (bool) ($isEdit ?? false);
    $works = is_array($works ?? null) ? $works : [];
    $mangaId = (int) ($chapter['manga_id'] ?? 0);
    $name = (string) ($chapter['name'] ?? '');
    $sort = (int) ($chapter['sort'] ?? 0);
    $vip = (string) ($chapter['vip'] ?? '0');
    $pics = (string) ($chapter['pics'] ?? '');
    $id = (int) ($chapter['id'] ?? 0);
    $back = $mangaId > 0
        ? '/admin/video/mangas?desk=work&manga_id='.$mangaId
        : '/admin/video/mangas?desk=chapters';
    $chJsLang = [
        'appended' => admin_t('ui.appended'),
        'upload_fail' => admin_t('ui.upload_fail'),
        'please_pick_work' => admin_t('ui.please_pick_work'),
        'please_fill_chapter_name' => admin_t('ui.please_fill_chapter_name'),
        'save_fail' => admin_t('ui.save_fail'),
        'saved' => admin_t('ui.saved'),
        'created' => admin_t('ui.created'),
    ];
@endphp

@section('plain')
<div class="card card-panel">
    <div class="card-header">
        <span>{{ $isEdit ? admin_t('ui.edit_chapter') : admin_t('ui.add_chapter') }}@if($isEdit && $name !== '') <em>{{ $name }}</em>@endif</span>
        <a class="btn btn-muted btn-sm" href="{{ $back }}">{{ admin_t('ui.back') }}</a>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">{{ admin_t('ui.chapter_form_lead') }}</p>
        <form class="admin-form tag-form" id="manga-chapter-form">
            <input type="hidden" name="id" value="{{ $isEdit ? $id : '' }}">
            <label for="ch-manga">{{ admin_t('ui.works') }}</label>
            <select id="ch-manga" name="manga_id" required>
                <option value="">{{ admin_t('manga.select_work') }}</option>
                @foreach($works as $work)
                    <option value="{{ $work['id'] }}" @selected($mangaId === (int) $work['id'])>{{ $work['title'] }} (#{{ $work['id'] }})</option>
                @endforeach
            </select>
            <label for="ch-name">{{ admin_t('ui.chapter_name') }}</label>
            <input id="ch-name" type="text" name="name" value="{{ $name }}" required autofocus>
            <label for="ch-sort">{{ admin_t('ui.sort') }}</label>
            <input id="ch-sort" type="number" name="sort" value="{{ $sort }}">
            <label for="ch-vip">{{ admin_t('ui.vip_lock') }}</label>
            <select id="ch-vip" name="vip">
                <option value="0" @selected($vip === '0')>{{ admin_t('ui.free') }}</option>
                <option value="1" @selected($vip === '1')>{{ admin_t('ui.vip_readable') }}</option>
            </select>
            <label for="ch-pics">{{ admin_t('ui.ph_image_url') }}</label>
            <textarea id="ch-pics" name="pics" rows="10" placeholder="{{ admin_t('ui.ph_pic_lines') }}">{{ $pics }}</textarea>
            <div class="field-inline" style="margin-top:8px">
                <button type="button" class="btn btn-sm" id="ch-pics-upload">{{ admin_t('ui.append_upload') }}</button>
            </div>
            <img class="img-preview" id="ch-pics-preview" alt="" style="display:none">
            <p class="muted field-hint">{{ admin_t('ui.js_not_ingested') }}</p>
            <div class="entry-save">
                <button class="btn" type="submit">{{ $isEdit ? admin_t('ui.save') : admin_t('ui.create_chapter') }}</button>
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
    var L = @json($chJsLang, JSON_UNESCAPED_UNICODE);
    var form = document.getElementById('manga-chapter-form');
    var pics = document.getElementById('ch-pics');
    var preview = document.getElementById('ch-pics-preview');
    var isEdit = !!String(form.querySelector('input[name="id"]').value || '').trim();
    function lastUrl() {
        var lines = String(pics.value || '').split(/\r?\n/);
        for (var i = lines.length - 1; i >= 0; i--) {
            var line = String(lines[i] || '').trim();
            if (line) return line;
        }
        return '';
    }
    function syncPreview() {
        var url = lastUrl();
        if (!preview) return;
        if (url) { preview.src = url; preview.style.display = 'block'; }
        else { preview.removeAttribute('src'); preview.style.display = 'none'; }
    }
    syncPreview();
    pics.addEventListener('input', syncPreview);
    U.on('#ch-pics-upload', 'click', function () {
        U.pickFile('image/*').then(function (file) {
            if (!file) return;
            U.loading(true);
            return U.upload(file).then(function (res) {
                U.loading(false);
                if (res && res.code === 0 && res.data && res.data.url) {
                    var cur = String(pics.value || '').replace(/\s+$/, '');
                    pics.value = cur ? (cur + '\n' + res.data.url) : res.data.url;
                    syncPreview();
                    U.toast(L.appended, 'ok');
                } else {
                    U.toast((res && res.msg) || L.upload_fail, 'err');
                }
            }).catch(function () { U.loading(false); });
        });
    });
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var data = U.formData(form);
        if (!data.manga_id) { U.toast(L.please_pick_work, 'err'); return; }
        if (!String(data.name || '').trim()) { U.toast(L.please_fill_chapter_name, 'err'); return; }
        U.loading(true);
        U.post('/admin/video/manga_chapters/save', data).then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.save_fail, 'err'); return; }
            var id = (res.data && res.data.id) || data.id;
            U.toast(isEdit ? L.saved : L.created, 'ok');
            if (!isEdit && id) location.href = '/admin/video/manga-chapters/' + encodeURIComponent(id) + '/edit';
        }).catch(function () { U.loading(false); U.toast(L.save_fail, 'err'); });
    });
})();
</script>
@endpush
