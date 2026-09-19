@extends('admin.layouts.inner')
@section('title', $isEdit ? '编辑标签' : '新建标签')

@php
    $tag = is_array($tag ?? null) ? $tag : [];
    $isEdit = (bool) ($isEdit ?? false);
    $name = (string) ($tag['name'] ?? '');
    $slug = (string) ($tag['slug'] ?? '');
    $sort = (int) ($tag['sort'] ?? 0);
    $status = (string) ($tag['status'] ?? '1');
    $count = (int) ($tag['art_count'] ?? 0);
    $frontUrl = trim((string) ($tag['url'] ?? ''));
@endphp

@section('plain')
<div class="card card-panel">
    <div class="card-header">
        <span>{{ $isEdit ? '编辑标签' : '新建标签' }}</span>
        <a class="btn btn-muted btn-sm" href="/admin/video/art-tags">返回标签</a>
    </div>
    <div class="card-body">
        @if($isEdit && $count > 0)
            <p class="muted recycle-lead">有 <a href="/admin/video/arts?tag_id={{ (int) ($tag['id'] ?? 0) }}">{{ $count }} 篇</a> 文章使用此标签。改名称不会拆掉已打的标。</p>
        @endif
        <form class="admin-form tag-form" id="art-tag-form">
            <input type="hidden" name="id" value="{{ $isEdit ? (int) ($tag['id'] ?? 0) : '' }}">
            <label for="tag-name">名称</label>
            <input id="tag-name" type="text" name="name" value="{{ $name }}" placeholder="如 影讯" required autofocus>
            <p class="muted field-hint" id="tag-slug-hint">保存后生成前台地址 /art/tag/…</p>
            <details class="entry-seo" @if($isEdit) open @endif>
                <summary>网址</summary>
                <label for="tag-slug">网址标识</label>
                <input id="tag-slug" type="text" name="slug" value="{{ $slug }}" placeholder="可空，按名称生成">
                <p class="muted field-hint">改了之后，旧的 /art/tag/ 地址不会自动跳转。</p>
                <label for="tag-sort">排序</label>
                <input id="tag-sort" type="number" name="sort" min="0" value="{{ $sort }}">
                <label for="tag-status">状态</label>
                <select id="tag-status" name="status">
                    <option value="1" @selected($status === '1')>启用</option>
                    <option value="0" @selected($status === '0')>停用</option>
                </select>
                <p class="muted field-hint">停用后前台标签页看不到，已打在文章上的词还在。</p>
            </details>
            <div class="entry-save">
                <button class="btn" type="submit">{{ $isEdit ? '保存' : '添加标签' }}</button>
                <a class="btn btn-muted" href="/admin/video/art-tags">取消</a>
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
    var form = document.getElementById('art-tag-form');
    var title = document.getElementById('tag-name');
    var slug = document.getElementById('tag-slug');
    var hint = document.getElementById('tag-slug-hint');
    var isEdit = !!String(form.querySelector('input[name="id"]').value || '').trim();
    function preview() {
        var custom = (slug && slug.value || '').trim();
        var raw = custom || (title.value || '').trim().toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9\-]+/g, '');
        hint.textContent = raw
            ? ('前台地址 /art/tag/' + raw)
            : '保存后自动生成前台地址，如 /art/tag/news';
    }
    title.addEventListener('input', preview);
    if (slug) slug.addEventListener('input', preview);
    preview();
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var data = U.formData(form);
        if (!String(data.name || '').trim()) {
            U.toast('请填写标签名称', 'err');
            title.focus();
            return;
        }
        U.loading(true);
        U.post('/admin/video/art-tags/save', data).then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) {
                U.toast((res && res.msg) || '保存失败', 'err');
                return;
            }
            var id = (res.data && res.data.id) || data.id;
            U.toast(isEdit ? '已保存' : '已添加', 'ok');
            if (!isEdit && id) location.href = '/admin/video/art-tags/' + encodeURIComponent(id) + '/edit';
        }).catch(function () {
            U.loading(false);
            U.toast('保存失败', 'err');
        });
    });
})();
</script>
@endpush
