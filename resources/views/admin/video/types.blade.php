@extends('admin.layouts.inner')
@section('title', admin_t('page.types'))

@section('plain')
<div class="card card-panel type-index">
    <div class="card-header">
        <span>分类 <em id="type-count"></em></span>
        <button type="button" class="btn btn-sm" id="video-type-add-btn">新增分类</button>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="video-type-search" onsubmit="return false;">
            <input type="text" name="name" placeholder="搜分类名" autocomplete="off">
            <button type="button" class="btn btn-sm" id="video-type-search-btn">查询</button>
            <button type="reset" class="btn btn-muted btn-sm" id="video-type-reset-btn">重置</button>
        </form>
        <p class="muted recycle-lead">下级缩进显示。先建电影 / 剧集这种一级，再在下面加动作片、国产剧。</p>
        <div class="batch-bar" id="type-batch" hidden>
            <strong id="type-batch-count">已选 0 个</strong>
            <button type="button" class="btn btn-sm" id="type-batch-on">启用</button>
            <button type="button" class="btn btn-muted btn-sm" id="type-batch-off">禁用</button>
            <select id="type-batch-parent" class="batch-select"><option value="">改到上级</option></select>
            <button type="button" class="btn btn-muted btn-sm" id="type-batch-move">移动</button>
            <button type="button" class="btn btn-danger btn-sm" id="type-batch-del">删除</button>
            <button type="button" class="btn btn-muted btn-sm" id="type-batch-clear">取消选择</button>
        </div>
        <div id="video-type-table"></div>
    </div>
