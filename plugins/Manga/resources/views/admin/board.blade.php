@extends('admin.layouts.inner')
@section('title', $title ?? admin_t('nav.manga'))

@php
    $desk = in_array((string) ($desk ?? ''), ['pending', 'types', 'chapters', 'pics', 'works'], true)
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
        <p class="muted recycle-lead">独立漫画库，不是影片分类。分类、章节、图片都在这页切换。关掉插件后前台 /manga 一起消失。</p>
        <div class="queue-chips" id="manga-desks">
            <a class="chip{{ $desk === 'works' ? ' active' : '' }}" href="/admin/video/mangas">作品</a>
            <a class="chip{{ $desk === 'pending' ? ' active' : '' }}" href="/admin/video/mangas?desk=pending">待审</a>
            <a class="chip{{ $desk === 'types' ? ' active' : '' }}" href="/admin/video/mangas?desk=types">分类</a>
            <a class="chip{{ $desk === 'chapters' ? ' active' : '' }}" href="/admin/video/mangas?desk=chapters">章节</a>
            <a class="chip{{ $desk === 'pics' ? ' active' : '' }}" href="/admin/video/mangas?desk=pics">图片</a>
        </div>
        <form class="filter-bar" id="manga-search" onsubmit="return false;">
            <input type="hidden" name="yid" value="{{ $desk === 'pending' ? '1' : ($desk === 'works' ? '0' : '') }}">
            <input type="hidden" name="manga_id" value="{{ $filterMangaId > 0 ? $filterMangaId : '' }}">
            <input type="search" name="q" placeholder="{{ $desk === 'types' ? '搜分类' : ($desk === 'chapters' ? '搜章节' : ($desk === 'pics' ? '搜图片地址' : '搜名称、作者、标签')) }}" autocomplete="off">
            @if(in_array($desk, ['works', 'pending'], true) && $types !== [])
                <select name="type_id" aria-label="分类">
                    <option value="">全部分类</option>
                    @foreach($types as $type)
                        <option value="{{ $type['id'] }}">{{ $type['name'] }}</option>
                    @endforeach
                </select>
            @endif
            <button type="button" class="btn btn-sm" id="manga-search-btn">查询</button>
            <button type="reset" class="btn btn-muted btn-sm" id="manga-reset-btn">重置</button>
        </form>
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
                <option value="{{ $type['id'] }}">{{ $type['name'] }}</option>
            @endforeach
        </select>
        <label>作者</label>
        <input type="text" name="author">
        <label>封面</label>
        <input type="text" name="cover" placeholder="图片地址">
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
        <label>上级编号</label>
        <input type="number" name="parent_id" value="0">
        <p class="muted field-hint">0 表示顶级。填已有分类的编号。</p>
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
        <p class="muted field-hint">图片地址每行一条。保存后会写入图片表。javascript: 不会收录。</p>
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
        <input type="text" name="url" required placeholder="http(s) 或 / 开头">
        <label>排序</label>
        <input type="number" name="sort" value="0">
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
        pics: 'manga_pics'
    };
    var module = modules[desk] || 'mangas';
    var addLabels = {
        works: '新增作品',
        pending: '新增作品',
        types: '新增分类',
        chapters: '新增章节',
        pics: '新增图片'
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
            pics: ['还没有图片', '新增图片']
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
    } else if (desk === 'types') {
        cols = [
            {title: '名称', html: function (d) { return '<a class="js-edit" href="#">' + U.escape(d.name || '未填写') + '</a>'; }},
            {title: '上级', width: 80, html: function (d) { return U.escape(String(d.parent_id || 0)); }},
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
            {title: '章节', html: function (d) { return '<a class="js-edit" href="#">' + U.escape(d.name || '未填写') + '</a>'; }},
            {title: '作品', html: function (d) { return U.escape(d.manga_title || ('#' + (d.manga_id || ''))); }},
            {title: '图片数', width: 72, html: function (d) { return U.escape(String(d.pic_count == null ? 0 : d.pic_count)); }},
            {title: '排序', width: 72, html: function (d) { return U.escape(String(d.sort || 0)); }},
            {title: '操作', cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-edit">编辑</a><a href="#" class="btn-link js-del">删除</a>';
            }}
        ];
    } else {
        cols = [
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
        cols: cols
    });

    function runSearch() { table.reload(queryWhere()); }
    function tplId() {
        if (desk === 'types') return 'manga-type-tpl';
        if (desk === 'chapters') return 'manga-chapter-tpl';
        if (desk === 'pics') return 'manga-pic-tpl';
        return 'manga-work-tpl';
    }
    function titles(mode) {
        var map = {
            works: ['新增作品', '编辑作品'],
            pending: ['新增作品', '编辑作品'],
            types: ['新增分类', '编辑分类'],
            chapters: ['新增章节', '编辑章节'],
            pics: ['新增图片', '编辑图片']
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
    function openDialog(mode, row) {
        row = row || {};
        U.dialog({
            title: titles(mode),
            content: document.getElementById(tplId()).innerHTML,
            onOpen: function (body) {
                U.fillForm(body.querySelector('form'), fill(mode, row));
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (desk === 'types' && !data.name) { U.toast('请填写名称', 'err'); return false; }
                if ((desk === 'works' || desk === 'pending') && !data.title) { U.toast('请填写名称', 'err'); return false; }
                if (desk === 'chapters' && !data.name) { U.toast('请填写章节名', 'err'); return false; }
                if (desk === 'pics' && !data.url) { U.toast('请填写图片地址', 'err'); return false; }
                if (mode !== 'edit') delete data.id; else data.id = row.id;
                return U.post('/admin/video/' + module + '/save', data).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return false; }
                    U.toast(mode === 'edit' ? '已保存' : '已创建', 'ok');
                    table.refresh();
                });
            }
        });
    }

    U.on('#manga-search-btn', 'click', runSearch);
    U.on('#manga-reset-btn', 'click', function () { setTimeout(runSearch, 0); });
    U.on('#manga-add-btn', 'click', function () { openDialog('add'); });
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
