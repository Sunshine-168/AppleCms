@extends('admin.layouts.inner')
@section('title', $title)

@php
    $ready = (bool) ($ready ?? false);
@endphp

@section('plain')
<div class="card card-panel tag-index">
    <div class="card-header">
        <span>作者 <em id="novel-author-count"></em></span>
        <a class="btn btn-muted btn-sm" href="/admin/video/novel-authors/create">完整表单</a>
    </div>
    <div class="card-body">
        <div class="tag-compose">
            <form class="tag-compose-form" id="novel-author-compose" onsubmit="return false;">
                <label class="tag-compose-label" for="novel-author-quick">新增作者</label>
                <div class="tag-compose-row">
                    <input id="novel-author-quick" type="text" name="name" value="" placeholder="输入名称，如 尾田荣一郎" aria-label="新增作者" @if($ready) autofocus @endif>
                    <button class="btn" type="submit" id="novel-author-add">添加</button>
                </div>
                <p class="muted field-hint">回车可连续添加。需要改网址时，<a href="/admin/video/novel-authors/create">打开完整表单</a>。只给小说用，不是会员或影人库。</p>
            </form>
        </div>
        <form class="filter-bar" id="novel-author-search" onsubmit="return false;">
            <input type="hidden" name="unused" value="">
            <input type="search" name="q" placeholder="搜索作者名或网址标识" autocomplete="off" aria-label="搜索作者">
            <button type="button" class="btn btn-sm" id="novel-author-search-btn">搜索</button>
            <button type="reset" class="btn btn-muted btn-sm" id="novel-author-reset-btn">重置</button>
        </form>
        <div class="queue-chips" id="novel-author-queues">
            <button type="button" class="chip" data-unused="">全部</button>
            <button type="button" class="chip" data-unused="1">未使用</button>
        </div>
        <p class="muted recycle-lead">小说作者库。删作者只拿掉关联，作品还在。作品上也可直接填逗号作者，会自动进这个库。</p>
        <div class="batch-bar" id="novel-author-batch" hidden>
            <strong id="novel-author-batch-count">已选 0 个</strong>
            <button type="button" class="btn btn-danger btn-sm" id="novel-author-batch-del">删除</button>
            <button type="button" class="btn btn-muted btn-sm" id="novel-author-batch-clear">取消选择</button>
        </div>
        <div id="novel-author-table"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var form = document.getElementById('novel-author-search');
    var compose = document.getElementById('novel-author-compose');
    var batchBar = document.getElementById('novel-author-batch');
    var batchCount = document.getElementById('novel-author-batch-count');
    var countEl = document.getElementById('novel-author-count');
    var ready = @json($ready);
    var base = '/admin/video/novel-authors';

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
        U.qa('#novel-author-queues .chip').forEach(function (chip) {
            chip.classList.toggle('active', (chip.getAttribute('data-unused') || '') === unused);
        });
    }
    function runSearch() {
        table.reload(queryWhere());
        markChips();
    }

    var table = U.table({
        el: '#novel-author-table',
        url: base + '/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (!ready) {
                return '<div class="list-empty"><p>请先执行数据库迁移</p></div>';
            }
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>没有符合条件的作者。</p><p><button type="button" class="btn btn-muted btn-sm" id="novel-author-empty-reset">清除筛选</button></p></div>';
            }
            return '<div class="list-empty"><p>还没有作者。</p><p class="muted">在上方输入名称即可添加。</p></div>';
        },
        onDraw: function (_wrap, list) {
            countEl.textContent = list.length ? '· ' + list.length : '';
            var reset = document.getElementById('novel-author-empty-reset');
            if (reset) reset.addEventListener('click', function () { form.reset(); if (form.unused) form.unused.value = ''; runSearch(); });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = '已选 ' + ids.length + ' 个';
        },
        cols: [
            {check: true, width: 36},
            {title: '作者', html: function (d) {
                var slug = String(d.slug || '').trim();
                return '<a class="entry-row-title" href="' + base + '/' + encodeURIComponent(d.id) + '/edit">' + U.escape(d.name || '无标题') + '</a>'
                    + '<div class="entry-row-meta">/novel?author=' + U.escape(slug || d.name || d.id) + ' · #' + U.escape(d.id) + '</div>';
            }},
            {title: '作品', width: 120, html: function (d) {
                var n = parseInt(d.novel_count, 10) || 0;
                if (n > 0) return '<a href="/admin/video/mangas?author_id=' + encodeURIComponent(d.id) + '">' + n + ' 部</a>';
                return '<span class="muted">未使用</span>';
            }},
            {title: '操作', cls: 'actions', html: function (d) {
                var href = d.url ? String(d.url) : ('/novel?author=' + encodeURIComponent(d.slug || d.name || d.id));
                return '<a href="' + U.escape(href) + '" target="_blank" rel="noopener" class="btn-link">前台</a>'
                    + '<a href="' + base + '/' + encodeURIComponent(d.id) + '/edit" class="btn-link">编辑</a>'
                    + '<a href="#" class="btn-link js-del">删除</a>';
            }}
        ]
    });
    markChips();

    U.on('#novel-author-search-btn', 'click', runSearch);
    U.on('#novel-author-reset-btn', 'click', function () { setTimeout(function () { if (form.unused) form.unused.value = ''; runSearch(); }, 0); });
    document.getElementById('novel-author-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-unused]');
        if (!chip || !form.unused) return;
        form.unused.value = chip.getAttribute('data-unused') || '';
        runSearch();
    });
    compose.addEventListener('submit', function (e) {
        e.preventDefault();
        var name = String((compose.name && compose.name.value) || '').trim();
        if (!name) { U.toast('请填写作者名称', 'err'); compose.name.focus(); return; }
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
    U.on('#novel-author-batch-del', 'click', function () {
        var ids = table.selectedIds();
        if (!ids.length) { U.toast('请先勾选作者', 'err'); return; }
        if (!U.confirm('删除已选作者？作品还在，只去掉关联。')) return;
        U.post(base + '/batch', {ids: ids.join(','), action: 'delete'}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || '已删除', 'ok');
        });
    });
    U.on('#novel-author-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#novel-author-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a || a.target === '_blank') return;
        if (a.getAttribute('href') && a.getAttribute('href').indexOf(base + '/') === 0 && !a.classList.contains('js-del')) return;
        if (!a.classList.contains('js-del')) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        var n = parseInt(row.novel_count, 10) || 0;
        var msg = n > 0
            ? ('「' + (row.name || '') + '」用在 ' + n + ' 部上，删除后只去掉关联，作品还在。确定？')
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
