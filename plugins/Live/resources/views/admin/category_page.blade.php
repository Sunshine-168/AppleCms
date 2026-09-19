@extends('admin.layouts.inner')
@section('title', $isEdit ? '编辑分类' : '新建分类')

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
@endphp

@section('plain')
<div class="card card-panel">
    <div class="card-header">
        <span>{{ $isEdit ? '编辑分类' : '新建分类' }}@if($isEdit && $name !== '') <em>{{ $name }}</em>@endif</span>
        <a class="btn btn-muted btn-sm" href="{{ $back }}">返回分类</a>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">直播分类只给 IPTV 频道分组用，和影片 / 文章分类不是同一棵树。</p>
        @if($isEdit && $count > 0)
            <p class="muted field-hint">有 <a href="/admin/video/lives?cate_id={{ $id }}">{{ $count }} 个</a> 频道挂在此分类。改名不会拆掉关联；删除分类会把频道改成未分类。</p>
        @endif
        <form class="tag-form" id="live-category-form" style="max-width:520px">
            <input type="hidden" name="id" value="{{ $isEdit ? $id : '' }}">
            <input type="hidden" name="desk" value="categories">

            <label for="cate-name">分类名</label>
            <input id="cate-name" type="text" name="name" value="{{ $name }}" required autofocus placeholder="如 央视、卫视、地方台">
            <p class="muted field-hint">出现在前台「直播」分类条和后台筛选里。</p>

            <label for="cate-slug">标识</label>
            <input id="cate-slug" type="text" name="slug" value="{{ $slug }}" placeholder="可空，按名称生成">
            <p class="muted field-hint">英文、数字和短横线。留空则保存时自动生成。</p>

            <label for="cate-pic">图片</label>
            <div class="field-inline">
                <input id="cate-pic" type="text" name="pic" value="{{ $pic }}" placeholder="图片地址，可空">
                <button type="button" class="btn btn-sm" id="cate-pic-pick">上传</button>
            </div>
            <img class="img-preview" id="cate-pic-preview" alt="" @if($pic === '') style="display:none" @else src="{{ $pic }}" @endif>
            <p class="muted field-hint">可选。可粘贴地址或点上传。</p>

            <label for="cate-sort">排序</label>
            <input id="cate-sort" type="number" name="sort" value="{{ $sort }}">
            <p class="muted field-hint">数字越大越靠前。</p>

            <label for="cate-status">状态</label>
            <select id="cate-status" name="status">
                <option value="1" @selected($status === '1')>启用</option>
                <option value="0" @selected($status === '0')>停用</option>
            </select>
            <p class="muted field-hint">停用后前台分类条不再显示，已挂频道还在。</p>

            <div class="form-actions">
                <button type="submit" class="btn">保存</button>
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
    var form = document.getElementById('live-category-form');
    var isEdit = !!String(form.id.value || '').trim();
    U.bindImageField(form, { input: '#cate-pic', btn: '#cate-pic-pick', preview: '#cate-pic-preview' });
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var data = U.formData(form);
        if (!String(data.name || '').trim()) { U.toast('请填写分类名', 'err'); return; }
        if (!isEdit) delete data.id;
        U.loading(true);
        U.post('/admin/video/lives/save', data).then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '保存失败', 'err'); return; }
            U.toast('已保存', 'ok');
            var id = (res.data && res.data.id) || data.id;
            if (!isEdit && id) location.href = '/admin/video/live-categories/' + encodeURIComponent(id) + '/edit';
            else location.href = '/admin/video/lives?desk=categories';
        }).catch(function () { U.loading(false); U.toast('保存失败', 'err'); });
    });
})();
</script>
@endpush
