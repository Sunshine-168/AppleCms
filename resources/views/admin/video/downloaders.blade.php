@extends('admin.layouts.inner')
@section('title', $title ?? admin_t('page.downloaders'))

@php
    $queues = $queues ?? ['all' => 0, 'on' => 0, 'off' => 0, 'tpl' => 0, 'prefix' => 0, 'empty' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
@endphp

@section('plain')
<div class="card card-panel downer-index" id="downer-index">
    <div class="card-header">
        <span>下载器 <em id="downer-count"></em></span>
        <div>
            <button type="button" class="btn btn-sm" id="downer-add-btn">新增下载器</button>
            <a class="btn btn-muted btn-sm" href="/admin/video/players">播放器</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/servers">服务器组</a>
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">下载页把剧集地址套进模板。<code>{url}</code> 是地址，<code>{id}</code> 是影片 ID。不是后台去把文件下下来。加域名请去服务器组。</p>
        <form class="filter-bar" id="downer-search" onsubmit="return false;">
            <input type="hidden" name="kind">
            <input type="search" name="q" placeholder="搜名称、标识或模板" autocomplete="off" aria-label="搜索下载器">
            <select name="status">
                <option value="">状态</option>
                <option value="1">启用</option>
                <option value="0">停用</option>
            </select>
            <button type="button" class="btn btn-sm" id="downer-search-btn">查询</button>
            <button type="reset" class="btn btn-muted btn-sm" id="downer-reset-btn">重置</button>
        </form>
        <div class="queue-chips" id="downer-queues">
            <button type="button" class="chip" data-queue="">全部@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="1">启用@if($q('on') > 0)<em>{{ $q('on') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="0">停用@if($q('off') > 0)<em>{{ $q('off') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="kind" data-value="tpl">模板@if($q('tpl') > 0)<em>{{ $q('tpl') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="kind" data-value="prefix">前缀@if($q('prefix') > 0)<em>{{ $q('prefix') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="kind" data-value="empty">模板空着@if($q('empty') > 0)<em>{{ $q('empty') }}</em>@endif</button>
        </div>
        <form class="filter-bar downer-try-bar" id="downer-try" onsubmit="return false;">
            <input type="text" name="code" placeholder="标识，可空" autocomplete="off" aria-label="试下载器标识">
            <input type="text" name="url" placeholder="填一条剧集地址，看会变成什么" autocomplete="off" aria-label="试下载地址">
            <input type="number" name="video_id" placeholder="影片 ID" min="0" aria-label="影片 ID">
            <button type="button" class="btn btn-muted btn-sm" id="downer-try-btn">试一下</button>
            <span class="muted" id="downer-try-out"></span>
        </form>
        <p class="muted field-hint">试的是已经保存并且启用的模板。先匹配线路标识，再看地址是不是以标识开头。停用的不算。</p>
        <div class="batch-bar" id="downer-batch" hidden>
            <strong id="downer-batch-count">已选 0 个</strong>
            <button type="button" class="btn btn-sm" id="downer-batch-on">启用</button>
            <button type="button" class="btn btn-muted btn-sm" id="downer-batch-off">停用</button>
            <button type="button" class="btn btn-danger btn-sm" id="downer-batch-del">删除</button>
            <button type="button" class="btn btn-muted btn-sm" id="downer-batch-clear">取消选择</button>
        </div>
        <div id="downer-table"></div>
    </div>
</div>
<template id="downer-dialog-tpl">
    <form>
        <input type="hidden" name="id">
        <label>名称</label>
        <input type="text" name="name" placeholder="如 迅雷、直链" required>
        <label>标识</label>
        <input type="text" name="code" placeholder="http" required>
        <p class="muted field-hint">要和线路上的「下载器」字段一致。采集常见 http、xunlei。</p>
        <label>模板</label>
        <textarea name="parse" placeholder="https://dl.example/get?u={url}"></textarea>
        <p class="muted field-hint">写 <code>{url}</code> / <code>{id}</code> 会替换；只写前缀会拼在地址前面；空着则原样输出。不会去拉文件。</p>
        <label>排序</label>
        <input type="number" name="sort" value="0">
        <label>状态</label>
        <select name="status">
            <option value="1">启用</option>
            <option value="0">停用</option>
        </select>
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var QUEUE_KEYS = ['kind'];
    var form = document.getElementById('downer-search');
    var tryForm = document.getElementById('downer-try');
    var tryOut = document.getElementById('downer-try-out');
    var batchBar = document.getElementById('downer-batch');
    var batchCount = document.getElementById('downer-batch-count');
    var countEl = document.getElementById('downer-count');

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
        var status = form.status.value;
        var kind = form.kind.value;
        U.qa('#downer-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = false;
            if (key === '' && status === '' && kind === '') on = true;
            else if (key === 'status' && kind === '' && status === val) on = true;
            else if (key === 'kind' && status === '' && kind === val) on = true;
            chip.classList.toggle('active', on);
        });
    }
    function applyQueue(key, value) {
        QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
        form.status.value = '';
        if (key === 'status') form.status.value = value || '';
        else if (key && form[key]) form[key].value = value || '';
        runSearch();
    }
    function runSearch() {
        table.reload(queryWhere());
        markChips();
    }
    function nameHtml(d) {
        var badge = d.is_on ? '' : '<span class="badge badge-off">停用</span>';
        var kind = U.escape(d.parse_kind_label || '');
        var used = parseInt(d.source_count, 10) || 0;
        var meta = [kind, used > 0 ? (used + ' 条线路') : '还没线路用'];
        if (d.parse_preview) meta.push(U.escape(d.parse_preview));
        return '<div><div class="entry-row-title-line"><a class="entry-row-title js-edit" href="#">' + U.escape(d.name || '') + '</a> ' + badge + '</div>'
            + '<div class="entry-row-meta">' + meta.join(' · ') + '</div></div>';
    }

    var table = U.table({
        el: '#downer-table',
        queueKeys: QUEUE_KEYS,
        countEl: countEl,
        url: '/admin/video/downloaders/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>没有符合条件的下载器</p><p><button type="button" class="btn btn-muted btn-sm" id="downer-empty-reset">清除筛选</button></p></div>';
            }
            return '<div class="list-empty"><p>还没有下载器</p><p class="muted">点新增，填标识和模板。直链可以模板留空。播放内核请去播放器。</p><p><button type="button" class="btn btn-primary btn-sm" id="downer-empty-add">新增下载器</button> <a class="btn btn-muted btn-sm" href="/admin/video/players">去播放器</a></p></div>';
        },
        onDraw: function (_wrap, list) {
            var add = document.getElementById('downer-empty-add');
            var reset = document.getElementById('downer-empty-reset');
            if (add) add.addEventListener('click', function () { openDialog('add'); });
            if (reset) reset.addEventListener('click', function () { form.reset(); runSearch(); });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = '已选 ' + ids.length + ' 个';
        },
        cols: [
            {check: true, width: 36},
            {title: '名称', html: nameHtml},
            {title: '标识', width: 100, html: function (d) { return U.escape(d.code || ''); }},
            {title: '用法', width: 72, html: function (d) { return U.escape(d.parse_kind_label || ''); }},
            {title: '状态', width: 72, html: function (d) {
                return d.is_on ? U.status(true, '启用') : U.status(false, '停用');
            }},
            {title: '操作', cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-edit">编辑</a><a href="#" class="btn-link js-del">删除</a>';
            }}
        ]
    });
    markChips();

    function openDialog(mode, row) {
        row = row || {};
        U.dialog({
            title: mode === 'edit' ? '编辑下载器' : '新增下载器',
            content: document.getElementById('downer-dialog-tpl').innerHTML,
            onOpen: function (body) {
                U.fillForm(body.querySelector('form'), {
                    id: mode === 'edit' ? (row.id || '') : '',
                    name: row.name || '',
                    code: row.code || '',
                    parse: row.parse || '',
                    sort: row.sort == null ? 0 : row.sort,
                    status: row.status == null ? '1' : String(row.status)
                });
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (!data.name) { U.toast('请填写名称', 'err'); return false; }
                if (!data.code) { U.toast('请填写标识', 'err'); return false; }
                if (mode !== 'edit') delete data.id; else data.id = row.id;
                return U.post('/admin/video/downloaders/save', data).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return false; }
                    U.toast(mode === 'edit' ? '已保存' : '已创建', 'ok');
                    table.refresh();
                });
            }
        });
    }

    function selectedIds() { return table.selectedIds(); }
    function batch(action, value, confirmText) {
        var ids = selectedIds();
        if (!ids.length) { U.toast('请先勾选下载器', 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/downloaders/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '操作失败', 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || '操作成功', 'ok');
        });
    }

    U.on('#downer-search-btn', 'click', runSearch);
    U.on('#downer-reset-btn', 'click', function () { setTimeout(runSearch, 0); });
    U.on('#downer-add-btn', 'click', function () { openDialog('add'); });
    U.on('#downer-try-btn', 'click', function () {
        var data = U.formData(tryForm);
        if (!data.url) { U.toast('请填一条下载地址试试', 'err'); return; }
        tryOut.textContent = '…';
        U.post('/admin/video/downloaders/try', data).then(function (res) {
            tryOut.textContent = (res && res.msg) || '失败';
            if (!res || res.code !== 0) U.toast((res && res.msg) || '失败', 'err');
        });
    });
    document.getElementById('downer-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#downer-batch-on', 'click', function () { batch('status', 1); });
    U.on('#downer-batch-off', 'click', function () { batch('status', 0); });
    U.on('#downer-batch-del', 'click', function () { batch('delete', '', '确认删除选中下载器？有线路在用的删不掉。'); });
    U.on('#downer-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#downer-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-edit')) openDialog('edit', row);
        if (a.classList.contains('js-del')) {
            if (!U.confirm('删除下载器「' + (row.name || row.code || '') + '」？')) return;
            U.post('/admin/video/downloaders/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh();
                U.toast('已删除', 'ok');
            });
        }
    });
})();
</script>
@endpush
