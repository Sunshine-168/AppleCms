@extends('admin.layouts.inner')
@section('title', $isEdit ? '编辑图片' : '新增图片')

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
@endphp

@section('plain')
<div class="card card-panel">
    <div class="card-header">
        <span>{{ $isEdit ? '编辑图片' : '新增图片' }}</span>
        <a class="btn btn-muted btn-sm" href="{{ $back }}">返回</a>
    </div>
    <div class="card-body">
        <form class="admin-form tag-form" id="manga-pic-form">
            <input type="hidden" name="id" value="{{ $isEdit ? $id : '' }}">
            <label for="pic-manga">作品</label>
            <select id="pic-manga" name="manga_id" required>
                <option value="">选择作品</option>
                @foreach($works as $work)
                    <option value="{{ $work['id'] }}" @selected($mangaId === (int) $work['id'])>{{ $work['title'] }} (#{{ $work['id'] }})</option>
                @endforeach
            </select>
            <label for="pic-chapter">章节 ID</label>
            <input id="pic-chapter" type="number" name="chapter_id" value="{{ $chapterId > 0 ? $chapterId : '' }}" required>
            <p class="muted field-hint">填章节数字 ID。也可在章节完整表单里一次贴多行图。</p>
            <label for="pic-url">图片地址</label>
            <div class="field-inline">
                <input id="pic-url" type="text" name="url" value="{{ $url }}" required placeholder="http(s) 或 / 开头">
                <button type="button" class="btn btn-sm" id="pic-upload">上传</button>
            </div>
            <img class="img-preview" id="pic-preview" alt="" @if($url === '') style="display:none" @else src="{{ $url }}" @endif>
            <label for="pic-sort">排序</label>
            <input id="pic-sort" type="number" name="sort" value="{{ $sort }}">
            <div class="entry-save">
                <button class="btn" type="submit">{{ $isEdit ? '保存' : '添加图片' }}</button>
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
    var form = document.getElementById('manga-pic-form');
    var isEdit = !!String(form.querySelector('input[name="id"]').value || '').trim();
    U.bindImageField(form, { input: '[name=url]', btn: '#pic-upload', preview: '#pic-preview' });
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var data = U.formData(form);
        if (!data.manga_id) { U.toast('请选择作品', 'err'); return; }
        if (!data.chapter_id) { U.toast('请填写章节 ID', 'err'); return; }
        if (!String(data.url || '').trim()) { U.toast('请填写图片地址', 'err'); return; }
        U.loading(true);
        U.post('/admin/video/manga_pics/save', data).then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '保存失败', 'err'); return; }
            var id = (res.data && res.data.id) || data.id;
            U.toast(isEdit ? '已保存' : '已添加', 'ok');
            if (!isEdit && id) location.href = '/admin/video/manga-pics/' + encodeURIComponent(id) + '/edit';
        }).catch(function () { U.loading(false); U.toast('保存失败', 'err'); });
    });
})();
</script>
@endpush
