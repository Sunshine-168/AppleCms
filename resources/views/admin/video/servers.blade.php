@extends('admin.layouts.inner')
@section('title', $title ?? admin_t('page.servers'))

@php
    $queues = $queues ?? ['all' => 0, 'on' => 0, 'off' => 0, 'empty' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
    $onServers = $onServers ?? [];
@endphp

@section('plain')
<div class="card card-panel server-index" id="server-index">
    <div class="card-header">
        <span>服务器组 <em id="server-count"></em></span>
        <div>
            <button type="button" class="btn btn-sm" id="server-add-btn">新增服务器组</button>
            <a class="btn btn-muted btn-sm" href="/admin/video/players">播放器</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/downloaders">下载器</a>
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">线路选中一组后，相对路径会拼上此前缀。已是 http(s) 或 <code>//</code> 的不改。停用或前缀为空则原样。不是下载页模板，也不是播放内核。</p>
        <form class="filter-bar" id="server-search" onsubmit="return false;">
            <input type="hidden" name="empty_url">
            <input type="search" name="q" placeholder="搜名称或前缀" autocomplete="off" aria-label="搜索服务器组">
            <select name="status">
                <option value="">状态</option>
                <option value="1">启用</option>
                <option value="0">停用</option>
            </select>
            <button type="button" class="btn btn-sm" id="server-search-btn">查询</button>
            <button type="reset" class="btn btn-muted btn-sm" id="server-reset-btn">重置</button>
        </form>
        <div class="queue-chips" id="server-queues">
            <button type="button" class="chip" data-queue="">全部@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="1">启用@if($q('on') > 0)<em>{{ $q('on') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="0">停用@if($q('off') > 0)<em>{{ $q('off') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="empty_url" data-value="1">前缀空着@if($q('empty') > 0)<em>{{ $q('empty') }}</em>@endif</button>
        </div>
        <form class="filter-bar server-try-bar" id="server-try" onsubmit="return false;">
            <select name="server_id" aria-label="试服务器组">
                <option value="0">不选组</option>
                @foreach($onServers as $s)
                    <option value="{{ (int) ($s['id'] ?? 0) }}">{{ $s['name'] ?? '' }}</option>
                @endforeach
            </select>
            <input type="text" name="url" placeholder="填一条相对路径，如 ep1.m3u8" autocomplete="off" aria-label="试播放地址">
            <button type="button" class="btn btn-muted btn-sm" id="server-try-btn">试一下</button>
            <span class="muted" id="server-try-out"></span>
        </form>
        <p class="muted field-hint">试的是已经保存并且启用的组。完整地址不会拼；停用或前缀空着地址原样。</p>
        <div class="batch-bar" id="server-batch" hidden>
            <strong id="server-batch-count">已选 0 个</strong>
            <button type="button" class="btn btn-sm" id="server-batch-on">启用</button>
            <button type="button" class="btn btn-muted btn-sm" id="server-batch-off">停用</button>
            <button type="button" class="btn btn-danger btn-sm" id="server-batch-del">删除</button>
            <button type="button" class="btn btn-muted btn-sm" id="server-batch-clear">取消选择</button>
        </div>
        <div id="server-table"></div>
    </div>
</div>
<template id="server-dialog-tpl">
    <form>
        <input type="hidden" name="id">
        <label>名称</label>
        <input type="text" name="name" placeholder="如 线路A、主站 CDN" required>
        <p class="muted field-hint">会出现在线路的「服务器组」下拉里，不要重名。</p>
        <label>地址前缀</label>
        <input type="text" name="url" placeholder="https://play.example.com/hls">
        <p class="muted field-hint">可空。空着则相对路径也原样。不要写 javascript:。以 <code>/</code> 开头的当路径前缀，不会自动加 https。</p>
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
    var QUEUE_KEYS = ['empty_url'];
    var form = document.getElementById('server-search');
    var tryForm = document.getElementById('server-try');
    var tryOut = document.getElementById('server-try-out');
    var batchBar = document.getElementById('server-batch');
    var batchCount = document.getElementById('server-batch-count');
    var countEl = document.getElementById('server-count');

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
        var emptyUrl = form.empty_url.value;
        U.qa('#server-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = false;
            if (key === '' && status === '' && emptyUrl === '') on = true;
            else if (key === 'status' && emptyUrl === '' && status === val) on = true;
            else if (key === 'empty_url' && status === '' && emptyUrl === val) on = true;
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
    function fillTryGroups(list) {
        var sel = tryForm.server_id;
        if (!sel) return;
        var seen = {};
        Array.prototype.forEach.call(sel.options, function (opt) {
            if (opt.value && opt.value !== '0') seen[opt.value] = opt;
        });
        (list || []).forEach(function (row) {
            var id = String(row.id);
            if (row.is_on) {
                if (!seen[id]) {
                    var opt = document.createElement('option');
                    opt.value = id;
                    opt.textContent = row.name || ('#' + id);
                    sel.appendChild(opt);
                    seen[id] = opt;
                } else {
                    seen[id].textContent = row.name || ('#' + id);
                }
            } else if (seen[id]) {
                seen[id].remove();
                delete seen[id];
            }
        });
    }
    function nameHtml(d) {
        var badge = d.is_on ? '' : '<span class="badge badge-off">停用</span>';
        var used = parseInt(d.source_count, 10) || 0;
        var meta = [d.has_url ? '有前缀' : '前缀空着', used > 0 ? (used + ' 条线路') : '还没线路用'];
        if (d.url_preview) meta.push(U.escape(d.url_preview));
        return '<div><div class="entry-row-title-line"><a class="entry-row-title js-edit" href="#">' + U.escape(d.name || '') + '</a> ' + badge + '</div>'
            + '<div class="entry-row-meta">' + meta.join(' · ') + '</div></div>';
    }

    var table = U.table({
        el: '#server-table',
        url: '/admin/video/servers/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>没有符合条件的服务器组</p><p><button type="button" class="btn btn-muted btn-sm" id="server-empty-reset">清除筛选</button></p></div>';
            }
            return '<div class="list-empty"><p>还没有服务器组</p><p class="muted">点新增，填名称。前缀可空。下载页模板请去下载器，播内核请去播放器。</p><p><button type="button" class="btn btn-primary btn-sm" id="server-empty-add">新增服务器组</button> <a class="btn btn-muted btn-sm" href="/admin/video/downloaders">去下载器</a></p></div>';
        },
        onDraw: function (_wrap, list) {
            countEl.textContent = list.length ? '· ' + list.length : '';
            fillTryGroups(list);
            var add = document.getElementById('server-empty-add');
            var reset = document.getElementById('server-empty-reset');
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
            {title: '前缀', html: function (d) { return d.has_url ? U.escape(d.url_preview || '') : '<span class="muted">空着</span>'; }},
            {title: '线路', width: 80, html: function (d) {
                var n = parseInt(d.source_count, 10) || 0;
                return n > 0 ? (n + ' 条') : '—';
            }},
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
            title: mode === 'edit' ? '编辑服务器组' : '新增服务器组',
            content: document.getElementById('server-dialog-tpl').innerHTML,
            onOpen: function (body) {
                U.fillForm(body.querySelector('form'), {
                    id: mode === 'edit' ? (row.id || '') : '',
                    name: row.name || '',
                    url: row.url || '',
                    sort: row.sort == null ? 0 : row.sort,
                    status: row.status == null ? '1' : String(row.status)
                });
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (!data.name) { U.toast('请填写名称', 'err'); return false; }
                if (mode !== 'edit') delete data.id; else data.id = row.id;
                return U.post('/admin/video/servers/save', data).then(function (res) {
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
        if (!ids.length) { U.toast('请先勾选服务器组', 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/servers/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '操作失败', 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || '操作成功', 'ok');
        });
    }

    U.on('#server-search-btn', 'click', runSearch);
    U.on('#server-reset-btn', 'click', function () { setTimeout(runSearch, 0); });
    U.on('#server-add-btn', 'click', function () { openDialog('add'); });
    U.on('#server-try-btn', 'click', function () {
        var data = U.formData(tryForm);
        if (!data.url) { U.toast('请填一条相对路径试试', 'err'); return; }
        tryOut.textContent = '…';
        U.post('/admin/video/servers/try', data).then(function (res) {
            tryOut.textContent = (res && res.msg) || '失败';
            if (!res || res.code !== 0) U.toast((res && res.msg) || '失败', 'err');
        });
    });
    document.getElementById('server-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#server-batch-on', 'click', function () { batch('status', 1); });
    U.on('#server-batch-off', 'click', function () { batch('status', 0); });
    U.on('#server-batch-del', 'click', function () { batch('delete', '', '确认删除选中服务器组？有线路在用的删不掉。'); });
    U.on('#server-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#server-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-edit')) openDialog('edit', row);
        if (a.classList.contains('js-del')) {
            if (!U.confirm('删除服务器组「' + (row.name || '') + '」？')) return;
            U.post('/admin/video/servers/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh();
                U.toast('已删除', 'ok');
            });
        }
    });
})();
</script>
@endpush
