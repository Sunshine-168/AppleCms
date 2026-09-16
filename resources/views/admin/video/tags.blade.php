@extends('admin.layouts.inner')
@section('title', admin_t('page.tags'))

@section('plain')
<div class="card card-panel tag-index">
    <div class="card-header">
        <span>标签 <em id="tag-count"></em></span>
        <button type="button" class="btn btn-sm" id="video-tag-add-btn">新增标签</button>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="video-tag-search" onsubmit="return false;">
            <input type="hidden" name="unused">
            <input type="text" name="name" placeholder="搜标签名" autocomplete="off">
            <select name="status">
                <option value="">状态</option>
                <option value="1">启用</option>
                <option value="0">禁用</option>
            </select>
            <button type="button" class="btn btn-sm" id="video-tag-search-btn">查询</button>
            <button type="reset" class="btn btn-muted btn-sm" id="video-tag-reset-btn">重置</button>
        </form>
        <div class="queue-chips" id="tag-queues">
            <button type="button" class="chip" data-queue="">全部</button>
            <button type="button" class="chip" data-queue="status" data-value="1">启用</button>
            <button type="button" class="chip" data-queue="status" data-value="0">禁用</button>
            <button type="button" class="chip" data-queue="unused" data-value="1">未使用</button>
        </div>
        <p class="muted recycle-lead">标签是聚合词，比如贺岁、高分，不是分类。影片保存时填的标签会自动建档；没用过的可以清掉。</p>
        <div class="batch-bar" id="tag-batch" hidden>
            <strong id="tag-batch-count">已选 0 个</strong>
            <button type="button" class="btn btn-sm" id="tag-batch-on">启用</button>
            <button type="button" class="btn btn-muted btn-sm" id="tag-batch-off">禁用</button>
            <button type="button" class="btn btn-danger btn-sm" id="tag-batch-del">删除</button>
            <button type="button" class="btn btn-muted btn-sm" id="tag-batch-clear">取消选择</button>
        </div>
        <div id="video-tag-table"></div>
    </div>
</div>
<template id="video-tag-dialog-tpl">
    <form>
        <input type="hidden" name="id">
        <label>名称</label>
        <input type="text" name="name" placeholder="如 高分">
        <label>别名</label>
        <input type="text" name="slug" placeholder="前台网址用，可空">
        <label>排序</label>
        <input type="number" name="sort" value="0">
        <label>状态</label>
        <select name="status">
            <option value="1">启用</option>
            <option value="0">禁用</option>
        </select>
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var QUEUE_KEYS = ['unused'];
    var form = document.getElementById('video-tag-search');
    var batchBar = document.getElementById('tag-batch');
    var batchCount = document.getElementById('tag-batch-count');
    var countEl = document.getElementById('tag-count');

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
        U.qa('#tag-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = false;
            if (key === '' && status === '' && unused === '') on = true;
            else if (key === 'status' && unused === '' && status === val) on = true;
            else if (key === 'unused' && unused === val) on = true;
            chip.classList.toggle('active', on);
        });
    }
    function applyQueue(key, value) {
        QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
        if (key === 'status') form.status.value = value || '';
        else {
            form.status.value = '';
            if (key && form[key]) form[key].value = value || '1';
        }
        runSearch();
    }
    function runSearch() {
        table.reload(queryWhere());
        markChips();
    }
    function nameHtml(d) {
        var meta = '#' + U.escape(d.id);
        if (d.slug) meta += ' · /' + U.escape(d.slug);
        var n = parseInt(d.video_count, 10) || 0;
        meta += n > 0 ? ' · ' + n + ' 部' : ' · 还没挂片';
        return '<div><a class="vod-title js-edit" href="#">' + U.escape(d.name || '') + '</a>'
            + '<div class="muted">' + meta + '</div></div>';
    }

    var table = U.table({
        el: '#video-tag-table',
        url: '/admin/video/tags/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>没有符合条件的标签</p><p><button type="button" class="btn btn-muted btn-sm" id="tag-empty-reset">清除筛选</button></p></div>';
            }
            return '<div class="list-empty"><p>还没有标签</p><p class="muted">标签给前台做聚合，不是栏目。也可以先在影片里填标签，名字会自动建档。</p><p><button type="button" class="btn btn-primary btn-sm" id="tag-empty-add">新增标签</button></p></div>';
        },
        onDraw: function (_wrap, list) {
            countEl.textContent = list.length ? '· ' + list.length : '';
            var add = document.getElementById('tag-empty-add');
            var reset = document.getElementById('tag-empty-reset');
            if (add) add.addEventListener('click', function () { openDialog('add'); });
            if (reset) reset.addEventListener('click', function () { form.reset(); runSearch(); });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = '已选 ' + ids.length + ' 个';
        },
        cols: [
            {check: true, width: 36},
            {title: '标签', html: nameHtml},
            {key: 'sort', title: '排序', width: 64},
            {title: '状态', width: 72, html: function (d) {
                return String(d.status) === '1' ? U.status(true, '启用') : U.status(false, '禁用');
            }},
            {title: '操作', cls: 'actions', html: function (d) {
                var href = d.url ? String(d.url) : ('/tag/' + encodeURIComponent(d.slug || d.id));
                return '<a href="/admin/video?tag_id=' + encodeURIComponent(d.id) + '" class="btn-link">影片</a>'
                    + '<a href="' + U.escape(href) + '" target="_blank" rel="noopener" class="btn-link">前台</a>'
                    + '<a href="#" class="btn-link js-edit">编辑</a>'
                    + '<a href="#" class="btn-link js-del">删除</a>';
            }}
        ]
    });
    markChips();

    function openDialog(mode, row) {
        row = row || {};
        U.dialog({
            title: mode === 'edit' ? '编辑标签' : '新增标签',
            content: document.getElementById('video-tag-dialog-tpl').innerHTML,
            onOpen: function (body) {
                U.fillForm(body.querySelector('form'), {
                    id: mode === 'edit' ? (row.id || '') : '',
                    name: row.name || '',
                    slug: row.slug || '',
                    sort: row.sort == null ? 0 : row.sort,
                    status: row.status == null ? '1' : String(row.status)
                });
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (!data.name) { U.toast('请填写名称', 'err'); return false; }
                if (mode !== 'edit') delete data.id; else data.id = row.id;
                return U.post('/admin/video/tags/save', data).then(function (res) {
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
        if (!ids.length) { U.toast('请先勾选标签', 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/tags/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '操作失败', 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || '操作成功', 'ok');
        });
    }

    U.on('#video-tag-search-btn', 'click', runSearch);
    U.on('#video-tag-reset-btn', 'click', function () { setTimeout(runSearch, 0); });
    U.on('#video-tag-add-btn', 'click', function () { openDialog('add'); });
    document.getElementById('tag-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#tag-batch-on', 'click', function () { batch('status', 1); });
    U.on('#tag-batch-off', 'click', function () { batch('status', 0); });
    U.on('#tag-batch-del', 'click', function () { batch('delete', '', '确认删除选中标签？与影片的关联会一起去掉。'); });
    U.on('#tag-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#video-tag-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        if (a.target === '_blank' || (a.getAttribute('href') || '').indexOf('/admin/video') === 0) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-edit')) openDialog('edit', row);
        if (a.classList.contains('js-del')) {
            if (!U.confirm('删除标签「' + (row.name || '') + '」？')) return;
            U.post('/admin/video/tags/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh();
                U.toast('已删除', 'ok');
            });
        }
    });
})();
</script>
@endpush
