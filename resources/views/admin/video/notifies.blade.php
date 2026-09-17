@extends('admin.layouts.inner')
@section('title', $title)

@php
    $queues = $queues ?? ['all' => 0, 'all_members' => 0, 'one' => 0, 'unread' => 0, 'today' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
    $memberId = (int) ($memberId ?? 0);
@endphp

@section('plain')
<div class="card card-panel notify-index">
    <div class="card-header">
        <span>会员通知@if($q('unread') > 0) <em>· {{ $q('unread') }} 未读</em>@endif</span>
        <div>
            <button type="button" class="btn btn-sm" id="notify-add-btn">发通知</button>
            <a class="btn btn-muted btn-sm" href="/admin/video/pms">站内信</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/members">会员</a>
        </div>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="notify-search" onsubmit="return false;">
            <input type="hidden" name="is_read">
            <input type="hidden" name="today">
            <input type="hidden" name="audience">
            <input type="search" name="q" placeholder="搜标题、内容或会员" autocomplete="off" aria-label="搜索会员通知">
            <button type="button" class="btn btn-sm" id="notify-search-btn">查询</button>
            <button type="reset" class="btn btn-muted btn-sm" id="notify-reset-btn">重置</button>
        </form>
        <div class="queue-chips" id="notify-queues">
            <button type="button" class="chip" data-queue="">全部@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="audience" data-value="all">全站@if($q('all_members') > 0)<em>{{ $q('all_members') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="audience" data-value="one">指定会员@if($q('one') > 0)<em>{{ $q('one') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="is_read" data-value="0">未读@if($q('unread') > 0)<em>{{ $q('unread') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="today" data-value="1">今天@if($q('today') > 0)<em>{{ $q('today') }}</em>@endif</button>
        </div>
        <p class="muted recycle-lead">会员 ID 为 0（或不填）发给全部会员；填指定 ID 只给那一个人。一对一请用「站内信」——会员登录后在会员中心能看到。这里可标已读；删掉后记录就没了。</p>
        <div class="batch-bar" id="notify-batch" hidden>
            <strong id="notify-batch-count">已选 0 条</strong>
            <button type="button" class="btn btn-sm" id="notify-batch-read">标为已读</button>
            <button type="button" class="btn btn-danger btn-sm" id="notify-batch-del">删除</button>
            <button type="button" class="btn btn-muted btn-sm" id="notify-batch-clear">取消选择</button>
        </div>
        <div id="notify-table"></div>
    </div>
</div>
<template id="notify-compose-tpl">
    <form>
        <input type="hidden" name="is_read" value="0">
        <label for="notify-scope">范围</label>
        <select id="notify-scope" name="scope">
            <option value="0">全部会员</option>
            <option value="1">指定会员</option>
        </select>
        <label for="notify-member">指定会员 ID</label>
        <input id="notify-member" type="number" name="member_id" min="1" placeholder="会员 ID" autocomplete="off">
        <p class="muted field-hint">默认发给全部会员。只给一个人填 ID；一对一更适合用「站内信」。</p>
        <label for="notify-title">标题</label>
        <input id="notify-title" type="text" name="title" maxlength="120" autocomplete="off">
        <label for="notify-content">内容</label>
        <textarea id="notify-content" name="content" rows="8"></textarea>
    </form>
</template>
<template id="notify-view-tpl">
    <form>
        <label>范围</label>
        <input type="text" name="audience_label" readonly>
        <label>标题</label>
        <input type="text" name="title" readonly>
        <label>内容</label>
        <textarea name="content" rows="8" readonly></textarea>
        <p class="muted field-hint" data-role="meta"></p>
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
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
    function noticeHtml(d) {
        var audience = d.audience_label || (parseInt(d.member_id, 10) > 0 ? ('会员 #' + d.member_id) : '全站');
        var meta = U.escape(audience);
        if (d.created_at_text) meta += ' · ' + U.escape(d.created_at_text);
        var preview = d.content_preview || '';
        return '<div class="comment-cell"><div class="entry-row-title-line"><span class="entry-row-title">' + U.escape(d.title || '（无标题）') + '</span></div>'
            + (preview ? '<div class="muted">' + U.escape(preview) + '</div>' : '')
            + '<div class="muted">' + meta + '</div></div>';
    }

    var table = U.table({
        el: '#notify-table',
        url: '/admin/video/notifies/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>没有符合条件的通知</p><p><button type="button" class="btn btn-muted btn-sm" id="notify-empty-reset">清除筛选</button></p></div>';
            }
            return '<div class="list-empty"><p>还没有通知</p><p class="muted">点右上角「发通知」。默认发给全部会员；只要一个人用「站内信」。</p><p><button type="button" class="btn btn-primary btn-sm" id="notify-empty-add">发通知</button></p></div>';
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
            batchCount.textContent = '已选 ' + ids.length + ' 条';
        },
        cols: [
            {check: true, width: 36},
            {title: '通知', html: noticeHtml},
            {title: '状态', width: 88, html: function (d) {
                return parseInt(d.is_read, 10) === 1 ? U.status(true, '已读') : U.status(false, '未读');
            }},
            {title: '操作', cls: 'actions', html: function (d) {
                var html = '<a href="#" class="btn-link js-view">查看</a>';
                if (parseInt(d.is_read, 10) !== 1) html += '<a href="#" class="btn-link js-read">已读</a>';
                if (parseInt(d.member_id, 10) > 0) {
                    html += '<a class="btn-link" href="/admin/video/members">会员</a>';
                }
                html += '<a href="#" class="btn-link js-del">删除</a>';
                return html;
            }}
        ]
    });
    markChips();

    function openCompose(row) {
        row = row || {};
        U.dialog({
            title: '发通知',
            okText: '发送',
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
                    if (mid < 1) { U.toast('请填写指定会员 ID', 'err'); return false; }
                } else {
                    mid = 0;
                }
                if (!String(data.title || '').trim()) { U.toast('请填写标题', 'err'); return false; }
                if (!String(data.content || '').trim()) { U.toast('请填写内容', 'err'); return false; }
                data.member_id = mid;
                data.is_read = 0;
                delete data.scope;
                return U.post('/admin/video/notifies/save', data).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return false; }
                    U.toast('已发送', 'ok');
                    table.refresh();
                });
            }
        });
    }
    function openView(row) {
        U.dialog({
            title: '查看通知',
            hideOk: true,
            cancelText: '关闭',
            content: document.getElementById('notify-view-tpl').innerHTML,
            onOpen: function (body) {
                var audience = row.audience_label || (parseInt(row.member_id, 10) > 0 ? ('会员 #' + row.member_id) : '全站');
                U.fillForm(body.querySelector('form'), {
                    audience_label: audience,
                    title: row.title || '',
                    content: row.content || ''
                });
                var meta = body.querySelector('[data-role=meta]');
                if (meta) {
                    var status = parseInt(row.is_read, 10) === 1 ? '已读' : '未读';
                    meta.textContent = status + (row.created_at_text ? (' · ' + row.created_at_text) : '');
                }
            }
        });
    }
    function selectedIds() { return table.selectedIds(); }
    function batch(action, value, confirmText) {
        var ids = selectedIds();
        if (!ids.length) { U.toast('请先勾选通知', 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/notifies/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '操作失败', 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || '操作成功', 'ok');
        });
    }
    function markRead(row) {
        U.post('/admin/video/notifies/save', {id: row.id, is_read: 1}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
            table.refresh();
            U.toast('已标为已读', 'ok');
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
    U.on('#notify-batch-del', 'click', function () { batch('delete', '', '删除选中通知？删掉后会员也看不到。'); });
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
            if (!U.confirm('删除这条通知？删掉后会员也看不到。')) return;
            U.post('/admin/video/notifies/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh();
                U.toast('已删除', 'ok');
            });
        }
    });
    if (memberPrefill > 0) openCompose({member_id: memberPrefill});
})();
</script>
@endpush
