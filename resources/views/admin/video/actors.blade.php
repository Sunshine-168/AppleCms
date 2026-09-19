@extends('admin.layouts.inner')
@section('title', admin_t('page.actors'))

@section('plain')
<div class="card card-panel actor-index list-desk">
    <div class="card-header">
        <span>演员 <em id="actor-count"></em></span>
        <button type="button" class="btn btn-sm" id="actor-add-btn">新增演员</button>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="actor-search" onsubmit="return false;">
            <input type="hidden" name="empty_pic">
            <input type="hidden" name="repeat">
            <input type="text" name="name" placeholder="搜演员名" autocomplete="off">
            <select name="status">
                <option value="">状态</option>
                <option value="1">上架</option>
                <option value="0">下架</option>
            </select>
            <button type="button" class="btn btn-sm" id="actor-search-btn">查询</button>
            <button type="reset" class="btn btn-muted btn-sm" id="actor-reset-btn">重置</button>
        </form>
        <div class="queue-chips" id="actor-queues">
            <button type="button" class="chip" data-queue="">全部</button>
            <button type="button" class="chip" data-queue="status" data-value="1">上架</button>
            <button type="button" class="chip" data-queue="status" data-value="0">下架</button>
            <button type="button" class="chip" data-queue="empty_pic" data-value="1">无头像</button>
            <button type="button" class="chip" data-queue="repeat" data-value="1">重名</button>
        </div>
        <p class="muted recycle-lead">演员是人物库。影片里填主演名会自动建档；这里补头像和简介。</p>
        <div class="batch-bar" id="actor-batch" hidden>
            <strong id="actor-batch-count">已选 0 个</strong>
            <button type="button" class="btn btn-sm" id="actor-batch-on">上架</button>
            <button type="button" class="btn btn-muted btn-sm" id="actor-batch-off">下架</button>
            <button type="button" class="btn btn-danger btn-sm" id="actor-batch-del">删除</button>
            <button type="button" class="btn btn-muted btn-sm" id="actor-batch-clear">取消选择</button>
        </div>
        <div id="actor-table"></div>
    </div>
