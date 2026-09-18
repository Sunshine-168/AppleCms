@extends('admin.layouts.inner')
@section('title', $title ?? admin_t('nav.manga'))

@php
    $desk = in_array((string) ($desk ?? ''), ['pending', 'types', 'chapters', 'pics', 'comments', 'works'], true)
        ? (string) $desk
        : 'works';
    $types = is_array($types ?? null) ? $types : [];
    $filterMangaId = (int) ($filterMangaId ?? 0);
    $hint = (string) ($hint ?? '');
@endphp

@section('plain')
<div class="card card-panel manga-board desk-board" id="manga-board">
    <div class="card-header">
        <span>漫画 <em id="manga-count"></em></span>
        <div>
            <button type="button" class="btn btn-sm" id="manga-add-btn">新增</button>
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">独立漫画库，不是影片分类。分类、章节、图片、评论都在这页切换。关掉插件后前台 /manga 一起消失。</p>
        <div class="queue-chips" id="manga-desks">
            <a class="chip{{ $desk === 'works' ? ' active' : '' }}" href="/admin/video/mangas">作品</a>
            <a class="chip{{ $desk === 'pending' ? ' active' : '' }}" href="/admin/video/mangas?desk=pending">待审</a>
            <a class="chip{{ $desk === 'types' ? ' active' : '' }}" href="/admin/video/mangas?desk=types">分类</a>
            <a class="chip{{ $desk === 'chapters' ? ' active' : '' }}" href="/admin/video/mangas?desk=chapters">章节</a>
            <a class="chip{{ $desk === 'pics' ? ' active' : '' }}" href="/admin/video/mangas?desk=pics">图片</a>
            <a class="chip{{ $desk === 'comments' ? ' active' : '' }}" href="/admin/video/mangas?desk=comments">评论</a>
        </div>
        <form class="filter-bar" id="manga-search" onsubmit="return false;">
            <input type="hidden" name="yid" value="{{ $desk === 'pending' ? '1' : ($desk === 'works' ? '0' : '') }}">
            <input type="hidden" name="manga_id" value="{{ $filterMangaId > 0 ? $filterMangaId : '' }}">
            <input type="search" name="q" placeholder="{{ $desk === 'types' ? '搜分类' : ($desk === 'chapters' ? '搜章节' : ($desk === 'pics' ? '搜图片地址' : ($desk === 'comments' ? '搜评论、作品' : '搜名称、作者、标签'))) }}" autocomplete="off">
            @if(in_array($desk, ['works', 'pending'], true) && $types !== [])
                <select name="type_id" aria-label="分类">
                    <option value="">全部分类</option>
                    @foreach($types as $type)
                        <option value="{{ $type['id'] }}">{{ $type['label'] ?? $type['name'] }}</option>
                    @endforeach
                </select>
                <select name="serialize" aria-label="连载">
                    <option value="">全部状态</option>
                    <option value="0">连载</option>
                    <option value="1">完结</option>
                </select>
                <select name="recommend" aria-label="推荐">
                    <option value="">全部</option>
                    <option value="1">推荐</option>
                </select>
            @endif
            @if($desk === 'comments')
                <select name="status" aria-label="状态">
                    <option value="">全部状态</option>
                    <option value="1">显示</option>
                    <option value="0">待审</option>
                </select>
            @endif
            <button type="button" class="btn btn-sm" id="manga-search-btn">查询</button>
            <button type="reset" class="btn btn-muted btn-sm" id="manga-reset-btn">重置</button>
        </form>
        @if(in_array($desk, ['works', 'pending', 'comments', 'chapters', 'pics'], true))
            <div class="batch-bar" id="manga-batch" hidden>
                <strong id="manga-batch-count">已选 0 条</strong>
                @if(in_array($desk, ['works', 'pending'], true))
                    <button type="button" class="btn btn-sm" id="manga-batch-on">上架</button>
                    <button type="button" class="btn btn-muted btn-sm" id="manga-batch-off">下架</button>
                    <button type="button" class="btn btn-sm" id="manga-batch-pass">通过审核</button>
                    <button type="button" class="btn btn-sm" id="manga-batch-rec">推荐</button>
                    <button type="button" class="btn btn-muted btn-sm" id="manga-batch-unrec">取消推荐</button>
                @endif
                @if($desk === 'comments')
                    <button type="button" class="btn btn-sm" id="manga-batch-on">通过</button>
                    <button type="button" class="btn btn-muted btn-sm" id="manga-batch-off">隐藏</button>
                @endif
                <button type="button" class="btn btn-danger btn-sm" id="manga-batch-del">删除</button>
                <button type="button" class="btn btn-muted btn-sm" id="manga-batch-clear">取消选择</button>
            </div>
        @endif
        <div id="manga-table"></div>
    </div>
