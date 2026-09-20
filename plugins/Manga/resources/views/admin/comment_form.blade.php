@extends('admin.layouts.inner')
@section('title', $isEdit ? admin_t('ui.edit_comment') : admin_t('ui.add_comment'))

@php
    $comment = is_array($comment ?? null) ? $comment : [];
    $isEdit = (bool) ($isEdit ?? false);
    $works = is_array($works ?? null) ? $works : [];
    $mangaId = (int) ($comment['manga_id'] ?? 0);
    $authorName = (string) ($comment['author_name'] ?? '');
    $content = (string) ($comment['content'] ?? '');
    $status = (string) ($comment['status'] ?? '1');
    $id = (int) ($comment['id'] ?? 0);
    $back = $mangaId > 0
        ? '/admin/video/mangas?desk=comments&manga_id='.$mangaId
        : '/admin/video/mangas?desk=comments';
    $cmJsLang = [
        'please_pick_work' => admin_t('ui.please_pick_work'),
        'please_fill_comment' => admin_t('ui.please_fill_comment'),
        'save_fail' => admin_t('ui.save_fail'),
        'saved' => admin_t('ui.saved'),
        'added' => admin_t('ui.added'),
    ];
@endphp

@section('plain')
<div class="card card-panel">
    <div class="card-header">
        <span>{{ $isEdit ? admin_t('ui.edit_comment') : admin_t('ui.add_comment') }}</span>
        <a class="btn btn-muted btn-sm" href="{{ $back }}">{{ admin_t('ui.back') }}</a>
    </div>
    <div class="card-body">
        <form class="admin-form tag-form" id="manga-comment-form">
            <input type="hidden" name="id" value="{{ $isEdit ? $id : '' }}">
            <label for="cm-manga">{{ admin_t('ui.works') }}</label>
            <select id="cm-manga" name="manga_id" required>
                <option value="">{{ admin_t('manga.select_work') }}</option>
                @foreach($works as $work)
                    <option value="{{ $work['id'] }}" @selected($mangaId === (int) $work['id'])>{{ $work['title'] }} (#{{ $work['id'] }})</option>
                @endforeach
            </select>
            <label for="cm-name">{{ admin_t('ui.nickname') }}</label>
            <input id="cm-name" type="text" name="author_name" value="{{ $authorName }}">
            <label for="cm-content">{{ admin_t('ui.content') }}</label>
            <textarea id="cm-content" name="content" rows="6" required>{{ $content }}</textarea>
            <label for="cm-status">{{ admin_t('ui.status') }}</label>
            <select id="cm-status" name="status">
                <option value="1" @selected($status === '1')>{{ admin_t('ui.show') }}</option>
                <option value="0" @selected($status === '0')>{{ admin_t('ui.pending') }}</option>
            </select>
            <div class="entry-save">
                <button class="btn" type="submit">{{ $isEdit ? admin_t('ui.save') : admin_t('ui.add') }}</button>
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
    var L = @json($cmJsLang, JSON_UNESCAPED_UNICODE);
    var form = document.getElementById('manga-comment-form');
    var isEdit = !!String(form.querySelector('input[name="id"]').value || '').trim();
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var data = U.formData(form);
        if (!data.manga_id) { U.toast(L.please_pick_work, 'err'); return; }
        if (!String(data.content || '').trim()) { U.toast(L.please_fill_comment, 'err'); return; }
        U.loading(true);
        U.post('/admin/video/manga_comments/save', data).then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.save_fail, 'err'); return; }
            var id = (res.data && res.data.id) || data.id;
            U.toast(isEdit ? L.saved : L.added, 'ok');
            if (!isEdit && id) location.href = '/admin/video/manga-comments/' + encodeURIComponent(id) + '/edit';
        }).catch(function () { U.loading(false); U.toast(L.save_fail, 'err'); });
    });
})();
</script>
@endpush
