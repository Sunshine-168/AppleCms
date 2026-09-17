@extends('admin.layouts.inner')
@section('title', admin_t('page.dict'))

@php
    $types = $types ?? [];
    $queues = $queues ?? ['all' => 0, 'off' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
@endphp

@section('plain')
<div class="card card-panel dict-index">
    <div class="card-header">
        <span>字典 <em id="dict-count"></em></span>
        <div>
            <a class="btn btn-muted btn-sm" href="/admin/video/types">分类</a>
            <a class="btn btn-muted btn-sm" href="/admin/system/database/dict">数据库字典</a>
        </div>
    </div>
    <div class="card-body">
        <div class="dict-compose">
            <form id="dict-compose" autocomplete="off" onsubmit="return false;">
                <label class="dict-compose-label" for="dict-quick-type">新增选项</label>
                <div class="dict-compose-row">
                    <input id="dict-quick-type" type="text" name="dict_type" list="dict-type-list" placeholder="分组，如 pay_status" aria-label="分组" required>
                    <input type="text" name="label" placeholder="显示名" aria-label="显示名">
                    <input type="text" name="dict_key" placeholder="标识，如 paid" aria-label="标识" required>
                    <button class="btn" type="submit" id="dict-compose-btn">添加</button>
                </div>
                <p class="muted field-hint">同一分组里放一组选项。程序按「标识」取值，显示名给人看。值默认等于标识，复杂类型点编辑。</p>
            </form>
        </div>

        <form class="filter-bar dict-find" id="dict-search" onsubmit="return false;">
            <input type="hidden" name="dict_type">
            <input type="hidden" name="status">
            <input type="search" name="q" placeholder="搜显示名、标识或分组" autocomplete="off" aria-label="搜索字典">
            <button type="button" class="btn btn-sm" id="dict-search-btn">搜索</button>
            <button type="reset" class="btn btn-muted btn-sm" id="dict-reset-btn">重置</button>
        </form>
        <div class="queue-chips" id="dict-queues">
            <button type="button" class="chip" data-queue="">全部@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            @foreach($types as $type)
                <button type="button" class="chip" data-queue="dict_type" data-value="{{ $type['dict_type'] }}">{{ $type['dict_type'] }}@if(($type['cnt'] ?? 0) > 0)<em>{{ $type['cnt'] }}</em>@endif</button>
            @endforeach
            <button type="button" class="chip" data-queue="status" data-value="1">已停用@if($q('off') > 0)<em>{{ $q('off') }}</em>@endif</button>
        </div>
        <p class="muted recycle-lead">给下拉框、状态码这类固定选项用。影片分类不在这里，在「<a href="/admin/video/types">分类</a>」。地区、年份在站点设置。看表结构去「<a href="/admin/system/database/dict">数据库字典</a>」。</p>
        <div id="dict-table"></div>
    </div>
</div>
<datalist id="dict-type-list">
    @foreach($types as $type)
        <option value="{{ $type['dict_type'] }}"></option>
    @endforeach
</datalist>
<template id="dict-dialog-tpl">
    <form>
        <input type="hidden" name="id">
        <label>分组</label>
        <input type="text" name="dict_type" list="dict-type-list" placeholder="英文小写，如 pay_status">
        <p class="muted field-hint">同一分组的选项会出现在一起。不要和影片分类、数据表字段混用。</p>
        <label>显示名</label>
        <input type="text" name="label" placeholder="给人看的名字">
        <label>标识</label>
        <input type="text" name="dict_key" placeholder="程序用来取值，同组不能重复">
        <label>值</label>
        <input type="text" name="dict_value" class="dict-value-input">
        <textarea name="dict_value_text" class="dict-value-text" style="display:none;"></textarea>
        <details class="settings-details dict-advanced">
            <summary>值类型和排序</summary>
            <label>值类型</label>
            <select name="value_type" class="dict-value-type">
                <option value="0">文本</option>
                <option value="1">整数</option>
                <option value="2">小数</option>
                <option value="3">JSON</option>
                <option value="4">数组</option>
                <option value="5">枚举</option>
                <option value="6">长文本</option>
            </select>
            <div class="dict-enum-limit-box" style="display:none;">
                <label>枚举范围</label>
                <textarea name="enum_limit" placeholder='如 ["a","b"]'></textarea>
            </div>
            <label>排序</label>
            <input type="number" name="sort" value="0">
        </details>
        <label>状态</label>
        <select name="status"><option value="0">启用</option><option value="1">停用</option></select>
        <label>备注</label>
        <textarea name="remark" placeholder="选填，只在后台看到"></textarea>
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var form = document.getElementById('dict-search');
    var compose = document.getElementById('dict-compose');
    var countEl = document.getElementById('dict-count');
    var queuesEl = document.getElementById('dict-queues');
    var typeList = document.getElementById('dict-type-list');

    function cleanWhere(data) {
        var out = {};
        Object.keys(data || {}).forEach(function (k) { if (data[k] !== '') out[k] = data[k]; });
        return out;
    }
    function queryWhere() {
        return Object.assign({limit: 20}, cleanWhere(U.formData(form)));
    }
    function isFiltered(where) {
        return Object.keys(where || {}).some(function (k) { return k !== 'limit' && where[k] !== ''; });
    }
    function markChips() {
        var type = form.dict_type.value;
        var status = form.status.value;
        U.qa('#dict-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = false;
            if (key === '' && type === '' && status === '') on = true;
            else if (key === 'dict_type' && status === '' && type === val) on = true;
            else if (key === 'status' && type === '' && status === val) on = true;
            chip.classList.toggle('active', on);
        });
    }
    function applyQueue(key, value) {
        form.dict_type.value = '';
        form.status.value = '';
        if (key === 'dict_type') {
            form.dict_type.value = value || '';
            if (compose && value) compose.dict_type.value = value;
        } else if (key === 'status') {
            form.status.value = value || '';
        }
        runSearch();
    }
    function renderTypes(types, queues) {
        types = types || [];
        queues = queues || {};
        var html = '<button type="button" class="chip" data-queue="">全部' + (queues.all > 0 ? '<em>' + queues.all + '</em>' : '') + '</button>';
        types.forEach(function (t) {
            var name = t.dict_type || '';
            html += '<button type="button" class="chip" data-queue="dict_type" data-value="' + U.escape(name) + '">' + U.escape(name);
            if (t.cnt > 0) html += '<em>' + t.cnt + '</em>';
            html += '</button>';
        });
        html += '<button type="button" class="chip" data-queue="status" data-value="1">已停用' + (queues.off > 0 ? '<em>' + queues.off + '</em>' : '') + '</button>';
        queuesEl.innerHTML = html;
        if (typeList) {
            typeList.innerHTML = types.map(function (t) {
                return '<option value="' + U.escape(t.dict_type || '') + '"></option>';
            }).join('');
        }
        markChips();
    }
    function runSearch() {
        table.reload(queryWhere());
        markChips();
    }
    function toggleValueTypeUI(root, valueType) {
        var t = String(valueType || '0');
        var showText = (t === '6' || t === '3' || t === '4' || t === '5');
        var input = root.querySelector('.dict-value-input');
        var area = root.querySelector('.dict-value-text');
        var enumBox = root.querySelector('.dict-enum-limit-box');
        if (input) input.style.display = showText ? 'none' : '';
        if (area) area.style.display = showText ? '' : 'none';
        if (enumBox) enumBox.style.display = t === '5' ? '' : 'none';
        var details = root.querySelector('.dict-advanced');
        if (details && t !== '0') details.open = true;
    }
    function nameHtml(d) {
        var badges = '';
        if (!d.is_on) badges += '<span class="badge badge-off">停用</span>';
        var meta = U.escape(d.dict_type || '');
        if (d.dict_key) meta += (meta ? ' · ' : '') + U.escape(d.dict_key);
        if (d.remark) meta += (meta ? ' · ' : '') + U.escape(d.remark);
        if (d.update_time) meta += (meta ? ' · ' : '') + U.escape(d.update_time);
        return '<div class="entry-row-title-line"><a class="entry-row-title js-edit" href="#">' + U.escape(d.title || '未命名') + '</a> ' + badges + '</div>'
            + '<div class="entry-row-meta">' + meta + '</div>';
    }
    function valueHtml(d) {
        var text = d.value_preview || '';
        if (!text) return '<span class="muted">空</span>';
        var extra = d.value_type_label && d.value_type != 0 ? (' · ' + U.escape(d.value_type_label)) : '';
        return U.escape(text) + extra;
    }

    var table = U.table({
        el: '#dict-table',
        url: '/admin/system/dicts/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                if (where.dict_type) {
                    return '<div class="list-empty"><p>这个分组还没有选项。</p><p class="muted">用上方「新增选项」加一条，分组会带上当前筛选。</p></div>';
                }
                return '<div class="list-empty"><p>没有符合条件的选项。</p><p><button type="button" class="btn btn-muted btn-sm" id="dict-empty-reset">清除筛选</button></p></div>';
            }
            return '<div class="list-empty"><p>还没有字典。</p><p class="muted">需要自定义下拉时再加。影片分类不在这里。</p></div>';
        },
        onDraw: function (_wrap, list, parsed) {
            countEl.textContent = list.length ? '· ' + list.length : '';
            if (parsed) renderTypes(parsed.types || [], parsed.queues || {});
            var reset = document.getElementById('dict-empty-reset');
            if (reset) reset.addEventListener('click', function () { form.reset(); form.dict_type.value = ''; form.status.value = ''; runSearch(); });
            U.qa('#dict-table tr[data-idx]').forEach(function (tr) {
                var row = (table.rows() || [])[tr.getAttribute('data-idx')];
                if (row && !row.is_on) tr.classList.add('is-off');
            });
        },
        cols: [
            {title: '选项', html: nameHtml},
            {title: '值', html: valueHtml},
            {title: '操作', cls: 'actions', html: function (d) {
                var html = '<a href="#" class="btn-link js-edit">编辑</a>';
                html += d.is_on
                    ? '<a href="#" class="btn-link js-state">停用</a>'
                    : '<a href="#" class="btn-link js-state">启用</a>';
                html += '<a href="#" class="btn-link js-del">删除</a>';
                return html;
            }}
        ]
    });
    markChips();

    function openForm(data) {
        data = data || {};
        U.dialog({
            title: data.id ? '编辑选项' : '新增选项',
            wide: true,
            content: document.getElementById('dict-dialog-tpl').innerHTML,
            onOpen: function (body) {
                var vt = data.value_type != null ? String(data.value_type) : '0';
                U.fillForm(body.querySelector('form'), {
                    id: data.id || '',
                    dict_type: data.dict_type || form.dict_type.value || '',
                    dict_key: data.dict_key || '',
                    label: data.label || '',
                    value_type: vt,
                    sort: data.sort || 0,
                    status: data.status == 1 ? '1' : '0',
                    remark: data.remark || ''
                });
                var dictValue = data.dict_value != null ? String(data.dict_value) : '';
                if (vt === '6' || vt === '3' || vt === '4' || vt === '5') body.querySelector('.dict-value-text').value = dictValue;
                else body.querySelector('.dict-value-input').value = dictValue;
                var enumText = '';
                if (data.enum_limit != null) {
                    try { enumText = typeof data.enum_limit === 'string' ? data.enum_limit : JSON.stringify(data.enum_limit); } catch (e) { enumText = ''; }
                }
                body.querySelector('textarea[name=enum_limit]').value = enumText;
                toggleValueTypeUI(body, vt);
                body.querySelector('.dict-value-type').addEventListener('change', function () { toggleValueTypeUI(body, this.value); });
            },
            onSave: function (body) {
                var payload = U.formData(body.querySelector('form'));
                if (!payload.dict_type) { U.toast('请填写分组', 'err'); return false; }
                if (!payload.dict_key) { U.toast('请填写标识', 'err'); return false; }
                var vt = String(payload.value_type || '0');
                var dictValue = (vt === '6' || vt === '3' || vt === '4' || vt === '5')
                    ? (body.querySelector('.dict-value-text').value || '').trim()
                    : (payload.dict_value || '').trim();
                if (vt === '3' || vt === '4' || vt === '5') {
                    try { dictValue = JSON.stringify(JSON.parse(dictValue || 'null')); }
                    catch (e) { U.toast('值不是合法 JSON', 'err'); return false; }
                }
                var enumLimit;
                if (vt === '5') {
                    try { enumLimit = JSON.stringify(JSON.parse((body.querySelector('textarea[name=enum_limit]').value || '').trim() || '[]')); }
                    catch (e) { U.toast('枚举不是合法 JSON 数组', 'err'); return false; }
                }
                payload.dict_value = dictValue;
                payload.enum_limit = enumLimit;
                payload.value_type = vt;
                var url = payload.id ? '/admin/system/dicts/update' : '/admin/system/dicts/add';
                if (!payload.id) delete payload.id;
                return U.post(url, payload).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || '没能保存', 'err'); return false; }
                    U.toast((res && res.msg) || '已保存', 'ok');
                    table.refresh();
                });
            }
        });
    }

    U.on('#dict-search-btn', 'click', runSearch);
    U.on('#dict-reset-btn', 'click', function () { setTimeout(function () { form.dict_type.value = ''; form.status.value = ''; runSearch(); }, 0); });
    U.on('#dict-queues', 'click', function (e) {
        var chip = e.target.closest('.chip');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#dict-compose', 'submit', function (e) {
        e.preventDefault();
        var data = U.formData(compose);
        if (!data.dict_type) { U.toast('请填写分组', 'err'); return; }
        if (!data.dict_key) { U.toast('请填写标识', 'err'); return; }
        if (!data.label) data.label = data.dict_key;
        data.dict_value = data.dict_key;
        data.value_type = '0';
        data.status = '0';
        data.sort = '0';
        U.post('/admin/system/dicts/add', data).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '没能添加', 'err'); return; }
            U.toast((res && res.msg) || '已添加', 'ok');
            var keepType = data.dict_type;
            compose.reset();
            compose.dict_type.value = keepType;
            table.refresh();
        });
    });
    U.on('#dict-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        var tr = e.target.closest('tr');
        if (!tr) return;
        var row = (table.rows() || [])[tr.getAttribute('data-idx')];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-edit')) openForm(row);
        if (a.classList.contains('js-state')) {
            var next = row.is_on ? 1 : 0;
            U.post('/admin/system/dicts/state', {id: row.id, status: next}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '没能改状态', 'err'); return; }
                table.refresh();
                U.toast(next === 1 ? '已停用' : '已启用', 'ok');
            });
        }
        if (a.classList.contains('js-del')) {
            if (!U.confirm('确定删除「' + (row.title || '') + '」？分组里会少这一条。')) return;
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
