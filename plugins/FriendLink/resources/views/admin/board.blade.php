@extends('admin.layouts.inner')
@section('title', $title ?? admin_t('nav.flink'))

@php
    $desk = in_array((string) ($desk ?? ''), ['links', 'pending', 'cates', 'clicks', 'hits', 'stats', 'settings'], true)
        ? (string) $desk
        : 'links';
    $cates = is_array($cates ?? null) ? $cates : [];
    $options = is_array($options ?? null) ? $options : ['mode' => 'normal', 'min_referer' => 1, 'allow_apply' => 1, 'allow_edit' => 1];
    $period = in_array((string) request()->query('period', 'day'), ['day', 'month', 'year'], true)
        ? (string) request()->query('period', 'day')
        : 'day';
    $stats = is_array($stats ?? null) ? $stats : [];
    $today = is_array($stats['today'] ?? null) ? $stats['today'] : ['hits' => 0, 'clicks' => 0];
    $yesterday = is_array($stats['yesterday'] ?? null) ? $stats['yesterday'] : ['hits' => 0, 'clicks' => 0];
    $week = is_array($stats['week'] ?? null) ? $stats['week'] : ['hits' => 0, 'clicks' => 0];
    $month = is_array($stats['month'] ?? null) ? $stats['month'] : ['hits' => 0, 'clicks' => 0];
    $linksStat = is_array($stats['links'] ?? null) ? $stats['links'] : ['all' => 0, 'show' => 0, 'pending' => 0, 'reject' => 0, 'freeze' => 0];
    $topHits = is_array($stats['top_hits'] ?? null) ? $stats['top_hits'] : [];
    $topClicks = is_array($stats['top_clicks'] ?? null) ? $stats['top_clicks'] : [];
    $daily = is_array($stats['daily'] ?? null) ? $stats['daily'] : [];
    $hosts = is_array($stats['hosts'] ?? null) ? $stats['hosts'] : [];
    $flinkJsLang = [
        'fail' => admin_t('ui.fail'),
        'saved' => admin_t('ui.saved'),
        'created' => admin_t('ui.created'),
        'deleted' => admin_t('ui.deleted'),
        'add' => admin_t('ui.add'),
        'add_flink' => admin_t('ui.add_flink'),
        'edit_flink' => admin_t('ui.edit_flink'),
        'add_type' => admin_t('ui.add_type'),
        'edit_type' => admin_t('ui.edit_type'),
        'please_fill_name' => admin_t('ui.please_fill_name'),
        'confirm_remove' => admin_t('ui.confirm_remove'),
        'no_match_rows' => admin_t('ui.no_match_rows'),
        'clear_filter' => admin_t('ui.clear_filter'),
        'empty_flink' => admin_t('ui.empty_flink'),
        'no_pending_apply' => admin_t('ui.no_pending_apply'),
        'empty_types' => admin_t('ui.empty_types'),
        'empty_outbound' => admin_t('ui.empty_outbound'),
        'empty_referrers' => admin_t('ui.empty_referrers'),
        'empty_referrer_stats' => admin_t('ui.empty_referrer_stats'),
        'flink_short' => admin_t('ui.flink_short'),
        'referrer' => admin_t('ui.referrer'),
        'referrer_host' => admin_t('ui.referrer_host'),
        'outbound' => admin_t('ui.outbound'),
        'label_url' => admin_t('ui.label_url'),
        'empty_rows' => admin_t('ui.no_match_rows'),
    ];
@endphp

