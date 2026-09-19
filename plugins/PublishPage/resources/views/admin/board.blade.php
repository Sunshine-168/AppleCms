@extends('admin.layouts.inner')
@section('title', $title ?? admin_t('nav.publish_page'))

@php
    $desk = in_array((string) ($desk ?? ''), ['config', 'groups'], true) ? (string) $desk : 'config';
    $options = is_array($options ?? null) ? $options : [];
@endphp

@section('plain')
<div class="card card-panel publish-board desk-board" id="publish-board">
    <div class="card-header">
        <span>发布页 <em id="publish-count"></em></span>
        <div>
            @if($desk === 'groups')
                <button type="button" class="btn btn-sm" id="publish-add-btn">{{ admin_t('ui.add') }}</button>
            @endif
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">首次访问首页根路径（没有查询参数）可拦一层。点「进入本站」写 Cookie，有效期一年。不是服务器 HTML 缓存。默认关闭。</p>
        <div class="queue-chips">
            <a class="chip{{ $desk === 'config' ? ' active' : '' }}" href="/admin/video/publish_pages">参数</a>
            <a class="chip{{ $desk === 'groups' ? ' active' : '' }}" href="/admin/video/publish_pages?desk=groups">线路组</a>
        </div>
        @if($desk === 'config')
            <form id="publish-config" onsubmit="return false;">
                <input type="hidden" name="desk" value="config">
                <label>开关</label>
                <select name="status">
                    <option value="0" @selected((int) ($options['status'] ?? 0) !== 1)>关闭（首页仍是站点）</option>
                    <option value="1" @selected((int) ($options['status'] ?? 0) === 1)>开启</option>
                </select>
                <label>标题</label>
                <input type="text" name="title" value="{{ $options['title'] ?? '地址发布页' }}">
                <label>副标题</label>
                <input type="text" name="subtitle" value="{{ $options['subtitle'] ?? '' }}">
                <label>收藏提示</label>
                <input type="text" name="bookmark" value="{{ $options['bookmark'] ?? '' }}">
                <label>页脚</label>
                <input type="text" name="footer" value="{{ $options['footer'] ?? '' }}">
                <label>永久地址文字</label>
                <input type="text" name="permanent_text" value="{{ $options['permanent_text'] ?? '' }}">
                <label>永久地址</label>
                <input type="text" name="permanent_url" value="{{ $options['permanent_url'] ?? '' }}" placeholder="https://">
                <p class="muted field-hint">只接受 http 或 https。记住访客靠 Cookie lv_publish_entered，清掉就会再看到发布页。</p>
                <p><button type="button" class="btn btn-sm" id="publish-config-save">保存</button></p>
            </form>
        @else
            <form class="filter-bar" id="publish-search" onsubmit="return false;">
                <input type="hidden" name="desk" value="groups">
                <input type="search" name="q" placeholder="搜线路组" autocomplete="off">
                <button type="button" class="btn btn-sm" id="publish-search-btn">{{ admin_t('ui.search') }}</button>
                <button type="reset" class="btn btn-muted btn-sm" id="publish-reset-btn">{{ admin_t('ui.reset') }}</button>
            </form>
            <div id="publish-table"></div>
        @endif
    </div>
</div>
<template id="publish-group-tpl">
    <form>
        <input type="hidden" name="id">
        <input type="hidden" name="desk" value="groups">
        <label>名称</label>
        <input class="entry-title" type="text" name="title" required placeholder="如 线路一" autofocus>
        <label>说明</label>
        <input type="text" name="hint" placeholder="给访客看的一行说明，可空">
        <label>地址</label>
        <textarea name="urls_text" rows="6" placeholder="每行：名称 https://example.com"></textarea>
        <p class="muted field-hint">每行一条，空格分隔名称和网址。javascript: 不会收录。进入本站不会在这页写入 Cookie。</p>
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var desk = @json($desk);
    if (desk === 'config') {
        U.on('#publish-config-save', 'click', function () {
            var data = U.formData(document.getElementById('publish-config'));
            data.desk = 'config';
            U.post('/admin/video/publish_pages/save', data).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                U.toast('已保存', 'ok');
            });
        });
        return;
    }
    var form = document.getElementById('publish-search');
    var countEl = document.getElementById('publish-count');
    function queryWhere() {
        var data = U.formData(form);
        var out = {desk: 'groups', limit: 20};
        if (data.q) out.q = data.q;
        return out;
    }
    var table = U.table({
        el: '#publish-table',
        url: '/admin/video/publish_pages/list',
        where: queryWhere(),
        emptyHtml: function (_p, where) {
            if (where && where.q) return '<div class="list-empty"><p>没有符合条件的记录</p></div>';
            return '<div class="list-empty"><p>还没有线路组</p><p><button type="button" class="btn btn-primary btn-sm" id="publish-empty-add">新增线路组</button></p></div>';
        },
        onDraw: function (_w, list) {
            countEl.textContent = list.length ? '· ' + list.length : '';
            var add = document.getElementById('publish-empty-add');
            if (add) add.addEventListener('click', function () { openDialog('add'); });
        },
        cols: [
            {title: '名称', html: function (d) { return '<a class="js-edit" href="#">' + U.escape(d.title || '未填写') + '</a>'; }},
            {title: '地址数', width: 80, html: function (d) { return U.escape(String(d.url_count == null ? 0 : d.url_count)); }},
            {title: '操作', cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-edit">编辑</a><a href="#" class="btn-link js-del">删除</a>';
            }}
        ]
    });
    function openDialog(mode, row) {
        row = row || {};
        U.dialog({
            wide: true,
            title: mode === 'edit' ? '编辑线路组' : '新增线路组',
            content: document.getElementById('publish-group-tpl').innerHTML,
            onOpen: function (body) {
                U.fillForm(body.querySelector('form'), {
                    id: mode === 'edit' ? (row.id || '') : '',
                    title: row.title || '',
                    hint: row.hint || '',
                    urls_text: row.urls_text || ''
                });
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (!data.title) { U.toast('请填写名称', 'err'); return false; }
                data.desk = 'groups';
                if (mode !== 'edit') delete data.id; else data.id = row.id;
                return U.post('/admin/video/publish_pages/save', data).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return false; }
                    U.toast('已保存', 'ok');
                    table.refresh();
                });
            }
        });
    }
    U.on('#publish-search-btn', 'click', function () { table.reload(queryWhere()); });
    U.on('#publish-reset-btn', 'click', function () { setTimeout(function () { table.reload(queryWhere()); }, 0); });
    U.on('#publish-add-btn', 'click', function () { openDialog('add'); });
    U.on('#publish-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-edit')) openDialog('edit', row);
        if (a.classList.contains('js-del')) {
            if (!U.confirm('确认删除？')) return;
            U.post('/admin/video/publish_pages/delete', {id: row.id, desk: 'groups'}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh();
                U.toast('已删除', 'ok');
            });
        }
    });
})();
</script>
@endpush
