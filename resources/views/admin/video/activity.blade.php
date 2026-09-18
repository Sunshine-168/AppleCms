@extends('admin.layouts.inner')
@section('title', $title ?? admin_t('nav.activity'))

@php
    $desk = in_array((string) ($desk ?? ''), ['tasks', 'logs', 'signs', 'milestones'], true)
        ? (string) $desk
        : 'tasks';
    $hint = (string) ($hint ?? '');
@endphp

@section('plain')
<div class="card card-panel activity-board" id="activity-board">
    <div class="card-header">
        <span>用户活动 <em id="activity-count"></em></span>
        <div>
            <button type="button" class="btn btn-sm" id="activity-add-btn">新增</button>
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">对照苹果：每日签到当场入账；连续天数达标再加里程碑。观看、评论、复制链接会涨每日任务。绑定手机有号才发一次。绑定邮箱默认关闭（注册就要邮箱）。分享不是微信。</p>
        <div class="queue-chips" id="activity-desks">
            <a class="chip{{ $desk === 'tasks' ? ' active' : '' }}" href="/admin/video/activity">任务</a>
            <a class="chip{{ $desk === 'logs' ? ' active' : '' }}" href="/admin/video/activity?desk=logs">记录</a>
            <a class="chip{{ $desk === 'signs' ? ' active' : '' }}" href="/admin/video/activity?desk=signs">签到</a>
            <a class="chip{{ $desk === 'milestones' ? ' active' : '' }}" href="/admin/video/activity?desk=milestones">里程碑</a>
        </div>
        <form class="filter-bar" id="activity-search" onsubmit="return false;">
            <input type="hidden" name="desk" value="{{ $desk }}">
            <input type="search" name="q" placeholder="{{ $desk === 'tasks' ? '搜任务名、动作' : ($desk === 'milestones' ? '搜里程碑' : '搜会员编号、动作或日期') }}" autocomplete="off">
            @if($desk === 'tasks')
                <select name="type" aria-label="类型">
                    <option value="">全部类型</option>
                    <option value="1">每日</option>
                    <option value="2">新手</option>
                </select>
                <select name="status" aria-label="状态">
                    <option value="">全部状态</option>
                    <option value="1">启用</option>
                    <option value="0">未启用</option>
                </select>
            @elseif($desk === 'logs')
                <select name="status" aria-label="状态">
                    <option value="">全部状态</option>
                    <option value="0">进行中</option>
                    <option value="1">待领取</option>
                    <option value="2">已入账</option>
                </select>
            @elseif($desk === 'milestones')
                <select name="status" aria-label="状态">
                    <option value="">全部状态</option>
                    <option value="1">启用</option>
                    <option value="0">未启用</option>
                </select>
            @endif
            <button type="button" class="btn btn-sm" id="activity-search-btn">查询</button>
            <button type="reset" class="btn btn-muted btn-sm" id="activity-reset-btn">重置</button>
        </form>
        <div id="activity-table"></div>
    </div>
</div>

<template id="activity-task-tpl">
    <form>
        <input type="hidden" name="id">
        <label>名称</label>
        <input type="text" name="name" required maxlength="40">
        <label>类型</label>
        <select name="type">
            <option value="1">每日</option>
            <option value="2">新手</option>
        </select>
        <label>动作</label>
        <input type="text" name="action" required maxlength="40" placeholder="daily_sign / watch_vod">
        <p class="muted field-hint">动作标识唯一。本站接上的：daily_sign、watch_vod、post_comment、share_vod、bind_phone。bind_email 默认关掉。</p>
        <label>说明</label>
        <input type="text" name="hint" maxlength="255">
        <label>积分</label>
        <input type="number" name="points" value="0" min="0">
        <label>目标次数</label>
        <input type="number" name="target" value="1" min="1">
        <label>排序</label>
        <input type="number" name="sort" value="0">
        <label>状态</label>
        <select name="status">
            <option value="1">启用</option>
            <option value="0">未启用</option>
        </select>
    </form>
</template>

