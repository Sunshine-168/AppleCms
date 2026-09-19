@extends('admin.layouts.inner')
@section('title', $title ?? admin_t('ui.searchwords'))

@php
    $queues = $queues ?? ['all' => 0, 'hot' => 0, 'once' => 0, 'today' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
    $swordJsLang = [
        'actions' => admin_t('ui.actions'),
        'delete' => admin_t('ui.delete'),
        'fail' => admin_t('ui.fail'),
        'saved' => admin_t('ui.saved'),
        'deleted' => admin_t('ui.deleted'),
        'clear_filter' => admin_t('ui.clear_filter'),
        'selected_rows' => admin_t('ui.selected_rows', ['n' => '__N__']),
        'op_fail' => admin_t('manga.op_fail'),
        'keyword' => admin_t('ui.keyword'),
        'add_searchword' => admin_t('ui.add_searchword'),
        'edit_hits' => admin_t('ui.edit_hits'),
        'col_recent' => admin_t('ui.col_recent'),
        'badge_hot' => admin_t('ui.badge_hot'),
        'badge_once' => admin_t('ui.badge_once'),
        'searched_n' => admin_t('ui.searched_n', ['n' => '__N__']),
        'empty_word' => admin_t('ui.empty_word'),
        'empty_searchwords' => admin_t('ui.empty_searchwords'),
        'empty_searchwords_hint' => admin_t('ui.empty_searchwords_hint'),
        'no_match_searchwords' => admin_t('ui.no_match_searchwords'),
        'go_front_search' => admin_t('ui.go_front_search'),
        'edit_searchword' => admin_t('ui.edit_searchword'),
        'add_hot_searchword' => admin_t('ui.add_hot_searchword'),
        'please_fill_keyword' => admin_t('ui.please_fill_keyword'),
        'added_ok' => admin_t('ui.added_ok'),
        'please_select_searchwords' => admin_t('ui.please_select_searchwords'),
        'confirm_batch_del_searchwords' => admin_t('ui.confirm_batch_del_searchwords'),
        'confirm_del_searchword' => admin_t('ui.confirm_del_searchword', ['name' => '__NAME__']),
        'today_at' => admin_t('ui.today_at', ['time' => '__TIME__']),
        'yesterday_at' => admin_t('ui.yesterday_at', ['time' => '__TIME__']),
    ];
@endphp

@section('plain')
<div class="card card-panel searchword-index">
    <div class="card-header">
        <span>{{ admin_t('ui.searchwords') }} <em id="sword-count"></em></span>
        <div>
            <button type="button" class="btn btn-sm" id="sword-add-btn">{{ admin_t('ui.add_searchword') }}</button>
            <a class="btn btn-muted btn-sm" href="/search" target="_blank" rel="noopener">{{ admin_t('ui.front_search') }}</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/synonyms">{{ admin_t('ui.synonyms') }}</a>
        </div>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="sword-search" onsubmit="return false;">
            <input type="hidden" name="hot">
            <input type="hidden" name="once">
            <input type="hidden" name="today">
            <input type="search" name="q" placeholder="{{ admin_t('ui.ph_searchword') }}" autocomplete="off" aria-label="{{ admin_t('ui.searchwords') }}">
            <button type="button" class="btn btn-sm" id="sword-search-btn">{{ admin_t('ui.search') }}</button>
            <button type="reset" class="btn btn-muted btn-sm" id="sword-reset-btn">{{ admin_t('ui.reset') }}</button>
        </form>
        <div class="queue-chips" id="sword-queues">
            <button type="button" class="chip" data-queue="">{{ admin_t('ui.all') }}@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="hot" data-value="1">{{ admin_t('ui.chip_hot') }}@if($q('hot') > 0)<em>{{ $q('hot') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="once" data-value="1">{{ admin_t('ui.chip_once') }}@if($q('once') > 0)<em>{{ $q('once') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="today" data-value="1">{{ admin_t('ui.chip_today') }}@if($q('today') > 0)<em>{{ $q('today') }}</em>@endif</button>
        </div>
        <p class="muted recycle-lead">{{ admin_t('ui.searchwords_lead') }}</p>
        <div class="batch-bar" id="sword-batch" hidden>
            <strong id="sword-batch-count">{{ admin_t('ui.selected_rows', ['n' => 0]) }}</strong>
            <button type="button" class="btn btn-danger btn-sm" id="sword-batch-del">{{ admin_t('ui.delete') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="sword-batch-clear">{{ admin_t('ui.clear_selection') }}</button>
        </div>
        <div id="sword-table"></div>
    </div>
</div>
<template id="sword-dialog-tpl">
    <form>
        <input type="hidden" name="id">
        <label for="sword-word">{{ admin_t('ui.keyword') }}</label>
        <input id="sword-word" type="text" name="word" maxlength="80" autocomplete="off">
        <label for="sword-hits">{{ admin_t('ui.label_hits') }}</label>
        <input id="sword-hits" type="number" name="hits" min="0" value="1">
        <p class="muted field-hint">{{ admin_t('ui.hint_searchword_hits') }}</p>
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($swordJsLang);
    var form = document.getElementById('sword-search');
    var batchBar = document.getElementById('sword-batch');
    var batchCount = document.getElementById('sword-batch-count');
    var countEl = document.getElementById('sword-count');
    var QUEUE_KEYS = ['hot', 'once', 'today'];

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
        var hot = form.hot.value;
        var once = form.once.value;
        var today = form.today.value;
        U.qa('#sword-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var on = false;
            if (key === '' && hot === '' && once === '' && today === '') on = true;
            else if (key === 'hot' && hot === '1') on = true;
            else if (key === 'once' && once === '1') on = true;
            else if (key === 'today' && today === '1') on = true;
            chip.classList.toggle('active', on);
        });
    }
    function applyQueue(key) {
        QUEUE_KEYS.forEach(function (k) { form[k].value = ''; });
        if (key && form[key]) form[key].value = '1';
        runSearch();
    }
    function runSearch() {
        table.reload(queryWhere());
        markChips();
    }
    function fmtTime(ts) {
        ts = parseInt(ts, 10) || 0;
        if (!ts) return '—';
        var d = new Date(ts * 1000);
        var now = new Date();
        var pad = function (n) { return n < 10 ? '0' + n : '' + n; };
        var hm = pad(d.getHours()) + ':' + pad(d.getMinutes());
        if (d.toDateString() === now.toDateString()) return String(L.today_at || '').replace('__TIME__', hm);
        var y = new Date(now);
        y.setDate(now.getDate() - 1);
        if (d.toDateString() === y.toDateString()) return String(L.yesterday_at || '').replace('__TIME__', hm);
        if (d.getFullYear() === now.getFullYear()) return pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + ' ' + hm;
        return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
    }
    function wordHtml(d) {
        var hits = parseInt(d.hits, 10) || 0;
        var badge = hits >= 10 ? ' <span class="badge badge-ok">' + L.badge_hot + '</span>' : (hits === 1 ? ' <span class="badge badge-off">' + L.badge_once + '</span>' : '');
        var href = d.search_url || ('/search?wd=' + encodeURIComponent(d.word || ''));
        return '<div class="entry-row-title-line"><a class="entry-row-title" href="' + U.escape(href) + '" target="_blank" rel="noopener">' + U.escape(d.word || L.empty_word) + '</a>' + badge + '</div>'
            + '<div class="entry-row-meta">' + String(L.searched_n || '').replace('__N__', U.escape(String(hits))) + '</div>';
    }

    var table = U.table({
        el: '#sword-table',
        queueKeys: QUEUE_KEYS,
        countEl: countEl,
        url: '/admin/video/searchwords/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>' + L.no_match_searchwords + '</p><p><button type="button" class="btn btn-muted btn-sm" id="sword-empty-reset">' + L.clear_filter + '</button></p></div>';
            }
            return '<div class="list-empty"><p>' + L.empty_searchwords + '</p><p class="muted">' + L.empty_searchwords_hint + '</p><p><a class="btn btn-muted btn-sm" href="/search" target="_blank" rel="noopener">' + L.go_front_search + '</a> <button type="button" class="btn btn-primary btn-sm" id="sword-empty-add">' + L.add_searchword + '</button></p></div>';
        },
        onDraw: function (wrap, list) {
            U.qa('tbody tr[data-idx]', wrap).forEach(function (tr) {
                var d = list[parseInt(tr.getAttribute('data-idx'), 10)];
                if (d && parseInt(d.hot, 10) === 1) tr.classList.add('is-hot');
            });
            var reset = document.getElementById('sword-empty-reset');
            if (reset) reset.addEventListener('click', function () {
                form.reset();
                QUEUE_KEYS.forEach(function (k) { form[k].value = ''; });
                runSearch();
            });
            var emptyAdd = document.getElementById('sword-empty-add');
            if (emptyAdd) emptyAdd.addEventListener('click', function () { openDialog({}); });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = String(L.selected_rows || '').replace('__N__', String(ids.length));
        },
        cols: [
            {check: true, width: 36},
            {title: L.keyword, html: wordHtml},
            {title: L.col_recent, width: 120, html: function (d) { return fmtTime(d.updated_at); }},
            {title: L.actions, cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-edit">' + L.edit_hits + '</a><a href="#" class="btn-link js-del">' + L.delete + '</a>';
            }}
        ]
    });
    markChips();

    function openDialog(row) {
        row = row || {};
        U.dialog({
            title: row.id ? L.edit_searchword : L.add_hot_searchword,
            content: document.getElementById('sword-dialog-tpl').innerHTML,
            onOpen: function (body) {
                U.fillForm(body.querySelector('form'), {
                    id: row.id || '',
                    word: row.word || '',
                    hits: row.hits == null || row.hits === '' ? 1 : row.hits
                });
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (!String(data.word || '').trim()) { U.toast(L.please_fill_keyword, 'err'); return false; }
                return U.post('/admin/video/searchwords/save', data).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return false; }
                    U.toast(row.id ? L.saved : L.added_ok, 'ok');
                    table.refresh();
                });
            }
        });
    }
    function batchDel() {
        var ids = table.selectedIds();
        if (!ids.length) { U.toast(L.please_select_searchwords, 'err'); return; }
        if (!U.confirm(L.confirm_batch_del_searchwords)) return;
        U.post('/admin/video/searchwords/batch', {ids: ids.join(','), action: 'delete'}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.op_fail, 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || L.deleted, 'ok');
        });
    }

    U.on('#sword-search-btn', 'click', runSearch);
    U.on('#sword-reset-btn', 'click', function () {
        setTimeout(function () {
            QUEUE_KEYS.forEach(function (k) { form[k].value = ''; });
            runSearch();
        }, 0);
    });
    document.getElementById('sword-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '');
    });
    U.on('#sword-add-btn', 'click', function () { openDialog({}); });
    U.on('#sword-batch-del', 'click', batchDel);
    U.on('#sword-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#sword-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        if (a.target === '_blank') return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        e.preventDefault();
        if (!row) return;
        if (a.classList.contains('js-edit')) openDialog(row);
        if (a.classList.contains('js-del')) {
            if (!U.confirm(String(L.confirm_del_searchword || '').replace('__NAME__', row.word || ''))) return;
            U.post('/admin/video/searchwords/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
                table.refresh();
                U.toast(L.deleted, 'ok');
            });
        }
    });
})();
</script>
@endpush
