@extends('admin.layouts.inner')
@section('title', $title)

@php
    $queues = $queues ?? ['all' => 0, 'header' => 0, 'footer' => 0, 'play' => 0, 'expired' => 0, 'off' => 0];
    $types = $types ?? [];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
@endphp

@section('plain')
<div class="card card-panel ad-index">
    <div class="card-header">
        <span>广告位 <em id="ad-count"></em></span>
        <button type="button" class="btn btn-sm" id="ad-add-btn">新增广告</button>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="ad-search" onsubmit="return false;">
            <input type="hidden" name="slot">
            <input type="hidden" name="expired">
            <input type="text" name="name" placeholder="搜名称或位置" autocomplete="off">
            <select name="status">
                <option value="">状态</option>
                <option value="1">启用</option>
                <option value="0">停用</option>
            </select>
            <button type="button" class="btn btn-sm" id="ad-search-btn">查询</button>
            <button type="reset" class="btn btn-muted btn-sm" id="ad-reset-btn">重置</button>
        </form>
        <div class="queue-chips" id="ad-queues">
            <button type="button" class="chip" data-queue="">全部@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="slot" data-value="header">页头@if($q('header') > 0)<em>{{ $q('header') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="slot" data-value="footer">页脚@if($q('footer') > 0)<em>{{ $q('footer') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="slot" data-value="play">播放页@if($q('play') > 0)<em>{{ $q('play') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="expired" data-value="1">已到期@if($q('expired') > 0)<em>{{ $q('expired') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="0">已停用@if($q('off') > 0)<em>{{ $q('off') }}</em>@endif</button>
        </div>
        <p class="muted recycle-lead">页头、页脚、播放器下插入的 HTML。主题按位置调用，例如 <code>@@vodAd(['slot' => 'header'])</code>。过期或停用后前台不再输出。</p>
        <div class="batch-bar" id="ad-batch" hidden>
            <strong id="ad-batch-count">已选 0 条</strong>
            <button type="button" class="btn btn-sm" id="ad-batch-on">启用</button>
            <button type="button" class="btn btn-muted btn-sm" id="ad-batch-off">停用</button>
            <select id="ad-batch-slot" class="batch-select" aria-label="目标位置">
                <option value="">改到位置</option>
                <option value="header">页头</option>
                <option value="footer">页脚</option>
                <option value="play">播放页</option>
            </select>
            <button type="button" class="btn btn-muted btn-sm" id="ad-batch-move">移动</button>
            <button type="button" class="btn btn-danger btn-sm" id="ad-batch-del">删除</button>
            <button type="button" class="btn btn-muted btn-sm" id="ad-batch-clear">取消选择</button>
        </div>
        <div id="ad-table"></div>
    </div>
</div>
<template id="ad-dialog-tpl">
    <form>
        <input type="hidden" name="id">
        <label>名称</label>
        <input type="text" name="name" placeholder="如 页头联盟、播放页横幅">
        <p class="muted field-hint">后台列表里看到的名字，前台不显示。</p>
        <label>位置</label>
        <select name="slot_pick">
            <option value="header">页头</option>
            <option value="footer">页脚</option>
            <option value="play">播放页</option>
            <option value="custom">自定义</option>
        </select>
        <input type="text" name="slot_custom" placeholder="英文字母开头，如 list" hidden autocomplete="off">
        <p class="muted field-hint ad-call-hint">默认主题：页头在导航旁，页脚在版权上，播放页在播放器下。</p>
        <label>调用</label>
        <div class="ad-call">
            <input type="text" id="ad-call-code" readonly>
            <button type="button" class="btn btn-muted btn-sm" id="ad-call-copy">复制</button>
        </div>
        <label>代码</label>
        <div class="field-inline">
            <span class="muted">图片或附件会写成标签插进代码。</span>
            <button type="button" class="btn btn-muted btn-sm" id="ad-insert-btn">插入图片</button>
        </div>
        <textarea name="content" class="ad-content" placeholder="HTML / 脚本，例如 &lt;a href=&quot;&quot;&gt;&lt;img src=&quot;&quot;&gt;&lt;/a&gt;"></textarea>
        <label>仅某分类</label>
        <select name="type_id">
            <option value="0">全部分类</option>
            @foreach($types as $t)
                <option value="{{ $t['id'] }}">{{ $t['name'] }}</option>
            @endforeach
        </select>
        <p class="muted field-hint">默认主题只按位置输出。主题调用时传入 type_id 才按分类过滤。</p>
        <label>到期</label>
        <input type="datetime-local" name="expire_at">
        <p class="muted field-hint">留空表示不过期。到期后前台不再输出，后台仍能改。</p>
        <label>排序</label>
        <input type="number" name="sort" value="0">
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
    var QUEUE_KEYS = ['slot', 'expired'];
    var KNOWN = {header: 1, footer: 1, play: 1};
    var form = document.getElementById('ad-search');
    var batchBar = document.getElementById('ad-batch');
    var batchCount = document.getElementById('ad-batch-count');
    var countEl = document.getElementById('ad-count');

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
        var slot = form.slot.value;
        var expired = form.expired.value;
        U.qa('#ad-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = false;
            if (key === '' && status === '' && slot === '' && expired === '') on = true;
            else if (key === 'slot' && status === '' && expired === '' && slot === val) on = true;
            else if (key === 'expired' && status === '' && slot === '' && expired === val) on = true;
            else if (key === 'status' && slot === '' && expired === '' && status === val) on = true;
            chip.classList.toggle('active', on);
        });
    }
    function applyQueue(key, value) {
        QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
        form.status.value = '';
        if (key === 'status') form.status.value = value || '';
        else if (key && form[key]) form[key].value = value || '';
        runSearch();
    }
    function runSearch() {
        table.reload(queryWhere());
        markChips();
    }
    function copyText(text) {
        if (!text) return Promise.resolve();
        if (navigator.clipboard && navigator.clipboard.writeText) {
            return navigator.clipboard.writeText(text).catch(function () {});
        }
        var ta = document.createElement('textarea');
        ta.value = text;
        document.body.appendChild(ta);
        ta.select();
        try { document.execCommand('copy'); } catch (e) {}
        document.body.removeChild(ta);
        return Promise.resolve();
    }
    function callCode(slot) {
        slot = String(slot || 'header');
        return "@@vodAd(['slot' => '" + slot + "'])";
    }
    function nameHtml(d) {
        var pic = String(d.preview_img || '').trim();
        var thumb = pic
            ? '<img class="ad-thumb" src="' + U.escape(pic) + '" alt="">'
            : '<span class="ad-thumb is-empty">代码</span>';
        var badges = '';
        if (String(d.status) !== '1') badges += '<span class="badge badge-off">停用</span>';
        if (d.is_expired) badges += '<span class="badge badge-off">已到期</span>';
        var meta = [U.escape(d.slot_label || d.slot || ''), U.escape(d.type_name || '全部分类'), U.escape(d.expire_text || '不过期')].join(' · ');
        var snip = d.preview_text ? '<div class="ad-snippet">' + U.escape(d.preview_text) + '</div>' : '';
        return '<div class="vod-cell">' + thumb + '<div><div class="entry-row-title-line"><a class="entry-row-title js-edit" href="#">' + U.escape(d.name || '未命名') + '</a> ' + badges + '</div>'
            + '<div class="entry-row-meta">' + meta + '</div>' + snip + '</div></div>';
    }

    var table = U.table({
        el: '#ad-table',
        url: '/admin/video/ads/list',
        where: queryWhere(),
        pager: false,
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>没有符合条件的广告</p><p><button type="button" class="btn btn-muted btn-sm" id="ad-empty-reset">清除筛选</button></p></div>';
            }
            return '<div class="list-empty"><p>还没有广告</p><p class="muted">广告是页头、页脚、播放页插入的 HTML。建好后主题用位置调用。</p><p><button type="button" class="btn btn-primary btn-sm" id="ad-empty-add">新增广告</button></p></div>';
        },
        onDraw: function (_wrap, list) {
            countEl.textContent = list.length ? '· ' + list.length : '';
            var add = document.getElementById('ad-empty-add');
            var reset = document.getElementById('ad-empty-reset');
            if (add) add.addEventListener('click', function () { openDialog('add'); });
            if (reset) reset.addEventListener('click', function () { form.reset(); runSearch(); });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = '已选 ' + ids.length + ' 条';
        },
        cols: [
            {check: true, width: 36},
            {title: '广告', html: nameHtml},
            {key: 'sort', title: '排序', width: 64},
            {title: '状态', width: 72, html: function (d) {
                if (d.is_expired) return U.status(false, '已到期');
                return String(d.status) === '1' ? U.status(true, '启用') : U.status(false, '停用');
            }},
            {title: '操作', cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-copy">复制调用</a><a href="#" class="btn-link js-edit">编辑</a><a href="#" class="btn-link js-del">删除</a>';
            }}
        ]
    });
    markChips();

    function insertAt(el, text) {
        if (!el || !text) return;
        el.focus();
        var start = el.selectionStart, end = el.selectionEnd, val = el.value;
        el.value = val.slice(0, start) + text + val.slice(end);
        el.selectionStart = el.selectionEnd = start + text.length;
    }
    function bindSlot(formEl) {
        var pick = formEl.querySelector('[name=slot_pick]');
        var custom = formEl.querySelector('[name=slot_custom]');
        var code = formEl.querySelector('#ad-call-code');
        function slot() {
            return pick.value === 'custom' ? String(custom.value || '').trim() : pick.value;
        }
        function sync() {
            custom.hidden = pick.value !== 'custom';
            code.value = callCode(slot() || 'header');
        }
        pick.addEventListener('change', sync);
        custom.addEventListener('input', sync);
        formEl._adSlot = slot;
        formEl._adSync = sync;
        sync();
    }
    function bindInsert(formEl) {
        var ta = formEl.querySelector('[name=content]');
        var btn = formEl.querySelector('#ad-insert-btn');
        btn.addEventListener('click', function () {
            U.pickFile('image/*').then(function (file) {
                if (!file) return;
                U.loading(true);
                return U.upload(file).then(function (res) {
                    U.loading(false);
                    if (res && res.code === 0 && res.data && res.data.url) {
                        insertAt(ta, '<a href="" target="_blank" rel="nofollow"><img src="' + res.data.url + '" alt=""></a>');
                        U.toast('已插入', 'ok');
                    } else U.toast((res && res.msg) || '上传失败', 'err');
                });
            });
        });
    }

    function openDialog(mode, row) {
        row = row || {};
        U.dialog({
            title: mode === 'edit' ? '编辑广告' : '新增广告',
            wide: true,
            content: document.getElementById('ad-dialog-tpl').innerHTML,
            onOpen: function (body) {
                var formEl = body.querySelector('form');
                var slot = String(row.slot || 'header');
                var known = !!KNOWN[slot];
                U.fillForm(formEl, {
                    id: mode === 'edit' ? (row.id || '') : '',
                    name: row.name || '',
                    content: row.content || '',
                    type_id: row.type_id == null ? 0 : row.type_id,
                    expire_at: row.expire_local || '',
                    sort: row.sort == null ? 0 : row.sort,
                    status: row.status == null ? '1' : String(row.status),
                    slot_pick: known ? slot : 'custom',
                    slot_custom: known ? '' : slot
                });
                bindSlot(formEl);
                bindInsert(formEl);
                body.querySelector('#ad-call-copy').addEventListener('click', function () {
                    copyText(body.querySelector('#ad-call-code').value).then(function () { U.toast('已复制', 'ok'); });
                });
            },
            onSave: function (body) {
                var formEl = body.querySelector('form');
                var data = U.formData(formEl);
                var slot = formEl._adSlot ? formEl._adSlot() : data.slot_pick;
                if (!data.name) { U.toast('请填写名称', 'err'); return false; }
                if (!slot) { U.toast('请填写位置', 'err'); return false; }
                data.slot = slot;
                delete data.slot_pick;
                delete data.slot_custom;
                if (mode !== 'edit') delete data.id; else data.id = row.id;
                return U.post('/admin/video/ads/save', data).then(function (res) {
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
        if (!ids.length) { U.toast('请先勾选广告', 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/ads/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '操作失败', 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || '操作成功', 'ok');
        });
    }

    U.on('#ad-search-btn', 'click', runSearch);
    U.on('#ad-reset-btn', 'click', function () { setTimeout(runSearch, 0); });
    U.on('#ad-add-btn', 'click', function () { openDialog('add'); });
    document.getElementById('ad-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#ad-batch-on', 'click', function () { batch('status', 1); });
    U.on('#ad-batch-off', 'click', function () { batch('status', 0); });
    U.on('#ad-batch-move', 'click', function () {
        var val = document.getElementById('ad-batch-slot').value;
        if (!val) { U.toast('请先选择位置，再点「移动」', 'err'); return; }
        batch('slot', val);
    });
    U.on('#ad-batch-del', 'click', function () { batch('delete', '', '确认删除选中广告？主题将取不到这些代码。'); });
    U.on('#ad-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#ad-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-copy')) {
            copyText(callCode(row.slot)).then(function () { U.toast('已复制调用代码', 'ok'); });
        }
        if (a.classList.contains('js-edit')) openDialog('edit', row);
        if (a.classList.contains('js-del')) {
            if (!U.confirm('删除广告「' + (row.name || '') + '」？')) return;
            U.post('/admin/video/ads/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh();
                U.toast('已删除', 'ok');
            });
        }
    });
})();
</script>
@endpush
