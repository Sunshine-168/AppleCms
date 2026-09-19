@extends('admin.layouts.inner')
@section('title', $title)

@php
    $queues = $queues ?? ['all' => 0, 'pending' => 0, 'paid' => 0, 'closed' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
    $orderJsLang = [
        'status' => admin_t('ui.status'),
        'actions' => admin_t('ui.actions'),
        'edit' => admin_t('ui.edit'),
        'delete' => admin_t('ui.delete'),
        'close' => admin_t('ui.close'),
        'fail' => admin_t('ui.fail'),
        'saved' => admin_t('ui.saved'),
        'deleted' => admin_t('ui.deleted'),
        'clear_filter' => admin_t('ui.clear_filter'),
        'selected_orders' => admin_t('ui.selected_orders', ['n' => '__N__']),
        'op_fail' => admin_t('manga.op_fail'),
        'op_ok' => admin_t('manga.op_ok'),
        'col_points' => admin_t('ui.col_points'),
        'pay_pending' => admin_t('ui.pay_pending'),
        'pay_paid' => admin_t('ui.pay_paid'),
        'pay_closed' => admin_t('ui.pay_closed'),
        'confirm_paid' => admin_t('ui.confirm_paid'),
        'add_order' => admin_t('ui.add_order'),
        'edit_order' => admin_t('ui.edit_order'),
        'col_order' => admin_t('ui.col_order'),
        'col_amount' => admin_t('ui.col_amount'),
        'col_time' => admin_t('ui.col_time'),
        'today_at' => admin_t('ui.today_at', ['time' => '__TIME__']),
        'yesterday_at' => admin_t('ui.yesterday_at', ['time' => '__TIME__']),
        'member_hash' => admin_t('ui.member_hash', ['id' => '__ID__']),
        'no_order_no' => admin_t('ui.no_order_no'),
        'amount_yuan' => admin_t('ui.amount_yuan', ['n' => '__N__']),
        'empty_orders' => admin_t('ui.empty_orders'),
        'empty_orders_hint' => admin_t('ui.empty_orders_hint'),
        'no_match_orders' => admin_t('ui.no_match_orders'),
        'please_fill_member_id' => admin_t('ui.please_fill_member_id'),
        'please_select_orders' => admin_t('ui.please_select_orders'),
        'order_created' => admin_t('ui.order_created'),
        'order_credited' => admin_t('ui.order_credited'),
        'order_closed_ok' => admin_t('ui.order_closed_ok'),
        'confirm_batch_pay' => admin_t('ui.confirm_batch_pay'),
        'confirm_batch_close_orders' => admin_t('ui.confirm_batch_close_orders'),
        'confirm_batch_del_orders' => admin_t('ui.confirm_batch_del_orders'),
        'confirm_pay_order' => admin_t('ui.confirm_pay_order', ['no' => '__NO__', 'n' => '__N__']),
        'confirm_close_order' => admin_t('ui.confirm_close_order', ['no' => '__NO__']),
        'confirm_del_order' => admin_t('ui.confirm_del_order', ['no' => '__NO__']),
    ];
@endphp

@section('plain')
<div class="card card-panel order-index">
    <div class="card-header">
        <span>{{ admin_t('ui.orders') }} <em id="order-count"></em></span>
        <div>
            <a class="btn btn-muted btn-sm" href="/admin/video/members">{{ admin_t('ui.members') }}</a>
            <button type="button" class="btn btn-sm" id="order-add-btn">{{ admin_t('ui.add_order') }}</button>
        </div>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="order-search" onsubmit="return false;">
            <input type="hidden" name="status">
            <input type="search" name="q" placeholder="{{ admin_t('ui.ph_order') }}" autocomplete="off" aria-label="{{ admin_t('ui.orders') }}">
            <select name="channel" aria-label="{{ admin_t('ui.all_channels') }}">
                <option value="">{{ admin_t('ui.all_channels') }}</option>
                <option value="wechat">{{ admin_t('ui.channel_wechat') }}</option>
                <option value="alipay">{{ admin_t('ui.channel_alipay') }}</option>
                <option value="epay">{{ admin_t('ui.channel_epay') }}</option>
                <option value="dfpay">DfPay</option>
                <option value="manual">{{ admin_t('ui.channel_manual') }}</option>
            </select>
            <button type="button" class="btn btn-sm" id="order-search-btn">{{ admin_t('ui.search') }}</button>
            <button type="reset" class="btn btn-muted btn-sm" id="order-reset-btn">{{ admin_t('ui.reset') }}</button>
        </form>
        <div class="queue-chips" id="order-queues">
            <button type="button" class="chip" data-queue="">{{ admin_t('ui.all') }}@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="0">{{ admin_t('ui.pay_pending') }}@if($q('pending') > 0)<em>{{ $q('pending') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="1">{{ admin_t('ui.pay_paid') }}@if($q('paid') > 0)<em>{{ $q('paid') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="2">{{ admin_t('ui.pay_closed') }}@if($q('closed') > 0)<em>{{ $q('closed') }}</em>@endif</button>
        </div>
        <p class="muted recycle-lead">{{ admin_t('ui.orders_lead') }}</p>
        <div class="batch-bar" id="order-batch" hidden>
            <strong id="order-batch-count">{{ admin_t('ui.selected_orders', ['n' => 0]) }}</strong>
            <button type="button" class="btn btn-sm" id="order-batch-pay">{{ admin_t('ui.confirm_paid') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="order-batch-close">{{ admin_t('ui.close') }}</button>
            <button type="button" class="btn btn-danger btn-sm" id="order-batch-del">{{ admin_t('ui.delete') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="order-batch-clear">{{ admin_t('ui.clear_selection') }}</button>
        </div>
        <div id="order-table"></div>
    </div>
</div>
<template id="order-dialog-tpl">
    <form class="admin-form">
        <input type="hidden" name="id">
        <label>{{ admin_t('ui.label_member_id') }}</label>
        <input type="number" name="member_id" placeholder="{{ admin_t('ui.ph_member_id') }}">
        <p class="muted field-hint">{{ admin_t('ui.hint_member_id') }}</p>
        <label>{{ admin_t('ui.label_order_no') }}</label>
        <input type="text" name="order_no" placeholder="{{ admin_t('ui.ph_order_no') }}">
        <label>{{ admin_t('ui.label_amount_yuan') }}</label>
        <input type="number" name="amount_yuan" value="0" min="0" step="0.01">
        <label>{{ admin_t('ui.label_points') }}</label>
        <input type="number" name="points" value="0" min="0">
        <p class="muted field-hint">{{ admin_t('ui.hint_order_points') }}</p>
        <label>{{ admin_t('ui.label_channel') }}</label>
        <select name="channel">
            <option value="manual">{{ admin_t('ui.channel_manual') }}</option>
            <option value="wechat">{{ admin_t('ui.channel_wechat') }}</option>
            <option value="alipay">{{ admin_t('ui.channel_alipay') }}</option>
            <option value="epay">{{ admin_t('ui.channel_epay') }}</option>
            <option value="dfpay">DfPay</option>
        </select>
        <label>{{ admin_t('ui.label_trade_no') }}</label>
        <input type="text" name="trade_no" placeholder="{{ admin_t('ui.ph_optional') }}">
        <label>{{ admin_t('ui.status') }}</label>
        <select name="status">
            <option value="0">{{ admin_t('ui.pay_pending') }}</option>
            <option value="1">{{ admin_t('ui.pay_paid') }}</option>
            <option value="2">{{ admin_t('ui.close') }}</option>
        </select>
        <label>{{ admin_t('ui.label_remark') }}</label>
        <input type="text" name="remark" placeholder="{{ admin_t('ui.ph_optional') }}">
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($orderJsLang);
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
        if (d.toDateString() === now.toDateString()) return String(L.today_at || '').replace('__TIME__', hm);
        var y = new Date(now);
        y.setDate(now.getDate() - 1);
        if (d.toDateString() === y.toDateString()) return String(L.yesterday_at || '').replace('__TIME__', hm);
        if (d.getFullYear() === now.getFullYear()) return pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + ' ' + hm;
        return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
    }
    function titleHtml(d) {
        var st = String(d.status);
        var badge = st === '1' ? '<span class="badge badge-ok">' + L.pay_paid + '</span>' : (st === '2' ? '<span class="badge badge-off">' + L.pay_closed + '</span>' : '<span class="badge badge-warn">' + L.pay_pending + '</span>');
        var who = d.member_name ? U.escape(d.member_name) : String(L.member_hash || '').replace('__ID__', U.escape(d.member_id || 0));
        var meta = who;
        if (d.member_email) meta += ' · ' + U.escape(d.member_email);
        meta += ' · ' + U.escape(d.channel_label || '');
        if (d.trade_no) meta += ' · ' + U.escape(d.trade_no);
        return '<div class="entry-row-title-line"><a class="entry-row-title js-edit" href="#">' + U.escape(d.order_no || L.no_order_no) + '</a> ' + badge + '</div>'
            + '<div class="entry-row-meta">' + meta + '</div>';
    }
    function statusHtml(d) {
        var st = String(d.status);
        if (st === '1') return U.status(true, L.pay_paid);
        if (st === '2') return U.status(false, L.pay_closed);
        return '<span class="status status-warn">' + L.pay_pending + '</span>';
    }

    var table = U.table({
        el: '#order-table',
        countEl: countEl,
        url: '/admin/video/orders/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>' + L.no_match_orders + '</p><p><button type="button" class="btn btn-muted btn-sm" id="order-empty-reset">' + L.clear_filter + '</button></p></div>';
            }
            return '<div class="list-empty"><p>' + L.empty_orders + '</p><p class="muted">' + L.empty_orders_hint + '</p><p><button type="button" class="btn btn-primary btn-sm" id="order-empty-add">' + L.add_order + '</button></p></div>';
        },
        onDraw: function (_wrap, list) {
            var add = document.getElementById('order-empty-add');
            var reset = document.getElementById('order-empty-reset');
            if (add) add.addEventListener('click', function () { openDialog('add'); });
            if (reset) reset.addEventListener('click', function () { form.reset(); runSearch(); });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = String(L.selected_orders || '').replace('__N__', String(ids.length));
        },
        cols: [
            {check: true, width: 36},
            {title: L.col_order, html: titleHtml},
            {title: L.col_amount, width: 96, html: function (d) { return U.escape(String(L.amount_yuan || '').replace('__N__', d.amount_yuan || '0.00')); }},
            {key: 'points', title: L.col_points, width: 72},
            {title: L.status, width: 72, html: statusHtml},
            {title: L.col_time, width: 120, html: function (d) { return fmtTime(d.created_at); }},
            {title: L.actions, cls: 'actions', html: function (d) {
                var html = '';
                if (String(d.status) === '0') html += '<a href="#" class="btn-link js-pay">' + L.confirm_paid + '</a><a href="#" class="btn-link js-close">' + L.close + '</a>';
                else if (String(d.status) === '1') html += '<a href="#" class="btn-link js-close">' + L.close + '</a>';
                html += '<a href="#" class="btn-link js-edit">' + L.edit + '</a><a href="#" class="btn-link js-del">' + L.delete + '</a>';
                return html;
            }}
        ]
    });
    markChips();

    function openDialog(mode, row) {
        row = row || {};
        U.dialog({
            title: mode === 'edit' ? L.edit_order : L.add_order,
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
                if (!data.member_id) { U.toast(L.please_fill_member_id, 'err'); return false; }
                if (mode !== 'edit') delete data.id; else data.id = row.id;
                return U.post('/admin/video/orders/save', data).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return false; }
                    U.toast(mode === 'edit' ? L.saved : L.order_created, 'ok');
                    table.refresh();
                });
            }
        });
    }

    function selectedIds() { return table.selectedIds(); }
    function batch(action, value, confirmText) {
        var ids = selectedIds();
        if (!ids.length) { U.toast(L.please_select_orders, 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/orders/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.op_fail, 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || L.op_ok, 'ok');
        });
    }
    function setStatus(row, status, confirmText) {
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/orders/save', {id: row.id, status: status}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
            table.refresh();
            U.toast(status === 1 ? L.order_credited : L.order_closed_ok, 'ok');
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
    U.on('#order-batch-pay', 'click', function () { batch('status', 1, L.confirm_batch_pay); });
    U.on('#order-batch-close', 'click', function () { batch('status', 2, L.confirm_batch_close_orders); });
    U.on('#order-batch-del', 'click', function () { batch('delete', '', L.confirm_batch_del_orders); });
    U.on('#order-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#order-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-edit')) openDialog('edit', row);
        if (a.classList.contains('js-pay')) setStatus(row, 1, String(L.confirm_pay_order || '').replace('__NO__', row.order_no || '').replace('__N__', String(row.points || 0)));
        if (a.classList.contains('js-close')) setStatus(row, 2, String(L.confirm_close_order || '').replace('__NO__', row.order_no || ''));
        if (a.classList.contains('js-del')) {
            if (!U.confirm(String(L.confirm_del_order || '').replace('__NO__', row.order_no || ''))) return;
            U.post('/admin/video/orders/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
                table.refresh();
                U.toast(L.deleted, 'ok');
            });
        }
    });
})();
</script>
@endpush
