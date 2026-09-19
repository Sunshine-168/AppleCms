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
    $lead = $scope === 'art'
        ? '文章页发来的评论。不是影片评论。打开「审核设置」后，新评论会先进入待审。'
        : '待审优先处理。勾选后可批量通过或删除。打开「审核设置」后，新评论会先进入待审。';
    $emptyHint = $scope === 'art'
        ? '用户在文章页发的评论会出现在这里。需要先审再显示时，打开右上角「审核设置」。'
        : '用户在播放页发的评论会出现在这里。需要先审再显示时，打开右上角「审核设置」。';
@endphp

@section('plain')
<div class="card card-panel comment-index list-desk">
    <div class="card-header">
        <span>评论 <em id="comment-count"></em></span>
        <a class="btn btn-muted btn-sm" href="/admin/video/config/comment">审核设置</a>
    </div>
    <div class="card-body">
        @if($scope === 'art' && ! $ready)
            <p class="muted recycle-lead">请先执行数据库迁移，文章评论才能和影片评论分开。</p>
        @else
        <form class="filter-bar" id="comment-search" onsubmit="return false;">
            <input type="hidden" name="report">
            @if($scope === 'art')
                <input type="hidden" name="comment_mid" value="2">
            @endif
            <input type="text" name="q" placeholder="搜内容或昵称" autocomplete="off">
            <select name="status">
                <option value="">状态</option>
                <option value="0">待审</option>
                <option value="1">已通过</option>
            </select>
            <button type="button" class="btn btn-sm" id="comment-search-btn">查询</button>
            <button type="reset" class="btn btn-muted btn-sm" id="comment-reset-btn">重置</button>
        </form>
        <div class="queue-chips" id="comment-queues">
            <button type="button" class="chip" data-queue="">全部@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="0">待审@if($q('pending') > 0)<em>{{ $q('pending') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="1">已通过@if($q('pass') > 0)<em>{{ $q('pass') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="report" data-value="1">被举报@if($q('report') > 0)<em>{{ $q('report') }}</em>@endif</button>
        </div>
        <p class="muted recycle-lead">{{ $lead }}</p>
        <div class="batch-bar" id="comment-batch" hidden>
            <strong id="comment-batch-count">已选 0 条</strong>
            <button type="button" class="btn btn-sm" id="comment-batch-on">通过</button>
            <button type="button" class="btn btn-muted btn-sm" id="comment-batch-off">隐藏</button>
            <button type="button" class="btn btn-danger btn-sm" id="comment-batch-del">删除</button>
            <button type="button" class="btn btn-muted btn-sm" id="comment-batch-clear">取消选择</button>
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
        <label>昵称</label>
        <input type="text" name="author_name">
        <label>内容</label>
        <textarea name="content"></textarea>
        <label>状态</label>
        <select name="status">
            <option value="1">显示</option>
            <option value="0">待审 / 隐藏</option>
        </select>
    </form>
</template>
@endsection

@push('scripts')
@if($scope !== 'art' || $ready)
<script>
(function () {
    var U = AdminUi;
    var QUEUE_KEYS = ['report'];
    var SCOPE = {!! json_encode($scope, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!};
    var API = {!! json_encode($api, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!};
    var EMPTY_HINT = {!! json_encode($emptyHint, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!};
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
        var who = U.escape(d.author_name || '游客');
        var meta = who;
        if (d.created_at_text) meta += ' · ' + U.escape(d.created_at_text);
        if (d.ip) meta += ' · ' + U.escape(d.ip);
        var href = d.target_url || '';
        var kind = d.target_kind === 'art' || SCOPE === 'art' ? '文章' : '影片';
        var film = d.video_title
            ? (href
                ? '<a href="' + U.escape(href) + '" target="_blank" rel="noopener">' + U.escape(d.video_title) + '</a>'
                : U.escape(d.video_title))
            : (d.video_id ? kind + ' #' + U.escape(d.video_id) : kind + '已删');
        var badges = [];
        if (parseInt(d.comment_report, 10) > 0) badges.push('<span class="badge badge-off">举报 ' + U.escape(d.comment_report) + '</span>');
        if (parseInt(d.comment_up, 10) > 0) badges.push('<span class="badge badge-ok">赞 ' + U.escape(d.comment_up) + '</span>');
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
                return '<div class="list-empty"><p>没有符合条件的评论</p><p><button type="button" class="btn btn-muted btn-sm" id="comment-empty-reset">清除筛选</button></p></div>';
            }
            return '<div class="list-empty"><p>还没有评论</p><p class="muted">' + U.escape(EMPTY_HINT) + '</p><p><a class="btn btn-muted btn-sm" href="' + (SCOPE === 'art' ? '/admin/video/arts' : '/admin/video') + '">去' + (SCOPE === 'art' ? '文章' : '影片') + '列表</a></p></div>';
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
            batchCount.textContent = '已选 ' + ids.length + ' 条';
        },
        cols: [
            {check: true, width: 36},
            {title: '评论', html: contentHtml},
            {title: '状态', width: 80, html: function (d) {
                return String(d.status) === '1' ? U.status(true, '显示') : U.status(false, '待审');
            }},
            {title: '操作', cls: 'actions', html: function (d) {
                var html = '';
                if (String(d.status) !== '1') html += '<a href="#" class="btn-link js-pass">通过</a>';
                else html += '<a href="#" class="btn-link js-hide">隐藏</a>';
                html += '<a href="#" class="btn-link js-edit">编辑</a><a href="#" class="btn-link js-del">删除</a>';
                return html;
            }}
        ]
    });
    markChips();

    function openEdit(row) {
        U.dialog({
            title: '编辑评论',
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
                if (!data.content) { U.toast('请填写内容', 'err'); return false; }
                if (SCOPE === 'art') data.mid = 2;
                return U.post(API.save, data).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return false; }
                    U.toast('已保存', 'ok');
                    table.refresh();
                });
            }
        });
    }
    function selectedIds() { return table.selectedIds(); }
    function batch(action, value, confirmText) {
        var ids = selectedIds();
        if (!ids.length) { U.toast('请先勾选评论', 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post(API.batch, {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '操作失败', 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || '操作成功', 'ok');
        });
    }
    function setStatus(row, status) {
        var payload = {id: row.id, status: status};
        if (SCOPE === 'art') payload.mid = 2;
        U.post(API.save, payload).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
            table.refresh();
            U.toast(status === 1 ? '已通过' : '已隐藏', 'ok');
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
    U.on('#comment-batch-del', 'click', function () { batch('delete', '', '确认删除选中评论？'); });
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
            if (!U.confirm('删除这条评论？')) return;
            U.post(API.delete, {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh();
                U.toast('已删除', 'ok');
            });
        }
    });
})();
</script>
@endif
@endpush
