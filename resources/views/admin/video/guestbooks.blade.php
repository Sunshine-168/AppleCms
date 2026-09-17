@extends('admin.layouts.inner')
@section('title', $title)

@php
    $queues = $queues ?? ['all' => 0, 'pending' => 0, 'shown' => 0, 'noreply' => 0, 'today' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
    $audit = (bool) ($audit ?? false);
@endphp

@section('plain')
<div class="card card-panel gbook-index">
    <div class="card-header">
        <span>留言@if($q('pending') > 0) <em>· {{ $q('pending') }} 待审</em>@endif</span>
        <div>
            <a class="btn btn-muted btn-sm" href="/gbook" target="_blank" rel="noopener">前台留言板</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/comments">评论</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/config/comment">审核设置</a>
        </div>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="gbook-search" onsubmit="return false;">
            <input type="hidden" name="status">
            <input type="hidden" name="noreply">
            <input type="hidden" name="today">
            <input type="search" name="q" placeholder="搜内容、昵称或回复" autocomplete="off" aria-label="搜索留言">
            <button type="button" class="btn btn-sm" id="gbook-search-btn">查询</button>
            <button type="reset" class="btn btn-muted btn-sm" id="gbook-reset-btn">重置</button>
        </form>
        <div class="queue-chips" id="gbook-queues">
            <button type="button" class="chip" data-queue="">全部@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="0">待审@if($q('pending') > 0)<em>{{ $q('pending') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="1">已显示@if($q('shown') > 0)<em>{{ $q('shown') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="noreply" data-value="1">未回复@if($q('noreply') > 0)<em>{{ $q('noreply') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="today" data-value="1">今天@if($q('today') > 0)<em>{{ $q('today') }}</em>@endif</button>
        </div>
        @if($audit)
            <p class="muted recycle-lead">新留言先进待审，通过后才出现在前台留言板。回复会显示在访客那条下面。影片评论在「评论」。</p>
        @else
            <p class="muted recycle-lead">新留言会直接显示在前台。要先审再上，打开「审核设置」里的「留言要先审再显示」。回复会显示在访客那条下面。</p>
        @endif
        <div class="batch-bar" id="gbook-batch" hidden>
            <strong id="gbook-batch-count">已选 0 条</strong>
            <button type="button" class="btn btn-sm" id="gbook-batch-on">通过</button>
            <button type="button" class="btn btn-muted btn-sm" id="gbook-batch-off">隐藏</button>
            <button type="button" class="btn btn-danger btn-sm" id="gbook-batch-del">删除</button>
            <button type="button" class="btn btn-muted btn-sm" id="gbook-batch-clear">取消选择</button>
        </div>
        <div id="gbook-table"></div>
    </div>
</div>
<template id="gbook-dialog-tpl">
    <form>
        <input type="hidden" name="id">
        <label>昵称</label>
        <input type="text" name="author_name" readonly>
        <label>内容</label>
        <textarea name="content" rows="4" readonly></textarea>
        <label>回复</label>
        <textarea name="reply" rows="4" placeholder="写给访客看的回复"></textarea>
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
        var who = U.escape(d.author_name || d.member_name || (parseInt(d.member_id, 10) > 0 ? ('会员 #' + d.member_id) : '游客'));
        var meta = who;
        if (d.created_at_text) meta += ' · ' + U.escape(d.created_at_text);
        if (d.ip) meta += ' · ' + U.escape(d.ip);
        var html = '<div class="comment-cell"><div class="comment-body">' + U.escape(d.content || '') + '</div>'
            + '<div class="muted">' + meta + '</div>';
        if (d.reply) {
            html += '<div class="gbook-reply"><span class="muted">回复</span> ' + U.escape(d.reply) + '</div>';
        }
        html += '</div>';
        return html;
    }

    var table = U.table({
        el: '#gbook-table',
        url: '/admin/video/guestbooks/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>没有符合条件的留言</p><p><button type="button" class="btn btn-muted btn-sm" id="gbook-empty-reset">清除筛选</button></p></div>';
            }
            return '<div class="list-empty"><p>还没有留言</p><p class="muted">访客在前台留言板提交后会出现在这里。需要先审再显示时，打开右上角「审核设置」。</p></div>';
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
            batchCount.textContent = '已选 ' + ids.length + ' 条';
        },
        cols: [
            {check: true, width: 36},
            {title: '留言', html: contentHtml},
            {title: '状态', width: 88, html: function (d) {
                var html = parseInt(d.status, 10) === 1 ? U.status(true, '显示') : U.status(false, '待审');
                if (!parseInt(d.has_reply, 10)) html += '<div class="muted">未回复</div>';
                return html;
            }},
            {title: '操作', cls: 'actions', html: function (d) {
                var html = '<a href="#" class="btn-link js-reply">回复</a>';
                if (parseInt(d.status, 10) !== 1) html += '<a href="#" class="btn-link js-pass">通过</a>';
                else html += '<a href="#" class="btn-link js-hide">隐藏</a>';
                html += '<a href="#" class="btn-link js-del">删除</a>';
                return html;
            }}
        ]
    });
    markChips();

    function openReply(row) {
        U.dialog({
            title: '回复留言',
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
        if (!ids.length) { U.toast('请先勾选留言', 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/guestbooks/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '操作失败', 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || '操作成功', 'ok');
        });
    }
    function setStatus(row, status) {
        U.post('/admin/video/guestbooks/save', {id: row.id, status: status}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
            table.refresh();
            U.toast(status === 1 ? '已通过' : '已隐藏', 'ok');
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
    U.on('#gbook-batch-del', 'click', function () { batch('delete', '', '确认删除选中留言？'); });
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
            if (!U.confirm('删除这条留言？')) return;
            U.post('/admin/video/guestbooks/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh();
                U.toast('已删除', 'ok');
            });
        }
    });
})();
</script>
@endpush
