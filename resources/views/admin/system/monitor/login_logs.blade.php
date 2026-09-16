@extends('admin.layouts.inner')
@section('title', admin_t('page.login_logs'))

@php
    $currentName = (string) ($currentName ?? '');
    $today = $today ?? now()->toDateString();
    $yesterday = $yesterday ?? now()->subDay()->toDateString();
    $weekFrom = $weekFrom ?? now()->subDays(6)->toDateString();
    $monthFrom = $monthFrom ?? now()->subDays(29)->toDateString();
@endphp

@section('plain')
<div class="card card-panel log-index login-log-index">
    <div class="card-header">
        <span>登录日志 <em id="login-log-count"></em></span>
        <a class="btn btn-muted btn-sm" href="/admin/user">管理员</a>
    </div>
    <div class="card-body">
        <form class="filter-bar log-find" id="login-log-search" onsubmit="return false;">
            <input type="hidden" name="mine">
            <input type="hidden" name="login_ip">
            <input type="search" name="q" placeholder="搜管理员或 IP" autocomplete="off" aria-label="搜索登录日志">
            <div class="field">
                <label for="login-log-from">从</label>
                <input id="login-log-from" type="date" name="start_time" aria-label="开始日期">
            </div>
            <div class="field">
                <label for="login-log-to">到</label>
                <input id="login-log-to" type="date" name="end_time" aria-label="结束日期">
            </div>
            <button type="button" class="btn btn-sm" id="login-log-search-btn">查询</button>
            <button type="reset" class="btn btn-muted btn-sm" id="login-log-reset-btn">重置</button>
        </form>
        <div class="queue-chips" id="login-log-queues">
            <button type="button" class="chip" data-chip="all">全部</button>
            <button type="button" class="chip" data-chip="mine">我的</button>
            <button type="button" class="chip" data-chip="today">今天</button>
            <button type="button" class="chip" data-chip="yesterday">昨天</button>
            <button type="button" class="chip" data-chip="week">近7天</button>
            <button type="button" class="chip" data-chip="month">近30天</button>
            <button type="button" class="chip" data-chip="ip" id="login-log-ip-chip" hidden></button>
        </div>
        <p class="muted recycle-lead">谁在什么时候从哪登录过后台。点 IP 只看这个地址。打开页面不会记。</p>
        <div id="login-log-table"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var form = document.getElementById('login-log-search');
    var countEl = document.getElementById('login-log-count');
    var ipChip = document.getElementById('login-log-ip-chip');
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
        U.qa('#login-log-queues .chip').forEach(function (chip) {
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
        url: '/admin/system/monitor/login-logs/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>没有符合条件的记录。</p><p><button type="button" class="btn btn-muted btn-sm" id="login-log-empty-reset">清除筛选</button></p></div>';
            }
            return '<div class="list-empty"><p>还没有登录记录。</p><p class="muted">登录后台会出现在这里，并写清是谁、从哪、用什么设备。</p></div>';
        },
        onDraw: function (_wrap, list) {
            countEl.textContent = list.length ? '· ' + list.length : '';
            var reset = document.getElementById('login-log-empty-reset');
            if (reset) reset.addEventListener('click', function () { form.reset(); form.mine.value = ''; form.login_ip.value = ''; runSearch(); });
        },
        cols: [
            {title: '登录', html: rowHtml}
        ]
    });
    markChips();

    U.on('#login-log-search-btn', 'click', runSearch);
    U.on('#login-log-reset-btn', 'click', function () {
        setTimeout(function () { form.mine.value = ''; form.login_ip.value = ''; runSearch(); }, 0);
    });
    U.on('#login-log-queues', 'click', function (e) {
        var chip = e.target.closest('.chip');
        if (!chip) return;
        applyChip(chip.getAttribute('data-chip') || '');
    });
    U.on('#login-log-table', 'click', function (e) {
        var a = e.target.closest('a.js-ip');
        if (!a) return;
        e.preventDefault();
        filterIp(a.getAttribute('data-ip') || '');
    });
})();
</script>
@endpush
