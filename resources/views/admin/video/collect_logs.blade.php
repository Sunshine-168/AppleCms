@extends('admin.layouts.inner')
@section('title', $title)

@php
    $queues = $queues ?? ['all' => 0, 'ok' => 0, 'fail' => 0, 'today' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
    $sourceId = (int) ($sourceId ?? 0);
    $sourceName = trim((string) ($sourceName ?? ''));
    $sourceChip = $sourceName !== '' ? $sourceName : ($sourceId > 0 ? ('采集源 #'.$sourceId) : '');
    $okPrefill = (string) ($okPrefill ?? '');
    $todayPrefill = (string) ($todayPrefill ?? '');
@endphp

@section('plain')
<div class="card card-panel collect-log-index">
    <div class="card-header">
        <span>采集日志 <em id="clog-count"></em></span>
        <div>
            <a class="btn btn-muted btn-sm" href="/admin/video/collects">采集源</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/collect_tasks">定时采集</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/collect_temps">待审入库</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/unions">推荐资源</a>
        </div>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="clog-search" onsubmit="return false;">
            <input type="hidden" name="ok" value="{{ $okPrefill }}">
            <input type="hidden" name="today" value="{{ $todayPrefill }}">
            <input type="hidden" name="collect_source_id" value="{{ $sourceId > 0 ? $sourceId : '' }}">
            <input type="search" name="q" placeholder="搜采集源或说明" autocomplete="off" aria-label="搜索采集日志">
            <button type="button" class="btn btn-sm" id="clog-search-btn">查询</button>
            <button type="reset" class="btn btn-muted btn-sm" id="clog-reset-btn">重置</button>
        </form>
        <div class="queue-chips" id="clog-queues">
            <button type="button" class="chip" data-queue="">全部@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="ok" data-value="1">成功@if($q('ok') > 0)<em>{{ $q('ok') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="ok" data-value="0">失败@if($q('fail') > 0)<em>{{ $q('fail') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="today" data-value="1">今天@if($q('today') > 0)<em>{{ $q('today') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="source" id="clog-source-chip" @if($sourceId < 1) hidden @endif>{{ $sourceChip }}</button>
        </div>
        <p class="muted recycle-lead">每次在采集源点「当天」「本周」「全部」都会记一行。失败看说明；删掉记录不会改片库。</p>
        <div class="batch-bar" id="clog-batch" hidden>
            <strong id="clog-batch-count">已选 0 条</strong>
            <button type="button" class="btn btn-danger btn-sm" id="clog-batch-del">删除记录</button>
            <button type="button" class="btn btn-muted btn-sm" id="clog-batch-clear">取消选择</button>
        </div>
        <div id="clog-table"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var form = document.getElementById('clog-search');
    var batchBar = document.getElementById('clog-batch');
    var batchCount = document.getElementById('clog-batch-count');
    var countEl = document.getElementById('clog-count');
    var sourceChip = document.getElementById('clog-source-chip');

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
    function setSource(id, name) {
        form.collect_source_id.value = id ? String(id) : '';
        if (!sourceChip) return;
        sourceChip.textContent = name || (id ? ('采集源 #' + id) : '');
        sourceChip.hidden = !form.collect_source_id.value;
    }
    function markChips() {
        var ok = form.ok.value;
        var today = form.today.value;
        var source = form.collect_source_id.value;
        U.qa('#clog-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = false;
            if (key === 'source') on = !!source;
            else if (key === '' && ok === '' && today === '') on = true;
            else if (key === 'ok' && ok === val && today === '') on = true;
            else if (key === 'today' && today === '1' && ok === '') on = true;
            chip.classList.toggle('active', on);
        });
    }
    function applyQueue(key, value) {
        if (key === 'source') {
            setSource('', '');
        } else {
            form.ok.value = '';
            form.today.value = '';
            if (key === 'ok') form.ok.value = value || '';
            if (key === 'today') form.today.value = '1';
        }
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
    function titleHtml(d) {
        var name = d.source_name || ('采集源 #' + (d.collect_source_id || 0));
        var ok = parseInt(d.ok, 10) === 1;
        var badge = '<span class="badge ' + (ok ? 'badge-ok' : 'badge-warn') + '">' + U.escape(d.ok_label || (ok ? '成功' : '失败')) + '</span>';
        var meta = [];
        if (d.page_text) meta.push(U.escape(d.page_text));
        if (d.stat_text) meta.push(U.escape(d.stat_text));
        if (d.msg) meta.push(U.escape(d.msg));
        return '<div class="entry-row-title-line"><a class="entry-row-title js-source" href="#">' + U.escape(name) + '</a> ' + badge + '</div>'
            + '<div class="entry-row-meta">' + (meta.join(' · ') || '—') + '</div>';
    }
    function statHtml(d) {
        return '<span class="clog-stat"><strong>' + U.escape(String(d.created_n || 0)) + '</strong> 新'
            + ' · <strong>' + U.escape(String(d.updated_n || 0)) + '</strong> 更'
            + ' · <strong>' + U.escape(String(d.skipped_n || 0)) + '</strong> 跳</span>';
    }

    var table = U.table({
        el: '#clog-table',
        url: '/admin/video/collect_logs/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>没有符合条件的采集记录。</p><p><button type="button" class="btn btn-muted btn-sm" id="clog-empty-reset">清除筛选</button></p></div>';
            }
            return '<div class="list-empty"><p>还没有采集记录。</p><p class="muted">在采集源里点「当天」就会出现在这里。</p><p><a class="btn btn-primary btn-sm" href="/admin/video/collects">去采集源</a></p></div>';
        },
        onDraw: function (wrap, list) {
            countEl.textContent = list.length ? '· ' + list.length : '';
            U.qa('tbody tr[data-idx]', wrap).forEach(function (tr) {
                var d = list[parseInt(tr.getAttribute('data-idx'), 10)];
                if (d && parseInt(d.ok, 10) !== 1) tr.classList.add('is-fail');
            });
            var reset = document.getElementById('clog-empty-reset');
            if (reset) reset.addEventListener('click', function () {
                form.reset();
                form.ok.value = '';
                form.today.value = '';
                setSource('', '');
                runSearch();
            });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = '已选 ' + ids.length + ' 条';
        },
        cols: [
            {check: true, width: 36},
            {title: '采集源', html: titleHtml},
            {title: '条数', width: 140, html: statHtml},
            {title: '时间', width: 120, html: function (d) { return fmtTime(d.created_at); }},
            {title: '操作', cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-del">删除</a>';
            }}
        ]
    });
    markChips();

    function selectedIds() { return table.selectedIds(); }
    function batchDel() {
        var ids = selectedIds();
        if (!ids.length) { U.toast('请先勾选日志', 'err'); return; }
        if (!U.confirm('删除选中记录？片库不会变，只是少了这些采集行。')) return;
        U.post('/admin/video/collect_logs/batch', {ids: ids.join(','), action: 'delete'}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '操作失败', 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || '操作成功', 'ok');
        });
    }

    U.on('#clog-search-btn', 'click', runSearch);
    U.on('#clog-reset-btn', 'click', function () {
        setTimeout(function () {
            form.ok.value = '';
            form.today.value = '';
            setSource('', '');
            runSearch();
        }, 0);
    });
    document.getElementById('clog-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#clog-batch-del', 'click', batchDel);
    U.on('#clog-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#clog-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        if (a.getAttribute('href') && a.getAttribute('href').indexOf('/admin/video/collects') === 0) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        e.preventDefault();
        if (a.classList.contains('js-source')) {
            if (!row) return;
            setSource(row.collect_source_id || '', row.source_name || '');
            runSearch();
            return;
        }
        if (!row) return;
        if (a.classList.contains('js-del')) {
            if (!U.confirm('删除这条采集记录？片库不会变。')) return;
            U.post('/admin/video/collect_logs/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh();
                U.toast('已删除', 'ok');
            });
        }
    });
})();
</script>
@endpush
