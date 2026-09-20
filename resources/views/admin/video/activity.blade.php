@extends('admin.layouts.inner')
@section('title', $title ?? admin_t('nav.activity'))

@php
    $desk = in_array((string) ($desk ?? ''), ['tasks', 'logs', 'signs', 'milestones'], true)
        ? (string) $desk
        : 'tasks';
    $hint = (string) ($hint ?? '');
    $activityJsLang = [
        'add_task' => admin_t('ui.add_task'),
        'edit_task' => admin_t('ui.edit_task'),
        'add_milestone' => admin_t('ui.add_milestone'),
        'edit_milestone' => admin_t('ui.edit_milestone'),
        'no_match_rows' => admin_t('ui.no_match_rows'),
        'clear_filter' => admin_t('ui.clear_filter'),
        'empty_tasks' => admin_t('ui.empty_tasks'),
        'empty_task_logs' => admin_t('ui.empty_task_logs'),
        'empty_signs' => admin_t('ui.empty_signs'),
        'empty_milestones' => admin_t('ui.empty_milestones'),
        'please_fill_name' => admin_t('ui.please_fill_name'),
        'fail' => admin_t('ui.fail'),
        'saved' => admin_t('ui.saved'),
        'created' => admin_t('ui.created'),
        'deleted' => admin_t('ui.deleted'),
        'confirm_remove' => admin_t('ui.confirm_remove'),
        'action_key' => admin_t('ui.action_key'),
        'goal' => admin_t('ui.goal'),
        'streak' => admin_t('ui.streak'),
        'days_col' => admin_t('ui.days_col'),
        'tasks' => admin_t('nav.activity_tasks'),
        'progress' => admin_t('ui.progress'),
        'unit_day' => admin_t('ui.unit_day'),
        'please_fill_action' => admin_t('ui.please_fill_action'),
        'confirm_remove' => admin_t('ui.confirm_remove'),
    ];
@endphp

@section('plain')
<div class="card card-panel activity-board" id="activity-board">
    <div class="card-header">
        <span>{{ admin_t('nav.activity') }} <em id="activity-count"></em></span>
        <div>
            <button type="button" class="btn btn-sm" id="activity-add-btn">{{ admin_t('ui.add') }}</button>
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">{{ admin_t('ui.activity_lead') }}</p>
        <div class="queue-chips" id="activity-desks">
            <a class="chip{{ $desk === 'tasks' ? ' active' : '' }}" href="/admin/video/activity">{{ admin_t('nav.activity_tasks') }}</a>
            <a class="chip{{ $desk === 'logs' ? ' active' : '' }}" href="/admin/video/activity?desk=logs">{{ admin_t('ui.records') }}</a>
            <a class="chip{{ $desk === 'signs' ? ' active' : '' }}" href="/admin/video/activity?desk=signs">{{ admin_t('item.signs') }}</a>
            <a class="chip{{ $desk === 'milestones' ? ' active' : '' }}" href="/admin/video/activity?desk=milestones">{{ admin_t('ui.milestone') }}</a>
        </div>
        <form class="filter-bar" id="activity-search" onsubmit="return false;">
            <input type="hidden" name="desk" value="{{ $desk }}">
            <input type="search" name="q" placeholder="{{ $desk === 'tasks' ? admin_t('ui.ph_search_task') : ($desk === 'milestones' ? admin_t('ui.ph_search_milestone') : admin_t('ui.ph_search_activity')) }}" autocomplete="off">
            @if($desk === 'tasks')
                <select name="type" aria-label="{{ admin_t('ui.col_type') }}">
                    <option value="">{{ admin_t('ui.all_types') }}</option>
                    <option value="1">{{ admin_t('ui.daily') }}</option>
                    <option value="2">{{ admin_t('ui.newbie') }}</option>
                </select>
                <select name="status" aria-label="{{ admin_t('ui.status') }}">
                    <option value="">{{ admin_t('ui.all_status') }}</option>
                    <option value="1">{{ admin_t('ui.enabled') }}</option>
                    <option value="0">{{ admin_t('ui.not_enabled') }}</option>
                </select>
            @elseif($desk === 'logs')
                <select name="status" aria-label="{{ admin_t('ui.status') }}">
                    <option value="">{{ admin_t('ui.all_status') }}</option>
                    <option value="0">{{ admin_t('ui.in_progress') }}</option>
                    <option value="1">{{ admin_t('ui.claimable') }}</option>
                    <option value="2">{{ admin_t('ui.credited') }}</option>
                </select>
            @elseif($desk === 'milestones')
                <select name="status" aria-label="{{ admin_t('ui.status') }}">
                    <option value="">{{ admin_t('ui.all_status') }}</option>
                    <option value="1">{{ admin_t('ui.enabled') }}</option>
                    <option value="0">{{ admin_t('ui.not_enabled') }}</option>
                </select>
            @endif
            <button type="button" class="btn btn-sm" id="activity-search-btn">{{ admin_t('ui.search') }}</button>
            <button type="reset" class="btn btn-muted btn-sm" id="activity-reset-btn">{{ admin_t('ui.reset') }}</button>
        </form>
        <div id="activity-table"></div>
    </div>
