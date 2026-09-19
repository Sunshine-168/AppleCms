@extends('admin.layouts.inner')
@section('title', $title)

@php
    $queues = $queues ?? ['all' => 0, 'pending' => 0, 'pass' => 0, 'report' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
    $scope = ($scope ?? 'vod') === 'art' ? 'art' : 'vod';
    $ready = (bool) ($ready ?? true);
    $api = $scope === 'art'
        ? [
            'list' => '/admin/video/art-comments/list',
            'save' => '/admin/video/art-comments/save',
            'delete' => '/admin/video/art-comments/delete',
            'batch' => '/admin/video/art-comments/batch',
        ]
        : [
            'list' => '/admin/video/comments/list',
            'save' => '/admin/video/comments/save',
            'delete' => '/admin/video/comments/delete',
            'batch' => '/admin/video/comments/batch',
        ];
    $lead = $scope === 'art' ? admin_t('ui.comments_lead_art') : admin_t('ui.comments_lead_vod');
    $emptyHint = $scope === 'art' ? admin_t('ui.empty_comments_hint_art') : admin_t('ui.empty_comments_hint_vod');
    $commentJsLang = [
        'status' => admin_t('ui.status'),
        'actions' => admin_t('ui.actions'),
        'edit' => admin_t('ui.edit'),
        'delete' => admin_t('ui.delete'),
        'hide' => admin_t('ui.hide'),
        'show' => admin_t('ui.show'),
        'fail' => admin_t('ui.fail'),
        'saved' => admin_t('ui.saved'),
        'deleted' => admin_t('ui.deleted'),
        'clear_filter' => admin_t('ui.clear_filter'),
        'selected_rows' => admin_t('ui.selected_rows', ['n' => '__N__']),
        'op_fail' => admin_t('manga.op_fail'),
        'op_ok' => admin_t('manga.op_ok'),
        'col_comment' => admin_t('ui.col_comment'),
        'approve' => admin_t('ui.approve'),
        'pending_review' => admin_t('ui.pending_review'),
        'empty_comments' => admin_t('ui.empty_comments'),
        'no_match_comments' => admin_t('ui.no_match_comments'),
        'edit_comment' => admin_t('ui.edit_comment'),
        'please_fill_content' => admin_t('ui.please_fill_content'),
        'please_select_comments' => admin_t('ui.please_select_comments'),
        'confirm_del_comment' => admin_t('ui.confirm_del_comment'),
        'confirm_batch_del_comments' => admin_t('ui.confirm_batch_del_comments'),
        'comment_approved' => admin_t('ui.comment_approved'),
        'comment_hidden' => admin_t('ui.comment_hidden'),
        'guest' => admin_t('ui.guest'),
        'kind_art' => admin_t('ui.kind_art'),
        'kind_vod' => admin_t('ui.kind_vod'),
        'kind_deleted' => admin_t('ui.kind_deleted', ['kind' => '__KIND__']),
        'report_n' => admin_t('ui.report_n', ['n' => '__N__']),
        'like_n' => admin_t('ui.like_n', ['n' => '__N__']),
        'go_arts' => admin_t('ui.go_arts'),
        'go_videos' => admin_t('ui.go_videos'),
        'empty_hint' => $emptyHint,
    ];
@endphp

@section('plain')
<div class="card card-panel comment-index list-desk">
    <div class="card-header">
        <span>{{ admin_t('ui.comments') }} <em id="comment-count"></em></span>
        <a class="btn btn-muted btn-sm" href="/admin/video/config/comment">{{ admin_t('ui.comment_audit') }}</a>
    </div>
    <div class="card-body">
        @if($scope === 'art' && ! $ready)
            <p class="muted recycle-lead">{{ admin_t('ui.comments_migrate') }}</p>
        @else
        <form class="filter-bar" id="comment-search" onsubmit="return false;">
            <input type="hidden" name="report">
            @if($scope === 'art')
                <input type="hidden" name="comment_mid" value="2">
            @endif
            <input type="text" name="q" placeholder="{{ admin_t('ui.ph_comment') }}" autocomplete="off">
            <select name="status">
                <option value="">{{ admin_t('ui.status') }}</option>
                <option value="0">{{ admin_t('ui.pending_review') }}</option>
                <option value="1">{{ admin_t('ui.approved') }}</option>
            </select>
            <button type="button" class="btn btn-sm" id="comment-search-btn">{{ admin_t('ui.search') }}</button>
            <button type="reset" class="btn btn-muted btn-sm" id="comment-reset-btn">{{ admin_t('ui.reset') }}</button>
        </form>
        <div class="queue-chips" id="comment-queues">
            <button type="button" class="chip" data-queue="">{{ admin_t('ui.all') }}@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="0">{{ admin_t('ui.pending_review') }}@if($q('pending') > 0)<em>{{ $q('pending') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="1">{{ admin_t('ui.approved') }}@if($q('pass') > 0)<em>{{ $q('pass') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="report" data-value="1">{{ admin_t('ui.reported') }}@if($q('report') > 0)<em>{{ $q('report') }}</em>@endif</button>
        </div>
        <p class="muted recycle-lead">{{ $lead }}</p>
        <div class="batch-bar" id="comment-batch" hidden>
            <strong id="comment-batch-count">{{ admin_t('ui.selected_rows', ['n' => 0]) }}</strong>
            <button type="button" class="btn btn-sm" id="comment-batch-on">{{ admin_t('ui.approve') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="comment-batch-off">{{ admin_t('ui.hide') }}</button>
            <button type="button" class="btn btn-danger btn-sm" id="comment-batch-del">{{ admin_t('ui.delete') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="comment-batch-clear">{{ admin_t('ui.clear_selection') }}</button>
        </div>
        <div id="comment-table"></div>
        @endif
    </div>
</div>
<template id="comment-dialog-tpl">
    <form>
        <input type="hidden" name="id">
        <input type="hidden" name="video_id">
        @if($scope === 'art')
            <input type="hidden" name="mid" value="2">
        @endif
        <label>{{ admin_t('ui.label_nickname') }}</label>
        <input type="text" name="author_name" placeholder="{{ admin_t('ui.ph_nickname') }}">
        <label>{{ admin_t('ui.label_content') }}</label>
        <textarea name="content" rows="5" placeholder="{{ admin_t('ui.ph_comment_body') }}"></textarea>
        <label>{{ admin_t('ui.status') }}</label>
        <select name="status">
            <option value="1">{{ admin_t('ui.status_show') }}</option>
            <option value="0">{{ admin_t('ui.status_pending_hide') }}</option>
        </select>
        <p class="muted field-hint">{{ admin_t('ui.comment_status_hint') }}</p>
    </form>
</template>
@endsection

@push('scripts')
@if($scope !== 'art' || $ready)
<script>
(function () {
    var U = AdminUi;
    var L = @json($commentJsLang);
    var QUEUE_KEYS = ['report'];
    var SCOPE = {!! json_encode($scope, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!};
    var API = {!! json_encode($api, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!};
    var form = document.getElementById('comment-search');
    var batchBar = document.getElementById('comment-batch');
    var batchCount = document.getElementById('comment-batch-count');

    function cleanWhere(data) {
        var out = {};
        Object.keys(data).forEach(function (k) { if (data[k] !== '') out[k] = data[k]; });
        if (SCOPE === 'art') out.comment_mid = 2;
        return out;
    }
    function isFiltered(where) {
        return Object.keys(where || {}).some(function (k) {
            if (k === 'comment_mid') return false;
            return where[k] !== '';
        });
    }
    function markChips() {
        var status = form.status.value;
        var report = form.report.value;
        U.qa('#comment-queues .chip').forEach(function (chip) {
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
        var who = U.escape(d.author_name || L.guest);
        var meta = who;
        if (d.created_at_text) meta += ' · ' + U.escape(d.created_at_text);
        if (d.ip) meta += ' · ' + U.escape(d.ip);
        var href = d.target_url || '';
        var kind = d.target_kind === 'art' || SCOPE === 'art' ? L.kind_art : L.kind_vod;
        var film = d.video_title
            ? (href
                ? '<a href="' + U.escape(href) + '" target="_blank" rel="noopener">' + U.escape(d.video_title) + '</a>'
                : U.escape(d.video_title))
            : (d.video_id ? kind + ' #' + U.escape(d.video_id) : String(L.kind_deleted || '').replace('__KIND__', kind));
        var badges = [];
        if (parseInt(d.comment_report, 10) > 0) badges.push('<span class="badge badge-off">' + String(L.report_n || '').replace('__N__', String(d.comment_report)) + '</span>');
        if (parseInt(d.comment_up, 10) > 0) badges.push('<span class="badge badge-ok">' + String(L.like_n || '').replace('__N__', String(d.comment_up)) + '</span>');
        return '<div class="comment-cell"><div class="comment-body">' + U.escape(d.content || '') + '</div>'
            + '<div class="muted">' + meta + ' · ' + film + '</div>'
            + (badges.length ? '<div class="vod-badges">' + badges.join('') + '</div>' : '')
            + '</div>';
    }

    var table = U.table({
        el: '#comment-table',
        countEl: document.getElementById('comment-count'),
        queueKeys: QUEUE_KEYS,
        url: API.list,
        where: cleanWhere(U.formData(form)),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>' + L.no_match_comments + '</p><p><button type="button" class="btn btn-muted btn-sm" id="comment-empty-reset">' + L.clear_filter + '</button></p></div>';
            }
            var go = SCOPE === 'art' ? L.go_arts : L.go_videos;
            var href = SCOPE === 'art' ? '/admin/video/arts' : '/admin/video';
            return '<div class="list-empty"><p>' + L.empty_comments + '</p><p class="muted">' + U.escape(L.empty_hint) + '</p><p><a class="btn btn-muted btn-sm" href="' + href + '">' + go + '</a></p></div>';
        },
        onDraw: function () {
            var reset = document.getElementById('comment-empty-reset');
            if (reset) reset.addEventListener('click', function () {
                form.reset();
                QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
                if (form.comment_mid) form.comment_mid.value = '2';
                runSearch();
            });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = String(L.selected_rows || '').replace('__N__', String(ids.length));
        },
        cols: [
            {check: true, width: 36},
            {title: L.col_comment, html: contentHtml},
            {title: L.status, width: 80, html: function (d) {
                return String(d.status) === '1' ? U.status(true, L.show) : U.status(false, L.pending_review);
            }},
            {title: L.actions, cls: 'actions', html: function (d) {
                var html = '';
                if (String(d.status) !== '1') html += '<a href="#" class="btn-link js-pass">' + L.approve + '</a>';
                else html += '<a href="#" class="btn-link js-hide">' + L.hide + '</a>';
                html += '<a href="#" class="btn-link js-edit">' + L.edit + '</a><a href="#" class="btn-link js-del">' + L.delete + '</a>';
                return html;
            }}
        ]
    });
    markChips();

    function openEdit(row) {
        U.dialog({
            title: L.edit_comment,
            wide: true,
            content: document.getElementById('comment-dialog-tpl').innerHTML,
            onOpen: function (body) {
                U.fillForm(body.querySelector('form'), {
                    id: row.id || '',
                    video_id: row.video_id || '',
                    author_name: row.author_name || '',
                    content: row.content || '',
                    status: row.status == null ? '1' : String(row.status)
                });
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (!data.content) { U.toast(L.please_fill_content, 'err'); return false; }
                if (SCOPE === 'art') data.mid = 2;
                return U.post(API.save, data).then(function (res) {
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
        if (!ids.length) { U.toast(L.please_select_comments, 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post(API.batch, {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.op_fail, 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || L.op_ok, 'ok');
        });
    }
    function setStatus(row, status) {
        var payload = {id: row.id, status: status};
        if (SCOPE === 'art') payload.mid = 2;
        U.post(API.save, payload).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
            table.refresh();
            U.toast(status === 1 ? L.comment_approved : L.comment_hidden, 'ok');
        });
    }

    U.on('#comment-search-btn', 'click', runSearch);
    U.on('#comment-reset-btn', 'click', function () {
        setTimeout(function () {
            QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
            if (form.comment_mid) form.comment_mid.value = '2';
            runSearch();
        }, 0);
    });
    document.getElementById('comment-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#comment-batch-on', 'click', function () { batch('status', 1); });
    U.on('#comment-batch-off', 'click', function () { batch('status', 0); });
    U.on('#comment-batch-del', 'click', function () { batch('delete', '', L.confirm_batch_del_comments); });
    U.on('#comment-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#comment-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        if (a.target === '_blank') return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-edit')) openEdit(row);
        if (a.classList.contains('js-pass')) setStatus(row, 1);
        if (a.classList.contains('js-hide')) setStatus(row, 0);
        if (a.classList.contains('js-del')) {
            if (!U.confirm(L.confirm_del_comment)) return;
            U.post(API.delete, {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
                table.refresh();
                U.toast(L.deleted, 'ok');
            });
        }
    });
})();
</script>
@endif
@endpush
