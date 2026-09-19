@extends('admin.layouts.inner')
@section('title', $title)

@php
    $queues = $queues ?? ['all' => 0, 'on' => 0, 'off' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
    $groupJsLang = [
        'sort' => admin_t('ui.sort'),
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
        'selected_groups' => admin_t('ui.selected_groups', ['n' => '__N__']),
        'op_fail' => admin_t('manga.op_fail'),
        'op_ok' => admin_t('manga.op_ok'),
        'unnamed' => admin_t('ui.unnamed'),
        'members' => admin_t('ui.members'),
        'add_group' => admin_t('ui.add_group'),
        'edit_group' => admin_t('ui.edit_group'),
        'col_group_name' => admin_t('ui.col_group_name'),
        'please_fill_name' => admin_t('ui.please_fill_name'),
        'please_select_groups' => admin_t('ui.please_select_groups'),
        'empty_groups' => admin_t('ui.empty_groups'),
        'empty_groups_hint' => admin_t('ui.empty_groups_hint'),
        'no_match_groups' => admin_t('ui.no_match_groups'),
        'confirm_batch_del_groups' => admin_t('ui.confirm_batch_del_groups'),
        'confirm_del_group' => admin_t('ui.confirm_del_group', ['name' => '__NAME__']),
        'confirm_del_group_people' => admin_t('ui.confirm_del_group_people', ['name' => '__NAME__', 'n' => '__N__']),
        'group_enabled' => admin_t('ui.group_enabled'),
        'group_disabled' => admin_t('ui.group_disabled'),
        'group_points_min' => admin_t('ui.group_points_min', ['n' => '__N__']),
        'group_no_threshold' => admin_t('ui.group_no_threshold'),
        'group_trysee' => admin_t('ui.group_trysee', ['n' => '__N__']),
        'group_day_free' => admin_t('ui.group_day_free', ['n' => '__N__']),
        'group_no_people' => admin_t('ui.group_no_people'),
        'group_people_n' => admin_t('ui.group_people_n', ['n' => '__N__']),
    ];
@endphp

@section('plain')
<div class="card card-panel group-index">
    <div class="card-header">
        <span>{{ admin_t('ui.member_groups') }} <em id="group-count"></em></span>
        <div>
            <a class="btn btn-muted btn-sm" href="/admin/video/members">{{ admin_t('ui.members') }}</a>
            <button type="button" class="btn btn-sm" id="group-add-btn">{{ admin_t('ui.add_group') }}</button>
        </div>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="group-search" onsubmit="return false;">
            <input type="hidden" name="status">
            <input type="text" name="name" placeholder="{{ admin_t('ui.ph_group') }}" autocomplete="off" aria-label="{{ admin_t('ui.member_groups') }}">
            <button type="button" class="btn btn-sm" id="group-search-btn">{{ admin_t('ui.search') }}</button>
            <button type="reset" class="btn btn-muted btn-sm" id="group-reset-btn">{{ admin_t('ui.reset') }}</button>
        </form>
        <div class="queue-chips" id="group-queues">
            <button type="button" class="chip" data-queue="">{{ admin_t('ui.all') }}@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="1">{{ admin_t('ui.enabled') }}@if($q('on') > 0)<em>{{ $q('on') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="0">{{ admin_t('ui.deactivated') }}@if($q('off') > 0)<em>{{ $q('off') }}</em>@endif</button>
        </div>
        <p class="muted recycle-lead">{{ admin_t('ui.groups_lead') }}</p>
        <div class="batch-bar" id="group-batch" hidden>
            <strong id="group-batch-count">{{ admin_t('ui.selected_groups', ['n' => 0]) }}</strong>
            <button type="button" class="btn btn-sm" id="group-batch-on">{{ admin_t('ui.enabled') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="group-batch-off">{{ admin_t('ui.disabled') }}</button>
            <button type="button" class="btn btn-danger btn-sm" id="group-batch-del">{{ admin_t('ui.delete') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="group-batch-clear">{{ admin_t('ui.clear_selection') }}</button>
        </div>
        <div id="group-table"></div>
    </div>
</div>
<template id="group-dialog-tpl">
    <form>
        <input type="hidden" name="id">
        <label>{{ admin_t('ui.label_name') }}</label>
        <input class="entry-title" type="text" name="name" placeholder="{{ admin_t('ui.ph_group_name') }}" autofocus>
        <p class="muted field-hint">{{ admin_t('ui.hint_group_name') }}</p>
        <div class="admin-dialog-grid">
            <div>
                <label>{{ admin_t('ui.label_points_min') }}</label>
                <input type="number" name="points_min" value="0" min="0">
            </div>
            <div>
                <label>{{ admin_t('ui.label_trysee') }}</label>
                <input type="number" name="trysee" value="0" min="0">
            </div>
        </div>
        <p class="muted field-hint">{{ admin_t('ui.hint_group_threshold') }}</p>
        <div class="admin-dialog-grid">
            <div>
                <label>{{ admin_t('ui.label_day_free') }}</label>
                <input type="number" name="day_free" value="0" min="0">
            </div>
            <div>
                <label>{{ admin_t('ui.sort') }}</label>
                <input type="number" name="sort" value="0">
            </div>
        </div>
        <p class="muted field-hint">{{ admin_t('ui.hint_day_free') }}</p>
        <label>{{ admin_t('ui.status') }}</label>
        <select name="status">
            <option value="1">{{ admin_t('ui.enabled') }}</option>
            <option value="0">{{ admin_t('ui.disabled') }}</option>
        </select>
        <details class="form-more">
            <summary>{{ admin_t('ui.more') }}</summary>
            <label>{{ admin_t('ui.label_need_login') }}</label>
            <select name="need_login">
                <option value="0">{{ admin_t('ui.no') }}</option>
                <option value="1">{{ admin_t('ui.yes') }}</option>
            </select>
            <p class="muted field-hint">{{ admin_t('ui.hint_need_login') }}</p>
        </details>
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($groupJsLang);
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
        var badge = String(d.status) === '1' ? '' : '<span class="badge badge-off">' + L.disabled + '</span>';
        var bits = [];
        var min = parseInt(d.points_min, 10) || 0;
        var trysee = parseInt(d.trysee, 10) || 0;
        var free = parseInt(d.day_free, 10) || 0;
        bits.push(min > 0 ? String(L.group_points_min || '').replace('__N__', String(min)) : L.group_no_threshold);
        if (trysee > 0) bits.push(String(L.group_trysee || '').replace('__N__', String(trysee)));
        if (free > 0) bits.push(String(L.group_day_free || '').replace('__N__', String(free)));
        return '<div class="entry-row-title-line"><a class="entry-row-title js-edit" href="#">' + U.escape(d.name || L.unnamed) + '</a> ' + badge + '</div>'
            + '<div class="entry-row-meta">' + U.escape(bits.join(' · ')) + '</div>';
    }
    function peopleHtml(d) {
        var n = parseInt(d.member_count, 10) || 0;
        if (n < 1) return '<span class="muted">' + L.group_no_people + '</span>';
        return '<a href="/admin/video/members?group_id=' + encodeURIComponent(d.id) + '">' + String(L.group_people_n || '').replace('__N__', String(n)) + '</a>';
    }

    var table = U.table({
        el: '#group-table',
        countEl: countEl,
        url: '/admin/video/groups/list',
        where: queryWhere(),
        pager: false,
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>' + L.no_match_groups + '</p><p><button type="button" class="btn btn-muted btn-sm" id="group-empty-reset">' + L.clear_filter + '</button></p></div>';
            }
            return '<div class="list-empty"><p>' + L.empty_groups + '</p><p class="muted">' + L.empty_groups_hint + '</p><p><button type="button" class="btn btn-primary btn-sm" id="group-empty-add">' + L.add_group + '</button></p></div>';
        },
        onDraw: function (_wrap, list) {
            var add = document.getElementById('group-empty-add');
            var reset = document.getElementById('group-empty-reset');
            if (add) add.addEventListener('click', function () { openDialog('add'); });
            if (reset) reset.addEventListener('click', function () { form.reset(); runSearch(); });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = String(L.selected_groups || '').replace('__N__', String(ids.length));
        },
        cols: [
            {check: true, width: 36},
            {title: L.col_group_name, html: nameHtml},
            {title: L.members, width: 88, html: peopleHtml},
            {key: 'sort', title: L.sort, width: 64},
            {title: L.status, width: 72, html: function (d) {
                return String(d.status) === '1' ? U.status(true, L.enabled) : U.status(false, L.disabled);
            }},
            {title: L.actions, cls: 'actions', html: function (d) {
                var html = String(d.status) === '1'
                    ? '<a href="#" class="btn-link js-off">' + L.disabled + '</a>'
                    : '<a href="#" class="btn-link js-on">' + L.enabled + '</a>';
                html += '<a href="#" class="btn-link js-edit">' + L.edit + '</a><a href="#" class="btn-link js-del">' + L.delete + '</a>';
                return html;
            }}
        ]
    });
    markChips();

    function openDialog(mode, row) {
        row = row || {};
        U.dialog({
            wide: true,
            title: mode === 'edit' ? L.edit_group : L.add_group,
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
                if (!data.name) { U.toast(L.please_fill_name, 'err'); return false; }
                if (mode !== 'edit') delete data.id; else data.id = row.id;
                return U.post('/admin/video/groups/save', data).then(function (res) {
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
        if (!ids.length) { U.toast(L.please_select_groups, 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/groups/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.op_fail, 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || L.op_ok, 'ok');
        });
    }
    function setStatus(row, status) {
        U.post('/admin/video/groups/save', {id: row.id, status: status}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
            table.refresh();
            U.toast(status === 1 ? L.group_enabled : L.group_disabled, 'ok');
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
    U.on('#group-batch-del', 'click', function () { batch('delete', '', L.confirm_batch_del_groups); });
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
                ? String(L.confirm_del_group_people || '').replace('__NAME__', row.name || '').replace('__N__', String(n))
                : String(L.confirm_del_group || '').replace('__NAME__', row.name || '');
            if (!U.confirm(msg)) return;
            U.post('/admin/video/groups/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
                table.refresh();
                U.toast(L.deleted, 'ok');
            });
        }
    });
})();
</script>
@endpush
