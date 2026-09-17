@extends('admin.layouts.inner')
@section('title', $title ?? admin_t('page.plots'))

@php
    $queues = $queues ?? ['all' => 0, 'no_content' => 0, 'no_title' => 0, 'no_video' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
    $videoId = (int) ($videoId ?? 0);
    $videoTitle = (string) ($videoTitle ?? '');
@endphp

@section('plain')
<div class="card card-panel plot-index" id="plot-index">
    <div class="card-header">
        <span>分集剧情 <em id="plot-count"></em></span>
        <div>
            <button type="button" class="btn btn-sm" id="plot-add-btn">新增剧情</button>
            <a class="btn btn-muted btn-sm" href="/admin/video">影片</a>
            <a class="btn btn-muted btn-sm" href="/admin/video?has_plot=1">有剧情的片子</a>
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">按集写剧情，挂到一部片子。详情页会列出。不会从播放地址自动生成。</p>
        @if($videoId > 0)
            <p class="plot-focus">{{ $videoTitle !== '' ? '正在看「'.$videoTitle.'」的分集剧情。' : '影片 #'.$videoId.' 不存在，保存时会失败。' }}</p>
        @endif
        <form class="filter-bar" id="plot-search" onsubmit="return false;">
            <input type="hidden" name="empty_video">
            <input type="hidden" name="empty_content">
            <input type="hidden" name="empty_title">
            <input type="hidden" name="video_id" value="{{ $videoId > 0 ? $videoId : '' }}">
            <input type="search" name="q" placeholder="搜剧情、片名或集数" autocomplete="off" aria-label="搜索分集剧情">
            <button type="button" class="btn btn-sm" id="plot-search-btn">查询</button>
            <button type="reset" class="btn btn-muted btn-sm" id="plot-reset-btn">重置</button>
        </form>
        <div class="queue-chips" id="plot-queues">
            <button type="button" class="chip" data-queue="">全部@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="empty_content" data-value="1">没写正文@if($q('no_content') > 0)<em>{{ $q('no_content') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="empty_title" data-value="1">没写标题@if($q('no_title') > 0)<em>{{ $q('no_title') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="empty_video" data-value="1">没挂影片@if($q('no_video') > 0)<em>{{ $q('no_video') }}</em>@endif</button>
        </div>
        <div class="batch-bar" id="plot-batch" hidden>
            <strong id="plot-batch-count">已选 0 条</strong>
            <button type="button" class="btn btn-danger btn-sm" id="plot-batch-del">删除</button>
            <button type="button" class="btn btn-muted btn-sm" id="plot-batch-clear">取消选择</button>
        </div>
        <div id="plot-table"></div>
    </div>
</div>
<template id="plot-dialog-tpl">
    <form>
        <input type="hidden" name="id">
        <label>影片 ID</label>
        <input type="number" name="video_id" min="1" placeholder="这部戏的影片编号" inputmode="numeric" required>
        <p class="muted field-hint" id="plot-video-hint">必须挂到一部已有的片子。</p>
        <label>集数</label>
        <input type="number" name="episode_num" min="1" placeholder="从 1 开始" inputmode="numeric" required>
        <p class="muted field-hint">同一部片子每一集只能写一条。不必等播放地址齐了再写。</p>
        <label>标题</label>
        <input type="text" name="title" placeholder="可空，空着列表会显示第几集">
        <label>剧情</label>
        <textarea name="content" rows="8" placeholder="这一集发生了什么，不要整集台词" required></textarea>
        <label>排序</label>
        <input type="number" name="sort" value="0">
        <p class="muted field-hint">数字越小越靠前。一般不用改。</p>
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var QUEUE_KEYS = ['empty_video', 'empty_content', 'empty_title'];
    var form = document.getElementById('plot-search');
    var batchBar = document.getElementById('plot-batch');
    var batchCount = document.getElementById('plot-batch-count');
    var countEl = document.getElementById('plot-count');
    var prefillVideo = @json($videoId > 0 ? $videoId : 0);
    var prefillVideoTitle = @json($videoTitle);

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
        var active = '';
        QUEUE_KEYS.forEach(function (k) {
            if (form[k] && form[k].value === '1') active = k;
        });
        U.qa('#plot-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var on = false;
            if (key === '' && !active) on = true;
            else if (key && active === key) on = true;
            chip.classList.toggle('active', on);
        });
    }
    function applyQueue(key, value) {
        QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
        if (key && form[key]) form[key].value = value || '1';
        runSearch();
    }
    function runSearch() {
        table.reload(queryWhere());
        markChips();
    }
    function plotHtml(d) {
        var title = d.title_text || d.title || '';
        var preview = d.content_preview || '';
        var meta = '#' + U.escape(d.id);
        if (!d.has_content) meta += ' · 没写正文';
        else if (preview) meta += ' · ' + U.escape(preview);
        return '<div><a class="vod-title js-edit" href="#">' + U.escape(title) + '</a>'
            + '<div class="muted">' + meta + '</div></div>';
    }
    function videoHtml(d) {
        var vid = parseInt(d.video_id, 10) || 0;
        if (vid < 1) return '<span class="muted">没挂影片</span>';
        if (d.video_missing) return '<span class="muted">片子已删 #' + vid + '</span>';
        var title = d.video_title || ('影片 #' + vid);
        return '<a href="/admin/video/' + vid + '/edit">' + U.escape(title) + '</a>';
    }

    var table = U.table({
        el: '#plot-table',
        url: '/admin/video/plots/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>没有符合条件的分集剧情</p><p><button type="button" class="btn btn-muted btn-sm" id="plot-empty-reset">清除筛选</button></p></div>';
            }
            return '<div class="list-empty"><p>还没有分集剧情</p><p class="muted">点新增，填影片、集数和这一集发生了什么。不会从播放地址自动生成。</p><p><button type="button" class="btn btn-primary btn-sm" id="plot-empty-add">新增剧情</button></p></div>';
        },
        onDraw: function (_wrap, list) {
            countEl.textContent = list.length ? '· ' + list.length : '';
            var add = document.getElementById('plot-empty-add');
            var reset = document.getElementById('plot-empty-reset');
            if (add) add.addEventListener('click', function () { openDialog('add'); });
            if (reset) reset.addEventListener('click', function () {
                form.reset();
                if (prefillVideo) form.video_id.value = String(prefillVideo);
                runSearch();
            });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = '已选 ' + ids.length + ' 条';
        },
        cols: [
            {check: true, width: 36},
            {title: '剧情', html: plotHtml},
            {title: '影片', html: videoHtml},
            {title: '集数', width: 88, html: function (d) { return U.escape(d.episode_label || ''); }},
            {key: 'sort', title: '排序', width: 64},
            {title: '写入', width: 140, html: function (d) { return U.escape(d.created_at_text || ''); }},
            {title: '操作', cls: 'actions', html: function (d) {
                var href = d.url ? String(d.url) : ('/plot/' + encodeURIComponent(d.id));
                return '<a href="' + U.escape(href) + '" target="_blank" rel="noopener" class="btn-link">前台</a>'
                    + '<a href="#" class="btn-link js-edit">编辑</a>'
                    + '<a href="#" class="btn-link js-del">删除</a>';
            }}
        ]
    });
    markChips();

    function bindLookups(formEl, row) {
        var videoInput = formEl.querySelector('input[name=video_id]');
        var videoHint = formEl.querySelector('#plot-video-hint');
        function setHint(text) { if (videoHint) videoHint.textContent = text; }
        function showVideo(id, title, missing) {
            id = parseInt(id, 10) || 0;
            if (id < 1) { setHint('必须挂到一部已有的片子。'); return; }
            if (missing) { setHint('影片 #' + id + ' 不存在，保存会失败。'); return; }
            if (title) { setHint('将挂到「' + title + '」。'); return; }
            setHint('影片 #' + id);
        }
        showVideo(videoInput.value, row.video_title || (String(videoInput.value) === String(prefillVideo) ? prefillVideoTitle : ''), row.video_missing);
        videoInput.addEventListener('blur', function () {
            var id = parseInt(videoInput.value, 10) || 0;
            if (id < 1) { showVideo(0, '', 0); return; }
            U.get('/admin/video/info', {id: id}).then(function (res) {
                var d = (res && res.data) || {};
                var title = d.title || '';
                showVideo(id, title, res && res.code === 0 && title ? 0 : 1);
            });
        });
    }

    function openDialog(mode, row) {
        row = row || {};
        U.dialog({
            title: mode === 'edit' ? '编辑剧情' : '新增剧情',
            content: document.getElementById('plot-dialog-tpl').innerHTML,
            onOpen: function (body) {
                var formEl = body.querySelector('form');
                var videoVal = mode === 'edit' ? (row.video_id || 0) : (row.video_id || prefillVideo || 0);
                U.fillForm(formEl, {
                    id: mode === 'edit' ? (row.id || '') : '',
                    video_id: videoVal || '',
                    episode_num: row.episode_num || '',
                    title: row.title || '',
                    content: row.content || '',
                    sort: row.sort == null ? 0 : row.sort
                });
                bindLookups(formEl, {
                    video_title: row.video_title || '',
                    video_missing: row.video_missing
                });
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (!data.video_id) { U.toast('请填写影片 ID', 'err'); return false; }
                if (!data.episode_num || parseInt(data.episode_num, 10) < 1) { U.toast('请填写集数，从 1 开始', 'err'); return false; }
                if (mode !== 'edit' && !data.content) { U.toast('请填写这一集的剧情', 'err'); return false; }
                if (mode !== 'edit') delete data.id; else data.id = row.id;
                return U.post('/admin/video/plots/save', data).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return false; }
                    U.toast(mode === 'edit' ? '已保存' : '已创建', 'ok');
                    table.refresh();
                });
            }
        });
    }

    function selectedIds() { return table.selectedIds(); }
    function batch(action, confirmText) {
        var ids = selectedIds();
        if (!ids.length) { U.toast('请先勾选剧情', 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/plots/batch', {ids: ids.join(','), action: action}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '操作失败', 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || '操作成功', 'ok');
        });
    }

    U.on('#plot-search-btn', 'click', runSearch);
    U.on('#plot-reset-btn', 'click', function () {
        setTimeout(function () {
            if (prefillVideo) form.video_id.value = String(prefillVideo);
            runSearch();
        }, 0);
    });
    U.on('#plot-add-btn', 'click', function () { openDialog('add'); });
    document.getElementById('plot-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#plot-batch-del', 'click', function () { batch('delete', '确认删除选中剧情？'); });
    U.on('#plot-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#plot-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        if (a.target === '_blank' || (a.getAttribute('href') || '').indexOf('/admin/video/') === 0) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-edit')) openDialog('edit', row);
        if (a.classList.contains('js-del')) {
            if (!U.confirm('删除「' + (row.title_text || row.episode_label || '') + '」的剧情？')) return;
            U.post('/admin/video/plots/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh();
                U.toast('已删除', 'ok');
            });
        }
    });
})();
</script>
@endpush
