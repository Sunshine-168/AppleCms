@extends('admin.layouts.inner')
@section('title', $title)

@php
    $queues = $queues ?? ['all' => 0, 'home' => 0, 'play' => 0, 'hidden' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
@endphp

@section('plain')
<div class="card card-panel slide-index">
    <div class="card-header">
        <span>幻灯片 <em id="slide-count"></em></span>
        <button type="button" class="btn btn-sm" id="slide-add-btn">新增幻灯</button>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="slide-search" onsubmit="return false;">
            <input type="hidden" name="slot">
            <input type="text" name="name" placeholder="搜名称" autocomplete="off">
            <select name="status">
                <option value="">状态</option>
                <option value="1">显示</option>
                <option value="0">隐藏</option>
            </select>
            <button type="button" class="btn btn-sm" id="slide-search-btn">查询</button>
            <button type="reset" class="btn btn-muted btn-sm" id="slide-reset-btn">重置</button>
        </form>
        <div class="queue-chips" id="slide-queues">
            <button type="button" class="chip" data-queue="">全部@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="slot" data-value="home">首页@if($q('home') > 0)<em>{{ $q('home') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="slot" data-value="play">播放页@if($q('play') > 0)<em>{{ $q('play') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="0">已隐藏@if($q('hidden') > 0)<em>{{ $q('hidden') }}</em>@endif</button>
        </div>
        <p class="muted recycle-lead">首页轮播、播放页贴片。先选位置，再上传横图和跳转链接。主题用位置调用，例如 <code>@@vodSlide(['slot' => 'home'])</code>。</p>
        <div class="batch-bar" id="slide-batch" hidden>
            <strong id="slide-batch-count">已选 0 张</strong>
            <button type="button" class="btn btn-sm" id="slide-batch-on">显示</button>
            <button type="button" class="btn btn-muted btn-sm" id="slide-batch-off">隐藏</button>
            <select id="slide-batch-slot" class="batch-select" aria-label="目标位置">
                <option value="">改到位置</option>
                <option value="home">首页</option>
                <option value="play">播放页</option>
            </select>
            <button type="button" class="btn btn-muted btn-sm" id="slide-batch-move">移动</button>
            <button type="button" class="btn btn-danger btn-sm" id="slide-batch-del">删除</button>
            <button type="button" class="btn btn-muted btn-sm" id="slide-batch-clear">取消选择</button>
        </div>
        <div id="slide-table"></div>
    </div>
</div>
<template id="slide-dialog-tpl">
    <form>
        <input type="hidden" name="id">
        <label>名称</label>
        <input type="text" name="name" placeholder="如 首页大图">
        <p class="muted field-hint">后台列表里看到的名字。</p>
        <label>图片</label>
        <div class="field-inline">
            <input type="text" name="pic" placeholder="图片地址">
            <button type="button" class="btn btn-muted slide-pic-upload-btn">上传</button>
        </div>
        <img class="img-preview slide-pic-preview" alt="">
        <label>链接</label>
        <input type="text" name="url" placeholder="点击后打开，可空">
        <label>位置</label>
        <select name="slot">
            <option value="home">首页</option>
            <option value="play">播放页</option>
        </select>
        <p class="muted field-hint">决定出现在哪块前台。首页轮播选首页，播放器上下贴片选播放页。</p>
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
    var QUEUE_KEYS = ['slot'];
    var form = document.getElementById('slide-search');
    var batchBar = document.getElementById('slide-batch');
    var batchCount = document.getElementById('slide-batch-count');
    var countEl = document.getElementById('slide-count');

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
        var slot = form.slot.value;
        U.qa('#slide-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = false;
            if (key === '' && status === '' && slot === '') on = true;
            else if (key === 'slot' && status === '' && slot === val) on = true;
            else if (key === 'status' && slot === '' && status === val) on = true;
            chip.classList.toggle('active', on);
        });
    }
    function applyQueue(key, value) {
        QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
        if (key === 'status') form.status.value = value || '';
        else {
            form.status.value = '';
            if (key && form[key]) form[key].value = value || '';
        }
        runSearch();
    }
    function runSearch() {
        table.reload(queryWhere());
        markChips();
    }
    function nameHtml(d) {
        var pic = String(d.pic || '').trim();
        var thumb = pic
            ? '<img class="slide-thumb" src="' + U.escape(pic) + '" alt="">'
            : '<span class="slide-thumb is-empty">无图</span>';
        var badge = String(d.status) === '1' ? '' : '<span class="badge badge-off">隐藏</span>';
        var meta = U.escape(d.slot_label || d.slot || '');
        if (d.url) meta += ' · ' + U.escape(d.url);
        return '<div class="vod-cell">' + thumb + '<div><div class="entry-row-title-line"><a class="entry-row-title js-edit" href="#">' + U.escape(d.name || '未命名') + '</a> ' + badge + '</div>'
            + '<div class="entry-row-meta">' + meta + '</div></div></div>';
    }

    var table = U.table({
        el: '#slide-table',
        queueKeys: QUEUE_KEYS,
        countEl: countEl,
        url: '/admin/video/slides/list',
        where: queryWhere(),
        pager: false,
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>没有符合条件的幻灯片</p><p><button type="button" class="btn btn-muted btn-sm" id="slide-empty-reset">清除筛选</button></p></div>';
            }
            return '<div class="list-empty"><p>还没有幻灯片</p><p class="muted">幻灯是首页轮播、播放页贴片。建好后主题用位置调用。</p><p><button type="button" class="btn btn-primary btn-sm" id="slide-empty-add">新增幻灯</button></p></div>';
        },
        onDraw: function (_wrap, list) {
            var add = document.getElementById('slide-empty-add');
            var reset = document.getElementById('slide-empty-reset');
            if (add) add.addEventListener('click', function () { openDialog('add'); });
            if (reset) reset.addEventListener('click', function () { form.reset(); runSearch(); });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = '已选 ' + ids.length + ' 张';
        },
        cols: [
            {check: true, width: 36},
            {title: '幻灯', html: nameHtml},
            {key: 'sort', title: '排序', width: 64},
            {title: '状态', width: 72, html: function (d) {
                return String(d.status) === '1' ? U.status(true, '显示') : U.status(false, '隐藏');
            }},
            {title: '操作', cls: 'actions', html: function (d) {
                var html = '';
                if (d.url) html += '<a href="' + U.escape(d.url) + '" target="_blank" rel="noopener" class="btn-link">链接</a>';
                html += '<a href="#" class="btn-link js-edit">编辑</a>';
                html += '<a href="#" class="btn-link js-del">删除</a>';
                return html;
            }}
        ]
    });
    markChips();

    function bindPic(formEl) {
        U.bindImageField(formEl, {
            input: 'input[name=pic]',
            btn: '.slide-pic-upload-btn',
            preview: '.slide-pic-preview'
        });
    }

    function openDialog(mode, row) {
        row = row || {};
        U.dialog({
            title: mode === 'edit' ? '编辑幻灯' : '新增幻灯',
            content: document.getElementById('slide-dialog-tpl').innerHTML,
            onOpen: function (body) {
                var formEl = body.querySelector('form');
                U.fillForm(formEl, {
                    id: mode === 'edit' ? (row.id || '') : '',
                    name: row.name || '',
                    pic: row.pic || '',
                    url: row.url || '',
                    slot: row.slot || 'home',
                    sort: row.sort == null ? 0 : row.sort,
                    status: row.status == null ? '1' : String(row.status)
                });
                bindPic(formEl);
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (!data.name) { U.toast('请填写名称', 'err'); return false; }
                if (!data.pic) { U.toast('请上传图片', 'err'); return false; }
                if (mode !== 'edit') delete data.id; else data.id = row.id;
                return U.post('/admin/video/slides/save', data).then(function (res) {
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
        if (!ids.length) { U.toast('请先勾选幻灯片', 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/slides/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '操作失败', 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || '操作成功', 'ok');
        });
    }

    U.on('#slide-search-btn', 'click', runSearch);
    U.on('#slide-reset-btn', 'click', function () { setTimeout(runSearch, 0); });
    U.on('#slide-add-btn', 'click', function () { openDialog('add'); });
    document.getElementById('slide-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#slide-batch-on', 'click', function () { batch('status', 1); });
    U.on('#slide-batch-off', 'click', function () { batch('status', 0); });
    U.on('#slide-batch-move', 'click', function () {
        var val = document.getElementById('slide-batch-slot').value;
        if (!val) { U.toast('请先选择位置，再点「移动」', 'err'); return; }
        batch('slot', val);
    });
    U.on('#slide-batch-del', 'click', function () { batch('delete', '', '确认删除选中幻灯？主题将取不到这些图。'); });
    U.on('#slide-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#slide-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        if (a.target === '_blank') return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-edit')) openDialog('edit', row);
        if (a.classList.contains('js-del')) {
            if (!U.confirm('删除幻灯「' + (row.name || '') + '」？')) return;
            U.post('/admin/video/slides/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh();
                U.toast('已删除', 'ok');
            });
        }
    });
})();
</script>
@endpush
