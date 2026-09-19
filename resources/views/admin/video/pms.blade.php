@extends('admin.layouts.inner')
@section('title', $title)

@php
    $queues = $queues ?? ['all' => 0, 'unread' => 0, 'today' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
    $toId = (int) ($toId ?? 0);
@endphp

@section('plain')
<div class="card card-panel pm-index">
    <div class="card-header">
        <span>站内信@if($q('unread') > 0) <em>· {{ $q('unread') }} 未读</em>@endif</span>
        <div>
            <button type="button" class="btn btn-sm" id="pm-add-btn">写给会员</button>
            <a class="btn btn-muted btn-sm" href="/admin/video/members">会员</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/notifies">会员通知</a>
        </div>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="pm-search" onsubmit="return false;">
            <input type="hidden" name="is_read">
            <input type="hidden" name="today">
            <input type="search" name="q" placeholder="搜标题、内容或会员" autocomplete="off" aria-label="搜索站内信">
            <button type="button" class="btn btn-sm" id="pm-search-btn">查询</button>
            <button type="reset" class="btn btn-muted btn-sm" id="pm-reset-btn">重置</button>
        </form>
        <div class="queue-chips" id="pm-queues">
            <button type="button" class="chip" data-queue="">全部@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="is_read" data-value="0">未读@if($q('unread') > 0)<em>{{ $q('unread') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="today" data-value="1">今天@if($q('today') > 0)<em>{{ $q('today') }}</em>@endif</button>
        </div>
        <p class="muted recycle-lead">发给指定会员，登录后在「站内信」里看。打开信箱会全部标已读。通知所有人用「会员通知」。</p>
        <div class="batch-bar" id="pm-batch" hidden>
            <strong id="pm-batch-count">已选 0 条</strong>
            <button type="button" class="btn btn-sm" id="pm-batch-read">标为已读</button>
            <button type="button" class="btn btn-danger btn-sm" id="pm-batch-del">删除</button>
            <button type="button" class="btn btn-muted btn-sm" id="pm-batch-clear">取消选择</button>
        </div>
        <div id="pm-table"></div>
    </div>
</div>
<template id="pm-compose-tpl">
    <form>
        <input type="hidden" name="from_id" value="0">
        <input type="hidden" name="is_read" value="0">
        <label for="pm-to">收件会员 ID</label>
        <input id="pm-to" type="number" name="to_id" min="1" placeholder="会员 ID" autocomplete="off">
        <p class="muted field-hint">发给指定的人。不知道 ID 去「会员」里查。</p>
        <label for="pm-title">标题</label>
        <input id="pm-title" type="text" name="title" maxlength="120" autocomplete="off">
        <label for="pm-content">内容</label>
        <textarea id="pm-content" name="content" rows="8"></textarea>
    </form>
</template>
<template id="pm-view-tpl">
    <form>
        <label>收件人</label>
        <input type="text" name="to_label" readonly>
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
    function letterHtml(d) {
        var fromName = d.from_name || (parseInt(d.from_id, 10) > 0 ? ('会员 #' + d.from_id) : '系统');
        var toName = d.to_name || (parseInt(d.to_id, 10) > 0 ? ('会员 #' + d.to_id) : '—');
        var meta = U.escape(fromName) + ' → ' + U.escape(toName);
        if (d.created_at_text) meta += ' · ' + U.escape(d.created_at_text);
        var preview = d.content_preview || '';
        return '<div class="comment-cell"><div class="entry-row-title-line"><span class="entry-row-title">' + U.escape(d.title || '（无标题）') + '</span></div>'
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
                return '<div class="list-empty"><p>没有符合条件的站内信</p><p><button type="button" class="btn btn-muted btn-sm" id="pm-empty-reset">清除筛选</button></p></div>';
            }
            return '<div class="list-empty"><p>还没有站内信</p><p class="muted">点右上角「写给会员」发给指定的人。全站公告在「会员通知」。</p><p><button type="button" class="btn btn-primary btn-sm" id="pm-empty-add">写给会员</button></p></div>';
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
            batchCount.textContent = '已选 ' + ids.length + ' 条';
        },
        cols: [
            {check: true, width: 36},
            {title: '信件', html: letterHtml},
            {title: '状态', width: 88, html: function (d) {
                return parseInt(d.is_read, 10) === 1 ? U.status(true, '已读') : U.status(false, '未读');
            }},
            {title: '操作', cls: 'actions', html: function (d) {
                var html = '<a href="#" class="btn-link js-view">查看</a>';
                if (parseInt(d.is_read, 10) !== 1) html += '<a href="#" class="btn-link js-read">已读</a>';
                if (parseInt(d.to_id, 10) > 0) {
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
            title: '写给会员',
            okText: '发送',
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
                if (!parseInt(data.to_id, 10)) { U.toast('请填写收件会员 ID', 'err'); return false; }
                if (!String(data.title || '').trim()) { U.toast('请填写标题', 'err'); return false; }
                if (!String(data.content || '').trim()) { U.toast('请填写内容', 'err'); return false; }
                data.from_id = 0;
                data.is_read = 0;
                return U.post('/admin/video/pms/save', data).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return false; }
                    U.toast('已发送', 'ok');
                    table.refresh();
                });
            }
        });
    }
    function openView(row) {
        U.dialog({
            title: '查看站内信',
            hideOk: true,
            cancelText: '关闭',
            content: document.getElementById('pm-view-tpl').innerHTML,
            onOpen: function (body) {
                var toName = row.to_name || (parseInt(row.to_id, 10) > 0 ? ('会员 #' + row.to_id) : '—');
                U.fillForm(body.querySelector('form'), {
                    to_label: toName,
                    title: row.title || '',
                    content: row.content || ''
                });
                var meta = body.querySelector('[data-role=meta]');
                if (meta) {
                    var fromName = row.from_name || (parseInt(row.from_id, 10) > 0 ? ('会员 #' + row.from_id) : '系统');
                    meta.textContent = fromName + (row.created_at_text ? (' · ' + row.created_at_text) : '');
                }
            }
        });
    }
    function selectedIds() { return table.selectedIds(); }
    function batch(action, value, confirmText) {
        var ids = selectedIds();
        if (!ids.length) { U.toast('请先勾选站内信', 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/pms/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '操作失败', 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || '操作成功', 'ok');
        });
    }
    function markRead(row) {
        U.post('/admin/video/pms/save', {id: row.id, is_read: 1}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
            table.refresh();
            U.toast('已标为已读', 'ok');
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
    U.on('#pm-batch-del', 'click', function () { batch('delete', '', '删除选中站内信？会员那边也会看不到。'); });
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
            if (!U.confirm('删除这封站内信？会员那边也会看不到。')) return;
            U.post('/admin/video/pms/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh();
                U.toast('已删除', 'ok');
            });
        }
    });
    if (toPrefill > 0) openCompose({to_id: toPrefill});
})();
</script>
@endpush
