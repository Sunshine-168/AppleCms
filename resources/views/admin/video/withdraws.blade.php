@extends('admin.layouts.inner')
@section('title', $title)

@php
    $queues = $queues ?? ['all' => 0, 'pending' => 0, 'paid' => 0, 'rejected' => 0, 'today' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
    $withdrawJsLang = [
        'status' => admin_t('ui.status'),
        'actions' => admin_t('ui.actions'),
        'delete' => admin_t('ui.delete'),
        'fail' => admin_t('ui.fail'),
        'saved' => admin_t('ui.saved'),
        'deleted' => admin_t('ui.deleted'),
        'clear_filter' => admin_t('ui.clear_filter'),
        'selected_rows' => admin_t('ui.selected_rows', ['n' => '__N__']),
        'op_fail' => admin_t('manga.op_fail'),
        'op_ok' => admin_t('manga.op_ok'),
        'members' => admin_t('ui.members'),
        'pending_review' => admin_t('ui.pending_review'),
        'withdraw_paid' => admin_t('ui.withdraw_paid'),
        'withdraw_rejected' => admin_t('ui.withdraw_rejected'),
        'mark_paid' => admin_t('ui.mark_paid'),
        'col_withdraw' => admin_t('ui.col_withdraw'),
        'amount_yuan' => admin_t('ui.amount_yuan', ['n' => '__YUAN__']),
        'amount_fen' => admin_t('ui.amount_fen', ['n' => '__FEN__']),
        'points_now' => admin_t('ui.points_now', ['n' => '__N__']),
        'label_remark' => admin_t('ui.label_remark'),
        'empty_withdraws' => admin_t('ui.empty_withdraws'),
        'empty_withdraws_hint' => admin_t('ui.empty_withdraws_hint'),
        'no_match_withdraws' => admin_t('ui.no_match_withdraws'),
        'please_select_withdraws' => admin_t('ui.please_select_withdraws'),
        'withdraw_paid_ok' => admin_t('ui.withdraw_paid_ok'),
        'withdraw_rejected_ok' => admin_t('ui.withdraw_rejected_ok'),
        'confirm_batch_pay_withdraws' => admin_t('ui.confirm_batch_pay_withdraws'),
        'confirm_batch_reject_withdraws' => admin_t('ui.confirm_batch_reject_withdraws'),
        'confirm_batch_del_withdraws' => admin_t('ui.confirm_batch_del_withdraws'),
        'confirm_pay_withdraw' => admin_t('ui.confirm_pay_withdraw', ['name' => '__NAME__', 'yuan' => '__YUAN__', 'fen' => '__FEN__']),
        'confirm_reject_withdraw' => admin_t('ui.confirm_reject_withdraw'),
        'confirm_del_withdraw' => admin_t('ui.confirm_del_withdraw'),
        'member_hash' => admin_t('ui.member_hash', ['id' => '__ID__']),
        'go_members' => admin_t('ui.go_members'),
    ];
@endphp

@section('plain')
<div class="card card-panel withdraw-index list-desk">
    <div class="card-header">
        <span>{{ admin_t('ui.withdraws') }}@if($q('pending') > 0) <em>{{ admin_t('ui.header_pending_n', ['n' => $q('pending')]) }}</em>@endif</span>
        <div>
            <a class="btn btn-muted btn-sm" href="/admin/video/orders">{{ admin_t('ui.orders') }}</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/plogs">{{ admin_t('ui.plogs') }}</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/members">{{ admin_t('ui.members') }}</a>
        </div>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="withdraw-search" onsubmit="return false;">
            <input type="hidden" name="status">
            <input type="hidden" name="today">
            <input type="search" name="q" placeholder="{{ admin_t('ui.ph_withdraw') }}" autocomplete="off" aria-label="{{ admin_t('ui.withdraws') }}">
            <button type="button" class="btn btn-sm" id="withdraw-search-btn">{{ admin_t('ui.search') }}</button>
            <button type="reset" class="btn btn-muted btn-sm" id="withdraw-reset-btn">{{ admin_t('ui.reset') }}</button>
        </form>
        <div class="queue-chips" id="withdraw-queues">
            <button type="button" class="chip" data-queue="">{{ admin_t('ui.all') }}@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="0">{{ admin_t('ui.pending_review') }}@if($q('pending') > 0)<em>{{ $q('pending') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="1">{{ admin_t('ui.withdraw_paid') }}@if($q('paid') > 0)<em>{{ $q('paid') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="2">{{ admin_t('ui.withdraw_rejected') }}@if($q('rejected') > 0)<em>{{ $q('rejected') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="today" data-value="1">{{ admin_t('ui.today') }}@if($q('today') > 0)<em>{{ $q('today') }}</em>@endif</button>
        </div>
        <p class="muted recycle-lead">{{ admin_t('ui.withdraws_lead') }}</p>
        <div class="batch-bar" id="withdraw-batch" hidden>
            <strong id="withdraw-batch-count">{{ admin_t('ui.selected_rows', ['n' => 0]) }}</strong>
            <button type="button" class="btn btn-sm" id="withdraw-batch-pay">{{ admin_t('ui.mark_paid') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="withdraw-batch-reject">{{ admin_t('ui.withdraw_rejected') }}</button>
            <button type="button" class="btn btn-danger btn-sm" id="withdraw-batch-del">{{ admin_t('ui.delete') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="withdraw-batch-clear">{{ admin_t('ui.clear_selection') }}</button>
        </div>
        <div id="withdraw-table"></div>
    </div>
</div>
<template id="withdraw-remark-tpl">
    <form class="admin-form">
        <input type="hidden" name="id">
        <label>{{ admin_t('ui.label_remark') }}</label>
        <input type="text" name="remark" maxlength="255" placeholder="{{ admin_t('ui.ph_optional') }}" autocomplete="off">
        <p class="muted field-hint">{{ admin_t('ui.hint_withdraw_remark') }}</p>
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($withdrawJsLang, JSON_UNESCAPED_UNICODE);
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
    function memberLabel(d) {
        if (d.member_name) return d.member_name;
        return String(L.member_hash || '').replace('__ID__', String(d.member_id || 0));
    }
    function applyHtml(d) {
        var who = U.escape(memberLabel(d));
        var st = parseInt(d.status, 10);
        var badge = st === 1 ? '<span class="badge badge-ok">' + L.withdraw_paid + '</span>' : (st === 2 ? '<span class="badge badge-off">' + L.withdraw_rejected + '</span>' : '<span class="badge badge-warn">' + L.pending_review + '</span>');
        var yuanVal = d.amount_yuan || '0.00';
        var fenVal = d.amount == null ? 0 : d.amount;
        var yuan = U.escape(String(L.amount_yuan || '').replace('__YUAN__', yuanVal));
        var fen = U.escape(String(L.amount_fen || '').replace('__FEN__', String(fenVal)));
        var meta = [];
        if (d.account) meta.push(U.escape(d.account));
        if (d.remark) meta.push(U.escape(d.remark));
        if (d.created_at_text) meta.push(U.escape(d.created_at_text));
        if (d.member_points != null && d.member_points !== '') {
            meta.push(U.escape(String(L.points_now || '').replace('__N__', String(d.member_points))));
        }
        return '<div class="entry-row-title-line"><span class="entry-row-title">' + who + '</span> <strong>' + yuan + '</strong> <span class="muted">' + fen + '</span> ' + badge + '</div>'
            + (meta.length ? '<div class="entry-row-meta">' + meta.join(' · ') + '</div>' : '');
    }
    function statusHtml(d) {
        var st = parseInt(d.status, 10);
        if (st === 1) return U.status(true, L.withdraw_paid);
        if (st === 2) return U.status(false, L.withdraw_rejected);
        return '<span class="status status-warn">' + L.pending_review + '</span>';
    }

    var table = U.table({
        el: '#withdraw-table',
        queueKeys: QUEUE_KEYS,
        url: '/admin/video/withdraws/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>' + L.no_match_withdraws + '</p><p><button type="button" class="btn btn-muted btn-sm" id="withdraw-empty-reset">' + L.clear_filter + '</button></p></div>';
            }
            return '<div class="list-empty"><p>' + L.empty_withdraws + '</p><p class="muted">' + L.empty_withdraws_hint + '</p><p><a class="btn btn-muted btn-sm" href="/admin/video/members">' + L.go_members + '</a></p></div>';
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
            batchCount.textContent = String(L.selected_rows || '').replace('__N__', String(ids.length));
        },
        cols: [
            {check: true, width: 36},
            {title: L.col_withdraw, html: applyHtml},
            {title: L.status, width: 88, html: statusHtml},
            {title: L.actions, cls: 'actions', html: function (d) {
                var st = parseInt(d.status, 10);
                var html = '';
                if (st === 0) {
                    html += '<a href="#" class="btn-link js-pay">' + L.withdraw_paid + '</a><a href="#" class="btn-link js-reject">' + L.withdraw_rejected + '</a>';
                }
                html += '<a href="#" class="btn-link js-remark">' + L.label_remark + '</a>';
                if (parseInt(d.member_id, 10) > 0) {
                    html += '<a class="btn-link" href="/admin/video/members">' + L.members + '</a>';
                }
                html += '<a href="#" class="btn-link js-del">' + L.delete + '</a>';
                return html;
            }}
        ]
    });
    markChips();

    function selectedIds() { return table.selectedIds(); }
    function batch(action, value, confirmText) {
        var ids = selectedIds();
        if (!ids.length) { U.toast(L.please_select_withdraws, 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/withdraws/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.op_fail, 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || L.op_ok, 'ok');
        });
    }
    function setStatus(row, status, confirmText) {
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/withdraws/save', {id: row.id, status: status}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
            table.refresh();
            U.toast(status === 1 ? L.withdraw_paid_ok : L.withdraw_rejected_ok, 'ok');
        });
    }
    function openRemark(row) {
        U.dialog({
            title: L.label_remark,
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
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return false; }
                    U.toast(L.saved, 'ok');
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
        batch('status', 1, L.confirm_batch_pay_withdraws);
    });
    U.on('#withdraw-batch-reject', 'click', function () {
        batch('status', 2, L.confirm_batch_reject_withdraws);
    });
    U.on('#withdraw-batch-del', 'click', function () { batch('delete', '', L.confirm_batch_del_withdraws); });
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
            var payMsg = String(L.confirm_pay_withdraw || '')
                .replace('__NAME__', memberLabel(row))
                .replace('__YUAN__', row.amount_yuan || '0.00')
                .replace('__FEN__', String(row.amount == null ? 0 : row.amount));
            setStatus(row, 1, payMsg);
        }
        if (a.classList.contains('js-reject')) setStatus(row, 2, L.confirm_reject_withdraw);
        if (a.classList.contains('js-remark')) openRemark(row);
        if (a.classList.contains('js-del')) {
            if (!U.confirm(L.confirm_del_withdraw)) return;
            U.post('/admin/video/withdraws/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
                table.refresh();
                U.toast(L.deleted, 'ok');
            });
        }
    });
})();
</script>
@endpush
