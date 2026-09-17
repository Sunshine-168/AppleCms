@extends('admin.layouts.inner')
@section('title', $title)

@php
    $groups = $groups ?? [];
    $queues = $queues ?? ['all' => 0, 'off' => 0, 'none' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
@endphp

@section('plain')
<div class="card card-panel member-index">
    <div class="card-header">
        <span>会员 <em id="member-count"></em></span>
        <div>
            <a class="btn btn-muted btn-sm" href="/admin/video/groups">会员组</a>
            <button type="button" class="btn btn-sm" id="member-add-btn">新建会员</button>
        </div>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="member-search" onsubmit="return false;">
            <input type="hidden" name="group_id">
            <input type="hidden" name="status">
            <input type="search" name="q" placeholder="搜索昵称、邮箱或 ID" autocomplete="off" aria-label="搜索会员">
            <button type="button" class="btn btn-sm" id="member-search-btn">搜索</button>
            <button type="reset" class="btn btn-muted btn-sm" id="member-reset-btn">重置</button>
        </form>
        <div class="queue-chips" id="member-queues">
            <button type="button" class="chip" data-queue="">全部@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            @foreach($groups as $group)
                <button type="button" class="chip" data-queue="group_id" data-value="{{ $group['id'] }}">{{ $group['name'] }}@if(($group['count'] ?? 0) > 0)<em>{{ $group['count'] }}</em>@endif</button>
            @endforeach
            <button type="button" class="chip" data-queue="group_id" data-value="0">未分组@if($q('none') > 0)<em>{{ $q('none') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="0">已停用@if($q('off') > 0)<em>{{ $q('off') }}</em>@endif</button>
        </div>
        <p class="muted recycle-lead">他们登录的是网站，不是后台。积分用来点播；分组决定试看和门槛。点姓名改资料，勾选后可停用、换组或调积分。</p>
        <div class="batch-bar" id="member-batch" hidden>
            <strong id="member-batch-count">已选 0 人</strong>
            <button type="button" class="btn btn-sm" id="member-batch-on">启用</button>
            <button type="button" class="btn btn-muted btn-sm" id="member-batch-off">停用</button>
            <select id="member-batch-group" class="batch-select" aria-label="目标分组">
                <option value="">改到分组</option>
                <option value="0">未分组</option>
                @foreach($groups as $group)
                    <option value="{{ $group['id'] }}">{{ $group['name'] }}</option>
                @endforeach
            </select>
            <button type="button" class="btn btn-muted btn-sm" id="member-batch-move">移动</button>
            <input type="number" id="member-batch-points" class="batch-points" placeholder="积分±" aria-label="调整积分">
            <button type="button" class="btn btn-muted btn-sm" id="member-batch-points-go">调整积分</button>
            <button type="button" class="btn btn-danger btn-sm" id="member-batch-del">删除</button>
            <button type="button" class="btn btn-muted btn-sm" id="member-batch-clear">取消选择</button>
        </div>
        <div id="member-table"></div>
    </div>
</div>
<template id="member-dialog-tpl">
    <form>
        <input type="hidden" name="id">
        <label>昵称</label>
        <input type="text" name="name" placeholder="前台看到的名字">
        <p class="muted field-hint">评论和个人中心里显示的名字。</p>
        <label>登录邮箱</label>
        <input type="email" name="email" placeholder="用来登录网站">
        <p class="muted field-hint">这是前台账号，进不了后台。</p>
        <label>密码</label>
        <input type="password" name="password" autocomplete="new-password" placeholder="新建必填，编辑留空不改">
        <label>会员组</label>
        <select name="group_id">
            <option value="0">未分组</option>
            @foreach($groups as $group)
                <option value="{{ $group['id'] }}">{{ $group['name'] }}{{ (int) ($group['status'] ?? 1) === 1 ? '' : '（停用）' }}</option>
            @endforeach
        </select>
        <p class="muted field-hint">决定试看秒数和积分门槛。组权限在「会员组」里改。</p>
        <label>积分</label>
        <input type="number" name="points" value="0">
        <p class="muted field-hint">点播扣分用。改这里会记进积分流水。</p>
        <label>状态</label>
        <select name="status">
            <option value="1">正常</option>
            <option value="0">停用</option>
        </select>
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var QUEUE_KEYS = ['group_id'];
    var form = document.getElementById('member-search');
    var qs = new URLSearchParams(location.search);
    if (qs.get('group_id') && form.group_id) form.group_id.value = qs.get('group_id');
    var batchBar = document.getElementById('member-batch');
    var batchCount = document.getElementById('member-batch-count');
    var countEl = document.getElementById('member-count');

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
        var groupId = form.group_id.value;
        U.qa('#member-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = false;
            if (key === '' && status === '' && groupId === '') on = true;
            else if (key === 'group_id' && status === '' && groupId === val) on = true;
            else if (key === 'status' && groupId === '' && status === val) on = true;
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
        var badge = String(d.status) === '1' ? '' : '<span class="badge badge-off">停用</span>';
        var meta = U.escape(d.email || '');
        if (d.joined_text) meta += (meta ? ' · ' : '') + '加入 ' + U.escape(d.joined_text);
        meta += (meta ? ' · ' : '') + '#' + U.escape(d.id);
        return '<div class="entry-row-title-line"><a class="entry-row-title js-edit" href="#">' + U.escape(d.name || '未命名') + '</a> ' + badge + '</div>'
            + '<div class="entry-row-meta">' + meta + '</div>';
    }

    var table = U.table({
        el: '#member-table',
        url: '/admin/video/members/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>没有符合条件的会员。</p><p><button type="button" class="btn btn-muted btn-sm" id="member-empty-reset">清除筛选</button></p></div>';
            }
            return '<div class="list-empty"><p>还没有会员。</p><p class="muted">前台注册或这里添加。他们登录的是网站，不是后台。</p><p><button type="button" class="btn btn-primary btn-sm" id="member-empty-add">新建会员</button></p></div>';
        },
        onDraw: function (_wrap, list) {
            countEl.textContent = list.length ? '· ' + list.length : '';
            var add = document.getElementById('member-empty-add');
            var reset = document.getElementById('member-empty-reset');
            if (add) add.addEventListener('click', function () { openDialog('add'); });
            if (reset) reset.addEventListener('click', function () { form.reset(); runSearch(); });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = '已选 ' + ids.length + ' 人';
        },
        cols: [
            {check: true, width: 36},
            {title: '会员', html: nameHtml},
            {title: '分组', width: 120, html: function (d) { return U.escape(d.group_name || '未分组'); }},
            {key: 'points', title: '积分', width: 72},
            {title: '状态', width: 72, html: function (d) {
                return String(d.status) === '1' ? U.status(true, '正常') : U.status(false, '停用');
            }},
            {title: '操作', cls: 'actions', html: function (d) {
                var html = String(d.status) === '1'
                    ? '<a href="#" class="btn-link js-off">停用</a>'
                    : '<a href="#" class="btn-link js-on">启用</a>';
                html += '<a href="#" class="btn-link js-edit">编辑</a>';
                html += '<a href="/admin/video/plogs?member_id=' + encodeURIComponent(d.id) + '">流水</a>';
                html += '<a class="btn-link" href="/admin/video/pms?to=' + encodeURIComponent(d.id) + '">写信</a>';
                html += '<a class="btn-link" href="/admin/video/notifies?member=' + encodeURIComponent(d.id) + '">通知</a>';
                html += '<a href="#" class="btn-link js-del">删除</a>';
                return html;
            }}
        ]
    });
    markChips();

    function openDialog(mode, row) {
        row = row || {};
        U.dialog({
            title: mode === 'edit' ? '编辑会员' : '新建会员',
            content: document.getElementById('member-dialog-tpl').innerHTML,
            onOpen: function (body) {
                var formEl = body.querySelector('form');
                U.fillForm(formEl, {
                    id: mode === 'edit' ? (row.id || '') : '',
                    name: row.name || '',
                    email: row.email || '',
                    password: '',
                    group_id: row.group_id == null || row.group_id === '' ? '0' : String(row.group_id),
                    points: row.points == null ? 0 : row.points,
                    status: row.status == null ? '1' : String(row.status)
                });
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (!data.name) { U.toast('请填写昵称', 'err'); return false; }
                if (!data.email) { U.toast('请填写邮箱', 'err'); return false; }
                if (mode !== 'edit' && !data.password) { U.toast('请填写密码', 'err'); return false; }
                if (mode !== 'edit') delete data.id; else data.id = row.id;
                if (!data.password) delete data.password;
                return U.post('/admin/video/members/save', data).then(function (res) {
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
        if (!ids.length) { U.toast('请先勾选会员', 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/members/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '操作失败', 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || '操作成功', 'ok');
        });
    }
    function setStatus(row, status) {
        U.post('/admin/video/members/save', {id: row.id, status: status}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
            table.refresh();
            U.toast(status === 1 ? '已启用' : '已停用', 'ok');
        });
    }

    U.on('#member-search-btn', 'click', runSearch);
    U.on('#member-reset-btn', 'click', function () { setTimeout(runSearch, 0); });
    U.on('#member-add-btn', 'click', function () { openDialog('add'); });
    document.getElementById('member-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#member-batch-on', 'click', function () { batch('status', 1); });
    U.on('#member-batch-off', 'click', function () { batch('status', 0); });
    U.on('#member-batch-move', 'click', function () {
        var sel = document.getElementById('member-batch-group');
        if (sel.value === '') { U.toast('请先选择分组，再点「移动」', 'err'); return; }
        batch('group', sel.value);
    });
    U.on('#member-batch-points-go', 'click', function () {
        var val = parseInt(document.getElementById('member-batch-points').value, 10);
        if (!val) { U.toast('请填写不为 0 的积分，例如 100 或 -50', 'err'); return; }
        batch('points', val, '给已选会员调整 ' + val + ' 积分？会记进流水。');
    });
    U.on('#member-batch-del', 'click', function () { batch('delete', '', '确定删除选中会员？不能再登录，评论还在。'); });
    U.on('#member-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#member-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        if (a.getAttribute('href') && (a.getAttribute('href').indexOf('/admin/video/plogs') === 0 || a.getAttribute('href').indexOf('/admin/video/pms') === 0 || a.getAttribute('href').indexOf('/admin/video/notifies') === 0)) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-edit')) openDialog('edit', row);
        if (a.classList.contains('js-on')) setStatus(row, 1);
        if (a.classList.contains('js-off')) {
            if (!U.confirm('停用「' + (row.name || '') + '」？不能登录前台，评论还在。')) return;
            setStatus(row, 0);
        }
        if (a.classList.contains('js-del')) {
            if (!U.confirm('确定删除「' + (row.name || row.email || '') + '」？不能再登录，评论还在。')) return;
            U.post('/admin/video/members/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh();
                U.toast('已删除', 'ok');
            });
        }
    });
})();
</script>
@endpush
