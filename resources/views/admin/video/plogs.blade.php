@extends('admin.layouts.inner')
@section('title', $title ?? admin_t('ui.plogs'))

@php
    $queues = $queues ?? ['all' => 0, 'in' => 0, 'out' => 0, 'play' => 0, 'order' => 0, 'card' => 0, 'admin' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
    $plogJsLang = [
        'actions' => admin_t('ui.actions'),
        'delete' => admin_t('ui.delete'),
        'fail' => admin_t('ui.fail'),
        'deleted' => admin_t('ui.deleted'),
        'clear_filter' => admin_t('ui.clear_filter'),
        'selected_rows' => admin_t('ui.selected_rows', ['n' => '__N__']),
        'op_fail' => admin_t('manga.op_fail'),
        'op_ok' => admin_t('manga.op_ok'),
        'members' => admin_t('ui.members'),
        'adjust_points' => admin_t('ui.adjust_points'),
        'col_member' => admin_t('ui.col_member'),
        'col_delta' => admin_t('ui.col_delta'),
        'col_balance' => admin_t('ui.col_balance'),
        'col_type' => admin_t('ui.col_type'),
        'col_time' => admin_t('ui.col_time'),
        'today_at' => admin_t('ui.today_at', ['time' => '__TIME__']),
        'yesterday_at' => admin_t('ui.yesterday_at', ['time' => '__TIME__']),
        'member_hash' => admin_t('ui.member_hash', ['id' => '__ID__']),
        'id_n' => admin_t('ui.id_n', ['id' => '__ID__']),
        'empty_plogs' => admin_t('ui.empty_plogs'),
        'empty_plogs_hint' => admin_t('ui.empty_plogs_hint'),
        'no_match_plogs' => admin_t('ui.no_match_plogs'),
        'please_fill_member_id' => admin_t('ui.please_fill_member_id'),
        'please_points_change_nonzero' => admin_t('ui.please_points_change_nonzero'),
        'please_select_plogs' => admin_t('ui.please_select_plogs'),
        'btn_credit' => admin_t('ui.btn_credit'),
        'order_credited' => admin_t('ui.order_credited'),
        'confirm_batch_del_plogs' => admin_t('ui.confirm_batch_del_plogs'),
        'confirm_del_plog' => admin_t('ui.confirm_del_plog'),
    ];
@endphp

@section('plain')
<div class="card card-panel plog-index">
    <div class="card-header">
        <span>{{ admin_t('ui.plogs') }} <em id="plog-count"></em></span>
        <div>
            <a class="btn btn-muted btn-sm" href="/admin/video/cards">{{ admin_t('ui.cards') }}</a>
            <button type="button" class="btn btn-sm" id="plog-add-btn">{{ admin_t('ui.adjust_points') }}</button>
        </div>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="plog-search" onsubmit="return false;">
            <input type="hidden" name="dir">
            <input type="hidden" name="type">
            <input type="hidden" name="member_id">
            <input type="search" name="q" placeholder="{{ admin_t('ui.ph_plog') }}" autocomplete="off" aria-label="{{ admin_t('ui.plogs') }}">
            <button type="button" class="btn btn-sm" id="plog-search-btn">{{ admin_t('ui.search') }}</button>
            <button type="reset" class="btn btn-muted btn-sm" id="plog-reset-btn">{{ admin_t('ui.reset') }}</button>
        </form>
        <div class="queue-chips" id="plog-queues">
            <button type="button" class="chip" data-queue="">{{ admin_t('ui.all') }}@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="dir" data-value="in">{{ admin_t('ui.chip_income') }}@if($q('in') > 0)<em>{{ $q('in') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="dir" data-value="out">{{ admin_t('ui.chip_expense') }}@if($q('out') > 0)<em>{{ $q('out') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="type" data-value="play">{{ admin_t('ui.chip_play') }}@if($q('play') > 0)<em>{{ $q('play') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="type" data-value="order">{{ admin_t('ui.orders') }}@if($q('order') > 0)<em>{{ $q('order') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="type" data-value="card">{{ admin_t('ui.cards') }}@if($q('card') > 0)<em>{{ $q('card') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="type" data-value="admin">{{ admin_t('ui.chip_admin') }}@if($q('admin') > 0)<em>{{ $q('admin') }}</em>@endif</button>
        </div>
        <p class="muted recycle-lead">{{ admin_t('ui.plogs_lead') }}</p>
        <div class="batch-bar" id="plog-batch" hidden>
            <strong id="plog-batch-count">{{ admin_t('ui.selected_rows', ['n' => 0]) }}</strong>
            <button type="button" class="btn btn-danger btn-sm" id="plog-batch-del">{{ admin_t('ui.delete_records') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="plog-batch-clear">{{ admin_t('ui.clear_selection') }}</button>
        </div>
        <div id="plog-table"></div>
    </div>
</div>
<template id="plog-dialog-tpl">
    <form class="admin-form">
        <label>{{ admin_t('ui.label_member_id') }}</label>
        <input type="number" name="member_id" placeholder="{{ admin_t('ui.ph_member_id') }}" min="1">
        <p class="muted field-hint">{{ admin_t('ui.hint_plog_member') }}</p>
        <label>{{ admin_t('ui.label_points_change') }}</label>
        <input type="number" name="points" value="0">
        <p class="muted field-hint">{{ admin_t('ui.hint_points_change') }}</p>
        <label>{{ admin_t('ui.label_remark') }}</label>
        <input type="text" name="remark" placeholder="{{ admin_t('ui.ph_plog_remark') }}" maxlength="250">
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($plogJsLang, JSON_UNESCAPED_UNICODE);
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
        if (d.toDateString() === now.toDateString()) return String(L.today_at || '').replace('__TIME__', hm);
        var y = new Date(now);
        y.setDate(now.getDate() - 1);
        if (d.toDateString() === y.toDateString()) return String(L.yesterday_at || '').replace('__TIME__', hm);
        if (d.getFullYear() === now.getFullYear()) return pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + ' ' + hm;
        return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
    }
    function titleHtml(d) {
        var who = d.member_name ? U.escape(d.member_name) : String(L.member_hash || '').replace('__ID__', U.escape(d.member_id || 0));
        var meta = d.member_email ? U.escape(d.member_email) : String(L.id_n || '').replace('__ID__', U.escape(d.member_id || 0));
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
                return '<div class="list-empty"><p>' + L.no_match_plogs + '</p><p><button type="button" class="btn btn-muted btn-sm" id="plog-empty-reset">' + L.clear_filter + '</button></p></div>';
            }
            return '<div class="list-empty"><p>' + L.empty_plogs + '</p><p class="muted">' + L.empty_plogs_hint + '</p><p><button type="button" class="btn btn-primary btn-sm" id="plog-empty-add">' + L.adjust_points + '</button></p></div>';
        },
        onDraw: function (_wrap, list) {
            var add = document.getElementById('plog-empty-add');
            var reset = document.getElementById('plog-empty-reset');
            if (add) add.addEventListener('click', openAdjust);
            if (reset) reset.addEventListener('click', function () { form.reset(); runSearch(); });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = String(L.selected_rows || '').replace('__N__', ids.length);
        },
        cols: [
            {check: true, width: 36},
            {title: L.col_member, html: titleHtml},
            {title: L.col_delta, width: 88, html: deltaHtml},
            {title: L.col_balance, width: 72, html: function (d) { return U.escape(String(d.balance == null ? '' : d.balance)); }},
            {title: L.col_type, width: 72, html: function (d) { return U.escape(d.type_label || ''); }},
            {title: L.col_time, width: 120, html: function (d) { return fmtTime(d.created_at); }},
            {title: L.actions, cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-del">' + L.delete + '</a>';
            }}
        ]
    });
    markChips();

    function openAdjust() {
        U.dialog({
            title: L.adjust_points,
            content: document.getElementById('plog-dialog-tpl').innerHTML,
            okText: L.btn_credit,
            onOpen: function (body) {
                U.fillForm(body.querySelector('form'), {
                    member_id: form.member_id.value || '',
                    points: 0,
                    remark: ''
                });
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (!data.member_id || parseInt(data.member_id, 10) < 1) { U.toast(L.please_fill_member_id, 'err'); return false; }
                if (!data.points || parseInt(data.points, 10) === 0) { U.toast(L.please_points_change_nonzero, 'err'); return false; }
                return U.post('/admin/video/plogs/save', data).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return false; }
                    U.toast(L.order_credited, 'ok');
                    table.refresh();
                });
            }
        });
    }

    function selectedIds() { return table.selectedIds(); }
    function batchDel() {
        var ids = selectedIds();
        if (!ids.length) { U.toast(L.please_select_plogs, 'err'); return; }
        if (!U.confirm(L.confirm_batch_del_plogs)) return;
        U.post('/admin/video/plogs/batch', {ids: ids.join(','), action: 'delete'}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.op_fail, 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || L.op_ok, 'ok');
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
            if (!U.confirm(L.confirm_del_plog)) return;
            U.post('/admin/video/plogs/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
                table.refresh();
                U.toast(L.deleted, 'ok');
            });
        }
    });
})();
</script>
@endpush
