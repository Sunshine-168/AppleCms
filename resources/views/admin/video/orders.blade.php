@extends('admin.layouts.inner')
@section('title', $title)

@php
    $queues = $queues ?? ['all' => 0, 'pending' => 0, 'paid' => 0, 'closed' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
@endphp

@section('plain')
<div class="card card-panel order-index">
    <div class="card-header">
        <span>订单 <em id="order-count"></em></span>
        <div>
            <a class="btn btn-muted btn-sm" href="/admin/video/members">会员</a>
            <button type="button" class="btn btn-sm" id="order-add-btn">补录订单</button>
        </div>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="order-search" onsubmit="return false;">
            <input type="hidden" name="status">
            <input type="search" name="q" placeholder="搜索单号、流水、会员名或 ID" autocomplete="off" aria-label="搜索订单">
            <select name="channel" aria-label="渠道">
                <option value="">全部渠道</option>
                <option value="wechat">微信</option>
                <option value="alipay">支付宝</option>
                <option value="manual">人工</option>
            </select>
            <button type="button" class="btn btn-sm" id="order-search-btn">搜索</button>
            <button type="reset" class="btn btn-muted btn-sm" id="order-reset-btn">重置</button>
        </form>
        <div class="queue-chips" id="order-queues">
            <button type="button" class="chip" data-queue="">全部@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="0">待付@if($q('pending') > 0)<em>{{ $q('pending') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="1">已付@if($q('paid') > 0)<em>{{ $q('paid') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="2">已关闭@if($q('closed') > 0)<em>{{ $q('closed') }}</em>@endif</button>
        </div>
        <p class="muted recycle-lead">确认已付会给会员加积分，每笔只加一次。关掉或删除不会扣回去。微信/支付宝到账要等支付回调；线下到账在这里补录。</p>
        <div class="batch-bar" id="order-batch" hidden>
            <strong id="order-batch-count">已选 0 笔</strong>
            <button type="button" class="btn btn-sm" id="order-batch-pay">确认已付</button>
            <button type="button" class="btn btn-muted btn-sm" id="order-batch-close">关闭</button>
            <button type="button" class="btn btn-danger btn-sm" id="order-batch-del">删除</button>
            <button type="button" class="btn btn-muted btn-sm" id="order-batch-clear">取消选择</button>
        </div>
        <div id="order-table"></div>
    </div>
</div>
<template id="order-dialog-tpl">
    <form>
        <input type="hidden" name="id">
        <label>会员 ID</label>
        <input type="number" name="member_id" placeholder="前台会员的数字 ID">
        <p class="muted field-hint">在「会员」列表里看 ID。这不是后台账号。</p>
        <label>单号</label>
        <input type="text" name="order_no" placeholder="留空自动生成">
        <label>金额（元）</label>
        <input type="number" name="amount_yuan" value="0" min="0" step="0.01">
        <label>积分</label>
        <input type="number" name="points" value="0" min="0">
        <p class="muted field-hint">确认已付时加到会员账上。已付过的不会再加。</p>
        <label>渠道</label>
        <select name="channel">
            <option value="manual">人工</option>
            <option value="wechat">微信</option>
            <option value="alipay">支付宝</option>
        </select>
        <label>支付流水</label>
        <input type="text" name="trade_no" placeholder="可空">
        <label>状态</label>
        <select name="status">
            <option value="0">待付</option>
            <option value="1">已付</option>
            <option value="2">关闭</option>
        </select>
        <label>备注</label>
        <input type="text" name="remark" placeholder="可空">
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var form = document.getElementById('order-search');
    var batchBar = document.getElementById('order-batch');
    var batchCount = document.getElementById('order-batch-count');
    var countEl = document.getElementById('order-count');

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
        U.qa('#order-queues .chip').forEach(function (chip) {
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
        var st = String(d.status);
        var badge = st === '1' ? '<span class="badge badge-ok">已付</span>' : (st === '2' ? '<span class="badge badge-off">关闭</span>' : '<span class="badge badge-warn">待付</span>');
        var who = d.member_name ? U.escape(d.member_name) : ('会员 #' + U.escape(d.member_id || 0));
        var meta = who;
        if (d.member_email) meta += ' · ' + U.escape(d.member_email);
        meta += ' · ' + U.escape(d.channel_label || '');
        if (d.trade_no) meta += ' · ' + U.escape(d.trade_no);
        return '<div class="entry-row-title-line"><a class="entry-row-title js-edit" href="#">' + U.escape(d.order_no || '无单号') + '</a> ' + badge + '</div>'
            + '<div class="entry-row-meta">' + meta + '</div>';
    }
    function statusHtml(d) {
        var st = String(d.status);
        if (st === '1') return U.status(true, '已付');
        if (st === '2') return U.status(false, '关闭');
        return '<span class="status status-warn">待付</span>';
    }

    var table = U.table({
        el: '#order-table',
        url: '/admin/video/orders/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>没有符合条件的订单。</p><p><button type="button" class="btn btn-muted btn-sm" id="order-empty-reset">清除筛选</button></p></div>';
            }
            return '<div class="list-empty"><p>还没有订单。</p><p class="muted">会员充值会出现在这里。线下到账可以补录，确认已付才会加积分。</p><p><button type="button" class="btn btn-primary btn-sm" id="order-empty-add">补录订单</button></p></div>';
        },
        onDraw: function (_wrap, list) {
            countEl.textContent = list.length ? '· ' + list.length : '';
            var add = document.getElementById('order-empty-add');
            var reset = document.getElementById('order-empty-reset');
            if (add) add.addEventListener('click', function () { openDialog('add'); });
            if (reset) reset.addEventListener('click', function () { form.reset(); runSearch(); });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = '已选 ' + ids.length + ' 笔';
        },
        cols: [
            {check: true, width: 36},
            {title: '订单', html: titleHtml},
            {title: '金额', width: 96, html: function (d) { return U.escape((d.amount_yuan || '0.00') + ' 元'); }},
            {key: 'points', title: '积分', width: 72},
            {title: '状态', width: 72, html: statusHtml},
            {title: '时间', width: 120, html: function (d) { return fmtTime(d.created_at); }},
            {title: '操作', cls: 'actions', html: function (d) {
                var html = '';
                if (String(d.status) === '0') html += '<a href="#" class="btn-link js-pay">确认已付</a><a href="#" class="btn-link js-close">关闭</a>';
                else if (String(d.status) === '1') html += '<a href="#" class="btn-link js-close">关闭</a>';
                html += '<a href="#" class="btn-link js-edit">编辑</a><a href="#" class="btn-link js-del">删除</a>';
                return html;
            }}
        ]
    });
    markChips();

    function openDialog(mode, row) {
        row = row || {};
        U.dialog({
            title: mode === 'edit' ? '编辑订单' : '补录订单',
            content: document.getElementById('order-dialog-tpl').innerHTML,
            onOpen: function (body) {
                var formEl = body.querySelector('form');
                U.fillForm(formEl, {
                    id: mode === 'edit' ? (row.id || '') : '',
                    member_id: row.member_id || '',
                    order_no: row.order_no || '',
                    amount_yuan: row.amount_yuan != null ? row.amount_yuan : '0.00',
                    points: row.points == null ? 0 : row.points,
                    channel: row.channel || 'manual',
                    trade_no: row.trade_no || '',
                    status: row.status == null ? '0' : String(row.status),
                    remark: row.remark || ''
                });
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (!data.member_id) { U.toast('请填写会员 ID', 'err'); return false; }
                if (mode !== 'edit') delete data.id; else data.id = row.id;
                return U.post('/admin/video/orders/save', data).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return false; }
                    U.toast(mode === 'edit' ? '已保存' : '已补录', 'ok');
                    table.refresh();
                });
            }
        });
    }

    function selectedIds() { return table.selectedIds(); }
    function batch(action, value, confirmText) {
        var ids = selectedIds();
        if (!ids.length) { U.toast('请先勾选订单', 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/orders/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '操作失败', 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || '操作成功', 'ok');
        });
    }
    function setStatus(row, status, confirmText) {
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/orders/save', {id: row.id, status: status}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
            table.refresh();
            U.toast(status === 1 ? '已入账' : '已关闭', 'ok');
        });
    }

    U.on('#order-search-btn', 'click', runSearch);
    U.on('#order-reset-btn', 'click', function () { setTimeout(runSearch, 0); });
    U.on('#order-add-btn', 'click', function () { openDialog('add'); });
    document.getElementById('order-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#order-batch-pay', 'click', function () {
        batch('status', 1, '确认已付会给会员加积分，每笔只加一次。确定入账？');
    });
    U.on('#order-batch-close', 'click', function () {
        batch('status', 2, '关闭已选订单？已付的不会扣回积分。');
    });
    U.on('#order-batch-del', 'click', function () { batch('delete', '', '确定删除选中订单？已付的不会扣回积分。'); });
    U.on('#order-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#order-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-edit')) openDialog('edit', row);
        if (a.classList.contains('js-pay')) setStatus(row, 1, '确认订单「' + (row.order_no || '') + '」已付？将给会员加上 ' + (row.points || 0) + ' 积分。');
        if (a.classList.contains('js-close')) setStatus(row, 2, '关闭订单「' + (row.order_no || '') + '」？已付的不会扣回积分。');
        if (a.classList.contains('js-del')) {
            if (!U.confirm('确定删除订单「' + (row.order_no || '') + '」？已付的不会扣回积分。')) return;
            U.post('/admin/video/orders/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh();
                U.toast('已删除', 'ok');
            });
        }
    });
})();
</script>
@endpush
