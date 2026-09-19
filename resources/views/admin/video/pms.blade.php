@extends('admin.layouts.inner')
@section('title', $title)

@php
    $queues = $queues ?? ['all' => 0, 'unread' => 0, 'today' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
    $toId = (int) ($toId ?? 0);
    $pmJsLang = [
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
        'col_letter' => admin_t('ui.col_letter'),
        'no_title' => admin_t('ui.no_title'),
        'member_hash' => admin_t('ui.member_hash', ['id' => '__ID__']),
        'pm_system' => admin_t('ui.pm_system'),
        'empty_pms' => admin_t('ui.empty_pms'),
        'empty_pms_hint' => admin_t('ui.empty_pms_hint'),
        'no_match_pms' => admin_t('ui.no_match_pms'),
        'write_to_member' => admin_t('ui.write_to_member'),
        'send' => admin_t('ui.send'),
        'please_fill_title' => admin_t('ui.please_fill_title'),
        'please_fill_content' => admin_t('ui.please_fill_content'),
        'please_fill_to_member_id' => admin_t('ui.please_fill_to_member_id'),
        'please_select_pms' => admin_t('ui.please_select_pms'),
        'sent' => admin_t('ui.sent'),
        'view_pm' => admin_t('ui.view_pm'),
        'close' => admin_t('ui.close'),
        'marked_read' => admin_t('ui.marked_read'),
        'confirm_batch_del_pms' => admin_t('ui.confirm_batch_del_pms'),
        'confirm_del_pm' => admin_t('ui.confirm_del_pm'),
    ];
@endphp

@section('plain')
<div class="card card-panel pm-index">
    <div class="card-header">
        <span>{{ admin_t('ui.pms') }}@if($q('unread') > 0) <em>{{ admin_t('ui.unread_n', ['n' => $q('unread')]) }}</em>@endif</span>
        <div>
            <button type="button" class="btn btn-sm" id="pm-add-btn">{{ admin_t('ui.write_to_member') }}</button>
            <a class="btn btn-muted btn-sm" href="/admin/video/members">{{ admin_t('ui.members') }}</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/notifies">{{ admin_t('ui.notifies') }}</a>
        </div>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="pm-search" onsubmit="return false;">
            <input type="hidden" name="is_read">
            <input type="hidden" name="today">
            <input type="search" name="q" placeholder="{{ admin_t('ui.ph_pm') }}" autocomplete="off" aria-label="{{ admin_t('ui.aria_search_pms') }}">
            <button type="button" class="btn btn-sm" id="pm-search-btn">{{ admin_t('ui.search') }}</button>
            <button type="reset" class="btn btn-muted btn-sm" id="pm-reset-btn">{{ admin_t('ui.reset') }}</button>
        </form>
        <div class="queue-chips" id="pm-queues">
            <button type="button" class="chip" data-queue="">{{ admin_t('ui.all') }}@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="is_read" data-value="0">{{ admin_t('ui.unread') }}@if($q('unread') > 0)<em>{{ $q('unread') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="today" data-value="1">{{ admin_t('ui.today') }}@if($q('today') > 0)<em>{{ $q('today') }}</em>@endif</button>
        </div>
        <p class="muted recycle-lead">{{ admin_t('ui.pms_lead') }}</p>
        <div class="batch-bar" id="pm-batch" hidden>
            <strong id="pm-batch-count">{{ admin_t('ui.selected_rows', ['n' => 0]) }}</strong>
            <button type="button" class="btn btn-sm" id="pm-batch-read">{{ admin_t('ui.mark_as_read') }}</button>
            <button type="button" class="btn btn-danger btn-sm" id="pm-batch-del">{{ admin_t('ui.delete') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="pm-batch-clear">{{ admin_t('ui.clear_selection') }}</button>
        </div>
        <div id="pm-table"></div>
    </div>
</div>
<template id="pm-compose-tpl">
    <form class="admin-form">
        <input type="hidden" name="from_id" value="0">
        <input type="hidden" name="is_read" value="0">
        <label for="pm-to">{{ admin_t('ui.label_to_member_id') }}</label>
        <input id="pm-to" type="number" name="to_id" min="1" placeholder="{{ admin_t('ui.ph_member_id_short') }}" autocomplete="off">
        <p class="muted field-hint">{{ admin_t('ui.hint_pm_compose') }}</p>
        <label for="pm-title">{{ admin_t('ui.label_title') }}</label>
        <input id="pm-title" type="text" name="title" maxlength="120" autocomplete="off">
        <label for="pm-content">{{ admin_t('ui.label_content') }}</label>
        <textarea id="pm-content" name="content" rows="8"></textarea>
    </form>
</template>
<template id="pm-view-tpl">
    <form class="admin-form">
        <label>{{ admin_t('ui.label_recipient') }}</label>
        <input type="text" name="to_label" readonly>
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
    var L = @json($pmJsLang);
    var form = document.getElementById('pm-search');
    var batchBar = document.getElementById('pm-batch');
    var batchCount = document.getElementById('pm-batch-count');
    var QUEUE_KEYS = ['today'];
    var toPrefill = {{ $toId }};

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
        U.qa('#pm-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = false;
            if (key === '' && isRead === '' && today === '') on = true;
            else if (key === 'is_read' && today === '' && isRead === val) on = true;
            else if (key === 'today' && today === val) on = true;
            chip.classList.toggle('active', on);
        });
    }
    function applyQueue(key, value) {
        QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
        form.is_read.value = '';
        if (key === 'is_read') form.is_read.value = value || '';
        else if (key && form[key]) form[key].value = value || '1';
        runSearch();
    }
    function runSearch() {
        table.reload(queryWhere());
        markChips();
    }
    function memberLabel(id, name, fallback) {
        if (name) return name;
        if (parseInt(id, 10) > 0) return String(L.member_hash || '').replace('__ID__', String(id));
        return fallback;
    }
    function letterHtml(d) {
        var fromName = memberLabel(d.from_id, d.from_name, L.pm_system);
        var toName = memberLabel(d.to_id, d.to_name, '—');
        var meta = U.escape(fromName) + ' → ' + U.escape(toName);
        if (d.created_at_text) meta += ' · ' + U.escape(d.created_at_text);
        var preview = d.content_preview || '';
        return '<div class="comment-cell"><div class="entry-row-title-line"><span class="entry-row-title">' + U.escape(d.title || L.no_title) + '</span></div>'
            + (preview ? '<div class="muted">' + U.escape(preview) + '</div>' : '')
            + '<div class="muted">' + meta + '</div></div>';
    }

    var table = U.table({
        el: '#pm-table',
        queueKeys: QUEUE_KEYS,
        url: '/admin/video/pms/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>' + L.no_match_pms + '</p><p><button type="button" class="btn btn-muted btn-sm" id="pm-empty-reset">' + L.clear_filter + '</button></p></div>';
            }
            return '<div class="list-empty"><p>' + L.empty_pms + '</p><p class="muted">' + U.escape(L.empty_pms_hint) + '</p><p><button type="button" class="btn btn-primary btn-sm" id="pm-empty-add">' + L.write_to_member + '</button></p></div>';
        },
        onDraw: function (wrap, list) {
            U.qa('tbody tr[data-idx]', wrap).forEach(function (tr) {
                var d = list[parseInt(tr.getAttribute('data-idx'), 10)];
                if (d && parseInt(d.unread, 10) === 1) tr.classList.add('is-unread');
            });
            var reset = document.getElementById('pm-empty-reset');
            if (reset) reset.addEventListener('click', function () {
                form.reset();
                QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
                form.is_read.value = '';
                runSearch();
            });
            var emptyAdd = document.getElementById('pm-empty-add');
            if (emptyAdd) emptyAdd.addEventListener('click', function () { openCompose({}); });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = String(L.selected_rows || '').replace('__N__', String(ids.length));
        },
        cols: [
            {check: true, width: 36},
            {title: L.col_letter, html: letterHtml},
            {title: L.status, width: 88, html: function (d) {
                return parseInt(d.is_read, 10) === 1 ? U.status(true, L.is_read) : U.status(false, L.unread);
            }},
            {title: L.actions, cls: 'actions', html: function (d) {
                var html = '<a href="#" class="btn-link js-view">' + L.view + '</a>';
                if (parseInt(d.is_read, 10) !== 1) html += '<a href="#" class="btn-link js-read">' + L.is_read + '</a>';
                if (parseInt(d.to_id, 10) > 0) {
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
            title: L.write_to_member,
            okText: L.send,
            content: document.getElementById('pm-compose-tpl').innerHTML,
            onOpen: function (body) {
                var toId = row.to_id != null && row.to_id !== '' ? row.to_id : (toPrefill > 0 ? toPrefill : '');
                U.fillForm(body.querySelector('form'), {
                    from_id: 0,
                    is_read: 0,
                    to_id: toId,
                    title: row.title || '',
                    content: row.content || ''
                });
                var toInput = body.querySelector('[name=to_id]');
                if (toInput) toInput.focus();
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (!parseInt(data.to_id, 10)) { U.toast(L.please_fill_to_member_id, 'err'); return false; }
                if (!String(data.title || '').trim()) { U.toast(L.please_fill_title, 'err'); return false; }
                if (!String(data.content || '').trim()) { U.toast(L.please_fill_content, 'err'); return false; }
                data.from_id = 0;
                data.is_read = 0;
                return U.post('/admin/video/pms/save', data).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return false; }
                    U.toast(L.sent, 'ok');
                    table.refresh();
                });
            }
        });
    }
    function openView(row) {
        U.dialog({
            title: L.view_pm,
            hideOk: true,
            cancelText: L.close,
            content: document.getElementById('pm-view-tpl').innerHTML,
            onOpen: function (body) {
                U.fillForm(body.querySelector('form'), {
                    to_label: memberLabel(row.to_id, row.to_name, '—'),
                    title: row.title || '',
                    content: row.content || ''
                });
                var meta = body.querySelector('[data-role=meta]');
                if (meta) {
                    var fromName = memberLabel(row.from_id, row.from_name, L.pm_system);
                    meta.textContent = fromName + (row.created_at_text ? (' · ' + row.created_at_text) : '');
                }
            }
        });
    }
    function selectedIds() { return table.selectedIds(); }
    function batch(action, value, confirmText) {
        var ids = selectedIds();
        if (!ids.length) { U.toast(L.please_select_pms, 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/pms/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.op_fail, 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || L.op_ok, 'ok');
        });
    }
    function markRead(row) {
        U.post('/admin/video/pms/save', {id: row.id, is_read: 1}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
            table.refresh();
            U.toast(L.marked_read, 'ok');
        });
    }

    U.on('#pm-search-btn', 'click', runSearch);
    U.on('#pm-reset-btn', 'click', function () {
        setTimeout(function () {
            QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
            form.is_read.value = '';
            runSearch();
        }, 0);
    });
    document.getElementById('pm-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#pm-add-btn', 'click', function () { openCompose({}); });
    U.on('#pm-batch-read', 'click', function () { batch('read', 1); });
    U.on('#pm-batch-del', 'click', function () { batch('delete', '', L.confirm_batch_del_pms); });
    U.on('#pm-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#pm-table', 'click', function (e) {
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
            if (!U.confirm(L.confirm_del_pm)) return;
            U.post('/admin/video/pms/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
                table.refresh();
                U.toast(L.deleted, 'ok');
            });
        }
    });
    if (toPrefill > 0) openCompose({to_id: toPrefill});
})();
</script>
@endpush