</div>

<template id="manga-work-tpl">
    <form>
        <input type="hidden" name="id">
        <label>名称</label>
        <input type="text" name="title" required>
        <label>分类</label>
        <select name="type_id">
            <option value="0">未分类</option>
            @foreach($types as $type)
                <option value="{{ $type['id'] }}">{{ $type['label'] ?? $type['name'] }}</option>
            @endforeach
        </select>
        <label>作者</label>
        <input type="text" name="author">
        <label>封面</label>
        <div class="field-inline">
            <input type="text" name="cover" placeholder="图片地址">
            <button type="button" class="btn btn-sm js-cover-pick">上传</button>
        </div>
        <img class="img-preview js-cover-preview" alt="">
        <label>连载</label>
        <select name="serialize">
            <option value="0">连载</option>
            <option value="1">完结</option>
        </select>
        <label>标签</label>
        <input type="text" name="tags" placeholder="多个用逗号分隔">
        <label>推荐</label>
        <select name="recommend">
            <option value="0">否</option>
            <option value="1">是</option>
        </select>
        <label>审核</label>
        <select name="yid">
            <option value="0">已审</option>
            <option value="1">待审</option>
        </select>
        <label>状态</label>
        <select name="status">
            <option value="1">上架</option>
            <option value="0">下架</option>
        </select>
        <label>备注</label>
        <input type="text" name="remarks">
        <label>简介</label>
        <textarea name="content"></textarea>
        <label>人气</label>
        <input type="number" name="hits" value="0">
        <label>排序</label>
        <input type="number" name="sort" value="0">
    </form>
</template>

<template id="manga-type-tpl">
    <form>
        <input type="hidden" name="id">
        <label>名称</label>
        <input type="text" name="name" required>
        <label>上级</label>
        <select name="parent_id">
            <option value="0">顶级</option>
            @foreach($types as $type)
                <option value="{{ $type['id'] }}">{{ $type['label'] ?? $type['name'] }}</option>
            @endforeach
        </select>
        <p class="muted field-hint">选已有分类作上级。不要选自己。</p>
        <label>排序</label>
        <input type="number" name="sort" value="0">
        <label>状态</label>
        <select name="status">
            <option value="1">显示</option>
            <option value="0">隐藏</option>
        </select>
    </form>
</template>

<template id="manga-chapter-tpl">
    <form>
        <input type="hidden" name="id">
        <label>漫画ID</label>
        <input type="number" name="manga_id" required>
        <label>章节名</label>
        <input type="text" name="name" required>
        <label>排序</label>
        <input type="number" name="sort" value="0">
        <label>图片地址</label>
        <textarea name="pics" rows="8" placeholder="每行一条，http(s) 或 / 开头的站内路径"></textarea>
        <div class="field-inline" style="margin-top:8px">
            <button type="button" class="btn btn-sm js-pics-upload">上传并追加</button>
        </div>
        <img class="img-preview js-pics-preview" alt="">
        <p class="muted field-hint">图片地址每行一条。可上传追加。保存后会写入图片表。javascript: 不会收录。</p>
    </form>
