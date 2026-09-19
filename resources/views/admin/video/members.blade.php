@extends('admin.layouts.inner')
@section('title', $title)

@php
    $groups = $groups ?? [];
    $queues = $queues ?? ['all' => 0, 'off' => 0, 'none' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
    $memberJsLang = [
        'status' => admin_t('ui.status'),
        'actions' => admin_t('ui.actions'),
        'edit' => admin_t('ui.edit'),
        'delete' => admin_t('ui.delete'),
        'enabled' => admin_t('ui.enabled'),
        'disabled' => admin_t('ui.disabled'),
        'fail' => admin_t('ui.fail'),
        'saved' => admin_t('ui.saved'),
        'created' => admin_t('ui.created'),
        'deleted' => admin_t('ui.deleted'),
        'clear_filter' => admin_t('ui.clear_filter'),
        'selected_people' => admin_t('ui.selected_people', ['n' => '__N__']),
        'op_fail' => admin_t('manga.op_fail'),
        'op_ok' => admin_t('manga.op_ok'),
        'unnamed' => admin_t('ui.unnamed'),
        'ungrouped' => admin_t('ui.ungrouped'),
        'add_member' => admin_t('ui.add_member'),
        'edit_member' => admin_t('ui.edit_member'),
        'col_member' => admin_t('ui.col_member'),
        'col_group' => admin_t('ui.col_group'),
        'col_points' => admin_t('ui.col_points'),
        'status_normal' => admin_t('ui.status_normal'),
        'joined_at' => admin_t('ui.joined_at', ['time' => '__TIME__']),
        'plog_link' => admin_t('ui.plog_link'),
        'favor_link' => admin_t('ui.favor_link'),
        'pm_link' => admin_t('ui.pm_link'),
        'notify_link' => admin_t('ui.notify_link'),
        'empty_members' => admin_t('ui.empty_members'),
        'empty_members_hint' => admin_t('ui.empty_members_hint'),
        'no_match_members' => admin_t('ui.no_match_members'),
        'please_fill_nickname' => admin_t('ui.please_fill_nickname'),
        'please_fill_email' => admin_t('ui.please_fill_email'),
        'please_fill_password' => admin_t('ui.please_fill_password'),
        'please_select_members' => admin_t('ui.please_select_members'),
        'please_pick_group' => admin_t('ui.please_pick_group'),
        'please_points_nonzero' => admin_t('ui.please_points_nonzero'),
        'confirm_adjust_points' => admin_t('ui.confirm_adjust_points', ['n' => '__N__']),
        'confirm_batch_del_members' => admin_t('ui.confirm_batch_del_members'),
        'confirm_disable_member' => admin_t('ui.confirm_disable_member', ['name' => '__NAME__']),
        'confirm_del_member' => admin_t('ui.confirm_del_member', ['name' => '__NAME__']),
        'member_enabled' => admin_t('ui.member_enabled'),
        'member_disabled' => admin_t('ui.member_disabled'),
    ];
@endphp

@section('plain')
<div class="card card-panel member-index list-desk">
    <div class="card-header">
        <span>{{ admin_t('ui.members') }} <em id="member-count"></em></span>
        <div>
            <a class="btn btn-muted btn-sm" href="/admin/video/groups">{{ admin_t('ui.member_groups') }}</a>
            <button type="button" class="btn btn-sm" id="member-add-btn">{{ admin_t('ui.add_member') }}</button>
        </div>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="member-search" onsubmit="return false;">
            <input type="hidden" name="group_id">
            <input type="hidden" name="status">
            <input type="search" name="q" placeholder="{{ admin_t('ui.ph_member') }}" autocomplete="off" aria-label="{{ admin_t('ui.members') }}">
            <button type="button" class="btn btn-sm" id="member-search-btn">{{ admin_t('ui.search') }}</button>
            <button type="reset" class="btn btn-muted btn-sm" id="member-reset-btn">{{ admin_t('ui.reset') }}</button>
        </form>
        <div class="queue-chips" id="member-queues">
            <button type="button" class="chip" data-queue="">{{ admin_t('ui.all') }}@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            @foreach($groups as $group)
                <button type="button" class="chip" data-queue="group_id" data-value="{{ $group['id'] }}">{{ $group['name'] }}@if(($group['count'] ?? 0) > 0)<em>{{ $group['count'] }}</em>@endif</button>
            @endforeach
            <button type="button" class="chip" data-queue="group_id" data-value="0">{{ admin_t('ui.ungrouped') }}@if($q('none') > 0)<em>{{ $q('none') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="0">{{ admin_t('ui.deactivated') }}@if($q('off') > 0)<em>{{ $q('off') }}</em>@endif</button>
        </div>
        <p class="muted recycle-lead">{{ admin_t('ui.members_lead') }}</p>
        <div class="batch-bar" id="member-batch" hidden>
            <strong id="member-batch-count">{{ admin_t('ui.selected_people', ['n' => 0]) }}</strong>
            <button type="button" class="btn btn-sm" id="member-batch-on">{{ admin_t('ui.enabled') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="member-batch-off">{{ admin_t('ui.disabled') }}</button>
            <select id="member-batch-group" class="batch-select" aria-label="{{ admin_t('ui.move_group') }}">
                <option value="">{{ admin_t('ui.move_group') }}</option>
                <option value="0">{{ admin_t('ui.ungrouped') }}</option>
                @foreach($groups as $group)
                    <option value="{{ $group['id'] }}">{{ $group['name'] }}</option>
                @endforeach
            </select>
            <button type="button" class="btn btn-muted btn-sm" id="member-batch-move">{{ admin_t('ui.move') }}</button>
            <input type="number" id="member-batch-points" class="batch-points" placeholder="{{ admin_t('ui.points_delta') }}" aria-label="{{ admin_t('ui.adjust_points') }}">
            <button type="button" class="btn btn-muted btn-sm" id="member-batch-points-go">{{ admin_t('ui.adjust_points') }}</button>
            <button type="button" class="btn btn-danger btn-sm" id="member-batch-del">{{ admin_t('ui.delete') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="member-batch-clear">{{ admin_t('ui.clear_selection') }}</button>
        </div>
        <div id="member-table"></div>
    </div>
</div>
<template id="member-dialog-tpl">
    <form>
        <input type="hidden" name="id">
        <label>{{ admin_t('ui.label_nickname') }}</label>
        <input type="text" name="name" placeholder="{{ admin_t('ui.ph_member_name') }}">
        <p class="muted field-hint">{{ admin_t('ui.hint_member_name') }}</p>
        <label>{{ admin_t('ui.label_login_email') }}</label>
        <input type="email" name="email" placeholder="{{ admin_t('ui.ph_member_email') }}">
        <p class="muted field-hint">{{ admin_t('ui.hint_member_email') }}</p>
        <label>{{ admin_t('ui.label_password') }}</label>
        <input type="password" name="password" autocomplete="new-password" placeholder="{{ admin_t('ui.ph_member_password') }}">
        <label>{{ admin_t('ui.label_member_group') }}</label>
        <select name="group_id">
            <option value="0">{{ admin_t('ui.ungrouped') }}</option>
            @foreach($groups as $group)
                <option value="{{ $group['id'] }}">{{ $group['name'] }}{{ (int) ($group['status'] ?? 1) === 1 ? '' : admin_t('ui.group_disabled_suffix') }}</option>
            @endforeach
        </select>
        <p class="muted field-hint">{{ admin_t('ui.hint_member_group') }}</p>
        <label>{{ admin_t('ui.label_points') }}</label>
        <input type="number" name="points" value="0">
        <p class="muted field-hint">{{ admin_t('ui.hint_member_points') }}</p>
        <label>{{ admin_t('ui.status') }}</label>
        <select name="status">
            <option value="1">{{ admin_t('ui.status_normal') }}</option>
            <option value="0">{{ admin_t('ui.disabled') }}</option>
        </select>
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($memberJsLang);
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
        var badge = String(d.status) === '1' ? '' : '<span class="badge badge-off">' + L.disabled + '</span>';
        var meta = U.escape(d.email || '');
        if (d.joined_text) meta += (meta ? ' · ' : '') + String(L.joined_at || '').replace('__TIME__', U.escape(d.joined_text));
        meta += (meta ? ' · ' : '') + '#' + U.escape(d.id);
        return '<div class="entry-row-title-line"><a class="entry-row-title js-edit" href="#">' + U.escape(d.name || L.unnamed) + '</a> ' + badge + '</div>'
            + '<div class="entry-row-meta">' + meta + '</div>';
    }

    var table = U.table({
        el: '#member-table',
        queueKeys: QUEUE_KEYS,
        countEl: countEl,
        url: '/admin/video/members/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>' + L.no_match_members + '</p><p><button type="button" class="btn btn-muted btn-sm" id="member-empty-reset">' + L.clear_filter + '</button></p></div>';
            }
            return '<div class="list-empty"><p>' + L.empty_members + '</p><p class="muted">' + L.empty_members_hint + '</p><p><button type="button" class="btn btn-primary btn-sm" id="member-empty-add">' + L.add_member + '</button></p></div>';
        },
        onDraw: function (_wrap, list) {
            var add = document.getElementById('member-empty-add');
            var reset = document.getElementById('member-empty-reset');
            if (add) add.addEventListener('click', function () { openDialog('add'); });
            if (reset) reset.addEventListener('click', function () { form.reset(); runSearch(); });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = String(L.selected_people || '').replace('__N__', String(ids.length));
        },
        cols: [
            {check: true, width: 36},
            {title: L.col_member, html: nameHtml},
            {title: L.col_group, width: 120, html: function (d) { return U.escape(d.group_name || L.ungrouped); }},
            {key: 'points', title: L.col_points, width: 72},
            {title: L.status, width: 72, html: function (d) {
                return String(d.status) === '1' ? U.status(true, L.status_normal) : U.status(false, L.disabled);
            }},
            {title: L.actions, cls: 'actions', html: function (d) {
                var html = String(d.status) === '1'
                    ? '<a href="#" class="btn-link js-off">' + L.disabled + '</a>'
                    : '<a href="#" class="btn-link js-on">' + L.enabled + '</a>';
                html += '<a href="#" class="btn-link js-edit">' + L.edit + '</a>';
                html += '<a href="/admin/video/plogs?member_id=' + encodeURIComponent(d.id) + '">' + L.plog_link + '</a>';
                html += '<a class="btn-link" href="/admin/video/favorites?member_id=' + encodeURIComponent(d.id) + '">' + L.favor_link + '</a>';
                html += '<a class="btn-link" href="/admin/video/pms?to=' + encodeURIComponent(d.id) + '">' + L.pm_link + '</a>';
                html += '<a class="btn-link" href="/admin/video/notifies?member=' + encodeURIComponent(d.id) + '">' + L.notify_link + '</a>';
                html += '<a href="#" class="btn-link js-del">' + L.delete + '</a>';
                return html;
            }}
        ]
    });
    markChips();

    function openDialog(mode, row) {
        row = row || {};
        U.dialog({
            title: mode === 'edit' ? L.edit_member : L.add_member,
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
                if (!data.name) { U.toast(L.please_fill_nickname, 'err'); return false; }
                if (!data.email) { U.toast(L.please_fill_email, 'err'); return false; }
                if (mode !== 'edit' && !data.password) { U.toast(L.please_fill_password, 'err'); return false; }
                if (mode !== 'edit') delete data.id; else data.id = row.id;
                if (!data.password) delete data.password;
                return U.post('/admin/video/members/save', data).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return false; }
                    U.toast(mode === 'edit' ? L.saved : L.created, 'ok');
                    table.refresh();
                });
            }
        });
    }

    function selectedIds() { return table.selectedIds(); }
    function batch(action, value, confirmText) {
        var ids = selectedIds();
        if (!ids.length) { U.toast(L.please_select_members, 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/members/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.op_fail, 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || L.op_ok, 'ok');
        });
    }
    function setStatus(row, status) {
        U.post('/admin/video/members/save', {id: row.id, status: status}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
            table.refresh();
            U.toast(status === 1 ? L.member_enabled : L.member_disabled, 'ok');
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
        if (sel.value === '') { U.toast(L.please_pick_group, 'err'); return; }
        batch('group', sel.value);
    });
    U.on('#member-batch-points-go', 'click', function () {
        var val = parseInt(document.getElementById('member-batch-points').value, 10);
        if (!val) { U.toast(L.please_points_nonzero, 'err'); return; }
        batch('points', val, String(L.confirm_adjust_points || '').replace('__N__', String(val)));
    });
    U.on('#member-batch-del', 'click', function () { batch('delete', '', L.confirm_batch_del_members); });
    U.on('#member-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#member-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        if (a.getAttribute('href') && (a.getAttribute('href').indexOf('/admin/video/plogs') === 0 || a.getAttribute('href').indexOf('/admin/video/favorites') === 0 || a.getAttribute('href').indexOf('/admin/video/pms') === 0 || a.getAttribute('href').indexOf('/admin/video/notifies') === 0)) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-edit')) openDialog('edit', row);
        if (a.classList.contains('js-on')) setStatus(row, 1);
        if (a.classList.contains('js-off')) {
            if (!U.confirm(String(L.confirm_disable_member || '').replace('__NAME__', row.name || ''))) return;
            setStatus(row, 0);
        }
        if (a.classList.contains('js-del')) {
            if (!U.confirm(String(L.confirm_del_member || '').replace('__NAME__', row.name || row.email || ''))) return;
            U.post('/admin/video/members/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
                table.refresh();
                U.toast(L.deleted, 'ok');
            });
        }
    });
})();
</script>
@endpush
