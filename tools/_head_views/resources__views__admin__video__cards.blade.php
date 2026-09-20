fatal: path 'resources\views\admin\video\cards.blade.php' exists on disk, but not in 'HEAD'
@extends('admin.layouts.inner')
@section('title', $title)

@php
    $queues = $queues ?? ['all' => 0, 'unused' => 0, 'used' => 0, 'void' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
    $cardJsLang = [
        'status' => admin_t('ui.status'),
        'actions' => admin_t('ui.actions'),
        'edit' => admin_t('ui.edit'),
        'delete' => admin_t('ui.delete'),
        'fail' => admin_t('ui.fail'),
        'saved' => admin_t('ui.saved'),
        'created' => admin_t('ui.created'),
        'deleted' => admin_t('ui.deleted'),
        'close' => admin_t('ui.close'),
        'clear_filter' => admin_t('ui.clear_filter'),
        'selected_cards' => admin_t('ui.selected_cards', ['n' => '__N__']),
        'op_fail' => admin_t('manga.op_fail'),
        'op_ok' => admin_t('manga.op_ok'),
        'col_points' => admin_t('ui.col_points'),
        'card_unused' => admin_t('ui.card_unused'),
        'card_used' => admin_t('ui.card_used'),
        'card_void' => admin_t('ui.card_void'),
        'add_card' => admin_t('ui.add_card'),
        'gen_cards' => admin_t('ui.gen_cards'),
        'edit_card_points' => admin_t('ui.edit_card_points'),
        'col_card' => admin_t('ui.col_card'),
        'col_redeemer' => admin_t('ui.col_redeemer'),
        'col_redeemed_at' => admin_t('ui.col_redeemed_at'),
        'copy' => admin_t('ui.copy'),
        'restore' => admin_t('ui.restore'),
        'generate' => admin_t('ui.generate'),
        'points_n' => admin_t('ui.points_n', ['n' => '__N__']),
        'generated_at' => admin_t('ui.generated_at', ['time' => '__TIME__']),
        'today_at' => admin_t('ui.today_at', ['time' => '__TIME__']),
        'yesterday_at' => admin_t('ui.yesterday_at', ['time' => '__TIME__']),
        'member_hash' => admin_t('ui.member_hash', ['id' => '__ID__']),
        'empty_cards' => admin_t('ui.empty_cards'),
        'empty_cards_hint' => admin_t('ui.empty_cards_hint'),
        'no_match_cards' => admin_t('ui.no_match_cards'),
        'please_select_cards' => admin_t('ui.please_select_cards'),
        'points_min_one' => admin_t('ui.points_min_one'),
        'please_fill_count' => admin_t('ui.please_fill_count'),
        'generated_ok' => admin_t('ui.generated_ok'),
        'generated_n' => admin_t('ui.generated_n', ['n' => '__N__']),
        'generated_copy_hint' => admin_t('ui.generated_copy_hint'),
        'codes_copy_lead' => admin_t('ui.codes_copy_lead'),
        'copy_all' => admin_t('ui.copy_all'),
        'copied' => admin_t('ui.copied'),
        'copied_n' => admin_t('ui.copied_n', ['n' => '__N__']),
        'copied_code' => admin_t('ui.copied_code'),
        'nothing_to_copy' => admin_t('ui.nothing_to_copy'),
        'copy_fail' => admin_t('ui.copy_fail'),
        'card_restored' => admin_t('ui.card_restored'),
        'card_voided' => admin_t('ui.card_voided'),
        'confirm_batch_void_cards' => admin_t('ui.confirm_batch_void_cards'),
        'confirm_batch_del_cards' => admin_t('ui.confirm_batch_del_cards'),
        'confirm_void_card' => admin_t('ui.confirm_void_card', ['code' => '__CODE__']),
        'confirm_del_card' => admin_t('ui.confirm_del_card', ['code' => '__CODE__']),
    ];
@endphp

@section('plain')
<div class="card card-panel card-index">
    <div class="card-header">
        <span>{{ admin_t('ui.cards') }} <em id="card-count"></em></span>
        <div>
            <a class="btn btn-muted btn-sm" href="/admin/video/members">{{ admin_t('ui.members') }}</a>
            <button type="button" class="btn btn-muted btn-sm" id="card-add-btn">{{ admin_t('ui.add_card') }}</button>
            <button type="button" class="btn btn-sm" id="card-gen-btn">{{ admin_t('ui.gen_cards') }}</button>
        </div>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="card-search" onsubmit="return false;">
            <input type="hidden" name="queue">
            <input type="hidden" name="used_by">
            <input type="search" name="q" placeholder="{{ admin_t('ui.ph_card') }}" autocomplete="off" aria-label="{{ admin_t('ui.cards') }}">
            <button type="button" class="btn btn-sm" id="card-search-btn">{{ admin_t('ui.search') }}</button>
            <button type="reset" class="btn btn-muted btn-sm" id="card-reset-btn">{{ admin_t('ui.reset') }}</button>
        </form>
        <div class="queue-chips" id="card-queues">
            <button type="button" class="chip" data-queue="">{{ admin_t('ui.all') }}@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="queue" data-value="unused">{{ admin_t('ui.card_unused') }}@if($q('unused') > 0)<em>{{ $q('unused') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="queue" data-value="used">{{ admin_t('ui.card_used') }}@if($q('used') > 0)<em>{{ $q('used') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="queue" data-value="void">{{ admin_t('ui.card_void') }}@if($q('void') > 0)<em>{{ $q('void') }}</em>@endif</button>
        </div>
        <p class="muted recycle-lead">{{ admin_t('ui.cards_lead') }}</p>
        <div class="batch-bar" id="card-batch" hidden>
            <strong id="card-batch-count">{{ admin_t('ui.selected_cards', ['n' => 0]) }}</strong>
            <button type="button" class="btn btn-sm" id="card-batch-copy">{{ admin_t('ui.copy_cards') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="card-batch-void">{{ admin_t('ui.card_void') }}</button>
            <button type="button" class="btn btn-danger btn-sm" id="card-batch-del">{{ admin_t('ui.delete') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="card-batch-clear">{{ admin_t('ui.clear_selection') }}</button>
        </div>
        <div id="card-table"></div>
    </div>
</div>
<template id="card-dialog-tpl">
    <form class="admin-form">
        <input type="hidden" name="id">
        <label>{{ admin_t('ui.label_card_code') }}</label>
        <input type="text" name="code" placeholder="{{ admin_t('ui.ph_card_code') }}" maxlength="40" autocomplete="off">
        <p class="muted field-hint">{{ admin_t('ui.hint_card_code') }}</p>
        <label>{{ admin_t('ui.label_points') }}</label>
        <input type="number" name="points" value="100" min="1">
        <p class="muted field-hint">{{ admin_t('ui.hint_card_points') }}</p>
    </form>
</template>
<template id="card-gen-tpl">
    <form class="admin-form">
        <label>{{ admin_t('ui.label_card_count') }}</label>
        <input type="number" name="count" value="10" min="1" max="200">
        <p class="muted field-hint">{{ admin_t('ui.hint_card_count') }}</p>
        <label>{{ admin_t('ui.label_card_points_each') }}</label>
        <input type="number" name="points" value="100" min="1">
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($cardJsLang);
    var form = document.getElementById('card-search');
    var qs = new URLSearchParams(location.search);
    if (qs.get('used_by') && form.used_by) form.used_by.value = qs.get('used_by');
    var batchBar = document.getElementById('card-batch');
    var batchCount = document.getElementById('card-batch-count');
    var countEl = document.getElementById('card-count');

    function cleanWhere(data) {
        var out = {};
        Object.keys(data).forEach(function (k) { if (data[k] !== '') out[k] = data[k]; });
        return out;
    }
    function queryWhere() {
        return Object.assign({limit: 20}, cleanWhere(U.formData(form)));
    }
    function isFiltered(where) {
        return Object.keys(where || {}).some(function (k) { return k !== 'limit' && where[k] !== ''; });
    }
    function markChips() {
        var queue = form.queue.value;
        U.qa('#card-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = (key === '' && queue === '') || (key === 'queue' && queue === val);
            chip.classList.toggle('active', on);
        });
    }
    function applyQueue(key, value) {
        form.queue.value = key === 'queue' ? (value || '') : '';
        form.used_by.value = '';
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
    function copyText(text, okMsg) {
        text = String(text || '').trim();
        if (!text) { U.toast(L.nothing_to_copy, 'err'); return; }
        function ok() { U.toast(okMsg || L.copied, 'ok'); }
        function fail() { U.toast(L.copy_fail, 'err'); }
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(ok).catch(function () { fallback(); });
            return;
        }
        fallback();
        function fallback() {
            var ta = document.createElement('textarea');
            ta.value = text;
            ta.setAttribute('readonly', '');
            ta.style.position = 'fixed';
            ta.style.left = '-9999px';
            document.body.appendChild(ta);
            ta.select();
            try { document.execCommand('copy') ? ok() : fail(); } catch (e) { fail(); }
            ta.remove();
        }
    }
    function badgeHtml(state) {
        if (state === 'used') return '<span class="badge badge-ok">' + L.card_used + '</span>';
        if (state === 'void') return '<span class="badge badge-off">' + L.card_void + '</span>';
        return '<span class="badge badge-warn">' + L.card_unused + '</span>';
    }
    function titleHtml(d) {
        var meta = String(L.points_n || '').replace('__N__', String(parseInt(d.points, 10) || 0));
        if (d.created_at) meta += ' · ' + String(L.generated_at || '').replace('__TIME__', fmtTime(d.created_at));
        return '<div class="entry-row-title-line"><a class="entry-row-title card-code js-copy" href="#">' + U.escape(d.code || '') + '</a> ' + badgeHtml(d.state) + '</div>'
            + '<div class="entry-row-meta">' + U.escape(meta) + '</div>';
    }
    function userHtml(d) {
        if (d.state !== 'used' || !(parseInt(d.used_by, 10) > 0)) {
            return '<span class="muted">—</span>';
        }
        var name = d.member_name ? U.escape(d.member_name) : String(L.member_hash || '').replace('__ID__', U.escape(d.used_by));
        var extra = d.member_email ? '<div class="entry-row-meta">' + U.escape(d.member_email) + '</div>' : '';
        return '<a href="/admin/video/members?q=' + encodeURIComponent(d.used_by) + '">' + name + '</a>' + extra;
    }
    function statusHtml(d) {
        if (d.state === 'used') return U.status(true, L.card_used);
        if (d.state === 'void') return U.status(false, L.card_void);
        return '<span class="status status-warn">' + L.card_unused + '</span>';
    }

    var table = U.table({
        el: '#card-table',
        countEl: countEl,
        url: '/admin/video/cards/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>' + L.no_match_cards + '</p><p><button type="button" class="btn btn-muted btn-sm" id="card-empty-reset">' + L.clear_filter + '</button></p></div>';
            }
            return '<div class="list-empty"><p>' + L.empty_cards + '</p><p class="muted">' + L.empty_cards_hint + '</p><p><button type="button" class="btn btn-primary btn-sm" id="card-empty-gen">' + L.gen_cards + '</button></p></div>';
        },
        onDraw: function (_wrap, list) {
            var gen = document.getElementById('card-empty-gen');
            var reset = document.getElementById('card-empty-reset');
            if (gen) gen.addEventListener('click', openGenerate);
            if (reset) reset.addEventListener('click', function () { form.reset(); runSearch(); });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = String(L.selected_cards || '').replace('__N__', String(ids.length));
        },
        cols: [
            {check: true, width: 36},
            {title: L.col_card, html: titleHtml},
            {title: L.col_points, width: 72, html: function (d) { return U.escape(String(d.points == null ? '' : d.points)); }},
            {title: L.col_redeemer, html: userHtml},
            {title: L.status, width: 72, html: statusHtml},
            {title: L.col_redeemed_at, width: 120, html: function (d) { return d.state === 'used' ? fmtTime(d.used_at) : '—'; }},
            {title: L.actions, cls: 'actions', html: function (d) {
                var html = '<a href="#" class="btn-link js-copy">' + L.copy + '</a>';
                if (d.state === 'unused') {
                    html += '<a href="#" class="btn-link js-edit">' + L.edit_card_points + '</a><a href="#" class="btn-link js-void">' + L.card_void + '</a><a href="#" class="btn-link js-del">' + L.delete + '</a>';
                } else if (d.state === 'void') {
                    html += '<a href="#" class="btn-link js-on">' + L.restore + '</a><a href="#" class="btn-link js-del">' + L.delete + '</a>';
                }
                return html;
            }}
        ]
    });
    markChips();

    function openDialog(mode, row) {
        row = row || {};
        U.dialog({
            title: mode === 'edit' ? L.edit_card_points : L.add_card,
            content: document.getElementById('card-dialog-tpl').innerHTML,
            onOpen: function (body) {
                var codeInput = body.querySelector('[name=code]');
                U.fillForm(body.querySelector('form'), {
                    id: mode === 'edit' ? (row.id || '') : '',
                    code: row.code || '',
                    points: row.points == null ? 100 : row.points
                });
                if (mode === 'edit' && codeInput) {
                    codeInput.readOnly = true;
                    codeInput.placeholder = '';
                }
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (mode === 'edit') {
                    data.id = row.id;
                    data.code = row.code;
                } else {
                    delete data.id;
                }
                if (!data.points || parseInt(data.points, 10) < 1) { U.toast(L.points_min_one, 'err'); return false; }
                return U.post('/admin/video/cards/save', data).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return false; }
                    U.toast(mode === 'edit' ? L.saved : L.created, 'ok');
                    table.refresh();
                });
            }
        });
    }
    function openGenerate() {
        U.dialog({
            title: L.gen_cards,
            content: document.getElementById('card-gen-tpl').innerHTML,
            okText: L.generate,
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                var count = parseInt(data.count, 10) || 0;
                var points = parseInt(data.points, 10) || 0;
                if (count < 1) { U.toast(L.please_fill_count, 'err'); return false; }
                if (points < 1) { U.toast(L.points_min_one, 'err'); return false; }
                return U.post('/admin/video/cards/generate', {count: count, points: points}).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return false; }
                    table.refresh();
                    var codes = (res.data && res.data.codes) || [];
                    showCodes(codes, (res.msg || L.generated_ok) + L.generated_copy_hint);
                });
            }
        });
    }
    function showCodes(codes, title) {
        codes = Array.isArray(codes) ? codes : [];
        if (!codes.length) { U.toast(title || L.generated_ok, 'ok'); return; }
        var text = codes.join('\n');
        U.dialog({
            title: title || String(L.generated_n || '').replace('__N__', String(codes.length)),
            content: '<p class="muted field-hint">' + L.codes_copy_lead + '</p><textarea class="card-codes" readonly>' + U.escape(text) + '</textarea>',
            okText: L.copy_all,
            cancelText: L.close,
            onSave: function () {
                copyText(text, String(L.copied_n || '').replace('__N__', String(codes.length)));
                return false;
            }
        });
    }

    function selectedIds() { return table.selectedIds(); }
    function selectedCodes() {
        var ids = selectedIds();
        var map = {};
        ids.forEach(function (id) { map[String(id)] = true; });
        var codes = [];
        (table.rows() || []).forEach(function (row) {
            if (map[String(row.id)] && row.code) codes.push(row.code);
        });
        return codes;
    }
    function batch(action, value, confirmText) {
        var ids = selectedIds();
        if (!ids.length) { U.toast(L.please_select_cards, 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/cards/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.op_fail, 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || L.op_ok, 'ok');
        });
    }
    function setStatus(row, status) {
        U.post('/admin/video/cards/save', {id: row.id, status: status}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
            table.refresh();
            U.toast(status === 1 ? L.card_restored : L.card_voided, 'ok');
        });
    }

    U.on('#card-search-btn', 'click', runSearch);
    U.on('#card-reset-btn', 'click', function () { setTimeout(runSearch, 0); });
    U.on('#card-add-btn', 'click', function () { openDialog('add'); });
    U.on('#card-gen-btn', 'click', openGenerate);
    document.getElementById('card-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#card-batch-copy', 'click', function () {
        var codes = selectedCodes();
        if (!codes.length) { U.toast(L.please_select_cards, 'err'); return; }
        copyText(codes.join('\n'), String(L.copied_n || '').replace('__N__', String(codes.length)));
    });
    U.on('#card-batch-void', 'click', function () { batch('status', 0, L.confirm_batch_void_cards); });
    U.on('#card-batch-del', 'click', function () { batch('delete', '', L.confirm_batch_del_cards); });
    U.on('#card-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#card-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        if (a.getAttribute('href') && a.getAttribute('href').indexOf('/admin/video/members') === 0) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-copy')) copyText(row.code, L.copied_code);
        if (a.classList.contains('js-edit')) openDialog('edit', row);
        if (a.classList.contains('js-void')) {
            if (!U.confirm(String(L.confirm_void_card || '').replace('__CODE__', row.code || ''))) return;
            setStatus(row, 0);
        }
        if (a.classList.contains('js-on')) setStatus(row, 1);
        if (a.classList.contains('js-del')) {
            if (!U.confirm(String(L.confirm_del_card || '').replace('__CODE__', row.code || ''))) return;
            U.post('/admin/video/cards/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
                table.refresh();
                U.toast(L.deleted, 'ok');
            });
        }
    });
})();
</script>
@endpush