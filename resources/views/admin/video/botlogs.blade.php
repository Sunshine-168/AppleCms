@extends('admin.layouts.inner')
@section('title', $title)

@php
    $queues = $queues ?? ['all' => 0, 'today' => 0, 'baidu' => 0, 'google' => 0, 'bing' => 0, 'other' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
    $botJsLang = [
        'spider_default' => admin_t('ui.spider_default'),
        'no_match' => admin_t('ui.no_match'),
        'clear_filter' => admin_t('ui.clear_filter'),
        'empty_botlogs' => admin_t('ui.empty_botlogs'),
        'empty_botlogs_hint' => admin_t('ui.empty_botlogs_hint'),
        'open_site_front' => admin_t('ui.open_site_front'),
        'selected_n' => admin_t('ui.selected_rows'),
        'please_select_botlogs' => admin_t('ui.please_select_botlogs'),
        'confirm_batch_del_botlogs' => admin_t('ui.confirm_batch_del_botlogs'),
        'confirm_del_botlog' => admin_t('ui.confirm_del_botlog'),
        'op_fail' => admin_t('ui.fail'),
        'deleted' => admin_t('ui.deleted'),
    ];
@endphp

@section('plain')
<div class="card card-panel botlog-index list-desk">
    <div class="card-header">
        <span>{{ admin_t('ui.botlogs') }} <em id="botlog-count"></em></span>
        <div>
            <a class="btn btn-muted btn-sm" href="/admin/stats/spiders">{{ admin_t('ui.spider_stats') }}</a>
            <a class="btn btn-muted btn-sm" href="/admin/stats/logs?visitor=spider">{{ admin_t('ui.visit_detail') }}</a>
            <a class="btn btn-muted btn-sm" href="/admin/system/runtime?desk=access&view=logs">{{ admin_t('ui.access_risk') }}</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/push">{{ admin_t('ui.search_push') }}</a>
            <a class="btn btn-muted btn-sm" href="/robots.txt" target="_blank" rel="noopener">robots</a>
        </div>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="botlog-search" onsubmit="return false;">
            <input type="hidden" name="today">
            <input type="hidden" name="engine">
            <input type="search" name="q" placeholder="{{ admin_t('ui.ph_botlog') }}" autocomplete="off" aria-label="{{ admin_t('ui.aria_search_botlogs') }}">
            <button type="button" class="btn btn-sm" id="botlog-search-btn">{{ admin_t('ui.search') }}</button>
            <button type="reset" class="btn btn-muted btn-sm" id="botlog-reset-btn">{{ admin_t('ui.reset') }}</button>
        </form>
        <div class="queue-chips" id="botlog-queues">
            <button type="button" class="chip" data-queue="">{{ admin_t('ui.all') }}@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="today" data-value="1">{{ admin_t('ui.today_chip') }}@if($q('today') > 0)<em>{{ $q('today') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="engine" data-value="baidu">{{ admin_t('ui.engine_baidu') }}@if($q('baidu') > 0)<em>{{ $q('baidu') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="engine" data-value="google">Google @if($q('google') > 0)<em>{{ $q('google') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="engine" data-value="bing">Bing @if($q('bing') > 0)<em>{{ $q('bing') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="engine" data-value="other">{{ admin_t('ui.engine_other') }}@if($q('other') > 0)<em>{{ $q('other') }}</em>@endif</button>
        </div>
        <p class="muted recycle-lead">{{ admin_t('ui.botlogs_lead_before') }}<a href="/admin/system/runtime?desk=access&view=logs">{{ admin_t('ui.access_risk') }}</a>{{ admin_t('ui.botlogs_lead_after_access') }}<a href="/admin/stats/spiders">{{ admin_t('ui.spider_stats') }}</a>{{ admin_t('ui.botlogs_lead_after_spiders') }}<a href="/admin/stats/logs?visitor=spider">{{ admin_t('ui.visit_detail') }}</a>{{ admin_t('ui.botlogs_lead_after_detail') }}<strong>{{ admin_t('ui.botlogs_lead_strong') }}</strong>{{ admin_t('ui.botlogs_lead_tail') }}</p>
        <div class="batch-bar" id="botlog-batch" hidden>
            <strong id="botlog-batch-count">{{ admin_t('ui.selected_n', ['n' => 0]) }}</strong>
            <button type="button" class="btn btn-danger btn-sm" id="botlog-batch-del">{{ admin_t('ui.delete') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="botlog-batch-clear">{{ admin_t('ui.clear_selection') }}</button>
        </div>
        <div id="botlog-table"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($botJsLang, JSON_UNESCAPED_UNICODE);
    var form = document.getElementById('botlog-search');
    var batchBar = document.getElementById('botlog-batch');
    var batchCount = document.getElementById('botlog-batch-count');
    var countEl = document.getElementById('botlog-count');
    var QUEUE_KEYS = ['today', 'engine'];

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
        var today = form.today.value;
        var engine = form.engine.value;
        U.qa('#botlog-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = false;
            if (key === '' && today === '' && engine === '') on = true;
            else if (key === 'today' && engine === '' && today === val) on = true;
            else if (key === 'engine' && today === '' && engine === val) on = true;
            chip.classList.toggle('active', on);
        });
    }
    function applyQueue(key, value) {
        QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
        if (key && form[key]) form[key].value = value || '1';
        runSearch();
    }
    function runSearch() {
        table.reload(queryWhere());
        markChips();
    }
    function spiderHtml(d) {
        var html = '<strong>' + U.escape(d.spider_label || L.spider_default) + '</strong>';
        if (d.group_label) html += '<div class="muted">' + U.escape(d.group_label) + '</div>';
        return html;
    }
    function urlHtml(d) {
        var full = d.url || '';
        var short = d.url_short || full;
        if (!full) return '<span class="muted">—</span>';
        if (!/^https?:\/\//i.test(full)) return U.escape(short);
        return '<a class="botlog-url" href="' + U.escape(full) + '" target="_blank" rel="noopener">' + U.escape(short) + '</a>';
    }

    var table = U.table({
        el: '#botlog-table',
        queueKeys: QUEUE_KEYS,
        countEl: countEl,
        url: '/admin/video/botlogs/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>' + L.no_match + '</p><p><button type="button" class="btn btn-muted btn-sm" id="botlog-empty-reset">' + L.clear_filter + '</button></p></div>';
            }
            return '<div class="list-empty"><p>' + L.empty_botlogs + '</p><p class="muted">' + L.empty_botlogs_hint + '</p><p><a class="btn btn-muted btn-sm" href="/" target="_blank" rel="noopener">' + L.open_site_front + '</a></p></div>';
        },
        onDraw: function (_wrap, list) {
            var reset = document.getElementById('botlog-empty-reset');
            if (reset) reset.addEventListener('click', function () {
                form.reset();
                QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
                runSearch();
            });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = String(L.selected_n || '').replace(':n', String(ids.length));
        },
        cols: [
            {check: true, width: 36},
            {title: AdminUi.t('time'), width: 150, html: function (d) { return U.escape(d.created_at_text || ''); }},
            {title: AdminUi.t('spider'), width: 120, html: spiderHtml},
            {title: AdminUi.t('address'), html: urlHtml},
            {title: 'IP', width: 130, html: function (d) { return U.escape(d.ip || ''); }},
            {title: AdminUi.t('identifier'), html: function (d) { return '<span class="muted" title="' + U.escape(d.ua || '') + '">' + U.escape(d.ua_short || d.ua || '') + '</span>'; }},
            {title: AdminUi.t('actions'), cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-del">' + AdminUi.t('delete') + '</a>';
            }}
        ]
    });
    markChips();

    function selectedIds() { return table.selectedIds(); }
    function batchDel() {
        var ids = selectedIds();
        if (!ids.length) { U.toast(L.please_select_botlogs, 'err'); return; }
        if (!U.confirm(String(L.confirm_batch_del_botlogs || '').replace(':n', String(ids.length)))) return;
        U.post('/admin/video/botlogs/batch', {ids: ids.join(','), action: 'delete'}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.op_fail, 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || L.deleted, 'ok');
        });
    }

    U.on('#botlog-search-btn', 'click', runSearch);
    U.on('#botlog-reset-btn', 'click', function () { setTimeout(function () {
        QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
        runSearch();
    }, 0); });
    document.getElementById('botlog-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#botlog-batch-del', 'click', batchDel);
    U.on('#botlog-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#botlog-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        var href = a.getAttribute('href') || '';
        if (href && href !== '#' && href.indexOf('javascript:') !== 0) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-del')) {
            if (!U.confirm(L.confirm_del_botlog)) return;
            U.post('/admin/video/botlogs/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.op_fail, 'err'); return; }
                table.refresh();
                U.toast(L.deleted, 'ok');
            });
        }
    });
})();
</script>
@endpush
