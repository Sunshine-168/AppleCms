@extends('admin.layouts.inner')
@section('title', $title)

@php
    $ready = (bool) ($ready ?? false);
@endphp

@section('plain')
<div class="card card-panel tag-index">
    <div class="card-header">
        <span>标签 <em id="manga-tag-count"></em></span>
        <a class="btn btn-muted btn-sm" href="/admin/video/manga-tags/create">完整表单</a>
    </div>
    <div class="card-body">
        <div class="tag-compose">
            <form class="tag-compose-form" id="manga-tag-compose" onsubmit="return false;">
                <label class="tag-compose-label" for="manga-tag-quick">新增标签</label>
                <div class="tag-compose-row">
                    <input id="manga-tag-quick" type="text" name="name" value="" placeholder="输入名称，如 热血" aria-label="新增标签" @if($ready) autofocus @endif>
                    <button class="btn" type="submit" id="manga-tag-add">添加</button>
                </div>
                <p class="muted field-hint">回车可连续添加。需要改网址时，<a href="/admin/video/manga-tags/create">打开完整表单</a>。只给漫画用，不会进文章或影片标签库。</p>
            </form>
        </div>
        <form class="filter-bar" id="manga-tag-search" onsubmit="return false;">
            <input type="hidden" name="unused" value="">
            <input type="search" name="q" placeholder="搜索标签名或网址标识" autocomplete="off" aria-label="搜索标签">
            <button type="button" class="btn btn-sm" id="manga-tag-search-btn">搜索</button>
            <button type="reset" class="btn btn-muted btn-sm" id="manga-tag-reset-btn">重置</button>
        </form>
        <div class="queue-chips" id="manga-tag-queues">
            <button type="button" class="chip" data-unused="">全部</button>
            <button type="button" class="chip" data-unused="1">未使用</button>
        </div>
        <p class="muted recycle-lead">热血、日常这类给漫画用的聚合词，不是分类，也不是文章/影片标签。删标签只拿掉标记，作品还在。作品上也可直接填逗号标签，会自动进这个库。</p>
        <div class="batch-bar" id="manga-tag-batch" hidden>
            <strong id="manga-tag-batch-count">已选 0 个</strong>
            <button type="button" class="btn btn-danger btn-sm" id="manga-tag-batch-del">删除</button>
            <button type="button" class="btn btn-muted btn-sm" id="manga-tag-batch-clear">取消选择</button>
        </div>
        <div id="manga-tag-table"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var form = document.getElementById('manga-tag-search');
    var compose = document.getElementById('manga-tag-compose');
    var batchBar = document.getElementById('manga-tag-batch');
    var batchCount = document.getElementById('manga-tag-batch-count');
    var countEl = document.getElementById('manga-tag-count');
    var ready = @json($ready);
    var base = '/admin/video/manga-tags';

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
        var unused = form.unused ? form.unused.value : '';
        U.qa('#manga-tag-queues .chip').forEach(function (chip) {
            chip.classList.toggle('active', (chip.getAttribute('data-unused') || '') === unused);
        });
    }
    function runSearch() {
        table.reload(queryWhere());
        markChips();
    }

    var table = U.table({
        el: '#manga-tag-table',
        url: base + '/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (!ready) {
                return '<div class="list-empty"><p>请先执行数据库迁移</p></div>';
            }
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>没有符合条件的标签。</p><p><button type="button" class="btn btn-muted btn-sm" id="manga-tag-empty-reset">清除筛选</button></p></div>';
            }
            return '<div class="list-empty"><p>还没有标签。</p><p class="muted">在上方输入名称即可添加，用来给漫画归类。</p></div>';
        },
        onDraw: function (_wrap, list) {
            countEl.textContent = list.length ? '· ' + list.length : '';
            var reset = document.getElementById('manga-tag-empty-reset');
            if (reset) reset.addEventListener('click', function () { form.reset(); if (form.unused) form.unused.value = ''; runSearch(); });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = '已选 ' + ids.length + ' 个';
        },
        cols: [
            {check: true, width: 36},
            {title: '标签', html: function (d) {
                var slug = String(d.slug || '').trim();
                return '<a class="entry-row-title" href="' + base + '/' + encodeURIComponent(d.id) + '/edit">' + U.escape(d.name || '无标题') + '</a>'
                    + '<div class="entry-row-meta">/manga?tag=' + U.escape(slug || d.id) + ' · #' + U.escape(d.id) + '</div>';
            }},
            {title: '作品', width: 120, html: function (d) {
                var n = parseInt(d.manga_count, 10) || 0;
                if (n > 0) return '<a href="/admin/video/mangas?tag_id=' + encodeURIComponent(d.id) + '">' + n + ' 部</a>';
                return '<span class="muted">未使用</span>';
            }},
            {title: '操作', cls: 'actions', html: function (d) {
                var href = d.url ? String(d.url) : ('/manga?tag=' + encodeURIComponent(d.slug || d.id));
                return '<a href="' + U.escape(href) + '" target="_blank" rel="noopener" class="btn-link">前台</a>'
                    + '<a href="' + base + '/' + encodeURIComponent(d.id) + '/edit" class="btn-link">编辑</a>'
                    + '<a href="#" class="btn-link js-del">删除</a>';
            }}
        ]
    });
    markChips();

    U.on('#manga-tag-search-btn', 'click', runSearch);
    U.on('#manga-tag-reset-btn', 'click', function () { setTimeout(function () { if (form.unused) form.unused.value = ''; runSearch(); }, 0); });
    document.getElementById('manga-tag-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-unused]');
        if (!chip || !form.unused) return;
        form.unused.value = chip.getAttribute('data-unused') || '';
        runSearch();
    });
    compose.addEventListener('submit', function (e) {
        e.preventDefault();
        var name = String((compose.name && compose.name.value) || '').trim();
        if (!name) { U.toast('请填写标签名称', 'err'); compose.name.focus(); return; }
        U.loading(true);
        U.post(base + '/save', {name: name, status: 1}).then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '添加失败', 'err'); return; }
            compose.name.value = '';
            compose.name.focus();
            table.refresh();
            U.toast('已添加', 'ok');
        }).catch(function () { U.loading(false); U.toast('添加失败', 'err'); });
    });
    U.on('#manga-tag-batch-del', 'click', function () {
        var ids = table.selectedIds();
        if (!ids.length) { U.toast('请先勾选标签', 'err'); return; }
        if (!U.confirm('删除已选标签？作品还在，只去掉标记。')) return;
        U.post(base + '/batch', {ids: ids.join(','), action: 'delete'}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || '已删除', 'ok');
        });
    });
    U.on('#manga-tag-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#manga-tag-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a || a.target === '_blank') return;
        if (a.getAttribute('href') && a.getAttribute('href').indexOf(base + '/') === 0 && !a.classList.contains('js-del')) return;
        if (!a.classList.contains('js-del')) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        var n = parseInt(row.manga_count, 10) || 0;
        var msg = n > 0
            ? ('「' + (row.name || '') + '」用在 ' + n + ' 部上，删除后只去掉标签，作品还在。确定？')
            : ('确定删除「' + (row.name || '') + '」？');
        if (!U.confirm(msg)) return;
        U.post(base + '/delete', {id: row.id}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || '已删除', 'ok');
        });
    });
})();
</script>
@endpush
