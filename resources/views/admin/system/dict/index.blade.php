@extends('admin.layouts.inner')
@section('title', admin_t('page.dict'))

@php
    $ui = $ui ?? [];
    $groups = $groups ?? [];
    $queues = $queues ?? ['all' => 0, 'off' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
    $valueTypes = $ui['value_types'] ?? \App\Services\Admin\System\SysDictService::VALUE_TYPES;
@endphp

@section('plain')
<div class="card card-panel dict-index" id="dict-index">
    <div class="card-header">
        <span>{{ $ui['title'] ?? '字典' }} <em id="dict-count"></em></span>
        <button type="button" class="btn btn-sm" id="dict-add-btn">{{ $ui['compose'] ?? '添加' }}</button>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">{{ $ui['lead'] ?? '' }}</p>

        <div class="queue-chips dict-groups" id="dict-groups">
            <button type="button" class="chip active" data-queue="">全部@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            @foreach($groups as $group)
                <button type="button" class="chip" data-queue="dict_type" data-value="{{ $group['dict_type'] }}">{{ $group['label'] }}@if(($group['cnt'] ?? 0) > 0)<em>{{ $group['cnt'] }}</em>@endif</button>
            @endforeach
            <button type="button" class="chip" data-queue="status" data-value="1">已停用@if($q('off') > 0)<em>{{ $q('off') }}</em>@endif</button>
        </div>
        <p class="muted field-hint" id="dict-group-hint">点一个类型看选项。</p>

        <form class="filter-bar dict-find" id="dict-search" onsubmit="return false;">
            <input type="hidden" name="dict_type">
            <input type="hidden" name="status">
            <input type="search" name="q" placeholder="{{ $ui['find'] ?? '搜类型、Key 或显示名' }}" autocomplete="off" aria-label="搜索字典">
            <button type="button" class="btn btn-sm" id="dict-search-btn">搜索</button>
            <button type="reset" class="btn btn-muted btn-sm" id="dict-reset-btn">重置</button>
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
            <h3>基础信息</h3>
            <div class="dict-form-grid">
                <div>
                    <label for="dict-f-type">字典类型</label>
                    <input id="dict-f-type" type="text" name="dict_type" list="dict-type-list" placeholder="如：filter_area">
                </div>
                <div>
                    <label for="dict-f-key">字典键名</label>
                    <input id="dict-f-key" type="text" name="dict_key" placeholder="如：大陆">
                </div>
                <div>
                    <label for="dict-f-vtype">值类型</label>
                    <select id="dict-f-vtype" name="value_type" class="dict-value-type">
                        @foreach($valueTypes as $code => $label)
                            <option value="{{ $code }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="dict-f-label">显示名称</label>
                    <input id="dict-f-label" type="text" name="label" placeholder="请输入显示名称">
                </div>
                <div>
                    <label for="dict-f-sort">排序</label>
                    <input id="dict-f-sort" type="number" name="sort" value="0">
                </div>
                <div>
                    <label for="dict-f-status">状态</label>
                    <select id="dict-f-status" name="status">
                        <option value="0">启用</option>
                        <option value="1">停用</option>
                    </select>
                </div>
            </div>
        </section>
        <section class="dict-form-sec">
            <h3>字典值配置</h3>
            <div class="dict-value-box" data-type="0">
                <label>字典值</label>
                <input type="text" name="dict_value" class="dict-v dict-v-0" placeholder="请输入字符串值">
            </div>
            <div class="dict-value-box" data-type="1" hidden>
                <label>字典值</label>
                <input type="number" name="dict_value_int" class="dict-v dict-v-1" placeholder="请输入整数" step="1">
            </div>
            <div class="dict-value-box" data-type="2" hidden>
                <label>字典值</label>
                <input type="text" name="dict_value_float" class="dict-v dict-v-2" placeholder="如: 3.14, 0.5">
            </div>
            <div class="dict-value-box" data-type="3" hidden>
                <label>字典值</label>
                <textarea name="dict_value_json_obj" class="dict-v dict-v-3" rows="6" placeholder='{"name":"示例"}'></textarea>
                <p class="muted field-hint">请输入 JSON 对象，如 {"k":"v"}。不是数组。</p>
            </div>
            <div class="dict-value-box" data-type="4" hidden>
                <label>字典值</label>
                <textarea name="dict_value_json_arr" class="dict-v dict-v-4" rows="6" placeholder='["选项1","选项2"]'></textarea>
                <p class="muted field-hint">请输入 JSON 数组，如 ["a","b"]。不是对象。</p>
            </div>
            <div class="dict-value-box" data-type="5" hidden>
                <label>枚举限制</label>
                <textarea name="enum_limit" class="dict-enum-limit" rows="4" placeholder='["on","off"]'></textarea>
                <p class="muted field-hint">先定义可选值列表，再在下面选当前值。</p>
                <label>字典值</label>
                <select name="dict_value_enum" class="dict-v dict-v-5"></select>
            </div>
            <div class="dict-value-box" data-type="6" hidden>
                <label>字典值</label>
                <textarea name="dict_value_text" class="dict-v dict-v-6" rows="8" placeholder="可写 HTML 文本。本站没有可视化编辑器。"></textarea>
                <p class="muted field-hint">富文本按 HTML 保存，前台怎么用由调用方决定。</p>
            </div>
        </section>
        <section class="dict-form-sec">
            <h3>备注说明</h3>
            <label for="dict-f-remark">备注信息</label>
            <textarea id="dict-f-remark" name="remark" rows="3" maxlength="200" placeholder="可选填，用于说明该字典项的用途和注意事项"></textarea>
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
    var GROUPS = @json($groups);
    var UI = @json($ui);

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
        if (hintEl) hintEl.textContent = g ? (g.hint || '') : '点一个类型看选项。';
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
        var html = '<button type="button" class="chip" data-queue="">全部' + (queues.all > 0 ? '<em>' + queues.all + '</em>' : '') + '</button>';
        GROUPS.forEach(function (t) {
            html += '<button type="button" class="chip" data-queue="dict_type" data-value="' + U.escape(t.dict_type || '') + '">' + U.escape(t.label || t.dict_type || '');
            if (t.cnt > 0) html += '<em>' + t.cnt + '</em>';
            html += '</button>';
        });
        html += '<button type="button" class="chip" data-queue="status" data-value="1">已停用' + (queues.off > 0 ? '<em>' + queues.off + '</em>' : '') + '</button>';
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
        var html = '<option value="">请选择枚举值</option>';
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
        return '<span class="dict-type-tag">' + U.escape(d.value_type_label || '字符串') + '</span>';
    }

    var table = U.table({
        el: '#dict-table',
        countEl: countEl,
        url: '/admin/system/dicts/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                if (where.dict_type) {
                    return '<div class="list-empty"><p>这个类型还没有选项。</p><p class="muted">点右上角「添加」。</p></div>';
                }
                return '<div class="list-empty"><p>没有符合条件的选项。</p><p><button type="button" class="btn btn-muted btn-sm" id="dict-empty-reset">清除筛选</button></p></div>';
            }
            return '<div class="list-empty"><p>还没有选项。</p><p class="muted">点「添加」，选一个类型再填。</p></div>';
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
            {title: '类型', width: 120, html: function (d) {
                var g = groupById(d.dict_type || '');
                return U.escape((g && g.label) ? g.label : (d.dict_type || ''));
            }},
            {title: 'Key', width: 120, html: function (d) { return '<code>' + U.escape(d.dict_key || '') + '</code>'; }},
            {title: '显示名称', html: function (d) { return U.escape(d.title || ''); }},
            {title: '值类型', width: 100, html: typeTag},
            {title: '字典值', html: function (d) {
                var text = d.value_preview || '';
                return text ? U.escape(text) : '<span class="muted">-</span>';
            }},
            {title: '枚举限制', html: function (d) {
                return d.enum_preview ? U.escape(d.enum_preview) : '<span class="muted">-</span>';
            }},
            {title: '状态', width: 80, html: function (d) {
                return '<button type="button" class="dict-switch js-state' + (d.is_on ? ' is-on' : '') + '" title="' + (d.is_on ? '启用' : '停用') + '"></button>';
            }},
            {title: '备注', html: function (d) { return d.remark ? U.escape(d.remark) : '<span class="muted">-</span>'; }},
            {title: '排序', width: 60, html: function (d) { return U.escape(d.sort == null ? '0' : d.sort); }},
            {title: '操作', cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-edit">编辑</a><a href="#" class="btn-link js-del">删除</a>';
            }}
        ]
    });
    markChips();
    showGroupMeta();

    function openForm(data) {
        data = data || {};
        U.dialog({
            title: data.id ? (UI.edit || '编辑字典') : (UI.add || '新增字典'),
            wide: true,
            okText: '确认保存',
            content: document.getElementById('dict-dialog-tpl').innerHTML,
            onOpen: function (body) { bindForm(body, data); },
            onSave: function (body) {
                var payload = collectPayload(body);
                if (!payload.dict_type) { U.toast('请填写分类', 'err'); return false; }
                if (!payload.dict_key) { U.toast('请填写标识', 'err'); return false; }
                var url = payload.id ? '/admin/system/dicts/update' : '/admin/system/dicts/add';
                return U.post(url, payload).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || '没能保存', 'err'); return false; }
                    U.toast((res && res.msg) || '已保存', 'ok');
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
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '没能改状态', 'err'); return; }
                table.refresh();
                U.toast(next === 1 ? '已停用' : '已启用', 'ok');
            });
            return;
        }
        var a = e.target.closest('a');
        if (!a) return;
        e.preventDefault();
        if (a.classList.contains('js-edit')) openForm(row);
        if (a.classList.contains('js-del')) {
            if (!U.confirm('确定删除「' + (row.title || '') + '」？分类里会少这一条。')) return;
            U.post('/admin/system/dicts/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '没能删除', 'err'); return; }
                table.refresh();
                U.toast((res && res.msg) || '已删除', 'ok');
            });
        }
    });
})();
</script>
@endpush