</div>

<template id="activity-task-tpl">
    <form class="admin-form">
        <input type="hidden" name="id">
        <label>{{ admin_t('ui.name') }}</label>
        <input class="entry-title" type="text" name="name" required maxlength="40" autofocus>
        <div class="admin-dialog-grid">
            <div>
                <label>{{ admin_t('ui.col_type') }}</label>
                <select name="type">
                    <option value="1">{{ admin_t('ui.daily') }}</option>
                    <option value="2">{{ admin_t('ui.newbie') }}</option>
                </select>
            </div>
            <div>
                <label>{{ admin_t('ui.action_key') }}</label>
                <input type="text" name="action" required maxlength="40" placeholder="daily_sign / watch_vod">
            </div>
        </div>
        <p class="muted field-hint">{{ admin_t('ui.action_hint') }}</p>
        <label>{{ admin_t('ui.intro') }}</label>
        <input type="text" name="hint" maxlength="255" placeholder="{{ admin_t('ui.ph_task_hint') }}">
        <div class="admin-dialog-grid">
            <div>
                <label>{{ admin_t('ui.points') }}</label>
                <input type="number" name="points" value="0" min="0">
            </div>
            <div>
                <label>{{ admin_t('ui.goal_count') }}</label>
                <input type="number" name="target" value="1" min="1">
            </div>
        </div>
        <div class="admin-dialog-grid">
            <div>
                <label>{{ admin_t('ui.sort') }}</label>
                <input type="number" name="sort" value="0">
            </div>
            <div>
                <label>{{ admin_t('ui.status') }}</label>
                <select name="status">
                    <option value="1">{{ admin_t('ui.enabled') }}</option>
                    <option value="0">{{ admin_t('ui.not_enabled') }}</option>
                </select>
            </div>
        </div>
    </form>
</template>