</template>

<template id="manga-pic-tpl">
    <form>
        <input type="hidden" name="id">
        <label>漫画ID</label>
        <input type="number" name="manga_id" required>
        <label>章节ID</label>
        <input type="number" name="chapter_id" required>
        <label>图片地址</label>
        <div class="field-inline">
            <input type="text" name="url" required placeholder="http(s) 或 / 开头">
            <button type="button" class="btn btn-sm js-pic-pick">上传</button>
        </div>
        <img class="img-preview js-pic-preview" alt="">
        <label>排序</label>
        <input type="number" name="sort" value="0">
    </form>
</template>

<template id="manga-comment-tpl">
    <form>
        <input type="hidden" name="id">
        <label>漫画ID</label>
        <input type="number" name="manga_id" required>
        <label>昵称</label>
        <input type="text" name="author_name">
        <label>内容</label>
        <textarea name="content" rows="4" required></textarea>
        <label>状态</label>
        <select name="status">
            <option value="1">显示</option>
            <option value="0">待审 / 隐藏</option>
        </select>
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var desk = @json($desk);
    var filterMangaId = @json($filterMangaId);
    var form = document.getElementById('manga-search');
    var countEl = document.getElementById('manga-count');
    var addBtn = document.getElementById('manga-add-btn');

    var modules = {
        works: 'mangas',
        pending: 'mangas',
        types: 'manga_types',
        chapters: 'manga_chapters',
        pics: 'manga_pics',
        comments: 'manga_comments'
    };
    var module = modules[desk] || 'mangas';
    var addLabels = {
        works: '新增作品',
        pending: '新增作品',
        types: '新增分类',
        chapters: '新增章节',
        pics: '新增图片',
        comments: '新增评论'
    };
    if (addBtn) addBtn.textContent = addLabels[desk] || '新增';

    function cleanWhere(data) {
        var out = {};
        Object.keys(data || {}).forEach(function (k) {
            if (data[k] !== '' && data[k] != null) out[k] = data[k];
        });
        return out;
    }
    function queryWhere() {
        var data = cleanWhere(U.formData(form));
        data.limit = 20;
        if (desk === 'pending') data.yid = 1;
        if (desk === 'works') data.yid = 0;
        return data;
    }
    function isFiltered(where) {
        return Object.keys(where || {}).some(function (k) {
            if (k === 'limit' || k === 'yid') return false;
            return where[k] !== '' && where[k] != null;
        });
    }
    function emptyHtml(_parsed, where) {
        if (isFiltered(where)) {
            return '<div class="list-empty"><p>没有符合条件的记录</p><p><button type="button" class="btn btn-muted btn-sm" id="manga-empty-reset">清除筛选</button></p></div>';
        }
        var copy = {
            works: ['还没有漫画作品', '新增作品'],
            pending: ['没有待审作品', '新增作品'],
            types: ['还没有分类', '新增分类'],
            chapters: ['还没有章节', '新增章节'],
            pics: ['还没有图片', '新增图片'],
            comments: ['还没有评论', '新增评论']
        }[desk] || ['还没有记录', '新增'];
        return '<div class="list-empty"><p>' + copy[0] + '</p><p><button type="button" class="btn btn-primary btn-sm" id="manga-empty-add">' + copy[1] + '</button></p></div>';
    }

    function workStatus(d) {
        var st = String(d.status) === '1' ? '上架' : '下架';
        var yid = d.yid_label || (String(d.yid) === '1' ? '待审' : '已审');
        return U.escape(st + ' / ' + yid);
    }

    var cols = [];
    if (desk === 'works' || desk === 'pending') {
        cols = [
            {check: true, width: 36},
            {title: '名称', html: function (d) {
                return '<a class="entry-row-title js-edit" href="#">' + U.escape(d.title || '未填写') + '</a>';
            }},
            {title: '分类', html: function (d) { return U.escape(d.type_name || '未分类'); }},
            {title: '作者', html: function (d) { return U.escape(d.author || ''); }},
            {title: '连载', width: 72, html: function (d) { return U.escape(d.serialize_label || ''); }},
            {title: '状态/待审', width: 110, html: workStatus},
            {title: '章节数', width: 72, html: function (d) { return U.escape(String(d.chapter_count == null ? 0 : d.chapter_count)); }},
            {title: '操作', cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-edit">编辑</a><a href="#" class="btn-link js-chapters">章节</a>';
            }}
        ];
    } else if (desk === 'comments') {
        cols = [
            {check: true, width: 36},
            {title: '内容', html: function (d) { return '<a class="js-edit" href="#">' + U.escape(d.content || '未填写') + '</a>'; }},
            {title: '作品', html: function (d) { return U.escape(d.manga_title || ('#' + (d.manga_id || ''))); }},
            {title: '昵称', width: 100, html: function (d) { return U.escape(d.author_name || ''); }},
            {title: '状态', width: 90, html: function (d) {
                return String(d.status) === '1' ? U.status(true, '显示') : U.status(false, '待审');
            }},
            {title: '操作', cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-edit">编辑</a><a href="#" class="btn-link js-del">删除</a>';
            }}
        ];
    } else if (desk === 'types') {
        cols = [
            {title: '名称', html: function (d) { return '<a class="js-edit" href="#">' + U.escape(d.name || '未填写') + '</a>'; }},
            {title: '上级', width: 120, html: function (d) { return U.escape(d.parent_name || (String(d.parent_id || 0) === '0' ? '顶级' : String(d.parent_id))); }},
            {title: '排序', width: 72, html: function (d) { return U.escape(String(d.sort || 0)); }},
            {title: '状态', width: 72, html: function (d) {
                return String(d.status) === '1' ? U.status(true, '显示') : U.status(false, '隐藏');
            }},
            {title: '操作', cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-edit">编辑</a><a href="#" class="btn-link js-del">删除</a>';
            }}
        ];
    } else if (desk === 'chapters') {
        cols = [
            {check: true, width: 36},
            {title: '章节', html: function (d) { return '<a class="js-edit" href="#">' + U.escape(d.name || '未填写') + '</a>'; }},
            {title: '作品', html: function (d) { return U.escape(d.manga_title || ('#' + (d.manga_id || ''))); }},
            {title: '图片数', width: 72, html: function (d) { return U.escape(String(d.pic_count == null ? 0 : d.pic_count)); }},
            {title: '排序', width: 72, html: function (d) { return U.escape(String(d.sort || 0)); }},
            {title: '操作', cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-edit">编辑</a><a href="#" class="btn-link js-del">删除</a>';
            }}
        ];
    } else if (desk === 'pics') {
        cols = [
            {check: true, width: 36},
            {title: '图片', html: function (d) { return '<a class="js-edit" href="#">' + U.escape(d.url || '未填写') + '</a>'; }},
            {title: '作品', html: function (d) { return U.escape(d.manga_title || ('#' + (d.manga_id || ''))); }},
            {title: '章节', html: function (d) { return U.escape(d.chapter_name || ('#' + (d.chapter_id || ''))); }},
            {title: '排序', width: 72, html: function (d) { return U.escape(String(d.sort || 0)); }},
            {title: '操作', cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-edit">编辑</a><a href="#" class="btn-link js-del">删除</a>';
            }}
        ];
    }

    var table = U.table({
        el: '#manga-table',
        url: '/admin/video/' + module + '/list',
        where: queryWhere(),
        emptyHtml: emptyHtml,
        onDraw: function (_wrap, list) {
            countEl.textContent = list.length ? '· ' + list.length : '';
            var add = document.getElementById('manga-empty-add');
            var reset = document.getElementById('manga-empty-reset');
            if (add) add.addEventListener('click', function () { openDialog('add'); });
            if (reset) reset.addEventListener('click', function () { form.reset(); runSearch(); });
        },
        onCheck: function (ids) {
            var bar = document.getElementById('manga-batch');
            var count = document.getElementById('manga-batch-count');
            if (!bar) return;
            bar.hidden = !ids.length;
            if (count) count.textContent = '已选 ' + ids.length + ' 条';
        },
        cols: cols
    });

    function runSearch() { table.reload(queryWhere()); }
    function tplId() {
        if (desk === 'types') return 'manga-type-tpl';
        if (desk === 'chapters') return 'manga-chapter-tpl';
        if (desk === 'pics') return 'manga-pic-tpl';
        if (desk === 'comments') return 'manga-comment-tpl';
        return 'manga-work-tpl';
    }
    function titles(mode) {
        var map = {
            works: ['新增作品', '编辑作品'],
            pending: ['新增作品', '编辑作品'],
            types: ['新增分类', '编辑分类'],
            chapters: ['新增章节', '编辑章节'],
            pics: ['新增图片', '编辑图片'],
            comments: ['新增评论', '编辑评论']
        }[desk] || ['新增', '编辑'];
        return mode === 'edit' ? map[1] : map[0];
    }
    function fill(mode, row) {
        row = row || {};
        if (desk === 'types') {
            return {
                id: mode === 'edit' ? (row.id || '') : '',
                name: row.name || '',
                parent_id: row.parent_id == null ? 0 : row.parent_id,
                sort: row.sort == null ? 0 : row.sort,
                status: row.status == null ? '1' : String(row.status)
            };
        }
        if (desk === 'chapters') {
            return {
                id: mode === 'edit' ? (row.id || '') : '',
                manga_id: row.manga_id || (filterMangaId || ''),
                name: row.name || '',
                sort: row.sort == null ? 0 : row.sort,
                pics: row.pics || ''
            };
        }
        if (desk === 'pics') {
            return {
                id: mode === 'edit' ? (row.id || '') : '',
                manga_id: row.manga_id || (filterMangaId || ''),
                chapter_id: row.chapter_id || '',
                url: row.url || '',
                sort: row.sort == null ? 0 : row.sort
            };
        }
        if (desk === 'comments') {
            return {
                id: mode === 'edit' ? (row.id || '') : '',
                manga_id: row.manga_id || (filterMangaId || ''),
                author_name: row.author_name || '',
                content: row.content || '',
                status: row.status == null ? '1' : String(row.status)
            };
        }
        return {
            id: mode === 'edit' ? (row.id || '') : '',
            title: row.title || '',
            type_id: row.type_id == null ? 0 : row.type_id,
            author: row.author || '',
            cover: row.cover || '',
            serialize: row.serialize == null ? '0' : String(row.serialize),
            tags: row.tags || '',
            recommend: row.recommend == null ? '0' : String(row.recommend),
            yid: desk === 'pending' && mode !== 'edit' ? '1' : (row.yid == null ? '0' : String(row.yid)),
            status: row.status == null ? '1' : String(row.status),
            remarks: row.remarks || '',
            content: row.content || '',
            hits: row.hits == null ? 0 : row.hits,
            sort: row.sort == null ? 0 : row.sort
        };
    }
    function bindImageFields(body) {
        U.bindImageField(body, {
            input: '[name=cover]',
            btn: '.js-cover-pick',
            preview: '.js-cover-preview'
        });
        U.bindImageField(body, {
            input: '[name=url]',
            btn: '.js-pic-pick',
            preview: '.js-pic-preview'
        });
        var pics = body.querySelector('textarea[name=pics]');
        var picsBtn = body.querySelector('.js-pics-upload');
        var picsPreview = body.querySelector('.js-pics-preview');
        if (!pics || !picsBtn) return;
        function lastPicUrl() {
            var lines = String(pics.value || '').split(/\r?\n/);
            for (var i = lines.length - 1; i >= 0; i--) {
                var line = String(lines[i] || '').trim();
                if (line) return line;
            }
            return '';
        }
        function syncPicsPreview() {
            var url = lastPicUrl();
            if (!picsPreview) return;
            if (url) {
                picsPreview.src = url;
                picsPreview.style.display = 'block';
            } else {
                picsPreview.removeAttribute('src');
                picsPreview.style.display = 'none';
            }
        }
        syncPicsPreview();
        pics.addEventListener('input', syncPicsPreview);
        picsBtn.addEventListener('click', function () {
            U.pickFile('image/*').then(function (file) {
                if (!file) return;
                U.loading(true);
                return U.upload(file).then(function (res) {
                    U.loading(false);
                    if (res && res.code === 0 && res.data && res.data.url) {
                        var cur = String(pics.value || '').replace(/\s+$/, '');
                        pics.value = cur ? (cur + '\n' + res.data.url) : res.data.url;
                        syncPicsPreview();
                        U.toast('已追加', 'ok');
                    } else {
                        U.toast((res && res.msg) || '上传失败', 'err');
                    }
                }).catch(function () { U.loading(false); });
            });
        });
    }
    function openDialog(mode, row) {
        row = row || {};
        U.dialog({
            title: titles(mode),
            content: document.getElementById(tplId()).innerHTML,
            onOpen: function (body) {
                U.fillForm(body.querySelector('form'), fill(mode, row));
                bindImageFields(body);
                if (desk === 'types' && mode === 'edit' && row.id) {
                    var opt = body.querySelector('select[name=parent_id] option[value="' + row.id + '"]');
                    if (opt) opt.remove();
                }
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (desk === 'types' && !data.name) { U.toast('请填写名称', 'err'); return false; }
                if ((desk === 'works' || desk === 'pending') && !data.title) { U.toast('请填写名称', 'err'); return false; }
                if (desk === 'chapters' && !data.name) { U.toast('请填写章节名', 'err'); return false; }
                if (desk === 'pics' && !data.url) { U.toast('请填写图片地址', 'err'); return false; }
                if (desk === 'comments' && !data.content) { U.toast('请填写评论', 'err'); return false; }
                if (mode !== 'edit') delete data.id; else data.id = row.id;
                return U.post('/admin/video/' + module + '/save', data).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return false; }
                    U.toast(mode === 'edit' ? '已保存' : '已创建', 'ok');
                    table.refresh();
                });
            }
        });
    }

    function batch(action, value, confirmText) {
        var ids = table.selectedIds();
        if (!ids.length) { U.toast('请先勾选记录', 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/' + module + '/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '操作失败', 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || '操作成功', 'ok');
        });
    }
    U.on('#manga-search-btn', 'click', runSearch);
    U.on('#manga-reset-btn', 'click', function () { setTimeout(runSearch, 0); });
    U.on('#manga-add-btn', 'click', function () { openDialog('add'); });
    U.on('#manga-batch-on', 'click', function () { batch('status', 1); });
    U.on('#manga-batch-off', 'click', function () { batch('status', 0); });
    U.on('#manga-batch-pass', 'click', function () { batch('yid', 0); });
    U.on('#manga-batch-rec', 'click', function () { batch('recommend', 1); });
    U.on('#manga-batch-unrec', 'click', function () { batch('recommend', 0); });
    U.on('#manga-batch-del', 'click', function () { batch('delete', '', '确认删除选中记录？'); });
    U.on('#manga-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#manga-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-edit')) openDialog('edit', row);
        if (a.classList.contains('js-chapters')) {
            location.href = '/admin/video/mangas?desk=chapters&manga_id=' + encodeURIComponent(row.id);
        }
        if (a.classList.contains('js-del')) {
            if (!U.confirm('确认删除？')) return;
            U.post('/admin/video/' + module + '/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh();
                U.toast('已删除', 'ok');
            });
        }
    });
})();
</script>
@endpush
