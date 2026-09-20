@extends('admin.layouts.inner')
@section('title', $title ?? admin_t('nav.chatroom'))

@php
    $desk = in_array((string) ($desk ?? ''), ['messages', 'settings'], true) ? (string) $desk : 'messages';
    $queues = $queues ?? ['all' => 0, 'on' => 0, 'off' => 0, 'report' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
    $options = is_array($options ?? null) ? $options : ['chatroom_enabled' => 1, 'chatroom_login' => 0];
    $chatJsLang = [
        'fail' => admin_t('ui.fail'),
        'saved' => admin_t('ui.saved'),
        'deleted' => admin_t('ui.deleted'),
        'show' => admin_t('ui.show'),
        'hide' => admin_t('ui.hide'),
        'delete' => admin_t('ui.delete'),
        'status' => admin_t('ui.status'),
        'actions' => admin_t('ui.actions'),
        'speech' => admin_t('ui.speech'),
        'guest' => admin_t('ui.guest'),
        'shown' => admin_t('ui.shown'),
        'hidden' => admin_t('ui.hidden'),
        'cleared' => admin_t('ui.emptied'),
        'op_fail' => admin_t('ui.op_fail'),
        'op_ok' => admin_t('ui.op_ok'),
        'clear_filter' => admin_t('ui.clear_filter'),
        'empty_speech' => admin_t('ui.empty_speech'),
        'empty_speech_hint' => admin_t('ui.empty_speech_hint'),
        'no_match_speech' => admin_t('ui.no_match_speech'),
        'please_select_posts' => admin_t('ui.please_select_posts'),
        'confirm_del_speech' => admin_t('ui.confirm_del_speech'),
        'confirm_batch_del_speech' => admin_t('ui.confirm_batch_del_speech'),
        'confirm_clear_speech' => admin_t('ui.confirm_clear_speech'),
        'selected_n' => admin_t('ui.selected_n', ['n' => '__N__']),
        'member_hash' => admin_t('ui.member_hash', ['id' => '__ID__']),
        'vod_hash' => admin_t('ui.vod_hash', ['id' => '__ID__']),
        'video_gone' => admin_t('ui.video_gone'),
        'report_n' => admin_t('ui.report_n', ['n' => '__N__']),
    ];
@endphp

@section('plain')
<div class="card card-panel chat-board desk-board" id="chat-board">
    <div class="card-header">
        <span>{{ admin_t('nav.chatroom') }}</span>
        @if($desk === 'messages')
            <button type="button" class="btn btn-danger btn-sm" id="chat-clear-all">{{ admin_t('ui.clear_all') }}</button>
        @endif
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">{{ admin_t('ui.chatroom_lead') }}</p>
        <div class="queue-chips">
            <a class="chip{{ $desk === 'messages' ? ' active' : '' }}" href="/admin/video/chat_messages">{{ admin_t('ui.speech') }}</a>
            <a class="chip{{ $desk === 'settings' ? ' active' : '' }}" href="/admin/video/chat_messages?desk=settings">{{ admin_t('ui.settings') }}</a>
        </div>
        @if($desk === 'settings')
            <form id="chat-settings" onsubmit="return false;">
                <input type="hidden" name="desk" value="settings">
                <label>{{ admin_t('nav.chatroom') }}</label>
                <select name="chatroom_enabled">
                    <option value="1" @selected((int) ($options['chatroom_enabled'] ?? 1) === 1)>{{ admin_t('ui.on_switch') }}</option>
                    <option value="0" @selected((int) ($options['chatroom_enabled'] ?? 1) !== 1)>{{ admin_t('ui.off_switch') }}</option>
                </select>
                <p class="muted field-hint">{{ admin_t('ui.chatroom_off_hint') }}</p>
                <label>{{ admin_t('ui.need_login_post') }}</label>
                <select name="chatroom_login">
                    <option value="0" @selected((int) ($options['chatroom_login'] ?? 0) !== 1)>{{ admin_t('ui.no') }}</option>
                    <option value="1" @selected((int) ($options['chatroom_login'] ?? 0) === 1)>{{ admin_t('ui.yes') }}</option>
                </select>
                <p class="muted field-hint">{{ admin_t('ui.guest_ok_hint') }}</p>
                <p><button type="button" class="btn btn-sm" id="chat-settings-save">{{ admin_t('ui.save') }}</button></p>
            </form>
        @else
            <form class="filter-bar" id="chat-search" onsubmit="return false;">
                <input type="hidden" name="desk" value="messages">
                <input type="hidden" name="report">
                <input type="search" name="q" placeholder="{{ admin_t('ui.ph_search_speech') }}" autocomplete="off">
                <input type="number" name="video_id" placeholder="{{ admin_t('ui.ph_video_no') }}" min="1">
                <input type="number" name="member_id" placeholder="{{ admin_t('ui.ph_member_no') }}" min="1">
                <select name="status">
                    <option value="">{{ admin_t('ui.status') }}</option>
                    <option value="1">{{ admin_t('ui.show') }}</option>
                    <option value="0">{{ admin_t('ui.hide') }}</option>
                </select>
                <button type="button" class="btn btn-sm" id="chat-search-btn">{{ admin_t('ui.search') }}</button>
                <button type="reset" class="btn btn-muted btn-sm" id="chat-reset-btn">{{ admin_t('ui.reset') }}</button>
            </form>
            <div class="queue-chips" id="chat-queues">
                <button type="button" class="chip" data-queue="">{{ admin_t('ui.all') }}@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
                <button type="button" class="chip" data-queue="status" data-value="1">{{ admin_t('ui.show') }}@if($q('on') > 0)<em>{{ $q('on') }}</em>@endif</button>
                <button type="button" class="chip" data-queue="status" data-value="0">{{ admin_t('ui.hide') }}@if($q('off') > 0)<em>{{ $q('off') }}</em>@endif</button>
                <button type="button" class="chip" data-queue="report" data-value="1">{{ admin_t('ui.reported') }}@if($q('report') > 0)<em>{{ $q('report') }}</em>@endif</button>
            </div>
            <div class="batch-bar" id="chat-batch" hidden>
                <strong id="chat-batch-count">{{ admin_t('ui.selected_n', ['n' => 0]) }}</strong>
                <button type="button" class="btn btn-sm" id="chat-batch-on">{{ admin_t('ui.show') }}</button>
                <button type="button" class="btn btn-muted btn-sm" id="chat-batch-off">{{ admin_t('ui.hide') }}</button>
                <button type="button" class="btn btn-danger btn-sm" id="chat-batch-del">{{ admin_t('ui.delete') }}</button>
                <button type="button" class="btn btn-muted btn-sm" id="chat-batch-clear">{{ admin_t('ui.clear_selection') }}</button>
            </div>
            <div id="chat-table" class="desk-table"></div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($chatJsLang, JSON_UNESCAPED_UNICODE);
    var desk = @json($desk);
    if (desk === 'settings') {
        U.on('#chat-settings-save', 'click', function () {
            var data = U.formData(document.getElementById('chat-settings'));
            U.post('/admin/video/chat_messages/save', data).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
                U.toast((res && res.msg) || L.saved, 'ok');
            });
        });
        return;
    }
    var QUEUE_KEYS = ['report'];
    var form = document.getElementById('chat-search');
    var batchBar = document.getElementById('chat-batch');
    var batchCount = document.getElementById('chat-batch-count');
    function cleanWhere(data) {
        var out = {};
        Object.keys(data).forEach(function (k) { if (data[k] !== '') out[k] = data[k]; });
        return out;
    }
    function isFiltered(where) {
        return Object.keys(where || {}).some(function (k) { return k !== 'desk' && where[k] !== ''; });
    }
    function markChips() {
        var status = form.status.value;
        var report = form.report.value;
        U.qa('#chat-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = false;
            if (key === '' && status === '' && report === '') on = true;
            else if (key === 'status' && report === '' && status === val) on = true;
            else if (key === 'report' && report === val) on = true;
            chip.classList.toggle('active', on);
        });
    }
    function applyQueue(key, value) {
        QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
        if (key === 'status') form.status.value = value || '';
        else {
            form.status.value = '';
            if (key && form[key]) form[key].value = value || '1';
        }
        runSearch();
    }
    function runSearch() {
        table.reload(cleanWhere(U.formData(form)));
        markChips();
    }
    function contentHtml(d) {
        var who = U.escape(d.name || L.guest);
        var meta = who;
        if (d.created_at_text) meta += ' · ' + U.escape(d.created_at_text);
        if (d.ip) meta += ' · ' + U.escape(d.ip);
        if (d.member_id) meta += ' · ' + String(L.member_hash || '').replace('__ID__', U.escape(d.member_id));
        var film = d.video_title
            ? '<a href="/vod/' + encodeURIComponent(d.video_id) + '" target="_blank" rel="noopener">' + U.escape(d.video_title) + '</a>'
            : (d.video_id ? String(L.vod_hash || '').replace('__ID__', U.escape(d.video_id)) : L.video_gone);
        var badges = [];
        if (parseInt(d.report, 10) > 0) badges.push('<span class="badge badge-off">' + String(L.report_n || '').replace('__N__', U.escape(d.report)) + '</span>');
        return '<div class="comment-cell"><div class="comment-body">' + U.escape(d.text || '') + '</div>'
            + '<div class="muted">' + meta + ' · ' + film + '</div>'
            + (badges.length ? '<div class="vod-badges">' + badges.join('') + '</div>' : '')
            + '</div>';
    }
    var table = U.table({
        el: '#chat-table',
        url: '/admin/video/chat_messages/list',
        where: cleanWhere(U.formData(form)),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>' + U.escape(L.no_match_speech) + '</p><p><button type="button" class="btn btn-muted btn-sm" id="chat-empty-reset">' + U.escape(L.clear_filter) + '</button></p></div>';
            }
            return '<div class="list-empty"><p>' + U.escape(L.empty_speech) + '</p><p class="muted">' + U.escape(L.empty_speech_hint) + '</p></div>';
        },
        onDraw: function () {
            var reset = document.getElementById('chat-empty-reset');
            if (reset) reset.addEventListener('click', function () {
                form.reset();
                QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
                runSearch();
            });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = String(L.selected_n || '').replace('__N__', String(ids.length));
        },
        cols: [
            {check: true, width: 36},
            {title: L.speech, html: contentHtml},
            {title: L.status, width: 80, html: function (d) {
                return String(d.status) === '1' ? U.status(true, L.show) : U.status(false, L.hide);
            }},
            {title: L.actions, cls: 'actions', html: function (d) {
                var html = '';
                if (String(d.status) !== '1') html += '<a href="#" class="btn-link js-pass">' + U.escape(L.show) + '</a>';
                else html += '<a href="#" class="btn-link js-hide">' + U.escape(L.hide) + '</a>';
                html += '<a href="#" class="btn-link js-del">' + U.escape(L.delete) + '</a>';
                return html;
            }}
        ]
    });
    markChips();
    function selectedIds() { return table.selectedIds(); }
    function batch(action, value, confirmText) {
        var ids = selectedIds();
        if (!ids.length) { U.toast(L.please_select_posts, 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/chat_messages/batch', {ids: ids.join(','), action: action, value: value, desk: 'messages'}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.op_fail, 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || L.op_ok, 'ok');
        });
    }
    function setStatus(row, status) {
        U.post('/admin/video/chat_messages/save', {id: row.id, status: status, desk: 'messages'}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
            table.refresh();
            U.toast(status === 1 ? L.shown : L.hidden, 'ok');
        });
    }
    U.on('#chat-search-btn', 'click', runSearch);
    U.on('#chat-reset-btn', 'click', function () {
        setTimeout(function () {
            QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
            runSearch();
        }, 0);
    });
    document.getElementById('chat-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#chat-batch-on', 'click', function () { batch('status', 1); });
    U.on('#chat-batch-off', 'click', function () { batch('status', 0); });
    U.on('#chat-batch-del', 'click', function () { batch('delete', '', L.confirm_batch_del_speech); });
    U.on('#chat-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#chat-clear-all', 'click', function () {
        if (!U.confirm(L.confirm_clear_speech)) return;
        U.post('/admin/video/chat_messages/batch', {action: 'clear', desk: 'messages'}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || L.cleared, 'ok');
        });
    });
    U.on('#chat-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        if (a.target === '_blank') return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-pass')) setStatus(row, 1);
        if (a.classList.contains('js-hide')) setStatus(row, 0);
        if (a.classList.contains('js-del')) {
            if (!U.confirm(L.confirm_del_speech)) return;
            U.post('/admin/video/chat_messages/delete', {id: row.id, desk: 'messages'}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
                table.refresh();
                U.toast(L.deleted, 'ok');
            });
        }
    });
})();
</script>
@endpush
