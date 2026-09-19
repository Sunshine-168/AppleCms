@extends('admin.layouts.inner')
@section('title', $isEdit ? '编辑评论' : '新增评论')

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
@endphp

@section('plain')
<div class="card card-panel">
    <div class="card-header">
        <span>{{ $isEdit ? '编辑评论' : '新增评论' }}</span>
        <a class="btn btn-muted btn-sm" href="{{ $back }}">返回</a>
    </div>
    <div class="card-body">
        <form class="admin-form tag-form" id="manga-comment-form">
            <input type="hidden" name="id" value="{{ $isEdit ? $id : '' }}">
            <label for="cm-manga">作品</label>
            <select id="cm-manga" name="manga_id" required>
                <option value="">选择作品</option>
                @foreach($works as $work)
                    <option value="{{ $work['id'] }}" @selected($mangaId === (int) $work['id'])>{{ $work['title'] }} (#{{ $work['id'] }})</option>
                @endforeach
            </select>
            <label for="cm-name">昵称</label>
            <input id="cm-name" type="text" name="author_name" value="{{ $authorName }}">
            <label for="cm-content">内容</label>
            <textarea id="cm-content" name="content" rows="6" required>{{ $content }}</textarea>
            <label for="cm-status">状态</label>
            <select id="cm-status" name="status">
                <option value="1" @selected($status === '1')>显示</option>
                <option value="0" @selected($status === '0')>待审 / 隐藏</option>
            </select>
            <div class="entry-save">
                <button class="btn" type="submit">{{ $isEdit ? '保存' : '添加评论' }}</button>
                <a class="btn btn-muted" href="{{ $back }}">取消</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var form = document.getElementById('manga-comment-form');
    var isEdit = !!String(form.querySelector('input[name="id"]').value || '').trim();
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var data = U.formData(form);
        if (!data.manga_id) { U.toast('请选择作品', 'err'); return; }
        if (!String(data.content || '').trim()) { U.toast('请填写评论', 'err'); return; }
        U.loading(true);
        U.post('/admin/video/manga_comments/save', data).then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '保存失败', 'err'); return; }
            var id = (res.data && res.data.id) || data.id;
            U.toast(isEdit ? '已保存' : '已添加', 'ok');
            if (!isEdit && id) location.href = '/admin/video/manga-comments/' + encodeURIComponent(id) + '/edit';
        }).catch(function () { U.loading(false); U.toast('保存失败', 'err'); });
    });
})();
</script>
@endpush
