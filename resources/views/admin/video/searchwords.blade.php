@extends('admin.layouts.inner')
@section('title', $title)

@php
    $queues = $queues ?? ['all' => 0, 'hot' => 0, 'once' => 0, 'today' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
@endphp

@section('plain')
<div class="card card-panel searchword-index">
    <div class="card-header">
        <span>搜索词 <em id="sword-count"></em></span>
        <div>
            <button type="button" class="btn btn-sm" id="sword-add-btn">加一条</button>
            <a class="btn btn-muted btn-sm" href="/search" target="_blank" rel="noopener">前台搜索</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/synonyms">同义词</a>
        </div>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="sword-search" onsubmit="return false;">
            <input type="hidden" name="hot">
            <input type="hidden" name="once">
            <input type="hidden" name="today">
            <input type="search" name="q" placeholder="搜关键词" autocomplete="off" aria-label="搜索关键词">
            <button type="button" class="btn btn-sm" id="sword-search-btn">查询</button>
            <button type="reset" class="btn btn-muted btn-sm" id="sword-reset-btn">重置</button>
        </form>
        <div class="queue-chips" id="sword-queues">
            <button type="button" class="chip" data-queue="">全部@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="hot" data-value="1">热搜@if($q('hot') > 0)<em>{{ $q('hot') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="once" data-value="1">只搜过一次@if($q('once') > 0)<em>{{ $q('once') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="today" data-value="1">今天@if($q('today') > 0)<em>{{ $q('today') }}</em>@endif</button>
        </div>
        <p class="muted recycle-lead">前台搜片会自动记次数。热搜是 10 次以上。删掉不会改影片。也可手工加一条，把次数调高就会排在前面。</p>
        <div class="batch-bar" id="sword-batch" hidden>
            <strong id="sword-batch-count">已选 0 条</strong>
            <button type="button" class="btn btn-danger btn-sm" id="sword-batch-del">删除</button>
            <button type="button" class="btn btn-muted btn-sm" id="sword-batch-clear">取消选择</button>
        </div>
        <div id="sword-table"></div>
    </div>
</div>
<template id="sword-dialog-tpl">
    <form>
        <input type="hidden" name="id">
        <label for="sword-word">关键词</label>
        <input id="sword-word" type="text" name="word" maxlength="80" autocomplete="off">
        <label for="sword-hits">次数</label>
        <input id="sword-hits" type="number" name="hits" min="0" value="1">
        <p class="muted field-hint">访客每搜一次加 1。手工加热搜可以填大一点。</p>
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var form = document.getElementById('sword-search');
    var batchBar = document.getElementById('sword-batch');
    var batchCount = document.getElementById('sword-batch-count');
    var countEl = document.getElementById('sword-count');
    var QUEUE_KEYS = ['hot', 'once', 'today'];

    function cleanWhere(data) {
        var out = {};
        Object.keys(data || {}).forEach(function (k) { if (data[k] !== '') out[k] = data[k]; });
        return out;
    }
    function queryWhere() {
        return Object.assign({limit: 20}, cleanWhere(U.formData(form)));
    }
    function isFiltered(where) {
        return Object.keys(where || {}).some(function (k) { return k !== 'limit' && where[k] !== ''; });
    }
    function markChips() {
        var hot = form.hot.value;
        var once = form.once.value;
        var today = form.today.value;
        U.qa('#sword-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var on = false;
            if (key === '' && hot === '' && once === '' && today === '') on = true;
            else if (key === 'hot' && hot === '1') on = true;
            else if (key === 'once' && once === '1') on = true;
            else if (key === 'today' && today === '1') on = true;
            chip.classList.toggle('active', on);
        });
    }
    function applyQueue(key) {
        QUEUE_KEYS.forEach(function (k) { form[k].value = ''; });
        if (key && form[key]) form[key].value = '1';
        runSearch();
    }
    function runSearch() {
        table.reload(queryWhere());
        markChips();
    }
    function fmtTime(ts) {
        ts = parseInt(ts, 10) || 0;
        if (!ts) return '—';
        var d = new Date(ts * 1000);
        var now = new Date();
        var pad = function (n) { return n < 10 ? '0' + n : '' + n; };
        var hm = pad(d.getHours()) + ':' + pad(d.getMinutes());
        if (d.toDateString() === now.toDateString()) return '今天 ' + hm;
        var y = new Date(now);
        y.setDate(now.getDate() - 1);
        if (d.toDateString() === y.toDateString()) return '昨天 ' + hm;
        if (d.getFullYear() === now.getFullYear()) return pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + ' ' + hm;
        return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
    }
    function wordHtml(d) {
        var hits = parseInt(d.hits, 10) || 0;
        var badge = hits >= 10 ? ' <span class="badge badge-ok">热</span>' : (hits === 1 ? ' <span class="badge badge-off">1 次</span>' : '');
        var href = d.search_url || ('/search?wd=' + encodeURIComponent(d.word || ''));
        return '<div class="entry-row-title-line"><a class="entry-row-title" href="' + U.escape(href) + '" target="_blank" rel="noopener">' + U.escape(d.word || '（空）') + '</a>' + badge + '</div>'
            + '<div class="entry-row-meta">搜过 <strong>' + U.escape(String(hits)) + '</strong> 次</div>';
    }

    var table = U.table({
        el: '#sword-table',
        queueKeys: QUEUE_KEYS,
        countEl: countEl,
        url: '/admin/video/searchwords/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>没有符合条件的搜索词。</p><p><button type="button" class="btn btn-muted btn-sm" id="sword-empty-reset">清除筛选</button></p></div>';
            }
            return '<div class="list-empty"><p>还没有人搜过。</p><p class="muted">去前台搜一次就会出现在这里。也可先加一条热搜。</p><p><a class="btn btn-muted btn-sm" href="/search" target="_blank" rel="noopener">去前台搜索</a> <button type="button" class="btn btn-primary btn-sm" id="sword-empty-add">加一条</button></p></div>';
        },
        onDraw: function (wrap, list) {
            U.qa('tbody tr[data-idx]', wrap).forEach(function (tr) {
                var d = list[parseInt(tr.getAttribute('data-idx'), 10)];
                if (d && parseInt(d.hot, 10) === 1) tr.classList.add('is-hot');
            });
            var reset = document.getElementById('sword-empty-reset');
            if (reset) reset.addEventListener('click', function () {
                form.reset();
                QUEUE_KEYS.forEach(function (k) { form[k].value = ''; });
                runSearch();
            });
            var emptyAdd = document.getElementById('sword-empty-add');
            if (emptyAdd) emptyAdd.addEventListener('click', function () { openDialog({}); });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = '已选 ' + ids.length + ' 条';
        },
        cols: [
            {check: true, width: 36},
            {title: '关键词', html: wordHtml},
            {title: '最近', width: 120, html: function (d) { return fmtTime(d.updated_at); }},
            {title: '操作', cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-edit">改次数</a><a href="#" class="btn-link js-del">删除</a>';
            }}
        ]
    });
    markChips();

    function openDialog(row) {
        row = row || {};
        U.dialog({
            title: row.id ? '改搜索词' : '加一条热搜',
            content: document.getElementById('sword-dialog-tpl').innerHTML,
            onOpen: function (body) {
                U.fillForm(body.querySelector('form'), {
                    id: row.id || '',
                    word: row.word || '',
                    hits: row.hits == null || row.hits === '' ? 1 : row.hits
                });
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (!String(data.word || '').trim()) { U.toast('请填写关键词', 'err'); return false; }
                return U.post('/admin/video/searchwords/save', data).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return false; }
                    U.toast(row.id ? '已保存' : '已加上', 'ok');
                    table.refresh();
                });
            }
        });
    }
    function batchDel() {
        var ids = table.selectedIds();
        if (!ids.length) { U.toast('请先勾选搜索词', 'err'); return; }
        if (!U.confirm('删除选中搜索词？影片不会变。')) return;
        U.post('/admin/video/searchwords/batch', {ids: ids.join(','), action: 'delete'}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '操作失败', 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || '已删除', 'ok');
        });
    }

    U.on('#sword-search-btn', 'click', runSearch);
    U.on('#sword-reset-btn', 'click', function () {
        setTimeout(function () {
            QUEUE_KEYS.forEach(function (k) { form[k].value = ''; });
            runSearch();
        }, 0);
    });
    document.getElementById('sword-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '');
    });
    U.on('#sword-add-btn', 'click', function () { openDialog({}); });
    U.on('#sword-batch-del', 'click', batchDel);
    U.on('#sword-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#sword-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        if (a.target === '_blank') return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        e.preventDefault();
        if (!row) return;
        if (a.classList.contains('js-edit')) openDialog(row);
        if (a.classList.contains('js-del')) {
            if (!U.confirm('删除「' + (row.word || '') + '」？影片不会变。')) return;
            U.post('/admin/video/searchwords/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh();
                U.toast('已删除', 'ok');
            });
        }
    });
})();
</script>
@endpush
