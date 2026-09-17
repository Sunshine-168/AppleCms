@extends('admin.layouts.inner')
@section('title', $title)

@php
    $queues = $queues ?? ['all' => 0, 'today' => 0, 'baidu' => 0, 'google' => 0, 'bing' => 0, 'other' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
@endphp

@section('plain')
<div class="card card-panel botlog-index">
    <div class="card-header">
        <span>爬虫日志 <em id="botlog-count"></em></span>
        <div>
            <a class="btn btn-muted btn-sm" href="/admin/stats/spiders">蜘蛛统计</a>
            <a class="btn btn-muted btn-sm" href="/admin/stats/logs?visitor=spider">访问明细</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/accesslogs">访问风控</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/push">搜索推送</a>
            <a class="btn btn-muted btn-sm" href="/robots.txt" target="_blank" rel="noopener">robots</a>
        </div>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="botlog-search" onsubmit="return false;">
            <input type="hidden" name="today">
            <input type="hidden" name="engine">
            <input type="search" name="q" placeholder="搜 IP、地址或标识" autocomplete="off" aria-label="搜索爬虫日志">
            <button type="button" class="btn btn-sm" id="botlog-search-btn">查询</button>
            <button type="reset" class="btn btn-muted btn-sm" id="botlog-reset-btn">重置</button>
        </form>
        <div class="queue-chips" id="botlog-queues">
            <button type="button" class="chip" data-queue="">全部@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="today" data-value="1">今天@if($q('today') > 0)<em>{{ $q('today') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="engine" data-value="baidu">百度@if($q('baidu') > 0)<em>{{ $q('baidu') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="engine" data-value="google">Google @if($q('google') > 0)<em>{{ $q('google') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="engine" data-value="bing">Bing @if($q('bing') > 0)<em>{{ $q('bing') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="engine" data-value="other">其他@if($q('other') > 0)<em>{{ $q('other') }}</em>@endif</button>
        </div>
        <p class="muted recycle-lead">和「<a href="/admin/video/accesslogs">访问风控</a>」同一张前台页面流水，这里只看浏览器标识被认成爬虫的。趋势去「<a href="/admin/stats/spiders">蜘蛛统计</a>」；带标题和来路的页去「<a href="/admin/stats/logs?visitor=spider">访问明细</a>」。<strong>不能封 IP</strong>。删掉只清流水，不影响收录。</p>
        <div class="batch-bar" id="botlog-batch" hidden>
            <strong id="botlog-batch-count">已选 0 条</strong>
            <button type="button" class="btn btn-danger btn-sm" id="botlog-batch-del">删除</button>
            <button type="button" class="btn btn-muted btn-sm" id="botlog-batch-clear">取消选择</button>
        </div>
        <div id="botlog-table"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var form = document.getElementById('botlog-search');
    var batchBar = document.getElementById('botlog-batch');
    var batchCount = document.getElementById('botlog-batch-count');
    var countEl = document.getElementById('botlog-count');
    var QUEUE_KEYS = ['today', 'engine'];

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
        var today = form.today.value;
        var engine = form.engine.value;
        U.qa('#botlog-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = false;
            if (key === '' && today === '' && engine === '') on = true;
            else if (key === 'today' && engine === '' && today === val) on = true;
            else if (key === 'engine' && today === '' && engine === val) on = true;
            chip.classList.toggle('active', on);
        });
    }
    function applyQueue(key, value) {
        QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
        if (key && form[key]) form[key].value = value || '1';
        runSearch();
    }
    function runSearch() {
        table.reload(queryWhere());
        markChips();
    }
    function spiderHtml(d) {
        var html = '<strong>' + U.escape(d.spider_label || '爬虫') + '</strong>';
        if (d.group_label) html += '<div class="muted">' + U.escape(d.group_label) + '</div>';
        return html;
    }
    function urlHtml(d) {
        var full = d.url || '';
        var short = d.url_short || full;
        if (!full) return '<span class="muted">—</span>';
        if (!/^https?:\/\//i.test(full)) return U.escape(short);
        return '<a class="botlog-url" href="' + U.escape(full) + '" target="_blank" rel="noopener">' + U.escape(short) + '</a>';
    }

    var table = U.table({
        el: '#botlog-table',
        url: '/admin/video/botlogs/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>没有符合条件的记录</p><p><button type="button" class="btn btn-muted btn-sm" id="botlog-empty-reset">清除筛选</button></p></div>';
            }
            return '<div class="list-empty"><p>还没有爬虫进来</p><p class="muted">本机只开后台不会记。站点放到公网、等搜索引擎来抓，或前台被带爬虫标识的访问打到，才会出现。</p></div>';
        },
        onDraw: function (_wrap, list) {
            if (countEl) countEl.textContent = list.length ? '· ' + list.length : '';
            var reset = document.getElementById('botlog-empty-reset');
            if (reset) reset.addEventListener('click', function () {
                form.reset();
                QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
                runSearch();
            });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = '已选 ' + ids.length + ' 条';
        },
        cols: [
            {check: true, width: 36},
            {title: '时间', width: 150, html: function (d) { return U.escape(d.created_at_text || ''); }},
            {title: '蜘蛛', width: 120, html: spiderHtml},
            {title: '地址', html: urlHtml},
            {title: 'IP', width: 130, html: function (d) { return U.escape(d.ip || ''); }},
            {title: '标识', html: function (d) { return '<span class="muted" title="' + U.escape(d.ua || '') + '">' + U.escape(d.ua_short || d.ua || '') + '</span>'; }},
            {title: '操作', cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-del">删除</a>';
            }}
        ]
    });
    markChips();

    function selectedIds() { return table.selectedIds(); }
    function batchDel() {
        var ids = selectedIds();
        if (!ids.length) { U.toast('请先勾选记录', 'err'); return; }
        if (!U.confirm('删除这 ' + ids.length + ' 条？不影响收录。')) return;
        U.post('/admin/video/botlogs/batch', {ids: ids.join(','), action: 'delete'}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '操作失败', 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || '已删除', 'ok');
        });
    }

    U.on('#botlog-search-btn', 'click', runSearch);
    U.on('#botlog-reset-btn', 'click', function () { setTimeout(function () {
        QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
        runSearch();
    }, 0); });
    document.getElementById('botlog-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#botlog-batch-del', 'click', batchDel);
    U.on('#botlog-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#botlog-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        var href = a.getAttribute('href') || '';
        if (href && href !== '#' && href.indexOf('javascript:') !== 0) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-del')) {
            if (!U.confirm('删除这条？不影响收录。')) return;
            U.post('/admin/video/botlogs/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh();
                U.toast('已删除', 'ok');
            });
        }
    });
})();
</script>
@endpush
