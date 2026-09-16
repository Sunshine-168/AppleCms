@extends('admin.layouts.inner')
@section('title', $title)

@php
    $queues = $queues ?? ['all' => 0, 'pending' => 0, 'pass' => 0, 'report' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
@endphp

@section('plain')
<div class="card card-panel comment-index">
    <div class="card-header">
        <span>评论</span>
        <a class="btn btn-muted btn-sm" href="/admin/video/config/comment">审核设置</a>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="comment-search" onsubmit="return false;">
            <input type="hidden" name="report">
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
        <p class="muted recycle-lead">待审优先处理。勾选后可批量通过或删除。打开「审核设置」后，新评论会先进入待审。</p>
        <div class="batch-bar" id="comment-batch" hidden>
            <strong id="comment-batch-count">已选 0 条</strong>
            <button type="button" class="btn btn-sm" id="comment-batch-on">通过</button>
            <button type="button" class="btn btn-muted btn-sm" id="comment-batch-off">隐藏</button>
            <button type="button" class="btn btn-danger btn-sm" id="comment-batch-del">删除</button>
            <button type="button" class="btn btn-muted btn-sm" id="comment-batch-clear">取消选择</button>
        </div>
        <div id="comment-table"></div>
    </div>
</div>
<template id="comment-dialog-tpl">
    <form>
        <input type="hidden" name="id">
        <input type="hidden" name="video_id">
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
<script>
(function () {
    var U = AdminUi;
    var QUEUE_KEYS = ['report'];
    var form = document.getElementById('comment-search');
    var batchBar = document.getElementById('comment-batch');
    var batchCount = document.getElementById('comment-batch-count');

    function cleanWhere(data) {
        var out = {};
        Object.keys(data).forEach(function (k) { if (data[k] !== '') out[k] = data[k]; });
        return out;
    }
    function isFiltered(where) {
        return Object.keys(where || {}).some(function (k) { return where[k] !== ''; });
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
        var film = d.video_title
            ? '<a href="/vod/' + encodeURIComponent(d.video_id) + '" target="_blank" rel="noopener">' + U.escape(d.video_title) + '</a>'
            : (d.video_id ? '影片 #' + U.escape(d.video_id) : '影片已删');
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
        url: '/admin/video/comments/list',
        where: cleanWhere(U.formData(form)),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>没有符合条件的评论</p><p><button type="button" class="btn btn-muted btn-sm" id="comment-empty-reset">清除筛选</button></p></div>';
            }
            return '<div class="list-empty"><p>还没有评论</p><p class="muted">用户在播放页发的评论会出现在这里。需要先审再显示时，打开右上角「审核设置」。</p></div>';
        },
        onDraw: function () {
            var reset = document.getElementById('comment-empty-reset');
            if (reset) reset.addEventListener('click', function () {
                form.reset();
                QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
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
                return U.post('/admin/video/comments/save', data).then(function (res) {
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
        U.post('/admin/video/comments/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '操作失败', 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || '操作成功', 'ok');
        });
    }
    function setStatus(row, status) {
        U.post('/admin/video/comments/save', {id: row.id, status: status}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
            table.refresh();
            U.toast(status === 1 ? '已通过' : '已隐藏', 'ok');
        });
    }

    U.on('#comment-search-btn', 'click', runSearch);
    U.on('#comment-reset-btn', 'click', function () {
        setTimeout(function () {
            QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
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
            U.post('/admin/video/comments/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh();
                U.toast('已删除', 'ok');
            });
        }
    });
})();
</script>
@endpush
