@extends('admin.layouts.inner')
@section('title', $isEdit ? '编辑图片' : '新增图片')

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
@endphp

@section('plain')
<div class="card card-panel">
    <div class="card-header">
        <span>{{ $isEdit ? '编辑图片' : '新增图片' }}</span>
        <a class="btn btn-muted btn-sm" href="{{ $back }}">返回</a>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">批量多行地址请回图片台快捷添加；本页改单张地址、标题与排序。</p>
        <form class="admin-form tag-form" id="gallery-pic-form">
            <input type="hidden" name="id" value="{{ $isEdit ? $id : '' }}">
            <input type="hidden" name="desk" value="pics">
            <label for="pic-gallery">图集</label>
            <select id="pic-gallery" name="gallery_id" required>
                <option value="">选择图集</option>
                @foreach($works as $work)
                    <option value="{{ $work->id }}" @selected($galleryId === (int) $work->id)>{{ $work->title }} (#{{ $work->id }})</option>
                @endforeach
            </select>
            <label for="pic-url">图片地址</label>
            <div class="field-inline">
                <input id="pic-url" type="text" name="url" value="{{ $url }}" required placeholder="https:// 或 /upload/...">
                <button type="button" class="btn btn-sm" id="pic-upload">上传</button>
            </div>
            <img class="img-preview" id="pic-preview" alt="" @if($url === '') style="display:none" @else src="{{ $url }}" @endif>
            <label for="pic-title">标题</label>
            <input id="pic-title" type="text" name="title" value="{{ $title }}" placeholder="可选">
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
        if (!data.gallery_id) { U.toast('请选择图集', 'err'); return; }
        if (!String(data.url || '').trim()) { U.toast('请填写图片地址', 'err'); return; }
        data.desk = 'pics';
        U.loading(true);
        U.post('/admin/video/galleries/save', data).then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) {
                U.toast((res && res.msg) || '保存失败', 'err');
                return;
            }
            var id = (res.data && res.data.id) || data.id;
            U.toast(isEdit ? '已保存' : '已添加', 'ok');
            if (!isEdit && id) {
                location.href = '/admin/video/gallery-pics/' + encodeURIComponent(id) + '/edit';
            }
        }).catch(function () {
            U.loading(false);
            U.toast('保存失败', 'err');
        });
    });
})();
</script>
@endpush
