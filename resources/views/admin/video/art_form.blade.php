@extends('admin.layouts.inner')
@section('title', $isEdit ? '编辑内容' : '写内容')

@php
    $art = is_array($art ?? null) ? $art : [];
    $isEdit = (bool) ($isEdit ?? false);
    $types = is_array($types ?? null) ? $types : [];
    $title = (string) ($art['title'] ?? '');
    $cover = trim((string) ($art['cover'] ?? ''));
    $content = (string) ($art['content'] ?? '');
    $typeId = (string) ($art['type_id'] ?? '0');
    $status = (string) ($art['status'] ?? '1');
    $hits = (int) ($art['hits'] ?? 0);
    $frontUrl = trim((string) ($art['url'] ?? ''));
    if ($frontUrl === '' && $isEdit) {
        $frontUrl = '/art/'.(int) ($art['id'] ?? 0);
    }
@endphp

@section('plain')
<form class="entry-form art-form-page" id="art-form">
    <input type="hidden" name="id" value="{{ $isEdit ? (int) ($art['id'] ?? 0) : '' }}">
    <div class="entry-layout">
        <div class="entry-main">
            <div class="card card-panel">
                <div class="card-header">
                    <span>{{ $isEdit ? '编辑内容' : '写内容' }} · 文章</span>
                </div>
                <div class="card-body">
                    <label for="art-title">标题</label>
                    <input id="art-title" type="text" name="title" class="entry-title" value="{{ $title }}" placeholder="读者看到的标题" required>
                    <label for="art-content">正文</label>
                    <textarea id="art-content" name="content" class="cms-editor art-content" placeholder="正文">{{ $content }}</textarea>
                </div>
            </div>
        </div>
        <aside class="entry-aside">
            <div class="card card-panel">
                <div class="card-header"><span>发布</span></div>
                <div class="card-body">
                    <label for="art-status">状态</label>
                    <select id="art-status" name="status">
                        <option value="1" @selected($status === '1')>发布</option>
                        <option value="0" @selected($status === '0')>草稿</option>
                    </select>
                    <p class="muted field-hint">草稿前台看不到。发布后可从列表打开前台核对。</p>
                    <div class="entry-save">
                        <button class="btn" type="submit" id="art-save">保存</button>
                        <a class="btn btn-muted" href="/admin/video/arts">返回文章</a>
                    </div>
                    @if($isEdit && $status === '1' && $frontUrl !== '')
                        <p class="muted field-hint"><a href="{{ $frontUrl }}" target="_blank" rel="noopener">打开前台</a></p>
                    @endif
                </div>
            </div>
            <div class="card card-panel">
                <div class="card-header"><span>栏目与展示</span></div>
                <div class="card-body">
                    <label for="art-type">栏目</label>
                    <select id="art-type" name="type_id">
                        <option value="0">未分栏</option>
                        @foreach($types as $type)
                            <option value="{{ $type['id'] }}" @selected($typeId === (string) $type['id'])>{{ $type['name'] }}</option>
                        @endforeach
                    </select>
                    @if($types === [])
                        <p class="muted field-hint">还没有文章栏目。先去分类里建一个，模型选「文章」。</p>
                    @else
                        <p class="muted field-hint">决定这篇出现在哪个文章栏目。</p>
                    @endif
                    <label for="art-cover">封面</label>
                    <div class="media-field">
                        <div class="media-preview" id="art-cover-preview" @if($cover === '') hidden @endif>
                            <img id="art-cover-img" src="{{ $cover }}" alt="封面预览">
                            <button type="button" class="media-preview-clear" id="art-cover-clear" title="移除封面">&times;</button>
                        </div>
                        <div class="cover-row">
                            <input id="art-cover" type="text" name="cover" value="{{ $cover }}" placeholder="图片地址">
                            <button type="button" class="btn btn-muted" id="art-cover-upload">上传</button>
                        </div>
                    </div>
                </div>
            </div>
            <details class="card card-panel entry-aside-more">
                <summary>更多设置</summary>
                <div class="card-body">
                    <label for="art-hits">点击</label>
                    <input id="art-hits" type="number" name="hits" min="0" value="{{ $hits }}">
                    <p class="muted field-hint">一般不用改，前台浏览会自己加。</p>
                </div>
            </details>
        </aside>
    </div>
</form>
@endsection

@include('admin.partials.editor-assets')

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var form = document.getElementById('art-form');
    var idInput = form.querySelector('input[name="id"]');
    var isEdit = !!String(idInput && idInput.value || '').trim();
    var coverInput = document.getElementById('art-cover');

    function syncCover(url) {
        var img = document.getElementById('art-cover-img');
        var preview = document.getElementById('art-cover-preview');
        url = String(url || '').trim();
        if (!img || !preview) return;
        if (!url) {
            img.removeAttribute('src');
            preview.hidden = true;
            return;
        }
        img.onload = function () { preview.hidden = false; };
        img.onerror = function () { preview.hidden = true; };
        if (img.getAttribute('src') !== url) img.src = url;
        else preview.hidden = false;
    }
    if (coverInput) coverInput.addEventListener('input', function () { syncCover(coverInput.value); });
    U.on('#art-cover-clear', 'click', function () {
        coverInput.value = '';
        syncCover('');
    });
    U.on('#art-cover-upload', 'click', function () {
        U.pickFile('image/*').then(function (file) {
            if (!file) return;
            U.loading(true);
            return U.upload(file).then(function (res) {
                U.loading(false);
                if (res && res.code === 0 && res.data && res.data.url) {
                    coverInput.value = res.data.url;
                    syncCover(res.data.url);
                    U.toast('上传成功', 'ok');
                } else U.toast((res && res.msg) || '上传失败', 'err');
            });
        });
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (window.tinymce) tinymce.triggerSave();
        var data = U.formData(form);
        if (!String(data.title || '').trim()) {
            U.toast('请填写标题', 'err');
            document.getElementById('art-title').focus();
            return;
        }
        U.loading(true);
        U.post('/admin/video/arts/save', data).then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) {
                U.toast((res && res.msg) || '保存失败', 'err');
                return;
            }
            var id = (res.data && res.data.id) || data.id;
            U.toast(isEdit ? '已保存' : '已创建', 'ok');
            if (!isEdit && id) {
                location.href = '/admin/video/arts/' + encodeURIComponent(id) + '/edit';
            }
        }).catch(function () {
            U.loading(false);
            U.toast('保存失败', 'err');
        });
    });
})();
</script>
@endpush
