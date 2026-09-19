@extends('admin.layouts.inner')
@section('title', $isEdit ? '编辑作品' : '新增作品')

@php
    $work = is_array($work ?? null) ? $work : [];
    $isEdit = (bool) ($isEdit ?? false);
    $types = is_array($types ?? null) ? $types : [];
    $selectedTags = is_array($selectedTags ?? null) ? $selectedTags : [];
    $selectedAuthors = is_array($selectedAuthors ?? null) ? $selectedAuthors : [];
    $tagsReady = (bool) ($tagsReady ?? true);
    $authorsReady = (bool) ($authorsReady ?? true);
    $title = (string) ($work['title'] ?? '');
    $typeId = (int) ($work['type_id'] ?? 0);
    $cover = trim((string) ($work['cover'] ?? ''));
    $serialize = (string) ($work['serialize'] ?? '0');
    $recommend = (string) ($work['recommend'] ?? '0');
    $yid = (string) ($work['yid'] ?? '0');
    $status = (string) ($work['status'] ?? '1');
    $remarks = (string) ($work['remarks'] ?? '');
    $content = (string) ($work['content'] ?? '');
    $hits = (int) ($work['hits'] ?? 0);
    $sort = (int) ($work['sort'] ?? 0);
    $frontUrl = trim((string) ($work['front_url'] ?? ''));
    $workId = (int) ($work['id'] ?? 0);
    $back = ((string) ($yid) === '1' && ! $isEdit) ? '/admin/video/mangas?desk=pending' : '/admin/video/mangas';
@endphp

