@extends('admin.layouts.inner')
@section('title', admin_t('nav.menus'))

@section('plain')
<div class="card card-panel menu-index">
    <div class="card-header">
        <span>{{ admin_t('access.step_menus') }} <em id="menu-count"></em></span>
    </div>
    <div class="card-body">
        @include('admin.partials.access-chain', ['step' => 'menus'])
        <div class="menu-compose">
            <div class="menu-compose-row">
                <button type="button" class="btn" id="menu-sync-btn">{{ admin_t('access.sync') }}</button>
                <button type="button" class="btn btn-muted" id="menu-add-btn">加一页</button>
            </div>
        </div>
        <form class="filter-bar menu-find" id="menu-search">
            <input type="search" name="name" placeholder="搜名称" autocomplete="off" aria-label="搜索菜单">
            <input type="search" name="api" placeholder="搜地址" autocomplete="off" aria-label="搜索地址">
            <button type="submit" class="btn btn-sm" id="menu-search-btn">查询</button>
            <button type="reset" class="btn btn-muted btn-sm" id="menu-reset-btn">重置</button>
        </form>
        <div id="menu-table"></div>
    </div>
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
        <input type="text" name="name" placeholder="角色勾权限时看到的名字">
        <label>地址</label>
        <input type="text" name="api" placeholder="/admin/system/menus">
        <p class="muted field-hint">填后台页地址。对齐当前工作区会按侧栏重写这些页，自定义页请用别的地址。</p>
        <label>方法</label>
        <input type="text" name="method" placeholder="可空，空着则 GET/POST 都认">
        <label>标识</label>
        <input type="text" name="code" placeholder="为空则根据地址生成">
        <label>图标</label>
        <input type="text" name="icon" placeholder="user-cog">
        <label>排序</label>
        <input type="number" name="sort" value="0">
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var form = document.getElementById('menu-search');
    var countEl = document.getElementById('menu-count');
    function codeFromApi(api) {
        api = String(api || '').trim();
        if (!api) return '';
        if (api.indexOf('route:') === 0) return api;
        api = api.replace(/^https?:\/\/[^/]+/i, '').replace(/^\/+/, '');
        return api ? api.replace(/\/+/g, '/').replace(/\//g, '.') : '';
    }
    function cleanWhere(data) {
        var out = {};
        Object.keys(data || {}).forEach(function (k) { if (data[k] !== '') out[k] = data[k]; });
        return out;
    }
    function queryWhere() {
        return cleanWhere(U.formData(form));
    }
    function isFiltered(where) {
        return Object.keys(where || {}).some(function (k) { return where[k] !== ''; });
    }
    function nameHtml(d) {
        var p = '';
        for (var i = 0; i < (parseInt(d.level, 10) || 0); i++) p += '— ';
        var meta = U.escape(d.api || d.code || '');
        if (d.parent_name) meta = U.escape(d.parent_name) + (meta ? ' · ' + meta : '');
        return '<div class="entry-row-title-line"><a class="entry-row-title js-edit" href="#">' + U.escape(p + (d.name || '未命名')) + '</a></div>'
            + (meta ? '<div class="entry-row-meta">' + meta + '</div>' : '');
    }
    var table = U.table({
        el: '#menu-table',
        url: '/admin/system/menus/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>没有符合条件的页。</p><p><button type="button" class="btn btn-muted btn-sm" id="menu-empty-reset">清除筛选</button></p></div>';
            }
            return '<div class="list-empty"><p>还没有可授权的页。</p><p class="muted">点「对齐当前工作区」把侧栏页写进来，再到角色里勾。</p></div>';
        },
        onDraw: function (_wrap, list) {
            if (countEl) countEl.textContent = list.length ? '· ' + list.length : '';
            var reset = document.getElementById('menu-empty-reset');
            if (reset) reset.addEventListener('click', function () { form.reset(); table.reload({}); });
        },
        cols: [
            {title: '页面', html: nameHtml},
            {title: '操作', cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-edit">编辑</a><a href="#" class="btn-link js-del">删除</a>';
            }}
        ]
    });
    function openForm(data) {
        data = data || {};
        var isEdit = !!data.id;
        U.dialog({
            title: isEdit ? '编辑这一页' : '加一页',
            content: document.getElementById('menu-dialog-tpl').innerHTML,
            onOpen: function (body) {
                var formEl = body.querySelector('form');
                U.fillForm(formEl, {
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
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || '没能保存', 'err'); return false; }
                    table.refresh();
                });
            }
        });
    }
    U.on('#menu-search', 'submit', function (e) { e.preventDefault(); table.reload(queryWhere()); });
    U.on('#menu-search-btn', 'click', function () { table.reload(queryWhere()); });
    U.on('#menu-reset-btn', 'click', function () { setTimeout(function () { table.reload({}); }, 0); });
    U.on('#menu-add-btn', 'click', function () { openForm({}); });
    U.on('#menu-sync-btn', 'click', function () {
        U.post('/admin/system/menus/sync', {}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '没能对齐', 'err'); return; }
            U.toast((res && res.msg) || '已对齐当前工作区', 'ok');
            table.refresh();
        });
    });
    U.on('#menu-table', 'click', function (e) {
        var a = e.target.closest ? e.target.closest('a') : null; if (!a) return;
        var row = U.rowFromClick(e, table);
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-edit')) openForm(row);
        if (a.classList.contains('js-del') && U.confirm('删掉后角色里也勾不上这一页。子级会一起删。')) {
            U.post('/admin/system/menus/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '没能删除', 'err'); return; }
                table.refresh();
            });
        }
    });
})();
</script>
@endpush
