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
        <a class="btn btn-muted btn-sm" href="/admin/system/monitor/login-logs">登录日志</a>
    </div>
    <div class="card-body">
        <form class="filter-bar log-find" id="operate-log-search" onsubmit="return false;">
            <input type="hidden" name="mine">
            <input type="hidden" name="login_ip">
            <input type="search" name="q" placeholder="搜操作人、内容或模块" autocomplete="off" aria-label="搜索操作日志">
            <div class="field">
                <label for="operate-log-from">从</label>
                <input id="operate-log-from" type="date" name="start_time" aria-label="开始日期">
            </div>
            <div class="field">
                <label for="operate-log-to">到</label>
                <input id="operate-log-to" type="date" name="end_time" aria-label="结束日期">
            </div>
            <button type="button" class="btn btn-sm" id="operate-log-search-btn">查询</button>
            <button type="reset" class="btn btn-muted btn-sm" id="operate-log-reset-btn">重置</button>
        </form>
        <div class="queue-chips" id="operate-log-queues">
            <button type="button" class="chip" data-chip="all">全部</button>
            <button type="button" class="chip" data-chip="mine">我的</button>
            <button type="button" class="chip" data-chip="today">今天</button>
            <button type="button" class="chip" data-chip="yesterday">昨天</button>
            <button type="button" class="chip" data-chip="week">近7天</button>
            <button type="button" class="chip" data-chip="month">近30天</button>
            <button type="button" class="chip" data-chip="ip" id="operate-log-ip-chip" hidden></button>
        </div>
        <p class="muted recycle-lead">后台改数据会记一行。登录仍在「登录日志」。前台会员操作不记在这里。</p>
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
    var TODAY = @json($today);
    var YESTERDAY = @json($yesterday);
    var WEEK_FROM = @json($weekFrom);
    var MONTH_FROM = @json($monthFrom);

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
    function markChips() {
        var mine = form.mine.value === '1';
        var from = form.start_time.value;
        var to = form.end_time.value;
        var ip = form.login_ip.value;
        U.qa('#operate-log-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-chip') || '';
            var on = false;
            if (key === 'all') on = !mine && !from && !to && !ip;
            else if (key === 'mine') on = mine;
            else if (key === 'today') on = from === TODAY && to === TODAY;
            else if (key === 'yesterday') on = from === YESTERDAY && to === YESTERDAY;
            else if (key === 'week') on = from === WEEK_FROM && to === TODAY;
            else if (key === 'month') on = from === MONTH_FROM && to === TODAY;
            else if (key === 'ip') on = ip !== '';
            chip.classList.toggle('active', on);
        });
        if (ipChip) {
            ipChip.hidden = ip === '';
            ipChip.textContent = ip;
        }
    }
    function applyChip(key) {
        if (key === 'ip') {
            form.login_ip.value = '';
            runSearch();
            return;
        }
        form.mine.value = '';
        form.start_time.value = '';
        form.end_time.value = '';
        if (key === 'mine') form.mine.value = '1';
        else if (key === 'today') { form.start_time.value = TODAY; form.end_time.value = TODAY; }
        else if (key === 'yesterday') { form.start_time.value = YESTERDAY; form.end_time.value = YESTERDAY; }
        else if (key === 'week') { form.start_time.value = WEEK_FROM; form.end_time.value = TODAY; }
        else if (key === 'month') { form.start_time.value = MONTH_FROM; form.end_time.value = TODAY; }
        runSearch();
    }
    function runSearch() {
        table.reload(queryWhere());
        markChips();
    }
    function filterIp(ip) {
        form.login_ip.value = ip || '';
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
        url: '/admin/system/monitor/operate-logs/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>没有符合条件的记录。</p><p><button type="button" class="btn btn-muted btn-sm" id="operate-log-empty-reset">清除筛选</button></p></div>';
            }
            return '<div class="list-empty"><p>还没有操作记录。</p><p class="muted">后台改数据会记一行，写清谁改了什么。打开页面不会记。</p></div>';
        },
        onDraw: function (_wrap, list) {
            countEl.textContent = list.length ? '· ' + list.length : '';
            var reset = document.getElementById('operate-log-empty-reset');
            if (reset) reset.addEventListener('click', function () { form.reset(); form.mine.value = ''; form.login_ip.value = ''; runSearch(); });
        },
        cols: [
            {title: '操作', html: rowHtml}
        ]
    });
    markChips();

    U.on('#operate-log-search-btn', 'click', runSearch);
    U.on('#operate-log-reset-btn', 'click', function () {
        setTimeout(function () { form.mine.value = ''; form.login_ip.value = ''; runSearch(); }, 0);
    });
    U.on('#operate-log-queues', 'click', function (e) {
        var chip = e.target.closest('.chip');
        if (!chip) return;
        applyChip(chip.getAttribute('data-chip') || '');
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
        filterIp(a.getAttribute('data-ip') || '');
    });
})();
</script>
@endpush
