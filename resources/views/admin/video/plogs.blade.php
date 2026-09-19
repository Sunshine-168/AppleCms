@extends('admin.layouts.inner')
@section('title', $title)

@php
    $queues = $queues ?? ['all' => 0, 'in' => 0, 'out' => 0, 'play' => 0, 'order' => 0, 'card' => 0, 'admin' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
@endphp

@section('plain')
<div class="card card-panel plog-index">
    <div class="card-header">
        <span>积分流水 <em id="plog-count"></em></span>
        <div>
            <a class="btn btn-muted btn-sm" href="/admin/video/members">会员</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/orders">订单</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/cards">卡密</a>
            <button type="button" class="btn btn-sm" id="plog-add-btn">调积分</button>
        </div>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="plog-search" onsubmit="return false;">
            <input type="hidden" name="dir">
            <input type="hidden" name="type">
            <input type="hidden" name="member_id">
            <input type="search" name="q" placeholder="搜备注、会员名或 ID" autocomplete="off" aria-label="搜索流水">
            <button type="button" class="btn btn-sm" id="plog-search-btn">搜索</button>
            <button type="reset" class="btn btn-muted btn-sm" id="plog-reset-btn">重置</button>
        </form>
        <div class="queue-chips" id="plog-queues">
            <button type="button" class="chip" data-queue="">全部@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="dir" data-value="in">收入@if($q('in') > 0)<em>{{ $q('in') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="dir" data-value="out">支出@if($q('out') > 0)<em>{{ $q('out') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="type" data-value="play">点播@if($q('play') > 0)<em>{{ $q('play') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="type" data-value="order">订单@if($q('order') > 0)<em>{{ $q('order') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="type" data-value="card">卡密@if($q('card') > 0)<em>{{ $q('card') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="type" data-value="admin">后台@if($q('admin') > 0)<em>{{ $q('admin') }}</em>@endif</button>
        </div>
        <p class="muted recycle-lead">积分对账单。点播扣分、充值、卡密兑换、后台调积分都会记。删掉一行不会改会员积分。</p>
        <div class="batch-bar" id="plog-batch" hidden>
            <strong id="plog-batch-count">已选 0 条</strong>
            <button type="button" class="btn btn-danger btn-sm" id="plog-batch-del">删除记录</button>
            <button type="button" class="btn btn-muted btn-sm" id="plog-batch-clear">取消选择</button>
        </div>
        <div id="plog-table"></div>
    </div>
</div>
<template id="plog-dialog-tpl">
    <form>
        <label>会员 ID</label>
        <input type="number" name="member_id" placeholder="前台会员的数字 ID" min="1">
        <p class="muted field-hint">在「会员」列表里看 ID。会同时改余额并记一笔流水。</p>
        <label>变动</label>
        <input type="number" name="points" value="0">
        <p class="muted field-hint">正数加积分，负数扣积分。不能填 0。</p>
        <label>备注</label>
        <input type="text" name="remark" placeholder="如 线下补分、纠错" maxlength="250">
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var form = document.getElementById('plog-search');
    var qs = new URLSearchParams(location.search);
    if (qs.get('member_id') && form.member_id) form.member_id.value = qs.get('member_id');
    var batchBar = document.getElementById('plog-batch');
    var batchCount = document.getElementById('plog-batch-count');
    var countEl = document.getElementById('plog-count');

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
        var dir = form.dir.value;
        var type = form.type.value;
        U.qa('#plog-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = (key === '' && dir === '' && type === '')
                || (key === 'dir' && dir === val && type === '')
                || (key === 'type' && type === val && dir === '');
            chip.classList.toggle('active', on);
        });
    }
    function applyQueue(key, value) {
        form.dir.value = '';
        form.type.value = '';
        if (key === 'dir') form.dir.value = value || '';
        if (key === 'type') form.type.value = value || '';
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
        var who = d.member_name ? U.escape(d.member_name) : ('会员 #' + U.escape(d.member_id || 0));
        var meta = d.member_email ? U.escape(d.member_email) : ('ID ' + U.escape(d.member_id || 0));
        if (d.remark) meta += ' · ' + U.escape(d.remark);
        return '<div class="entry-row-title-line"><a class="entry-row-title" href="/admin/video/members?q=' + encodeURIComponent(d.member_id || '') + '">' + who + '</a> <span class="badge">' + U.escape(d.type_label || '') + '</span></div>'
            + '<div class="entry-row-meta">' + meta + '</div>';
    }
    function deltaHtml(d) {
        var n = parseInt(d.points, 10) || 0;
        var cls = n < 0 ? 'plog-out' : 'plog-in';
        var text = (n > 0 ? '+' : '') + n;
        return '<span class="' + cls + '">' + U.escape(text) + '</span>';
    }

    var table = U.table({
        el: '#plog-table',
        countEl: countEl,
        url: '/admin/video/plogs/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>没有符合条件的流水。</p><p><button type="button" class="btn btn-muted btn-sm" id="plog-empty-reset">清除筛选</button></p></div>';
            }
            return '<div class="list-empty"><p>还没有流水。</p><p class="muted">点播、充值、卡密兑换、后台调积分会出现在这里。</p><p><button type="button" class="btn btn-primary btn-sm" id="plog-empty-add">调积分</button> <a class="btn btn-muted btn-sm" href="/admin/video/members">去会员</a></p></div>';
        },
        onDraw: function (_wrap, list) {
            var add = document.getElementById('plog-empty-add');
            var reset = document.getElementById('plog-empty-reset');
            if (add) add.addEventListener('click', openAdjust);
            if (reset) reset.addEventListener('click', function () { form.reset(); runSearch(); });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = '已选 ' + ids.length + ' 条';
        },
        cols: [
            {check: true, width: 36},
            {title: '会员', html: titleHtml},
            {title: '变动', width: 88, html: deltaHtml},
            {title: '余额', width: 72, html: function (d) { return U.escape(String(d.balance == null ? '' : d.balance)); }},
            {title: '类型', width: 72, html: function (d) { return U.escape(d.type_label || ''); }},
            {title: '时间', width: 120, html: function (d) { return fmtTime(d.created_at); }},
            {title: '操作', cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-del">删除</a>';
            }}
        ]
    });
    markChips();

    function openAdjust() {
        U.dialog({
            title: '调积分',
            content: document.getElementById('plog-dialog-tpl').innerHTML,
            okText: '入账',
            onOpen: function (body) {
                U.fillForm(body.querySelector('form'), {
                    member_id: form.member_id.value || '',
                    points: 0,
                    remark: ''
                });
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (!data.member_id || parseInt(data.member_id, 10) < 1) { U.toast('请填写会员 ID', 'err'); return false; }
                if (!data.points || parseInt(data.points, 10) === 0) { U.toast('变动不能为 0', 'err'); return false; }
                return U.post('/admin/video/plogs/save', data).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return false; }
                    U.toast('已入账', 'ok');
                    table.refresh();
                });
            }
        });
    }

    function selectedIds() { return table.selectedIds(); }
    function batchDel() {
        var ids = selectedIds();
        if (!ids.length) { U.toast('请先勾选流水', 'err'); return; }
        if (!U.confirm('删除选中记录？会员积分不会变，只是少了这些对账行。')) return;
        U.post('/admin/video/plogs/batch', {ids: ids.join(','), action: 'delete'}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '操作失败', 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || '操作成功', 'ok');
        });
    }

    U.on('#plog-search-btn', 'click', runSearch);
    U.on('#plog-reset-btn', 'click', function () { setTimeout(runSearch, 0); });
    U.on('#plog-add-btn', 'click', openAdjust);
    document.getElementById('plog-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#plog-batch-del', 'click', batchDel);
    U.on('#plog-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#plog-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        if (a.getAttribute('href') && a.getAttribute('href').indexOf('/admin/video/members') === 0) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-del')) {
            if (!U.confirm('删除这条流水？会员积分不会变。')) return;
            U.post('/admin/video/plogs/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh();
                U.toast('已删除', 'ok');
            });
        }
    });
})();
</script>
@endpush
