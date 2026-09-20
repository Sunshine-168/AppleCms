@extends('admin.layouts.inner')
@section('title', admin_t('page.dict'))

@php
    $ui = $ui ?? [];
    $groups = $groups ?? [];
    $queues = $queues ?? ['all' => 0, 'off' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
    $valueTypes = $ui['value_types'] ?? \App\Services\Admin\System\SysDictService::valueTypeLabels();
    $dictJsLang = [
        'dict_group_hint' => admin_t('ui.dict_group_hint'),
        'empty_type_opts' => admin_t('ui.empty_type_opts'),
        'click_add' => admin_t('ui.click_add'),
        'no_match_opts' => admin_t('ui.no_match_opts'),
        'clear_filter' => admin_t('ui.clear_filter'),
        'empty_opts' => admin_t('ui.empty_opts'),
        'click_add_type' => admin_t('ui.click_add_type'),
        'display_name' => admin_t('ui.display_name'),
        'value_type' => admin_t('ui.value_type'),
        'dict_value' => admin_t('ui.dict_value'),
        'enum_limit' => admin_t('ui.enum_limit'),
        'remark' => admin_t('ui.remark'),
        'vt_string' => admin_t('ui.vt_string'),
        'pick_enum' => admin_t('ui.pick_enum'),
        'confirm_save' => admin_t('ui.confirm_save'),
        'please_fill_type' => admin_t('ui.please_fill_type'),
        'please_fill_key' => admin_t('ui.please_fill_key'),
        'dict_save_fail' => admin_t('ui.dict_save_fail'),
        'saved' => admin_t('ui.saved'),
        'state_fail' => admin_t('ui.state_fail'),
        'confirm_del_dict' => admin_t('ui.confirm_del_dict'),
        'delete_fail' => admin_t('ui.delete_fail'),
        'deleted' => admin_t('ui.deleted'),
        'dict_add' => admin_t('ui.dict_add'),
        'dict_edit' => admin_t('ui.dict_edit'),
    ];
@endphp

@section('plain')
<div class="card card-panel dict-index" id="dict-index">
    <div class="card-header">
        <span>{{ $ui['title'] ?? admin_t('page.dict') }} <em id="dict-count"></em></span>
        <button type="button" class="btn btn-sm" id="dict-add-btn">{{ $ui['compose'] ?? admin_t('ui.add_action') }}</button>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">{{ $ui['lead'] ?? '' }}</p>

        <div class="queue-chips dict-groups" id="dict-groups">
            <button type="button" class="chip active" data-queue="">{{ admin_t('ui.all') }}@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            @foreach($groups as $group)
                <button type="button" class="chip" data-queue="dict_type" data-value="{{ $group['dict_type'] }}">{{ $group['label'] }}@if(($group['cnt'] ?? 0) > 0)<em>{{ $group['cnt'] }}</em>@endif</button>
            @endforeach
            <button type="button" class="chip" data-queue="status" data-value="1">{{ admin_t('ui.already_off') }}@if($q('off') > 0)<em>{{ $q('off') }}</em>@endif</button>
        </div>
        <p class="muted field-hint" id="dict-group-hint">{{ admin_t('ui.dict_group_hint') }}</p>

        <form class="filter-bar dict-find" id="dict-search" onsubmit="return false;">
            <input type="hidden" name="dict_type">
            <input type="hidden" name="status">
            <input type="search" name="q" placeholder="{{ $ui['find'] ?? admin_t('ui.dict_find') }}" autocomplete="off" aria-label="{{ admin_t('ui.dict_search_aria') }}">
            <button type="button" class="btn btn-sm" id="dict-search-btn">{{ admin_t('ui.search') }}</button>
            <button type="reset" class="btn btn-muted btn-sm" id="dict-reset-btn">{{ admin_t('ui.reset') }}</button>
        </form>
        <div id="dict-table"></div>
    </div>
</div>
<datalist id="dict-type-list">
    @foreach($groups as $group)
        <option value="{{ $group['dict_type'] }}">{{ $group['label'] }}</option>
    @endforeach
</datalist>
<template id="dict-dialog-tpl">
    <form class="dict-form" autocomplete="off">
        <input type="hidden" name="id">
        <section class="dict-form-sec">
            <h3>{{ admin_t('ui.dict_basic') }}</h3>
            <div class="dict-form-grid">
                <div>
                    <label for="dict-f-type">{{ admin_t('ui.dict_type_label') }}</label>
                    <input id="dict-f-type" type="text" name="dict_type" list="dict-type-list" placeholder="{{ admin_t('ui.ph_dict_type') }}">
                </div>
                <div>
                    <label for="dict-f-key">{{ admin_t('ui.dict_key_label') }}</label>
                    <input id="dict-f-key" type="text" name="dict_key" placeholder="{{ admin_t('ui.ph_dict_key') }}">
                </div>
                <div>
                    <label for="dict-f-vtype">{{ admin_t('ui.value_type') }}</label>
                    <select id="dict-f-vtype" name="value_type" class="dict-value-type">
                        @foreach($valueTypes as $code => $label)
                            <option value="{{ $code }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="dict-f-label">{{ admin_t('ui.display_name') }}</label>
                    <input id="dict-f-label" type="text" name="label" placeholder="{{ admin_t('ui.ph_display_name') }}">
                </div>
                <div>
                    <label for="dict-f-sort">{{ admin_t('ui.sort') }}</label>
                    <input id="dict-f-sort" type="number" name="sort" value="0">
                </div>
                <div>
                    <label for="dict-f-status">{{ admin_t('ui.status') }}</label>
                    <select id="dict-f-status" name="status">
                        <option value="0">{{ admin_t('ui.enabled') }}</option>
                        <option value="1">{{ admin_t('ui.disabled') }}</option>
                    </select>
                </div>
            </div>
        </section>
        <section class="dict-form-sec">
            <h3>{{ admin_t('ui.dict_value_cfg') }}</h3>
            <div class="dict-value-box" data-type="0">
                <label>{{ admin_t('ui.dict_value') }}</label>
                <input type="text" name="dict_value" class="dict-v dict-v-0" placeholder="{{ admin_t('ui.ph_string_val') }}">
            </div>
            <div class="dict-value-box" data-type="1" hidden>
                <label>{{ admin_t('ui.dict_value') }}</label>
                <input type="number" name="dict_value_int" class="dict-v dict-v-1" placeholder="{{ admin_t('ui.ph_int_val') }}" step="1">
            </div>
            <div class="dict-value-box" data-type="2" hidden>
                <label>{{ admin_t('ui.dict_value') }}</label>
                <input type="text" name="dict_value_float" class="dict-v dict-v-2" placeholder="{{ admin_t('ui.ph_float_val') }}">
            </div>
            <div class="dict-value-box" data-type="3" hidden>
                <label>{{ admin_t('ui.dict_value') }}</label>
                <textarea name="dict_value_json_obj" class="dict-v dict-v-3" rows="6" placeholder='{"name":"example"}'></textarea>
                <p class="muted field-hint">{{ admin_t('ui.json_obj_hint') }}</p>
            </div>
            <div class="dict-value-box" data-type="4" hidden>
                <label>{{ admin_t('ui.dict_value') }}</label>
                <textarea name="dict_value_json_arr" class="dict-v dict-v-4" rows="6" placeholder='["a","b"]'></textarea>
                <p class="muted field-hint">{{ admin_t('ui.json_arr_hint') }}</p>
            </div>
            <div class="dict-value-box" data-type="5" hidden>
                <label>{{ admin_t('ui.enum_limit') }}</label>
                <textarea name="enum_limit" class="dict-enum-limit" rows="4" placeholder='["on","off"]'></textarea>
                <p class="muted field-hint">{{ admin_t('ui.enum_limit_hint') }}</p>
                <label>{{ admin_t('ui.dict_value') }}</label>
                <select name="dict_value_enum" class="dict-v dict-v-5"></select>
            </div>
            <div class="dict-value-box" data-type="6" hidden>
                <label>{{ admin_t('ui.dict_value') }}</label>
                <textarea name="dict_value_text" class="dict-v dict-v-6" rows="8" placeholder="{{ admin_t('ui.ph_html_val') }}"></textarea>
                <p class="muted field-hint">{{ admin_t('ui.html_val_hint') }}</p>
            </div>
        </section>
        <section class="dict-form-sec">
            <h3>{{ admin_t('ui.remark') }}</h3>
            <label for="dict-f-remark">{{ admin_t('ui.remark_info') }}</label>
            <textarea id="dict-f-remark" name="remark" rows="3" maxlength="200" placeholder="{{ admin_t('ui.ph_dict_remark') }}"></textarea>
            <p class="muted field-hint dict-remark-count">0 / 200</p>
        </section>
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var form = document.getElementById('dict-search');
    var countEl = document.getElementById('dict-count');
    var groupsEl = document.getElementById('dict-groups');
    var typeList = document.getElementById('dict-type-list');
    var hintEl = document.getElementById('dict-group-hint');
    var GROUPS = @json($groups, JSON_UNESCAPED_UNICODE);
    var UI = @json($ui, JSON_UNESCAPED_UNICODE);
    var L = @json($dictJsLang, JSON_UNESCAPED_UNICODE);

    function groupById(id) {
        id = String(id || '');
        for (var i = 0; i < GROUPS.length; i++) if (GROUPS[i].dict_type === id) return GROUPS[i];
        return null;
    }
    function cleanWhere(data) {
        var out = {};
        Object.keys(data || {}).forEach(function (k) { if (data[k] !== '') out[k] = data[k]; });
        return out;
    }
    function queryWhere() {
        var where = Object.assign({limit: form.dict_type.value ? 100 : 20}, cleanWhere(U.formData(form)));
        return where;
    }
    function isFiltered(where) {
        return Object.keys(where || {}).some(function (k) { return k !== 'limit' && where[k] !== ''; });
    }
    function markChips() {
        var type = form.dict_type.value;
        var status = form.status.value;
        U.qa('#dict-groups .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = false;
            if (key === '' && type === '' && status === '') on = true;
            else if (key === 'status' && status === val && type === '') on = true;
            else if (key === 'dict_type' && status === '' && type === val) on = true;
            chip.classList.toggle('active', on);
        });
    }
    function showGroupMeta() {
        var g = groupById(form.dict_type.value);
        if (hintEl) hintEl.textContent = g ? (g.hint || '') : (L.dict_group_hint || '');
    }
    function applyQueue(key, value) {
        if (key === 'status') {
            form.dict_type.value = '';
            form.status.value = value || '';
        } else if (key === 'dict_type') {
            form.status.value = '';
            form.dict_type.value = value || '';
        } else {
            form.dict_type.value = '';
            form.status.value = '';
        }
        showGroupMeta();
        runSearch();
    }
    function renderGroups(queues) {
        queues = queues || {};
        var html = '<button type="button" class="chip" data-queue="">' + U.escape(AdminUi.t('all')) + (queues.all > 0 ? '<em>' + queues.all + '</em>' : '') + '</button>';
        GROUPS.forEach(function (t) {
            html += '<button type="button" class="chip" data-queue="dict_type" data-value="' + U.escape(t.dict_type || '') + '">' + U.escape(t.label || t.dict_type || '');
            if (t.cnt > 0) html += '<em>' + t.cnt + '</em>';
            html += '</button>';
        });
        html += '<button type="button" class="chip" data-queue="status" data-value="1">' + U.escape(AdminUi.t('already_off')) + (queues.off > 0 ? '<em>' + queues.off + '</em>' : '') + '</button>';
        groupsEl.innerHTML = html;
        if (typeList) {
            typeList.innerHTML = GROUPS.map(function (t) {
                return '<option value="' + U.escape(t.dict_type || '') + '">' + U.escape(t.label || '') + '</option>';
            }).join('');
        }
        markChips();
    }
    function runSearch() {
        table.reload(queryWhere());
        markChips();
        showGroupMeta();
    }
    function parseEnumLimit(text) {
        text = String(text || '').trim();
        if (!text) return [];
        try {
            var parsed = JSON.parse(text);
            return Array.isArray(parsed) ? parsed.map(function (x) { return String(x); }) : [];
        } catch (e) {
            return [];
        }
    }
    function fillEnumSelect(select, options, current) {
        var html = '<option value="">' + U.escape(L.pick_enum || '') + '</option>';
        options.forEach(function (item) {
            html += '<option value="' + U.escape(item) + '"' + (String(item) === String(current || '') ? ' selected' : '') + '>' + U.escape(item) + '</option>';
        });
        select.innerHTML = html;
        if (current) select.value = String(current);
    }
    function showValueType(root, valueType) {
        var t = String(valueType == null ? '0' : valueType);
        U.qa('.dict-value-box', root).forEach(function (box) {
            box.hidden = box.getAttribute('data-type') !== t;
        });
    }
    function currentValue(root, vt) {
        if (vt === '1') return (root.querySelector('.dict-v-1').value || '').trim();
        if (vt === '2') return (root.querySelector('.dict-v-2').value || '').trim();
        if (vt === '3') return (root.querySelector('.dict-v-3').value || '').trim();
        if (vt === '4') return (root.querySelector('.dict-v-4').value || '').trim();
        if (vt === '5') return (root.querySelector('.dict-v-5').value || '').trim();
        if (vt === '6') return (root.querySelector('.dict-v-6').value || '').trim();
        return (root.querySelector('.dict-v-0').value || '').trim();
    }
    function setCurrentValue(root, vt, value) {
        value = value == null ? '' : String(value);
        if (vt === '1') root.querySelector('.dict-v-1').value = value;
        else if (vt === '2') root.querySelector('.dict-v-2').value = value;
        else if (vt === '3') root.querySelector('.dict-v-3').value = value || '{}';
        else if (vt === '4') root.querySelector('.dict-v-4').value = value || '[]';
        else if (vt === '5') fillEnumSelect(root.querySelector('.dict-v-5'), parseEnumLimit(root.querySelector('.dict-enum-limit').value), value);
        else if (vt === '6') root.querySelector('.dict-v-6').value = value;
        else root.querySelector('.dict-v-0').value = value;
    }
    function prettyJson(value, fallback) {
        if (value == null || value === '') return fallback || '';
        if (typeof value === 'object') {
            try { return JSON.stringify(value, null, 2); } catch (e) { return fallback || ''; }
        }
        var text = String(value);
        try { return JSON.stringify(JSON.parse(text), null, 2); } catch (e) { return text; }
    }
    function bindForm(root, data) {
        data = data || {};
        var vt = data.value_type != null ? String(data.value_type) : '0';
        U.fillForm(root.querySelector('form'), {
            id: data.id || '',
            dict_type: data.dict_type || form.dict_type.value || '',
            dict_key: data.dict_key || '',
            label: data.label || '',
            value_type: vt,
            sort: data.sort || 0,
            status: data.status == 1 ? '1' : '0',
            remark: data.remark || ''
        });
        var enumText = '';
        if (data.enum_limit != null && data.enum_limit !== '') {
            enumText = prettyJson(data.enum_limit, '');
        }
        root.querySelector('.dict-enum-limit').value = enumText;
        setCurrentValue(root, vt, data.dict_value);
        showValueType(root, vt);
        var remark = root.querySelector('[name=remark]');
        var count = root.querySelector('.dict-remark-count');
        function syncCount() { if (count) count.textContent = String((remark.value || '').length) + ' / 200'; }
        remark.addEventListener('input', syncCount);
        syncCount();
        root.querySelector('.dict-value-type').addEventListener('change', function () {
            var next = this.value;
            showValueType(root, next);
            if (next === '3' && !root.querySelector('.dict-v-3').value) root.querySelector('.dict-v-3').value = '{}';
            if (next === '4' && !root.querySelector('.dict-v-4').value) root.querySelector('.dict-v-4').value = '[]';
            if (next === '5') fillEnumSelect(root.querySelector('.dict-v-5'), parseEnumLimit(root.querySelector('.dict-enum-limit').value), root.querySelector('.dict-v-5').value);
        });
        root.querySelector('.dict-enum-limit').addEventListener('input', function () {
            fillEnumSelect(root.querySelector('.dict-v-5'), parseEnumLimit(this.value), root.querySelector('.dict-v-5').value);
        });
    }
    function collectPayload(root) {
        var payload = U.formData(root.querySelector('form'));
        var vt = String(payload.value_type || '0');
        payload.dict_value = currentValue(root, vt);
        payload.enum_limit = vt === '5' ? (root.querySelector('.dict-enum-limit').value || '').trim() : '';
        payload.value_type = vt;
        if (!payload.id) delete payload.id;
        return payload;
    }
    function typeTag(d) {
        return '<span class="dict-type-tag">' + U.escape(d.value_type_label || L.vt_string || '') + '</span>';
    }

    var table = U.table({
        el: '#dict-table',
        countEl: countEl,
        url: '/admin/system/dicts/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                if (where.dict_type) {
                    return '<div class="list-empty"><p>' + U.escape(L.empty_type_opts) + '</p><p class="muted">' + U.escape(L.click_add) + '</p></div>';
                }
                return '<div class="list-empty"><p>' + U.escape(L.no_match_opts) + '</p><p><button type="button" class="btn btn-muted btn-sm" id="dict-empty-reset">' + U.escape(L.clear_filter) + '</button></p></div>';
            }
            return '<div class="list-empty"><p>' + U.escape(L.empty_opts) + '</p><p class="muted">' + U.escape(L.click_add_type) + '</p></div>';
        },
        onDraw: function (_wrap, list, parsed) {
            if (parsed && parsed.groups && parsed.groups.length) GROUPS = parsed.groups;
            renderGroups(parsed && parsed.queues ? parsed.queues : {});
            var reset = document.getElementById('dict-empty-reset');
            if (reset) reset.addEventListener('click', function () { form.reset(); form.dict_type.value = ''; form.status.value = ''; runSearch(); });
            U.qa('#dict-table tr[data-idx]').forEach(function (tr) {
                var row = (table.rows() || [])[tr.getAttribute('data-idx')];
                if (row && !row.is_on) tr.classList.add('is-off');
            });
            showGroupMeta();
        },
        cols: [
            {title: AdminUi.t('type'), width: 120, html: function (d) {
                var g = groupById(d.dict_type || '');
                return U.escape((g && g.label) ? g.label : (d.dict_type || ''));
            }},
            {title: 'Key', width: 120, html: function (d) { return '<code>' + U.escape(d.dict_key || '') + '</code>'; }},
            {title: L.display_name, html: function (d) { return U.escape(d.title || ''); }},
            {title: L.value_type, width: 100, html: typeTag},
            {title: L.dict_value, html: function (d) {
                var text = d.value_preview || '';
                return text ? U.escape(text) : '<span class="muted">-</span>';
            }},
            {title: L.enum_limit, html: function (d) {
                return d.enum_preview ? U.escape(d.enum_preview) : '<span class="muted">-</span>';
            }},
            {title: AdminUi.t('status'), width: 80, html: function (d) {
                return '<button type="button" class="dict-switch js-state' + (d.is_on ? ' is-on' : '') + '" title="' + U.escape(d.is_on ? AdminUi.t('enabled') : AdminUi.t('disabled')) + '"></button>';
            }},
            {title: L.remark, html: function (d) { return d.remark ? U.escape(d.remark) : '<span class="muted">-</span>'; }},
            {title: AdminUi.t('sort'), width: 60, html: function (d) { return U.escape(d.sort == null ? '0' : d.sort); }},
            {title: AdminUi.t('actions'), cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-edit">' + AdminUi.t('edit') + '</a><a href="#" class="btn-link js-del">' + AdminUi.t('delete') + '</a>';
            }}
        ]
    });
    markChips();
    showGroupMeta();

    function openForm(data) {
        data = data || {};
        U.dialog({
            title: data.id ? (UI.edit || L.dict_edit) : (UI.add || L.dict_add),
            wide: true,
            okText: L.confirm_save,
            content: document.getElementById('dict-dialog-tpl').innerHTML,
            onOpen: function (body) { bindForm(body, data); },
            onSave: function (body) {
                var payload = collectPayload(body);
                if (!payload.dict_type) { U.toast(L.please_fill_type, 'err'); return false; }
                if (!payload.dict_key) { U.toast(L.please_fill_key, 'err'); return false; }
                var url = payload.id ? '/admin/system/dicts/update' : '/admin/system/dicts/add';
                return U.post(url, payload).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || L.dict_save_fail, 'err'); return false; }
                    U.toast((res && res.msg) || L.saved, 'ok');
                    table.refresh();
                });
            }
        });
    }

    U.on('#dict-add-btn', 'click', function () {
        openForm({ dict_type: form.dict_type.value || '', value_type: 0, sort: 0, status: 0 });
    });
    U.on('#dict-search-btn', 'click', runSearch);
    U.on('#dict-reset-btn', 'click', function () { setTimeout(function () { form.dict_type.value = ''; form.status.value = ''; runSearch(); }, 0); });
    U.on('#dict-groups', 'click', function (e) {
        var chip = e.target.closest('.chip');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#dict-table', 'click', function (e) {
        var tr = e.target.closest('tr');
        if (!tr) return;
        var row = (table.rows() || [])[tr.getAttribute('data-idx')];
        if (!row) return;
        if (e.target.closest('.js-state')) {
            e.preventDefault();
            var next = row.is_on ? 1 : 0;
            U.post('/admin/system/dicts/state', {id: row.id, status: next}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.state_fail, 'err'); return; }
                table.refresh();
                U.toast(next === 1 ? AdminUi.t('already_off') : AdminUi.t('already_on'), 'ok');
            });
            return;
        }
        var a = e.target.closest('a');
        if (!a) return;
        e.preventDefault();
        if (a.classList.contains('js-edit')) openForm(row);
        if (a.classList.contains('js-del')) {
            if (!U.confirm((L.confirm_del_dict || '').replace(':name', row.title || ''))) return;
            U.post('/admin/system/dicts/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.delete_fail, 'err'); return; }
                table.refresh();
                U.toast((res && res.msg) || L.deleted, 'ok');
            });
        }
    });
})();
</script>
@endpush
