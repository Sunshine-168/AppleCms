@extends('admin.layouts.inner')
@section('title', $title ?? admin_t('page.synonyms'))

@php
    $queues = $queues ?? ['all' => 0, 'on' => 0, 'off' => 0, 'empty_to' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
@endphp

@section('plain')
<div class="card card-panel synonym-index" id="synonym-index">
    <div class="card-header">
        <span>同义词 <em id="syn-count"></em></span>
        <div>
            <button type="button" class="btn btn-sm" id="syn-add-btn">新增规则</button>
            <a class="btn btn-muted btn-sm" href="/admin/video/searchwords">搜索词</a>
            <a class="btn btn-muted btn-sm" href="/search" target="_blank" rel="noopener">前台搜索</a>
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">搜到原词时按当成的词去查。采集入库的片名也会换。已经在片库里的名字不会改。热搜在「搜索词」。</p>
        <form class="filter-bar" id="syn-search" onsubmit="return false;">
            <input type="hidden" name="empty_to">
            <input type="search" name="q" placeholder="搜原词或当成的词" autocomplete="off" aria-label="搜索同义词">
            <select name="status">
                <option value="">状态</option>
                <option value="1">启用</option>
                <option value="0">停用</option>
            </select>
            <button type="button" class="btn btn-sm" id="syn-search-btn">查询</button>
            <button type="reset" class="btn btn-muted btn-sm" id="syn-reset-btn">重置</button>
        </form>
        <div class="queue-chips" id="syn-queues">
            <button type="button" class="chip" data-queue="">全部@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="1">启用@if($q('on') > 0)<em>{{ $q('on') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="0">停用@if($q('off') > 0)<em>{{ $q('off') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="empty_to" data-value="1">当成词空着@if($q('empty_to') > 0)<em>{{ $q('empty_to') }}</em>@endif</button>
        </div>
        <form class="filter-bar syn-try-bar" id="syn-try" onsubmit="return false;">
            <input type="search" name="kw" placeholder="填一个词，看启用规则会不会换成别的" autocomplete="off" aria-label="试同义词">
            <button type="button" class="btn btn-muted btn-sm" id="syn-try-btn">试一下</button>
            <span class="muted" id="syn-try-out"></span>
        </form>
        <p class="muted field-hint">试的是已经保存并且启用的规则，按原词从长到短依次替换。停用的不算。未保存的草稿也不算。</p>
        <div class="batch-bar" id="syn-batch" hidden>
            <strong id="syn-batch-count">已选 0 条</strong>
            <button type="button" class="btn btn-sm" id="syn-batch-on">启用</button>
            <button type="button" class="btn btn-muted btn-sm" id="syn-batch-off">停用</button>
            <button type="button" class="btn btn-danger btn-sm" id="syn-batch-del">删除</button>
            <button type="button" class="btn btn-muted btn-sm" id="syn-batch-clear">取消选择</button>
        </div>
        <div id="syn-table"></div>
    </div>
</div>
<template id="syn-dialog-tpl">
    <form>
        <input type="hidden" name="id">
        <label>原词</label>
        <input type="text" name="from_word" maxlength="80" placeholder="访客会搜的那个词" required>
        <label>当成</label>
        <input type="text" name="to_word" maxlength="80" placeholder="实际拿去查片的词" required>
        <p class="muted field-hint">两边不能一样。同一个原词只能有一条。当成的词不能空，否则搜索会被掏空。</p>
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
    var QUEUE_KEYS = ['empty_to'];
    var form = document.getElementById('syn-search');
    var tryForm = document.getElementById('syn-try');
    var tryOut = document.getElementById('syn-try-out');
    var batchBar = document.getElementById('syn-batch');
    var batchCount = document.getElementById('syn-batch-count');
    var countEl = document.getElementById('syn-count');

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
        var emptyTo = form.empty_to.value;
        U.qa('#syn-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = false;
            if (key === '' && status === '' && emptyTo === '') on = true;
            else if (key === 'status' && emptyTo === '' && status === val) on = true;
            else if (key === 'empty_to' && status === '' && emptyTo === val) on = true;
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
    function wordHtml(d) {
        var badge = d.is_on ? '' : '<span class="badge badge-off">停用</span>';
        var preview = d.preview || ((d.from_word || '') + ' → ' + (d.to_word || ''));
        var meta = d.empty_to ? '当成的词是空的，启用后会把搜索掏空' : (d.is_on ? '搜索和采集会换' : '停用，暂不生效');
        return '<div><div class="entry-row-title-line"><a class="entry-row-title js-edit" href="#">' + U.escape(preview) + '</a> ' + badge + '</div>'
            + '<div class="entry-row-meta">' + meta + '</div></div>';
    }

    var table = U.table({
        el: '#syn-table',
        url: '/admin/video/synonyms/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>没有符合条件的同义词</p><p><button type="button" class="btn btn-muted btn-sm" id="syn-empty-reset">清除筛选</button></p></div>';
            }
            return '<div class="list-empty"><p>还没有同义词</p><p class="muted">点新增，把访客常搜的错名、别名换成片库里的词。不会改已经入库的片名。</p><p><button type="button" class="btn btn-primary btn-sm" id="syn-empty-add">新增规则</button> <a class="btn btn-muted btn-sm" href="/admin/video/searchwords">去搜索词</a></p></div>';
        },
        onDraw: function (_wrap, list) {
            countEl.textContent = list.length ? '· ' + list.length : '';
            var add = document.getElementById('syn-empty-add');
            var reset = document.getElementById('syn-empty-reset');
            if (add) add.addEventListener('click', function () { openDialog('add'); });
            if (reset) reset.addEventListener('click', function () { form.reset(); runSearch(); });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = '已选 ' + ids.length + ' 条';
        },
        cols: [
            {check: true, width: 36},
            {title: '规则', html: wordHtml},
            {title: '原词', width: 140, html: function (d) { return U.escape(d.from_word || ''); }},
            {title: '当成', width: 140, html: function (d) { return d.empty_to ? '<span class="muted">空</span>' : U.escape(d.to_word || ''); }},
            {title: '状态', width: 72, html: function (d) {
                return d.is_on ? U.status(true, '启用') : U.status(false, '停用');
            }},
            {title: '操作', cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-edit">编辑</a><a href="#" class="btn-link js-del">删除</a>';
            }}
        ]
    });
    markChips();

    function openDialog(mode, row) {
        row = row || {};
        U.dialog({
            title: mode === 'edit' ? '编辑规则' : '新增规则',
            content: document.getElementById('syn-dialog-tpl').innerHTML,
            onOpen: function (body) {
                U.fillForm(body.querySelector('form'), {
                    id: mode === 'edit' ? (row.id || '') : '',
                    from_word: row.from_word || '',
                    to_word: row.to_word || '',
                    status: row.status == null ? '1' : String(row.status)
                });
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (!data.from_word) { U.toast('请填写原词', 'err'); return false; }
                if (!data.to_word) { U.toast('请填写要当成的词', 'err'); return false; }
                if (data.from_word === data.to_word) { U.toast('原词和当成的词不能一样', 'err'); return false; }
                if (mode !== 'edit') delete data.id; else data.id = row.id;
                return U.post('/admin/video/synonyms/save', data).then(function (res) {
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
        if (!ids.length) { U.toast('请先勾选同义词', 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/synonyms/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '操作失败', 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || '操作成功', 'ok');
        });
    }

    U.on('#syn-search-btn', 'click', runSearch);
    U.on('#syn-reset-btn', 'click', function () { setTimeout(runSearch, 0); });
    U.on('#syn-add-btn', 'click', function () { openDialog('add'); });
    U.on('#syn-try-btn', 'click', function () {
        var kw = String((U.formData(tryForm).kw || '')).trim();
        if (!kw) { U.toast('请填一个词试试', 'err'); return; }
        tryOut.textContent = '…';
        U.post('/admin/video/synonyms/try', {kw: kw}).then(function (res) {
            tryOut.textContent = (res && res.msg) || '失败';
            if (!res || res.code !== 0) U.toast((res && res.msg) || '失败', 'err');
        });
    });
    document.getElementById('syn-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#syn-batch-on', 'click', function () { batch('status', 1); });
    U.on('#syn-batch-off', 'click', function () { batch('status', 0); });
    U.on('#syn-batch-del', 'click', function () { batch('delete', '', '确认删除选中规则？片子名字不会改。'); });
    U.on('#syn-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#syn-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-edit')) openDialog('edit', row);
        if (a.classList.contains('js-del')) {
            if (!U.confirm('删除规则「' + (row.from_word || '') + '」？片子名字不会改。')) return;
            U.post('/admin/video/synonyms/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh();
                U.toast('已删除', 'ok');
            });
        }
    });
})();
</script>
@endpush
