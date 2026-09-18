@extends('admin.layouts.inner')
@section('title', $title)

@section('plain')
<div class="card card-panel topic-index">
    <div class="card-header">
        <span>专题</span>
        <button type="button" class="btn btn-sm" id="topic-add-btn">新增专题</button>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="topic-search" onsubmit="return false;">
            <input type="text" name="name" placeholder="搜专题名" autocomplete="off">
            <select name="status">
                <option value="">状态</option>
                <option value="1">上架</option>
                <option value="0">下架</option>
            </select>
            <button type="button" class="btn btn-sm" id="topic-search-btn">查询</button>
            <button type="reset" class="btn btn-muted btn-sm" id="topic-reset-btn">重置</button>
        </form>
        <div class="queue-chips" id="topic-queues">
            <button type="button" class="chip" data-queue="">全部</button>
            <button type="button" class="chip" data-queue="status" data-value="1">上架</button>
            <button type="button" class="chip" data-queue="status" data-value="0">下架</button>
        </div>
        <p class="muted recycle-lead">专题是片单，如贺岁档、冷门佳片。先建专题，再点「绑片」挂影片；资讯站还可以「绑文」。</p>
        <div class="batch-bar" id="topic-batch" hidden>
            <strong id="topic-batch-count">已选 0 个</strong>
            <button type="button" class="btn btn-sm" id="topic-batch-on">上架</button>
            <button type="button" class="btn btn-muted btn-sm" id="topic-batch-off">下架</button>
            <button type="button" class="btn btn-danger btn-sm" id="topic-batch-del">删除</button>
            <button type="button" class="btn btn-muted btn-sm" id="topic-batch-clear">取消选择</button>
        </div>
        <div id="topic-table"></div>
    </div>
