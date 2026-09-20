@extends('admin.layouts.inner')
@section('title', $title)

@php
    $queues = $queues ?? ['all' => 0, 'all_members' => 0, 'one' => 0, 'unread' => 0, 'today' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
    $memberId = (int) ($memberId ?? 0);
    $notifyJsLang = [
        'status' => admin_t('ui.status'),
        'actions' => admin_t('ui.actions'),
        'delete' => admin_t('ui.delete'),
        'fail' => admin_t('ui.fail'),
        'deleted' => admin_t('ui.deleted'),
        'clear_filter' => admin_t('ui.clear_filter'),
        'selected_rows' => admin_t('ui.selected_rows', ['n' => '__N__']),
        'op_fail' => admin_t('manga.op_fail'),
        'op_ok' => admin_t('manga.op_ok'),
        'members' => admin_t('ui.members'),
        'view' => admin_t('ui.view'),
        'is_read' => admin_t('ui.is_read'),
        'unread' => admin_t('ui.unread'),
        'col_notify' => admin_t('ui.col_notify'),
        'no_title' => admin_t('ui.no_title'),
        'sitewide' => admin_t('ui.sitewide'),
        'member_hash' => admin_t('ui.member_hash', ['id' => '__ID__']),
        'empty_notifies' => admin_t('ui.empty_notifies'),
        'empty_notifies_hint' => admin_t('ui.empty_notifies_hint'),
        'no_match_notifies' => admin_t('ui.no_match_notifies'),
        'send_notify' => admin_t('ui.send_notify'),
        'send' => admin_t('ui.send'),
        'please_fill_title' => admin_t('ui.please_fill_title'),
        'please_fill_content' => admin_t('ui.please_fill_content'),
        'please_fill_target_member_id' => admin_t('ui.please_fill_target_member_id'),
        'please_select_notifies' => admin_t('ui.please_select_notifies'),
        'sent' => admin_t('ui.sent'),
        'view_notify' => admin_t('ui.view_notify'),
        'close' => admin_t('ui.close'),
        'marked_read' => admin_t('ui.marked_read'),
        'confirm_batch_del_notifies' => admin_t('ui.confirm_batch_del_notifies'),
        'confirm_del_notify' => admin_t('ui.confirm_del_notify'),
    ];
@endphp

@section('plain')
<div class="card card-panel notify-index">
    <div class="card-header">
        <span>{{ admin_t('ui.notifies') }}@if($q('unread') > 0) <em>{{ admin_t('ui.unread_n', ['n' => $q('unread')]) }}</em>@endif</span>
        <div>
            <button type="button" class="btn btn-sm" id="notify-add-btn">{{ admin_t('ui.send_notify') }}</button>
            <a class="btn btn-muted btn-sm" href="/admin/video/pms">{{ admin_t('ui.pms') }}</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/members">{{ admin_t('ui.members') }}</a>
        </div>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="notify-search" onsubmit="return false;">
            <input type="hidden" name="is_read">
            <input type="hidden" name="today">
            <input type="hidden" name="audience">
            <input type="search" name="q" placeholder="{{ admin_t('ui.ph_notify') }}" autocomplete="off" aria-label="{{ admin_t('ui.aria_search_notifies') }}">
            <button type="button" class="btn btn-sm" id="notify-search-btn">{{ admin_t('ui.search') }}</button>
            <button type="reset" class="btn btn-muted btn-sm" id="notify-reset-btn">{{ admin_t('ui.reset') }}</button>
        </form>
        <div class="queue-chips" id="notify-queues">
            <button type="button" class="chip" data-queue="">{{ admin_t('ui.all') }}@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="audience" data-value="all">{{ admin_t('ui.sitewide') }}@if($q('all_members') > 0)<em>{{ $q('all_members') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="audience" data-value="one">{{ admin_t('ui.chip_one_member') }}@if($q('one') > 0)<em>{{ $q('one') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="is_read" data-value="0">{{ admin_t('ui.unread') }}@if($q('unread') > 0)<em>{{ $q('unread') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="today" data-value="1">{{ admin_t('ui.today') }}@if($q('today') > 0)<em>{{ $q('today') }}</em>@endif</button>
        </div>
        <p class="muted recycle-lead">{{ admin_t('ui.notifies_lead') }}</p>
        <div class="batch-bar" id="notify-batch" hidden>
            <strong id="notify-batch-count">{{ admin_t('ui.selected_rows', ['n' => 0]) }}</strong>
            <button type="button" class="btn btn-sm" id="notify-batch-read">{{ admin_t('ui.mark_as_read') }}</button>
            <button type="button" class="btn btn-danger btn-sm" id="notify-batch-del">{{ admin_t('ui.delete') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="notify-batch-clear">{{ admin_t('ui.clear_selection') }}</button>
        </div>
        <div id="notify-table"></div>
    </div>
</div>
<template id="notify-compose-tpl">
    <form class="admin-form">
        <input type="hidden" name="is_read" value="0">
        <label for="notify-scope">{{ admin_t('ui.label_scope') }}</label>
        <select id="notify-scope" name="scope">
            <option value="0">{{ admin_t('ui.scope_all_members') }}</option>
            <option value="1">{{ admin_t('ui.scope_one_member') }}</option>
        </select>
        <label for="notify-member">{{ admin_t('ui.label_target_member_id') }}</label>
        <input id="notify-member" type="number" name="member_id" min="1" placeholder="{{ admin_t('ui.ph_member_id_short') }}" autocomplete="off">
        <p class="muted field-hint">{{ admin_t('ui.hint_notify_compose') }}</p>
        <label for="notify-title">{{ admin_t('ui.label_title') }}</label>
        <input id="notify-title" type="text" name="title" maxlength="120" autocomplete="off">
        <label for="notify-content">{{ admin_t('ui.label_content') }}</label>
        <textarea id="notify-content" name="content" rows="8"></textarea>
    </form>
</template>
<template id="notify-view-tpl">
    <form class="admin-form">
        <label>{{ admin_t('ui.label_scope') }}</label>
        <input type="text" name="audience_label" readonly>
        <label>{{ admin_t('ui.label_title') }}</label>
        <input type="text" name="title" readonly>
        <label>{{ admin_t('ui.label_content') }}</label>
        <textarea name="content" rows="8" readonly></textarea>
        <p class="muted field-hint" data-role="meta"></p>
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($notifyJsLang, JSON_UNESCAPED_UNICODE);
    var form = document.getElementById('notify-search');
    var batchBar = document.getElementById('notify-batch');
    var batchCount = document.getElementById('notify-batch-count');
    var QUEUE_KEYS = ['today', 'audience'];
    var memberPrefill = {{ $memberId }};

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
        var isRead = form.is_read.value;
        var today = form.today.value;
        var audience = form.audience.value;
        U.qa('#notify-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = false;
            if (key === '' && isRead === '' && today === '' && audience === '') on = true;
            else if (key === 'audience' && today === '' && isRead === '' && audience === val) on = true;
            else if (key === 'is_read' && today === '' && audience === '' && isRead === val) on = true;
            else if (key === 'today' && today === val) on = true;
            chip.classList.toggle('active', on);
        });
    }
    function applyQueue(key, value) {
        QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
        form.is_read.value = '';
        if (key === 'is_read') form.is_read.value = value || '';
        else if (key === 'audience') form.audience.value = value || '';
        else if (key && form[key]) form[key].value = value || '1';
        runSearch();
    }
    function runSearch() {
        table.reload(queryWhere());
        markChips();
    }
    function audienceLabel(d) {
        if (d.audience_label) return d.audience_label;
        if (parseInt(d.member_id, 10) > 0) return String(L.member_hash || '').replace('__ID__', String(d.member_id));
        return L.sitewide;
    }
    function noticeHtml(d) {
        var meta = U.escape(audienceLabel(d));
        if (d.created_at_text) meta += ' · ' + U.escape(d.created_at_text);
        var preview = d.content_preview || '';
        return '<div class="comment-cell"><div class="entry-row-title-line"><span class="entry-row-title">' + U.escape(d.title || L.no_title) + '</span></div>'
            + (preview ? '<div class="muted">' + U.escape(preview) + '</div>' : '')
            + '<div class="muted">' + meta + '</div></div>';
    }

    var table = U.table({
        el: '#notify-table',
        queueKeys: QUEUE_KEYS,
        url: '/admin/video/notifies/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>' + L.no_match_notifies + '</p><p><button type="button" class="btn btn-muted btn-sm" id="notify-empty-reset">' + L.clear_filter + '</button></p></div>';
            }
            return '<div class="list-empty"><p>' + L.empty_notifies + '</p><p class="muted">' + U.escape(L.empty_notifies_hint) + '</p><p><button type="button" class="btn btn-primary btn-sm" id="notify-empty-add">' + L.send_notify + '</button></p></div>';
        },
        onDraw: function (wrap, list) {
            U.qa('tbody tr[data-idx]', wrap).forEach(function (tr) {
                var d = list[parseInt(tr.getAttribute('data-idx'), 10)];
                if (d && parseInt(d.unread, 10) === 1) tr.classList.add('is-unread');
            });
            var reset = document.getElementById('notify-empty-reset');
            if (reset) reset.addEventListener('click', function () {
                form.reset();
                QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
                form.is_read.value = '';
                runSearch();
            });
            var emptyAdd = document.getElementById('notify-empty-add');
            if (emptyAdd) emptyAdd.addEventListener('click', function () { openCompose({}); });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = String(L.selected_rows || '').replace('__N__', String(ids.length));
        },
        cols: [
            {check: true, width: 36},
            {title: L.col_notify, html: noticeHtml},
            {title: L.status, width: 88, html: function (d) {
                return parseInt(d.is_read, 10) === 1 ? U.status(true, L.is_read) : U.status(false, L.unread);
            }},
            {title: L.actions, cls: 'actions', html: function (d) {
                var html = '<a href="#" class="btn-link js-view">' + L.view + '</a>';
                if (parseInt(d.is_read, 10) !== 1) html += '<a href="#" class="btn-link js-read">' + L.is_read + '</a>';
                if (parseInt(d.member_id, 10) > 0) {
                    html += '<a class="btn-link" href="/admin/video/members">' + L.members + '</a>';
                }
                html += '<a href="#" class="btn-link js-del">' + L.delete + '</a>';
                return html;
            }}
        ]
    });
    markChips();

    function openCompose(row) {
        row = row || {};
        U.dialog({
            title: L.send_notify,
            okText: L.send,
            content: document.getElementById('notify-compose-tpl').innerHTML,
            onOpen: function (body) {
                var memberId = row.member_id != null && parseInt(row.member_id, 10) > 0
                    ? row.member_id
                    : (memberPrefill > 0 ? memberPrefill : '');
                U.fillForm(body.querySelector('form'), {
                    is_read: 0,
                    scope: memberId ? '1' : '0',
                    member_id: memberId,
                    title: row.title || '',
                    content: row.content || ''
                });
                var memberInput = body.querySelector('[name=member_id]');
                var titleInput = body.querySelector('[name=title]');
                if (memberId && memberInput) memberInput.focus();
                else if (titleInput) titleInput.focus();
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                var mid = parseInt(data.member_id, 10) || 0;
                if (String(data.scope) === '1') {
                    if (mid < 1) { U.toast(L.please_fill_target_member_id, 'err'); return false; }
                } else {
                    mid = 0;
                }
                if (!String(data.title || '').trim()) { U.toast(L.please_fill_title, 'err'); return false; }
                if (!String(data.content || '').trim()) { U.toast(L.please_fill_content, 'err'); return false; }
                data.member_id = mid;
                data.is_read = 0;
                delete data.scope;
                return U.post('/admin/video/notifies/save', data).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return false; }
                    U.toast(L.sent, 'ok');
                    table.refresh();
                });
            }
        });
    }
    function openView(row) {
        U.dialog({
            title: L.view_notify,
            hideOk: true,
            cancelText: L.close,
            content: document.getElementById('notify-view-tpl').innerHTML,
            onOpen: function (body) {
                U.fillForm(body.querySelector('form'), {
                    audience_label: audienceLabel(row),
                    title: row.title || '',
                    content: row.content || ''
                });
                var meta = body.querySelector('[data-role=meta]');
                if (meta) {
                    var status = parseInt(row.is_read, 10) === 1 ? L.is_read : L.unread;
                    meta.textContent = status + (row.created_at_text ? (' · ' + row.created_at_text) : '');
                }
            }
        });
    }
    function selectedIds() { return table.selectedIds(); }
    function batch(action, value, confirmText) {
        var ids = selectedIds();
        if (!ids.length) { U.toast(L.please_select_notifies, 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/notifies/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.op_fail, 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || L.op_ok, 'ok');
        });
    }
    function markRead(row) {
        U.post('/admin/video/notifies/save', {id: row.id, is_read: 1}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
            table.refresh();
            U.toast(L.marked_read, 'ok');
        });
    }

    U.on('#notify-search-btn', 'click', runSearch);
    U.on('#notify-reset-btn', 'click', function () {
        setTimeout(function () {
            QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
            form.is_read.value = '';
            runSearch();
        }, 0);
    });
    document.getElementById('notify-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#notify-add-btn', 'click', function () { openCompose({}); });
    U.on('#notify-batch-read', 'click', function () { batch('read', 1); });
    U.on('#notify-batch-del', 'click', function () { batch('delete', '', L.confirm_batch_del_notifies); });
    U.on('#notify-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#notify-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        if (a.target === '_blank') return;
        if (a.getAttribute('href') && a.getAttribute('href').indexOf('/admin/video/') === 0) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-view')) openView(row);
        if (a.classList.contains('js-read')) markRead(row);
        if (a.classList.contains('js-del')) {
            if (!U.confirm(L.confirm_del_notify)) return;
            U.post('/admin/video/notifies/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
                table.refresh();
                U.toast(L.deleted, 'ok');
            });
        }
    });
    if (memberPrefill > 0) openCompose({member_id: memberPrefill});
})();
</script>
@endpush
