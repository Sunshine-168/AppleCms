@extends('admin.layouts.inner')
@section('title', $title)

@php
    $queues = $queues ?? ['all' => 0, 'on' => 0, 'off' => 0, 'logo' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
@endphp

@section('plain')
<div class="card card-panel link-index">
    <div class="card-header">
        <span>友情链接 <em id="link-count"></em></span>
        <div>
            <button type="button" class="btn btn-sm" id="link-add-btn">新增友链</button>
            <a class="btn btn-muted btn-sm" href="/admin/video/websites">网址导航</a>
        </div>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="link-search" onsubmit="return false;">
            <input type="hidden" name="logo">
            <input type="text" name="name" placeholder="搜名称或网址" autocomplete="off">
            <select name="status">
                <option value="">状态</option>
                <option value="1">显示</option>
                <option value="0">隐藏</option>
            </select>
            <button type="button" class="btn btn-sm" id="link-search-btn">查询</button>
            <button type="reset" class="btn btn-muted btn-sm" id="link-reset-btn">重置</button>
        </form>
        <div class="queue-chips" id="link-queues">
            <button type="button" class="chip" data-queue="">全部@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="1">显示中@if($q('on') > 0)<em>{{ $q('on') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="0">已隐藏@if($q('off') > 0)<em>{{ $q('off') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="logo" data-value="1">有图@if($q('logo') > 0)<em>{{ $q('logo') }}</em>@endif</button>
        </div>
        <p class="muted recycle-lead">友链出现在页脚。默认主题显示文字，写法 <code>@@vodLink</code>。隐藏后前台不再输出。顶栏导航请去网址导航。</p>
        <div class="batch-bar" id="link-batch" hidden>
            <strong id="link-batch-count">已选 0 条</strong>
            <button type="button" class="btn btn-sm" id="link-batch-on">显示</button>
            <button type="button" class="btn btn-muted btn-sm" id="link-batch-off">隐藏</button>
            <button type="button" class="btn btn-danger btn-sm" id="link-batch-del">删除</button>
            <button type="button" class="btn btn-muted btn-sm" id="link-batch-clear">取消选择</button>
        </div>
        <div id="link-table"></div>
    </div>
</div>
<template id="link-dialog-tpl">
    <form>
        <input type="hidden" name="id">
        <label>网站名称</label>
        <input type="text" name="name" placeholder="如 某某影视">
        <p class="muted field-hint">页脚上显示的文字。</p>
        <label>网址</label>
        <input type="text" name="url" placeholder="https://">
        <p class="muted field-hint">没写协议会自动加上 https://。点开会在新窗口。</p>
        <label>Logo</label>
        <div class="field-inline">
            <input type="text" name="logo" placeholder="可空，图链才需要">
            <button type="button" class="btn btn-muted link-logo-upload-btn">上传</button>
        </div>
        <img class="img-preview link-logo-preview" alt="">
        <p class="muted field-hint">默认主题不用 Logo。清空则按文字链接显示。</p>
        <label>排序</label>
        <input type="number" name="sort" value="0">
        <label>状态</label>
        <select name="status">
            <option value="1">显示</option>
            <option value="0">隐藏</option>
        </select>
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var QUEUE_KEYS = ['logo'];
    var form = document.getElementById('link-search');
    var batchBar = document.getElementById('link-batch');
    var batchCount = document.getElementById('link-batch-count');
    var countEl = document.getElementById('link-count');

    function cleanWhere(data) {
        var out = {};
        Object.keys(data).forEach(function (k) { if (data[k] !== '') out[k] = data[k]; });
        return out;
    }
    function queryWhere() {
        return Object.assign({limit: 30}, cleanWhere(U.formData(form)));
    }
    function isFiltered(where) {
        return Object.keys(where || {}).some(function (k) { return k !== 'limit' && where[k] !== ''; });
    }
    function markChips() {
        var status = form.status.value;
        var logo = form.logo.value;
        U.qa('#link-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = false;
            if (key === '' && status === '' && logo === '') on = true;
            else if (key === 'status' && logo === '' && status === val) on = true;
            else if (key === 'logo' && status === '' && logo === val) on = true;
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
        var logo = String(d.logo || '').trim();
        var letter = String(d.name || '?').slice(0, 1);
        var thumb = logo
            ? '<img class="link-thumb" src="' + U.escape(logo) + '" alt="">'
            : '<span class="link-thumb is-empty">' + U.escape(letter) + '</span>';
        var badge = String(d.status) === '1' ? '' : '<span class="badge badge-off">隐藏</span>';
        var kind = U.escape(d.kind_label || '文字');
        var url = String(d.url || '');
        return '<div class="vod-cell">' + thumb + '<div><div class="entry-row-title-line"><a class="entry-row-title js-edit" href="#">' + U.escape(d.name || '未命名') + '</a> ' + badge + '</div>'
            + '<div class="entry-row-meta">' + kind + (url ? ' · ' + U.escape(url) : '') + '</div></div></div>';
    }

    var table = U.table({
        el: '#link-table',
        url: '/admin/video/links/list',
        where: queryWhere(),
        pager: false,
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>没有符合条件的友链</p><p><button type="button" class="btn btn-muted btn-sm" id="link-empty-reset">清除筛选</button></p></div>';
            }
            return '<div class="list-empty"><p>还没有友情链接</p><p class="muted">友链出现在页脚。填名称和网址就能显示，Logo 可选。</p><p><button type="button" class="btn btn-primary btn-sm" id="link-empty-add">新增友链</button></p></div>';
        },
        onDraw: function (_wrap, list) {
            countEl.textContent = list.length ? '· ' + list.length : '';
            var add = document.getElementById('link-empty-add');
            var reset = document.getElementById('link-empty-reset');
            if (add) add.addEventListener('click', function () { openDialog('add'); });
            if (reset) reset.addEventListener('click', function () { form.reset(); runSearch(); });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = '已选 ' + ids.length + ' 条';
        },
        cols: [
            {check: true, width: 36},
            {title: '友链', html: nameHtml},
            {key: 'sort', title: '排序', width: 64},
            {title: '状态', width: 72, html: function (d) {
                return String(d.status) === '1' ? U.status(true, '显示') : U.status(false, '隐藏');
            }},
            {title: '操作', cls: 'actions', html: function (d) {
                var html = '';
                if (d.url) html += '<a href="' + U.escape(d.url) + '" target="_blank" rel="noopener noreferrer" class="btn-link">打开</a>';
                html += '<a href="#" class="btn-link js-edit">编辑</a>';
                html += '<a href="#" class="btn-link js-del">删除</a>';
                return html;
            }}
        ]
    });
    markChips();

    function bindLogo(formEl) {
        U.bindImageField(formEl, {
            input: 'input[name=logo]',
            btn: '.link-logo-upload-btn',
            preview: '.link-logo-preview'
        });
    }

    function openDialog(mode, row) {
        row = row || {};
        U.dialog({
            title: mode === 'edit' ? '编辑友链' : '新增友链',
            content: document.getElementById('link-dialog-tpl').innerHTML,
            onOpen: function (body) {
                var formEl = body.querySelector('form');
                U.fillForm(formEl, {
                    id: mode === 'edit' ? (row.id || '') : '',
                    name: row.name || '',
                    url: row.url || '',
                    logo: row.logo || '',
                    sort: row.sort == null ? 0 : row.sort,
                    status: row.status == null ? '1' : String(row.status)
                });
                bindLogo(formEl);
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (!data.name) { U.toast('请填写网站名称', 'err'); return false; }
                if (!data.url) { U.toast('请填写网址', 'err'); return false; }
                if (mode !== 'edit') delete data.id; else data.id = row.id;
                return U.post('/admin/video/links/save', data).then(function (res) {
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
        if (!ids.length) { U.toast('请先勾选友链', 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/links/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '操作失败', 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || '操作成功', 'ok');
        });
    }

    U.on('#link-search-btn', 'click', runSearch);
    U.on('#link-reset-btn', 'click', function () { setTimeout(runSearch, 0); });
    U.on('#link-add-btn', 'click', function () { openDialog('add'); });
    document.getElementById('link-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#link-batch-on', 'click', function () { batch('status', 1); });
    U.on('#link-batch-off', 'click', function () { batch('status', 0); });
    U.on('#link-batch-del', 'click', function () { batch('delete', '', '确认删除选中友链？页脚将不再显示。'); });
    U.on('#link-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#link-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        if (a.target === '_blank') return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-edit')) openDialog('edit', row);
        if (a.classList.contains('js-del')) {
            if (!U.confirm('删除友链「' + (row.name || '') + '」？')) return;
            U.post('/admin/video/links/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh();
                U.toast('已删除', 'ok');
            });
        }
    });
})();
</script>
@endpush
