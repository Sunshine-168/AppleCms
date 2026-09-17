@extends('admin.layouts.inner')
@section('title', $title)

@php
    $queues = $queues ?? ['all' => 0, 'unused' => 0, 'used' => 0, 'void' => 0, 'today' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
@endphp

@section('plain')
<div class="card card-panel invite-index">
    <div class="card-header">
        <span>邀请码 <em id="invite-count"></em></span>
        <div>
            <a class="btn btn-muted btn-sm" href="/admin/video/members">会员</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/settings?tab=interact">注册设置</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/cards">积分卡密</a>
            <button type="button" class="btn btn-sm" id="invite-gen-btn">批量生成</button>
        </div>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="invite-search" onsubmit="return false;">
            <input type="hidden" name="queue">
            <input type="hidden" name="today">
            <input type="search" name="q" placeholder="搜邀请码或会员" autocomplete="off" aria-label="搜索邀请码">
            <button type="button" class="btn btn-sm" id="invite-search-btn">搜索</button>
            <button type="reset" class="btn btn-muted btn-sm" id="invite-reset-btn">重置</button>
        </form>
        <div class="queue-chips" id="invite-queues">
            <button type="button" class="chip" data-queue="">全部@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="queue" data-value="unused">未用@if($q('unused') > 0)<em>{{ $q('unused') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="queue" data-value="used">已用@if($q('used') > 0)<em>{{ $q('used') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="queue" data-value="void">作废@if($q('void') > 0)<em>{{ $q('void') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="today" data-value="1">今天@if($q('today') > 0)<em>{{ $q('today') }}</em>@endif</button>
        </div>
        <p class="muted recycle-lead">注册用的码，一码一次。批量生成可带邀请人会员 ID 和积分：注册成功后新会员加这份积分；邀请人 ID 大于 0 时，邀请人也加同样积分。已用的不能删。卡密是兑积分，邀请码是注册。</p>
        <div class="batch-bar" id="invite-batch" hidden>
            <strong id="invite-batch-count">已选 0 个</strong>
            <button type="button" class="btn btn-sm" id="invite-batch-copy">复制邀请码</button>
            <button type="button" class="btn btn-muted btn-sm" id="invite-batch-void">作废</button>
            <button type="button" class="btn btn-danger btn-sm" id="invite-batch-del">删除</button>
            <button type="button" class="btn btn-muted btn-sm" id="invite-batch-clear">取消选择</button>
        </div>
        <div id="invite-table"></div>
    </div>
</div>
<template id="invite-gen-tpl">
    <form>
        <label>数量</label>
        <input type="number" name="count" value="10" min="1" max="200">
        <p class="muted field-hint">一次最多 200 个。生成后会弹出号码，方便复制发给要注册的人。</p>
        <label>积分</label>
        <input type="number" name="points" value="0" min="0">
        <p class="muted field-hint">注册成功后新会员加这份积分；邀请人会员 ID 大于 0 时，邀请人也加同样积分。填 0 则不加。</p>
        <label>邀请人会员 ID</label>
        <input type="number" name="member_id" value="0" min="0">
        <p class="muted field-hint">0 = 系统/无归属。填会员 ID 则这批码归该会员，用掉后邀请人也加积分。</p>
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var form = document.getElementById('invite-search');
    var batchBar = document.getElementById('invite-batch');
    var batchCount = document.getElementById('invite-batch-count');
    var countEl = document.getElementById('invite-count');
    var QUEUE_KEYS = ['today'];

    function cleanWhere(data) {
        var out = {};
        Object.keys(data).forEach(function (k) { if (data[k] !== '') out[k] = data[k]; });
        return out;
    }
    function queryWhere() {
        return Object.assign({limit: 20}, cleanWhere(U.formData(form)));
    }
    function isFiltered(where) {
        return Object.keys(where || {}).some(function (k) { return k !== 'limit' && where[k] !== ''; });
    }
    function markChips() {
        var queue = form.queue.value;
        var today = form.today.value;
        U.qa('#invite-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = false;
            if (key === '' && queue === '' && today === '') on = true;
            else if (key === 'queue' && today === '' && queue === val) on = true;
            else if (key === 'today' && today === val) on = true;
            chip.classList.toggle('active', on);
        });
    }
    function applyQueue(key, value) {
        QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
        form.queue.value = '';
        if (key === 'queue') form.queue.value = value || '';
        else if (key && form[key]) form[key].value = value || '1';
        runSearch();
    }
    function runSearch() {
        table.reload(queryWhere());
        markChips();
    }
    function copyText(text, okMsg) {
        text = String(text || '').trim();
        if (!text) { U.toast('没有可复制的内容', 'err'); return; }
        function ok() { U.toast(okMsg || '已复制', 'ok'); }
        function fail() { U.toast('复制失败，请手动选择', 'err'); }
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(ok).catch(function () {
                fallback();
            });
            return;
        }
        fallback();
        function fallback() {
            var ta = document.createElement('textarea');
            ta.value = text;
            ta.setAttribute('readonly', '');
            ta.style.position = 'fixed';
            ta.style.left = '-9999px';
            document.body.appendChild(ta);
            ta.select();
            try {
                document.execCommand('copy') ? ok() : fail();
            } catch (e) {
                fail();
            }
            ta.remove();
        }
    }
    function badgeHtml(state) {
        if (state === 'used') return '<span class="badge badge-ok">已用</span>';
        if (state === 'void') return '<span class="badge badge-off">作废</span>';
        return '<span class="badge badge-warn">未用</span>';
    }
    function memberHref(id) {
        id = parseInt(id, 10) || 0;
        if (id > 0) return '/admin/video/members?q=' + encodeURIComponent(id);
        return '/admin/video/members';
    }
    function titleHtml(d) {
        var owner = d.owner_name || (parseInt(d.member_id, 10) > 0 ? ('会员 #' + d.member_id) : '系统');
        var meta = ['邀请人 ' + owner];
        if (d.state === 'used') {
            meta.push('注册人 ' + (d.used_name || ('会员 #' + d.used_by)));
        }
        meta.push((parseInt(d.points, 10) || 0) + ' 积分');
        if (d.created_at_text) meta.push(d.created_at_text);
        return '<div class="entry-row-title-line"><a class="entry-row-title invite-code js-copy" href="#">' + U.escape(d.code || '') + '</a> ' + badgeHtml(d.state) + '</div>'
            + '<div class="entry-row-meta">' + U.escape(meta.join(' · ')) + '</div>';
    }
    function statusHtml(d) {
        if (d.state === 'used') return U.status(true, '已用');
        if (d.state === 'void') return U.status(false, '作废');
        return '<span class="status status-warn">未用</span>';
    }
    function memberLinkId(d) {
        if (parseInt(d.used_by, 10) > 0) return d.used_by;
        if (parseInt(d.member_id, 10) > 0) return d.member_id;
        return 0;
    }

    var table = U.table({
        el: '#invite-table',
        url: '/admin/video/invites/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>没有符合条件的邀请码。</p><p><button type="button" class="btn btn-muted btn-sm" id="invite-empty-reset">清除筛选</button></p></div>';
            }
            return '<div class="list-empty"><p>还没有邀请码</p><p class="muted">点「批量生成」。发给要注册的人。注册时填了有效码才会加积分。</p><p><button type="button" class="btn btn-primary btn-sm" id="invite-empty-gen">批量生成</button></p></div>';
        },
        onDraw: function (wrap, list) {
            countEl.textContent = list.length ? '· ' + list.length : '';
            U.qa('tbody tr[data-idx]', wrap).forEach(function (tr) {
                var d = list[parseInt(tr.getAttribute('data-idx'), 10)];
                if (d && d.state === 'void') tr.classList.add('is-off');
            });
            var gen = document.getElementById('invite-empty-gen');
            var reset = document.getElementById('invite-empty-reset');
            if (gen) gen.addEventListener('click', openGenerate);
            if (reset) reset.addEventListener('click', function () {
                form.reset();
                QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
                form.queue.value = '';
                runSearch();
            });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = '已选 ' + ids.length + ' 个';
        },
        cols: [
            {check: true, width: 36},
            {title: '邀请码', html: titleHtml},
            {title: '状态', width: 72, html: statusHtml},
            {title: '操作', cls: 'actions', html: function (d) {
                var html = '<a href="#" class="btn-link js-copy">复制</a>';
                if (d.state === 'unused') {
                    html += '<a href="#" class="btn-link js-void">作废</a><a href="#" class="btn-link js-del">删除</a>';
                } else if (d.state === 'void') {
                    html += '<a href="#" class="btn-link js-on">恢复</a><a href="#" class="btn-link js-del">删除</a>';
                }
                html += '<a class="btn-link" href="' + memberHref(memberLinkId(d)) + '">会员</a>';
                return html;
            }}
        ]
    });
    markChips();

    function openGenerate() {
        U.dialog({
            title: '批量生成',
            content: document.getElementById('invite-gen-tpl').innerHTML,
            okText: '生成',
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                var count = parseInt(data.count, 10) || 0;
                var points = parseInt(data.points, 10);
                if (isNaN(points) || points < 0) points = 0;
                var memberId = parseInt(data.member_id, 10) || 0;
                if (count < 1) { U.toast('请填写数量', 'err'); return false; }
                return U.post('/admin/video/invites/generate', {count: count, points: points, member_id: memberId}).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return false; }
                    table.refresh();
                    showCodes((res.data && res.data.codes) || [], (res.msg || '已生成') + '，复制后发给要注册的人');
                });
            }
        });
    }
    function showCodes(codes, title) {
        codes = Array.isArray(codes) ? codes : [];
        if (!codes.length) { U.toast(title || '已生成', 'ok'); return; }
        var text = codes.join('\n');
        U.dialog({
            title: title || ('已生成 ' + codes.length + ' 个'),
            content: '<p class="muted field-hint">未用邀请码，发出去前先复制。关闭后仍可在列表里勾选再复制。</p><textarea class="invite-codes" readonly>' + U.escape(text) + '</textarea>',
            okText: '复制全部',
            cancelText: '关闭',
            onSave: function () {
                copyText(text, '已复制 ' + codes.length + ' 个');
                return false;
            }
        });
    }

    function selectedIds() { return table.selectedIds(); }
    function selectedCodes() {
        var ids = selectedIds();
        var map = {};
        ids.forEach(function (id) { map[String(id)] = true; });
        var codes = [];
        (table.rows() || []).forEach(function (row) {
            if (map[String(row.id)] && row.code) codes.push(row.code);
        });
        return codes;
    }
    function batch(action, value, confirmText) {
        var ids = selectedIds();
        if (!ids.length) { U.toast('请先勾选邀请码', 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/invites/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '操作失败', 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || '操作成功', 'ok');
        });
    }
    function setStatus(row, status) {
        U.post('/admin/video/invites/save', {id: row.id, status: status}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
            table.refresh();
            U.toast(status === 1 ? '已恢复' : '已作废', 'ok');
        });
    }

    U.on('#invite-search-btn', 'click', runSearch);
    U.on('#invite-reset-btn', 'click', function () { setTimeout(runSearch, 0); });
    U.on('#invite-gen-btn', 'click', openGenerate);
    document.getElementById('invite-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#invite-batch-copy', 'click', function () {
        var codes = selectedCodes();
        if (!codes.length) { U.toast('请先勾选邀请码', 'err'); return; }
        copyText(codes.join('\n'), '已复制 ' + codes.length + ' 个');
    });
    U.on('#invite-batch-void', 'click', function () { batch('status', 0, '作废选中邀请码？已用的不会动。'); });
    U.on('#invite-batch-del', 'click', function () { batch('delete', '', '删除选中邀请码？已用的不会删。'); });
    U.on('#invite-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#invite-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        if (a.getAttribute('href') && a.getAttribute('href').indexOf('/admin/video/members') === 0) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-copy')) copyText(row.code, '已复制邀请码');
        if (a.classList.contains('js-void')) {
            if (!U.confirm('作废「' + (row.code || '') + '」？作废后不能再用来注册。')) return;
            setStatus(row, 0);
        }
        if (a.classList.contains('js-on')) setStatus(row, 1);
        if (a.classList.contains('js-del')) {
            if (!U.confirm('确定删除「' + (row.code || '') + '」？')) return;
            U.post('/admin/video/invites/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh();
                U.toast('已删除', 'ok');
            });
        }
    });
})();
</script>
@endpush
