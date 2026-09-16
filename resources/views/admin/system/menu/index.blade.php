@extends('admin.layouts.inner')
@section('title', admin_t('page.menus'))

@section('plain')
<div class="card card-panel">
    <div class="card-body">
        <form class="filter-bar" id="menu-search" onsubmit="return false;">
            <input type="text" name="name" placeholder="名称">
            <input type="text" name="code" placeholder="标识">
            <input type="text" name="api" placeholder="地址">
            <select name="type">
                <option value="">全部类型</option>
                <option value="1">菜单</option>
                <option value="2">按钮</option>
                <option value="3">接口</option>
            </select>
            <button type="button" class="btn btn-sm" id="menu-search-btn">查询</button>
            <button type="reset" class="btn btn-muted btn-sm" id="menu-reset-btn">重置</button>
        </form>
    </div>
</div>
<div class="card card-panel">
    <div class="card-header">
        <span>菜单</span>
        <div>
            <button type="button" class="btn btn-sm" id="menu-add-btn">新增</button>
            <button type="button" class="btn btn-muted btn-sm" id="menu-refresh-btn">刷新</button>
        </div>
    </div>
    <div class="card-body"><div id="menu-table"></div></div>
</div>
<template id="menu-dialog-tpl">
    <form>
        <input type="hidden" name="id">
        <label>类型</label>
        <select name="type">
            <option value="1">菜单</option>
            <option value="2">按钮</option>
            <option value="3">接口</option>
        </select>
        <label>父级</label>
        <select name="pid" id="menu-pid-select"><option value="0">顶级</option></select>
        <label>名称</label>
        <input type="text" name="name">
        <label>地址</label>
        <input type="text" name="api" placeholder="/admin/system/menus">
        <label>方法</label>
        <input type="text" name="method" placeholder="GET/POST">
        <label>标识</label>
        <input type="text" name="code" placeholder="为空则根据地址生成">
        <label>图标</label>
        <input type="text" name="icon" placeholder="layui-icon-xxx">
        <label>排序</label>
        <input type="number" name="sort" value="0">
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    function codeFromApi(api) {
        api = String(api || '').trim();
        if (!api) return '';
        if (api.indexOf('route:') === 0) return api;
        api = api.replace(/^https?:\/\/[^/]+/i, '').replace(/^\/+/, '');
        return api ? api.replace(/\/+/g, '/').replace(/\//g, '.') : '';
    }
    function typeHtml(d) {
        if (String(d.type) === '1') return '<span class="status status-info">菜单</span>';
        if (String(d.type) === '2') return '<span class="status status-ok">按钮</span>';
        return '<span class="status status-off">接口</span>';
    }
    var table = U.table({
        el: '#menu-table',
        url: '/admin/system/menus/list',
        cols: [
            {key: 'id', title: 'ID', width: 70},
            {title: '名称', html: function (d) {
                var p = '';
                for (var i = 0; i < (parseInt(d.level, 10) || 0); i++) p += '— ';
                return U.escape(p + (d.name || ''));
            }},
            {title: '类型', width: 80, html: typeHtml},
            {title: '父级', html: function (d) { return U.escape(d.parent_name || '顶级'); }},
            {key: 'code', title: '标识'},
            {key: 'api', title: '地址'},
            {key: 'method', title: '方法', width: 80},
            {key: 'icon', title: '图标', width: 120},
            {key: 'sort', title: '排序', width: 70},
            {title: '操作', cls: 'actions', html: function (d) {
                var html = '';
                if (String(d.type) === '1') html += '<a href="#" class="btn-link js-child">新增子级</a>';
                html += '<a href="#" class="btn-link js-edit">编辑</a><a href="#" class="btn-link js-del">删除</a>';
                return html;
            }}
        ]
    });
    function openForm(data) {
        data = data || {};
        var isEdit = !!data.id;
        U.dialog({
            title: isEdit ? '编辑' : '新增',
            content: document.getElementById('menu-dialog-tpl').innerHTML,
            onOpen: function (body) {
                var form = body.querySelector('form');
                U.fillForm(form, {
                    id: data.id || '',
                    type: data.type != null ? String(data.type) : '1',
                    pid: data.pid != null ? String(data.pid) : '0',
                    name: data.name || '',
                    api: data.api || '',
                    method: data.method || '',
                    code: data.code || '',
                    icon: data.icon || '',
                    sort: data.sort != null ? String(data.sort) : '0'
                });
                U.get('/admin/system/menus/parents', {type: 1}).then(function (res) {
                    var opts = (res && res.code === 0 && res.data) ? res.data : [{id: 0, name: '顶级'}];
                    var html = '';
                    opts.forEach(function (o) {
                        html += '<option value="' + U.escape(o.id != null ? o.id : 0) + '">' + U.escape(o.name || '') + '</option>';
                    });
                    var sel = body.querySelector('#menu-pid-select');
                    sel.innerHTML = html;
                    sel.value = data.pid != null ? String(data.pid) : '0';
                });
                body.querySelector('input[name=api]').addEventListener('blur', function () {
                    var code = body.querySelector('input[name=code]');
                    if (!code.value) code.value = codeFromApi(this.value);
                });
            },
            onSave: function (body) {
                var field = U.formData(body.querySelector('form'));
                field.pid = parseInt(field.pid || '0', 10);
                field.type = parseInt(field.type || '1', 10);
                field.sort = parseInt(field.sort || '0', 10);
                if (field.method) field.method = String(field.method).toUpperCase();
                if (!field.code && field.api) field.code = codeFromApi(field.api);
                var url = isEdit ? '/admin/system/menus/update' : '/admin/system/menus/add';
                return U.post(url, field).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return false; }
                    table.refresh();
                });
            }
        });
    }
    U.on('#menu-search-btn', 'click', function () { table.reload(U.formData('#menu-search')); });
    U.on('#menu-reset-btn', 'click', function () { setTimeout(function () { table.reload({}); }, 0); });
    U.on('#menu-refresh-btn', 'click', function () { table.refresh(); });
    U.on('#menu-add-btn', 'click', function () { openForm({}); });
    U.on('#menu-table', 'click', function (e) {
        var a = e.target.closest('a'); if (!a) return;
        var row = (table.rows() || [])[e.target.closest('tr').getAttribute('data-idx')];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-child')) {
            var childType = (row.level != null && parseInt(row.level, 10) >= 1) ? 2 : 1;
            openForm({pid: row.id, type: childType});
        }
        if (a.classList.contains('js-edit')) openForm(row);
        if (a.classList.contains('js-del') && U.confirm('确认删除？（会级联删除子项）')) {
            U.post('/admin/system/menus/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh();
            });
        }
    });
})();
</script>
@endpush
