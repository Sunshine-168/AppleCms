@extends('admin.layouts.inner')
@section('title', $isEdit ? '编辑作者' : '新建作者')

@php
    $author = is_array($author ?? null) ? $author : [];
    $isEdit = (bool) ($isEdit ?? false);
    $name = (string) ($author['name'] ?? '');
    $slug = (string) ($author['slug'] ?? '');
    $sort = (int) ($author['sort'] ?? 0);
    $status = (string) ($author['status'] ?? '1');
    $count = (int) ($author['gallery_count'] ?? 0);
    $frontUrl = trim((string) ($author['url'] ?? ''));
@endphp

@section('plain')
<div class="card card-panel">
    <div class="card-header">
        <span>{{ $isEdit ? '编辑作者' : '新建作者' }}</span>
        <a class="btn btn-muted btn-sm" href="/admin/video/gallery-authors">返回作者</a>
    </div>
    <div class="card-body">
        @if($isEdit && $count > 0)
            <p class="muted recycle-lead">有 <a href="/admin/video/mangas?author_id={{ (int) ($author['id'] ?? 0) }}">{{ $count }} 部</a> 图集使用此作者。改名称不会拆掉已打的关联。</p>
        @endif
        <form class="admin-form tag-form" id="gallery-author-form">
            <input type="hidden" name="id" value="{{ $isEdit ? (int) ($author['id'] ?? 0) : '' }}">
            <label for="author-name">名称</label>
            <input id="author-name" type="text" name="name" value="{{ $name }}" placeholder="如 尾田荣一郎" required autofocus>
            <p class="muted field-hint" id="author-slug-hint">保存后生成前台地址 /gallery?author=…</p>
            <details class="entry-seo" @if($isEdit) open @endif>
                <summary>网址</summary>
                <label for="author-slug">网址标识</label>
                <input id="author-slug" type="text" name="slug" value="{{ $slug }}" placeholder="可空，按名称生成">
                <p class="muted field-hint">改了之后，旧的 /gallery?author= 地址不会自动跳转。</p>
                <label for="author-sort">排序</label>
                <input id="author-sort" type="number" name="sort" min="0" value="{{ $sort }}">
                <label for="author-status">状态</label>
                <select id="author-status" name="status">
                    <option value="1" @selected($status === '1')>启用</option>
                    <option value="0" @selected($status === '0')>停用</option>
                </select>
                <p class="muted field-hint">停用后前台作者云不显示，已挂在作品上的还在。</p>
            </details>
            <div class="entry-save">
                <button class="btn" type="submit">{{ $isEdit ? '保存' : '添加作者' }}</button>
                <a class="btn btn-muted" href="/admin/video/gallery-authors">取消</a>
                @if($isEdit && $frontUrl !== '')
                    <a class="btn btn-muted" href="{{ $frontUrl }}" target="_blank" rel="noopener">前台</a>
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
    var form = document.getElementById('gallery-author-form');
    var title = document.getElementById('author-name');
    var slug = document.getElementById('author-slug');
    var hint = document.getElementById('author-slug-hint');
    var isEdit = !!String(form.querySelector('input[name="id"]').value || '').trim();
    function preview() {
        var custom = (slug && slug.value || '').trim();
        var raw = custom || (title.value || '').trim().toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9\-]+/g, '');
        hint.textContent = raw
            ? ('前台地址 /gallery?author=' + raw)
            : '保存后自动生成前台地址，如 /gallery?author=oda';
    }
    title.addEventListener('input', preview);
    if (slug) slug.addEventListener('input', preview);
    preview();
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var data = U.formData(form);
        if (!String(data.name || '').trim()) {
            U.toast('请填写作者名称', 'err');
            title.focus();
            return;
        }
        U.loading(true);
        U.post('/admin/video/gallery-authors/save', data).then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) {
                U.toast((res && res.msg) || '保存失败', 'err');
                return;
            }
            var id = (res.data && res.data.id) || data.id;
            U.toast(isEdit ? '已保存' : '已添加', 'ok');
            if (!isEdit && id) location.href = '/admin/video/gallery-authors/' + encodeURIComponent(id) + '/edit';
        }).catch(function () {
            U.loading(false);
            U.toast('保存失败', 'err');
        });
    });
})();
</script>
@endpush
