fatal: path 'resources\views\admin\video\invites.blade.php' exists on disk, but not in 'HEAD'
@extends('admin.layouts.inner')
@section('title', $title)

@php
    $queues = $queues ?? ['all' => 0, 'unused' => 0, 'used' => 0, 'void' => 0, 'today' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
    $inviteJsLang = [
        'status' => admin_t('ui.status'),
        'actions' => admin_t('ui.actions'),
        'delete' => admin_t('ui.delete'),
        'fail' => admin_t('ui.fail'),
        'deleted' => admin_t('ui.deleted'),
        'close' => admin_t('ui.close'),
        'clear_filter' => admin_t('ui.clear_filter'),
        'selected_rows' => admin_t('ui.selected_rows', ['n' => '__N__']),
        'op_fail' => admin_t('manga.op_fail'),
        'op_ok' => admin_t('manga.op_ok'),
        'card_unused' => admin_t('ui.card_unused'),
        'invite_used' => admin_t('ui.invite_used'),
        'card_void' => admin_t('ui.card_void'),
        'gen_cards' => admin_t('ui.gen_cards'),
        'generate' => admin_t('ui.generate'),
        'copy' => admin_t('ui.copy'),
        'restore' => admin_t('ui.restore'),
        'members' => admin_t('ui.members'),
        'col_invite' => admin_t('ui.col_invite'),
        'copy_invites' => admin_t('ui.copy_invites'),
        'points_n' => admin_t('ui.points_n', ['n' => '__N__']),
        'empty_invites' => admin_t('ui.empty_invites'),
        'empty_invites_hint' => admin_t('ui.empty_invites_hint'),
        'no_match_invites' => admin_t('ui.no_match_invites'),
        'please_select_invites' => admin_t('ui.please_select_invites'),
        'please_fill_qty' => admin_t('ui.please_fill_qty'),
        'generated_ok' => admin_t('ui.generated_ok'),
        'generated_invites_n' => admin_t('ui.generated_invites_n', ['n' => '__N__']),
        'generated_invite_hint' => admin_t('ui.generated_invite_hint'),
        'invite_codes_copy_lead' => admin_t('ui.invite_codes_copy_lead'),
        'copy_all' => admin_t('ui.copy_all'),
        'copied' => admin_t('ui.copied'),
        'copied_invites_n' => admin_t('ui.copied_invites_n', ['n' => '__N__']),
        'copied_invite' => admin_t('ui.copied_invite'),
        'nothing_to_copy' => admin_t('ui.nothing_to_copy'),
        'copy_fail' => admin_t('ui.copy_fail'),
        'card_restored' => admin_t('ui.card_restored'),
        'card_voided' => admin_t('ui.card_voided'),
        'confirm_batch_void_invites' => admin_t('ui.confirm_batch_void_invites'),
        'confirm_batch_del_invites' => admin_t('ui.confirm_batch_del_invites'),
        'confirm_void_invite' => admin_t('ui.confirm_void_invite', ['code' => '__CODE__']),
        'confirm_del_invite' => admin_t('ui.confirm_del_invite', ['code' => '__CODE__']),
        'inviter_meta' => admin_t('ui.inviter_meta', ['name' => '__NAME__']),
        'registrant_meta' => admin_t('ui.registrant_meta', ['name' => '__NAME__']),
        'system_owner' => admin_t('ui.system_owner'),
        'member_hash' => admin_t('ui.member_hash', ['id' => '__ID__']),
    ];
@endphp

@section('plain')
<div class="card card-panel invite-index">
    <div class="card-header">
        <span>{{ admin_t('ui.invites') }} <em id="invite-count"></em></span>
        <div>
            <a class="btn btn-muted btn-sm" href="/admin/video/members">{{ admin_t('ui.members') }}</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/settings?tab=interact">{{ admin_t('ui.register_settings') }}</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/cards">{{ admin_t('ui.cards') }}</a>
            <button type="button" class="btn btn-sm" id="invite-gen-btn">{{ admin_t('ui.gen_cards') }}</button>
        </div>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="invite-search" onsubmit="return false;">
            <input type="hidden" name="queue">
            <input type="hidden" name="today">
            <input type="search" name="q" placeholder="{{ admin_t('ui.ph_invite') }}" autocomplete="off" aria-label="{{ admin_t('ui.invites') }}">
            <button type="button" class="btn btn-sm" id="invite-search-btn">{{ admin_t('ui.search') }}</button>
            <button type="reset" class="btn btn-muted btn-sm" id="invite-reset-btn">{{ admin_t('ui.reset') }}</button>
        </form>
        <div class="queue-chips" id="invite-queues">
            <button type="button" class="chip" data-queue="">{{ admin_t('ui.all') }}@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="queue" data-value="unused">{{ admin_t('ui.card_unused') }}@if($q('unused') > 0)<em>{{ $q('unused') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="queue" data-value="used">{{ admin_t('ui.invite_used') }}@if($q('used') > 0)<em>{{ $q('used') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="queue" data-value="void">{{ admin_t('ui.card_void') }}@if($q('void') > 0)<em>{{ $q('void') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="today" data-value="1">{{ admin_t('ui.today') }}@if($q('today') > 0)<em>{{ $q('today') }}</em>@endif</button>
        </div>
        <p class="muted recycle-lead">{{ admin_t('ui.invites_lead') }}</p>
        <div class="batch-bar" id="invite-batch" hidden>
            <strong id="invite-batch-count">{{ admin_t('ui.selected_rows', ['n' => 0]) }}</strong>
            <button type="button" class="btn btn-sm" id="invite-batch-copy">{{ admin_t('ui.copy_invites') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="invite-batch-void">{{ admin_t('ui.card_void') }}</button>
            <button type="button" class="btn btn-danger btn-sm" id="invite-batch-del">{{ admin_t('ui.delete') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="invite-batch-clear">{{ admin_t('ui.clear_selection') }}</button>
        </div>
        <div id="invite-table"></div>
    </div>
</div>
<template id="invite-gen-tpl">
    <form class="admin-form">
        <label>{{ admin_t('ui.label_invite_count') }}</label>
        <input type="number" name="count" value="10" min="1" max="200">
        <p class="muted field-hint">{{ admin_t('ui.hint_invite_count') }}</p>
        <label>{{ admin_t('ui.label_points') }}</label>
        <input type="number" name="points" value="0" min="0">
        <p class="muted field-hint">{{ admin_t('ui.hint_invite_points') }}</p>
        <label>{{ admin_t('ui.label_inviter_id') }}</label>
        <input type="number" name="member_id" value="0" min="0">
        <p class="muted field-hint">{{ admin_t('ui.hint_inviter_id') }}</p>
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($inviteJsLang);
    var form = document.getElementById('invite-search');
    var batchBar = document.getElementById('invite-batch');
    var batchCount = document.getElementById('invite-batch-count');
    var countEl = document.getElementById('invite-count');
    var QUEUE_KEYS = ['today'];

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
        var today = form.today.value;
        U.qa('#invite-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = false;
            if (key === '' && queue === '' && today === '') on = true;
            else if (key === 'queue' && today === '' && queue === val) on = true;
            else if (key === 'today' && today === val) on = true;
            chip.classList.toggle('active', on);
        });
    }
    function applyQueue(key, value) {
        QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
        form.queue.value = '';
        if (key === 'queue') form.queue.value = value || '';
        else if (key && form[key]) form[key].value = value || '1';
        runSearch();
    }
    function runSearch() {
        table.reload(queryWhere());
        markChips();
    }
    function copyText(text, okMsg) {
        text = String(text || '').trim();
        if (!text) { U.toast(L.nothing_to_copy, 'err'); return; }
        function ok() { U.toast(okMsg || L.copied, 'ok'); }
        function fail() { U.toast(L.copy_fail, 'err'); }
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(ok).catch(function () {
                fallback();
            });
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
            try {
                document.execCommand('copy') ? ok() : fail();
            } catch (e) {
                fail();
            }
            ta.remove();
        }
    }
    function badgeHtml(state) {
        if (state === 'used') return '<span class="badge badge-ok">' + L.invite_used + '</span>';
        if (state === 'void') return '<span class="badge badge-off">' + L.card_void + '</span>';
        return '<span class="badge badge-warn">' + L.card_unused + '</span>';
    }
    function memberHref(id) {
        id = parseInt(id, 10) || 0;
        if (id > 0) return '/admin/video/members?q=' + encodeURIComponent(id);
        return '/admin/video/members';
    }
    function ownerName(d) {
        if (d.owner_name) return d.owner_name;
        if (parseInt(d.member_id, 10) > 0) {
            return String(L.member_hash || '').replace('__ID__', String(d.member_id));
        }
        return L.system_owner;
    }
    function titleHtml(d) {
        var owner = ownerName(d);
        var meta = [String(L.inviter_meta || '').replace('__NAME__', owner)];
        if (d.state === 'used') {
            var regName = d.used_name || String(L.member_hash || '').replace('__ID__', String(d.used_by));
            meta.push(String(L.registrant_meta || '').replace('__NAME__', regName));
        }
        meta.push(String(L.points_n || '').replace('__N__', String(parseInt(d.points, 10) || 0)));
        if (d.created_at_text) meta.push(d.created_at_text);
        return '<div class="entry-row-title-line"><a class="entry-row-title invite-code js-copy" href="#">' + U.escape(d.code || '') + '</a> ' + badgeHtml(d.state) + '</div>'
            + '<div class="entry-row-meta">' + U.escape(meta.join(' · ')) + '</div>';
    }
    function statusHtml(d) {
        if (d.state === 'used') return U.status(true, L.invite_used);
        if (d.state === 'void') return U.status(false, L.card_void);
        return '<span class="status status-warn">' + L.card_unused + '</span>';
    }
    function memberLinkId(d) {
        if (parseInt(d.used_by, 10) > 0) return d.used_by;
        if (parseInt(d.member_id, 10) > 0) return d.member_id;
        return 0;
    }

    var table = U.table({
        el: '#invite-table',
        queueKeys: QUEUE_KEYS,
        countEl: countEl,
        url: '/admin/video/invites/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>' + L.no_match_invites + '</p><p><button type="button" class="btn btn-muted btn-sm" id="invite-empty-reset">' + L.clear_filter + '</button></p></div>';
            }
            return '<div class="list-empty"><p>' + L.empty_invites + '</p><p class="muted">' + L.empty_invites_hint + '</p><p><button type="button" class="btn btn-primary btn-sm" id="invite-empty-gen">' + L.gen_cards + '</button></p></div>';
        },
        onDraw: function (wrap, list) {
            U.qa('tbody tr[data-idx]', wrap).forEach(function (tr) {
                var d = list[parseInt(tr.getAttribute('data-idx'), 10)];
                if (d && d.state === 'void') tr.classList.add('is-off');
            });
            var gen = document.getElementById('invite-empty-gen');
            var reset = document.getElementById('invite-empty-reset');
            if (gen) gen.addEventListener('click', openGenerate);
            if (reset) reset.addEventListener('click', function () {
                form.reset();
                QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
                form.queue.value = '';
                runSearch();
            });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = String(L.selected_rows || '').replace('__N__', String(ids.length));
        },
        cols: [
            {check: true, width: 36},
            {title: L.col_invite, html: titleHtml},
            {title: L.status, width: 72, html: statusHtml},
            {title: L.actions, cls: 'actions', html: function (d) {
                var html = '<a href="#" class="btn-link js-copy">' + L.copy + '</a>';
                if (d.state === 'unused') {
                    html += '<a href="#" class="btn-link js-void">' + L.card_void + '</a><a href="#" class="btn-link js-del">' + L.delete + '</a>';
                } else if (d.state === 'void') {
                    html += '<a href="#" class="btn-link js-on">' + L.restore + '</a><a href="#" class="btn-link js-del">' + L.delete + '</a>';
                }
                html += '<a class="btn-link" href="' + memberHref(memberLinkId(d)) + '">' + L.members + '</a>';
                return html;
            }}
        ]
    });
    markChips();

    function openGenerate() {
        U.dialog({
            title: L.gen_cards,
            content: document.getElementById('invite-gen-tpl').innerHTML,
            okText: L.generate,
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                var count = parseInt(data.count, 10) || 0;
                var points = parseInt(data.points, 10);
                if (isNaN(points) || points < 0) points = 0;
                var memberId = parseInt(data.member_id, 10) || 0;
                if (count < 1) { U.toast(L.please_fill_qty, 'err'); return false; }
                return U.post('/admin/video/invites/generate', {count: count, points: points, member_id: memberId}).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return false; }
                    table.refresh();
                    showCodes((res.data && res.data.codes) || [], (res.msg || L.generated_ok) + L.generated_invite_hint);
                });
            }
        });
    }
    function showCodes(codes, title) {
        codes = Array.isArray(codes) ? codes : [];
        if (!codes.length) { U.toast(title || L.generated_ok, 'ok'); return; }
        var text = codes.join('\n');
        U.dialog({
            title: title || String(L.generated_invites_n || '').replace('__N__', String(codes.length)),
            content: '<p class="muted field-hint">' + L.invite_codes_copy_lead + '</p><textarea class="invite-codes" readonly>' + U.escape(text) + '</textarea>',
            okText: L.copy_all,
            cancelText: L.close,
            onSave: function () {
                copyText(text, String(L.copied_invites_n || '').replace('__N__', String(codes.length)));
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
        if (!ids.length) { U.toast(L.please_select_invites, 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/invites/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.op_fail, 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || L.op_ok, 'ok');
        });
    }
    function setStatus(row, status) {
        U.post('/admin/video/invites/save', {id: row.id, status: status}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
            table.refresh();
            U.toast(status === 1 ? L.card_restored : L.card_voided, 'ok');
        });
    }

    U.on('#invite-search-btn', 'click', runSearch);
    U.on('#invite-reset-btn', 'click', function () { setTimeout(runSearch, 0); });
    U.on('#invite-gen-btn', 'click', openGenerate);
    document.getElementById('invite-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#invite-batch-copy', 'click', function () {
        var codes = selectedCodes();
        if (!codes.length) { U.toast(L.please_select_invites, 'err'); return; }
        copyText(codes.join('\n'), String(L.copied_invites_n || '').replace('__N__', String(codes.length)));
    });
    U.on('#invite-batch-void', 'click', function () { batch('status', 0, L.confirm_batch_void_invites); });
    U.on('#invite-batch-del', 'click', function () { batch('delete', '', L.confirm_batch_del_invites); });
    U.on('#invite-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#invite-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        if (a.getAttribute('href') && a.getAttribute('href').indexOf('/admin/video/members') === 0) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-copy')) copyText(row.code, L.copied_invite);
        if (a.classList.contains('js-void')) {
            if (!U.confirm(String(L.confirm_void_invite || '').replace('__CODE__', row.code || ''))) return;
            setStatus(row, 0);
        }
        if (a.classList.contains('js-on')) setStatus(row, 1);
        if (a.classList.contains('js-del')) {
            if (!U.confirm(String(L.confirm_del_invite || '').replace('__CODE__', row.code || ''))) return;
            U.post('/admin/video/invites/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
                table.refresh();
                U.toast(L.deleted, 'ok');
            });
        }
    });
})();
</script>
@endpush