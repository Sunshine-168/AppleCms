@extends('admin.layouts.inner')
@section('title', $isEdit ? '编辑章节' : '新增章节')

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
@endphp

@section('plain')
<div class="card card-panel">
    <div class="card-header">
        <span>{{ $isEdit ? '编辑章节' : '新增章节' }}@if($isEdit && $name !== '') <em>{{ $name }}</em>@endif</span>
        <a class="btn btn-muted btn-sm" href="{{ $back }}">返回</a>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">图片地址每行一条。保存后写入图片表。只改话名时也可在列表快捷添加。</p>
        <form class="admin-form tag-form" id="manga-chapter-form">
            <input type="hidden" name="id" value="{{ $isEdit ? $id : '' }}">
            <label for="ch-manga">作品</label>
            <select id="ch-manga" name="manga_id" required>
                <option value="">选择作品</option>
                @foreach($works as $work)
                    <option value="{{ $work['id'] }}" @selected($mangaId === (int) $work['id'])>{{ $work['title'] }} (#{{ $work['id'] }})</option>
                @endforeach
            </select>
            <label for="ch-name">章节名</label>
            <input id="ch-name" type="text" name="name" value="{{ $name }}" required autofocus>
            <label for="ch-sort">排序</label>
            <input id="ch-sort" type="number" name="sort" value="{{ $sort }}">
            <label for="ch-vip">VIP 锁章</label>
            <select id="ch-vip" name="vip">
                <option value="0" @selected($vip === '0')>免费</option>
                <option value="1" @selected($vip === '1')>VIP 可读</option>
            </select>
            <label for="ch-pics">图片地址</label>
            <textarea id="ch-pics" name="pics" rows="10" placeholder="每行一条，http(s) 或 / 开头的站内路径">{{ $pics }}</textarea>
            <div class="field-inline" style="margin-top:8px">
                <button type="button" class="btn btn-sm" id="ch-pics-upload">上传并追加</button>
            </div>
            <img class="img-preview" id="ch-pics-preview" alt="" style="display:none">
            <p class="muted field-hint">javascript: 不会收录。</p>
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
                    U.toast('已追加', 'ok');
                } else {
                    U.toast((res && res.msg) || '上传失败', 'err');
                }
            }).catch(function () { U.loading(false); });
        });
    });
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var data = U.formData(form);
        if (!data.manga_id) { U.toast('请选择作品', 'err'); return; }
        if (!String(data.name || '').trim()) { U.toast('请填写章节名', 'err'); return; }
        U.loading(true);
        U.post('/admin/video/manga_chapters/save', data).then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '保存失败', 'err'); return; }
            var id = (res.data && res.data.id) || data.id;
            U.toast(isEdit ? '已保存' : '已创建', 'ok');
            if (!isEdit && id) location.href = '/admin/video/manga-chapters/' + encodeURIComponent(id) + '/edit';
        }).catch(function () { U.loading(false); U.toast('保存失败', 'err'); });
    });
})();
</script>
@endpush
