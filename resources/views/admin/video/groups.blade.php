@extends('admin.layouts.inner')
@section('title', $title)

@php
    $queues = $queues ?? ['all' => 0, 'on' => 0, 'off' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
@endphp

@section('plain')
<div class="card card-panel group-index">
    <div class="card-header">
        <span>会员组 <em id="group-count"></em></span>
        <div>
            <a class="btn btn-muted btn-sm" href="/admin/video/members">会员</a>
            <button type="button" class="btn btn-sm" id="group-add-btn">新增分组</button>
        </div>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="group-search" onsubmit="return false;">
            <input type="hidden" name="status">
            <input type="text" name="name" placeholder="搜组名" autocomplete="off" aria-label="搜索会员组">
            <button type="button" class="btn btn-sm" id="group-search-btn">搜索</button>
            <button type="reset" class="btn btn-muted btn-sm" id="group-reset-btn">重置</button>
        </form>
        <div class="queue-chips" id="group-queues">
            <button type="button" class="chip" data-queue="">全部@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="1">启用@if($q('on') > 0)<em>{{ $q('on') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="0">已停用@if($q('off') > 0)<em>{{ $q('off') }}</em>@endif</button>
        </div>
        <p class="muted recycle-lead">会员组是点播档位。积分门槛、试看秒数、每天免费条数在这里。停用后权限关掉，人还在名单里。删掉后会员变成未分组。</p>
        <div class="batch-bar" id="group-batch" hidden>
            <strong id="group-batch-count">已选 0 组</strong>
            <button type="button" class="btn btn-sm" id="group-batch-on">启用</button>
            <button type="button" class="btn btn-muted btn-sm" id="group-batch-off">停用</button>
            <button type="button" class="btn btn-danger btn-sm" id="group-batch-del">删除</button>
            <button type="button" class="btn btn-muted btn-sm" id="group-batch-clear">取消选择</button>
        </div>
        <div id="group-table"></div>
    </div>
</div>
<template id="group-dialog-tpl">
    <form>
        <input type="hidden" name="id">
        <label>名称</label>
        <input type="text" name="name" placeholder="如 普通会员、VIP">
        <p class="muted field-hint">给会员分档时看到的名字。</p>
        <label>积分门槛</label>
        <input type="number" name="points_min" value="0" min="0">
        <p class="muted field-hint">满多少积分才适合进这组。不会自动升级，要在会员里改分组。</p>
        <label>试看秒数</label>
        <input type="number" name="trysee" value="0" min="0">
        <p class="muted field-hint">积分不够时能看几秒。0 表示不单独给试看。</p>
        <label>每天免费条数</label>
        <input type="number" name="day_free" value="0" min="0">
        <p class="muted field-hint">每天可以免积分点播几部。0 表示没有免费额度。</p>
        <label>排序</label>
        <input type="number" name="sort" value="0">
        <label>状态</label>
        <select name="status">
            <option value="1">启用</option>
            <option value="0">停用</option>
        </select>
        <details class="form-more">
            <summary>更多</summary>
            <label>点播需登录</label>
            <select name="need_login">
                <option value="0">否</option>
                <option value="1">是</option>
            </select>
            <p class="muted field-hint">标记这组成员看点播片是否必须先登录。</p>
        </details>
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var form = document.getElementById('group-search');
    var batchBar = document.getElementById('group-batch');
    var batchCount = document.getElementById('group-batch-count');
    var countEl = document.getElementById('group-count');

    function cleanWhere(data) {
        var out = {};
        Object.keys(data).forEach(function (k) { if (data[k] !== '') out[k] = data[k]; });
        return out;
    }
    function queryWhere() {
        return Object.assign({limit: 50}, cleanWhere(U.formData(form)));
    }
    function isFiltered(where) {
        return Object.keys(where || {}).some(function (k) { return k !== 'limit' && where[k] !== ''; });
    }
    function markChips() {
        var status = form.status.value;
        U.qa('#group-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = (key === '' && status === '') || (key === 'status' && status === val);
            chip.classList.toggle('active', on);
        });
    }
    function applyQueue(key, value) {
        form.status.value = key === 'status' ? (value || '') : '';
        runSearch();
    }
    function runSearch() {
        table.reload(queryWhere());
        markChips();
    }
    function nameHtml(d) {
        var badge = String(d.status) === '1' ? '' : '<span class="badge badge-off">停用</span>';
        var bits = [];
        var min = parseInt(d.points_min, 10) || 0;
        var trysee = parseInt(d.trysee, 10) || 0;
        var free = parseInt(d.day_free, 10) || 0;
        bits.push(min > 0 ? '满 ' + min + ' 积分' : '无门槛');
        if (trysee > 0) bits.push('试看 ' + trysee + ' 秒');
        if (free > 0) bits.push('每天免费 ' + free + ' 部');
        return '<div class="entry-row-title-line"><a class="entry-row-title js-edit" href="#">' + U.escape(d.name || '未命名') + '</a> ' + badge + '</div>'
            + '<div class="entry-row-meta">' + U.escape(bits.join(' · ')) + '</div>';
    }
    function peopleHtml(d) {
        var n = parseInt(d.member_count, 10) || 0;
        if (n < 1) return '<span class="muted">还没人</span>';
        return '<a href="/admin/video/members?group_id=' + encodeURIComponent(d.id) + '">' + n + ' 人</a>';
    }

    var table = U.table({
        el: '#group-table',
        countEl: countEl,
        url: '/admin/video/groups/list',
        where: queryWhere(),
        pager: false,
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>没有符合条件的会员组。</p><p><button type="button" class="btn btn-muted btn-sm" id="group-empty-reset">清除筛选</button></p></div>';
            }
            return '<div class="list-empty"><p>还没有会员组。</p><p class="muted">先建「普通会员」「VIP」这种档位，再把人分进去。</p><p><button type="button" class="btn btn-primary btn-sm" id="group-empty-add">新增分组</button></p></div>';
        },
        onDraw: function (_wrap, list) {
            var add = document.getElementById('group-empty-add');
            var reset = document.getElementById('group-empty-reset');
            if (add) add.addEventListener('click', function () { openDialog('add'); });
            if (reset) reset.addEventListener('click', function () { form.reset(); runSearch(); });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = '已选 ' + ids.length + ' 组';
        },
        cols: [
            {check: true, width: 36},
            {title: '分组', html: nameHtml},
            {title: '会员', width: 88, html: peopleHtml},
            {key: 'sort', title: '排序', width: 64},
            {title: '状态', width: 72, html: function (d) {
                return String(d.status) === '1' ? U.status(true, '启用') : U.status(false, '停用');
            }},
            {title: '操作', cls: 'actions', html: function (d) {
                var html = String(d.status) === '1'
                    ? '<a href="#" class="btn-link js-off">停用</a>'
                    : '<a href="#" class="btn-link js-on">启用</a>';
                html += '<a href="#" class="btn-link js-edit">编辑</a><a href="#" class="btn-link js-del">删除</a>';
                return html;
            }}
        ]
    });
    markChips();

    function openDialog(mode, row) {
        row = row || {};
        U.dialog({
            title: mode === 'edit' ? '编辑分组' : '新增分组',
            content: document.getElementById('group-dialog-tpl').innerHTML,
            onOpen: function (body) {
                U.fillForm(body.querySelector('form'), {
                    id: mode === 'edit' ? (row.id || '') : '',
                    name: row.name || '',
                    points_min: row.points_min == null ? 0 : row.points_min,
                    trysee: row.trysee == null ? 0 : row.trysee,
                    day_free: row.day_free == null ? 0 : row.day_free,
                    need_login: row.need_login == null ? '0' : String(row.need_login),
                    sort: row.sort == null ? 0 : row.sort,
                    status: row.status == null ? '1' : String(row.status)
                });
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (!data.name) { U.toast('请填写名称', 'err'); return false; }
                if (mode !== 'edit') delete data.id; else data.id = row.id;
                return U.post('/admin/video/groups/save', data).then(function (res) {
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
        if (!ids.length) { U.toast('请先勾选会员组', 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/groups/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '操作失败', 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || '操作成功', 'ok');
        });
    }
    function setStatus(row, status) {
        U.post('/admin/video/groups/save', {id: row.id, status: status}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
            table.refresh();
            U.toast(status === 1 ? '已启用' : '已停用', 'ok');
        });
    }

    U.on('#group-search-btn', 'click', runSearch);
    U.on('#group-reset-btn', 'click', function () { setTimeout(runSearch, 0); });
    U.on('#group-add-btn', 'click', function () { openDialog('add'); });
    document.getElementById('group-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#group-batch-on', 'click', function () { batch('status', 1); });
    U.on('#group-batch-off', 'click', function () { batch('status', 0); });
    U.on('#group-batch-del', 'click', function () { batch('delete', '', '删除选中分组？里面的会员会变成未分组。'); });
    U.on('#group-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#group-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        if (a.getAttribute('href') && a.getAttribute('href').indexOf('/admin/video/members') === 0) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-edit')) openDialog('edit', row);
        if (a.classList.contains('js-on')) setStatus(row, 1);
        if (a.classList.contains('js-off')) setStatus(row, 0);
        if (a.classList.contains('js-del')) {
            var n = parseInt(row.member_count, 10) || 0;
            var msg = n > 0
                ? '「' + (row.name || '') + '」还有 ' + n + ' 人。删掉后他们变成未分组，确定？'
                : '确定删除「' + (row.name || '') + '」？';
            if (!U.confirm(msg)) return;
            U.post('/admin/video/groups/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh();
                U.toast('已删除', 'ok');
            });
        }
    });
})();
</script>
@endpush
