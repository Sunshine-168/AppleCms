@extends('admin.layouts.inner')
@php
    $type = is_array($type ?? null) ? $type : [];
    $isEdit = (bool) ($isEdit ?? false);
    $parents = is_array($parents ?? null) ? $parents : [];
    $parent = is_array($parent ?? null) ? $parent : null;
    $name = (string) ($type['name'] ?? '');
    $parentId = (string) ($type['parent_id'] ?? '0');
    $status = (string) ($type['status'] ?? '1');
    $parentName = (string) ($parent['name'] ?? '');
    $base = '/admin/video/manga-types';
    $title = $isEdit ? '编辑分类' : ($parentName !== '' ? '添加下级' : '新增分类');
@endphp
@section('title', $title)

@section('plain')
<div class="card card-panel type-form-page">
    <div class="card-header">
        <span>{{ $title }}@if($isEdit && $name !== '') <em>{{ $name }}</em>@endif</span>
        <a class="btn btn-muted btn-sm" href="{{ $base }}">返回分类</a>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">
            @if($isEdit)
                改名称和别名会马上影响漫画分类页。下面有作品时不要删，可以先关掉前台显示。
            @elseif($parentName !== '')
                将建在「{{ $parentName }}」下面。保存后会出现在上级的下一层。
            @else
                分类是漫画的目录，例如 少年 → 热血。和影片分类、文章栏目不是同一棵树。
            @endif
        </p>

        <form class="admin-form type-form" id="manga-type-form">
            <input type="hidden" name="id" value="{{ $isEdit ? (int) ($type['id'] ?? 0) : '' }}">

            <h3>这个分类</h3>
            <label for="type-name">名称</label>
            <input id="type-name" type="text" name="name" value="{{ $name }}" placeholder="如 少年、热血" required>
            <label for="type-slug">网址别名</label>
            <input id="type-slug" type="text" name="slug" value="{{ $type['slug'] ?? '' }}" placeholder="如 shonen，可空">
            <p class="muted field-hint">出现在分类筛选链接里。只填英文、数字和短横线。留空则用数字 ID。</p>

            <label for="type-parent">上级</label>
            <select id="type-parent" name="parent_id">
                <option value="0" @selected($parentId === '0' || $parentId === '')>顶级（不挂在任何分类下）</option>
                @foreach($parents as $item)
                    <option value="{{ $item['id'] }}" @selected($parentId === (string) $item['id'])>
                        {{ str_repeat('└ ', max((int) ($item['depth'] ?? 0), 0)) }}{{ $item['name'] }}
                    </option>
                @endforeach
            </select>
            <p class="muted field-hint">选上级即可做多级。不能挂到自己的下级下面。</p>

            <label for="type-pic">封面</label>
            <div class="media-field">
                <div class="media-preview" id="type-pic-preview" @if(trim((string) ($type['pic'] ?? '')) === '') hidden @endif>
                    <img id="type-pic-img" src="{{ $type['pic'] ?? '' }}" alt="封面预览">
                    <button type="button" class="media-preview-clear" id="type-pic-clear" title="移除封面">&times;</button>
                </div>
                <div class="cover-row">
                    <input id="type-pic" type="text" name="pic" value="{{ $type['pic'] ?? '' }}" placeholder="图片地址，可空">
                    <button type="button" class="btn btn-muted" id="type-pic-upload">上传</button>
                </div>
            </div>

            <h3>显示</h3>
            <label for="type-sort">排序</label>
            <input id="type-sort" type="number" name="sort" value="{{ $type['sort'] ?? 0 }}">
            <p class="muted field-hint">同一上级下，数字越大越靠前。</p>
            <label for="type-page-size">分页条数</label>
            <input id="type-page-size" type="number" name="page_size" min="0" max="100" value="{{ (int) ($type['page_size'] ?? 0) }}">
            <p class="muted field-hint">前台按该分类筛选时每页部数。0 表示用默认 24。</p>
            <input type="hidden" name="status" value="0">
            <label class="inline">
                <input type="checkbox" name="status" value="1" @checked($status === '1')>
                在前台显示
            </label>
            <p class="muted field-hint">关掉后前台菜单里不再出现，作品还在。</p>

            <details class="settings-details" @if(trim((string) ($type['seo_title'] ?? '').($type['seo_keywords'] ?? '').($type['seo_description'] ?? '')) !== '') open @endif>
                <summary>搜索标题（可空）</summary>
                <p class="muted field-hint">给搜索引擎看。留空则用站点默认。</p>
                <label for="type-seo-title">标题</label>
                <input id="type-seo-title" type="text" name="seo_title" value="{{ $type['seo_title'] ?? '' }}" placeholder="{type} - {site}">
                <label for="type-seo-keywords">关键词</label>
                <input id="type-seo-keywords" type="text" name="seo_keywords" value="{{ $type['seo_keywords'] ?? '' }}">
                <label for="type-seo-description">描述</label>
                <textarea id="type-seo-description" name="seo_description" rows="3">{{ $type['seo_description'] ?? '' }}</textarea>
            </details>

            <div class="form-actions">
                <button type="submit" class="btn" id="type-save">保存</button>
                <button type="button" class="btn btn-muted" id="type-save-child">保存并添加下级</button>
                <button type="button" class="btn btn-muted" id="type-save-work">保存并新增作品</button>
                <a class="btn btn-muted" href="{{ $base }}">取消</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var form = document.getElementById('manga-type-form');
    var isEdit = !!String(form.id.value || '').trim();
    var base = @json($base);
    var api = '/admin/video/manga_types';

    function save(next) {
        var data = U.formData(form);
        if (!String(data.name || '').trim()) {
            U.toast('请填写名称', 'err');
            document.getElementById('type-name').focus();
            return;
        }
        if (!isEdit) delete data.id;
        U.loading(true);
        U.post(api + '/save', data).then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) {
                U.toast((res && res.msg) || '保存失败', 'err');
                return;
            }
            var id = (res.data && res.data.id) || data.id;
            U.toast('已保存', 'ok');
            if (next === 'child' && id) {
                location.href = base + '/create?parent_id=' + encodeURIComponent(id);
                return;
            }
            if (next === 'work' && id) {
                location.href = '/admin/video/mangas?type_id=' + encodeURIComponent(id);
                return;
            }
            location.href = base;
        }).catch(function () {
            U.loading(false);
            U.toast('保存失败', 'err');
        });
    }
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        save('');
    });
    document.getElementById('type-save-child').addEventListener('click', function () { save('child'); });
    document.getElementById('type-save-work').addEventListener('click', function () { save('work'); });

    var picInput = document.getElementById('type-pic');
    function syncPic(url) {
        var img = document.getElementById('type-pic-img');
        var preview = document.getElementById('type-pic-preview');
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
    if (picInput) picInput.addEventListener('input', function () { syncPic(picInput.value); });
    U.on('#type-pic-clear', 'click', function () {
        if (!picInput) return;
        picInput.value = '';
        syncPic('');
    });
    U.on('#type-pic-upload', 'click', function () {
        U.pickFile('image/*').then(function (file) {
            if (!file) return;
            U.loading(true);
            return U.upload(file).then(function (res) {
                U.loading(false);
                if (res && res.code === 0 && res.data && res.data.url) {
                    picInput.value = res.data.url;
                    syncPic(res.data.url);
                    U.toast('上传成功', 'ok');
                } else U.toast((res && res.msg) || '上传失败', 'err');
            });
        });
    });
})();
</script>
@endpush
