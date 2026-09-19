@extends('admin.layouts.inner')
@section('title', $title ?? '支付通道')

@php
    $desk = in_array((string) ($desk ?? ''), ['channels', 'stats'], true) ? (string) $desk : 'channels';
    $drivers = is_array($drivers ?? null) ? $drivers : ['epay' => '易支付', 'dfpay' => 'DfPay（A13 协议）'];
    $notifyEpay = (string) ($notify_epay ?? url('/pay/notify/epay'));
    $notifyDfpay = (string) ($notify_dfpay ?? url('/pay/notify/dfpay'));
    $stats = is_array($stats ?? null) ? $stats : [];
    $today = is_array($stats['today'] ?? null) ? $stats['today'] : ['orders' => 0, 'amount_yuan' => '0.00', 'points' => 0];
    $yesterday = is_array($stats['yesterday'] ?? null) ? $stats['yesterday'] : ['orders' => 0, 'amount_yuan' => '0.00', 'points' => 0];
    $week = is_array($stats['week'] ?? null) ? $stats['week'] : ['orders' => 0, 'amount_yuan' => '0.00', 'points' => 0];
    $month = is_array($stats['month'] ?? null) ? $stats['month'] : ['orders' => 0, 'amount_yuan' => '0.00', 'points' => 0];
    $byChannel = is_array($stats['by_channel'] ?? null) ? $stats['by_channel'] : [];
    $daily = is_array($stats['daily'] ?? null) ? $stats['daily'] : [];
@endphp

