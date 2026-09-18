@extends('admin.layouts.inner')
@section('title', $title ?? '支付通道')

@php
    $drivers = is_array($drivers ?? null) ? $drivers : ['epay' => '易支付', 'dfpay' => 'DfPay（A13 协议）'];
    $notifyEpay = (string) ($notify_epay ?? url('/pay/notify/epay'));
    $notifyDfpay = (string) ($notify_dfpay ?? url('/pay/notify/dfpay'));
@endphp

@section('plain')
<div class="card card-panel pay-channel-board desk-board" id="pay-channel-board">
    <div class="card-header">
        <span>支付通道 <em id="pay-ch-count"></em></span>
        <div>
            <button type="button" class="btn btn-sm" id="pay-ch-add-btn">新增通道</button>
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">简化版聚合通道：易支付（MacCMS 同款）和 DfPay（A13 协议）。填网关、商户号、密钥即可对接。官方微信 / 支付宝仍在「支付参数」页。回调地址：易支付 <code>{{ $notifyEpay }}</code> · DfPay <code>{{ $notifyDfpay }}</code></p>
        <form class="filter-bar" id="pay-ch-search" onsubmit="return false;">
            <input type="search" name="q" placeholder="搜名称、商户号、产品码" autocomplete="off">
            <select name="driver" aria-label="驱动">
                <option value="">全部驱动</option>
                @foreach($drivers as $k => $label)
                    <option value="{{ $k }}">{{ $label }}</option>
                @endforeach
            </select>
            <select name="status" aria-label="状态">
                <option value="">全部状态</option>
                <option value="1">启用</option>
                <option value="0">停用</option>
            </select>
            <button type="button" class="btn btn-sm" id="pay-ch-search-btn">查询</button>
            <button type="reset" class="btn btn-muted btn-sm" id="pay-ch-reset-btn">重置</button>
        </form>
        <div id="pay-ch-table"></div>
    </div>
</div>

<template id="pay-ch-tpl">
    <form>
        <input type="hidden" name="id">
        <label>名称</label>
        <input type="text" name="title" required placeholder="如 支付宝通道 / 微信H5">
        <label>驱动</label>
        <select name="driver">
            @foreach($drivers as $k => $label)
                <option value="{{ $k }}">{{ $label }}</option>
            @endforeach
        </select>
        <p class="muted field-hint">易支付：api_url 填到域名根（会拼 submit.php）。DfPay：api_url 填下单接口完整地址，产品码必填。</p>
        <label>产品码</label>
        <input type="text" name="code" placeholder="易支付：alipay / wxpay；DfPay：对方给的 payType">
        <label>网关地址</label>
        <input type="text" name="api_url" required placeholder="https://pay.example.com/">
        <label>商户号</label>
        <input type="text" name="mch_id" required placeholder="pid / partnerid">
        <label>密钥</label>
        <input type="text" name="app_key" required placeholder="appkey / sign key">
        <label>最低金额（元）</label>
        <input type="number" name="min_yuan" value="0" min="0" step="0.01">
        <label>最高金额（元）</label>
        <input type="number" name="max_yuan" value="0" min="0" step="0.01">
        <p class="muted field-hint">填 0 表示不限。</p>
        <label>说明</label>
        <input type="text" name="hint" placeholder="前台可选备注">
        <label>排序</label>
        <input type="number" name="sort" value="0">
        <label>状态</label>
        <select name="status">
            <option value="1">启用</option>
            <option value="0">停用</option>
        </select>
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var form = document.getElementById('pay-ch-search');
    var countEl = document.getElementById('pay-ch-count');

    function cleanWhere(data) {
        var out = {};
        Object.keys(data || {}).forEach(function (k) {
            if (data[k] !== '' && data[k] != null) out[k] = data[k];
        });
        return out;
    }
    function queryWhere() {
        return Object.assign({limit: 20}, cleanWhere(U.formData(form)));
    }
    function isFiltered(where) {
        return Object.keys(where || {}).some(function (k) { return k !== 'limit' && where[k] !== '' && where[k] != null; });
    }

    var table = U.table({
        el: '#pay-ch-table',
        url: '/admin/video/pay_channels/list',
        where: queryWhere(),
        emptyHtml: function (_p, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>没有符合条件的通道</p><p><button type="button" class="btn btn-muted btn-sm" id="pay-ch-empty-reset">清除筛选</button></p></div>';
            }
            return '<div class="list-empty"><p>还没有支付通道</p><p><button type="button" class="btn btn-primary btn-sm" id="pay-ch-empty-add">新增通道</button></p></div>';
        },
        onDraw: function (_wrap, list) {
            countEl.textContent = list.length ? '· ' + list.length : '';
            var add = document.getElementById('pay-ch-empty-add');
            var reset = document.getElementById('pay-ch-empty-reset');
            if (add) add.addEventListener('click', function () { openDialog('add'); });
            if (reset) reset.addEventListener('click', function () { form.reset(); runSearch(); });
        },
        cols: [
            {title: '名称', html: function (d) {
                return '<a class="entry-row-title js-edit" href="#">' + U.escape(d.title || '') + '</a>';
            }},
            {title: '驱动', width: 140, html: function (d) { return U.escape(d.driver_label || d.driver || ''); }},
            {title: '产品码', width: 100, html: function (d) { return U.escape(d.code || '-'); }},
            {title: '商户号', width: 120, html: function (d) { return U.escape(d.mch_id || ''); }},
            {title: '状态', width: 72, html: function (d) {
                return String(d.status) === '1' ? U.status(true, '启用') : U.status(false, '停用');
            }},
            {title: '操作', cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-edit">编辑</a><a href="#" class="btn-link js-del">删除</a>';
            }}
        ]
    });

    function runSearch() { table.reload(queryWhere()); }
    function fill(mode, row) {
        row = row || {};
        return {
            id: mode === 'edit' ? (row.id || '') : '',
            title: row.title || '',
            driver: row.driver || 'epay',
            code: row.code || '',
            api_url: row.api_url || '',
            mch_id: row.mch_id || '',
            app_key: row.app_key || '',
            min_yuan: row.min_yuan == null ? 0 : row.min_yuan,
            max_yuan: row.max_yuan == null ? 0 : row.max_yuan,
            hint: row.hint || '',
            sort: row.sort == null ? 0 : row.sort,
            status: row.status == null ? '1' : String(row.status)
        };
    }
    function openDialog(mode, row) {
        row = row || {};
        U.dialog({
            title: mode === 'edit' ? '编辑通道' : '新增通道',
            content: document.getElementById('pay-ch-tpl').innerHTML,
            onOpen: function (body) {
                U.fillForm(body.querySelector('form'), fill(mode, row));
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (!data.title) { U.toast('请填写名称', 'err'); return false; }
                if (mode !== 'edit') delete data.id; else data.id = row.id;
                return U.post('/admin/video/pay_channels/save', data).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return false; }
                    U.toast(mode === 'edit' ? '已保存' : '已创建', 'ok');
                    table.refresh();
                });
            }
        });
    }

    U.on('#pay-ch-search-btn', 'click', runSearch);
    U.on('#pay-ch-reset-btn', 'click', function () { setTimeout(runSearch, 0); });
    U.on('#pay-ch-add-btn', 'click', function () { openDialog('add'); });
    U.on('#pay-ch-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-edit')) openDialog('edit', row);
        if (a.classList.contains('js-del')) {
            if (!U.confirm('确认删除？')) return;
            U.post('/admin/video/pay_channels/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh();
                U.toast('已删除', 'ok');
            });
        }
    });
})();
</script>
@endpush
