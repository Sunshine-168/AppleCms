@extends('admin.layouts.inner')
@section('title', $title)

@php
    $queues = $queues ?? ['all' => 0, 'artplayer' => 0, 'dplayer' => 0, 'videojs' => 0, 'iframe' => 0, 'off' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
@endphp

@section('plain')
<div class="card card-panel player-index">
    <div class="card-header">
        <span>播放器 <em id="player-count"></em></span>
        <div>
            <button type="button" class="btn btn-muted btn-sm" id="player-ensure-btn">补齐内置</button>
            <button type="button" class="btn btn-sm" id="player-add-btn">新增播放器</button>
        </div>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="player-search" onsubmit="return false;">
            <input type="hidden" name="engine">
            <input type="text" name="name" placeholder="搜名称或标识" autocomplete="off">
            <select name="status">
                <option value="">状态</option>
                <option value="1">启用</option>
                <option value="0">停用</option>
            </select>
            <button type="button" class="btn btn-sm" id="player-search-btn">查询</button>
            <button type="reset" class="btn btn-muted btn-sm" id="player-reset-btn">重置</button>
        </form>
        <div class="queue-chips" id="player-queues">
            <button type="button" class="chip" data-queue="">全部@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="engine" data-value="artplayer">ArtPlayer @if($q('artplayer') > 0)<em>{{ $q('artplayer') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="engine" data-value="dplayer">DPlayer @if($q('dplayer') > 0)<em>{{ $q('dplayer') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="engine" data-value="videojs">Video.js @if($q('videojs') > 0)<em>{{ $q('videojs') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="engine" data-value="iframe">解析 @if($q('iframe') > 0)<em>{{ $q('iframe') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="0">已停用@if($q('off') > 0)<em>{{ $q('off') }}</em>@endif</button>
        </div>
        <p class="muted recycle-lead">线路上的「播放器」字段要对上这里的标识。直链 m3u8 / mp4 / flv 用开源内核；加密地址才填解析。批量换线路在「<a href="/admin/video/tools/players">批量播放器</a>」。</p>
        <div class="player-engines">
            <article>
                <strong>ArtPlayer</strong>
                <p>默认推荐。MIT，维护活跃，直链 HLS / FLV / MP4，界面好改。</p>
            </article>
            <article>
                <strong>DPlayer</strong>
                <p>苹果 CMS 常用。自带弹幕，适合已有 DPlayer 线路。</p>
            </article>
            <article>
                <strong>Video.js</strong>
                <p>兼容性最好，内置 HLS。体积更大，适合稳妥直链。</p>
            </article>
            <article>
                <strong>解析接口</strong>
                <p>iframe 打开解析页。地址里可用 <code>{url}</code> <code>{id}</code>。</p>
            </article>
        </div>
        <div class="batch-bar" id="player-batch" hidden>
            <strong id="player-batch-count">已选 0 个</strong>
            <button type="button" class="btn btn-sm" id="player-batch-on">启用</button>
            <button type="button" class="btn btn-muted btn-sm" id="player-batch-off">停用</button>
            <select id="player-batch-engine" class="batch-select" aria-label="目标内核">
                <option value="">改到内核</option>
                <option value="artplayer">ArtPlayer</option>
                <option value="dplayer">DPlayer</option>
                <option value="videojs">Video.js</option>
                <option value="iframe">解析接口</option>
            </select>
            <button type="button" class="btn btn-muted btn-sm" id="player-batch-move">更换</button>
            <button type="button" class="btn btn-danger btn-sm" id="player-batch-del">删除</button>
            <button type="button" class="btn btn-muted btn-sm" id="player-batch-clear">取消选择</button>
        </div>
        <div id="player-table"></div>
    </div>
</div>
<template id="player-dialog-tpl">
    <form>
        <input type="hidden" name="id">
        <label>名称</label>
        <input type="text" name="name" placeholder="如 ArtPlayer 直链">
        <p class="muted field-hint">后台列表里看到的名字。</p>
        <label>标识</label>
        <input type="text" name="code" placeholder="artplayer">
        <p class="muted field-hint">要和影片线路上的播放器字段一致，采集来的常见 dplayer、parse。</p>
        <label>内核</label>
        <select name="engine">
            <option value="artplayer">ArtPlayer 直链</option>
            <option value="dplayer">DPlayer 直链</option>
            <option value="videojs">Video.js 直链</option>
            <option value="iframe">解析接口 / iframe</option>
        </select>
        <div id="player-parse-wrap">
            <label>解析地址</label>
            <textarea name="parse" class="player-parse" placeholder="https://jx.example.com/?url={url}"></textarea>
            <p class="muted field-hint">仅解析内核需要。可用 <code>{url}</code>、<code>{id}</code>。直链内核请留空。</p>
        </div>
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
    var QUEUE_KEYS = ['engine'];
    var form = document.getElementById('player-search');
    var batchBar = document.getElementById('player-batch');
    var batchCount = document.getElementById('player-batch-count');
    var countEl = document.getElementById('player-count');

    function cleanWhere(data) {
        var out = {};
        Object.keys(data).forEach(function (k) { if (data[k] !== '') out[k] = data[k]; });
        return out;
    }
    function queryWhere() {
        return Object.assign({limit: 30}, cleanWhere(U.formData(form)));
    }
    function isFiltered(where) {
        return Object.keys(where || {}).some(function (k) { return k !== 'limit' && where[k] !== ''; });
    }
    function markChips() {
        var status = form.status.value;
        var engine = form.engine.value;
        U.qa('#player-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = false;
            if (key === '' && status === '' && engine === '') on = true;
            else if (key === 'engine' && status === '' && engine === val) on = true;
            else if (key === 'status' && engine === '' && status === val) on = true;
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
    function nameHtml(d) {
        var badge = String(d.status) === '1' ? '' : '<span class="badge badge-off">停用</span>';
        var meta = [U.escape(d.engine_label || d.engine || ''), '标识 ' + U.escape(d.code || '')];
        if (d.source_count) meta.push(d.source_count + ' 条线路');
        if (d.parse) meta.push('有解析');
        return '<div><div class="entry-row-title-line"><a class="entry-row-title js-edit" href="#">' + U.escape(d.name || '未命名') + '</a> ' + badge + '</div>'
            + '<div class="entry-row-meta">' + meta.join(' · ') + '</div></div>';
    }

    var table = U.table({
        el: '#player-table',
        url: '/admin/video/players/list',
        where: queryWhere(),
        pager: false,
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>没有符合条件的播放器</p><p><button type="button" class="btn btn-muted btn-sm" id="player-empty-reset">清除筛选</button></p></div>';
            }
            return '<div class="list-empty"><p>还没有播放器</p><p class="muted">先补齐 ArtPlayer、DPlayer、Video.js 和解析接口。</p><p><button type="button" class="btn btn-primary btn-sm" id="player-empty-ensure">补齐内置</button></p></div>';
        },
        onDraw: function (_wrap, list) {
            countEl.textContent = list.length ? '· ' + list.length : '';
            var ensure = document.getElementById('player-empty-ensure');
            var reset = document.getElementById('player-empty-reset');
            if (ensure) ensure.addEventListener('click', ensurePlayers);
            if (reset) reset.addEventListener('click', function () { form.reset(); runSearch(); });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = '已选 ' + ids.length + ' 个';
        },
        cols: [
            {check: true, width: 36},
            {title: '播放器', html: nameHtml},
            {key: 'sort', title: '排序', width: 64},
            {title: '状态', width: 72, html: function (d) {
                return String(d.status) === '1' ? U.status(true, '启用') : U.status(false, '停用');
            }},
            {title: '操作', cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-copy">复制标识</a><a href="#" class="btn-link js-edit">编辑</a><a href="#" class="btn-link js-del">删除</a>';
            }}
        ]
    });
    markChips();

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
    function bindEngine(formEl) {
        var engine = formEl.querySelector('[name=engine]');
        var wrap = formEl.querySelector('#player-parse-wrap');
        function sync() { wrap.hidden = engine.value !== 'iframe'; }
        engine.addEventListener('change', sync);
        sync();
    }
    function openDialog(mode, row) {
        row = row || {};
        U.dialog({
            title: mode === 'edit' ? '编辑播放器' : '新增播放器',
            content: document.getElementById('player-dialog-tpl').innerHTML,
            onOpen: function (body) {
                var formEl = body.querySelector('form');
                U.fillForm(formEl, {
                    id: mode === 'edit' ? (row.id || '') : '',
                    name: row.name || '',
                    code: row.code || (mode === 'add' ? 'artplayer' : ''),
                    engine: row.engine || 'artplayer',
                    parse: row.parse || '',
                    sort: row.sort == null ? 0 : row.sort,
                    status: row.status == null ? '1' : String(row.status)
                });
                bindEngine(formEl);
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (!data.name) { U.toast('请填写名称', 'err'); return false; }
                if (!data.code) { U.toast('请填写标识', 'err'); return false; }
                if (data.engine !== 'iframe') data.parse = data.parse || '';
                if (mode !== 'edit') delete data.id; else data.id = row.id;
                return U.post('/admin/video/players/save', data).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return false; }
                    U.toast(mode === 'edit' ? '已保存' : '已创建', 'ok');
                    table.refresh();
                });
            }
        });
    }
    function ensurePlayers() {
        U.post('/admin/video/players/ensure', {}).then(function (res) {
            U.toast((res && res.msg) || '完成', res && res.code === 0 ? 'ok' : 'err');
            if (res && res.code === 0) table.refresh();
        });
    }
    function selectedIds() { return table.selectedIds(); }
    function batch(action, value, confirmText) {
        var ids = selectedIds();
        if (!ids.length) { U.toast('请先勾选播放器', 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/players/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '操作失败', 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || '操作成功', 'ok');
        });
    }

    U.on('#player-search-btn', 'click', runSearch);
    U.on('#player-reset-btn', 'click', function () { setTimeout(runSearch, 0); });
    U.on('#player-add-btn', 'click', function () { openDialog('add'); });
    U.on('#player-ensure-btn', 'click', ensurePlayers);
    document.getElementById('player-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#player-batch-on', 'click', function () { batch('status', 1); });
    U.on('#player-batch-off', 'click', function () { batch('status', 0); });
    U.on('#player-batch-move', 'click', function () {
        var val = document.getElementById('player-batch-engine').value;
        if (!val) { U.toast('请先选择内核，再点「更换」', 'err'); return; }
        batch('engine', val);
    });
    U.on('#player-batch-del', 'click', function () { batch('delete', '', '确认删除选中播放器？仍被线路使用的不会删。'); });
    U.on('#player-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#player-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-copy')) {
            copyText(row.code || '').then(function () { U.toast('已复制标识', 'ok'); });
        }
        if (a.classList.contains('js-edit')) openDialog('edit', row);
        if (a.classList.contains('js-del')) {
            if (!U.confirm('删除播放器「' + (row.name || '') + '」？')) return;
            U.post('/admin/video/players/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh();
                U.toast('已删除', 'ok');
            });
        }
    });
})();
</script>
@endpush
