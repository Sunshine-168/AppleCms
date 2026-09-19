@extends('admin.layouts.inner')
@section('title', $title ?? admin_t('page.domains'))

@php
    $queues = $queues ?? ['all' => 0, 'on' => 0, 'off' => 0, 'current' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
    $themes = is_array($themes ?? null) ? $themes : [];
    $currentHost = trim((string) ($currentHost ?? ''));
@endphp

@section('plain')
<div class="card card-panel domain-index" id="domain-index">
    <div class="card-header">
        <span>绑定域名 <em id="domain-count"></em></span>
        <div>
            <button type="button" class="btn btn-sm" id="domain-add-btn">新增绑定</button>
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">同一套片库。按访问域名换站名和模板；没绑的走站点设置。不是独立分站库。@if($currentHost !== '') 当前访问 <code>{{ $currentHost }}</code>。@endif</p>
        <form class="filter-bar" id="domain-search" onsubmit="return false;">
            <input type="hidden" name="current">
            <input type="hidden" name="status">
            <input type="search" name="q" placeholder="搜域名" autocomplete="off" aria-label="搜索绑定域名">
            <button type="button" class="btn btn-sm" id="domain-search-btn">查询</button>
            <button type="reset" class="btn btn-muted btn-sm" id="domain-reset-btn">重置</button>
        </form>
        <div class="queue-chips" id="domain-queues">
            <button type="button" class="chip" data-queue="">全部@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="1">启用@if($q('on') > 0)<em>{{ $q('on') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="0">停用@if($q('off') > 0)<em>{{ $q('off') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="current" data-value="1">当前访问@if($q('current') > 0)<em>{{ $q('current') }}</em>@endif</button>
        </div>
        <div class="batch-bar" id="domain-batch" hidden>
            <strong id="domain-batch-count">已选 0 个</strong>
            <button type="button" class="btn btn-sm" id="domain-batch-on">启用</button>
            <button type="button" class="btn btn-muted btn-sm" id="domain-batch-off">停用</button>
            <button type="button" class="btn btn-danger btn-sm" id="domain-batch-del">删除</button>
            <button type="button" class="btn btn-muted btn-sm" id="domain-batch-clear">取消选择</button>
        </div>
        <div id="domain-table"></div>
    </div>
</div>
<template id="domain-dialog-tpl">
    <form>
        <input type="hidden" name="id">
        <label>域名</label>
        <input type="text" name="host" placeholder="如 example.com" required>
        <p class="muted field-hint">不要带 http 和路径。www 和裸域会当成同一个。IP 和 localhost 也可以。</p>
        <label>模板</label>
        <select name="theme">
            <option value="">跟站点设置</option>
            @foreach($themes as $theme)
                <option value="{{ $theme['name'] }}">{{ $theme['title'] }}</option>
            @endforeach
        </select>
        <p class="muted field-hint">只换这一套片库的外观，不另开数据库。</p>
        <label>站点名</label>
        <input type="text" name="site_name" placeholder="可空，跟站点设置">
        <label>关键词</label>
        <input type="text" name="site_keyword" placeholder="可空">
        <label>描述</label>
        <textarea name="site_description" placeholder="可空"></textarea>
        <label>备注</label>
        <input type="text" name="remark" placeholder="仅后台看见，可空">
        <label>状态</label>
        <select name="status">
            <option value="1">启用</option>
            <option value="0">停用</option>
        </select>
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var QUEUE_KEYS = ['current'];
    var form = document.getElementById('domain-search');
    var batchBar = document.getElementById('domain-batch');
    var batchCount = document.getElementById('domain-batch-count');
    var countEl = document.getElementById('domain-count');
    var currentHost = @json($currentHost);

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
        var current = form.current.value;
        U.qa('#domain-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = false;
            if (key === '' && status === '' && current === '') on = true;
            else if (key === 'status' && current === '' && status === val) on = true;
            else if (key === 'current' && status === '' && current === val) on = true;
            chip.classList.toggle('active', on);
        });
    }
    function applyQueue(key, value) {
        QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
        form.status.value = '';
        if (key === 'status') form.status.value = value || '';
        else if (key && form[key]) form[key].value = value || '1';
        runSearch();
    }
    function runSearch() {
        table.reload(queryWhere());
        markChips();
    }
    function hostIsIp(host) {
        host = String(host || '');
        if (/^\d{1,3}(?:\.\d{1,3}){3}$/.test(host)) return true;
        if (host.indexOf(':') !== -1) return true;
        return false;
    }
    function hostHtml(d) {
        var host = String(d.host || '');
        var badge = '';
        if (d.is_current) badge += '<span class="badge">当前访问</span>';
        if (String(d.status) !== '1') badge += '<span class="badge badge-off">停用</span>';
        return '<div><div class="entry-row-title-line"><a class="entry-row-title js-edit" href="#">' + U.escape(host || '未填写') + '</a> ' + badge + '</div>'
            + (d.remark ? '<div class="entry-row-meta">' + U.escape(d.remark) + '</div>' : '') + '</div>';
    }
    function themeHtml(d) {
        var label = d.theme_label || '跟站点设置';
        if (d.theme_missing) return '<span class="muted">' + U.escape(label) + '（目录不在）</span>';
        return U.escape(label);
    }

    var table = U.table({
        el: '#domain-table',
        queueKeys: QUEUE_KEYS,
        countEl: countEl,
        url: '/admin/video/domains/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>没有符合条件的域名</p><p><button type="button" class="btn btn-muted btn-sm" id="domain-empty-reset">清除筛选</button></p></div>';
            }
            return '<div class="list-empty"><p>还没有绑定域名</p><p class="muted">点新增绑定，填访问域名。同一套片库，只换站名和模板。没绑的走站点设置。</p><p><button type="button" class="btn btn-primary btn-sm" id="domain-empty-add">新增绑定</button></p></div>';
        },
        onDraw: function (_wrap, list) {
            var add = document.getElementById('domain-empty-add');
            var reset = document.getElementById('domain-empty-reset');
            if (add) add.addEventListener('click', function () { openDialog('add'); });
            if (reset) reset.addEventListener('click', function () { form.reset(); runSearch(); });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = '已选 ' + ids.length + ' 个';
        },
        cols: [
            {check: true, width: 36},
            {title: '域名', html: hostHtml},
            {title: '站点名', html: function (d) { return U.escape(d.site_name_label || '跟站点设置'); }},
            {title: '模板', html: themeHtml},
            {title: '状态', width: 72, html: function (d) {
                return String(d.status) === '1' ? U.status(true, '启用') : U.status(false, '停用');
            }},
            {title: '操作', cls: 'actions', html: function (d) {
                var html = '';
                var host = String(d.host || '');
                if (host && !hostIsIp(host)) {
                    html += '<a href="http://' + U.escape(host) + '" target="_blank" rel="noopener" class="btn-link">打开</a>';
                }
                html += '<a href="#" class="btn-link js-edit">编辑</a>';
                html += '<a href="#" class="btn-link js-del">删除</a>';
                return html;
            }}
        ]
    });
    markChips();

    function openDialog(mode, row) {
        row = row || {};
        U.dialog({
            title: mode === 'edit' ? '编辑绑定' : '新增绑定',
            content: document.getElementById('domain-dialog-tpl').innerHTML,
            onOpen: function (body) {
                U.fillForm(body.querySelector('form'), {
                    id: mode === 'edit' ? (row.id || '') : '',
                    host: mode === 'edit' ? (row.host || '') : (currentHost || ''),
                    theme: row.theme || '',
                    site_name: row.site_name || '',
                    site_keyword: row.site_keyword || '',
                    site_description: row.site_description || '',
                    remark: row.remark || '',
                    status: row.status == null ? '1' : String(row.status)
                });
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (!data.host) { U.toast('请填写域名', 'err'); return false; }
                if (mode !== 'edit') delete data.id; else data.id = row.id;
                return U.post('/admin/video/domains/save', data).then(function (res) {
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
        if (!ids.length) { U.toast('请先勾选域名', 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/domains/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '操作失败', 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || '操作成功', 'ok');
        });
    }

    U.on('#domain-search-btn', 'click', runSearch);
    U.on('#domain-reset-btn', 'click', function () { setTimeout(runSearch, 0); });
    U.on('#domain-add-btn', 'click', function () { openDialog('add'); });
    document.getElementById('domain-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#domain-batch-on', 'click', function () { batch('status', 1); });
    U.on('#domain-batch-off', 'click', function () { batch('status', 0); });
    U.on('#domain-batch-del', 'click', function () { batch('delete', '', '确认删除选中绑定？片库还在，只是这个域名不再换站名和模板。'); });
    U.on('#domain-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#domain-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        if (a.target === '_blank') return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-edit')) openDialog('edit', row);
        if (a.classList.contains('js-del')) {
            if (!U.confirm('删除绑定「' + (row.host || '') + '」？')) return;
            U.post('/admin/video/domains/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh();
                U.toast('已删除', 'ok');
            });
        }
    });
})();
</script>
@endpush