</div>
<template id="video-type-dialog-tpl">
    <form>
        <input type="hidden" name="id">
        <label>名称</label>
        <input type="text" name="name">
        <label>别名</label>
        <input type="text" name="slug" placeholder="如 movie，前台网址用">
        <label>上级</label>
        <select name="parent_id"></select>
        <label>模型</label>
        <select name="mid">
            <option value="1">视频</option>
            <option value="2">文章</option>
            <option value="3">网址</option>
        </select>
        <label>排序</label>
        <input type="number" name="sort" value="0">
        <label>状态</label>
        <select name="status">
            <option value="1">启用</option>
            <option value="0">禁用</option>
        </select>
        <details class="form-more">
            <summary>SEO</summary>
            <label>标题</label>
            <input type="text" name="seo_title">
            <label>关键词</label>
            <input type="text" name="seo_keywords">
            <label>描述</label>
            <textarea name="seo_description"></textarea>
        </details>
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var form = document.getElementById('video-type-search');
    var batchBar = document.getElementById('type-batch');
    var batchCount = document.getElementById('type-batch-count');
    var countEl = document.getElementById('type-count');

    function isFiltered(where) {
        return Object.keys(where || {}).some(function (k) { return where[k] !== ''; });
    }
    function fillParentSelect(sel, excludeId, selected, placeholder) {
        if (!sel) return;
        excludeId = parseInt(excludeId, 10) || 0;
        var skip = {};
        if (excludeId) skip[excludeId] = true;
        var rows = table.rows() || [];
        rows.forEach(function (r) {
            var id = parseInt(r.id, 10) || 0;
            var pid = parseInt(r.parent_id, 10) || 0;
            if (skip[pid]) skip[id] = true;
        });
        var html = placeholder ? '<option value="">' + U.escape(placeholder) + '</option>' : '';
        html += '<option value="0">顶级</option>';
        rows.forEach(function (r) {
            var id = parseInt(r.id, 10) || 0;
            if (skip[id]) return;
            var pad = '';
            var d = parseInt(r.depth, 10) || 0;
            while (d-- > 0) pad += '└ ';
            html += '<option value="' + U.escape(r.id) + '">' + pad + U.escape(r.name || '') + '</option>';
        });
        sel.innerHTML = html;
        if (placeholder && (selected === '' || selected == null)) {
            sel.value = '';
            return;
        }
        sel.value = selected == null || selected === '' ? '0' : String(selected);
    }
    function fillBatchParent() {
        fillParentSelect(document.getElementById('type-batch-parent'), 0, '', '改到上级');
    }
    function nameHtml(d) {
        var depth = parseInt(d.depth, 10) || 0;
        var branch = depth > 0 ? '<span class="cat-branch">└</span>' : '';
        var meta = '#' + U.escape(d.id);
        if (d.slug) meta += ' · /' + U.escape(d.slug);
        if (parseInt(d.video_count, 10) > 0) {
            meta += ' · <a href="/admin/video?type_id=' + encodeURIComponent(d.id) + '">' + U.escape(d.video_count) + ' 部</a>';
        } else {
            meta += ' · 0 部';
        }
        if (parseInt(d.child_count, 10) > 0) meta += ' · ' + U.escape(d.child_count) + ' 个子类';
        return '<div class="cat-cell" style="padding-left:' + (depth * 22) + 'px">' + branch
            + '<div><a class="vod-title js-edit" href="#">' + U.escape(d.name || '') + '</a>'
            + '<div class="muted">' + meta + '</div></div></div>';
    }
    function midHtml(d) {
        var map = {2: '文章', 3: '网址'};
        return map[String(d.mid)] || '视频';
    }

    var table = U.table({
        el: '#video-type-table',
        url: '/admin/video/types/list',
        pager: false,
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>没有符合名称的分类</p><p><button type="button" class="btn btn-muted btn-sm" id="type-empty-reset">清除筛选</button></p></div>';
            }
            return '<div class="list-empty"><p>还没有分类</p><p class="muted">栏目是片库的目录。先建一级，再点「添加下级」挂动作片、国产剧。</p><p><button type="button" class="btn btn-primary btn-sm" id="type-empty-add">新增分类</button></p></div>';
        },
        onDraw: function (_wrap, list) {
            countEl.textContent = list.length ? '· ' + list.length : '';
            fillBatchParent();
            var add = document.getElementById('type-empty-add');
            var reset = document.getElementById('type-empty-reset');
            if (add) add.addEventListener('click', function () { openDialog('add', {}); });
            if (reset) reset.addEventListener('click', function () { form.reset(); table.reload({}); });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = '已选 ' + ids.length + ' 个';
        },
        cols: [
            {check: true, width: 36},
            {title: '分类', html: nameHtml},
            {title: '模型', width: 72, html: midHtml},
            {key: 'sort', title: '排序', width: 64},
            {title: '状态', width: 72, html: function (d) {
                return String(d.status) === '1' ? U.status(true, '启用') : U.status(false, '禁用');
            }},
            {title: '操作', cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-child">下级</a>'
                    + '<a href="#" class="btn-link js-vod">影片</a>'
                    + '<a href="#" class="btn-link js-edit">编辑</a>'
                    + '<a href="#" class="btn-link js-del">删除</a>';
            }}
        ]
    });

    function openDialog(mode, row) {
        row = row || {};
        U.dialog({
            title: mode === 'edit' ? '编辑分类' : (row.parent_id ? '添加下级' : '新增分类'),
            content: document.getElementById('video-type-dialog-tpl').innerHTML,
            onOpen: function (body) {
                var formEl = body.querySelector('form');
                fillParentSelect(
                    body.querySelector('select[name=parent_id]'),
                    mode === 'edit' ? row.id : 0,
                    row.parent_id == null ? '0' : String(row.parent_id)
                );
                U.fillForm(formEl, {
                    id: mode === 'edit' ? (row.id || '') : '',
                    name: mode === 'edit' ? (row.name || '') : '',
                    slug: mode === 'edit' ? (row.slug || '') : '',
                    parent_id: row.parent_id == null ? '0' : String(row.parent_id),
                    mid: row.mid == null ? '1' : String(row.mid),
                    sort: row.sort == null ? 0 : row.sort,
                    status: row.status == null ? '1' : String(row.status),
                    seo_title: row.seo_title || '',
                    seo_keywords: row.seo_keywords || '',
                    seo_description: row.seo_description || ''
                });
                if (row.seo_title || row.seo_keywords || row.seo_description) {
                    var more = body.querySelector('.form-more');
                    if (more) more.open = true;
                }
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (!data.name) { U.toast('请填写名称', 'err'); return false; }
                if (mode !== 'edit') delete data.id;
                else data.id = row.id;
                return U.post('/admin/video/types/save', data).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return false; }
                    U.toast('保存成功', 'ok');
                    table.refresh();
                });
            }
        });
    }

    function selectedIds() { return table.selectedIds(); }
    function batch(action, value, confirmText) {
        var ids = selectedIds();
        if (!ids.length) { U.toast('请先勾选分类', 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/types/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '操作失败', 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || '操作成功', 'ok');
        });
    }

    U.on('#video-type-search-btn', 'click', function () {
        var where = {};
        var name = (form.name.value || '').trim();
        if (name) where.name = name;
        table.reload(where);
    });
    U.on('#video-type-reset-btn', 'click', function () {
        setTimeout(function () { table.reload({}); }, 0);
    });
    U.on('#video-type-add-btn', 'click', function () { openDialog('add', {}); });
    U.on('#type-batch-on', 'click', function () { batch('status', 1); });
    U.on('#type-batch-off', 'click', function () { batch('status', 0); });
    U.on('#type-batch-move', 'click', function () {
        var val = document.getElementById('type-batch-parent').value;
        if (val === '') { U.toast('请选择目标上级', 'err'); return; }
        batch('parent', val);
    });
    U.on('#type-batch-del', 'click', function () { batch('delete', '', '确认删除选中分类？有下级或影片的会跳过。'); });
    U.on('#type-batch-clear', 'click', function () { table.clearSelection(); });

    U.on('#video-type-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        if (a.getAttribute('href') && a.getAttribute('href').indexOf('/admin/video') === 0) return;
        e.preventDefault();
        if (a.classList.contains('js-edit')) openDialog('edit', row);
        if (a.classList.contains('js-child')) openDialog('add', {parent_id: row.id, mid: row.mid});
        if (a.classList.contains('js-vod')) location.href = '/admin/video?type_id=' + encodeURIComponent(row.id);
        if (a.classList.contains('js-del')) {
            if (!U.confirm('删除「' + (row.name || '') + '」？有下级或影片时无法删除。')) return;
            U.post('/admin/video/types/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh();
                U.toast('删除成功', 'ok');
            });
        }
    });
})();
</script>
@endpush
