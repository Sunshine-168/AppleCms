@extends('admin.layouts.inner')
@section('title', $title)

@php
    $queues = $queues ?? ['all' => 0, 'pending' => 0, 'paid' => 0, 'rejected' => 0, 'today' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
@endphp

@section('plain')
<div class="card card-panel withdraw-index">
    <div class="card-header">
        <span>提现@if($q('pending') > 0) <em>· {{ $q('pending') }} 待审</em>@endif</span>
        <div>
            <a class="btn btn-muted btn-sm" href="/admin/video/orders">订单</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/plogs">积分流水</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/members">会员</a>
        </div>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="withdraw-search" onsubmit="return false;">
            <input type="hidden" name="status">
            <input type="hidden" name="today">
            <input type="search" name="q" placeholder="搜账号、备注或会员" autocomplete="off" aria-label="搜索提现">
            <button type="button" class="btn btn-sm" id="withdraw-search-btn">查询</button>
            <button type="reset" class="btn btn-muted btn-sm" id="withdraw-reset-btn">重置</button>
        </form>
        <div class="queue-chips" id="withdraw-queues">
            <button type="button" class="chip" data-queue="">全部@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="0">待审@if($q('pending') > 0)<em>{{ $q('pending') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="1">已打款@if($q('paid') > 0)<em>{{ $q('paid') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="2">拒绝@if($q('rejected') > 0)<em>{{ $q('rejected') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="today" data-value="1">今天@if($q('today') > 0)<em>{{ $q('today') }}</em>@endif</button>
        </div>
        <p class="muted recycle-lead">积分兑现金。待审优先。标已打款会按金额分扣一次积分，拒绝不扣；已经打过款的不会再扣。删除只去记录，不退积分。</p>
        <div class="batch-bar" id="withdraw-batch" hidden>
            <strong id="withdraw-batch-count">已选 0 条</strong>
            <button type="button" class="btn btn-sm" id="withdraw-batch-pay">标为已打款</button>
            <button type="button" class="btn btn-muted btn-sm" id="withdraw-batch-reject">拒绝</button>
            <button type="button" class="btn btn-danger btn-sm" id="withdraw-batch-del">删除</button>
            <button type="button" class="btn btn-muted btn-sm" id="withdraw-batch-clear">取消选择</button>
        </div>
        <div id="withdraw-table"></div>
    </div>
</div>
<template id="withdraw-remark-tpl">
    <form>
        <input type="hidden" name="id">
        <label>备注</label>
        <input type="text" name="remark" maxlength="255" placeholder="可空" autocomplete="off">
        <p class="muted field-hint">只改备注，不改状态、不扣积分。</p>
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var form = document.getElementById('withdraw-search');
    var batchBar = document.getElementById('withdraw-batch');
    var batchCount = document.getElementById('withdraw-batch-count');
    var QUEUE_KEYS = ['today'];

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
        var status = form.status.value;
        var today = form.today.value;
        U.qa('#withdraw-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = false;
            if (key === '' && status === '' && today === '') on = true;
            else if (key === 'status' && today === '' && status === val) on = true;
            else if (key === 'today' && today === val) on = true;
            chip.classList.toggle('active', on);
        });
    }
    function applyQueue(key, value) {
        QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
        form.status.value = '';
        if (key === 'status') form.status.value = value || '';
        else if (key && form[key]) form[key].value = value || '1';
        runSearch();
    }
    function runSearch() {
        table.reload(queryWhere());
        markChips();
    }
    function applyHtml(d) {
        var who = d.member_name ? U.escape(d.member_name) : ('会员 #' + U.escape(d.member_id || 0));
        var st = parseInt(d.status, 10);
        var badge = st === 1 ? '<span class="badge badge-ok">已打款</span>' : (st === 2 ? '<span class="badge badge-off">拒绝</span>' : '<span class="badge badge-warn">待审</span>');
        var yuan = U.escape((d.amount_yuan || '0.00') + ' 元');
        var fen = U.escape(String(d.amount == null ? 0 : d.amount) + ' 分');
        var meta = [];
        if (d.account) meta.push(U.escape(d.account));
        if (d.remark) meta.push(U.escape(d.remark));
        if (d.created_at_text) meta.push(U.escape(d.created_at_text));
        if (d.member_points != null && d.member_points !== '') meta.push('现有积分 ' + U.escape(d.member_points));
        return '<div class="entry-row-title-line"><span class="entry-row-title">' + who + '</span> <strong>' + yuan + '</strong> <span class="muted">' + fen + '</span> ' + badge + '</div>'
            + (meta.length ? '<div class="entry-row-meta">' + meta.join(' · ') + '</div>' : '');
    }
    function statusHtml(d) {
        var st = parseInt(d.status, 10);
        if (st === 1) return U.status(true, '已打款');
        if (st === 2) return U.status(false, '拒绝');
        return '<span class="status status-warn">待审</span>';
    }

    var table = U.table({
        el: '#withdraw-table',
        url: '/admin/video/withdraws/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>没有符合条件的提现</p><p><button type="button" class="btn btn-muted btn-sm" id="withdraw-empty-reset">清除筛选</button></p></div>';
            }
            return '<div class="list-empty"><p>还没有提现申请</p><p class="muted">会员提交后会出现在这里。</p></div>';
        },
        onDraw: function (wrap, list) {
            U.qa('tbody tr[data-idx]', wrap).forEach(function (tr) {
                var d = list[parseInt(tr.getAttribute('data-idx'), 10)];
                if (d && parseInt(d.pending, 10) === 1) tr.classList.add('is-pending');
            });
            var reset = document.getElementById('withdraw-empty-reset');
            if (reset) reset.addEventListener('click', function () {
                form.reset();
                QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
                form.status.value = '';
                runSearch();
            });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = '已选 ' + ids.length + ' 条';
        },
        cols: [
            {check: true, width: 36},
            {title: '申请', html: applyHtml},
            {title: '状态', width: 88, html: statusHtml},
            {title: '操作', cls: 'actions', html: function (d) {
                var st = parseInt(d.status, 10);
                var html = '';
                if (st === 0) {
                    html += '<a href="#" class="btn-link js-pay">已打款</a><a href="#" class="btn-link js-reject">拒绝</a>';
                }
                html += '<a href="#" class="btn-link js-remark">备注</a>';
                if (parseInt(d.member_id, 10) > 0) {
                    html += '<a class="btn-link" href="/admin/video/members">会员</a>';
                }
                html += '<a href="#" class="btn-link js-del">删除</a>';
                return html;
            }}
        ]
    });
    markChips();

    function selectedIds() { return table.selectedIds(); }
    function batch(action, value, confirmText) {
        var ids = selectedIds();
        if (!ids.length) { U.toast('请先勾选提现', 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/withdraws/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '操作失败', 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || '操作成功', 'ok');
        });
    }
    function setStatus(row, status, confirmText) {
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/withdraws/save', {id: row.id, status: status}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
            table.refresh();
            U.toast(status === 1 ? '已打款' : '已拒绝', 'ok');
        });
    }
    function openRemark(row) {
        U.dialog({
            title: '备注',
            content: document.getElementById('withdraw-remark-tpl').innerHTML,
            onOpen: function (body) {
                U.fillForm(body.querySelector('form'), {
                    id: row.id || '',
                    remark: row.remark || ''
                });
                var input = body.querySelector('[name=remark]');
                if (input) input.focus();
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                data.id = row.id;
                return U.post('/admin/video/withdraws/save', data).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return false; }
                    U.toast('已保存', 'ok');
                    table.refresh();
                });
            }
        });
    }

    U.on('#withdraw-search-btn', 'click', runSearch);
    U.on('#withdraw-reset-btn', 'click', function () {
        setTimeout(function () {
            QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
            form.status.value = '';
            runSearch();
        }, 0);
    });
    document.getElementById('withdraw-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#withdraw-batch-pay', 'click', function () {
        batch('status', 1, '标为已打款会按金额分扣一次积分。已经打过款的不会再扣。确定打款？');
    });
    U.on('#withdraw-batch-reject', 'click', function () {
        batch('status', 2, '拒绝后不扣积分。确定拒绝？');
    });
    U.on('#withdraw-batch-del', 'click', function () { batch('delete', '', '删除选中提现？只去记录，不退积分。'); });
    U.on('#withdraw-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#withdraw-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        if (a.getAttribute('href') && a.getAttribute('href').indexOf('/admin/video/') === 0) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-pay')) {
            setStatus(row, 1, '标「' + (row.member_name || ('会员 #' + (row.member_id || ''))) + '」已打款？将按 ' + (row.amount_yuan || '0.00') + ' 元（' + (row.amount || 0) + ' 分）扣一次积分。');
        }
        if (a.classList.contains('js-reject')) setStatus(row, 2, '拒绝这条提现？不扣积分。');
        if (a.classList.contains('js-remark')) openRemark(row);
        if (a.classList.contains('js-del')) {
            if (!U.confirm('删除这条提现？只去记录，不退积分。')) return;
            U.post('/admin/video/withdraws/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh();
                U.toast('已删除', 'ok');
            });
        }
    });
})();
</script>
@endpush
