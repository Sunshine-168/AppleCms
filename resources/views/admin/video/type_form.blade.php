@extends('admin.layouts.inner')
@php
    $type = is_array($type ?? null) ? $type : [];
    $isEdit = (bool) ($isEdit ?? false);
    $scope = ($scope ?? 'vod') === 'art' ? 'art' : 'vod';
    $isArt = $scope === 'art';
    $parents = is_array($parents ?? null) ? $parents : [];
    $parent = is_array($parent ?? null) ? $parent : null;
    $name = (string) ($type['name'] ?? '');
    $parentId = (string) ($type['parent_id'] ?? '0');
    $status = (string) ($type['status'] ?? '1');
    $parentName = (string) ($parent['name'] ?? '');
    $base = $isArt ? '/admin/video/art-types' : '/admin/video/types';
    $noun = $isArt ? '栏目' : '分类';
    $title = $isEdit ? ('编辑'.$noun) : ($parentName !== '' ? ($isArt ? '添加下级栏目' : '添加下级') : ($isArt ? '新建栏目' : '新增分类'));
@endphp
@section('title', $title)

@section('plain')
<div class="card card-panel type-form-page">
    <div class="card-header">
        <span>{{ $title }}@if($isEdit && $name !== '') <em>{{ $name }}</em>@endif</span>
        <a class="btn btn-muted btn-sm" href="{{ $base }}">返回{{ $noun }}</a>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">
            @if($isEdit)
                @if($isArt)
                    改名称和别名会马上影响文章栏目页。下面有文章时不要删，可以先关掉前台显示。
                @else
                    改名称和别名会马上影响前台分类页。下面有片子时不要删，可以先禁用。
                @endif
            @elseif($parentName !== '')
                将建在「{{ $parentName }}」下面。保存后会出现在上级的下一层。
            @elseif($isArt)
                栏目是文章的目录，例如 资讯 → 公告。和影片分类不是同一棵树。
            @else
                先建电影、电视剧这种一级目录。动作片、国产剧请在对应分类里点「下级」。
            @endif
        </p>

        <form class="type-form" id="type-form">
            <input type="hidden" name="id" value="{{ $isEdit ? (int) ($type['id'] ?? 0) : '' }}">
            <input type="hidden" name="mid" value="{{ $isArt ? 2 : 1 }}">

            <h3>这个{{ $noun }}</h3>
            <label for="type-name">名称</label>
            <input id="type-name" type="text" name="name" value="{{ $name }}" placeholder="{{ $isArt ? '如 资讯、公告' : '如 电影、动作片' }}" required>
            <label for="type-slug">网址别名</label>
            <input id="type-slug" type="text" name="slug" value="{{ $type['slug'] ?? '' }}" placeholder="{{ $isArt ? '如 news，可空' : '如 movie，可空' }}">
            <p class="muted field-hint">出现在{{ $isArt ? '栏目' : '分类' }}页链接里。只填英文、数字和短横线。留空则用数字 ID。</p>

            <label for="type-parent">上级</label>
            <select id="type-parent" name="parent_id">
                <option value="0" @selected($parentId === '0' || $parentId === '')>顶级（不挂在任何{{ $noun }}下）</option>
                @foreach($parents as $item)
                    <option value="{{ $item['id'] }}" @selected($parentId === (string) $item['id'])>
                        {{ str_repeat('└ ', max((int) ($item['depth'] ?? 0), 0)) }}{{ $item['name'] }}
                    </option>
                @endforeach
            </select>
            <p class="muted field-hint">选上级即可做多级。不能挂到自己的下级下面。</p>

            @if($isArt)
                @php $kind = \App\Models\Video\VideoTypeModel::normalizeKind($type['kind'] ?? 'list'); @endphp
                <label for="type-kind">类型</label>
                <select id="type-kind" name="kind">
                    <option value="list" @selected($kind === 'list')>列表（显示文章）</option>
                    <option value="hub" @selected($kind === 'hub')>频道（只做目录，下面再挂列表）</option>
                    <option value="single" @selected($kind === 'single')>单页（打开栏目即那一篇）</option>
                    <option value="link" @selected($kind === 'link')>外链</option>
                </select>
                <p class="muted field-hint" id="type-kind-hint"></p>
                <div id="type-jump-wrap" hidden>
                    <label for="type-jump">外链地址</label>
                    <input id="type-jump" type="text" name="jump_url" value="{{ $type['jump_url'] ?? '' }}" placeholder="https:// 或 /arts">
                    <p class="muted field-hint">前台点这一栏会跳走。不能挂文章。</p>
                </div>
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
            @endif

            <h3>显示</h3>
            <label for="type-sort">排序</label>
            <input id="type-sort" type="number" name="sort" value="{{ $type['sort'] ?? 0 }}">
            <p class="muted field-hint">同一上级下，数字越大越靠前。</p>
            @if($isArt)
                <div id="type-page-wrap">
                    <label for="type-page-size">分页条数</label>
                    <input id="type-page-size" type="number" name="page_size" min="0" max="100" value="{{ (int) ($type['page_size'] ?? 0) }}">
                    <p class="muted field-hint">列表栏目前台每页篇数。0 表示用站点默认 20。</p>
                </div>
            @endif
            <input type="hidden" name="status" value="0">
            <label class="inline">
                <input type="checkbox" name="status" value="1" @checked($status === '1')>
                在前台显示
            </label>
            <p class="muted field-hint">关掉后前台菜单里不再出现，{{ $isArt ? '文章' : '片子' }}还在。</p>

            <details class="settings-details" @if(trim((string) ($type['seo_title'] ?? '').($type['seo_keywords'] ?? '').($type['seo_description'] ?? '')) !== '') open @endif>
                <summary>搜索标题（可空）</summary>
                <p class="muted field-hint">给搜索引擎看。留空则用站点设置里的{{ $noun }}标题模板。</p>
                <label for="type-seo-title">标题</label>
                <input id="type-seo-title" type="text" name="seo_title" value="{{ $type['seo_title'] ?? '' }}" placeholder="{type} - {site}">
                <label for="type-seo-keywords">关键词</label>
                <input id="type-seo-keywords" type="text" name="seo_keywords" value="{{ $type['seo_keywords'] ?? '' }}">
                <label for="type-seo-description">描述</label>
                <textarea id="type-seo-description" name="seo_description" rows="3">{{ $type['seo_description'] ?? '' }}</textarea>
            </details>

            @if($isArt)
                <details class="settings-details" @if(trim((string) ($type['tpl_list'] ?? '').($type['tpl_detail'] ?? '')) !== '') open @endif>
                    <summary>模板（可空）</summary>
                    <p class="muted field-hint">对应主题里的视图名，例如 arts_news 会找 vod.arts_news。找不到则用默认。</p>
                    <div id="type-tpl-list-wrap">
                        <label for="type-tpl-list">列表 / 频道模板</label>
                        <input id="type-tpl-list" type="text" name="tpl_list" value="{{ $type['tpl_list'] ?? '' }}" placeholder="空则用默认">
                    </div>
                    <label for="type-tpl-detail">详情 / 单页模板</label>
                    <input id="type-tpl-detail" type="text" name="tpl_detail" value="{{ $type['tpl_detail'] ?? '' }}" placeholder="空则用默认">
                </details>
            @endif

            <div class="form-actions">
                <button type="submit" class="btn" id="type-save">保存</button>
                <button type="button" class="btn btn-muted" id="type-save-child">保存并添加下级</button>
                @if($isArt)
                    <button type="button" class="btn btn-muted" id="type-save-art">保存并写文章</button>
                @endif
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
    var form = document.getElementById('type-form');
    var isEdit = !!String(form.id.value || '').trim();
    var base = @json($base);

    function save(next) {
        var data = U.formData(form);
        if (!String(data.name || '').trim()) {
            U.toast('请填写名称', 'err');
            document.getElementById('type-name').focus();
            return;
        }
        if (!isEdit) delete data.id;
        U.loading(true);
        U.post(base + '/save', data).then(function (res) {
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
            if (next === 'write' && id) {
                location.href = '/admin/video/arts/create?type_id=' + encodeURIComponent(id);
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
    var writeBtn = document.getElementById('type-save-art');
    if (writeBtn) writeBtn.addEventListener('click', function () { save('write'); });

    var kindSel = document.getElementById('type-kind');
    var hints = {
        list: '列表页显示这个栏目和下级里的文章。',
        hub: '频道只做目录，下面再挂列表栏目。前台打开这一栏会看到下级。',
        single: '前台打开这一栏，显示该栏目下排序最高的一篇已发布文章。',
        link: '前台点这一栏会跳到填写的地址，不能挂文章。'
    };
    function syncKind() {
        var kind = kindSel ? kindSel.value : 'list';
        var jump = document.getElementById('type-jump-wrap');
        var page = document.getElementById('type-page-wrap');
        var tplList = document.getElementById('type-tpl-list-wrap');
        var hint = document.getElementById('type-kind-hint');
        if (jump) jump.hidden = kind !== 'link';
        if (page) page.hidden = kind !== 'list';
        if (tplList) tplList.hidden = kind === 'link' || kind === 'single';
        if (hint) hint.textContent = hints[kind] || '';
        if (writeBtn) writeBtn.hidden = kind === 'hub' || kind === 'link';
    }
    if (kindSel) {
        kindSel.addEventListener('change', syncKind);
        syncKind();
    }
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
