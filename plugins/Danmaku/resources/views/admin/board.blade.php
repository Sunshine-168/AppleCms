@extends('admin.layouts.inner')
@section('title', $title ?? admin_t('nav.danmaku'))

@php
    $desk = in_array((string) ($desk ?? ''), ['messages', 'settings'], true) ? (string) $desk : 'messages';
    $queues = $queues ?? ['all' => 0, 'on' => 0, 'off' => 0, 'report' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
    $options = is_array($options ?? null) ? $options : ['danmaku_enabled' => 1, 'danmaku_login' => 0];
    $dmJsLang = [
        'fail' => admin_t('ui.fail'),
        'saved' => admin_t('ui.saved'),
        'deleted' => admin_t('ui.deleted'),
        'show' => admin_t('ui.show'),
        'hide' => admin_t('ui.hide'),
        'delete' => admin_t('ui.delete'),
        'status' => admin_t('ui.status'),
        'actions' => admin_t('ui.actions'),
        'danmaku' => admin_t('nav.danmaku'),
        'guest' => admin_t('ui.guest'),
        'shown' => admin_t('ui.shown'),
        'hidden' => admin_t('ui.hidden'),
        'cleared' => admin_t('ui.emptied'),
        'op_fail' => admin_t('ui.op_fail'),
        'op_ok' => admin_t('ui.op_ok'),
        'clear_filter' => admin_t('ui.clear_filter'),
        'empty_danmaku' => admin_t('ui.empty_danmaku'),
        'empty_danmaku_hint' => admin_t('ui.empty_danmaku_hint'),
        'no_match_danmaku' => admin_t('ui.no_match_danmaku'),
        'please_select_danmaku' => admin_t('ui.please_select_danmaku'),
        'confirm_del_danmaku' => admin_t('ui.confirm_del_danmaku'),
        'confirm_batch_del_danmaku' => admin_t('ui.confirm_batch_del_danmaku'),
        'confirm_clear_danmaku' => admin_t('ui.confirm_clear_danmaku'),
        'selected_n' => admin_t('ui.selected_n', ['n' => '__N__']),
        'member_hash' => admin_t('ui.member_hash', ['id' => '__ID__']),
        'vod_hash' => admin_t('ui.vod_hash', ['id' => '__ID__']),
        'ep_hash' => admin_t('ui.ep_hash', ['id' => '__ID__']),
        'video_gone' => admin_t('ui.video_gone'),
        'report_n' => admin_t('ui.report_n', ['n' => '__N__']),
    ];
@endphp

@section('plain')
<style>.dm-swatch{display:inline-block;width:10px;height:10px;border-radius:2px;margin-right:6px;vertical-align:middle;border:1px solid rgba(0,0,0,.15)}</style>
<div class="card card-panel danmaku-board desk-board" id="danmaku-board">
    <div class="card-header">
        <span>{{ admin_t('nav.danmaku') }}</span>
        @if($desk === 'messages')
            <button type="button" class="btn btn-danger btn-sm" id="dm-clear-all">{{ admin_t('ui.clear_all') }}</button>
        @endif
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">{{ admin_t('ui.danmaku_lead') }}</p>
        <div class="queue-chips">
            <a class="chip{{ $desk === 'messages' ? ' active' : '' }}" href="/admin/video/danmaku">{{ admin_t('nav.danmaku') }}</a>
            <a class="chip{{ $desk === 'settings' ? ' active' : '' }}" href="/admin/video/danmaku?desk=settings">{{ admin_t('ui.settings') }}</a>
        </div>
        @if($desk === 'settings')
            <form id="dm-settings" onsubmit="return false;">
                <input type="hidden" name="desk" value="settings">
                <label>{{ admin_t('nav.danmaku') }}</label>
                <select name="danmaku_enabled">
                    <option value="1" @selected((int) ($options['danmaku_enabled'] ?? 1) === 1)>{{ admin_t('ui.on_switch') }}</option>
                    <option value="0" @selected((int) ($options['danmaku_enabled'] ?? 1) !== 1)>{{ admin_t('ui.off_switch') }}</option>
                </select>
                <p class="muted field-hint">{{ admin_t('ui.danmaku_off_hint') }}</p>
                <label>{{ admin_t('ui.need_login_send') }}</label>
                <select name="danmaku_login">
                    <option value="0" @selected((int) ($options['danmaku_login'] ?? 0) !== 1)>{{ admin_t('ui.no') }}</option>
                    <option value="1" @selected((int) ($options['danmaku_login'] ?? 0) === 1)>{{ admin_t('ui.yes') }}</option>
                </select>
                <p class="muted field-hint">{{ admin_t('ui.guest_ok_hint') }}</p>
                <p><button type="button" class="btn btn-sm" id="dm-settings-save">{{ admin_t('ui.save') }}</button></p>
            </form>
        @else
            <form class="filter-bar" id="dm-search" onsubmit="return false;">
                <input type="hidden" name="desk" value="messages">
                <input type="hidden" name="report">
                <input type="search" name="q" placeholder="{{ admin_t('ui.ph_search_danmaku') }}" autocomplete="off">
                <input type="number" name="video_id" placeholder="{{ admin_t('ui.ph_video_no') }}" min="1">
                <input type="number" name="member_id" placeholder="{{ admin_t('ui.ph_member_no') }}" min="1">
                <select name="status">
                    <option value="">{{ admin_t('ui.status') }}</option>
                    <option value="1">{{ admin_t('ui.show') }}</option>
                    <option value="0">{{ admin_t('ui.hide') }}</option>
                </select>
                <select name="mode">
                    <option value="">{{ admin_t('ui.style') }}</option>
                    <option value="0">{{ admin_t('ui.scroll') }}</option>
                    <option value="1">{{ admin_t('ui.pos_top') }}</option>
                    <option value="2">{{ admin_t('ui.pos_bottom') }}</option>
                </select>
                <button type="button" class="btn btn-sm" id="dm-search-btn">{{ admin_t('ui.search') }}</button>
                <button type="reset" class="btn btn-muted btn-sm" id="dm-reset-btn">{{ admin_t('ui.reset') }}</button>
            </form>
            <div class="queue-chips" id="dm-queues">
                <button type="button" class="chip" data-queue="">{{ admin_t('ui.all') }}@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
                <button type="button" class="chip" data-queue="status" data-value="1">{{ admin_t('ui.show') }}@if($q('on') > 0)<em>{{ $q('on') }}</em>@endif</button>
                <button type="button" class="chip" data-queue="status" data-value="0">{{ admin_t('ui.hide') }}@if($q('off') > 0)<em>{{ $q('off') }}</em>@endif</button>
                <button type="button" class="chip" data-queue="report" data-value="1">{{ admin_t('ui.reported') }}@if($q('report') > 0)<em>{{ $q('report') }}</em>@endif</button>
            </div>
            <div class="batch-bar" id="dm-batch" hidden>
                <strong id="dm-batch-count">{{ admin_t('ui.selected_n', ['n' => 0]) }}</strong>
                <button type="button" class="btn btn-sm" id="dm-batch-on">{{ admin_t('ui.show') }}</button>
                <button type="button" class="btn btn-muted btn-sm" id="dm-batch-off">{{ admin_t('ui.hide') }}</button>
                <button type="button" class="btn btn-danger btn-sm" id="dm-batch-del">{{ admin_t('ui.delete') }}</button>
                <button type="button" class="btn btn-muted btn-sm" id="dm-batch-clear">{{ admin_t('ui.clear_selection') }}</button>
            </div>
            <div id="dm-table" class="desk-table"></div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($dmJsLang, JSON_UNESCAPED_UNICODE);
    var desk = @json($desk, JSON_UNESCAPED_UNICODE);
    if (desk === 'settings') {
        U.on('#dm-settings-save', 'click', function () {
            var data = U.formData(document.getElementById('dm-settings'));
            U.post('/admin/video/danmaku/save', data).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
                U.toast((res && res.msg) || L.saved, 'ok');
            });
        });
        return;
    }
    var QUEUE_KEYS = ['report'];
    var form = document.getElementById('dm-search');
    var batchBar = document.getElementById('dm-batch');
    var batchCount = document.getElementById('dm-batch-count');
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
        U.qa('#dm-queues .chip').forEach(function (chip) {
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
        var who = U.escape(d.name || (d.member_id ? String(L.member_hash || '').replace('__ID__', d.member_id) : L.guest));
        var meta = who;
        if (d.time_text) meta += ' · ' + U.escape(d.time_text);
        if (d.mode_label) meta += ' · ' + U.escape(d.mode_label);
        if (d.created_at_text) meta += ' · ' + U.escape(d.created_at_text);
        if (d.ip) meta += ' · ' + U.escape(d.ip);
        var film = d.video_title
            ? '<a href="/vod/' + encodeURIComponent(d.video_id) + '" target="_blank" rel="noopener">' + U.escape(d.video_title) + '</a>'
            : (d.video_id ? String(L.vod_hash || '').replace('__ID__', U.escape(d.video_id)) : L.video_gone);
        if (d.episode_id) film += ' · ' + String(L.ep_hash || '').replace('__ID__', U.escape(d.episode_id));
        var badges = [];
        if (parseInt(d.report, 10) > 0) badges.push('<span class="badge badge-off">' + String(L.report_n || '').replace('__N__', U.escape(d.report)) + '</span>');
        var swatch = d.color ? '<span class="dm-swatch" style="background:' + U.escape(d.color) + '"></span>' : '';
        return '<div class="comment-cell"><div class="comment-body">' + swatch + U.escape(d.text || '') + '</div>'
            + '<div class="muted">' + meta + ' · ' + film + '</div>'
            + (badges.length ? '<div class="vod-badges">' + badges.join('') + '</div>' : '')
            + '</div>';
    }
    var table = U.table({
        el: '#dm-table',
        url: '/admin/video/danmaku/list',
        where: cleanWhere(U.formData(form)),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>' + U.escape(L.no_match_danmaku) + '</p><p><button type="button" class="btn btn-muted btn-sm" id="dm-empty-reset">' + U.escape(L.clear_filter) + '</button></p></div>';
            }
            return '<div class="list-empty"><p>' + U.escape(L.empty_danmaku) + '</p><p class="muted">' + U.escape(L.empty_danmaku_hint) + '</p></div>';
        },
        onDraw: function () {
            var reset = document.getElementById('dm-empty-reset');
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
            {title: L.danmaku, html: contentHtml},
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
        if (!ids.length) { U.toast(L.please_select_danmaku, 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/danmaku/batch', {ids: ids.join(','), action: action, value: value, desk: 'messages'}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.op_fail, 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || L.op_ok, 'ok');
        });
    }
    function setStatus(row, status) {
        U.post('/admin/video/danmaku/save', {id: row.id, status: status, desk: 'messages'}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
            table.refresh();
            U.toast(status === 1 ? L.shown : L.hidden, 'ok');
        });
    }
    U.on('#dm-search-btn', 'click', runSearch);
    U.on('#dm-reset-btn', 'click', function () {
        setTimeout(function () {
            QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
            runSearch();
        }, 0);
    });
    document.getElementById('dm-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#dm-batch-on', 'click', function () { batch('status', 1); });
    U.on('#dm-batch-off', 'click', function () { batch('status', 0); });
    U.on('#dm-batch-del', 'click', function () { batch('delete', '', L.confirm_batch_del_danmaku); });
    U.on('#dm-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#dm-clear-all', 'click', function () {
        if (!U.confirm(L.confirm_clear_danmaku)) return;
        U.post('/admin/video/danmaku/batch', {action: 'clear', desk: 'messages'}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || L.cleared, 'ok');
        });
    });
    U.on('#dm-table', 'click', function (e) {
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
            if (!U.confirm(L.confirm_del_danmaku)) return;
            U.post('/admin/video/danmaku/delete', {id: row.id, desk: 'messages'}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
                table.refresh();
                U.toast(L.deleted, 'ok');
            });
        }
    });
})();
</script>
@endpush