@section('plain')
<div class="card card-panel flink-board desk-board" id="flink-board">
    <div class="card-header">
        <span>{{ $desk === 'stats' ? admin_t('ui.flink_stats') : admin_t('ui.friend_links') }} <em id="flink-count"></em></span>
        <div>
            @if(! in_array($desk, ['clicks', 'hits', 'stats', 'settings'], true))
                <button type="button" class="btn btn-sm" id="flink-add-btn">{{ admin_t('ui.add') }}</button>
            @endif
            @if($desk === 'stats')
                <a class="btn btn-muted btn-sm" href="/admin/video/flinks?desk=hits">{{ admin_t('ui.flink_hits') }}</a>
                <a class="btn btn-muted btn-sm" href="/admin/video/flinks?desk=clicks">{{ admin_t('ui.flink_clicks') }}</a>
            @endif
        </div>
    </div>
    <div class="card-body">
        <div class="queue-chips" id="flink-desks">
            <a class="chip{{ $desk === 'links' ? ' active' : '' }}" href="/admin/video/flinks">{{ admin_t('ui.links_short') }}</a>
            <a class="chip{{ $desk === 'pending' ? ' active' : '' }}" href="/admin/video/flinks?desk=pending">{{ admin_t('ui.pending') }}</a>
            <a class="chip{{ $desk === 'cates' ? ' active' : '' }}" href="/admin/video/flinks?desk=cates">{{ admin_t('ui.types') }}</a>
            <a class="chip{{ $desk === 'clicks' ? ' active' : '' }}" href="/admin/video/flinks?desk=clicks">{{ admin_t('ui.outbound') }}</a>
            <a class="chip{{ $desk === 'hits' ? ' active' : '' }}" href="/admin/video/flinks?desk=hits">{{ admin_t('ui.referrer') }}</a>
            <a class="chip{{ $desk === 'stats' ? ' active' : '' }}" href="/admin/video/flinks?desk=stats">{{ admin_t('ui.stats') }}</a>
            <a class="chip{{ $desk === 'settings' ? ' active' : '' }}" href="/admin/video/flinks?desk=settings">{{ admin_t('ui.settings') }}</a>
        </div>

        @if($desk === 'stats')
            <p class="muted recycle-lead">{{ admin_t('ui.flink_stats_lead') }}</p>
            <div class="stat-grid dash" style="margin:12px 0 20px">
                <div class="stat-card">
                    <em>{{ admin_t('ui.today') }}</em>
                    <strong>{{ (int) $today['hits'] }}</strong>
                    <span class="muted">{{ admin_t('ui.referrer') }} · {{ admin_t('ui.outbound') }} {{ (int) $today['clicks'] }}</span>
                </div>
                <div class="stat-card">
                    <em>{{ admin_t('ui.yesterday') }}</em>
                    <strong>{{ (int) $yesterday['hits'] }}</strong>
                    <span class="muted">{{ admin_t('ui.referrer') }} · {{ admin_t('ui.outbound') }} {{ (int) $yesterday['clicks'] }}</span>
                </div>
                <div class="stat-card">
                    <em>{{ admin_t('ui.week_7') }}</em>
                    <strong>{{ (int) $week['hits'] }}</strong>
                    <span class="muted">{{ admin_t('ui.referrer') }} · {{ admin_t('ui.outbound') }} {{ (int) $week['clicks'] }}</span>
                </div>
                <div class="stat-card">
                    <em>{{ admin_t('ui.month_30') }}</em>
                    <strong>{{ (int) $month['hits'] }}</strong>
                    <span class="muted">{{ admin_t('ui.referrer') }} · {{ admin_t('ui.outbound') }} {{ (int) $month['clicks'] }}</span>
                </div>
            </div>
            <div class="stat-grid dash" style="margin:0 0 20px">
                <div class="stat-card">
                    <em>{{ admin_t('ui.showing') }}</em>
                    <strong>{{ (int) ($linksStat['show'] ?? 0) }}</strong>
                    <span class="muted">{{ admin_t('ui.n_items', ['n' => (int) ($linksStat['all'] ?? 0)]) }}</span>
                </div>
                <div class="stat-card">
                    <em>{{ admin_t('ui.pending') }}</em>
                    <strong>{{ (int) ($linksStat['pending'] ?? 0) }}</strong>
                </div>
                <div class="stat-card">
                    <em>{{ admin_t('ui.reject') }}</em>
                    <strong>{{ (int) ($linksStat['reject'] ?? 0) }}</strong>
                </div>
                <div class="stat-card">
                    <em>{{ admin_t('ui.freeze') }}</em>
                    <strong>{{ (int) ($linksStat['freeze'] ?? 0) }}</strong>
                </div>
            </div>

            <div class="flink-stats-split" style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:8px">
                <div>
                    <h3 style="font-size:15px;margin:0 0 10px">{{ admin_t('ui.last_30d_referrer_top') }}</h3>
                    @if($topHits === [])
                        <p class="muted">{{ admin_t('ui.empty_referrers_dot') }}</p>
                    @else
                        <div class="table-wrap">
                            <table class="data-table">
                                <thead><tr><th>{{ admin_t('ui.flink_short') }}</th><th>{{ admin_t('ui.referrer') }}</th></tr></thead>
                                <tbody>
                                @foreach($topHits as $row)
                                    <tr>
                                        <td>
                                            {{ $row['name'] }}
                                            @if(($row['url'] ?? '') !== '')
                                                <div class="muted" style="font-size:12px">{{ $row['url'] }}</div>
                                            @endif
                                        </td>
                                        <td>{{ (int) $row['hits'] }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
                <div>
                    <h3 style="font-size:15px;margin:0 0 10px">{{ admin_t('ui.last_30d_outbound_top') }}</h3>
                    @if($topClicks === [])
                        <p class="muted">{{ admin_t('ui.empty_outbound') }}</p>
                    @else
                        <div class="table-wrap">
                            <table class="data-table">
                                <thead><tr><th>{{ admin_t('ui.flink_short') }}</th><th>{{ admin_t('ui.outbound') }}</th></tr></thead>
                                <tbody>
                                @foreach($topClicks as $row)
                                    <tr>
                                        <td>
                                            {{ $row['name'] }}
                                            @if(($row['url'] ?? '') !== '')
                                                <div class="muted" style="font-size:12px">{{ $row['url'] }}</div>
                                            @endif
                                        </td>
                                        <td>{{ (int) $row['clicks'] }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <h3 style="font-size:15px;margin:20px 0 10px">{{ admin_t('ui.last_14d_trend') }}</h3>
            @if($daily === [])
                <p class="muted">{{ admin_t('ui.none') }}</p>
            @else
                <div class="table-wrap">
                    <table class="data-table">
                        <thead><tr><th>{{ admin_t('ui.date') }}</th><th>{{ admin_t('ui.referrer') }}</th><th>{{ admin_t('ui.outbound') }}</th></tr></thead>
                        <tbody>
                        @foreach(array_reverse($daily) as $row)
                            <tr>
                                <td>{{ $row['day'] }}</td>
                                <td>{{ (int) $row['hits'] }}</td>
                                <td>{{ (int) $row['clicks'] }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <h3 style="font-size:15px;margin:20px 0 10px">{{ admin_t('ui.last_30d_hosts') }}</h3>
            @if($hosts === [])
                <p class="muted">{{ admin_t('ui.empty_hosts') }}</p>
            @else
                <div class="table-wrap">
                    <table class="data-table">
                        <thead><tr><th>{{ admin_t('ui.host_col') }}</th><th>{{ admin_t('ui.times_col') }}</th></tr></thead>
                        <tbody>
                        @foreach($hosts as $row)
                            <tr>
                                <td>{{ $row['host'] }}</td>
                                <td>{{ (int) $row['hits'] }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <h3 style="font-size:15px;margin:20px 0 10px">{{ admin_t('ui.period_detail') }}</h3>
            <form class="filter-bar" id="flink-search" onsubmit="return false;">
                <input type="hidden" name="desk" value="stats">
                <input type="search" name="q" placeholder="{{ admin_t('ui.ph_search_name') }}" autocomplete="off">
                <select name="period" aria-label="{{ admin_t('ui.period') }}">
                    <option value="day" @selected($period === 'day')>{{ admin_t('ui.by_day') }}</option>
                    <option value="month" @selected($period === 'month')>{{ admin_t('ui.by_month') }}</option>
                    <option value="year" @selected($period === 'year')>{{ admin_t('ui.by_year') }}</option>
                </select>
                <button type="button" class="btn btn-sm" id="flink-search-btn">{{ admin_t('ui.search') }}</button>
                <button type="reset" class="btn btn-muted btn-sm" id="flink-reset-btn">{{ admin_t('ui.reset') }}</button>
            </form>
            <div id="flink-table"></div>
        @elseif($desk === 'settings')
            <p class="muted recycle-lead">{{ admin_t('ui.flink_lead') }}</p>
            <form id="flink-settings" onsubmit="return false;">
                <input type="hidden" name="desk" value="settings">
                <label>{{ admin_t('ui.mode') }}</label>
                <select name="mode">
                    <option value="normal" @selected(($options['mode'] ?? '') === 'normal')>{{ admin_t('ui.mode_normal') }}</option>
                    <option value="strong" @selected(($options['mode'] ?? '') === 'strong')>{{ admin_t('ui.mode_strong') }}</option>
                </select>
                <p class="muted field-hint">{{ admin_t('ui.strong_mode_hint') }}</p>
                <label>{{ admin_t('ui.min_referer') }}</label>
                <input type="number" name="min_referer" min="1" value="{{ (int) ($options['min_referer'] ?? 1) }}">
                <label>{{ admin_t('ui.allow_apply') }}</label>
                <select name="allow_apply">
                    <option value="1" @selected((int) ($options['allow_apply'] ?? 1) === 1)>{{ admin_t('ui.allow') }}</option>
                    <option value="0" @selected((int) ($options['allow_apply'] ?? 1) !== 1)>{{ admin_t('ui.off_switch') }}</option>
                </select>
                <label>{{ admin_t('ui.self_edit') }}</label>
                <select name="allow_edit">
                    <option value="1" @selected((int) ($options['allow_edit'] ?? 1) === 1)>{{ admin_t('ui.allow') }}</option>
                    <option value="0" @selected((int) ($options['allow_edit'] ?? 1) !== 1)>{{ admin_t('ui.off_switch') }}</option>
                </select>
                <p><button type="button" class="btn btn-sm" id="flink-settings-save">{{ admin_t('ui.save') }}</button></p>
            </form>
        @else
            <p class="muted recycle-lead">{{ admin_t('ui.flink_lead_list') }}</p>
            <form class="filter-bar" id="flink-search" onsubmit="return false;">
                <input type="hidden" name="desk" value="{{ $desk }}">
                <input type="search" name="q" placeholder="{{ $desk === 'cates' ? admin_t('ui.ph_search_noun', ['name' => admin_t('ui.types')]) : ($desk === 'clicks' || $desk === 'hits' ? admin_t('ui.ph_search_ip_url') : admin_t('ui.ph_search_name_url')) }}" autocomplete="off">
                @if($desk === 'links')
                    <select name="status" aria-label="{{ admin_t('ui.status') }}">
                        <option value="">{{ admin_t('ui.all_status') }}</option>
                        <option value="1">{{ admin_t('ui.show') }}</option>
                        <option value="2">{{ admin_t('ui.reject') }}</option>
                        <option value="3">{{ admin_t('ui.freeze') }}</option>
                        <option value="0">{{ admin_t('ui.pending') }}</option>
                    </select>
                @endif
                <button type="button" class="btn btn-sm" id="flink-search-btn">{{ admin_t('ui.search') }}</button>
                <button type="reset" class="btn btn-muted btn-sm" id="flink-reset-btn">{{ admin_t('ui.reset') }}</button>
            </form>
            <div id="flink-table"></div>
        @endif
    </div>
</div>

<template id="flink-link-tpl">
    <form class="admin-form">
        <input type="hidden" name="id">
        <input type="hidden" name="desk" value="links">
        <label>{{ admin_t('ui.name') }}</label>
        <input type="text" name="name" required placeholder="{{ admin_t('ui.ph_flink_name') }}">
        <p class="muted field-hint">{{ admin_t('ui.footer_flink_hint') }}</p>
        <label>{{ admin_t('ui.label_url') }}</label>
        <input type="text" name="url" required placeholder="https://">
        <p class="muted field-hint">{{ admin_t('ui.https_only_go') }}</p>
        <label>{{ admin_t('ui.types') }}</label>
        <select name="cate_id">
            <option value="0">{{ admin_t('ui.ungrouped') }}</option>
            @foreach($cates as $cate)
                <option value="{{ $cate['id'] }}">{{ $cate['name'] }}</option>
            @endforeach
        </select>
        <label>{{ admin_t('ui.email') }}</label>
        <input type="text" name="email" placeholder="{{ admin_t('ui.optional') }}">
        <label>{{ admin_t('ui.remarks') }}</label>
        <input type="text" name="remark" placeholder="{{ admin_t('ui.optional') }}">
        <label>{{ admin_t('ui.sort') }}</label>
        <input type="number" name="sort" value="0">
        <p class="muted field-hint">{{ admin_t('ui.sort_normal_hint') }}</p>
        <label>{{ admin_t('ui.status') }}</label>
        <select name="status">
            <option value="0">{{ admin_t('ui.pending') }}</option>
            <option value="1">{{ admin_t('ui.show') }}</option>
            <option value="2">{{ admin_t('ui.reject') }}</option>
            <option value="3">{{ admin_t('ui.freeze') }}</option>
        </select>
    </form>
</template>
<template id="flink-cate-tpl">
    <form class="admin-form">
        <input type="hidden" name="id">
        <input type="hidden" name="desk" value="cates">
        <label>{{ admin_t('ui.name') }}</label>
        <input type="text" name="name" required placeholder="{{ admin_t('ui.ph_partner_site') }}" autofocus>
        <p class="muted field-hint">{{ admin_t('ui.flink_group_hint') }}</p>
        <label>{{ admin_t('ui.sort') }}</label>
        <input type="number" name="sort" value="0">
        <p class="muted field-hint">{{ admin_t('ui.hint_sort_desc') }}</p>
        <label>{{ admin_t('ui.status') }}</label>
        <select name="status">
            <option value="1">{{ admin_t('ui.show') }}</option>
            <option value="0">{{ admin_t('ui.hide') }}</option>
        </select>
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($flinkJsLang, JSON_UNESCAPED_UNICODE);
    var desk = @json($desk);
    if (desk === 'settings') {
        U.on('#flink-settings-save', 'click', function () {
            var form = document.getElementById('flink-settings');
            var data = U.formData(form);
            data.desk = 'settings';
            U.post('/admin/video/flinks/save', data).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
                U.toast(L.saved, 'ok');
            });
        });
        return;
    }
    var form = document.getElementById('flink-search');
    var countEl = document.getElementById('flink-count');
    var addBtn = document.getElementById('flink-add-btn');
    var addLabels = {links: L.add_flink, pending: L.add_flink, cates: L.add_type};
    if (addBtn) addBtn.textContent = addLabels[desk] || L.add;
    if (!form) return;

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
            if (k === 'limit' || k === 'desk' || k === 'period') return false;
            return where[k] !== '' && where[k] != null;
        });
    }
    function emptyHtml(_parsed, where) {
        if (isFiltered(where)) {
            return '<div class="list-empty"><p>' + L.no_match_rows + '</p><p><button type="button" class="btn btn-muted btn-sm" id="flink-empty-reset">' + L.clear_filter + '</button></p></div>';
        }
        var copy = {
            links: [L.empty_flink, L.add_flink],
            pending: [L.no_pending_apply, ''],
            cates: [L.empty_types, L.add_type],
            clicks: [L.empty_outbound, ''],
            hits: [L.empty_referrers, ''],
            stats: [L.empty_referrer_stats, '']
        }[desk] || [L.empty_rows, ''];
        if (!copy[1]) return '<div class="list-empty"><p>' + copy[0] + '</p></div>';
        return '<div class="list-empty"><p>' + copy[0] + '</p><p><button type="button" class="btn btn-primary btn-sm" id="flink-empty-add">' + copy[1] + '</button></p></div>';
    }

    var cols = [];
    if (desk === 'cates') {
        cols = [
            {title: AdminUi.t('name'), html: function (d) { return '<a class="js-edit" href="#">' + U.escape(d.name || AdminUi.t('not_filled')) + '</a>'; }},
            {title: AdminUi.t('sort'), width: 72, html: function (d) { return U.escape(String(d.sort || 0)); }},
            {title: AdminUi.t('status'), width: 72, html: function (d) {
                return String(d.status) === '1' ? U.status(true, AdminUi.t('show')) : U.status(false, AdminUi.t('hide'));
            }},
            {title: AdminUi.t('actions'), cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-edit">' + AdminUi.t('edit') + '</a><a href="#" class="btn-link js-del">' + AdminUi.t('delete') + '</a>';
            }}
        ];
    } else if (desk === 'clicks') {
        cols = [
            {title: L.flink_short, html: function (d) { return U.escape(d.link_name || ('#' + (d.link_id || ''))); }},
            {title: 'IP', width: 120, html: function (d) { return U.escape(d.ip || ''); }},
            {title: AdminUi.t('time'), width: 120, html: function (d) { return U.escape(String(d.created_at || '')); }}
        ];
    } else if (desk === 'hits') {
        cols = [
            {title: L.flink_short, html: function (d) { return U.escape(d.link_name || ('#' + (d.link_id || ''))); }},
            {title: L.referrer_host, html: function (d) { return U.escape(d.from_host || ''); }},
            {title: 'IP', width: 120, html: function (d) { return U.escape(d.ip || ''); }},
            {title: AdminUi.t('date'), width: 90, html: function (d) { return U.escape(d.day_key || ''); }}
        ];
    } else if (desk === 'stats') {
        cols = [
            {title: AdminUi.t('period'), width: 110, html: function (d) { return U.escape(d.period_key || ''); }},
            {title: L.flink_short, html: function (d) { return U.escape(d.link_name || ('#' + (d.link_id || ''))); }},
            {title: L.referrer, width: 72, html: function (d) { return U.escape(String(d.hits == null ? 0 : d.hits)); }}
        ];
    } else {
        cols = [
            {title: AdminUi.t('name'), html: function (d) {
                return '<a class="entry-row-title js-edit" href="#">' + U.escape(d.name || AdminUi.t('not_filled')) + '</a>';
            }},
            {title: L.label_url, html: function (d) { return U.escape(d.url || ''); }},
            {title: AdminUi.t('types'), width: 88, html: function (d) { return U.escape(d.cate_name || ''); }},
            {title: L.referrer, width: 72, html: function (d) { return U.escape(String(d.referer_total == null ? 0 : d.referer_total)); }},
            {title: L.outbound, width: 72, html: function (d) { return U.escape(String(d.clicks == null ? 0 : d.clicks)); }},
            {title: AdminUi.t('status'), width: 72, html: function (d) { return U.escape(d.status_label || ''); }},
            {title: AdminUi.t('actions'), cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-edit">' + AdminUi.t('edit') + '</a><a href="#" class="btn-link js-del">' + AdminUi.t('delete') + '</a>';
            }}
        ];
    }

    var table = U.table({
        el: '#flink-table',
        url: '/admin/video/flinks/list',
        where: queryWhere(),
        emptyHtml: emptyHtml,
        onDraw: function (_wrap, list) {
            if (countEl && desk !== 'stats') countEl.textContent = list.length ? '· ' + list.length : '';
            var add = document.getElementById('flink-empty-add');
            var reset = document.getElementById('flink-empty-reset');
            if (add) add.addEventListener('click', function () { openDialog('add'); });
            if (reset) reset.addEventListener('click', function () { form.reset(); runSearch(); });
        },
        cols: cols
    });

    function runSearch() { table.reload(queryWhere()); }
    function openDialog(mode, row) {
        row = row || {};
        var isCate = desk === 'cates';
        U.dialog({
            title: mode === 'edit' ? (isCate ? L.edit_type : L.edit_flink) : (isCate ? L.add_type : L.add_flink),
            content: document.getElementById(isCate ? 'flink-cate-tpl' : 'flink-link-tpl').innerHTML,
            onOpen: function (body) {
                U.fillForm(body.querySelector('form'), isCate ? {
                    id: mode === 'edit' ? (row.id || '') : '',
                    name: row.name || '',
                    sort: row.sort == null ? 0 : row.sort,
                    status: row.status == null ? '1' : String(row.status)
                } : {
                    id: mode === 'edit' ? (row.id || '') : '',
                    name: row.name || '',
                    url: row.url || '',
                    cate_id: row.cate_id == null ? 0 : row.cate_id,
                    email: row.email || '',
                    remark: row.remark || '',
                    sort: row.sort == null ? 0 : row.sort,
                    status: row.status == null ? (desk === 'pending' ? '0' : '1') : String(row.status)
                });
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (!data.name) { U.toast(L.please_fill_name, 'err'); return false; }
                data.desk = isCate ? 'cates' : 'links';
                if (mode !== 'edit') delete data.id; else data.id = row.id;
                return U.post('/admin/video/flinks/save', data).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return false; }
                    U.toast(mode === 'edit' ? L.saved : L.created, 'ok');
                    table.refresh();
                });
            }
        });
    }

    U.on('#flink-search-btn', 'click', runSearch);
    U.on('#flink-reset-btn', 'click', function () { setTimeout(runSearch, 0); });
    if (addBtn) U.on('#flink-add-btn', 'click', function () { openDialog('add'); });
    U.on('#flink-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-edit')) openDialog('edit', row);
        if (a.classList.contains('js-del')) {
            if (!U.confirm(L.confirm_remove)) return;
            U.post('/admin/video/flinks/delete', {id: row.id, desk: desk === 'cates' ? 'cates' : 'links'}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
                table.refresh();
                U.toast(L.deleted, 'ok');
            });
        }
    });
})();
</script>
<style>
@media (max-width: 900px) {
    .flink-stats-split { grid-template-columns: 1fr !important; }
}
</style>
@endpush