</div>
<template id="actor-dialog-tpl">
    <form>
        <input type="hidden" name="id">
        <label>名称</label>
        <input type="text" name="name" placeholder="如 梁朝伟">
        <label>别名</label>
        <input type="text" name="slug" placeholder="前台网址用，可空">
        <label>头像</label>
        <div class="field-inline">
            <input type="text" name="avatar" placeholder="图片 URL">
            <button type="button" class="btn btn-muted actor-avatar-upload-btn">上传</button>
        </div>
        <img class="img-preview actor-avatar-preview" alt="">
        <label>状态</label>
        <select name="status">
            <option value="1">上架</option>
            <option value="0">下架</option>
        </select>
        <label>排序</label>
        <input type="number" name="sort" value="0">
        <details class="form-more">
            <summary>资料</summary>
            <label>性别</label>
            <select name="sex">
                <option value="">未知</option>
                <option value="男">男</option>
                <option value="女">女</option>
            </select>
            <label>地区</label>
            <input type="text" name="area" placeholder="如 中国香港">
            <label>生日</label>
            <input type="text" name="birthday" placeholder="如 1962-06-27">
            <label>简介</label>
            <textarea name="content"></textarea>
        </details>
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var QUEUE_KEYS = ['empty_pic', 'repeat'];
    var form = document.getElementById('actor-search');
    var batchBar = document.getElementById('actor-batch');
    var batchCount = document.getElementById('actor-batch-count');
    var countEl = document.getElementById('actor-count');

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
        var active = '';
        QUEUE_KEYS.forEach(function (k) {
            if (form[k] && form[k].value === '1') active = k;
        });
        U.qa('#actor-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = false;
            if (key === '' && !active && status === '') on = true;
            else if (key === 'status' && !active && status === val) on = true;
            else if (key && key !== 'status' && active === key) on = true;
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
        var cover = String(d.avatar || '').trim();
        var thumb = cover
            ? '<img class="vod-thumb actor-thumb" src="' + U.escape(cover) + '" alt="">'
            : '<span class="vod-thumb actor-thumb is-empty">无图</span>';
        var meta = '#' + U.escape(d.id);
        var n = parseInt(d.video_count, 10) || 0;
        meta += n > 0 ? ' · ' + n + ' 部' : ' · 还没挂片';
        if (d.sex) meta += ' · ' + U.escape(d.sex);
        if (d.area) meta += ' · ' + U.escape(d.area);
        return '<div class="vod-cell">' + thumb + '<div><a class="vod-title js-edit" href="#">' + U.escape(d.name || '') + '</a>'
            + '<div class="muted">' + meta + '</div></div></div>';
    }

    var table = U.table({
        el: '#actor-table',
        queueKeys: QUEUE_KEYS,
        countEl: countEl,
        url: '/admin/video/actors/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>没有符合条件的演员</p><p><button type="button" class="btn btn-muted btn-sm" id="actor-empty-reset">清除筛选</button></p></div>';
            }
            return '<div class="list-empty"><p>还没有演员</p><p class="muted">人物库给前台演员页用。也可以先在影片里填主演，名字会自动建档，再回来补头像。</p><p><button type="button" class="btn btn-primary btn-sm" id="actor-empty-add">新增演员</button></p></div>';
        },
        onDraw: function (_wrap, list) {
            var add = document.getElementById('actor-empty-add');
            var reset = document.getElementById('actor-empty-reset');
            if (add) add.addEventListener('click', function () { openDialog('add'); });
            if (reset) reset.addEventListener('click', function () { form.reset(); runSearch(); });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = '已选 ' + ids.length + ' 个';
        },
        cols: [
            {check: true, width: 36},
            {title: '演员', html: nameHtml},
            {key: 'sort', title: '排序', width: 64},
            {title: '状态', width: 72, html: function (d) {
                return String(d.status) === '1' ? U.status(true, '上架') : U.status(false, '下架');
            }},
            {title: '操作', cls: 'actions', html: function (d) {
                var href = d.url ? String(d.url) : ('/actor/' + encodeURIComponent(d.id));
                return '<a href="/admin/video?actor_id=' + encodeURIComponent(d.id) + '" class="btn-link">影片</a>'
                    + '<a href="' + U.escape(href) + '" target="_blank" rel="noopener" class="btn-link">前台</a>'
                    + '<a href="#" class="btn-link js-edit">编辑</a>'
                    + '<a href="#" class="btn-link js-del">删除</a>';
            }}
        ]
    });
    markChips();

    function bindAvatar(formEl) {
        U.bindImageField(formEl, {
            input: 'input[name=avatar]',
            btn: '.actor-avatar-upload-btn',
            preview: '.actor-avatar-preview'
        });
    }

    function openDialog(mode, row) {
        row = row || {};
        U.dialog({
            title: mode === 'edit' ? '编辑演员' : '新增演员',
            content: document.getElementById('actor-dialog-tpl').innerHTML,
            onOpen: function (body) {
                var formEl = body.querySelector('form');
                U.fillForm(formEl, {
                    id: mode === 'edit' ? (row.id || '') : '',
                    name: row.name || '',
                    slug: row.slug || '',
                    avatar: row.avatar || '',
                    sex: row.sex || '',
                    area: row.area || '',
                    birthday: row.birthday || '',
                    content: row.content || '',
                    sort: row.sort == null ? 0 : row.sort,
                    status: row.status == null ? '1' : String(row.status)
                });
                bindAvatar(formEl);
                if (row.content || row.sex || row.area || row.birthday) {
                    var more = body.querySelector('.form-more');
                    if (more) more.open = true;
                }
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (!data.name) { U.toast('请填写名称', 'err'); return false; }
                if (mode !== 'edit') delete data.id; else data.id = row.id;
                return U.post('/admin/video/actors/save', data).then(function (res) {
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
        if (!ids.length) { U.toast('请先勾选演员', 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/actors/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '操作失败', 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || '操作成功', 'ok');
        });
    }

    U.on('#actor-search-btn', 'click', runSearch);
    U.on('#actor-reset-btn', 'click', function () { setTimeout(runSearch, 0); });
    U.on('#actor-add-btn', 'click', function () { openDialog('add'); });
    document.getElementById('actor-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#actor-batch-on', 'click', function () { batch('status', 1); });
    U.on('#actor-batch-off', 'click', function () { batch('status', 0); });
    U.on('#actor-batch-del', 'click', function () { batch('delete', '', '确认删除选中演员？与影片的关联会一起去掉。'); });
    U.on('#actor-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#actor-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        if (a.target === '_blank' || (a.getAttribute('href') || '').indexOf('/admin/video') === 0) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-edit')) openDialog('edit', row);
        if (a.classList.contains('js-del')) {
            if (!U.confirm('删除演员「' + (row.name || '') + '」？')) return;
            U.post('/admin/video/actors/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh();
                U.toast('已删除', 'ok');
            });
        }
    });
})();
</script>
@endpush
