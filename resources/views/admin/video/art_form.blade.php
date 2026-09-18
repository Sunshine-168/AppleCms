@extends('admin.layouts.inner')
@section('title', $isEdit ? '编辑文章' : '写文章')

@php
    $art = is_array($art ?? null) ? $art : [];
    $isEdit = (bool) ($isEdit ?? false);
    $types = is_array($types ?? null) ? $types : [];
    $title = (string) ($art['title'] ?? '');
    $blurb = (string) ($art['blurb'] ?? '');
    $cover = trim((string) ($art['cover'] ?? ''));
    $content = (string) ($art['content'] ?? '');
    $typeId = (string) ($art['type_id'] ?? '0');
    $status = (string) ($art['status'] ?? '1');
    $hits = (int) ($art['hits'] ?? 0);
    $sort = (int) ($art['sort'] ?? 0);
    $author = (string) ($art['author'] ?? '');
    $source = (string) ($art['source'] ?? '');
    $tag = (string) ($art['tag'] ?? '');
    $tagExtra = (string) ($art['tag_extra'] ?? '');
    $tagIds = array_map('intval', is_array($art['tag_ids'] ?? null) ? $art['tag_ids'] : []);
    $tags = is_array($tags ?? null) ? $tags : [];
    $tagsReady = (bool) ($tagsReady ?? false);
    $seoTitle = (string) ($art['seo_title'] ?? '');
    $seoKey = (string) ($art['seo_key'] ?? '');
    $seoDes = (string) ($art['seo_des'] ?? '');
    $flagSet = array_values(array_filter(array_map('trim', explode(',', (string) ($art['flags'] ?? '')))));
    $publishedAt = (int) ($art['published_at'] ?? 0);
    $publishedLocal = $publishedAt > 0 ? date('Y-m-d\TH:i', $publishedAt) : '';
    $listed = ! empty($art['listed']);
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
                    <span>{{ $isEdit ? '编辑文章' : '写文章' }}</span>
                </div>
                <div class="card-body">
                    <label for="art-title">标题</label>
                    <input id="art-title" type="text" name="title" class="entry-title" value="{{ $title }}" placeholder="读者看到的标题" required>
                    <label for="art-blurb">摘要</label>
                    <textarea id="art-blurb" name="blurb" rows="3" placeholder="列表和搜索用的一句话，可空">{{ $blurb }}</textarea>
                    <label for="art-content">正文</label>
                    <textarea id="art-content" name="content" class="cms-editor art-content" placeholder="正文">{{ $content }}</textarea>
                    <details class="entry-seo">
                        <summary>搜索标题 / 关键字 / 描述</summary>
                        <label for="art-seo-title">搜索标题</label>
                        <input id="art-seo-title" type="text" name="seo_title" value="{{ $seoTitle }}" placeholder="空则用标题">
                        <label for="art-seo-key">关键字</label>
                        <input id="art-seo-key" type="text" name="seo_key" value="{{ $seoKey }}" placeholder="逗号分隔，可空">
                        <label for="art-seo-des">描述</label>
                        <textarea id="art-seo-des" name="seo_des" rows="2" placeholder="空则用摘要或正文截取">{{ $seoDes }}</textarea>
                    </details>
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
                    <p class="muted field-hint">草稿前台看不到。填了未来时间也看不到，到点才出现。</p>
                    <label for="art-published-at">定时发布</label>
                    <input id="art-published-at" type="datetime-local" name="published_at" value="{{ $publishedLocal }}">
                    <p class="muted field-hint">可空。空则用创建时间；填未来时间则到点才出现在前台。</p>
                    <label for="art-hits">点击</label>
                    <input id="art-hits" type="number" name="hits" min="0" value="{{ $hits }}">
                    <p class="muted field-hint">一般不用改，前台浏览会自己加。</p>
                    <div class="entry-save">
                        <button class="btn" type="submit" id="art-save">保存</button>
                        <a class="btn btn-muted" href="/admin/video/arts">返回文章</a>
                    </div>
                    @if($isEdit && $listed && $frontUrl !== '')
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
                            <option value="{{ $type['id'] }}" @selected($typeId === (string) $type['id'])>{{ $type['name'] }}@if(!empty($type['kind_label']) && ($type['kind'] ?? 'list') !== 'list') · {{ $type['kind_label'] }}@endif</option>
                        @endforeach
                    </select>
                    @if($types === [])
                        <p class="muted field-hint">还没有文章栏目。<a href="/admin/video/art-types/create">去建一个栏目</a>，也可以先不选。频道和外链不能挂稿。</p>
                    @else
                        <p class="muted field-hint">决定这篇出现在哪个文章栏目。频道和外链不能挂稿，请选列表或单页。</p>
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
                    <label for="art-author">署名</label>
                    <input id="art-author" type="text" name="author" value="{{ $author }}" placeholder="纯文本，不是会员">
                    <label for="art-source">来源</label>
                    <input id="art-source" type="text" name="source" value="{{ $source }}" placeholder="转载出处，可空">
                    <label>标签</label>
                    @if($tags !== [])
                        <div class="choice-grid">
                            @foreach($tags as $opt)
                                <label class="inline">
                                    <input type="checkbox" class="js-art-tag" name="tag_ids[]" value="{{ (int) $opt['id'] }}" @checked(in_array((int) $opt['id'], $tagIds, true))>
                                    {{ $opt['name'] }}
                                </label>
                            @endforeach
                        </div>
                    @elseif(! $tagsReady)
                        <p class="muted field-hint">标签表还没建。可先在下面填词，迁移后再进标签台归档。</p>
                    @else
                        <p class="muted field-hint">还没有标签。<a href="/admin/video/art-tags">去标签台添加</a>，也可以在下面直接填新词。</p>
                    @endif
                    <label for="art-tag-extra">新标签</label>
                    <input id="art-tag-extra" type="text" name="tag_extra" value="{{ $tagExtra !== '' ? $tagExtra : ($tags === [] ? $tag : '') }}" placeholder="逗号分隔，没有的会新建">
                    <p class="muted field-hint">勾选已有的，或在这里填新词。和影片标签不是同一套。</p>
                </div>
            </div>
            <details class="card card-panel entry-aside-more">
                <summary>更多</summary>
                <div class="card-body">
                    <label>推荐属性</label>
                    <div class="choice-grid">
                        <label class="inline"><input type="checkbox" class="js-art-flag" name="flag_list[]" value="top" @checked(in_array('top', $flagSet, true))> 置顶</label>
                        <label class="inline"><input type="checkbox" class="js-art-flag" name="flag_list[]" value="recommend" @checked(in_array('recommend', $flagSet, true))> 推荐</label>
                        <label class="inline"><input type="checkbox" class="js-art-flag" name="flag_list[]" value="hot" @checked(in_array('hot', $flagSet, true))> 热门</label>
                    </div>
                    <label for="art-sort">排序</label>
                    <input id="art-sort" type="number" name="sort" min="0" value="{{ $sort }}">
                    <p class="muted field-hint">越大越靠前。标签按 ID 倒序时用不上。</p>
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
        var flags = [];
        form.querySelectorAll('.js-art-flag:checked').forEach(function (el) {
            flags.push(el.value);
        });
        data.flags = flags.join(',');
        delete data['flag_list[]'];
        var tagIds = [];
        form.querySelectorAll('.js-art-tag:checked').forEach(function (el) {
            tagIds.push(el.value);
        });
        data.tag_ids = tagIds;
        delete data['tag_ids[]'];
        if (!String(data.published_at || '').trim()) {
            data.published_at = 0;
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
