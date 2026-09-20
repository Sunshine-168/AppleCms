@extends('admin.layouts.inner')
@section('title', admin_t('page.operate_logs'))

@php
    $today = $today ?? now()->toDateString();
    $yesterday = $yesterday ?? now()->subDay()->toDateString();
    $weekFrom = $weekFrom ?? now()->subDays(6)->toDateString();
    $monthFrom = $monthFrom ?? now()->subDays(29)->toDateString();
@endphp

@section('plain')
<div class="card card-panel log-index operate-log-index">
    <div class="card-header">
        <span>操作日志 <em id="operate-log-count"></em></span>
    </div>
    <div class="card-body">
        @include('admin.partials.log-tabs', ['tab' => 'operate'])
        @include('admin.partials.log-filters', ['kind' => 'operate'])
        <p class="muted recycle-lead">后台改数据会记一行。打开页面不会记。前台会员操作不记。</p>
        <div id="operate-log-table"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var form = document.getElementById('operate-log-search');
    var countEl = document.getElementById('operate-log-count');
    var ipChip = document.getElementById('operate-log-ip-chip');
    var whenSel = document.getElementById('operate-log-when');
    var datesWrap = document.getElementById('operate-log-dates');
    var TODAY = @json($today);
    var YESTERDAY = @json($yesterday);
    var WEEK_FROM = @json($weekFrom);
    var MONTH_FROM = @json($monthFrom);
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
        var name = d.username || '未知管理员';
        var badges = d.is_self ? '<span class="badge badge-ok">当前账号</span>' : '';
        var summary = d.summary || d.title || '做了一次操作';
        var meta = [];
        if (d.module_text) meta.push(U.escape(d.module_text));
        if (d.time_text) meta.push(U.escape(d.time_text));
        if (d.login_ip) {
            meta.push('<a href="#" class="log-ip js-ip" data-ip="' + U.escape(d.login_ip) + '">' + U.escape(d.login_ip) + '</a>'
                + (d.place_text ? '<span class="log-ip-place">' + U.escape(d.place_text) + '</span>' : ''));
        }
        var extra = d.extra_text ? '<div class="log-extra">' + U.escape(d.extra_text) + '</div>' : '';
        var toggle = extra ? ' <button type="button" class="log-extra-toggle js-extra" aria-expanded="false">详情</button>' : '';
        return '<div class="entry-row-title-line"><span class="entry-row-title">' + U.escape(summary) + '</span> ' + badges + toggle + '</div>'
            + '<div class="entry-row-meta">' + U.escape(name) + (meta.length ? ' · ' + meta.join(' · ') : '') + '</div>'
            + extra;
    }

    var table = U.table({
        el: '#operate-log-table',
        countEl: countEl,
        url: '/admin/system/monitor/operate-logs/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>没有符合条件的记录。</p><p><button type="button" class="btn btn-muted btn-sm" id="operate-log-empty-reset">清除筛选</button></p></div>';
            }
            return '<div class="list-empty"><p>还没有操作记录。</p><p class="muted">后台改数据会记一行，写清谁改了什么。打开页面不会记。</p></div>';
        },
        onDraw: function (_wrap, list) {
            markExtra();
            var reset = document.getElementById('operate-log-empty-reset');
            if (reset) reset.addEventListener('click', resetAll);
        },
        cols: [
            {title: AdminUi.t('actions'), html: rowHtml}
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
    U.on('#operate-log-reset-btn', 'click', resetAll);
    U.on('#operate-log-ip-chip', 'click', function () {
        form.login_ip.value = '';
        runSearch();
    });
    U.on('#operate-log-table', 'click', function (e) {
        var extraBtn = e.target.closest('button.js-extra');
        if (extraBtn) {
            var cell = extraBtn.closest('td') || extraBtn.closest('tr');
            if (cell) {
                var open = cell.classList.toggle('is-open');
                extraBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
            }
            return;
        }
        var a = e.target.closest('a.js-ip');
        if (!a) return;
        e.preventDefault();
        form.login_ip.value = a.getAttribute('data-ip') || '';
        runSearch();
    });
})();
</script>
@endpush