<template id="activity-mile-tpl">
    <form class="admin-form">
        <input type="hidden" name="id">
        <label>{{ admin_t('ui.name') }}</label>
        <input class="entry-title" type="text" name="name" required maxlength="40" autofocus>
        <div class="admin-dialog-grid">
            <div>
                <label>{{ admin_t('ui.streak_days') }}</label>
                <input type="number" name="days" value="3" min="1">
            </div>
            <div>
                <label>{{ admin_t('ui.points') }}</label>
                <input type="number" name="points" value="0" min="0">
            </div>
        </div>
        <p class="muted field-hint">{{ admin_t('ui.streak_hint') }}</p>
        <div class="admin-dialog-grid">
            <div>
                <label>{{ admin_t('ui.sort') }}</label>
                <input type="number" name="sort" value="0">
            </div>
            <div>
                <label>{{ admin_t('ui.status') }}</label>
                <select name="status">
                    <option value="1">{{ admin_t('ui.enabled') }}</option>
                    <option value="0">{{ admin_t('ui.not_enabled') }}</option>
                </select>
            </div>
        </div>
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($activityJsLang, JSON_UNESCAPED_UNICODE);
    var desk = @json($desk);
    var form = document.getElementById('activity-search');
    var countEl = document.getElementById('activity-count');
    var addBtn = document.getElementById('activity-add-btn');
    var module = desk === 'tasks' ? 'activity' : (desk === 'logs' ? 'task_logs' : (desk === 'signs' ? 'signs' : 'sign_milestones'));
    if (addBtn) {
        addBtn.style.display = (desk === 'tasks' || desk === 'milestones') ? '' : 'none';
        addBtn.textContent = desk === 'milestones' ? L.add_milestone : L.add_task;
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
            return '<div class="list-empty"><p>' + L.no_match_rows + '</p><p><button type="button" class="btn btn-muted btn-sm" id="activity-empty-reset">' + L.clear_filter + '</button></p></div>';
        }
        var copy = {
            tasks: [L.empty_tasks, L.add_task],
            logs: [L.empty_task_logs, ''],
            signs: [L.empty_signs, ''],
            milestones: [L.empty_milestones, L.add_milestone]
        }[desk] || [L.no_match_rows, ''];
        if (!copy[1]) {
            return '<div class="list-empty"><p>' + copy[0] + '</p></div>';
        }
        return '<div class="list-empty"><p>' + copy[0] + '</p><p><button type="button" class="btn btn-primary btn-sm" id="activity-empty-add">' + copy[1] + '</button></p></div>';
    }

    var cols = [];
    if (desk === 'tasks') {
        cols = [
            {title: AdminUi.t('name'), html: function (d) {
                return '<a class="entry-row-title js-edit" href="#">' + U.escape(d.name || AdminUi.t('not_filled')) + '</a>';
            }},
            {title: AdminUi.t('type'), width: 72, html: function (d) { return U.escape(d.type_label || ''); }},
            {title: L.action_key, width: 120, html: function (d) { return U.escape(d.action || ''); }},
            {title: AdminUi.t('points'), width: 64, html: function (d) { return U.escape(String(d.points == null ? 0 : d.points)); }},
            {title: L.goal, width: 64, html: function (d) { return U.escape(String(d.target == null ? 1 : d.target)); }},
            {title: AdminUi.t('status'), width: 80, html: function (d) {
                return String(d.status) === '1' ? U.status(true, AdminUi.t('enabled')) : U.status(false, AdminUi.t('disabled'));
            }},
            {title: AdminUi.t('actions'), cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-edit">' + AdminUi.t('edit') + '</a><a href="#" class="btn-link js-del">' + AdminUi.t('delete') + '</a>';
            }}
        ];
    } else if (desk === 'logs') {
        cols = [
            {title: L.tasks, html: function (d) { return U.escape(d.task_name || d.action || ''); }},
            {title: AdminUi.t('member'), width: 100, html: function (d) { return U.escape(d.member_name || String(d.member_id || '')); }},
            {title: L.progress, width: 80, html: function (d) { return U.escape(String(d.progress == null ? 0 : d.progress)); }},
            {title: AdminUi.t('points'), width: 64, html: function (d) { return U.escape(String(d.points == null ? 0 : d.points)); }},
            {title: AdminUi.t('date'), width: 96, html: function (d) { return U.escape(d.day_key || ''); }},
            {title: AdminUi.t('status'), width: 88, html: function (d) { return U.escape(d.status_label || ''); }},
            {title: AdminUi.t('actions'), cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-del">' + AdminUi.t('delete') + '</a>';
            }}
        ];
    } else if (desk === 'signs') {
        cols = [
            {title: AdminUi.t('member'), html: function (d) { return U.escape(d.member_name || String(d.member_id || '')); }},
            {title: AdminUi.t('date'), width: 96, html: function (d) { return U.escape(d.day_key || ''); }},
            {title: L.streak, width: 72, html: function (d) { return U.escape(String(d.days == null ? 0 : d.days)); }},
            {title: AdminUi.t('points'), width: 72, html: function (d) { return U.escape(String(d.points == null ? 0 : d.points)); }},
            {title: AdminUi.t('actions'), cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-del">' + AdminUi.t('delete') + '</a>';
            }}
        ];
    } else {
        cols = [
            {title: AdminUi.t('name'), html: function (d) {
                return '<a class="entry-row-title js-edit" href="#">' + U.escape(d.name || (L.streak + (d.days || '') + L.unit_day)) + '</a>';
            }},
            {title: L.days_col, width: 72, html: function (d) { return U.escape(String(d.days == null ? 0 : d.days)); }},
            {title: AdminUi.t('points'), width: 72, html: function (d) { return U.escape(String(d.points == null ? 0 : d.points)); }},
            {title: AdminUi.t('status'), width: 80, html: function (d) {
                return String(d.status) === '1' ? U.status(true, AdminUi.t('enabled')) : U.status(false, AdminUi.t('disabled'));
            }},
            {title: AdminUi.t('actions'), cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-edit">' + AdminUi.t('edit') + '</a><a href="#" class="btn-link js-del">' + AdminUi.t('delete') + '</a>';
            }}
        ];
    }

    var table = U.table({
        el: '#activity-table',
        countEl: countEl,
        url: '/admin/video/' + module + '/list',
        where: queryWhere(),
        emptyHtml: emptyHtml,
        onDraw: function (_wrap, list) {
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
            wide: true,
            title: mode === 'edit' ? (isMile ? L.edit_milestone : L.edit_task) : (isMile ? L.add_milestone : L.add_task),
            content: document.getElementById(isMile ? 'activity-mile-tpl' : 'activity-task-tpl').innerHTML,
            onOpen: function (body) {
                U.fillForm(body.querySelector('form'), isMile ? fillMile(mode, row) : fillTask(mode, row));
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (!data.name) { U.toast(L.please_fill_name, 'err'); return false; }
                if (!isMile && !data.action) { U.toast(L.please_fill_action, 'err'); return false; }
                if (mode !== 'edit') delete data.id; else data.id = row.id;
                return U.post('/admin/video/' + module + '/save', data).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return false; }
                    U.toast(mode === 'edit' ? L.saved : L.created, 'ok');
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
            if (!U.confirm(L.confirm_remove)) return;
            U.post('/admin/video/' + module + '/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
                table.refresh();
                U.toast(L.deleted, 'ok');
            });
        }
    });
})();
</script>
@endpush
