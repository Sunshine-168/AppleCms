@extends('admin.layouts.inner')
@section('title', $title ?? admin_t('nav.mall'))

@php
    $desk = in_array((string) ($desk ?? ''), ['goods', 'orders', 'ship'], true)
        ? (string) $desk
        : 'goods';
    $groups = is_array($groups ?? null) ? $groups : [];
    $hint = (string) ($hint ?? '');
@endphp

@section('plain')
<div class="card card-panel mall-board desk-board" id="mall-board">
    <div class="card-header">
        <span>积分商城 <em id="mall-count"></em></span>
        <div>
            <button type="button" class="btn btn-sm" id="mall-add-btn">{{ admin_t('ui.add') }}</button>
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">用会员积分兑换。不接微信支付宝。会员时长（可设天数）、积分卡密（可立即到账）、实物周边（需联系方式）。关掉插件后 /mall 一起消失。</p>
        <div class="queue-chips" id="mall-desks">
            <a class="chip{{ $desk === 'goods' ? ' active' : '' }}" href="/admin/video/mall_goods">商品</a>
            <a class="chip{{ $desk === 'orders' ? ' active' : '' }}" href="/admin/video/mall_goods?desk=orders">订单</a>
            <a class="chip{{ $desk === 'ship' ? ' active' : '' }}" href="/admin/video/mall_goods?desk=ship">待发货</a>
        </div>
        <form class="filter-bar" id="mall-search" onsubmit="return false;">
            <input type="hidden" name="desk" value="{{ $desk }}">
            <input type="search" name="q" placeholder="{{ $desk === 'goods' ? '搜商品名' : '搜商品名、会员编号' }}" autocomplete="off">
            @if($desk === 'goods')
                <select name="type" aria-label="类型">
                    <option value="">全部类型</option>
                    <option value="vip">会员时长</option>
                    <option value="card">积分卡密</option>
                    <option value="goods">实物周边</option>
                </select>
                <select name="status" aria-label="状态">
                    <option value="">全部状态</option>
                    <option value="1">上架</option>
                    <option value="0">下架</option>
                </select>
            @elseif($desk === 'orders')
                <select name="goods_type" aria-label="类型">
                    <option value="">全部类型</option>
                    <option value="vip">会员时长</option>
                    <option value="card">积分卡密</option>
                    <option value="goods">实物周边</option>
                </select>
                <select name="status" aria-label="状态">
                    <option value="">全部状态</option>
                    <option value="1">待发货</option>
                    <option value="2">已完成</option>
                </select>
            @endif
            <button type="button" class="btn btn-sm" id="mall-search-btn">{{ admin_t('ui.search') }}</button>
            <button type="reset" class="btn btn-muted btn-sm" id="mall-reset-btn">{{ admin_t('ui.reset') }}</button>
        </form>
        <div id="mall-table"></div>
    </div>
</div>

