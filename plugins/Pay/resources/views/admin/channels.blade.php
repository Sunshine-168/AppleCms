@extends('admin.layouts.inner')
@section('title', $title ?? admin_t('nav.pay_channels'))

@php
    $desk = in_array((string) ($desk ?? ''), ['channels', 'stats'], true) ? (string) $desk : 'channels';
    $drivers = is_array($drivers ?? null) && $drivers !== []
        ? $drivers
        : ['epay' => admin_t('ui.channel_epay'), 'dfpay' => admin_t('ui.channel_dfpay')];
    $notifyEpay = (string) ($notify_epay ?? url('/pay/notify/epay'));
    $notifyDfpay = (string) ($notify_dfpay ?? url('/pay/notify/dfpay'));
    $stats = is_array($stats ?? null) ? $stats : [];
    $today = is_array($stats['today'] ?? null) ? $stats['today'] : ['orders' => 0, 'amount_yuan' => '0.00', 'points' => 0];
    $yesterday = is_array($stats['yesterday'] ?? null) ? $stats['yesterday'] : ['orders' => 0, 'amount_yuan' => '0.00', 'points' => 0];
    $week = is_array($stats['week'] ?? null) ? $stats['week'] : ['orders' => 0, 'amount_yuan' => '0.00', 'points' => 0];
    $month = is_array($stats['month'] ?? null) ? $stats['month'] : ['orders' => 0, 'amount_yuan' => '0.00', 'points' => 0];
    $byChannel = is_array($stats['by_channel'] ?? null) ? $stats['by_channel'] : [];
    $daily = is_array($stats['daily'] ?? null) ? $stats['daily'] : [];
    $payJsLang = [
        'no_match_channels' => admin_t('ui.no_match_channels'),
        'clear_filter' => admin_t('ui.clear_filter'),
        'empty_pay_channels' => admin_t('ui.empty_pay_channels'),
        'add_pay_channel' => admin_t('ui.add_pay_channel'),
        'edit_pay_channel' => admin_t('ui.edit_pay_channel'),
        'please_fill_name' => admin_t('ui.please_fill_name'),
        'fail' => admin_t('ui.fail'),
        'saved' => admin_t('ui.saved'),
        'created' => admin_t('ui.created'),
        'deleted' => admin_t('ui.deleted'),
        'confirm_remove' => admin_t('ui.confirm_remove'),
    ];
@endphp

