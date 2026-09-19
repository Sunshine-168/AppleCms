@extends('admin.layouts.inner')
@section('title', $isEdit ? '编辑章节' : '新增章节')

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
@endphp

@section('plain')
<div class="card card-panel">
    <div class="card-header">
        <span>{{ $isEdit ? '编辑章节' : '新增章节' }}@if($isEdit && $name !== '') <em>{{ $name }}</em>@endif</span>
        <a class="btn btn-muted btn-sm" href="{{ $back }}">返回</a>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">只改话名时也可在列表快捷添加；正文、VIP 请在本页填写。</p>
        <form class="tag-form" id="novel-chapter-form">
            <input type="hidden" name="id" value="{{ $isEdit ? $id : '' }}">
            <input type="hidden" name="desk" value="chapters">
            <label for="ch-novel">作品</label>
            <select id="ch-novel" name="novel_id" required>
                <option value="">选择作品</option>
                @foreach($works as $work)
                    <option value="{{ $work->id }}" @selected($novelId === (int) $work->id)>{{ $work->title }} (#{{ $work->id }})</option>
                @endforeach
            </select>
            <label for="ch-name">章节名</label>
            <input id="ch-name" type="text" name="name" value="{{ $name }}" required autofocus placeholder="如 第一章">
            <label for="ch-sort">排序</label>
            <input id="ch-sort" type="number" name="sort" value="{{ $sort }}">
            <label for="ch-vip">VIP 锁章</label>
            <select id="ch-vip" name="vip">
                <option value="0" @selected($vip === '0')>免费</option>
                <option value="1" @selected($vip === '1')>VIP 可读</option>
            </select>
            <label for="ch-content">正文</label>
            <textarea id="ch-content" name="content" rows="16" placeholder="章节正文">{{ $content }}</textarea>
            <div class="entry-save">
                <button class="btn" type="submit">{{ $isEdit ? '保存' : '创建章节' }}</button>
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
    var form = document.getElementById('novel-chapter-form');
    if (!U || !form) return;
    var isEdit = !!String(form.querySelector('input[name="id"]').value || '').trim();
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var data = U.formData(form);
        if (!data.novel_id) { U.toast('请选择作品', 'err'); return; }
        if (!String(data.name || '').trim()) { U.toast('请填写章节名', 'err'); return; }
        data.desk = 'chapters';
        U.loading(true);
        U.post('/admin/video/novels/save', data).then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) {
                U.toast((res && res.msg) || '保存失败', 'err');
                return;
            }
            var id = (res.data && res.data.id) || data.id;
            U.toast(isEdit ? '已保存' : '已创建', 'ok');
            if (!isEdit && id) {
                location.href = '/admin/video/novel-chapters/' + encodeURIComponent(id) + '/edit';
            }
        }).catch(function () {
            U.loading(false);
            U.toast('保存失败', 'err');
        });
    });
})();
</script>
@endpush
