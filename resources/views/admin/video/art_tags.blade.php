@extends('admin.layouts.inner')
@section('title', $title)

@php
    $ready = (bool) ($ready ?? false);
@endphp

@section('plain')
<div class="card card-panel tag-index">
    <div class="card-header">
        <span>标签 <em id="art-tag-count"></em></span>
        <a class="btn btn-muted btn-sm" href="/admin/video/art-tags/create">完整表单</a>
    </div>
    <div class="card-body">
        <div class="tag-compose">
            <form class="tag-compose-form" id="art-tag-compose" onsubmit="return false;">
                <label class="tag-compose-label" for="art-tag-quick">新增标签</label>
                <div class="tag-compose-row">
                    <input id="art-tag-quick" type="text" name="name" value="" placeholder="输入名称，如 影讯" aria-label="新增标签" @if($ready) autofocus @endif>
                    <button class="btn" type="submit" id="art-tag-add">添加</button>
                </div>
                <p class="muted field-hint">回车可连续添加。需要改网址时，<a href="/admin/video/art-tags/create">打开完整表单</a>。文章保存时勾选或新填的词也会建档。</p>
            </form>
        </div>
        <form class="filter-bar" id="art-tag-search" onsubmit="return false;">
            <input type="hidden" name="unused" value="">
            <input type="search" name="q" placeholder="搜索标签名或网址标识" autocomplete="off" aria-label="搜索标签">
            <button type="button" class="btn btn-sm" id="art-tag-search-btn">搜索</button>
            <button type="reset" class="btn btn-muted btn-sm" id="art-tag-reset-btn">重置</button>
        </form>
        <div class="queue-chips" id="art-tag-queues">
            <button type="button" class="chip" data-unused="">全部</button>
            <button type="button" class="chip" data-unused="1">未使用</button>
        </div>
        <p class="muted recycle-lead">贺岁、院线这类聚合词，不是栏目。删标签只拿掉标记，稿件还在。</p>
        <div class="batch-bar" id="art-tag-batch" hidden>
            <strong id="art-tag-batch-count">已选 0 个</strong>
            <button type="button" class="btn btn-danger btn-sm" id="art-tag-batch-del">删除</button>
            <button type="button" class="btn btn-muted btn-sm" id="art-tag-batch-clear">取消选择</button>
        </div>
        <div id="art-tag-table"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var form = document.getElementById('art-tag-search');
    var compose = document.getElementById('art-tag-compose');
    var batchBar = document.getElementById('art-tag-batch');
    var batchCount = document.getElementById('art-tag-batch-count');
    var countEl = document.getElementById('art-tag-count');
    var ready = @json($ready);

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
        U.qa('#art-tag-queues .chip').forEach(function (chip) {
            chip.classList.toggle('active', (chip.getAttribute('data-unused') || '') === unused);
        });
    }
    function runSearch() {
        table.reload(queryWhere());
        markChips();
    }

    var table = U.table({
        el: '#art-tag-table',
        url: '/admin/video/art-tags/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (!ready) {
                return '<div class="list-empty"><p>请先执行数据库迁移</p></div>';
            }
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>没有符合条件的标签。</p><p><button type="button" class="btn btn-muted btn-sm" id="art-tag-empty-reset">清除筛选</button></p></div>';
            }
            return '<div class="list-empty"><p>还没有标签。</p><p class="muted">在上方输入名称即可添加，用来给文章归类。</p></div>';
        },
        onDraw: function (_wrap, list) {
            countEl.textContent = list.length ? '· ' + list.length : '';
            var reset = document.getElementById('art-tag-empty-reset');
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
                return '<a class="entry-row-title" href="/admin/video/art-tags/' + encodeURIComponent(d.id) + '/edit">' + U.escape(d.name || '无标题') + '</a>'
                    + '<div class="entry-row-meta">/art/tag/' + U.escape(slug || d.id) + ' · #' + U.escape(d.id) + '</div>';
            }},
            {title: '文章', width: 120, html: function (d) {
                var n = parseInt(d.art_count, 10) || 0;
                if (n > 0) return '<a href="/admin/video/arts?tag_id=' + encodeURIComponent(d.id) + '">' + n + ' 篇</a>';
                return '<span class="muted">未使用</span>';
            }},
            {title: '操作', cls: 'actions', html: function (d) {
                var href = d.url ? String(d.url) : ('/art/tag/' + encodeURIComponent(d.slug || d.id));
                return '<a href="' + U.escape(href) + '" target="_blank" rel="noopener" class="btn-link">前台</a>'
                    + '<a href="/admin/video/art-tags/' + encodeURIComponent(d.id) + '/edit" class="btn-link">编辑</a>'
                    + '<a href="#" class="btn-link js-del">删除</a>';
            }}
        ]
    });
    markChips();

    U.on('#art-tag-search-btn', 'click', runSearch);
    U.on('#art-tag-reset-btn', 'click', function () { setTimeout(function () { if (form.unused) form.unused.value = ''; runSearch(); }, 0); });
    document.getElementById('art-tag-queues').addEventListener('click', function (e) {
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
        U.post('/admin/video/art-tags/save', {name: name, status: 1}).then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '添加失败', 'err'); return; }
            compose.name.value = '';
            compose.name.focus();
            table.refresh();
            U.toast('已添加', 'ok');
        }).catch(function () { U.loading(false); U.toast('添加失败', 'err'); });
    });
    U.on('#art-tag-batch-del', 'click', function () {
        var ids = table.selectedIds();
        if (!ids.length) { U.toast('请先勾选标签', 'err'); return; }
        if (!U.confirm('删除已选标签？稿件还在，只去掉标记。')) return;
        U.post('/admin/video/art-tags/batch', {ids: ids.join(','), action: 'delete'}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || '已删除', 'ok');
        });
    });
    U.on('#art-tag-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#art-tag-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a || a.target === '_blank') return;
        if (a.getAttribute('href') && a.getAttribute('href').indexOf('/admin/video/art-tags/') === 0 && !a.classList.contains('js-del')) return;
        if (!a.classList.contains('js-del')) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        var n = parseInt(row.art_count, 10) || 0;
        var msg = n > 0
            ? ('「' + (row.name || '') + '」用在 ' + n + ' 篇上，删除后只去掉标签，稿件还在。确定？')
            : ('确定删除「' + (row.name || '') + '」？');
        if (!U.confirm(msg)) return;
        U.post('/admin/video/art-tags/delete', {id: row.id}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || '已删除', 'ok');
        });
    });
})();
</script>
@endpush
