@extends('admin.layouts.inner')
@section('title', $isEdit ? '编辑分类' : ($parent ? '添加下级' : '新增分类'))

@php
    $type = is_array($type ?? null) ? $type : [];
    $isEdit = (bool) ($isEdit ?? false);
    $parents = is_array($parents ?? null) ? $parents : [];
    $parent = is_array($parent ?? null) ? $parent : null;
    $name = (string) ($type['name'] ?? '');
    $parentId = (string) ($type['parent_id'] ?? '0');
    $mid = (string) ($type['mid'] ?? '1');
    $status = (string) ($type['status'] ?? '1');
    $parentName = (string) ($parent['name'] ?? '');
    $title = $isEdit ? '编辑分类' : ($parentName !== '' ? '添加下级' : '新增分类');
@endphp

@section('plain')
<div class="card card-panel type-form-page">
    <div class="card-header">
        <span>{{ $title }}@if($isEdit && $name !== '') <em>{{ $name }}</em>@endif</span>
        <a class="btn btn-muted btn-sm" href="/admin/video/types">返回分类</a>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">
            @if($isEdit)
                改名称和别名会马上影响前台分类页。下面有片子时不要删，可以先禁用。
            @elseif($parentName !== '')
                将建在「{{ $parentName }}」下面。保存后会出现在上级的下一层，例如 电影 → 动作片。
            @else
                先建电影、电视剧这种一级目录。动作片、国产剧请在对应分类里点「下级」。
            @endif
        </p>

        <form class="type-form" id="type-form">
            <input type="hidden" name="id" value="{{ $isEdit ? (int) ($type['id'] ?? 0) : '' }}">

            <h3>这个分类</h3>
            <label for="type-name">名称</label>
            <input id="type-name" type="text" name="name" value="{{ $name }}" placeholder="如 电影、动作片" required>
            <label for="type-slug">网址别名</label>
            <input id="type-slug" type="text" name="slug" value="{{ $type['slug'] ?? '' }}" placeholder="如 movie，可空">
            <p class="muted field-hint">出现在分类页链接里。只填英文、数字和短横线。留空则用数字 ID。</p>

            <label for="type-parent">上级</label>
            <select id="type-parent" name="parent_id">
                <option value="0" @selected($parentId === '0' || $parentId === '')>顶级（不挂在任何分类下）</option>
                @foreach($parents as $item)
                    <option value="{{ $item['id'] }}" @selected($parentId === (string) $item['id'])>
                        {{ str_repeat('└ ', max((int) ($item['depth'] ?? 0), 0)) }}{{ $item['name'] }}
                    </option>
                @endforeach
            </select>
            <p class="muted field-hint">选上级即可做多级，例如 电视剧 → 国产剧。不能挂到自己的下级下面。</p>

            <label for="type-mid">用来放什么</label>
            <select id="type-mid" name="mid">
                <option value="1" @selected($mid === '1')>影片</option>
                <option value="2" @selected($mid === '2')>文章</option>
                <option value="3" @selected($mid === '3')>网址导航</option>
            </select>
            <p class="muted field-hint">影片进「影片」列表。文章进「文章」。网址导航很少用，一般不要选。</p>

            <h3>显示</h3>
            <label for="type-sort">排序</label>
            <input id="type-sort" type="number" name="sort" value="{{ $type['sort'] ?? 0 }}">
            <p class="muted field-hint">同一上级下，数字越大越靠前。</p>
            <input type="hidden" name="status" value="0">
            <label class="inline">
                <input type="checkbox" name="status" value="1" @checked($status === '1')>
                在前台显示
            </label>
            <p class="muted field-hint">关掉后前台菜单里不再出现，片子还在。</p>

            <details class="settings-details" @if(trim((string) ($type['seo_title'] ?? '').($type['seo_keywords'] ?? '').($type['seo_description'] ?? '')) !== '') open @endif>
                <summary>搜索标题（可空）</summary>
                <p class="muted field-hint">给搜索引擎看。留空则用站点设置里的分类标题模板。</p>
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
                <a class="btn btn-muted" href="/admin/video/types">取消</a>
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

    function save(goChild) {
        var data = U.formData(form);
        if (!String(data.name || '').trim()) {
            U.toast('请填写名称', 'err');
            document.getElementById('type-name').focus();
            return;
        }
        if (!isEdit) delete data.id;
        U.loading(true);
        U.post('/admin/video/types/save', data).then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) {
                U.toast((res && res.msg) || '保存失败', 'err');
                return;
            }
            var id = (res.data && res.data.id) || data.id;
            U.toast('已保存', 'ok');
            if (goChild && id) {
                location.href = '/admin/video/types/create?parent_id=' + encodeURIComponent(id);
                return;
            }
            location.href = '/admin/video/types';
        }).catch(function () {
            U.loading(false);
            U.toast('保存失败', 'err');
        });
    }
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        save(false);
    });
    document.getElementById('type-save-child').addEventListener('click', function () { save(true); });
})();
</script>
@endpush
