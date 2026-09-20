@extends('admin.layouts.inner')
@section('title', $title ?? admin_t('nav.coupons'))

@php
    $desk = in_array((string) ($desk ?? ''), ['campaigns', 'received'], true) ? (string) $desk : 'campaigns';
    $groups = is_array($groups ?? null) ? $groups : [];
    $couponJsLang = [
        'no_match_rows' => admin_t('ui.no_match_rows'),
        'empty_coupons' => admin_t('ui.empty_coupons'),
        'add_coupon' => admin_t('ui.add_coupon'),
        'edit_coupon' => admin_t('ui.edit_coupon'),
        'empty_claims' => admin_t('ui.empty_claims'),
        'please_fill_name' => admin_t('ui.please_fill_name'),
        'fail' => admin_t('ui.fail'),
        'saved' => admin_t('ui.saved'),
        'created' => admin_t('ui.created'),
        'deleted' => admin_t('ui.deleted'),
        'confirm_del_coupon' => admin_t('ui.confirm_del_coupon'),
    ];
@endphp

@section('plain')
<div class="card card-panel coupon-board desk-board" id="coupon-board">
    <div class="card-header">
        <span>{{ admin_t('nav.coupons') }} <em id="coupon-count"></em></span>
        <div>
            @if($desk === 'campaigns')
                <button type="button" class="btn btn-sm" id="coupon-add-btn">{{ admin_t('ui.add') }}</button>
            @endif
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">{{ admin_t('ui.coupon_lead') }}</p>
        <div class="queue-chips">
            <a class="chip{{ $desk === 'campaigns' ? ' active' : '' }}" href="/admin/video/coupons">{{ admin_t('ui.coupon') }}</a>
            <a class="chip{{ $desk === 'received' ? ' active' : '' }}" href="/admin/video/coupons?desk=received">{{ admin_t('ui.received') }}</a>
        </div>
        <form class="filter-bar" id="coupon-search" onsubmit="return false;">
            <input type="hidden" name="desk" value="{{ $desk }}">
            <input type="search" name="q" placeholder="{{ $desk === 'campaigns' ? admin_t('ui.ph_search_coupon') : admin_t('ui.ph_search_claim') }}" autocomplete="off">
            @if($desk === 'campaigns')
                <select name="scene" aria-label="{{ admin_t('ui.scene') }}">
                    <option value="">{{ admin_t('ui.all_scenes') }}</option>
                    <option value="all">{{ admin_t('ui.scene_all') }}</option>
                    <option value="recharge">{{ admin_t('ui.scene_recharge') }}</option>
                    <option value="vip">{{ admin_t('ui.member') }}</option>
                </select>
                <select name="type" aria-label="{{ admin_t('ui.col_type') }}">
                    <option value="">{{ admin_t('ui.all_types') }}</option>
                    <option value="amount">{{ admin_t('ui.coupon_amount') }}</option>
                    <option value="discount">{{ admin_t('ui.coupon_discount') }}</option>
                </select>
                <select name="validity" aria-label="{{ admin_t('ui.validity') }}">
                    <option value="">{{ admin_t('ui.all_validity') }}</option>
                    <option value="active">{{ admin_t('ui.not_expired') }}</option>
                    <option value="expired">{{ admin_t('ui.expired') }}</option>
                </select>
                <select name="status" aria-label="{{ admin_t('ui.status') }}">
                    <option value="">{{ admin_t('ui.all_status') }}</option>
                    <option value="1">{{ admin_t('ui.enabled') }}</option>
                    <option value="0">{{ admin_t('ui.disabled') }}</option>
                </select>
            @endif
            <button type="button" class="btn btn-sm" id="coupon-search-btn">{{ admin_t('ui.search') }}</button>
            <button type="reset" class="btn btn-muted btn-sm" id="coupon-reset-btn">{{ admin_t('ui.reset') }}</button>
        </form>
        <div id="coupon-table" class="desk-table"></div>
    </div>
