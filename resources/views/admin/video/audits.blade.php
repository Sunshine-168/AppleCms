@extends('admin.layouts.inner')
@section('title', $title)

@php
    $queues = $queues ?? ['all' => 0, 'on' => 0, 'off' => 0, 'skip' => 0, 'review' => 0, 'replace' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
@endphp

@section('plain')
<div class="card card-panel audit-index">
    <div class="card-header">
        <span>入库审核规则 <em id="audit-count"></em></span>
        <div>
            <a class="btn btn-sm" href="/admin/video/audits/create">新增规则</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/collects">采集源</a>
            <a class="btn btn-muted btn-sm" href="/admin/video?status=0">下架影片</a>
        </div>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="audit-search" onsubmit="return false;">
            <input type="hidden" name="status">
            <input type="hidden" name="action">
            <input type="hidden" name="scope">
            <input type="search" name="q" placeholder="搜名称或关键词" autocomplete="off" aria-label="搜索审核规则">
            <button type="button" class="btn btn-sm" id="audit-search-btn">查询</button>
            <button type="reset" class="btn btn-muted btn-sm" id="audit-reset-btn">重置</button>
        </form>
        <div class="queue-chips" id="audit-queues">
            <button type="button" class="chip" data-queue="">全部@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="1">启用@if($q('on') > 0)<em>{{ $q('on') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="0">停用@if($q('off') > 0)<em>{{ $q('off') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="action" data-value="skip">跳过@if($q('skip') > 0)<em>{{ $q('skip') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="action" data-value="review">下架入库@if($q('review') > 0)<em>{{ $q('review') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="action" data-value="replace">抠词@if($q('replace') > 0)<em>{{ $q('replace') }}</em>@endif</button>
        </div>
        <p class="muted recycle-lead">采集时按顺序匹配，命中第一条就停。跳过的片子不会进库；「入库并下架」能在影片列表里再上架。删规则不影响已经进库的片子。</p>
        <div class="batch-bar" id="audit-batch" hidden>
            <strong id="audit-batch-count">已选 0 条</strong>
            <button type="button" class="btn btn-sm" id="audit-batch-on">启用</button>
            <button type="button" class="btn btn-muted btn-sm" id="audit-batch-off">停用</button>
            <button type="button" class="btn btn-danger btn-sm" id="audit-batch-del">删除</button>
            <button type="button" class="btn btn-muted btn-sm" id="audit-batch-clear">取消选择</button>
        </div>
        <div id="audit-table"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var form = document.getElementById('audit-search');
    var batchBar = document.getElementById('audit-batch');
    var batchCount = document.getElementById('audit-batch-count');
    var countEl = document.getElementById('audit-count');

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
        var action = form.action.value;
        U.qa('#audit-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = false;
            if (key === '' && status === '' && action === '') on = true;
            else if (key === 'status' && action === '' && status === val) on = true;
            else if (key === 'action' && status === '' && action === val) on = true;
            chip.classList.toggle('active', on);
        });
    }
    function applyQueue(key, value) {
        form.status.value = '';
        form.action.value = '';
        form.scope.value = '';
        if (key === 'status') form.status.value = value || '';
        if (key === 'action') form.action.value = value || '';
        runSearch();
    }
    function runSearch() {
        table.reload(queryWhere());
        markChips();
    }
    function titleHtml(d) {
        var name = d.name || '未命名规则';
        var badges = [];
        if (parseInt(d.status, 10) !== 1) badges.push('<span class="badge badge-off">停用</span>');
        else badges.push('<span class="badge badge-ok">启用</span>');
        if (d.action === 'skip') badges.push('<span class="badge badge-warn">' + U.escape(d.action_label || '跳过') + '</span>');
        else if (d.action === 'review') badges.push('<span class="badge badge-search">' + U.escape(d.action_label || '下架') + '</span>');
        else badges.push('<span class="badge badge-ok">' + U.escape(d.action_label || '抠词') + '</span>');
        if (parseInt(d.is_regex, 10) === 1) badges.push('<span class="badge badge-off">正则</span>');
        var meta = [];
        if (d.scope_label) meta.push('看' + U.escape(d.scope_label));
        if (d.words_preview) meta.push(U.escape(d.words_preview));
        else meta.push('还没填词');
        if (parseInt(d.word_n, 10) > 0) meta.push(U.escape(String(d.word_n)) + ' 个词');
        return '<div class="entry-row-title-line"><a class="entry-row-title" href="/admin/video/audits/' + encodeURIComponent(d.id || '') + '/edit">' + U.escape(name) + '</a> ' + badges.join(' ') + '</div>'
            + '<div class="entry-row-meta">' + (meta.join(' · ') || '—') + '</div>';
    }

    var table = U.table({
        el: '#audit-table',
        countEl: countEl,
        url: '/admin/video/audits/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>没有符合条件的规则。</p><p><button type="button" class="btn btn-muted btn-sm" id="audit-empty-reset">清除筛选</button></p></div>';
            }
            return '<div class="list-empty"><p>还没有审核规则。</p><p class="muted">采集时可以按标题、简介、演员里的词决定跳过、下架或抠词。</p><p><a class="btn btn-primary btn-sm" href="/admin/video/audits/create">新增规则</a> <a class="btn btn-muted btn-sm" href="/admin/video/collects">去采集源</a></p></div>';
        },
        onDraw: function (wrap, list) {
            U.qa('tbody tr[data-idx]', wrap).forEach(function (tr) {
                var d = list[parseInt(tr.getAttribute('data-idx'), 10)];
                if (d && parseInt(d.status, 10) !== 1) tr.classList.add('is-off');
            });
            var reset = document.getElementById('audit-empty-reset');
            if (reset) reset.addEventListener('click', function () {
                form.reset();
                form.status.value = '';
                form.action.value = '';
                form.scope.value = '';
                runSearch();
            });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = '已选 ' + ids.length + ' 条';
        },
        cols: [
            {check: true, width: 36},
            {title: '规则', html: titleHtml},
            {title: '操作', cls: 'actions', html: function (d) {
                return '<a class="btn-link" href="/admin/video/audits/' + encodeURIComponent(d.id || '') + '/edit">编辑</a>'
                    + '<a href="#" class="btn-link js-del">删除</a>';
            }}
        ]
    });
    markChips();

    function selectedIds() { return table.selectedIds(); }
    function batch(action, value, confirmText) {
        var ids = selectedIds();
        if (!ids.length) { U.toast('请先勾选规则', 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/audits/batch', {ids: ids.join(','), action: action, value: value || ''}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '操作失败', 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || '操作成功', 'ok');
        });
    }

    U.on('#audit-search-btn', 'click', runSearch);
    U.on('#audit-reset-btn', 'click', function () {
        setTimeout(function () {
            form.status.value = '';
            form.action.value = '';
            form.scope.value = '';
            runSearch();
        }, 0);
    });
    document.getElementById('audit-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#audit-batch-on', 'click', function () { batch('status', 1); });
    U.on('#audit-batch-off', 'click', function () { batch('status', 0); });
    U.on('#audit-batch-del', 'click', function () { batch('delete', '', '删除选中规则？已经进库的片子不会变。'); });
    U.on('#audit-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#audit-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        var href = a.getAttribute('href') || '';
        if (href.indexOf('/admin/video/audits/') === 0 || href.indexOf('/admin/video/collects') === 0) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        e.preventDefault();
        if (!row) return;
        if (a.classList.contains('js-del')) {
            if (!U.confirm('删除这条规则？已经进库的片子不会变。')) return;
            U.post('/admin/video/audits/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh();
                U.toast('已删除', 'ok');
            });
        }
    });
})();
</script>
@endpush
