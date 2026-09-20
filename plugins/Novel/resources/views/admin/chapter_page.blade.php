@extends('admin.layouts.inner')
@section('title', $isEdit ? admin_t('ui.edit_chapter') : admin_t('ui.add_chapter'))

@php
    $chapter = is_array($chapter ?? null) ? $chapter : [];
    $isEdit = (bool) ($isEdit ?? false);
    $works = $works ?? collect();
    $novelId = (int) ($chapter['novel_id'] ?? 0);
    $name = (string) ($chapter['name'] ?? '');
    $content = (string) ($chapter['content'] ?? '');
    $sort = (int) ($chapter['sort'] ?? 0);
    $vip = (string) ($chapter['vip'] ?? '0');
    $id = (int) ($chapter['id'] ?? 0);
    $back = '/admin/video/novels?desk=chapters';
    $chJsLang = [
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
        <p class="muted recycle-lead">{{ admin_t('ui.novel_chapter_lead') }}</p>
        <form class="admin-form tag-form" id="novel-chapter-form">
            <input type="hidden" name="id" value="{{ $isEdit ? $id : '' }}">
            <input type="hidden" name="desk" value="chapters">
            <label for="ch-novel">{{ admin_t('ui.works') }}</label>
            <select id="ch-novel" name="novel_id" required>
                <option value="">{{ admin_t('novel.select_work') }}</option>
                @foreach($works as $work)
                    <option value="{{ $work->id }}" @selected($novelId === (int) $work->id)>{{ $work->title }} (#{{ $work->id }})</option>
                @endforeach
            </select>
            <label for="ch-name">{{ admin_t('ui.chapter_name') }}</label>
            <input id="ch-name" type="text" name="name" value="{{ $name }}" required autofocus placeholder="{{ admin_t('novel.ph_chapter') }}">
            <label for="ch-sort">{{ admin_t('ui.sort') }}</label>
            <input id="ch-sort" type="number" name="sort" value="{{ $sort }}">
            <label for="ch-vip">{{ admin_t('ui.vip_lock') }}</label>
            <select id="ch-vip" name="vip">
                <option value="0" @selected($vip === '0')>{{ admin_t('ui.free') }}</option>
                <option value="1" @selected($vip === '1')>{{ admin_t('ui.vip_readable') }}</option>
            </select>
            <label for="ch-content">{{ admin_t('ui.body_text') }}</label>
            <textarea id="ch-content" name="content" rows="16" placeholder="{{ admin_t('ui.ph_chapter_body') }}">{{ $content }}</textarea>
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
    var form = document.getElementById('novel-chapter-form');
    if (!U || !form) return;
    var isEdit = !!String(form.querySelector('input[name="id"]').value || '').trim();
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var data = U.formData(form);
        if (!data.novel_id) { U.toast(L.please_pick_work, 'err'); return; }
        if (!String(data.name || '').trim()) { U.toast(L.please_fill_chapter_name, 'err'); return; }
        data.desk = 'chapters';
        U.loading(true);
        U.post('/admin/video/novels/save', data).then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) {
                U.toast((res && res.msg) || L.save_fail, 'err');
                return;
            }
            var id = (res.data && res.data.id) || data.id;
            U.toast(isEdit ? L.saved : L.created, 'ok');
            if (!isEdit && id) {
                location.href = '/admin/video/novel-chapters/' + encodeURIComponent(id) + '/edit';
            }
        }).catch(function () {
            U.loading(false);
            U.toast(L.save_fail, 'err');
        });
    });
})();
</script>
@endpush
