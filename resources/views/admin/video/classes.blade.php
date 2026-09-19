@extends('admin.layouts.inner')
@section('title', $title ?? admin_t('page.classes'))

@php
    $queues = $queues ?? ['all' => 0, 'on' => 0, 'off' => 0, 'unused' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
@endphp

@section('plain')
<div class="card card-panel extclass-index" id="extclass-index">
    <div class="card-header">
        <span>扩展分类 <em id="extclass-count"></em></span>
        <div>
            <button type="button" class="btn btn-sm" id="extclass-add-btn">新增类型词</button>
            <a class="btn btn-muted btn-sm" href="/admin/video/types">分类</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/tags">标签</a>
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">分类页的「类型」筛选词，如喜剧、动作。栏目树在「分类」。贺岁、高分去标签。</p>
        <form class="filter-bar" id="extclass-search" onsubmit="return false;">
            <input type="hidden" name="unused">
            <input type="search" name="q" placeholder="搜类型词" autocomplete="off" aria-label="搜索扩展分类">
            <select name="status">
                <option value="">状态</option>
                <option value="1">启用</option>
                <option value="0">停用</option>
            </select>
            <button type="button" class="btn btn-sm" id="extclass-search-btn">查询</button>
            <button type="reset" class="btn btn-muted btn-sm" id="extclass-reset-btn">重置</button>
        </form>
        <div class="queue-chips" id="extclass-queues">
            <button type="button" class="chip" data-queue="">全部@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="1">启用@if($q('on') > 0)<em>{{ $q('on') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="0">停用@if($q('off') > 0)<em>{{ $q('off') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="unused" data-value="1">没片子用@if($q('unused') > 0)<em>{{ $q('unused') }}</em>@endif</button>
        </div>
        <p class="muted field-hint">词库空着时，分类页会用片子上已有的类型词。这里一旦有启用词，筛选只显示这些。采集写入的是影片上的词，不会自动进这张表。</p>
        <div class="batch-bar" id="extclass-batch" hidden>
            <strong id="extclass-batch-count">已选 0 个</strong>
            <button type="button" class="btn btn-sm" id="extclass-batch-on">启用</button>
            <button type="button" class="btn btn-muted btn-sm" id="extclass-batch-off">停用</button>
            <button type="button" class="btn btn-danger btn-sm" id="extclass-batch-del">删除</button>
            <button type="button" class="btn btn-muted btn-sm" id="extclass-batch-clear">取消选择</button>
        </div>
        <div id="extclass-table"></div>
    </div>
</div>
<template id="extclass-dialog-tpl">
    <form>
        <input type="hidden" name="id">
        <label>类型词</label>
        <input type="text" name="name" placeholder="如 喜剧" required>
        <p class="muted field-hint">一次一个词。会出现在分类页「类型」。不要写成电影、电视剧，那是分类。</p>
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
    var QUEUE_KEYS = ['unused'];
    var form = document.getElementById('extclass-search');
    var batchBar = document.getElementById('extclass-batch');
    var batchCount = document.getElementById('extclass-batch-count');
    var countEl = document.getElementById('extclass-count');

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
        var unused = form.unused.value;
        U.qa('#extclass-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = false;
            if (key === '' && status === '' && unused === '') on = true;
            else if (key === 'status' && unused === '' && status === val) on = true;
            else if (key === 'unused' && status === '' && unused === val) on = true;
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
    function nameHtml(d) {
        var badge = d.is_on ? '' : '<span class="badge badge-off">停用</span>';
        var used = parseInt(d.used_count, 10) || 0;
        var meta = used > 0 ? (used + ' 部片子') : '还没片子用';
        return '<div><div class="entry-row-title-line"><a class="entry-row-title js-edit" href="#">' + U.escape(d.name || '') + '</a> ' + badge + '</div>'
            + '<div class="entry-row-meta">' + meta + '</div></div>';
    }

    var table = U.table({
        el: '#extclass-table',
        queueKeys: QUEUE_KEYS,
        countEl: countEl,
        url: '/admin/video/classes/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>没有符合条件的类型词</p><p><button type="button" class="btn btn-muted btn-sm" id="extclass-empty-reset">清除筛选</button></p></div>';
            }
            return '<div class="list-empty"><p>还没有扩展分类</p><p class="muted">点新增，填喜剧、动作这种筛选词。栏目树请去分类，不要在这里建电影、电视剧。</p><p><button type="button" class="btn btn-primary btn-sm" id="extclass-empty-add">新增类型词</button> <a class="btn btn-muted btn-sm" href="/admin/video/types">去分类</a></p></div>';
        },
        onDraw: function (_wrap, list) {
            var add = document.getElementById('extclass-empty-add');
            var reset = document.getElementById('extclass-empty-reset');
            if (add) add.addEventListener('click', function () { openDialog('add'); });
            if (reset) reset.addEventListener('click', function () { form.reset(); runSearch(); });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = '已选 ' + ids.length + ' 个';
        },
        cols: [
            {check: true, width: 36},
            {title: '类型词', html: nameHtml},
            {key: 'sort', title: '排序', width: 64},
            {title: '状态', width: 72, html: function (d) {
                return d.is_on ? U.status(true, '启用') : U.status(false, '停用');
            }},
            {title: '操作', cls: 'actions', html: function (d) {
                var used = parseInt(d.used_count, 10) || 0;
                var html = '';
                if (used > 0) html += '<a href="/admin/video" class="btn-link">影片</a>';
                html += '<a href="#" class="btn-link js-edit">编辑</a>';
                html += '<a href="#" class="btn-link js-del">删除</a>';
                return html;
            }}
        ]
    });
    markChips();

    function openDialog(mode, row) {
        row = row || {};
        U.dialog({
            title: mode === 'edit' ? '编辑类型词' : '新增类型词',
            content: document.getElementById('extclass-dialog-tpl').innerHTML,
            onOpen: function (body) {
                U.fillForm(body.querySelector('form'), {
                    id: mode === 'edit' ? (row.id || '') : '',
                    name: row.name || '',
                    sort: row.sort == null ? 0 : row.sort,
                    status: row.status == null ? '1' : String(row.status)
                });
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (!data.name) { U.toast('请填写类型词', 'err'); return false; }
                if (/[,，]/.test(data.name)) { U.toast('一次只写一个词，不要逗号', 'err'); return false; }
                if (mode !== 'edit') delete data.id; else data.id = row.id;
                return U.post('/admin/video/classes/save', data).then(function (res) {
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
        if (!ids.length) { U.toast('请先勾选类型词', 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/classes/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '操作失败', 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || '操作成功', 'ok');
        });
    }

    U.on('#extclass-search-btn', 'click', runSearch);
    U.on('#extclass-reset-btn', 'click', function () { setTimeout(runSearch, 0); });
    U.on('#extclass-add-btn', 'click', function () { openDialog('add'); });
    document.getElementById('extclass-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#extclass-batch-on', 'click', function () { batch('status', 1); });
    U.on('#extclass-batch-off', 'click', function () { batch('status', 0); });
    U.on('#extclass-batch-del', 'click', function () { batch('delete', '', '确认删除选中类型词？片子上的词还在，只是词库少了。'); });
    U.on('#extclass-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#extclass-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        if ((a.getAttribute('href') || '').indexOf('/admin/video') === 0 && a.getAttribute('href') !== '#') return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-edit')) openDialog('edit', row);
        if (a.classList.contains('js-del')) {
            if (!U.confirm('删除类型词「' + (row.name || '') + '」？片子上的词还在。')) return;
            U.post('/admin/video/classes/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh();
                U.toast('已删除', 'ok');
            });
        }
    });
})();
</script>
@endpush