@section('plain')
<div class="card card-panel pay-channel-board desk-board" id="pay-channel-board">
    <div class="card-header">
        <span>{{ $desk === 'stats' ? admin_t('ui.pay_stats') : admin_t('nav.pay_channels') }} <em id="pay-ch-count"></em></span>
        <div>
            @if($desk === 'channels')
                <button type="button" class="btn btn-sm" id="pay-ch-add-btn">{{ admin_t('ui.add_pay_channel') }}</button>
            @endif
            <a class="btn btn-muted btn-sm" href="/admin/video/orders">{{ admin_t('ui.recharge_orders') }}</a>
        </div>
    </div>
    <div class="card-body">
        <div class="queue-chips" id="pay-desks">
            <a class="chip{{ $desk === 'channels' ? ' active' : '' }}" href="/admin/video/pay_channels">{{ admin_t('ui.pay_channel') }}</a>
            <a class="chip{{ $desk === 'stats' ? ' active' : '' }}" href="/admin/video/pay_channels?desk=stats">{{ admin_t('ui.stats') }}</a>
        </div>

        @if($desk === 'stats')
            <p class="muted recycle-lead">{{ admin_t('ui.pay_stats_lead') }} <a href="/admin/video/orders">{{ admin_t('ui.recharge_orders') }}</a></p>
            <div class="stat-grid dash" style="margin:12px 0 20px">
                <div class="stat-card">
                    <em>{{ admin_t('ui.today_take') }}</em>
                    <strong>¥ {{ $today['amount_yuan'] }}</strong>
                    <span class="muted">{{ admin_t('ui.n_orders_pts', ['orders' => (int) $today['orders'], 'points' => (int) $today['points']]) }}</span>
                </div>
                <div class="stat-card">
                    <em>{{ admin_t('ui.yesterday_take') }}</em>
                    <strong>¥ {{ $yesterday['amount_yuan'] }}</strong>
                    <span class="muted">{{ admin_t('ui.n_orders_pts', ['orders' => (int) $yesterday['orders'], 'points' => (int) $yesterday['points']]) }}</span>
                </div>
                <div class="stat-card">
                    <em>{{ admin_t('ui.week_7') }}</em>
                    <strong>¥ {{ $week['amount_yuan'] }}</strong>
                    <span class="muted">{{ admin_t('ui.n_orders_pts', ['orders' => (int) $week['orders'], 'points' => (int) $week['points']]) }}</span>
                </div>
                <div class="stat-card">
                    <em>{{ admin_t('ui.month_30') }}</em>
                    <strong>¥ {{ $month['amount_yuan'] }}</strong>
                    <span class="muted">{{ admin_t('ui.n_orders_pts', ['orders' => (int) $month['orders'], 'points' => (int) $month['points']]) }}</span>
                </div>
            </div>
            <div class="stat-grid dash" style="margin:0 0 20px">
                <div class="stat-card">
                    <em>{{ admin_t('ui.pending_pay') }}</em>
                    <strong>{{ (int) ($stats['pending'] ?? 0) }}</strong>
                </div>
                <div class="stat-card">
                    <em>{{ admin_t('ui.paid_total') }}</em>
                    <strong>{{ (int) ($stats['paid'] ?? 0) }}</strong>
                </div>
                <div class="stat-card">
                    <em>{{ admin_t('ui.closed_orders') }}</em>
                    <strong>{{ (int) ($stats['closed'] ?? 0) }}</strong>
                </div>
            </div>

            <h3 style="font-size:15px;margin:0 0 10px">{{ admin_t('ui.last_30d_channels') }}</h3>
            @if($byChannel === [])
                <p class="muted">{{ admin_t('ui.empty_paid_orders') }}</p>
            @else
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                        <tr><th>{{ admin_t('ui.pay_channel') }}</th><th>{{ admin_t('ui.n_count') }}</th><th>{{ admin_t('ui.amount') }}</th><th>{{ admin_t('ui.points') }}</th></tr>
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

            <h3 style="font-size:15px;margin:20px 0 10px">{{ admin_t('ui.last_14d_trend') }}</h3>
            @if($daily === [])
                <p class="muted">{{ admin_t('ui.none') }}</p>
            @else
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                        <tr><th>{{ admin_t('ui.date') }}</th><th>{{ admin_t('ui.n_count') }}</th><th>{{ admin_t('ui.amount') }}</th></tr>
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
            <p class="muted recycle-lead">{{ admin_t('ui.pay_channel_lead') }}<br>
                {{ admin_t('ui.pay_callback_lead') }} <a href="/admin/video/orders">{{ admin_t('ui.recharge_orders') }}</a>{{ admin_t('ui.pay_callback_tail') }}<br>
                {{ admin_t('ui.pay_notify') }}：{{ admin_t('ui.channel_epay') }} <code>{{ $notifyEpay }}</code> · DfPay <code>{{ $notifyDfpay }}</code></p>
            <form class="filter-bar" id="pay-ch-search" onsubmit="return false;">
                <input type="search" name="q" placeholder="{{ admin_t('ui.ph_search_pay_ch') }}" autocomplete="off">
                <select name="driver" aria-label="{{ admin_t('ui.driver') }}">
                    <option value="">{{ admin_t('ui.all_drivers') }}</option>
                    @foreach($drivers as $k => $label)
                        <option value="{{ $k }}">{{ $label }}</option>
                    @endforeach
                </select>
                <select name="status" aria-label="{{ admin_t('ui.status') }}">
                    <option value="">{{ admin_t('ui.all_status') }}</option>
                    <option value="1">{{ admin_t('ui.enabled') }}</option>
                    <option value="0">{{ admin_t('ui.disabled') }}</option>
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
    <form class="admin-form">
        <input type="hidden" name="id">
        <h3>{{ admin_t('ui.pay_channel') }}</h3>
        <label>{{ admin_t('ui.name') }}</label>
        <input class="entry-title" type="text" name="title" required placeholder="{{ admin_t('ui.ph_pay_channel_name') }}" autofocus>
        <label>{{ admin_t('ui.driver') }}</label>
        <select name="driver">
            @foreach($drivers as $k => $label)
                <option value="{{ $k }}">{{ $label }}</option>
            @endforeach
        </select>
        <p class="muted field-hint">{{ admin_t('ui.driver_hint') }}</p>
        <label>{{ admin_t('ui.product_code') }}</label>
        <input type="text" name="code" placeholder="{{ admin_t('ui.product_code_ph') }}">
        <p class="muted field-hint">{{ admin_t('ui.product_code_hint') }}</p>

        <h3>{{ admin_t('ui.gateway_creds') }}</h3>
        <label>{{ admin_t('ui.gateway_url') }}</label>
        <input type="text" name="api_url" required placeholder="https://pay.example.com/">
        <label>{{ admin_t('ui.mch_id') }}</label>
        <input type="text" name="mch_id" required placeholder="pid / partnerid">
        <label>{{ admin_t('ui.secret_key') }}</label>
        <input type="text" name="app_key" required placeholder="appkey / sign key">
        <p class="muted field-hint">{{ admin_t('ui.secret_keep_hint') }}</p>

        <h3>{{ admin_t('ui.limits_show') }}</h3>
        <div class="admin-dialog-grid">
            <div>
                <label>{{ admin_t('ui.min_amount_yuan') }}</label>
                <input type="number" name="min_yuan" value="0" min="0" step="0.01">
            </div>
            <div>
                <label>{{ admin_t('ui.max_amount_yuan') }}</label>
                <input type="number" name="max_yuan" value="0" min="0" step="0.01">
            </div>
        </div>
        <p class="muted field-hint">{{ admin_t('ui.zero_no_limit') }}</p>
        <label>{{ admin_t('ui.intro') }}</label>
        <input type="text" name="hint" placeholder="{{ admin_t('ui.front_note') }}">
        <div class="admin-dialog-grid">
            <div>
                <label>{{ admin_t('ui.sort') }}</label>
                <input type="number" name="sort" value="0">
            </div>
            <div>
                <label>{{ admin_t('ui.status') }}</label>
                <select name="status">
                    <option value="1">{{ admin_t('ui.enabled') }}</option>
                    <option value="0">{{ admin_t('ui.disabled') }}</option>
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
    var L = @json($payJsLang, JSON_UNESCAPED_UNICODE);
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
                return '<div class="list-empty"><p>' + L.no_match_channels + '</p><p><button type="button" class="btn btn-muted btn-sm" id="pay-ch-empty-reset">' + L.clear_filter + '</button></p></div>';
            }
            return '<div class="list-empty"><p>' + L.empty_pay_channels + '</p><p><button type="button" class="btn btn-primary btn-sm" id="pay-ch-empty-add">' + L.add_pay_channel + '</button></p></div>';
        },
        onDraw: function (_wrap, list) {
            countEl.textContent = list.length ? '· ' + list.length : '';
            var add = document.getElementById('pay-ch-empty-add');
            var reset = document.getElementById('pay-ch-empty-reset');
            if (add) add.addEventListener('click', function () { openDialog('add'); });
            if (reset) reset.addEventListener('click', function () { form.reset(); runSearch(); });
        },
        cols: [
            {title: AdminUi.t('name'), html: function (d) {
                return '<a class="entry-row-title js-edit" href="#">' + U.escape(d.title || '') + '</a>';
            }},
            {title: AdminUi.t('driver'), width: 140, html: function (d) { return U.escape(d.driver_label || d.driver || ''); }},
            {title: AdminUi.t('product_code'), width: 100, html: function (d) { return U.escape(d.code || '-'); }},
            {title: AdminUi.t('mch_id'), width: 120, html: function (d) { return U.escape(d.mch_id || ''); }},
            {title: AdminUi.t('status'), width: 72, html: function (d) {
                return String(d.status) === '1' ? U.status(true, AdminUi.t('enabled')) : U.status(false, AdminUi.t('disabled'));
            }},
            {title: AdminUi.t('actions'), cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-edit">' + AdminUi.t('edit') + '</a><a href="#" class="btn-link js-del">' + AdminUi.t('delete') + '</a>';
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
            title: mode === 'edit' ? L.edit_pay_channel : L.add_pay_channel,
            wide: true,
            content: document.getElementById('pay-ch-tpl').innerHTML,
            onOpen: function (body) {
                U.fillForm(body.querySelector('form'), fill(mode, row));
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (!data.title) { U.toast(L.please_fill_name, 'err'); return false; }
                if (mode !== 'edit') delete data.id; else data.id = row.id;
                return U.post('/admin/video/pay_channels/save', data).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return false; }
                    U.toast(mode === 'edit' ? L.saved : L.created, 'ok');
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
            if (!U.confirm(L.confirm_remove)) return;
            U.post('/admin/video/pay_channels/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
                table.refresh();
                U.toast(L.deleted, 'ok');
            });
        }
    });
})();
</script>
@endpush
@endif
