@extends('admin.layouts.inner')
@section('title', '字段管理')

@section('plain')
<div class="card card-panel">
    <div class="card-body">
        <form class="filter-bar" id="dict-search" onsubmit="return false;">
            <input type="text" name="dict_type" placeholder="类型">
            <input type="text" name="dict_key" placeholder="KEY">
            <input type="text" name="label" placeholder="名称">
            <button type="button" class="btn btn-sm" id="dict-search-btn">查询</button>
            <button type="reset" class="btn btn-muted btn-sm" id="dict-reset-btn">重置</button>
        </form>
    </div>
</div>
<div class="card card-panel">
    <div class="card-header">
        <span>字段</span>
        <div>
            <button type="button" class="btn btn-sm" id="dict-add-btn">新增字段</button>
            <button type="button" class="btn btn-muted btn-sm" id="dict-refresh-btn">刷新</button>
        </div>
    </div>
    <div class="card-body"><div id="dict-table"></div></div>
</div>
<template id="dict-dialog-tpl">
    <form>
        <input type="hidden" name="id">
        <label>类型</label>
        <input type="text" name="dict_type" placeholder="如 site_config">
        <label>KEY</label>
        <input type="text" name="dict_key" placeholder="如 usdt_rate">
        <label>名称</label>
        <input type="text" name="label" placeholder="展示名称">
        <label>值类型</label>
        <select name="value_type" id="dict-value-type">
            <option value="0">string</option>
            <option value="1">int</option>
            <option value="2">float</option>
            <option value="3">json</option>
            <option value="4">array</option>
            <option value="5">enum</option>
            <option value="6">text</option>
        </select>
        <div id="dict-value-string-box">
            <label>值</label>
            <input type="text" name="dict_value">
        </div>
        <div id="dict-value-text-box" style="display:none;">
            <label>值</label>
            <textarea name="dict_value_text"></textarea>
        </div>
        <div id="dict-enum-limit-box" style="display:none;">
            <label>枚举</label>
            <textarea name="enum_limit" placeholder='如 ["a","b"]'></textarea>
        </div>
        <label>状态</label>
        <select name="status"><option value="0">启用</option><option value="1">禁用</option></select>
        <label>排序</label>
        <input type="number" name="sort" value="0">
        <label>备注</label>
        <textarea name="remark"></textarea>
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    function toggleValueTypeUI(root, valueType) {
        var t = String(valueType || '0');
        var showText = (t === '6' || t === '3' || t === '4' || t === '5');
        root.querySelector('#dict-value-string-box').style.display = showText ? 'none' : '';
        root.querySelector('#dict-value-text-box').style.display = showText ? '' : 'none';
        root.querySelector('#dict-enum-limit-box').style.display = t === '5' ? '' : 'none';
    }
    var table = U.table({
        el: '#dict-table',
        url: '/admin/system/dicts/list',
        cols: [
            {key: 'id', title: 'ID', width: 70},
            {key: 'dict_type', title: '类型'},
            {key: 'dict_key', title: 'KEY'},
            {key: 'label', title: '名称'},
            {key: 'value_type', title: '值类型', width: 80},
            {key: 'dict_value', title: '值'},
            {title: '状态', width: 80, html: function (d) { return d.status == 0 ? U.status(true, '启用') : U.status(false, '禁用'); }},
            {key: 'sort', title: '排序', width: 70},
            {key: 'remark', title: '备注'},
            {key: 'update_time', title: '更新时间', width: 150},
            {title: '操作', cls: 'actions', html: function () { return '<a href="#" class="btn-link js-edit">编辑</a><a href="#" class="btn-link js-del">删除</a>'; }}
        ]
    });
    function openForm(data) {
        data = data || {};
        U.dialog({
            title: data.id ? '编辑字段' : '新增字段',
            wide: true,
            content: document.getElementById('dict-dialog-tpl').innerHTML,
            onOpen: function (body) {
                var vt = data.value_type != null ? String(data.value_type) : '0';
                U.fillForm(body.querySelector('form'), {
                    id: data.id || '',
                    dict_type: data.dict_type || '',
                    dict_key: data.dict_key || '',
                    label: data.label || '',
                    value_type: vt,
                    sort: data.sort || 0,
                    status: data.status == 1 ? '1' : '0',
                    remark: data.remark || ''
                });
                var dictValue = data.dict_value != null ? String(data.dict_value) : '';
                if (vt === '6' || vt === '3' || vt === '4' || vt === '5') body.querySelector('textarea[name=dict_value_text]').value = dictValue;
                else body.querySelector('input[name=dict_value]').value = dictValue;
                var enumText = '';
                if (data.enum_limit != null) {
                    try { enumText = JSON.stringify(data.enum_limit); } catch (e) { enumText = ''; }
                }
                body.querySelector('textarea[name=enum_limit]').value = enumText;
                toggleValueTypeUI(body, vt);
                body.querySelector('#dict-value-type').addEventListener('change', function () { toggleValueTypeUI(body, this.value); });
            },
            onSave: function (body) {
                var form = body.querySelector('form');
                var payload = U.formData(form);
                if (!payload.dict_type) { U.toast('请输入类型', 'err'); return false; }
                if (!payload.dict_key) { U.toast('请输入KEY', 'err'); return false; }
                var vt = String(payload.value_type || '0');
                var dictValue = (vt === '6' || vt === '3' || vt === '4' || vt === '5')
                    ? (body.querySelector('textarea[name=dict_value_text]').value || '').trim()
                    : (payload.dict_value || '').trim();
                if (vt === '3' || vt === '4' || vt === '5') {
                    try { dictValue = JSON.stringify(JSON.parse(dictValue || 'null')); }
                    catch (e) { U.toast('值不是合法JSON', 'err'); return false; }
                }
                var enumLimit;
                if (vt === '5') {
                    try { enumLimit = JSON.stringify(JSON.parse((body.querySelector('textarea[name=enum_limit]').value || '').trim() || '[]')); }
                    catch (e) { U.toast('枚举不是合法JSON数组', 'err'); return false; }
                }
                payload.dict_value = dictValue;
                payload.enum_limit = enumLimit;
                payload.value_type = vt;
                var url = payload.id ? '/admin/system/dicts/update' : '/admin/system/dicts/add';
                if (!payload.id) delete payload.id;
                return U.post(url, payload).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return false; }
                    U.toast('保存成功', 'ok');
                    table.refresh();
                });
            }
        });
    }
    U.on('#dict-search-btn', 'click', function () { table.reload(U.formData('#dict-search')); });
    U.on('#dict-reset-btn', 'click', function () { setTimeout(function () { table.reload({}); }, 0); });
    U.on('#dict-refresh-btn', 'click', function () { table.refresh(); });
    U.on('#dict-add-btn', 'click', function () { openForm({}); });
    U.on('#dict-table', 'click', function (e) {
        var a = e.target.closest('a'); if (!a) return;
        var row = (table.rows() || [])[e.target.closest('tr').getAttribute('data-idx')];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-edit')) openForm(row);
        if (a.classList.contains('js-del') && U.confirm('确认删除该字段？')) {
            U.post('/admin/system/dicts/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh(); U.toast('删除成功', 'ok');
            });
        }
    });
})();
</script>
@endpush
