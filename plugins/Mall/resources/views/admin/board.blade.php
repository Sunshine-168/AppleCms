@extends('admin.layouts.inner')
@section('title', $title ?? admin_t('nav.mall'))

@php
    $desk = in_array((string) ($desk ?? ''), ['goods', 'orders', 'ship'], true)
        ? (string) $desk
        : 'goods';
    $groups = is_array($groups ?? null) ? $groups : [];
    $hint = (string) ($hint ?? '');
    $mallJsLang = [
        'add_goods' => admin_t('ui.add_goods'),
        'edit_goods' => admin_t('ui.edit_goods'),
        'no_match_rows' => admin_t('ui.no_match_rows'),
        'clear_filter' => admin_t('ui.clear_filter'),
        'empty_goods' => admin_t('ui.empty_goods'),
        'empty_mall_orders' => admin_t('ui.empty_mall_orders'),
        'empty_ship' => admin_t('ui.empty_ship'),
        'please_fill_name' => admin_t('ui.please_fill_name'),
        'fail' => admin_t('ui.fail'),
        'saved' => admin_t('ui.saved'),
        'created' => admin_t('ui.created'),
        'deleted' => admin_t('ui.deleted'),
        'confirm_remove' => admin_t('ui.confirm_remove'),
        'on' => admin_t('ui.on'),
        'off' => admin_t('ui.off'),
        'hot_badge' => admin_t('ui.hot_badge'),
        'mark_shipped' => admin_t('ui.mark_shipped'),
        'delivery_contact' => admin_t('ui.delivery_contact'),
        'ship_note_ph' => admin_t('ui.ship_note_ph'),
        'confirm_ship' => admin_t('ui.confirm_ship'),
        'shipped' => admin_t('ui.shipped'),
        'pool_remain' => admin_t('ui.pool_remain'),
    ];
@endphp

@section('plain')
<div class="card card-panel mall-board desk-board" id="mall-board">
    <div class="card-header">
        <span>{{ admin_t('nav.mall') }} <em id="mall-count"></em></span>
        <div>
            <button type="button" class="btn btn-sm" id="mall-add-btn">{{ admin_t('ui.add') }}</button>
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">{{ admin_t('ui.mall_lead') }}</p>
        <div class="queue-chips" id="mall-desks">
            <a class="chip{{ $desk === 'goods' ? ' active' : '' }}" href="/admin/video/mall_goods">{{ admin_t('nav.mall_goods') }}</a>
            <a class="chip{{ $desk === 'orders' ? ' active' : '' }}" href="/admin/video/mall_goods?desk=orders">{{ admin_t('nav.mall_orders') }}</a>
            <a class="chip{{ $desk === 'ship' ? ' active' : '' }}" href="/admin/video/mall_goods?desk=ship">{{ admin_t('nav.mall_ship') }}</a>
        </div>
        <form class="filter-bar" id="mall-search" onsubmit="return false;">
            <input type="hidden" name="desk" value="{{ $desk }}">
            <input type="search" name="q" placeholder="{{ $desk === 'goods' ? admin_t('ui.ph_search_goods') : admin_t('ui.ph_search_mall_order') }}" autocomplete="off">
            @if($desk === 'goods')
                <select name="type" aria-label="{{ admin_t('ui.col_type') }}">
                    <option value="">{{ admin_t('ui.all_types') }}</option>
                    <option value="vip">{{ admin_t('ui.vip_duration') }}</option>
                    <option value="card">{{ admin_t('ui.point_card') }}</option>
                    <option value="goods">{{ admin_t('ui.physical') }}</option>
                </select>
                <select name="status" aria-label="{{ admin_t('ui.status') }}">
                    <option value="">{{ admin_t('ui.all_status') }}</option>
                    <option value="1">{{ admin_t('ui.on') }}</option>
                    <option value="0">{{ admin_t('ui.off') }}</option>
                </select>
            @elseif($desk === 'orders')
                <select name="goods_type" aria-label="{{ admin_t('ui.col_type') }}">
                    <option value="">{{ admin_t('ui.all_types') }}</option>
                    <option value="vip">{{ admin_t('ui.vip_duration') }}</option>
                    <option value="card">{{ admin_t('ui.point_card') }}</option>
                    <option value="goods">{{ admin_t('ui.physical') }}</option>
                </select>
                <select name="status" aria-label="{{ admin_t('ui.status') }}">
                    <option value="">{{ admin_t('ui.all_status') }}</option>
                    <option value="1">{{ admin_t('ui.to_ship') }}</option>
                    <option value="2">{{ admin_t('ui.completed') }}</option>
                </select>
            @endif
            <button type="button" class="btn btn-sm" id="mall-search-btn">{{ admin_t('ui.search') }}</button>
            <button type="reset" class="btn btn-muted btn-sm" id="mall-reset-btn">{{ admin_t('ui.reset') }}</button>
        </form>
        <div id="mall-table"></div>
    </div>