<template id="mall-goods-tpl">
    <form>
        <input type="hidden" name="id">
        <h3>基本</h3>
        <label>名称</label>
        <input class="entry-title" type="text" name="name" required placeholder="如 月卡 VIP / 500 积分礼包" autofocus>
        <label>类型</label>
        <select name="type" id="mall-type">
            <option value="vip">会员时长</option>
            <option value="card">积分卡密</option>
            <option value="goods">实物周边</option>
        </select>
        <p class="muted field-hint">影视站常见三类：VIP 天数、积分礼包/卡密、少量周边发货。不接微信支付宝。</p>
        <div class="js-vip-fields" style="display:none">
            <label>会员组</label>
            <select name="group_id">
                <option value="0">请选择</option>
                @foreach($groups as $group)
                    <option value="{{ $group['id'] }}">{{ $group['name'] }}</option>
                @endforeach
            </select>
            <label>时长（天）</label>
            <input type="number" name="vip_days" value="30" min="0">
            <p class="muted field-hint">填 0 表示长期。同组未过期会叠加天数；过期后从今天起算。</p>
        </div>
        <div class="js-card-fields" style="display:none">
            <label>卡密来源</label>
            <select name="card_mode">
                <option value="generate">生成新卡密</option>
                <option value="assign">从现有未用卡领取</option>
            </select>
            <label>卡密积分</label>
            <input type="number" name="card_points" value="0" min="0">
            <label>发放方式</label>
            <select name="auto_credit">
                <option value="1">立即到账</option>
                <option value="0">只发卡密（可转赠）</option>
            </select>
            <p class="muted field-hint">生成时用卡密积分；填 0 则用商品积分。领取模式看卡池剩余。立即到账会核销卡密并加积分。</p>
        </div>
        <label>封面</label>
        <div class="field-inline">
            <input type="text" name="cover" placeholder="图片地址，可空">
            <button type="button" class="btn btn-sm js-cover-pick">上传</button>
        </div>
        <img class="img-preview js-cover-preview" alt="">
        <h3>兑换</h3>
        <div class="admin-dialog-grid">
            <div>
                <label>积分</label>
                <input type="number" name="points" value="0" min="0">
            </div>
            <div>
                <label>库存</label>
                <input type="number" name="stock" value="0" min="0">
            </div>
        </div>
        <label>热门</label>
        <select name="is_hot">
            <option value="0">否</option>
            <option value="1">是（首页「大家都在换」）</option>
        </select>
        <label>说明</label>
        <textarea name="hint" rows="3" placeholder="前台商品说明，可空"></textarea>
        <div class="admin-dialog-grid">
            <div>
                <label>排序</label>
                <input type="number" name="sort" value="0">
            </div>
            <div>
                <label>状态</label>
                <select name="status">
                    <option value="1">上架</option>
                    <option value="0">下架</option>
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
    var desk = @json($desk);
    var form = document.getElementById('mall-search');
    var countEl = document.getElementById('mall-count');
    var addBtn = document.getElementById('mall-add-btn');
    var module = desk === 'goods' ? 'mall_goods' : 'mall_orders';
    if (addBtn) {
        addBtn.style.display = desk === 'goods' ? '' : 'none';
        addBtn.textContent = '新增商品';
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
            return '<div class="list-empty"><p>没有符合条件的记录</p><p><button type="button" class="btn btn-muted btn-sm" id="mall-empty-reset">清除筛选</button></p></div>';
        }
        var copy = {
            goods: ['还没有商品', '新增商品'],
            orders: ['还没有兑换订单', ''],
            ship: ['没有待发货的实物单', '']
        }[desk] || ['还没有记录', '新增'];
        if (!copy[1]) {
            return '<div class="list-empty"><p>' + copy[0] + '</p></div>';
        }
        return '<div class="list-empty"><p>' + copy[0] + '</p><p><button type="button" class="btn btn-primary btn-sm" id="mall-empty-add">' + copy[1] + '</button></p></div>';
    }

    var cols = [];
    if (desk === 'goods') {
        cols = [
            {title: '名称', html: function (d) {
                var hot = String(d.is_hot) === '1' ? '<span class="badge">热</span> ' : '';
                return hot + '<a class="entry-row-title js-edit" href="#">' + U.escape(d.name || '未填写') + '</a>';
            }},
            {title: '类型', width: 96, html: function (d) {
                var extra = '';
                if (d.type === 'vip') extra = ' · ' + U.escape(d.vip_days_label || '');
                if (d.type === 'card' && d.card_mode === 'assign') extra = ' · 池' + (d.pool_remain == null ? 0 : d.pool_remain);
                return U.escape(d.type_label || '') + extra;
            }},
            {title: '积分', width: 72, html: function (d) { return U.escape(String(d.points == null ? 0 : d.points)); }},
            {title: '库存', width: 72, html: function (d) {
                var n = Number(d.stock == null ? 0 : d.stock);
                return n < 5 ? '<span class="status status-off">' + n + '</span>' : String(n);
            }},
            {title: '销量', width: 64, html: function (d) { return U.escape(String(d.sales == null ? 0 : d.sales)); }},
            {title: '状态', width: 72, html: function (d) {
                return String(d.status) === '1' ? U.status(true, '上架') : U.status(false, '下架');
            }},
            {title: '操作', cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-edit">编辑</a><a href="#" class="btn-link js-del">删除</a>';
            }}
        ];
    } else {
        cols = [
            {title: '商品', html: function (d) { return U.escape(d.goods_name || ('#' + (d.goods_id || ''))); }},
            {title: '会员', width: 80, html: function (d) { return U.escape(String(d.member_id || '')); }},
            {title: '类型', width: 96, html: function (d) { return U.escape(d.type_label || ''); }},
            {title: '积分', width: 72, html: function (d) { return U.escape(String(d.points == null ? 0 : d.points)); }},
            {title: '状态', width: 88, html: function (d) { return U.escape(d.status_label || ''); }},
            {title: '发放 / 联系', html: function (d) { return U.escape(d.delivery_label || ''); }},
            {title: '操作', cls: 'actions', html: function (d) {
                if (String(d.status) === '1') {
                    return '<a href="#" class="btn-link js-ship">标为已发货</a>';
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
            title: mode === 'edit' ? '编辑商品' : '新增商品',
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
                if (!data.name) { U.toast('请填写名称', 'err'); return false; }
                if (mode !== 'edit') delete data.id; else data.id = row.id;
                return U.post('/admin/video/' + module + '/save', data).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return false; }
                    U.toast(mode === 'edit' ? '已保存' : '已创建', 'ok');
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
            if (!U.confirm('确认删除？')) return;
            U.post('/admin/video/' + module + '/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh();
                U.toast('已删除', 'ok');
            });
        }
        if (a.classList.contains('js-ship')) {
            var note = U.prompt('发货备注（可选）', row.remark || '');
            if (note === null) return;
            if (!U.confirm('标为已发货？')) return;
            U.post('/admin/video/mall_orders/save', {id: row.id, status: 2, remark: note}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh();
                U.toast('已发货', 'ok');
            });
        }
    });
})();
</script>
@endpush
