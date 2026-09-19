@extends('admin.layouts.inner')
@section('title', $title ?? admin_t('page.websites'))

@php
    $queues = $queues ?? ['all' => 0, 'on' => 0, 'off' => 0, 'no_type' => 0, 'logo' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
    $types = is_array($types ?? null) ? $types : [];
    $typeId = (int) ($typeId ?? 0);
    $flinkUrl = trim((string) ($flinkUrl ?? '/admin/video/links')) ?: '/admin/video/links';
    $typeUrl = '/admin/video/website-types';
@endphp

@section('plain')
<div class="card card-panel website-index" id="website-index">
    <div class="card-header">
        <span>网址导航 <em id="website-count"></em></span>
        <div>
            <button type="button" class="btn btn-sm" id="website-add-btn">新增站点</button>
            <a class="btn btn-muted btn-sm" href="{{ $flinkUrl }}">友情链接</a>
            <a class="btn btn-muted btn-sm" href="{{ $typeUrl }}">分类</a>
            <a class="btn btn-muted btn-sm" href="/website" target="_blank" rel="noopener">看前台</a>
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">顶栏「导航」的站外目录，可按分类分组。友情链接只出现在页脚，不是同一张表。</p>
        @if($types === [])
            <p class="muted field-hint">还没有导航分类。不分组也能加站点；要分组先<a href="{{ $typeUrl }}/create">新建导航分类</a>。</p>
        @endif
        <form class="filter-bar" id="website-search" onsubmit="return false;">
            <input type="hidden" name="empty_type">
            <input type="hidden" name="logo">
            <input type="search" name="q" placeholder="搜名称、网址或简介" autocomplete="off" aria-label="搜索网址导航">
            @if($types !== [])
                <select name="type_id">
                    <option value="">分类</option>
                    @foreach($types as $type)
                        <option value="{{ (int) $type['id'] }}" @selected($typeId === (int) $type['id'])>{{ $type['name'] }}</option>
                    @endforeach
                </select>
            @else
                <input type="hidden" name="type_id" value="{{ $typeId > 0 ? $typeId : '' }}">
            @endif
            <select name="status">
                <option value="">状态</option>
                <option value="1">显示</option>
                <option value="0">隐藏</option>
            </select>
            <button type="button" class="btn btn-sm" id="website-search-btn">{{ admin_t('ui.search') }}</button>
            <button type="reset" class="btn btn-muted btn-sm" id="website-reset-btn">{{ admin_t('ui.reset') }}</button>
        </form>
        <div class="queue-chips" id="website-queues">
            <button type="button" class="chip" data-queue="">全部@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="1">显示中@if($q('on') > 0)<em>{{ $q('on') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="0">已隐藏@if($q('off') > 0)<em>{{ $q('off') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="empty_type" data-value="1">没挂分类@if($q('no_type') > 0)<em>{{ $q('no_type') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="logo" data-value="1">有图@if($q('logo') > 0)<em>{{ $q('logo') }}</em>@endif</button>
        </div>
        <div class="batch-bar" id="website-batch" hidden>
            <strong id="website-batch-count">已选 0 个</strong>
            <button type="button" class="btn btn-sm" id="website-batch-on">显示</button>
            <button type="button" class="btn btn-muted btn-sm" id="website-batch-off">隐藏</button>
            <button type="button" class="btn btn-danger btn-sm" id="website-batch-del">删除</button>
            <button type="button" class="btn btn-muted btn-sm" id="website-batch-clear">取消选择</button>
        </div>
        <div id="website-table"></div>
    </div>
</div>
<template id="website-dialog-tpl">
    <form>
        <input type="hidden" name="id">
        <label>站点名称</label>
        <input class="entry-title" type="text" name="name" placeholder="如 某某资源站" required autofocus>
        <p class="muted field-hint">出现在前台「导航」列表里。</p>
        <label>网址</label>
        <input type="text" name="url" placeholder="https://" required>
        <p class="muted field-hint">没写协议会自动加上 https://。点开会在新窗口。不是页脚友链。</p>
        @if($types !== [])
            <label>分类</label>
            <select name="type_id">
                <option value="0">不分组</option>
                @foreach($types as $type)
                    <option value="{{ (int) $type['id'] }}">{{ $type['name'] }}</option>
                @endforeach
            </select>
            <p class="muted field-hint">只列出导航分类。没有合适的？去<a href="{{ $typeUrl }}/create" target="_blank" rel="noopener">新建</a>。</p>
        @else
            <input type="hidden" name="type_id" value="0">
        @endif
        <label>Logo</label>
        <div class="field-inline">
            <input type="text" name="logo" placeholder="可空，可粘贴或上传">
            <button type="button" class="btn btn-muted website-logo-upload-btn">上传</button>
        </div>
        <img class="img-preview link-logo-preview" alt="">
        <label>简介</label>
        <input type="text" name="blurb" placeholder="一两句，可空">
        <div class="admin-dialog-grid">
            <div>
                <label>排序</label>
                <input type="number" name="sort" value="0">
            </div>
            <div>
                <label>人气</label>
                <input type="number" name="hits" value="0" min="0">
            </div>
        </div>
        <p class="muted field-hint">前台「打开」会累加人气。排序数字越大越靠前。</p>
        <label>状态</label>
        <select name="status">
            <option value="1">显示</option>
            <option value="0">隐藏</option>
        </select>
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var QUEUE_KEYS = ['empty_type', 'logo'];
    var form = document.getElementById('website-search');
    var batchBar = document.getElementById('website-batch');
    var batchCount = document.getElementById('website-batch-count');
    var countEl = document.getElementById('website-count');
    var prefillType = @json($typeId > 0 ? $typeId : 0);
    var flinkUrl = @json($flinkUrl);
    var typeUrl = @json($typeUrl);

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
        var active = '';
        QUEUE_KEYS.forEach(function (k) {
            if (form[k] && form[k].value === '1') active = k;
        });
        U.qa('#website-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = false;
            if (key === '' && !active && status === '') on = true;
            else if (key === 'status' && !active && status === val) on = true;
            else if (key && key !== 'status' && active === key) on = true;
            chip.classList.toggle('active', on);
        });
    }
    function applyQueue(key, value) {
        QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
        if (key === '') {
            form.status.value = '';
            if (form.type_id) form.type_id.value = prefillType ? String(prefillType) : '';
        } else if (key === 'status') {
            form.status.value = value || '';
        } else {
            form.status.value = '';
            if (key === 'empty_type' && form.type_id) form.type_id.value = '';
            if (key && form[key]) form[key].value = value || '1';
        }
        runSearch();
    }
    function runSearch() {
        table.reload(queryWhere());
        markChips();
    }
    function nameHtml(d) {
        var logo = String(d.logo || '').trim();
        var letter = String(d.name || '?').slice(0, 1);
        var thumb = logo
            ? '<img class="link-thumb" src="' + U.escape(logo) + '" alt="">'
            : '<span class="link-thumb is-empty">' + U.escape(letter) + '</span>';
        var badge = String(d.status) === '1' ? '' : '<span class="badge badge-off">隐藏</span>';
        var meta = d.blurb ? U.escape(d.blurb) : U.escape(d.url || '');
        return '<div class="vod-cell">' + thumb + '<div><div class="entry-row-title-line"><a class="entry-row-title js-edit" href="#">' + U.escape(d.name || '未命名') + '</a> ' + badge + '</div>'
            + '<div class="entry-row-meta">' + meta + '</div></div></div>';
    }
    function typeHtml(d) {
        var tid = parseInt(d.type_id, 10) || 0;
        if (tid < 1) return '<span class="muted">未分组</span>';
        if (d.type_missing) return '<span class="muted">分类已删 #' + tid + '</span>';
        if (d.type_wrong) return '<span class="muted">不是导航分类</span>';
        return U.escape(d.type_name || ('分类 #' + tid));
    }

    var table = U.table({
        el: '#website-table',
        queueKeys: QUEUE_KEYS,
        countEl: countEl,
        url: '/admin/video/websites/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>没有符合条件的站点</p><p><button type="button" class="btn btn-muted btn-sm" id="website-empty-reset">清除筛选</button></p></div>';
            }
            return '<div class="list-empty"><p>还没有网址导航</p><p class="muted">点新增，填名称和网址。会出现在前台顶栏「导航」，不会进页脚。页脚交换请去友情链接。</p><p><button type="button" class="btn btn-primary btn-sm" id="website-empty-add">新增站点</button> <a class="btn btn-muted btn-sm" href="' + typeUrl + '/create">新建导航分类</a> <a class="btn btn-muted btn-sm" href="' + flinkUrl + '">去友情链接</a></p></div>';
        },
        onDraw: function (_wrap, list) {
            var add = document.getElementById('website-empty-add');
            var reset = document.getElementById('website-empty-reset');
            if (add) add.addEventListener('click', function () { openDialog('add'); });
            if (reset) reset.addEventListener('click', function () {
                form.reset();
                if (prefillType && form.type_id) form.type_id.value = String(prefillType);
                runSearch();
            });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = '已选 ' + ids.length + ' 个';
        },
        cols: [
            {check: true, width: 36},
            {title: '站点', html: nameHtml},
            {title: '分类', width: 140, html: typeHtml},
            {key: 'hits', title: '人气', width: 72, html: function (d) { return U.escape(String(d.hits == null ? 0 : d.hits)); }},
            {key: 'sort', title: '排序', width: 64},
            {title: '状态', width: 72, html: function (d) {
                return String(d.status) === '1' ? U.status(true, '显示') : U.status(false, '隐藏');
            }},
            {title: '操作', cls: 'actions', html: function (d) {
                var html = '';
                if (d.front_url) html += '<a href="' + U.escape(d.front_url) + '" target="_blank" rel="noopener" class="btn-link">前台</a>';
                if (d.url) html += '<a href="' + U.escape(d.url) + '" target="_blank" rel="noopener noreferrer" class="btn-link">打开</a>';
                html += '<a href="#" class="btn-link js-edit">编辑</a>';
                html += '<a href="#" class="btn-link js-del">删除</a>';
                return html;
            }}
        ]
    });
    markChips();

    function bindLogo(formEl) {
        U.bindImageField(formEl, {
            input: 'input[name=logo]',
            btn: '.website-logo-upload-btn',
            preview: '.link-logo-preview'
        });
    }

    function openDialog(mode, row) {
        row = row || {};
        U.dialog({
            wide: true,
            title: mode === 'edit' ? '编辑站点' : '新增站点',
            content: document.getElementById('website-dialog-tpl').innerHTML,
            onOpen: function (body) {
                var formEl = body.querySelector('form');
                var typeVal = mode === 'edit' ? (row.type_id || 0) : (row.type_id || prefillType || 0);
                U.fillForm(formEl, {
                    id: mode === 'edit' ? (row.id || '') : '',
                    name: row.name || '',
                    url: row.url || '',
                    type_id: String(typeVal || 0),
                    logo: row.logo || '',
                    blurb: row.blurb || '',
                    sort: row.sort == null ? 0 : row.sort,
                    hits: row.hits == null ? 0 : row.hits,
                    status: row.status == null ? '1' : String(row.status)
                });
                bindLogo(formEl);
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (!data.name) { U.toast('请填写站点名称', 'err'); return false; }
                if (!data.url) { U.toast('请填写网址', 'err'); return false; }
                if (mode !== 'edit') delete data.id; else data.id = row.id;
                return U.post('/admin/video/websites/save', data).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return false; }
                    U.toast(mode === 'edit' ? '已保存' : '已创建', 'ok');
                    table.refresh();
                });
            }
        });
    }

    function selectedIds() { return table.selectedIds(); }
    function batch(action, value, confirmText) {
        var ids = selectedIds();
        if (!ids.length) { U.toast('请先勾选站点', 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/websites/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '操作失败', 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || '操作成功', 'ok');
        });
    }

    U.on('#website-search-btn', 'click', runSearch);
    U.on('#website-reset-btn', 'click', function () {
        setTimeout(function () {
            if (prefillType && form.type_id) form.type_id.value = String(prefillType);
            runSearch();
        }, 0);
    });
    U.on('#website-add-btn', 'click', function () { openDialog('add'); });
    document.getElementById('website-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#website-batch-on', 'click', function () { batch('status', 1); });
    U.on('#website-batch-off', 'click', function () { batch('status', 0); });
    U.on('#website-batch-del', 'click', function () { batch('delete', '', '确认删除选中站点？前台导航将不再列出。'); });
    U.on('#website-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#website-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        if (a.target === '_blank') return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-edit')) openDialog('edit', row);
        if (a.classList.contains('js-del')) {
            if (!U.confirm('删除站点「' + (row.name || '') + '」？')) return;
            U.post('/admin/video/websites/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh();
                U.toast('已删除', 'ok');
            });
        }
    });
})();
</script>
@endpush
