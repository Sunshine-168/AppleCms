@extends('admin.layouts.inner')
@section('title', $title ?? admin_t('nav.coupons'))

@php
    $desk = in_array((string) ($desk ?? ''), ['campaigns', 'received'], true) ? (string) $desk : 'campaigns';
    $groups = is_array($groups ?? null) ? $groups : [];
@endphp

@section('plain')
<div class="card card-panel coupon-board desk-board" id="coupon-board">
    <div class="card-header">
        <span>优惠券 <em id="coupon-count"></em></span>
        <div>
            @if($desk === 'campaigns')
                <button type="button" class="btn btn-sm" id="coupon-add-btn">{{ admin_t('ui.add') }}</button>
            @endif
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">对照苹果：满减或折扣、门槛、通用/充值/会员、发放数、每人 1 张。本站现金通道只有充值，积分到账按套餐原价算；积分商城不抵。会员场景要现金买会员才用得上，现在没有这条通道。全额抵成 0 元会拒绝。</p>
        <div class="queue-chips">
            <a class="chip{{ $desk === 'campaigns' ? ' active' : '' }}" href="/admin/video/coupons">券</a>
            <a class="chip{{ $desk === 'received' ? ' active' : '' }}" href="/admin/video/coupons?desk=received">领取</a>
        </div>
        <form class="filter-bar" id="coupon-search" onsubmit="return false;">
            <input type="hidden" name="desk" value="{{ $desk }}">
            <input type="search" name="q" placeholder="{{ $desk === 'campaigns' ? '搜名称' : '搜会员编号、券编号、订单号' }}" autocomplete="off">
            @if($desk === 'campaigns')
                <select name="scene" aria-label="场景">
                    <option value="">{{ admin_t('ui.all_scenes') }}</option>
                    <option value="all">通用</option>
                    <option value="recharge">充值</option>
                    <option value="vip">会员</option>
                </select>
                <select name="type" aria-label="类型">
                    <option value="">{{ admin_t('ui.all_types') }}</option>
                    <option value="amount">满减</option>
                    <option value="discount">折扣</option>
                </select>
                <select name="validity" aria-label="有效期">
                    <option value="">{{ admin_t('ui.all_validity') }}</option>
                    <option value="active">未过期</option>
                    <option value="expired">已过期</option>
                </select>
                <select name="status" aria-label="状态">
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
        <h3>基本</h3>
        <label>优惠券名称</label>
        <input class="entry-title" type="text" name="name" required placeholder="例如：新人立减券" autofocus>
        <div class="admin-dialog-grid">
            <div>
                <label>类型</label>
                <select name="type">
                    <option value="amount">满减</option>
                    <option value="discount">折扣</option>
                </select>
            </div>
            <div>
                <label>面额/折扣</label>
                <input type="text" name="value" value="0.00" required>
            </div>
        </div>
        <p class="muted field-hint">满减填元，折扣填 0 到 100 的百分比。全额抵成 0 元下不了单。</p>
        <label>满减门槛</label>
        <input type="text" name="min_price" value="0.00">
        <p class="muted field-hint">订单金额需达到此门槛才可用。0 表示无门槛。</p>
        <label>适用场景</label>
        <select name="scene">
            <option value="all">通用</option>
            <option value="recharge">充值</option>
            <option value="vip">会员</option>
        </select>
        <p class="muted field-hint">通用和充值可在现金充值里用。会员要现金买会员组才对得上，本站会员组走积分商城。</p>

        <h3>发放</h3>
        <div class="admin-dialog-grid">
            <div>
                <label>发放总数</label>
                <input type="number" name="total" value="10" min="1">
            </div>
            <div>
                <label>每人限额</label>
                <input type="number" name="per_user" value="1" min="1" max="1" readonly>
            </div>
        </div>
        <label>适用会员组</label>
        <div class="check-row">
            @forelse($groups as $group)
                <label class="check-inline"><input type="checkbox" name="group_ids[]" value="{{ $group['id'] }}"> {{ $group['name'] }}</label>
            @empty
                <span class="muted">还没有会员组</span>
            @endforelse
        </div>
        <p class="muted field-hint">不勾表示全部会员组可用。</p>
        <label>适用时长</label>
        <div class="check-row">
            <label class="check-inline"><input type="checkbox" name="longs[]" value="day"> 日</label>
            <label class="check-inline"><input type="checkbox" name="longs[]" value="week"> 周</label>
            <label class="check-inline"><input type="checkbox" name="longs[]" value="month"> 月</label>
            <label class="check-inline"><input type="checkbox" name="longs[]" value="year"> 年</label>
        </div>
        <p class="muted field-hint">时长只在现金买会员时校验。不勾表示不限。</p>

        <h3>有效期</h3>
        <div class="admin-dialog-grid">
            <div>
                <label>开始时间</label>
                <input type="datetime-local" name="start_at">
            </div>
            <div>
                <label>结束时间</label>
                <input type="datetime-local" name="end_at">
            </div>
        </div>
        <p class="muted field-hint">结束留空表示长期有效。</p>
        <label>状态</label>
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
    var desk = @json($desk);
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
        if (filtered) return '<div class="list-empty"><p>没有符合条件的记录</p></div>';
        if (desk === 'campaigns') {
            return '<div class="list-empty"><p>还没有优惠券</p><p><button type="button" class="btn btn-primary btn-sm" id="coupon-empty-add">新增优惠券</button></p></div>';
        }
        return '<div class="list-empty"><p>还没有领取记录</p></div>';
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
            {title: '面额', width: 80, html: function (d) { return U.escape(String(d.value || '')); }},
            {title: '场景', width: 72, html: function (d) { return U.escape(d.scene_label || ''); }},
            {title: '发放', width: 72, html: function (d) { return U.escape(String(d.total == null ? 0 : d.total)); }},
            {title: '已领', width: 72, html: function (d) { return U.escape(String(d.received == null ? 0 : d.received)); }},
            {title: '已用', width: 72, html: function (d) { return U.escape(String(d.used == null ? 0 : d.used)); }},
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
            title: mode === 'edit' ? '编辑优惠券' : '新增优惠券',
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
                if (!data.name) { U.toast('请填写名称', 'err'); return false; }
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
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return false; }
                    U.toast(mode === 'edit' ? '已保存' : '已创建', 'ok');
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
            if (!U.confirm('确认删除？已领取的券不能删。')) return;
            U.post('/admin/video/coupons/delete', {id: row.id, desk: 'campaigns'}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh();
                U.toast('已删除', 'ok');
            });
        }
    });
})();
</script>
@endpush