</div>
<template id="coupon-form-tpl">
    <form class="admin-form">
        <input type="hidden" name="id">
        <input type="hidden" name="desk" value="campaigns">
        <h3>{{ admin_t('ui.section_basic') }}</h3>
        <label>{{ admin_t('ui.coupon_name') }}</label>
        <input class="entry-title" type="text" name="name" required placeholder="{{ admin_t('ui.ph_newcomer_coupon') }}" autofocus>
        <div class="admin-dialog-grid">
            <div>
                <label>{{ admin_t('ui.col_type') }}</label>
                <select name="type">
                    <option value="amount">{{ admin_t('ui.coupon_amount') }}</option>
                    <option value="discount">{{ admin_t('ui.coupon_discount') }}</option>
                </select>
            </div>
            <div>
                <label>{{ admin_t('ui.coupon_value') }}</label>
                <input type="text" name="value" value="0.00" required>
            </div>
        </div>
        <p class="muted field-hint">{{ admin_t('ui.coupon_value_hint') }}</p>
        <label>{{ admin_t('ui.min_spend') }}</label>
        <input type="text" name="min_price" value="0.00">
        <p class="muted field-hint">{{ admin_t('ui.min_spend_hint') }}</p>
        <label>{{ admin_t('ui.apply_scene') }}</label>
        <select name="scene">
            <option value="all">{{ admin_t('ui.scene_all') }}</option>
            <option value="recharge">{{ admin_t('ui.scene_recharge') }}</option>
            <option value="vip">{{ admin_t('ui.member') }}</option>
        </select>
        <p class="muted field-hint">{{ admin_t('ui.coupon_scene_hint') }}</p>

        <h3>{{ admin_t('ui.issue') }}</h3>
        <div class="admin-dialog-grid">
            <div>
                <label>{{ admin_t('ui.issue_total') }}</label>
                <input type="number" name="total" value="10" min="1">
            </div>
            <div>
                <label>{{ admin_t('ui.per_user_cap') }}</label>
                <input type="number" name="per_user" value="1" min="1" max="1" readonly>
            </div>
        </div>
        <label>{{ admin_t('ui.apply_groups') }}</label>
        <div class="check-row">
            @forelse($groups as $group)
                <label class="check-inline"><input type="checkbox" name="group_ids[]" value="{{ $group['id'] }}"> {{ $group['name'] }}</label>
            @empty
                <span class="muted">{{ admin_t('ui.no_groups_yet') }}</span>
            @endforelse
        </div>
        <p class="muted field-hint">{{ admin_t('ui.all_groups_hint') }}</p>
        <label>{{ admin_t('ui.apply_duration') }}</label>
        <div class="check-row">
            <label class="check-inline"><input type="checkbox" name="longs[]" value="day"> {{ admin_t('ui.unit_day') }}</label>
            <label class="check-inline"><input type="checkbox" name="longs[]" value="week"> {{ admin_t('ui.unit_week') }}</label>
            <label class="check-inline"><input type="checkbox" name="longs[]" value="month"> {{ admin_t('ui.unit_month') }}</label>
            <label class="check-inline"><input type="checkbox" name="longs[]" value="year"> {{ admin_t('ui.unit_year') }}</label>
        </div>
        <p class="muted field-hint">{{ admin_t('ui.duration_cash_hint') }}</p>

        <h3>{{ admin_t('ui.validity') }}</h3>
        <div class="admin-dialog-grid">
            <div>
                <label>{{ admin_t('ui.start_time') }}</label>
                <input type="datetime-local" name="start_at">
            </div>
            <div>
                <label>{{ admin_t('ui.end_time') }}</label>
                <input type="datetime-local" name="end_at">
            </div>
        </div>
        <p class="muted field-hint">{{ admin_t('ui.long_valid_hint') }}</p>
        <label>{{ admin_t('ui.status') }}</label>
        <select name="status">
            <option value="1">{{ admin_t('ui.enabled') }}</option>
            <option value="0">{{ admin_t('ui.disabled') }}</option>
        </select>
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($couponJsLang, JSON_UNESCAPED_UNICODE);
    var desk = @json($desk, JSON_UNESCAPED_UNICODE);
    var form = document.getElementById('coupon-search');
    var countEl = document.getElementById('coupon-count');
    function queryWhere() {
        var data = U.formData(form);
        var out = {desk: desk, limit: 20};
        Object.keys(data || {}).forEach(function (k) {
            if (data[k] !== '' && data[k] != null) out[k] = data[k];
        });
        out.desk = desk;
        return out;
    }
    function emptyHtml(_p, where) {
        var filtered = Object.keys(where || {}).some(function (k) {
            return k !== 'limit' && k !== 'desk' && where[k] !== '' && where[k] != null;
        });
        if (filtered) return '<div class="list-empty"><p>' + L.no_match_rows + '</p></div>';
        if (desk === 'campaigns') {
            return '<div class="list-empty"><p>' + L.empty_coupons + '</p><p><button type="button" class="btn btn-primary btn-sm" id="coupon-empty-add">' + L.add_coupon + '</button></p></div>';
        }
        return '<div class="list-empty"><p>' + L.empty_claims + '</p></div>';
    }
    function toLocal(ts) {
        ts = parseInt(ts, 10) || 0;
        if (ts < 1) return '';
        var d = new Date(ts * 1000);
        var p = function (n) { return (n < 10 ? '0' : '') + n; };
        return d.getFullYear() + '-' + p(d.getMonth() + 1) + '-' + p(d.getDate()) + 'T' + p(d.getHours()) + ':' + p(d.getMinutes());
    }
    function fromLocal(s) {
        s = String(s || '');
        if (!s) return 0;
        var t = Date.parse(s);
        return isNaN(t) ? 0 : Math.floor(t / 1000);
    }
    var cols = [];
    if (desk === 'received') {
        cols = [
            {title: AdminUi.t('coupon'), html: function (d) { return U.escape(d.coupon_name || ('#' + (d.coupon_id || ''))); }},
            {title: AdminUi.t('member'), width: 140, html: function (d) { return U.escape((d.member_name || '') + (d.member_id ? ' #' + d.member_id : '')); }},
            {title: AdminUi.t('status'), width: 72, html: function (d) { return U.escape(d.status_label || ''); }},
            {title: AdminUi.t('order'), html: function (d) { return U.escape(d.order_no || ''); }},
            {title: AdminUi.t('received'), width: 120, html: function (d) { return U.escape(d.received_at || ''); }},
            {title: AdminUi.t('used'), width: 120, html: function (d) { return U.escape(d.used_at || ''); }}
        ];
    } else {
        cols = [
            {title: AdminUi.t('name'), html: function (d) { return '<a class="js-edit" href="#">' + U.escape(d.name || AdminUi.t('not_filled')) + '</a>'; }},
            {title: AdminUi.t('type'), width: 72, html: function (d) { return U.escape(d.type_label || ''); }},
            {title: AdminUi.t('face_value'), width: 80, html: function (d) { return U.escape(String(d.value || '')); }},
            {title: AdminUi.t('scene'), width: 72, html: function (d) { return U.escape(d.scene_label || ''); }},
            {title: AdminUi.t('issued'), width: 72, html: function (d) { return U.escape(String(d.total == null ? 0 : d.total)); }},
            {title: AdminUi.t('received'), width: 72, html: function (d) { return U.escape(String(d.received == null ? 0 : d.received)); }},
            {title: AdminUi.t('used'), width: 72, html: function (d) { return U.escape(String(d.used == null ? 0 : d.used)); }},
            {title: AdminUi.t('expire'), width: 120, html: function (d) { return U.escape(d.end_label || ''); }},
            {title: AdminUi.t('status'), width: 72, html: function (d) { return String(d.status) === '1' ? U.status(true, AdminUi.t('enabled')) : U.status(false, AdminUi.t('disabled')); }},
            {title: AdminUi.t('actions'), cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-edit">' + AdminUi.t('edit') + '</a><a href="#" class="btn-link js-del">' + AdminUi.t('delete') + '</a>';
            }}
        ];
    }
    var table = U.table({
        el: '#coupon-table',
        url: '/admin/video/coupons/list',
        where: queryWhere(),
        emptyHtml: emptyHtml,
        onDraw: function (_w, list) {
            countEl.textContent = list.length ? '· ' + list.length : '';
            var add = document.getElementById('coupon-empty-add');
            if (add) add.addEventListener('click', function () { openDialog('add'); });
        },
        cols: cols
    });
    function openDialog(mode, row) {
        row = row || {};
        U.dialog({
            title: mode === 'edit' ? L.edit_coupon : L.add_coupon,
            wide: true,
            content: document.getElementById('coupon-form-tpl').innerHTML,
            onOpen: function (body) {
                U.fillForm(body.querySelector('form'), {
                    id: mode === 'edit' ? (row.id || '') : '',
                    name: row.name || '',
                    type: row.type || 'amount',
                    value: row.value || '0.00',
                    min_price: row.min_price || '0.00',
                    scene: row.scene || 'all',
                    total: row.total == null ? 10 : row.total,
                    per_user: 1,
                    start_at: toLocal(row.start_at),
                    end_at: toLocal(row.end_at),
                    status: row.status == null ? '1' : String(row.status)
                });
                var groups = row.group_ids || [];
                body.querySelectorAll('input[name="group_ids[]"]').forEach(function (el) {
                    el.checked = groups.indexOf(parseInt(el.value, 10)) >= 0 || groups.indexOf(el.value) >= 0;
                });
                var longs = row.longs || [];
                body.querySelectorAll('input[name="longs[]"]').forEach(function (el) {
                    el.checked = longs.indexOf(el.value) >= 0;
                });
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (!data.name) { U.toast(L.please_fill_name, 'err'); return false; }
                data.desk = 'campaigns';
                data.start_at = fromLocal(data.start_at);
                data.end_at = fromLocal(data.end_at);
                var g = [];
                body.querySelectorAll('input[name="group_ids[]"]:checked').forEach(function (el) { g.push(el.value); });
                data.group_ids = g.join(',');
                var l = [];
                body.querySelectorAll('input[name="longs[]"]:checked').forEach(function (el) { l.push(el.value); });
                data.longs = l.join(',');
                data.per_user = 1;
                if (mode !== 'edit') delete data.id; else data.id = row.id;
                return U.post('/admin/video/coupons/save', data).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return false; }
                    U.toast(mode === 'edit' ? L.saved : L.created, 'ok');
                    table.refresh();
                });
            }
        });
    }
    U.on('#coupon-search-btn', 'click', function () { table.reload(queryWhere()); });
    U.on('#coupon-reset-btn', 'click', function () { setTimeout(function () { table.reload(queryWhere()); }, 0); });
    U.on('#coupon-add-btn', 'click', function () { openDialog('add'); });
    U.on('#coupon-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-edit')) openDialog('edit', row);
        if (a.classList.contains('js-del')) {
            if (!U.confirm(L.confirm_del_coupon)) return;
            U.post('/admin/video/coupons/delete', {id: row.id, desk: 'campaigns'}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
                table.refresh();
                U.toast(L.deleted, 'ok');
            });
        }
    });
})();
</script>
@endpush
