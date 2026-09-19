@extends('admin.layouts.inner')
@section('title', $title)

@php
    $queues = $queues ?? ['all' => 0, 'pending' => 0, 'shown' => 0, 'noreply' => 0, 'today' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
    $audit = (bool) ($audit ?? false);
    $gbookJsLang = [
        'status' => admin_t('ui.status'),
        'actions' => admin_t('ui.actions'),
        'delete' => admin_t('ui.delete'),
        'hide' => admin_t('ui.hide'),
        'fail' => admin_t('ui.fail'),
        'saved' => admin_t('ui.saved'),
        'deleted' => admin_t('ui.deleted'),
        'clear_filter' => admin_t('ui.clear_filter'),
        'selected_rows' => admin_t('ui.selected_rows', ['n' => '__N__']),
        'op_fail' => admin_t('manga.op_fail'),
        'op_ok' => admin_t('manga.op_ok'),
        'approve' => admin_t('ui.approve'),
        'pending_review' => admin_t('ui.pending_review'),
        'status_show' => admin_t('ui.status_show'),
        'guest' => admin_t('ui.guest'),
        'reply' => admin_t('ui.reply'),
        'noreply' => admin_t('ui.noreply'),
        'col_guestbook' => admin_t('ui.col_guestbook'),
        'member_hash' => admin_t('ui.member_hash', ['id' => '__ID__']),
        'empty_guestbooks' => admin_t('ui.empty_guestbooks'),
        'empty_guestbooks_hint' => admin_t('ui.empty_guestbooks_hint'),
        'no_match_guestbooks' => admin_t('ui.no_match_guestbooks'),
        'reply_gbook' => admin_t('ui.reply_gbook'),
        'please_select_guestbooks' => admin_t('ui.please_select_guestbooks'),
        'confirm_batch_del_guestbooks' => admin_t('ui.confirm_batch_del_guestbooks'),
        'confirm_del_guestbook' => admin_t('ui.confirm_del_guestbook'),
        'comment_approved' => admin_t('ui.comment_approved'),
        'comment_hidden' => admin_t('ui.comment_hidden'),
    ];
@endphp

@section('plain')
<div class="card card-panel gbook-index">
    <div class="card-header">
        <span>{{ admin_t('ui.guestbooks') }}@if($q('pending') > 0) <em>{{ admin_t('ui.pending_n', ['n' => $q('pending')]) }}</em>@endif</span>
        <div>
            <a class="btn btn-muted btn-sm" href="/gbook" target="_blank" rel="noopener">{{ admin_t('ui.front_guestbook') }}</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/comments">{{ admin_t('ui.comments') }}</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/config/comment">{{ admin_t('ui.comment_audit') }}</a>
        </div>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="gbook-search" onsubmit="return false;">
            <input type="hidden" name="status">
            <input type="hidden" name="noreply">
            <input type="hidden" name="today">
            <input type="search" name="q" placeholder="{{ admin_t('ui.ph_guestbook') }}" autocomplete="off" aria-label="{{ admin_t('ui.aria_search_guestbooks') }}">
            <button type="button" class="btn btn-sm" id="gbook-search-btn">{{ admin_t('ui.search') }}</button>
            <button type="reset" class="btn btn-muted btn-sm" id="gbook-reset-btn">{{ admin_t('ui.reset') }}</button>
        </form>
        <div class="queue-chips" id="gbook-queues">
            <button type="button" class="chip" data-queue="">{{ admin_t('ui.all') }}@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="0">{{ admin_t('ui.pending_review') }}@if($q('pending') > 0)<em>{{ $q('pending') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="1">{{ admin_t('ui.shown') }}@if($q('shown') > 0)<em>{{ $q('shown') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="noreply" data-value="1">{{ admin_t('ui.noreply') }}@if($q('noreply') > 0)<em>{{ $q('noreply') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="today" data-value="1">{{ admin_t('ui.today') }}@if($q('today') > 0)<em>{{ $q('today') }}</em>@endif</button>
        </div>
        @if($audit)
            <p class="muted recycle-lead">{{ admin_t('ui.guestbooks_lead_audit') }}</p>
        @else
            <p class="muted recycle-lead">{{ admin_t('ui.guestbooks_lead_open') }}</p>
        @endif
        <div class="batch-bar" id="gbook-batch" hidden>
            <strong id="gbook-batch-count">{{ admin_t('ui.selected_rows', ['n' => 0]) }}</strong>
            <button type="button" class="btn btn-sm" id="gbook-batch-on">{{ admin_t('ui.approve') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="gbook-batch-off">{{ admin_t('ui.hide') }}</button>
            <button type="button" class="btn btn-danger btn-sm" id="gbook-batch-del">{{ admin_t('ui.delete') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="gbook-batch-clear">{{ admin_t('ui.clear_selection') }}</button>
        </div>
        <div id="gbook-table"></div>
    </div>
</div>
<template id="gbook-dialog-tpl">
    <form class="admin-form">
        <input type="hidden" name="id">
        <label>{{ admin_t('ui.label_nickname') }}</label>
        <input type="text" name="author_name" readonly>
        <label>{{ admin_t('ui.label_gbook_content') }}</label>
        <textarea name="content" rows="4" readonly></textarea>
        <p class="muted field-hint">{{ admin_t('ui.hint_gbook_readonly') }}</p>
        <label>{{ admin_t('ui.reply') }}</label>
        <textarea name="reply" rows="4" placeholder="{{ admin_t('ui.ph_gbook_reply') }}"></textarea>
        <label>{{ admin_t('ui.status') }}</label>
        <select name="status">
            <option value="1">{{ admin_t('ui.status_show') }}</option>
            <option value="0">{{ admin_t('ui.status_pending_hide') }}</option>
        </select>
        <p class="muted field-hint">{{ admin_t('ui.hint_gbook_status') }}</p>
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($gbookJsLang);
    var form = document.getElementById('gbook-search');
    var batchBar = document.getElementById('gbook-batch');
    var batchCount = document.getElementById('gbook-batch-count');
    var QUEUE_KEYS = ['noreply', 'today'];

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
        var status = form.status.value;
        var noreply = form.noreply.value;
        var today = form.today.value;
        U.qa('#gbook-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = false;
            if (key === '' && status === '' && noreply === '' && today === '') on = true;
            else if (key === 'status' && noreply === '' && today === '' && status === val) on = true;
            else if (key === 'noreply' && status === '' && today === '' && noreply === val) on = true;
            else if (key === 'today' && status === '' && noreply === '' && today === val) on = true;
            chip.classList.toggle('active', on);
        });
    }
    function applyQueue(key, value) {
        QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
        form.status.value = '';
        if (key === 'status') form.status.value = value || '';
        else if (key && form[key]) form[key].value = value || '1';
        runSearch();
    }
    function runSearch() {
        table.reload(queryWhere());
        markChips();
    }
    function contentHtml(d) {
        var who = U.escape(d.author_name || d.member_name || (parseInt(d.member_id, 10) > 0
            ? String(L.member_hash || '').replace('__ID__', String(d.member_id))
            : L.guest));
        var meta = who;
        if (d.created_at_text) meta += ' · ' + U.escape(d.created_at_text);
        if (d.ip) meta += ' · ' + U.escape(d.ip);
        var html = '<div class="comment-cell"><div class="comment-body">' + U.escape(d.content || '') + '</div>'
            + '<div class="muted">' + meta + '</div>';
        if (d.reply) {
            html += '<div class="gbook-reply"><span class="muted">' + U.escape(L.reply) + '</span> ' + U.escape(d.reply) + '</div>';
        }
        html += '</div>';
        return html;
    }

    var table = U.table({
        el: '#gbook-table',
        queueKeys: QUEUE_KEYS,
        url: '/admin/video/guestbooks/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>' + L.no_match_guestbooks + '</p><p><button type="button" class="btn btn-muted btn-sm" id="gbook-empty-reset">' + L.clear_filter + '</button></p></div>';
            }
            return '<div class="list-empty"><p>' + L.empty_guestbooks + '</p><p class="muted">' + U.escape(L.empty_guestbooks_hint) + '</p></div>';
        },
        onDraw: function () {
            var reset = document.getElementById('gbook-empty-reset');
            if (reset) reset.addEventListener('click', function () {
                form.reset();
                QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
                form.status.value = '';
                runSearch();
            });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = String(L.selected_rows || '').replace('__N__', String(ids.length));
        },
        cols: [
            {check: true, width: 36},
            {title: L.col_guestbook, html: contentHtml},
            {title: L.status, width: 88, html: function (d) {
                var html = parseInt(d.status, 10) === 1 ? U.status(true, L.status_show) : U.status(false, L.pending_review);
                if (!parseInt(d.has_reply, 10)) html += '<div class="muted">' + U.escape(L.noreply) + '</div>';
                return html;
            }},
            {title: L.actions, cls: 'actions', html: function (d) {
                var html = '<a href="#" class="btn-link js-reply">' + L.reply + '</a>';
                if (parseInt(d.status, 10) !== 1) html += '<a href="#" class="btn-link js-pass">' + L.approve + '</a>';
                else html += '<a href="#" class="btn-link js-hide">' + L.hide + '</a>';
                html += '<a href="#" class="btn-link js-del">' + L.delete + '</a>';
                return html;
            }}
        ]
    });
    markChips();

    function openReply(row) {
        U.dialog({
            title: L.reply_gbook,
            wide: true,
            content: document.getElementById('gbook-dialog-tpl').innerHTML,
            onOpen: function (body) {
                U.fillForm(body.querySelector('form'), {
                    id: row.id || '',
                    author_name: row.author_name || '',
                    content: row.content || '',
                    reply: row.reply || '',
                    status: row.status == null ? '1' : String(row.status)
                });
                var reply = body.querySelector('[name=reply]');
                if (reply) reply.focus();
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                return U.post('/admin/video/guestbooks/save', data).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return false; }
                    U.toast(L.saved, 'ok');
                    table.refresh();
                });
            }
        });
    }
    function selectedIds() { return table.selectedIds(); }
    function batch(action, value, confirmText) {
        var ids = selectedIds();
        if (!ids.length) { U.toast(L.please_select_guestbooks, 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/guestbooks/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.op_fail, 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || L.op_ok, 'ok');
        });
    }
    function setStatus(row, status) {
        U.post('/admin/video/guestbooks/save', {id: row.id, status: status}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
            table.refresh();
            U.toast(status === 1 ? L.comment_approved : L.comment_hidden, 'ok');
        });
    }

    U.on('#gbook-search-btn', 'click', runSearch);
    U.on('#gbook-reset-btn', 'click', function () {
        setTimeout(function () {
            QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
            form.status.value = '';
            runSearch();
        }, 0);
    });
    document.getElementById('gbook-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#gbook-batch-on', 'click', function () { batch('status', 1); });
    U.on('#gbook-batch-off', 'click', function () { batch('status', 0); });
    U.on('#gbook-batch-del', 'click', function () { batch('delete', '', L.confirm_batch_del_guestbooks); });
    U.on('#gbook-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#gbook-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        if (a.target === '_blank') return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-reply')) openReply(row);
        if (a.classList.contains('js-pass')) setStatus(row, 1);
        if (a.classList.contains('js-hide')) setStatus(row, 0);
        if (a.classList.contains('js-del')) {
            if (!U.confirm(L.confirm_del_guestbook)) return;
            U.post('/admin/video/guestbooks/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
                table.refresh();
                U.toast(L.deleted, 'ok');
            });
        }
    });
})();
</script>
@endpush