@section('plain')
<div class="card card-panel">
    <div class="card-header">
        <span>{{ $isEdit ? '编辑作品' : '新增作品' }}@if($isEdit && $title !== '') <em>{{ $title }}</em>@endif</span>
        <div>
            @if($isEdit && $workId > 0)
                <a class="btn btn-muted btn-sm" href="/admin/video/mangas?desk=work&manga_id={{ $workId }}">管理章节</a>
            @endif
            <a class="btn btn-muted btn-sm" href="{{ $back }}">返回作品</a>
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">章节和贴图请到作品「管理」工作台操作，这里只改作品信息。</p>
        <form class="tag-form" id="manga-work-form">
            <input type="hidden" name="id" value="{{ $isEdit ? $workId : '' }}">

            <h3>基本</h3>
            <label for="work-title">名称</label>
            <input id="work-title" class="entry-title" type="text" name="title" value="{{ $title }}" required autofocus>

            <label for="work-type">分类</label>
            <select id="work-type" name="type_id">
                <option value="0">未分类</option>
                @foreach($types as $type)
                    <option value="{{ $type['id'] }}" @selected($typeId === (int) $type['id'])>{{ $type['label'] ?? $type['name'] }}</option>
                @endforeach
            </select>
            <p class="muted field-hint">一部作品只能挂一个分类。要多入口聚合请用标签。</p>

            <label>作者</label>
            <div class="pick-field" id="work-author-pick"
                 data-ready="{{ $authorsReady ? '1' : '0' }}"
                 data-search="/admin/video/manga-authors/list"
                 data-create="/admin/video/manga-authors/save"
                 data-browse="1"
                 data-placeholder="搜作者名，点一下可看近期；回车可新建"
                 data-empty="还没有作者。输入名字回车即可新建，或去<a href=&quot;/admin/video/manga-authors&quot; target=&quot;_blank&quot; rel=&quot;noopener&quot;>作者台</a>。"
                 data-selected='@json($selectedAuthors, JSON_UNESCAPED_UNICODE)'></div>
            <p class="muted field-hint">不铺全表。搜索勾选；框内点一下先出一小批，量大也能用。</p>

            <label for="work-cover">封面</label>
            <div class="field-inline">
                <input id="work-cover" type="text" name="cover" value="{{ $cover }}" placeholder="图片地址">
                <button type="button" class="btn btn-sm" id="work-cover-pick">上传</button>
            </div>
            <img class="img-preview" id="work-cover-preview" alt="" @if($cover === '') style="display:none" @else src="{{ $cover }}" @endif>

            <label for="work-serialize">连载</label>
            <select id="work-serialize" name="serialize">
                <option value="0" @selected($serialize === '0')>连载</option>
                <option value="1" @selected($serialize === '1')>完结</option>
            </select>

            <h3>标签与推荐</h3>
            <label>标签</label>
            <div class="pick-field" id="work-tag-pick"
                 data-ready="{{ $tagsReady ? '1' : '0' }}"
                 data-search="/admin/video/manga-tags/list"
                 data-create="/admin/video/manga-tags/save"
                 data-browse="1"
                 data-placeholder="搜标签名，点一下可看近期；回车可新建"
                 data-empty="还没有标签。输入词回车即可新建，或去<a href=&quot;/admin/video/manga-tags&quot; target=&quot;_blank&quot; rel=&quot;noopener&quot;>标签台</a>。"
                 data-selected='@json($selectedTags, JSON_UNESCAPED_UNICODE)'></div>
            <p class="muted field-hint">和文章/影片标签不是同一套。几千个也只搜不铺；点输入框会先出最近一批。</p>

            <label for="work-recommend">推荐</label>
            <select id="work-recommend" name="recommend">
                <option value="0" @selected($recommend === '0')>否</option>
                <option value="1" @selected($recommend === '1')>是</option>
            </select>
            <p class="muted field-hint">选「是」会出现在首页推荐区和 /manga?recommend=1。</p>

            <h3>发布</h3>
            <label for="work-yid">审核</label>
            <select id="work-yid" name="yid">
                <option value="0" @selected($yid === '0')>已审</option>
                <option value="1" @selected($yid === '1')>待审</option>
            </select>
            <label for="work-status">状态</label>
            <select id="work-status" name="status">
                <option value="1" @selected($status === '1')>上架</option>
                <option value="0" @selected($status === '0')>下架</option>
            </select>
            <label for="work-remarks">备注</label>
            <input id="work-remarks" type="text" name="remarks" value="{{ $remarks }}">
            <label for="work-content">简介</label>
            <textarea id="work-content" name="content" rows="6">{{ $content }}</textarea>
            <label for="work-hits">人气 / 浏览</label>
            <input id="work-hits" type="number" name="hits" min="0" value="{{ $hits }}">
            <p class="muted field-hint">前台打开详情会自动累加，一般不用手改。</p>
            <label for="work-sort">排序</label>
            <input id="work-sort" type="number" name="sort" value="{{ $sort }}">

            <div class="entry-save">
                <button class="btn" type="submit">{{ $isEdit ? '保存' : '创建作品' }}</button>
                <a class="btn btn-muted" href="{{ $back }}">取消</a>
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
    var form = document.getElementById('manga-work-form');
    var isEdit = !!String(form.querySelector('input[name="id"]').value || '').trim();
    U.bindImageField(form, {
        input: '[name=cover]',
        btn: '#work-cover-pick',
        preview: '#work-cover-preview'
    });
    var authorPick = U.bindPickField(document.getElementById('work-author-pick'));
    var tagPick = U.bindPickField(document.getElementById('work-tag-pick'));

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var data = U.formData(form);
        if (!String(data.title || '').trim()) {
            U.toast('请填写名称', 'err');
            form.querySelector('[name=title]').focus();
            return;
        }
        data.author_ids = authorPick.ids();
        data.author_extra = '';
        data.tag_ids = tagPick.ids();
        data.tag_extra = '';
        delete data['author_ids[]'];
        delete data['tag_ids[]'];
        U.loading(true);
        U.post('/admin/video/mangas/save', data).then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) {
                U.toast((res && res.msg) || '保存失败', 'err');
                return;
            }
            var id = (res.data && res.data.id) || data.id;
            U.toast(isEdit ? '已保存' : '已创建', 'ok');
            if (!isEdit && id) {
                location.href = '/admin/video/mangas/' + encodeURIComponent(id) + '/edit';
            }
        }).catch(function () {
            U.loading(false);
            U.toast('保存失败', 'err');
        });
    });
})();
</script>
@endpush
