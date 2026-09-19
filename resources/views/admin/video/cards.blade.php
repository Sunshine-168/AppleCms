@extends('admin.layouts.inner')
@section('title', $title)

@php
    $queues = $queues ?? ['all' => 0, 'unused' => 0, 'used' => 0, 'void' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
@endphp

@section('plain')
<div class="card card-panel card-index">
    <div class="card-header">
        <span>积分卡密 <em id="card-count"></em></span>
        <div>
            <a class="btn btn-muted btn-sm" href="/admin/video/members">会员</a>
            <button type="button" class="btn btn-muted btn-sm" id="card-add-btn">手输一张</button>
            <button type="button" class="btn btn-sm" id="card-gen-btn">批量生成</button>
        </div>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="card-search" onsubmit="return false;">
            <input type="hidden" name="queue">
            <input type="hidden" name="used_by">
            <input type="search" name="q" placeholder="搜卡密、使用者或会员 ID" autocomplete="off" aria-label="搜索卡密">
            <button type="button" class="btn btn-sm" id="card-search-btn">搜索</button>
            <button type="reset" class="btn btn-muted btn-sm" id="card-reset-btn">重置</button>
        </form>
        <div class="queue-chips" id="card-queues">
            <button type="button" class="chip" data-queue="">全部@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="queue" data-value="unused">未用@if($q('unused') > 0)<em>{{ $q('unused') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="queue" data-value="used">已兑@if($q('used') > 0)<em>{{ $q('used') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="queue" data-value="void">作废@if($q('void') > 0)<em>{{ $q('void') }}</em>@endif</button>
        </div>
        <p class="muted recycle-lead">卡密给线下卖或发给会员。会员中心兑换后加积分，每张只能用一次。作废后不能兑；已兑的不能改也不能删。</p>
        <div class="batch-bar" id="card-batch" hidden>
            <strong id="card-batch-count">已选 0 张</strong>
            <button type="button" class="btn btn-sm" id="card-batch-copy">复制卡密</button>
            <button type="button" class="btn btn-muted btn-sm" id="card-batch-void">作废</button>
            <button type="button" class="btn btn-danger btn-sm" id="card-batch-del">删除</button>
            <button type="button" class="btn btn-muted btn-sm" id="card-batch-clear">取消选择</button>
        </div>
        <div id="card-table"></div>
    </div>
</div>
<template id="card-dialog-tpl">
    <form>
        <input type="hidden" name="id">
        <label>卡密</label>
        <input type="text" name="code" placeholder="留空自动生成" maxlength="40" autocomplete="off">
        <p class="muted field-hint">给会员兑换用的一串字符。建议用批量生成，这里只用来补一张指定号码。</p>
        <label>积分</label>
        <input type="number" name="points" value="100" min="1">
        <p class="muted field-hint">兑成后加到会员账上。已兑的不能改。</p>
    </form>
</template>
<template id="card-gen-tpl">
    <form>
        <label>张数</label>
        <input type="number" name="count" value="10" min="1" max="200">
        <p class="muted field-hint">一次最多 200 张。生成后会弹出号码，方便复制发出去。</p>
        <label>每张积分</label>
        <input type="number" name="points" value="100" min="1">
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var form = document.getElementById('card-search');
    var qs = new URLSearchParams(location.search);
    if (qs.get('used_by') && form.used_by) form.used_by.value = qs.get('used_by');
    var batchBar = document.getElementById('card-batch');
    var batchCount = document.getElementById('card-batch-count');
    var countEl = document.getElementById('card-count');

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
        U.qa('#card-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = (key === '' && queue === '') || (key === 'queue' && queue === val);
            chip.classList.toggle('active', on);
        });
    }
    function applyQueue(key, value) {
        form.queue.value = key === 'queue' ? (value || '') : '';
        form.used_by.value = '';
        runSearch();
    }
    function runSearch() {
        table.reload(queryWhere());
        markChips();
    }
    function fmtTime(ts) {
        ts = parseInt(ts, 10) || 0;
        if (!ts) return '—';
        var d = new Date(ts * 1000);
        var now = new Date();
        var pad = function (n) { return n < 10 ? '0' + n : '' + n; };
        var hm = pad(d.getHours()) + ':' + pad(d.getMinutes());
        if (d.toDateString() === now.toDateString()) return '今天 ' + hm;
        var y = new Date(now);
        y.setDate(now.getDate() - 1);
        if (d.toDateString() === y.toDateString()) return '昨天 ' + hm;
        if (d.getFullYear() === now.getFullYear()) return pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + ' ' + hm;
        return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
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
        if (state === 'used') return '<span class="badge badge-ok">已兑</span>';
        if (state === 'void') return '<span class="badge badge-off">作废</span>';
        return '<span class="badge badge-warn">未用</span>';
    }
    function titleHtml(d) {
        var meta = (parseInt(d.points, 10) || 0) + ' 积分';
        if (d.created_at) meta += ' · 生成 ' + fmtTime(d.created_at);
        return '<div class="entry-row-title-line"><a class="entry-row-title card-code js-copy" href="#">' + U.escape(d.code || '') + '</a> ' + badgeHtml(d.state) + '</div>'
            + '<div class="entry-row-meta">' + U.escape(meta) + '</div>';
    }
    function userHtml(d) {
        if (d.state !== 'used' || !(parseInt(d.used_by, 10) > 0)) {
            return '<span class="muted">—</span>';
        }
        var name = d.member_name ? U.escape(d.member_name) : ('会员 #' + U.escape(d.used_by));
        var extra = d.member_email ? '<div class="entry-row-meta">' + U.escape(d.member_email) + '</div>' : '';
        return '<a href="/admin/video/members?q=' + encodeURIComponent(d.used_by) + '">' + name + '</a>' + extra;
    }
    function statusHtml(d) {
        if (d.state === 'used') return U.status(true, '已兑');
        if (d.state === 'void') return U.status(false, '作废');
        return '<span class="status status-warn">未用</span>';
    }

    var table = U.table({
        el: '#card-table',
        countEl: countEl,
        url: '/admin/video/cards/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>没有符合条件的卡密。</p><p><button type="button" class="btn btn-muted btn-sm" id="card-empty-reset">清除筛选</button></p></div>';
            }
            return '<div class="list-empty"><p>还没有卡密。</p><p class="muted">批量生成后发给会员，他们在会员中心兑换加积分。</p><p><button type="button" class="btn btn-primary btn-sm" id="card-empty-gen">批量生成</button></p></div>';
        },
        onDraw: function (_wrap, list) {
            var gen = document.getElementById('card-empty-gen');
            var reset = document.getElementById('card-empty-reset');
            if (gen) gen.addEventListener('click', openGenerate);
            if (reset) reset.addEventListener('click', function () { form.reset(); runSearch(); });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = '已选 ' + ids.length + ' 张';
        },
        cols: [
            {check: true, width: 36},
            {title: '卡密', html: titleHtml},
            {title: '积分', width: 72, html: function (d) { return U.escape(String(d.points == null ? '' : d.points)); }},
            {title: '兑换人', html: userHtml},
            {title: '状态', width: 72, html: statusHtml},
            {title: '兑换时间', width: 120, html: function (d) { return d.state === 'used' ? fmtTime(d.used_at) : '—'; }},
            {title: '操作', cls: 'actions', html: function (d) {
                var html = '<a href="#" class="btn-link js-copy">复制</a>';
                if (d.state === 'unused') {
                    html += '<a href="#" class="btn-link js-edit">改积分</a><a href="#" class="btn-link js-void">作废</a><a href="#" class="btn-link js-del">删除</a>';
                } else if (d.state === 'void') {
                    html += '<a href="#" class="btn-link js-on">恢复</a><a href="#" class="btn-link js-del">删除</a>';
                }
                return html;
            }}
        ]
    });
    markChips();

    function openDialog(mode, row) {
        row = row || {};
        U.dialog({
            title: mode === 'edit' ? '改积分' : '手输一张',
            content: document.getElementById('card-dialog-tpl').innerHTML,
            onOpen: function (body) {
                var codeInput = body.querySelector('[name=code]');
                U.fillForm(body.querySelector('form'), {
                    id: mode === 'edit' ? (row.id || '') : '',
                    code: row.code || '',
                    points: row.points == null ? 100 : row.points
                });
                if (mode === 'edit' && codeInput) {
                    codeInput.readOnly = true;
                    codeInput.placeholder = '';
                }
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (mode === 'edit') {
                    data.id = row.id;
                    data.code = row.code;
                } else {
                    delete data.id;
                }
                if (!data.points || parseInt(data.points, 10) < 1) { U.toast('积分至少为 1', 'err'); return false; }
                return U.post('/admin/video/cards/save', data).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return false; }
                    U.toast(mode === 'edit' ? '已保存' : '已创建', 'ok');
                    table.refresh();
                });
            }
        });
    }
    function openGenerate() {
        U.dialog({
            title: '批量生成',
            content: document.getElementById('card-gen-tpl').innerHTML,
            okText: '生成',
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                var count = parseInt(data.count, 10) || 0;
                var points = parseInt(data.points, 10) || 0;
                if (count < 1) { U.toast('请填写张数', 'err'); return false; }
                if (points < 1) { U.toast('积分至少为 1', 'err'); return false; }
                return U.post('/admin/video/cards/generate', {count: count, points: points}).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return false; }
                    table.refresh();
                    showCodes((res.data && res.data.codes) || [], (res.msg || '已生成') + '，复制后发给会员');
                });
            }
        });
    }
    function showCodes(codes, title) {
        codes = Array.isArray(codes) ? codes : [];
        if (!codes.length) { U.toast(title || '已生成', 'ok'); return; }
        var text = codes.join('\n');
        U.dialog({
            title: title || ('已生成 ' + codes.length + ' 张'),
            content: '<p class="muted field-hint">未用卡密，发出去前先复制。关闭后仍可在列表里勾选再复制。</p><textarea class="card-codes" readonly>' + U.escape(text) + '</textarea>',
            okText: '复制全部',
            cancelText: '关闭',
            onSave: function () {
                copyText(text, '已复制 ' + codes.length + ' 张');
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
        if (!ids.length) { U.toast('请先勾选卡密', 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/cards/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '操作失败', 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || '操作成功', 'ok');
        });
    }
    function setStatus(row, status) {
        U.post('/admin/video/cards/save', {id: row.id, status: status}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
            table.refresh();
            U.toast(status === 1 ? '已恢复' : '已作废', 'ok');
        });
    }

    U.on('#card-search-btn', 'click', runSearch);
    U.on('#card-reset-btn', 'click', function () { setTimeout(runSearch, 0); });
    U.on('#card-add-btn', 'click', function () { openDialog('add'); });
    U.on('#card-gen-btn', 'click', openGenerate);
    document.getElementById('card-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#card-batch-copy', 'click', function () {
        var codes = selectedCodes();
        if (!codes.length) { U.toast('请先勾选卡密', 'err'); return; }
        copyText(codes.join('\n'), '已复制 ' + codes.length + ' 张');
    });
    U.on('#card-batch-void', 'click', function () { batch('status', 0, '作废选中卡密？已兑的不会动。'); });
    U.on('#card-batch-del', 'click', function () { batch('delete', '', '删除选中卡密？已兑的不会删。'); });
    U.on('#card-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#card-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        if (a.getAttribute('href') && a.getAttribute('href').indexOf('/admin/video/members') === 0) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-copy')) copyText(row.code, '已复制卡密');
        if (a.classList.contains('js-edit')) openDialog('edit', row);
        if (a.classList.contains('js-void')) {
            if (!U.confirm('作废「' + (row.code || '') + '」？作废后不能再兑换。')) return;
            setStatus(row, 0);
        }
        if (a.classList.contains('js-on')) setStatus(row, 1);
        if (a.classList.contains('js-del')) {
            if (!U.confirm('确定删除「' + (row.code || '') + '」？')) return;
            U.post('/admin/video/cards/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh();
                U.toast('已删除', 'ok');
            });
        }
    });
})();
</script>
@endpush