</div>
<template id="topic-dialog-tpl">
    <form>
        <input type="hidden" name="id">
        <label>名称</label>
        <input type="text" name="name" placeholder="如 贺岁档">
        <label>别名</label>
        <input type="text" name="slug" placeholder="如 new-year，前台网址用">
        <label>封面</label>
        <div class="field-inline">
            <input type="text" name="cover" class="topic-cover-input" placeholder="图片 URL">
            <button type="button" class="btn btn-muted topic-cover-upload-btn">上传</button>
        </div>
        <img class="img-preview topic-cover-preview" alt="">
        <label>简介</label>
        <input type="text" name="blurb" placeholder="一句话推荐">
        <label>介绍</label>
        <textarea name="content"></textarea>
        <details class="topic-more">
        <summary>展示与检索</summary>
        <label>副标</label>
        <input type="text" name="sub" placeholder="副标题">
        <div class="field-inline">
            <span>
                <label>首字母</label>
                <input type="text" name="letter" maxlength="8" placeholder="空则自动">
            </span>
            <span>
                <label>高亮色</label>
                <input type="text" name="color" maxlength="16" placeholder="#ff6600">
            </span>
        </div>
        <div class="field-inline">
            <span>
                <label>推荐</label>
                <input type="number" name="level" value="0" min="0">
            </span>
            <span>
                <label>备注</label>
                <input type="text" name="remarks" placeholder="内部备注">
            </span>
        </div>
        <label>缩略图</label>
        <div class="field-inline">
            <input type="text" name="cover_thumb" class="topic-thumb-input" placeholder="缩略图地址">
            <button type="button" class="btn btn-muted topic-thumb-upload-btn">上传</button>
        </div>
        <img class="img-preview topic-thumb-preview" alt="">
        <label>幻灯</label>
        <div class="field-inline">
            <input type="text" name="cover_slide" class="topic-slide-input" placeholder="幻灯图地址">
            <button type="button" class="btn btn-muted topic-slide-upload-btn">上传</button>
        </div>
        <img class="img-preview topic-slide-preview" alt="">
        <label>模板</label>
        <input type="text" name="tpl" placeholder="空则用默认详情">
        <label>扩展分类</label>
        <input type="text" name="type" placeholder="如 贺岁">
        <label>标签</label>
        <input type="text" name="tag" placeholder="逗号分隔">
        <label>SEO 标题</label>
        <input type="text" name="seo_title">
        <label>SEO 关键字</label>
        <input type="text" name="seo_key">
        <label>SEO 描述</label>
        <input type="text" name="seo_des">
        </details>
        <label>排序</label>
        <input type="number" name="sort" value="0">
        <label>状态</label>
        <select name="status">
            <option value="1">上架</option>
            <option value="0">下架</option>
        </select>
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var form = document.getElementById('topic-search');
    var batchBar = document.getElementById('topic-batch');
    var batchCount = document.getElementById('topic-batch-count');

    function cleanWhere(data) {
        var out = {};
        Object.keys(data).forEach(function (k) { if (data[k] !== '') out[k] = data[k]; });
        return out;
    }
    function queryWhere() {
        return Object.assign({limit: 20}, cleanWhere(U.formData(form)));
    }
    function isFiltered(where) {
        return Object.keys(where || {}).some(function (k) { return k !== 'limit' && where[k] !== ''; });
    }
    function markChips() {
        var status = form.status.value;
        U.qa('#topic-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = (key === '' && status === '') || (key === 'status' && status === val);
            chip.classList.toggle('active', on);
        });
    }
    function applyQueue(key, value) {
        form.status.value = key === 'status' ? (value || '') : '';
        runSearch();
    }
    function runSearch() {
        table.reload(queryWhere());
        markChips();
    }
    function nameHtml(d) {
        var cover = String(d.cover || '').trim();
        var thumb = cover
            ? '<img class="vod-thumb" src="' + U.escape(cover) + '" alt="">'
            : '<span class="vod-thumb is-empty">无图</span>';
        var meta = '#' + U.escape(d.id);
        if (d.slug) meta += ' · /' + U.escape(d.slug);
        var n = parseInt(d.video_count, 10) || 0;
        meta += n > 0 ? ' · ' + n + ' 部' : ' · 还没绑片';
        var an = parseInt(d.art_count, 10) || 0;
        if (an > 0) meta += ' · ' + an + ' 篇';
        if (d.blurb) meta += ' · ' + U.escape(d.blurb);
        return '<div class="vod-cell">' + thumb + '<div><a class="vod-title js-edit" href="#">' + U.escape(d.name || '') + '</a>'
            + '<div class="muted">' + meta + '</div></div></div>';
    }

    var table = U.table({
        el: '#topic-table',
        url: '/admin/video/topics/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>没有符合条件的专题</p><p><button type="button" class="btn btn-muted btn-sm" id="topic-empty-reset">清除筛选</button></p></div>';
            }
            return '<div class="list-empty"><p>还没有专题</p><p class="muted">专题是片单，不是分类。先建一个，再绑进若干部影片给前台做推荐墙。</p><p><button type="button" class="btn btn-primary btn-sm" id="topic-empty-add">新增专题</button></p></div>';
        },
        onDraw: function () {
            var add = document.getElementById('topic-empty-add');
            var reset = document.getElementById('topic-empty-reset');
            if (add) add.addEventListener('click', function () { openDialog('add'); });
            if (reset) reset.addEventListener('click', function () { form.reset(); runSearch(); });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = '已选 ' + ids.length + ' 个';
        },
        cols: [
            {check: true, width: 36},
            {title: '专题', html: nameHtml},
            {key: 'sort', title: '排序', width: 64},
            {title: '状态', width: 72, html: function (d) {
                return String(d.status) === '1' ? U.status(true, '上架') : U.status(false, '下架');
            }},
            {title: '操作', cls: 'actions', html: function (d) {
                var href = d.url ? String(d.url) : ('/topic/' + encodeURIComponent(d.slug || d.id));
                return '<a href="#" class="btn-link js-bind">绑片</a>'
                    + '<a href="#" class="btn-link js-bind-art">绑文</a>'
                    + '<a href="' + U.escape(href) + '" target="_blank" rel="noopener" class="btn-link">前台</a>'
                    + '<a href="#" class="btn-link js-edit">编辑</a>'
                    + '<a href="#" class="btn-link js-del">删除</a>';
            }}
        ]
    });
    markChips();

    function bindCover(formEl) {
        U.bindImageField(formEl, {
            input: 'input[name=cover]',
            btn: '.topic-cover-upload-btn',
            preview: '.topic-cover-preview'
        });
        U.bindImageField(formEl, {
            input: 'input[name=cover_thumb]',
            btn: '.topic-thumb-upload-btn',
            preview: '.topic-thumb-preview'
        });
        U.bindImageField(formEl, {
            input: 'input[name=cover_slide]',
            btn: '.topic-slide-upload-btn',
            preview: '.topic-slide-preview'
        });
    }

    function openDialog(mode, row) {
        row = row || {};
        U.dialog({
            title: mode === 'edit' ? '编辑专题' : '新增专题',
            content: document.getElementById('topic-dialog-tpl').innerHTML,
            onOpen: function (body) {
                var formEl = body.querySelector('form');
                U.fillForm(formEl, {
                    id: mode === 'edit' ? (row.id || '') : '',
                    name: row.name || '',
                    slug: row.slug || '',
                    cover: row.cover || '',
                    blurb: row.blurb || '',
                    content: row.content || '',
                    sub: row.sub || '',
                    letter: row.letter || '',
                    color: row.color || '',
                    level: row.level == null ? 0 : row.level,
                    remarks: row.remarks || '',
                    cover_thumb: row.cover_thumb || '',
                    cover_slide: row.cover_slide || '',
                    tpl: row.tpl || '',
                    type: row.type || '',
                    tag: row.tag || '',
                    seo_title: row.seo_title || '',
                    seo_key: row.seo_key || '',
                    seo_des: row.seo_des || '',
                    sort: row.sort == null ? 0 : row.sort,
                    status: row.status == null ? '1' : String(row.status)
                });
                bindCover(formEl);
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (!data.name) { U.toast('请填写名称', 'err'); return false; }
                if (mode !== 'edit') delete data.id; else data.id = row.id;
                return U.post('/admin/video/topics/save', data).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return false; }
                    U.toast(mode === 'edit' ? '已保存' : '已创建，接着点「绑片」', 'ok');
                    table.refresh();
                });
            }
        });
    }

    function openBind(row) {
        U.loading(true);
        U.get('/admin/video/topics/' + row.id + '/videos').then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '加载失败', 'err'); return; }
            var picked = (res.data && res.data.videos) ? res.data.videos.slice() : [];
            function renderList() {
                if (!picked.length) return '<p class="muted">还没绑影片。上面搜索标题后点加入。</p>';
                var html = '<ul class="topic-bind-list">';
                picked.forEach(function (v, i) {
                    html += '<li data-id="' + U.escape(v.id) + '"><span>' + U.escape(v.title || ('#' + v.id)) + '</span>'
                        + '<button type="button" class="btn-link js-remove" data-i="' + i + '">移除</button></li>';
                });
                html += '</ul>';
                return html;
            }
            var html = '<p class="hint">搜索片库加入本专题。顺序即列表顺序，越靠前越靠前展示。</p>'
                + '<div class="field-inline"><input type="text" id="topic-bind-q" placeholder="搜影片标题"><button type="button" class="btn btn-sm" id="topic-bind-search">搜索</button></div>'
                + '<div id="topic-bind-hits" class="topic-bind-hits"></div>'
                + '<div id="topic-bind-picked">' + renderList() + '</div>';
            U.dialog({
                title: '绑片 · ' + (row.name || ''),
                wide: true,
                okText: '保存片单',
                content: html,
                onOpen: function (body) {
                    var hits = body.querySelector('#topic-bind-hits');
                    var pickedBox = body.querySelector('#topic-bind-picked');
                    function redraw() { pickedBox.innerHTML = renderList(); }
                    function addVideo(v) {
                        var id = parseInt(v.id, 10);
                        if (picked.some(function (x) { return parseInt(x.id, 10) === id; })) {
                            U.toast('已经在片单里', 'err');
                            return;
                        }
                        picked.push({id: id, title: v.title || ('#' + id), cover: v.cover || ''});
                        redraw();
                    }
                    body.querySelector('#topic-bind-search').addEventListener('click', function () {
                        var q = (body.querySelector('#topic-bind-q').value || '').trim();
                        if (!q) { U.toast('输入片名再搜', 'err'); return; }
                        U.get('/admin/video/list', {title: q, limit: 8}).then(function (r) {
                            var list = (r && r.data && r.data.data) || [];
                            if (!list.length) { hits.innerHTML = '<p class="muted">片库没有匹配</p>'; return; }
                            var out = '';
                            list.forEach(function (v) {
                                out += '<button type="button" class="chip js-add" data-id="' + U.escape(v.id) + '" data-title="' + U.escape(v.title || '') + '">' + U.escape(v.title || ('#' + v.id)) + '</button>';
                            });
                            hits.innerHTML = out;
                        });
                    });
                    body.querySelector('#topic-bind-q').addEventListener('keydown', function (ev) {
                        if (ev.key === 'Enter') { ev.preventDefault(); body.querySelector('#topic-bind-search').click(); }
                    });
                    hits.addEventListener('click', function (e) {
                        var btn = e.target.closest('.js-add');
                        if (!btn) return;
                        addVideo({id: btn.getAttribute('data-id'), title: btn.getAttribute('data-title')});
                    });
                    pickedBox.addEventListener('click', function (e) {
                        var btn = e.target.closest('.js-remove');
                        if (!btn) return;
                        picked.splice(parseInt(btn.getAttribute('data-i'), 10), 1);
                        redraw();
                    });
                },
                onSave: function () {
                    var ids = picked.map(function (v) { return v.id; }).join(',');
                    return U.post('/admin/video/topics/' + row.id + '/videos', {video_ids: ids}).then(function (r) {
                        if (!r || r.code !== 0) { U.toast((r && r.msg) || '失败', 'err'); return false; }
                        U.toast((r && r.msg) || '片单已保存', 'ok');
                        table.refresh();
                    });
                }
            });
        });
    }

    function openBindArt(row) {
        U.loading(true);
        U.get('/admin/video/topics/' + row.id + '/arts').then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '加载失败', 'err'); return; }
            var picked = (res.data && res.data.arts) ? res.data.arts.slice() : [];
            function renderList() {
                if (!picked.length) return '<p class="muted">还没绑文章。上面搜索标题后点加入。</p>';
                var html = '<ul class="topic-bind-list">';
                picked.forEach(function (v, i) {
                    html += '<li data-id="' + U.escape(v.id) + '"><span>' + U.escape(v.title || ('#' + v.id)) + '</span>'
                        + '<button type="button" class="btn-link js-remove" data-i="' + i + '">移除</button></li>';
                });
                html += '</ul>';
                return html;
            }
            var html = '<p class="hint">搜索文章加入本专题。顺序即列表顺序，越靠前越靠前展示。</p>'
                + '<div class="field-inline"><input type="text" id="topic-bind-art-q" placeholder="搜文章标题"><button type="button" class="btn btn-sm" id="topic-bind-art-search">搜索</button></div>'
                + '<div id="topic-bind-art-hits" class="topic-bind-hits"></div>'
                + '<div id="topic-bind-art-picked">' + renderList() + '</div>';
            U.dialog({
                title: '绑文 · ' + (row.name || ''),
                wide: true,
                okText: '保存文单',
                content: html,
                onOpen: function (body) {
                    var hits = body.querySelector('#topic-bind-art-hits');
                    var pickedBox = body.querySelector('#topic-bind-art-picked');
                    function redraw() { pickedBox.innerHTML = renderList(); }
                    function addArt(v) {
                        var id = parseInt(v.id, 10);
                        if (picked.some(function (x) { return parseInt(x.id, 10) === id; })) {
                            U.toast('已经在专题里', 'err');
                            return;
                        }
                        picked.push({id: id, title: v.title || ('#' + id), cover: v.cover || ''});
                        redraw();
                    }
                    body.querySelector('#topic-bind-art-search').addEventListener('click', function () {
                        var q = (body.querySelector('#topic-bind-art-q').value || '').trim();
                        if (!q) { U.toast('输入标题再搜', 'err'); return; }
                        U.get('/admin/video/arts/list', {title: q, q: q, limit: 8}).then(function (r) {
                            var list = (r && r.data && r.data.data) || [];
                            if (!list.length) { hits.innerHTML = '<p class="muted">没有匹配的文章</p>'; return; }
                            var out = '';
                            list.forEach(function (v) {
                                out += '<button type="button" class="chip js-add" data-id="' + U.escape(v.id) + '" data-title="' + U.escape(v.title || '') + '">' + U.escape(v.title || ('#' + v.id)) + '</button>';
                            });
                            hits.innerHTML = out;
                        });
                    });
                    body.querySelector('#topic-bind-art-q').addEventListener('keydown', function (ev) {
                        if (ev.key === 'Enter') { ev.preventDefault(); body.querySelector('#topic-bind-art-search').click(); }
                    });
                    hits.addEventListener('click', function (e) {
                        var btn = e.target.closest('.js-add');
                        if (!btn) return;
                        addArt({id: btn.getAttribute('data-id'), title: btn.getAttribute('data-title')});
                    });
                    pickedBox.addEventListener('click', function (e) {
                        var btn = e.target.closest('.js-remove');
                        if (!btn) return;
                        picked.splice(parseInt(btn.getAttribute('data-i'), 10), 1);
                        redraw();
                    });
                },
                onSave: function () {
                    var ids = picked.map(function (v) { return v.id; }).join(',');
                    return U.post('/admin/video/topics/' + row.id + '/arts', {art_ids: ids}).then(function (r) {
                        if (!r || r.code !== 0) { U.toast((r && r.msg) || '失败', 'err'); return false; }
                        U.toast((r && r.msg) || '文章已保存', 'ok');
                        table.refresh();
                    });
                }
            });
        });
    }

    function selectedIds() { return table.selectedIds(); }
    function batch(action, value, confirmText) {
        var ids = selectedIds();
        if (!ids.length) { U.toast('请先勾选专题', 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/topics/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '操作失败', 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || '操作成功', 'ok');
        });
    }

    U.on('#topic-search-btn', 'click', runSearch);
    U.on('#topic-reset-btn', 'click', function () { setTimeout(runSearch, 0); });
    U.on('#topic-add-btn', 'click', function () { openDialog('add'); });
    document.getElementById('topic-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#topic-batch-on', 'click', function () { batch('status', 1); });
    U.on('#topic-batch-off', 'click', function () { batch('status', 0); });
    U.on('#topic-batch-del', 'click', function () { batch('delete', '', '确认删除选中专题？片单关系会一起去掉。'); });
    U.on('#topic-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#topic-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        if (a.target === '_blank') return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-edit')) openDialog('edit', row);
        if (a.classList.contains('js-bind')) openBind(row);
        if (a.classList.contains('js-bind-art')) openBindArt(row);
        if (a.classList.contains('js-del')) {
            if (!U.confirm('删除专题「' + (row.name || '') + '」？')) return;
            U.post('/admin/video/topics/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh();
                U.toast('已删除', 'ok');
            });
        }
    });
})();
</script>
@endpush