</div>

<template id="mall-goods-tpl">
    <form class="admin-form">
        <input type="hidden" name="id">
        <h3>{{ admin_t('ui.section_basic') }}</h3>
        <label>{{ admin_t('ui.name') }}</label>
        <input class="entry-title" type="text" name="name" required placeholder="{{ admin_t('ui.ph_mall_name') }}" autofocus>
        <label>{{ admin_t('ui.col_type') }}</label>
        <select name="type" id="mall-type">
            <option value="vip">{{ admin_t('ui.vip_duration') }}</option>
            <option value="card">{{ admin_t('ui.point_card') }}</option>
            <option value="goods">{{ admin_t('ui.physical') }}</option>
        </select>
        <p class="muted field-hint">{{ admin_t('ui.mall_type_hint') }}</p>
        <div class="js-vip-fields" style="display:none">
            <label>{{ admin_t('ui.apply_groups') }}</label>
            <select name="group_id">
                <option value="0">{{ admin_t('ui.pick_one') }}</option>
                @foreach($groups as $group)
                    <option value="{{ $group['id'] }}">{{ $group['name'] }}</option>
                @endforeach
            </select>
            <label>{{ admin_t('ui.vip_days') }}</label>
            <input type="number" name="vip_days" value="30" min="0">
            <p class="muted field-hint">{{ admin_t('ui.vip_days_hint') }}</p>
        </div>
        <div class="js-card-fields" style="display:none">
            <label>{{ admin_t('ui.card_source') }}</label>
            <select name="card_mode">
                <option value="generate">{{ admin_t('ui.gen_card') }}</option>
                <option value="assign">{{ admin_t('ui.assign_card') }}</option>
            </select>
            <label>{{ admin_t('ui.card_points') }}</label>
            <input type="number" name="card_points" value="0" min="0">
            <label>{{ admin_t('ui.grant_mode') }}</label>
            <select name="auto_credit">
                <option value="1">{{ admin_t('ui.credit_now') }}</option>
                <option value="0">{{ admin_t('ui.card_only') }}</option>
            </select>
            <p class="muted field-hint">{{ admin_t('ui.mall_type_hint') }}</p>
        </div>
        <label>{{ admin_t('ui.cover') }}</label>
        <div class="field-inline">
            <input type="text" name="cover" placeholder="{{ admin_t('ui.ph_image_url') }}">
            <button type="button" class="btn btn-sm js-cover-pick">{{ admin_t('ui.upload') }}</button>
        </div>
        <img class="img-preview js-cover-preview" alt="">
        <h3>{{ admin_t('ui.redeem') }}</h3>
        <div class="admin-dialog-grid">
            <div>
                <label>{{ admin_t('ui.points') }}</label>
                <input type="number" name="points" value="0" min="0">
            </div>
            <div>
                <label>{{ admin_t('ui.stock') }}</label>
                <input type="number" name="stock" value="0" min="0">
            </div>
        </div>
        <label>{{ admin_t('ui.hot') }}</label>
        <select name="is_hot">
            <option value="0">{{ admin_t('ui.no') }}</option>
            <option value="1">{{ admin_t('ui.hot_home') }}</option>
        </select>
        <label>{{ admin_t('ui.intro') }}</label>
        <textarea name="hint" rows="3" placeholder="{{ admin_t('ui.ph_goods_hint') }}"></textarea>
        <div class="admin-dialog-grid">
            <div>
                <label>{{ admin_t('ui.sort') }}</label>
                <input type="number" name="sort" value="0">
            </div>
            <div>
                <label>{{ admin_t('ui.status') }}</label>
                <select name="status">
                    <option value="1">{{ admin_t('ui.on') }}</option>
                    <option value="0">{{ admin_t('ui.off') }}</option>
                </select>
            </div>
        </div>
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($mallJsLang, JSON_UNESCAPED_UNICODE);
    var desk = @json($desk);
    var form = document.getElementById('mall-search');
    var countEl = document.getElementById('mall-count');
    var addBtn = document.getElementById('mall-add-btn');
    var module = desk === 'goods' ? 'mall_goods' : 'mall_orders';
    if (addBtn) {
        addBtn.style.display = desk === 'goods' ? '' : 'none';
        addBtn.textContent = L.add_goods;
    }

    function cleanWhere(data) {
        var out = {};
        Object.keys(data || {}).forEach(function (k) {
            if (data[k] !== '' && data[k] != null) out[k] = data[k];
        });
        return out;
    }
    function queryWhere() {
        var data = cleanWhere(U.formData(form));
        data.limit = 20;
        data.desk = desk;
        return data;
    }
    function isFiltered(where) {
        return Object.keys(where || {}).some(function (k) {
            if (k === 'limit' || k === 'desk') return false;
            return where[k] !== '' && where[k] != null;
        });
    }
    function emptyHtml(_parsed, where) {
        if (isFiltered(where)) {
            return '<div class="list-empty"><p>' + L.no_match_rows + '</p><p><button type="button" class="btn btn-muted btn-sm" id="mall-empty-reset">' + L.clear_filter + '</button></p></div>';
        }
        var copy = {
            goods: [L.empty_goods, L.add_goods],
            orders: [L.empty_mall_orders, ''],
            ship: [L.empty_ship, '']
        }[desk] || [L.no_match_rows, L.add_goods];
        if (!copy[1]) {
            return '<div class="list-empty"><p>' + copy[0] + '</p></div>';
        }
        return '<div class="list-empty"><p>' + copy[0] + '</p><p><button type="button" class="btn btn-primary btn-sm" id="mall-empty-add">' + copy[1] + '</button></p></div>';
    }

    var cols = [];
    if (desk === 'goods') {
        cols = [
            {title: AdminUi.t('name'), html: function (d) {
                var hot = String(d.is_hot) === '1' ? '<span class="badge">' + L.hot_badge + '</span> ' : '';
                return hot + '<a class="entry-row-title js-edit" href="#">' + U.escape(d.name || AdminUi.t('not_filled')) + '</a>';
            }},
            {title: AdminUi.t('type'), width: 96, html: function (d) {
                var extra = '';
                if (d.type === 'vip') extra = ' · ' + U.escape(d.vip_days_label || '');
                if (d.type === 'card' && d.card_mode === 'assign') extra = ' · ' + String(L.pool_remain || '').replace(':n', String(d.pool_remain == null ? 0 : d.pool_remain));
                return U.escape(d.type_label || '') + extra;
            }},
            {title: AdminUi.t('points'), width: 72, html: function (d) { return U.escape(String(d.points == null ? 0 : d.points)); }},
            {title: AdminUi.t('stock'), width: 72, html: function (d) {
                var n = Number(d.stock == null ? 0 : d.stock);
                return n < 5 ? '<span class="status status-off">' + n + '</span>' : String(n);
            }},
            {title: AdminUi.t('sales'), width: 64, html: function (d) { return U.escape(String(d.sales == null ? 0 : d.sales)); }},
            {title: AdminUi.t('status'), width: 72, html: function (d) {
                return String(d.status) === '1' ? U.status(true, L.on) : U.status(false, L.off);
            }},
            {title: AdminUi.t('actions'), cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-edit">' + AdminUi.t('edit') + '</a><a href="#" class="btn-link js-del">' + AdminUi.t('delete') + '</a>';
            }}
        ];
    } else {
        cols = [
            {title: AdminUi.t('goods'), html: function (d) { return U.escape(d.goods_name || ('#' + (d.goods_id || ''))); }},
            {title: AdminUi.t('member'), width: 80, html: function (d) { return U.escape(String(d.member_id || '')); }},
            {title: AdminUi.t('type'), width: 96, html: function (d) { return U.escape(d.type_label || ''); }},
            {title: AdminUi.t('points'), width: 72, html: function (d) { return U.escape(String(d.points == null ? 0 : d.points)); }},
            {title: AdminUi.t('status'), width: 88, html: function (d) { return U.escape(d.status_label || ''); }},
            {title: L.delivery_contact, html: function (d) { return U.escape(d.delivery_label || ''); }},
            {title: AdminUi.t('actions'), cls: 'actions', html: function (d) {
                if (String(d.status) === '1') {
                    return '<a href="#" class="btn-link js-ship">' + L.mark_shipped + '</a>';
                }
                return '';
            }}
        ];
    }

    var table = U.table({
        el: '#mall-table',
        url: '/admin/video/' + module + '/list',
        where: queryWhere(),
        emptyHtml: emptyHtml,
        onDraw: function (_wrap, list) {
            countEl.textContent = list.length ? '· ' + list.length : '';
            var add = document.getElementById('mall-empty-add');
            var reset = document.getElementById('mall-empty-reset');
            if (add) add.addEventListener('click', function () { openDialog('add'); });
            if (reset) reset.addEventListener('click', function () { form.reset(); runSearch(); });
        },
        cols: cols
    });

    function runSearch() { table.reload(queryWhere()); }
    function syncTypeFields(body) {
        var sel = body.querySelector('[name=type]');
        var type = sel ? sel.value : 'goods';
        var vip = body.querySelector('.js-vip-fields');
        var card = body.querySelector('.js-card-fields');
        if (vip) vip.style.display = type === 'vip' ? '' : 'none';
        if (card) card.style.display = type === 'card' ? '' : 'none';
    }
    function fill(mode, row) {
        row = row || {};
        return {
            id: mode === 'edit' ? (row.id || '') : '',
            name: row.name || '',
            type: row.type || 'vip',
            group_id: row.group_id == null ? 0 : row.group_id,
            vip_days: row.vip_days == null ? 30 : row.vip_days,
            card_mode: row.card_mode || 'generate',
            card_points: row.card_points == null ? 0 : row.card_points,
            auto_credit: row.auto_credit == null ? '1' : String(row.auto_credit),
            cover: row.cover || '',
            points: row.points == null ? 0 : row.points,
            stock: row.stock == null ? 0 : row.stock,
            is_hot: row.is_hot == null ? '0' : String(row.is_hot),
            hint: row.hint || '',
            sort: row.sort == null ? 0 : row.sort,
            status: row.status == null ? '1' : String(row.status)
        };
    }
    function openDialog(mode, row) {
        row = row || {};
        U.dialog({
            title: mode === 'edit' ? L.edit_goods : L.add_goods,
            wide: true,
            content: document.getElementById('mall-goods-tpl').innerHTML,
            onOpen: function (body) {
                U.fillForm(body.querySelector('form'), fill(mode, row));
                U.bindImageField(body, {
                    input: '[name=cover]',
                    btn: '.js-cover-pick',
                    preview: '.js-cover-preview'
                });
                syncTypeFields(body);
                var sel = body.querySelector('[name=type]');
                if (sel) sel.addEventListener('change', function () { syncTypeFields(body); });
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (!data.name) { U.toast(L.please_fill_name, 'err'); return false; }
                if (mode !== 'edit') delete data.id; else data.id = row.id;
                return U.post('/admin/video/' + module + '/save', data).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return false; }
                    U.toast(mode === 'edit' ? L.saved : L.created, 'ok');
                    table.refresh();
                });
            }
        });
    }

    U.on('#mall-search-btn', 'click', runSearch);
    U.on('#mall-reset-btn', 'click', function () { setTimeout(runSearch, 0); });
    U.on('#mall-add-btn', 'click', function () { openDialog('add'); });
    U.on('#mall-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-edit')) openDialog('edit', row);
        if (a.classList.contains('js-del')) {
            if (!U.confirm(L.confirm_remove)) return;
            U.post('/admin/video/' + module + '/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
                table.refresh();
                U.toast(L.deleted, 'ok');
            });
        }
        if (a.classList.contains('js-ship')) {
            var note = U.prompt(L.ship_note_ph, row.remark || '');
            if (note === null) return;
            if (!U.confirm(L.confirm_ship)) return;
            U.post('/admin/video/mall_orders/save', {id: row.id, status: 2, remark: note}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
                table.refresh();
                U.toast(L.shipped, 'ok');
            });
        }
    });
})();
</script>
@endpush
