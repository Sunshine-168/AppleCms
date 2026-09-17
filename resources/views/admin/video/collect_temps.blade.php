@extends('admin.layouts.inner')
@section('title', $title)

@php
    $queues = $queues ?? ['all' => 0, 'pending' => 0, 'failed' => 0, 'done' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
    $sourceId = (int) ($sourceId ?? 0);
    $sourceName = trim((string) ($sourceName ?? ''));
    $sourceChip = $sourceName !== '' ? $sourceName : ($sourceId > 0 ? ('采集源 #'.$sourceId) : '');
    $toTemp = (bool) ($toTemp ?? false);
@endphp

@section('plain')
<div class="card card-panel collect-temp-index">
    <div class="card-header">
        <span>待审入库 <em id="ctemp-count"></em></span>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="ctemp-search" onsubmit="return false;">
            <input type="hidden" name="status">
            <input type="hidden" name="failed">
            <input type="hidden" name="collect_source_id" value="{{ $sourceId > 0 ? $sourceId : '' }}">
            <input type="search" name="q" placeholder="搜片名或采集源" autocomplete="off" aria-label="搜索待审入库">
            <button type="button" class="btn btn-sm" id="ctemp-search-btn">查询</button>
            <button type="reset" class="btn btn-muted btn-sm" id="ctemp-reset-btn">重置</button>
        </form>
        <div class="queue-chips" id="ctemp-queues">
            <button type="button" class="chip" data-queue="">全部@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="0">待转入@if($q('pending') > 0)<em>{{ $q('pending') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="failed" data-value="1">转入失败@if($q('failed') > 0)<em>{{ $q('failed') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="1">已入库@if($q('done') > 0)<em>{{ $q('done') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="source" id="ctemp-source-chip" @if($sourceId < 1) hidden @endif>{{ $sourceChip }}</button>
        </div>
        @if($toTemp)
            <p class="muted recycle-lead">新片会先停在这里。核对封面和分类后再转入片库。删记录不会动已经入库的片子。</p>
        @else
            <p class="muted recycle-lead">现在采集是直接进片库的，所以这里通常是空的。若要先审再入库，到「<a href="/admin/video/config/collect">内容接入</a>」改成先待审再转入。</p>
        @endif
        <div class="batch-bar" id="ctemp-batch" hidden>
            <strong id="ctemp-batch-count">已选 0 部</strong>
            <button type="button" class="btn btn-sm" id="ctemp-batch-promote">转入选中</button>
            <button type="button" class="btn btn-danger btn-sm" id="ctemp-batch-del">删除</button>
            <button type="button" class="btn btn-muted btn-sm" id="ctemp-batch-clear">取消选择</button>
        </div>
        <div id="ctemp-table"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var form = document.getElementById('ctemp-search');
    var batchBar = document.getElementById('ctemp-batch');
    var batchCount = document.getElementById('ctemp-batch-count');
    var countEl = document.getElementById('ctemp-count');
    var sourceChip = document.getElementById('ctemp-source-chip');
    var toTemp = @json($toTemp);

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
        var status = form.status.value;
        var failed = form.failed.value;
        var source = form.collect_source_id.value;
        U.qa('#ctemp-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = false;
            if (key === 'source') on = !!source;
            else if (key === '' && status === '' && failed === '') on = true;
            else if (key === 'failed' && failed === '1') on = true;
            else if (key === 'status' && failed === '' && status === val) on = true;
            chip.classList.toggle('active', on);
        });
    }
    function applyQueue(key, value) {
        if (key === 'source') {
            setSource('', '');
        } else {
            form.status.value = '';
            form.failed.value = '';
            if (key === 'status') form.status.value = value || '';
            if (key === 'failed') form.failed.value = '1';
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
    function badgeClass(d) {
        if (parseInt(d.status, 10) === 1) return 'badge-ok';
        if (parseInt(d.failed, 10) === 1) return 'badge-warn';
        return 'badge-off';
    }
    function titleHtml(d) {
        var cover = (d.cover || '').trim();
        var thumb = cover
            ? '<img class="vod-thumb" src="' + U.escape(cover) + '" alt="">'
            : '<span class="vod-thumb is-empty">无图</span>';
        var badge = '<span class="badge ' + badgeClass(d) + '">' + U.escape(d.status_label || '待转入') + '</span>';
        var meta = [];
        if (d.source_name) meta.push('<a class="btn-link js-source" href="#">' + U.escape(d.source_name) + '</a>');
        if (d.type_name) meta.push(U.escape(d.type_name));
        if (d.collect_id) meta.push('源ID ' + U.escape(String(d.collect_id)));
        if (d.msg && parseInt(d.status, 10) !== 1) meta.push(U.escape(d.msg));
        return '<div class="vod-cell">' + thumb + '<div><div class="entry-row-title-line"><span class="entry-row-title">' + U.escape(d.title || '无标题') + '</span> ' + badge + '</div>'
            + '<div class="entry-row-meta">' + (meta.join(' · ') || '—') + '</div></div></div>';
    }
    function opsHtml(d) {
        var html = '';
        if (parseInt(d.status, 10) !== 1) html += '<a href="#" class="btn-link js-promote">转入</a>';
        html += '<a href="#" class="btn-link js-del">删除</a>';
        return html;
    }

    var table = U.table({
        el: '#ctemp-table',
        url: '/admin/video/collect_temps/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>没有符合条件的待审片子。</p><p><button type="button" class="btn btn-muted btn-sm" id="ctemp-empty-reset">清除筛选</button></p></div>';
            }
            if (!toTemp) {
                return '<div class="list-empty"><p>采集是直接入库的，这里通常没有片子。</p><p class="muted">若要先审再进片库，改成写入临时表。</p><p><a class="btn btn-primary btn-sm" href="/admin/video/config/collect">去内容接入</a> <a class="btn btn-muted btn-sm" href="/admin/video/collects">去采集源</a></p></div>';
            }
            return '<div class="list-empty"><p>还没有待审片子。</p><p class="muted">在采集源里点「当天」就会出现在这里。</p><p><a class="btn btn-primary btn-sm" href="/admin/video/collects">去采集源</a></p></div>';
        },
        onDraw: function (wrap, list) {
            countEl.textContent = list.length ? '· ' + list.length : '';
            U.qa('tbody tr[data-idx]', wrap).forEach(function (tr) {
                var d = list[parseInt(tr.getAttribute('data-idx'), 10)];
                if (!d) return;
                if (parseInt(d.failed, 10) === 1) tr.classList.add('is-fail');
                if (parseInt(d.status, 10) === 1) tr.classList.add('is-done');
            });
            var reset = document.getElementById('ctemp-empty-reset');
            if (reset) reset.addEventListener('click', function () {
                form.reset();
                form.status.value = '';
                form.failed.value = '';
                setSource('', '');
                runSearch();
            });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = '已选 ' + ids.length + ' 部';
        },
        cols: [
            {check: true, width: 36},
            {title: '片子', html: titleHtml},
            {title: '时间', width: 120, html: function (d) { return fmtTime(d.created_at); }},
            {title: '操作', cls: 'actions', html: opsHtml}
        ]
    });
    markChips();

    function selectedIds() { return table.selectedIds(); }
    function promoteIds(ids, doneMsg) {
        if (!ids.length) { U.toast('请先勾选片子', 'err'); return; }
        U.loading(true);
        U.post('/admin/video/collect_temps/promote', {ids: ids.join(',')}).then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '转入失败', 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || doneMsg || '已转入', 'ok');
        }).catch(function () {
            U.loading(false);
            U.toast('转入失败', 'err');
        });
    }
    function batchPromote() {
        var ids = selectedIds();
        if (!ids.length) { U.toast('请先勾选片子', 'err'); return; }
        if (!U.confirm('转入选中片子到片库？')) return;
        promoteIds(ids, '已转入');
    }
    function batchDel() {
        var ids = selectedIds();
        if (!ids.length) { U.toast('请先勾选片子', 'err'); return; }
        if (!U.confirm('删除选中记录？已经入库的片子不会被删。')) return;
        U.post('/admin/video/collect_temps/batch', {ids: ids.join(','), action: 'delete'}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '操作失败', 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || '操作成功', 'ok');
        });
    }

    U.on('#ctemp-search-btn', 'click', runSearch);
    U.on('#ctemp-reset-btn', 'click', function () {
        setTimeout(function () {
            form.status.value = '';
            form.failed.value = '';
            setSource('', '');
            runSearch();
        }, 0);
    });
    document.getElementById('ctemp-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#ctemp-batch-promote', 'click', batchPromote);
    U.on('#ctemp-batch-del', 'click', batchDel);
    U.on('#ctemp-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#ctemp-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        if (a.getAttribute('href') && a.getAttribute('href').indexOf('/admin/video/') === 0) return;
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
        if (a.classList.contains('js-promote')) {
            promoteIds([row.id], '已转入正式库');
            return;
        }
        if (a.classList.contains('js-del')) {
            if (!U.confirm('删除这条待审记录？已经入库的片子不会被删。')) return;
            U.post('/admin/video/collect_temps/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh();
                U.toast('已删除', 'ok');
            });
        }
    });
})();
</script>
@endpush