@section('plain')
<div class="card card-panel pay-channel-board desk-board" id="pay-channel-board">
    <div class="card-header">
        <span>{{ $desk === 'stats' ? '支付统计' : '支付通道' }} <em id="pay-ch-count"></em></span>
        <div>
            @if($desk === 'channels')
                <button type="button" class="btn btn-sm" id="pay-ch-add-btn">新增通道</button>
            @endif
            <a class="btn btn-muted btn-sm" href="/admin/video/orders">充值订单</a>
        </div>
    </div>
    <div class="card-body">
        <div class="queue-chips" id="pay-desks">
            <a class="chip{{ $desk === 'channels' ? ' active' : '' }}" href="/admin/video/pay_channels">通道</a>
            <a class="chip{{ $desk === 'stats' ? ' active' : '' }}" href="/admin/video/pay_channels?desk=stats">统计</a>
        </div>

        @if($desk === 'stats')
            <p class="muted recycle-lead">按已付订单统计（含官方微信/支付宝、易支付、DfPay、人工补录）。明细在 <a href="/admin/video/orders">充值订单</a>，可按单号搜索。</p>
            <div class="stat-grid dash" style="margin:12px 0 20px">
                <div class="stat-card">
                    <em>今日实收</em>
                    <strong>¥ {{ $today['amount_yuan'] }}</strong>
                    <span class="muted">{{ (int) $today['orders'] }} 笔 · {{ (int) $today['points'] }} 积分</span>
                </div>
                <div class="stat-card">
                    <em>昨日实收</em>
                    <strong>¥ {{ $yesterday['amount_yuan'] }}</strong>
                    <span class="muted">{{ (int) $yesterday['orders'] }} 笔 · {{ (int) $yesterday['points'] }} 积分</span>
                </div>
                <div class="stat-card">
                    <em>近 7 日</em>
                    <strong>¥ {{ $week['amount_yuan'] }}</strong>
                    <span class="muted">{{ (int) $week['orders'] }} 笔 · {{ (int) $week['points'] }} 积分</span>
                </div>
                <div class="stat-card">
                    <em>近 30 日</em>
                    <strong>¥ {{ $month['amount_yuan'] }}</strong>
                    <span class="muted">{{ (int) $month['orders'] }} 笔 · {{ (int) $month['points'] }} 积分</span>
                </div>
            </div>
            <div class="stat-grid dash" style="margin:0 0 20px">
                <div class="stat-card">
                    <em>待付</em>
                    <strong>{{ (int) ($stats['pending'] ?? 0) }}</strong>
                </div>
                <div class="stat-card">
                    <em>已付累计</em>
                    <strong>{{ (int) ($stats['paid'] ?? 0) }}</strong>
                </div>
                <div class="stat-card">
                    <em>已关闭</em>
                    <strong>{{ (int) ($stats['closed'] ?? 0) }}</strong>
                </div>
            </div>

            <h3 style="font-size:15px;margin:0 0 10px">近 30 日渠道</h3>
            @if($byChannel === [])
                <p class="muted">还没有已付订单。</p>
            @else
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                        <tr><th>渠道</th><th>笔数</th><th>金额</th><th>积分</th></tr>
                        </thead>
                        <tbody>
                        @foreach($byChannel as $row)
                            <tr>
                                <td>{{ $row['label'] }}</td>
                                <td>{{ (int) $row['orders'] }}</td>
                                <td>¥ {{ $row['amount_yuan'] }}</td>
                                <td>{{ (int) $row['points'] }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <h3 style="font-size:15px;margin:20px 0 10px">近 14 日趋势</h3>
            @if($daily === [])
                <p class="muted">暂无数据。</p>
            @else
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                        <tr><th>日期</th><th>笔数</th><th>金额</th></tr>
                        </thead>
                        <tbody>
                        @foreach(array_reverse($daily) as $row)
                            <tr>
                                <td>{{ $row['day'] }}</td>
                                <td>{{ (int) $row['orders'] }}</td>
                                <td>¥ {{ $row['amount_yuan'] }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        @else
            <p class="muted recycle-lead">简化版聚合通道：易支付 / DfPay。填网关、商户号、密钥即可对接。官方微信支付宝在「支付参数」。<br>
                回调成功：订单已付 → 加积分写流水 → 核销券 → 充值任务 → 站内信 → <code>MemberOrderPaid</code>。订单明细：<a href="/admin/video/orders">充值订单</a>（可搜单号）。统计见上方「统计」页签。<br>
                回调：易支付 <code>{{ $notifyEpay }}</code> · DfPay <code>{{ $notifyDfpay }}</code></p>
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
                <button type="button" class="btn btn-sm" id="pay-ch-search-btn">{{ admin_t('ui.search') }}</button>
                <button type="reset" class="btn btn-muted btn-sm" id="pay-ch-reset-btn">{{ admin_t('ui.reset') }}</button>
            </form>
            <div id="pay-ch-table"></div>
        @endif
    </div>
</div>

@if($desk === 'channels')
<template id="pay-ch-tpl">
    <form>
        <input type="hidden" name="id">
        <h3>通道</h3>
        <label>名称</label>
        <input class="entry-title" type="text" name="title" required placeholder="如 支付宝通道 / 微信H5" autofocus>
        <label>驱动</label>
        <select name="driver">
            @foreach($drivers as $k => $label)
                <option value="{{ $k }}">{{ $label }}</option>
            @endforeach
        </select>
        <p class="muted field-hint">易支付：api_url 填到域名根（会拼 submit.php）。DfPay：api_url 填下单接口完整地址，产品码必填。</p>
        <label>产品码</label>
        <input type="text" name="code" placeholder="易支付：alipay / wxpay；DfPay：对方给的 payType">
        <p class="muted field-hint">对应第三方支付方式编码，不是本站订单号。</p>

        <h3>网关凭证</h3>
        <label>网关地址</label>
        <input type="text" name="api_url" required placeholder="https://pay.example.com/">
        <label>商户号</label>
        <input type="text" name="mch_id" required placeholder="pid / partnerid">
        <label>密钥</label>
        <input type="text" name="app_key" required placeholder="appkey / sign key">
        <p class="muted field-hint">密钥只保存在本站，前台不会展示。改密钥后新单立即生效。</p>

        <h3>限额与展示</h3>
        <div class="admin-dialog-grid">
            <div>
                <label>最低金额（元）</label>
                <input type="number" name="min_yuan" value="0" min="0" step="0.01">
            </div>
            <div>
                <label>最高金额（元）</label>
                <input type="number" name="max_yuan" value="0" min="0" step="0.01">
            </div>
        </div>
        <p class="muted field-hint">填 0 表示不限。前台充值会校验区间。</p>
        <label>说明</label>
        <input type="text" name="hint" placeholder="前台可选备注">
        <div class="admin-dialog-grid">
            <div>
                <label>排序</label>
                <input type="number" name="sort" value="0">
            </div>
            <div>
                <label>状态</label>
                <select name="status">
                    <option value="1">启用</option>
                    <option value="0">停用</option>
                </select>
            </div>
        </div>
    </form>
</template>
@endif
@endsection

@if($desk === 'channels')
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
            wide: true,
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
@endif
