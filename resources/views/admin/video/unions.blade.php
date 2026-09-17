@extends('admin.layouts.inner')
@section('title', $title)

@php
    $queues = $queues ?? ['all' => 0, 'on' => 0, 'off' => 0, 'pending' => 0, 'adopted' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
@endphp

@section('plain')
<div class="card card-panel union-index">
    <div class="card-header">
        <span>推荐资源 <em id="union-count"></em></span>
        <div>
            <a class="btn btn-sm" href="/admin/video/unions/create">新增资源</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/collects">采集源</a>
        </div>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="union-search" onsubmit="return false;">
            <input type="hidden" name="adopted">
            <input type="text" name="name" placeholder="搜名称或接口" autocomplete="off">
            <select name="status">
                <option value="">状态</option>
                <option value="1">显示</option>
                <option value="0">隐藏</option>
            </select>
            <button type="button" class="btn btn-sm" id="union-search-btn">查询</button>
            <button type="reset" class="btn btn-muted btn-sm" id="union-reset-btn">重置</button>
        </form>
        <div class="queue-chips" id="union-queues">
            <button type="button" class="chip" data-queue="">全部@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="1">显示中@if($q('on') > 0)<em>{{ $q('on') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="0">已隐藏@if($q('off') > 0)<em>{{ $q('off') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="adopted" data-value="0">未接入@if($q('pending') > 0)<em>{{ $q('pending') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="adopted" data-value="1">已接入@if($q('adopted') > 0)<em>{{ $q('adopted') }}</em>@endif</button>
        </div>
        <p class="muted recycle-lead">这里只记接口。点「接入采集源」才会出现在采集源列表，不会立刻采片。</p>
        <div class="batch-bar" id="union-batch" hidden>
            <strong id="union-batch-count">已选 0 条</strong>
            <button type="button" class="btn btn-sm" id="union-batch-adopt">接入采集源</button>
            <button type="button" class="btn btn-sm" id="union-batch-on">显示</button>
            <button type="button" class="btn btn-muted btn-sm" id="union-batch-off">隐藏</button>
            <button type="button" class="btn btn-danger btn-sm" id="union-batch-del">删除</button>
            <button type="button" class="btn btn-muted btn-sm" id="union-batch-clear">取消选择</button>
        </div>
        <div id="union-table"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var QUEUE_KEYS = ['adopted'];
    var form = document.getElementById('union-search');
    var batchBar = document.getElementById('union-batch');
    var batchCount = document.getElementById('union-batch-count');
    var countEl = document.getElementById('union-count');

    function cleanWhere(data) {
        var out = {};
        Object.keys(data).forEach(function (k) { if (data[k] !== '') out[k] = data[k]; });
        return out;
    }
    function queryWhere() {
        return Object.assign({limit: 30}, cleanWhere(U.formData(form)));
    }
    function isFiltered(where) {
        return Object.keys(where || {}).some(function (k) { return k !== 'limit' && where[k] !== ''; });
    }
    function markChips() {
        var status = form.status.value;
        var adopted = form.adopted.value;
        U.qa('#union-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = false;
            if (key === '' && status === '' && adopted === '') on = true;
            else if (key === 'status' && adopted === '' && status === val) on = true;
            else if (key === 'adopted' && status === '' && adopted === val) on = true;
            chip.classList.toggle('active', on);
        });
    }
    function applyQueue(key, value) {
        QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
        form.status.value = '';
        if (key === 'status') form.status.value = value || '';
        else if (key && form[key]) form[key].value = value || '';
        runSearch();
    }
    function runSearch() {
        table.reload(queryWhere());
        markChips();
    }
    function nameHtml(d) {
        var letter = String(d.name || d.host || '?').slice(0, 1);
        var thumb = '<span class="link-thumb is-empty">' + U.escape(letter) + '</span>';
        var badge = String(d.status) === '1' ? '' : '<span class="badge badge-off">隐藏</span>';
        var adopted = String(d.adopted) === '1' ? '<span class="badge">已接入</span>' : '';
        var meta = [];
        if (d.host) meta.push(d.host);
        if (d.note) meta.push(d.note);
        return '<div class="vod-cell">' + thumb + '<div><div class="entry-row-title-line"><a class="entry-row-title" href="/admin/video/unions/' + d.id + '/edit">' + U.escape(d.name || '未命名') + '</a> ' + badge + ' ' + adopted + '</div>'
            + '<div class="entry-row-meta">' + U.escape(meta.join(' · ') || (d.api_url || '还没填接口')) + '</div></div></div>';
    }

    var table = U.table({
        el: '#union-table',
        url: '/admin/video/unions/list',
        where: queryWhere(),
        pager: false,
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>没有符合条件的资源站</p><p><button type="button" class="btn btn-muted btn-sm" id="union-empty-reset">清除筛选</button></p></div>';
            }
            return '<div class="list-empty"><p>还没有收藏的资源站</p><p class="muted">把别人给的苹果接口先记在这里。也可以先去探测，通了再收藏。</p><p><a class="btn btn-primary btn-sm" href="/admin/video/unions/create">新增资源</a> <a class="btn btn-muted btn-sm" href="/admin/video/tools/hub">试试接口</a></p></div>';
        },
        onDraw: function (_wrap, list) {
            countEl.textContent = list.length ? '· ' + list.length : '';
            var reset = document.getElementById('union-empty-reset');
            if (reset) reset.addEventListener('click', function () { form.reset(); runSearch(); });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = '已选 ' + ids.length + ' 条';
        },
        cols: [
            {check: true, width: 36},
            {title: '资源站', html: nameHtml},
            {key: 'sort', title: '排序', width: 64},
            {title: '状态', width: 72, html: function (d) {
                return String(d.status) === '1' ? U.status(true, '显示') : U.status(false, '隐藏');
            }},
            {title: '操作', cls: 'actions', html: function (d) {
                var html = '';
                if (String(d.adopted) === '1') {
                    html += '<a href="/admin/video/collects" class="btn-link">去采集</a>';
                } else {
                    html += '<a href="#" class="btn-link js-adopt">接入采集源</a>';
                }
                html += '<a href="/admin/video/unions/' + d.id + '/edit" class="btn-link">编辑</a>';
                html += '<a href="#" class="btn-link js-del">删除</a>';
                return html;
            }}
        ]
    });
    markChips();

    function selectedIds() { return table.selectedIds(); }
    function batch(action, value, confirmText) {
        var ids = selectedIds();
        if (!ids.length) { U.toast('请先勾选资源站', 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/unions/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '操作失败', 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || '操作成功', 'ok');
        });
    }
    function adopt(ids) {
        if (!ids.length) { U.toast('请先勾选资源站', 'err'); return; }
        U.post('/admin/video/unions/adopt', {ids: ids.join(',')}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '接入失败', 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || '已接入采集源', 'ok');
        });
    }

    U.on('#union-search-btn', 'click', runSearch);
    U.on('#union-reset-btn', 'click', function () { setTimeout(runSearch, 0); });
    document.getElementById('union-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#union-batch-adopt', 'click', function () { adopt(selectedIds()); });
    U.on('#union-batch-on', 'click', function () { batch('status', 1); });
    U.on('#union-batch-off', 'click', function () { batch('status', 0); });
    U.on('#union-batch-del', 'click', function () { batch('delete', '', '确认删除选中收藏？采集源里已经接入的不受影响。'); });
    U.on('#union-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#union-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (a.classList.contains('js-adopt')) {
            e.preventDefault();
            if (!row) return;
            adopt([row.id]);
            return;
        }
        if (a.classList.contains('js-del')) {
            e.preventDefault();
            if (!row) return;
            if (!U.confirm('删除收藏「' + (row.name || '') + '」？采集源不受影响。')) return;
            U.post('/admin/video/unions/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh();
                U.toast('已删除', 'ok');
            });
        }
    });
})();
</script>
@endpush