<template id="activity-mile-tpl">
    <form>
        <input type="hidden" name="id">
        <label>名称</label>
        <input type="text" name="name" required maxlength="40">
        <label>连续天数</label>
        <input type="number" name="days" value="3" min="1">
        <p class="muted field-hint">达到连续天数当场入账，不用会员再点领取。</p>
        <label>积分</label>
        <input type="number" name="points" value="0" min="0">
        <label>排序</label>
        <input type="number" name="sort" value="0">
        <label>状态</label>
        <select name="status">
            <option value="1">启用</option>
            <option value="0">未启用</option>
        </select>
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var desk = @json($desk);
    var form = document.getElementById('activity-search');
    var countEl = document.getElementById('activity-count');
    var addBtn = document.getElementById('activity-add-btn');
    var module = desk === 'tasks' ? 'activity' : (desk === 'logs' ? 'task_logs' : (desk === 'signs' ? 'signs' : 'sign_milestones'));
    if (addBtn) {
        addBtn.style.display = (desk === 'tasks' || desk === 'milestones') ? '' : 'none';
        addBtn.textContent = desk === 'milestones' ? '新增里程碑' : '新增任务';
    }

    function cleanWhere(data) {
        var out = {};
        Object.keys(data || {}).forEach(function (k) {
            if (data[k] !== '' && data[k] != null) out[k] = data[k];
        });
        return out;
    }
    function queryWhere() {
        var data = cleanWhere(U.formData(form));
        data.limit = 20;
        data.desk = desk;
        return data;
    }
    function isFiltered(where) {
        return Object.keys(where || {}).some(function (k) {
            if (k === 'limit' || k === 'desk') return false;
            return where[k] !== '' && where[k] != null;
        });
    }
    function emptyHtml(_parsed, where) {
        if (isFiltered(where)) {
            return '<div class="list-empty"><p>没有符合条件的记录</p><p><button type="button" class="btn btn-muted btn-sm" id="activity-empty-reset">清除筛选</button></p></div>';
        }
        var copy = {
            tasks: ['还没有任务', '新增任务'],
            logs: ['还没有任务记录', ''],
            signs: ['还没有签到记录', ''],
            milestones: ['还没有里程碑', '新增里程碑']
        }[desk] || ['还没有记录', ''];
        if (!copy[1]) {
            return '<div class="list-empty"><p>' + copy[0] + '</p></div>';
        }
        return '<div class="list-empty"><p>' + copy[0] + '</p><p><button type="button" class="btn btn-primary btn-sm" id="activity-empty-add">' + copy[1] + '</button></p></div>';
    }

    var cols = [];
    if (desk === 'tasks') {
        cols = [
            {title: '名称', html: function (d) {
                return '<a class="entry-row-title js-edit" href="#">' + U.escape(d.name || '未填写') + '</a>';
            }},
            {title: '类型', width: 72, html: function (d) { return U.escape(d.type_label || ''); }},
            {title: '动作', width: 120, html: function (d) { return U.escape(d.action || ''); }},
            {title: '积分', width: 64, html: function (d) { return U.escape(String(d.points == null ? 0 : d.points)); }},
            {title: '目标', width: 64, html: function (d) { return U.escape(String(d.target == null ? 1 : d.target)); }},
            {title: '状态', width: 80, html: function (d) {
                return String(d.status) === '1' ? U.status(true, '启用') : U.status(false, '未启用');
            }},
            {title: '操作', cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-edit">编辑</a><a href="#" class="btn-link js-del">删除</a>';
            }}
        ];
    } else if (desk === 'logs') {
        cols = [
            {title: '任务', html: function (d) { return U.escape(d.task_name || d.action || ''); }},
            {title: '会员', width: 100, html: function (d) { return U.escape(d.member_name || String(d.member_id || '')); }},
            {title: '进度', width: 80, html: function (d) { return U.escape(String(d.progress == null ? 0 : d.progress)); }},
            {title: '积分', width: 64, html: function (d) { return U.escape(String(d.points == null ? 0 : d.points)); }},
            {title: '日期', width: 96, html: function (d) { return U.escape(d.day_key || ''); }},
            {title: '状态', width: 88, html: function (d) { return U.escape(d.status_label || ''); }},
            {title: '操作', cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-del">删除</a>';
            }}
        ];
    } else if (desk === 'signs') {
        cols = [
            {title: '会员', html: function (d) { return U.escape(d.member_name || String(d.member_id || '')); }},
            {title: '日期', width: 96, html: function (d) { return U.escape(d.day_key || ''); }},
            {title: '连续', width: 72, html: function (d) { return U.escape(String(d.days == null ? 0 : d.days)); }},
            {title: '积分', width: 72, html: function (d) { return U.escape(String(d.points == null ? 0 : d.points)); }},
            {title: '操作', cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-del">删除</a>';
            }}
        ];
    } else {
        cols = [
            {title: '名称', html: function (d) {
                return '<a class="entry-row-title js-edit" href="#">' + U.escape(d.name || ('连续' + (d.days || '') + '天')) + '</a>';
            }},
            {title: '天数', width: 72, html: function (d) { return U.escape(String(d.days == null ? 0 : d.days)); }},
            {title: '积分', width: 72, html: function (d) { return U.escape(String(d.points == null ? 0 : d.points)); }},
            {title: '状态', width: 80, html: function (d) {
                return String(d.status) === '1' ? U.status(true, '启用') : U.status(false, '未启用');
            }},
            {title: '操作', cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-edit">编辑</a><a href="#" class="btn-link js-del">删除</a>';
            }}
        ];
    }

    var table = U.table({
        el: '#activity-table',
        url: '/admin/video/' + module + '/list',
        where: queryWhere(),
        emptyHtml: emptyHtml,
        onDraw: function (_wrap, list) {
            countEl.textContent = list.length ? '· ' + list.length : '';
            var add = document.getElementById('activity-empty-add');
            var reset = document.getElementById('activity-empty-reset');
            if (add) add.addEventListener('click', function () { openDialog('add'); });
            if (reset) reset.addEventListener('click', function () { form.reset(); runSearch(); });
        },
        cols: cols
    });

    function runSearch() { table.reload(queryWhere()); }
    function fillTask(mode, row) {
        row = row || {};
        return {
            id: mode === 'edit' ? (row.id || '') : '',
            name: row.name || '',
            type: row.type == null ? '1' : String(row.type),
            action: row.action || '',
            hint: row.hint || '',
            points: row.points == null ? 0 : row.points,
            target: row.target == null ? 1 : row.target,
            sort: row.sort == null ? 0 : row.sort,
            status: row.status == null ? '1' : String(row.status)
        };
    }
    function fillMile(mode, row) {
        row = row || {};
        return {
            id: mode === 'edit' ? (row.id || '') : '',
            name: row.name || '',
            days: row.days == null ? 3 : row.days,
            points: row.points == null ? 0 : row.points,
            sort: row.sort == null ? 0 : row.sort,
            status: row.status == null ? '1' : String(row.status)
        };
    }
    function openDialog(mode, row) {
        if (desk !== 'tasks' && desk !== 'milestones') return;
        row = row || {};
        var isMile = desk === 'milestones';
        U.dialog({
            title: mode === 'edit' ? (isMile ? '编辑里程碑' : '编辑任务') : (isMile ? '新增里程碑' : '新增任务'),
            content: document.getElementById(isMile ? 'activity-mile-tpl' : 'activity-task-tpl').innerHTML,
            onOpen: function (body) {
                U.fillForm(body.querySelector('form'), isMile ? fillMile(mode, row) : fillTask(mode, row));
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (!data.name) { U.toast('请填写名称', 'err'); return false; }
                if (!isMile && !data.action) { U.toast('请填写动作标识', 'err'); return false; }
                if (mode !== 'edit') delete data.id; else data.id = row.id;
                return U.post('/admin/video/' + module + '/save', data).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return false; }
                    U.toast(mode === 'edit' ? '已保存' : '已创建', 'ok');
                    table.refresh();
                });
            }
        });
    }

    U.on('#activity-search-btn', 'click', runSearch);
    U.on('#activity-reset-btn', 'click', function () { setTimeout(runSearch, 0); });
    U.on('#activity-add-btn', 'click', function () { openDialog('add'); });
    U.on('#activity-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-edit')) openDialog('edit', row);
        if (a.classList.contains('js-del')) {
            if (!U.confirm('确认删除？')) return;
            U.post('/admin/video/' + module + '/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh();
                U.toast('已删除', 'ok');
            });
        }
    });
})();
</script>
@endpush
