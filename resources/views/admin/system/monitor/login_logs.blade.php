@extends('admin.layouts.inner')
@section('title', admin_t('page.login_logs'))

@php
    $today = $today ?? now()->toDateString();
    $yesterday = $yesterday ?? now()->subDay()->toDateString();
    $weekFrom = $weekFrom ?? now()->subDays(6)->toDateString();
    $monthFrom = $monthFrom ?? now()->subDays(29)->toDateString();
    $jsLang = [
        'unknown_admin' => admin_t('ui.unknown_admin'),
        'current_account' => admin_t('ui.current_account'),
        'no_match' => admin_t('ui.no_match'),
        'clear_filter' => admin_t('ui.clear_filter'),
        'empty_login' => admin_t('ui.empty_login'),
        'empty_login_hint' => admin_t('ui.empty_login_hint'),
        'login_col' => admin_t('ui.tab_login'),
    ];
@endphp

@section('plain')
<div class="card card-panel log-index login-log-index">
    <div class="card-header">
        <span>{{ admin_t('page.login_logs') }} <em id="login-log-count"></em></span>
    </div>
    <div class="card-body">
        @include('admin.partials.log-tabs', ['tab' => 'login'])
        @include('admin.partials.log-filters', ['kind' => 'login'])
        <p class="muted recycle-lead">{{ admin_t('ui.login_lead') }}</p>
        <div id="login-log-table"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($jsLang, JSON_UNESCAPED_UNICODE);
    var form = document.getElementById('login-log-search');
    var countEl = document.getElementById('login-log-count');
    var ipChip = document.getElementById('login-log-ip-chip');
    var whenSel = document.getElementById('login-log-when');
    var datesWrap = document.getElementById('login-log-dates');
    var TODAY = @json($today, JSON_UNESCAPED_UNICODE);
    var YESTERDAY = @json($yesterday, JSON_UNESCAPED_UNICODE);
    var WEEK_FROM = @json($weekFrom, JSON_UNESCAPED_UNICODE);
    var MONTH_FROM = @json($monthFrom, JSON_UNESCAPED_UNICODE);
    var typing = 0;

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
    function setDates(from, to) {
        form.start_time.value = from || '';
        form.end_time.value = to || '';
    }
    function syncWhenUi() {
        var from = form.start_time.value;
        var to = form.end_time.value;
        var key = '';
        if (from === TODAY && to === TODAY) key = 'today';
        else if (from === YESTERDAY && to === YESTERDAY) key = 'yesterday';
        else if (from === WEEK_FROM && to === TODAY) key = 'week';
        else if (from === MONTH_FROM && to === TODAY) key = 'month';
        else if (from || to) key = 'custom';
        whenSel.value = key;
        datesWrap.hidden = key !== 'custom';
    }
    function applyWhen() {
        var key = whenSel.value;
        if (key === 'custom') {
            datesWrap.hidden = false;
            if (!form.start_time.value) form.start_time.value = TODAY;
            if (!form.end_time.value) form.end_time.value = TODAY;
            runSearch();
            return;
        }
        datesWrap.hidden = true;
        setDates('', '');
        if (key === 'today') setDates(TODAY, TODAY);
        else if (key === 'yesterday') setDates(YESTERDAY, YESTERDAY);
        else if (key === 'week') setDates(WEEK_FROM, TODAY);
        else if (key === 'month') setDates(MONTH_FROM, TODAY);
        runSearch();
    }
    function markExtra() {
        var ip = form.login_ip.value;
        if (ipChip) {
            ipChip.hidden = ip === '';
            ipChip.textContent = ip ? 'IP ' + ip : '';
        }
    }
    function runSearch() {
        table.reload(queryWhere());
        syncWhenUi();
        markExtra();
    }
    function resetAll() {
        form.reset();
        form.login_ip.value = '';
        setDates('', '');
        whenSel.value = '';
        datesWrap.hidden = true;
        runSearch();
    }
    function rowHtml(d) {
        var name = d.username || L.unknown_admin;
        var badges = d.is_self ? '<span class="badge badge-ok">' + U.escape(L.current_account) + '</span>' : '';
        var meta = [];
        if (d.time_text) meta.push(U.escape(d.time_text));
        if (d.login_ip) {
            meta.push('<a href="#" class="log-ip js-ip" data-ip="' + U.escape(d.login_ip) + '">' + U.escape(d.login_ip) + '</a>'
                + (d.place_text ? '<span class="log-ip-place">' + U.escape(d.place_text) + '</span>' : ''));
        }
        if (d.device_text) meta.push(U.escape(d.device_text));
        return '<div class="entry-row-title-line"><span class="entry-row-title">' + U.escape(name) + '</span> ' + badges + '</div>'
            + '<div class="entry-row-meta">' + meta.join(' · ') + '</div>';
    }

    var table = U.table({
        el: '#login-log-table',
        countEl: countEl,
        url: '/admin/system/monitor/login-logs/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>' + U.escape(L.no_match) + '</p><p><button type="button" class="btn btn-muted btn-sm" id="login-log-empty-reset">' + U.escape(L.clear_filter) + '</button></p></div>';
            }
            return '<div class="list-empty"><p>' + U.escape(L.empty_login) + '</p><p class="muted">' + U.escape(L.empty_login_hint) + '</p></div>';
        },
        onDraw: function (_wrap, list) {
            markExtra();
            var reset = document.getElementById('login-log-empty-reset');
            if (reset) reset.addEventListener('click', resetAll);
        },
        cols: [
            {title: L.login_col, html: rowHtml}
        ]
    });
    syncWhenUi();
    markExtra();

    whenSel.addEventListener('change', applyWhen);
    form.start_time.addEventListener('change', runSearch);
    form.end_time.addEventListener('change', runSearch);
    form.mine.addEventListener('change', runSearch);
    form.q.addEventListener('input', function () {
        clearTimeout(typing);
        typing = setTimeout(runSearch, 400);
    });
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        clearTimeout(typing);
        runSearch();
    });
    U.on('#login-log-reset-btn', 'click', resetAll);
    U.on('#login-log-ip-chip', 'click', function () {
        form.login_ip.value = '';
        runSearch();
    });
    U.on('#login-log-table', 'click', function (e) {
        var a = e.target.closest('a.js-ip');
        if (!a) return;
        e.preventDefault();
        form.login_ip.value = a.getAttribute('data-ip') || '';
        runSearch();
    });
})();
</script>
@endpush
