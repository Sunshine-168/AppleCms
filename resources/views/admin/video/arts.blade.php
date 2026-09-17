@extends('admin.layouts.inner')
@section('title', $title)

@php
    $types = $types ?? [];
    $queues = $queues ?? ['all' => 0, 'published' => 0, 'draft' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
@endphp

@section('plain')
<div class="card card-panel art-index">
    <div class="card-header">
        <span>文章 <em id="art-count"></em></span>
        <div>
            <a class="btn btn-muted btn-sm" href="/admin/video/types">栏目</a>
            <a class="btn btn-sm" href="/admin/video/arts/create">写文章</a>
        </div>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="art-search" onsubmit="return false;">
            <input type="search" name="q" placeholder="搜索标题、正文或 ID" autocomplete="off" aria-label="搜索文章">
            <select name="type_id" aria-label="栏目">
                <option value="">全部栏目</option>
                @foreach($types as $type)
                    <option value="{{ $type['id'] }}">{{ $type['name'] }}</option>
                @endforeach
            </select>
            <select name="status">
                <option value="">状态</option>
                <option value="1">已发布</option>
                <option value="0">草稿</option>
            </select>
            <button type="button" class="btn btn-sm" id="art-search-btn">搜索</button>
            <button type="reset" class="btn btn-muted btn-sm" id="art-reset-btn">重置</button>
        </form>
        <div class="queue-chips" id="art-queues">
            <button type="button" class="chip" data-queue="">全部@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="1">已发布@if($q('published') > 0)<em>{{ $q('published') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="0">草稿@if($q('draft') > 0)<em>{{ $q('draft') }}</em>@endif</button>
        </div>
        <p class="muted recycle-lead">站内资讯，不是影片。栏目在「分类」里把模型选成文章。勾选后可发布、改栏目或删除。</p>
        <div class="batch-bar" id="art-batch" hidden>
            <strong id="art-batch-count">已选 0 篇</strong>
            <button type="button" class="btn btn-sm" id="art-batch-on">发布到前台</button>
            <button type="button" class="btn btn-muted btn-sm" id="art-batch-off">改回草稿</button>
            <select id="art-batch-type" class="batch-select" aria-label="目标栏目">
                <option value="">选择栏目</option>
                @foreach($types as $type)
                    <option value="{{ $type['id'] }}">{{ $type['name'] }}</option>
                @endforeach
            </select>
            <button type="button" class="btn btn-muted btn-sm" id="art-batch-move">移动过去</button>
            <button type="button" class="btn btn-danger btn-sm" id="art-batch-del">删除</button>
            <button type="button" class="btn btn-muted btn-sm" id="art-batch-clear">取消选择</button>
        </div>
        <div id="art-table"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var form = document.getElementById('art-search');
    var batchBar = document.getElementById('art-batch');
    var batchCount = document.getElementById('art-batch-count');
    var countEl = document.getElementById('art-count');
    var hasTypes = @json(count($types) > 0);

    function cleanWhere(data) {
        var out = {};
        Object.keys(data).forEach(function (k) { if (data[k] !== '') out[k] = data[k]; });
        return out;
    }
    function queryWhere() {
        return Object.assign({limit: 15}, cleanWhere(U.formData(form)));
    }
    function isFiltered(where) {
        return Object.keys(where || {}).some(function (k) { return k !== 'limit' && where[k] !== ''; });
    }
    function markChips() {
        var status = form.status.value;
        U.qa('#art-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = (key === '' && status === '') || (key === 'status' && status === val);
            chip.classList.toggle('active', on);
        });
    }
    function applyQueue(key, value) {
        form.status.value = key === 'status' ? (value || '') : '';
        runSearch();
    }
    function runSearch() {
        table.reload(queryWhere());
        markChips();
    }
    function fmtTime(ts) {
        ts = parseInt(ts, 10) || 0;
        if (!ts) return '—';
        var d = new Date(ts * 1000);
        var now = new Date();
        var pad = function (n) { return n < 10 ? '0' + n : '' + n; };
        var hm = pad(d.getHours()) + ':' + pad(d.getMinutes());
        if (d.toDateString() === now.toDateString()) return '今天 ' + hm;
        var y = new Date(now);
        y.setDate(now.getDate() - 1);
        if (d.toDateString() === y.toDateString()) return '昨天 ' + hm;
        if (d.getFullYear() === now.getFullYear()) return pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + ' ' + hm;
        return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
    }
    function titleHtml(d) {
        var cover = String(d.cover || '').trim();
        var thumb = cover
            ? '<img class="vod-thumb" src="' + U.escape(cover) + '" alt="">'
            : '';
        var badge = String(d.status) === '1' ? '' : '<span class="badge badge-off">草稿</span>';
        var meta = (d.type_name ? U.escape(d.type_name) : '未分栏') + ' · #' + U.escape(d.id);
        if (parseInt(d.hits, 10) > 0) meta += ' · ' + U.escape(d.hits) + ' 次';
        return '<div class="vod-cell">' + thumb + '<div class="entry-row-title-line"><a class="entry-row-title" href="/admin/video/arts/' + encodeURIComponent(d.id) + '/edit">' + U.escape(d.title || '无标题') + '</a> ' + badge + '</div>'
            + '<div class="entry-row-meta">' + meta + '</div></div>';
    }

    var table = U.table({
        el: '#art-table',
        url: '/admin/video/arts/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>没有符合条件的内容。</p><p><button type="button" class="btn btn-muted btn-sm" id="art-empty-reset">清除筛选</button></p></div>';
            }
            if (!hasTypes) {
                return '<div class="list-empty"><p>还没有内容。</p><p class="muted">先去分类里建文章栏目，模型选「文章」，再回来写稿。</p><p><a class="btn btn-muted btn-sm" href="/admin/video/types">去建栏目</a> <a class="btn btn-primary btn-sm" href="/admin/video/arts/create">写文章</a></p></div>';
            }
            return '<div class="list-empty"><p>还没有内容。</p><p><a class="btn btn-primary btn-sm" href="/admin/video/arts/create">写文章</a></p></div>';
        },
        onDraw: function (_wrap, list) {
            countEl.textContent = list.length ? '· ' + list.length : '';
            var reset = document.getElementById('art-empty-reset');
            if (reset) reset.addEventListener('click', function () { form.reset(); runSearch(); });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = '已选 ' + ids.length + ' 篇';
        },
        cols: [
            {check: true, width: 36},
            {title: '标题', html: titleHtml},
            {title: '时间', width: 120, html: function (d) {
                return '<span class="muted">' + U.escape(fmtTime(d.updated_at_unix || d.updated_at || d.created_at)) + '</span>';
            }},
            {title: '操作', cls: 'actions', html: function (d) {
                var href = d.url ? String(d.url) : ('/art/' + encodeURIComponent(d.id));
                var html = '';
                if (String(d.status) === '1') {
                    html += '<a href="' + U.escape(href) + '" target="_blank" rel="noopener" class="btn-link">前台</a>';
                } else {
                    html += '<a href="#" class="btn-link js-pub">发布</a>';
                }
                html += '<a href="/admin/video/arts/' + encodeURIComponent(d.id) + '/edit" class="btn-link">编辑</a>';
                html += '<a href="#" class="btn-link js-del">删除</a>';
                return html;
            }}
        ]
    });
    markChips();

    function selectedIds() { return table.selectedIds(); }
    function batch(action, value, confirmText) {
        var ids = selectedIds();
        if (!ids.length) { U.toast('请先勾选要处理的内容', 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/arts/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '操作失败', 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || '操作成功', 'ok');
        });
    }

    U.on('#art-search-btn', 'click', runSearch);
    U.on('#art-reset-btn', 'click', function () { setTimeout(runSearch, 0); });
    document.getElementById('art-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#art-batch-on', 'click', function () { batch('status', 1, '将已选文章发布到前台？'); });
    U.on('#art-batch-off', 'click', function () { batch('status', 0, '将已选文章改回草稿？'); });
    U.on('#art-batch-move', 'click', function () {
        var val = document.getElementById('art-batch-type').value;
        if (!val) { U.toast('请先选择要换到的栏目，再点「移动」', 'err'); return; }
        batch('type', val, '移动到所选栏目？');
    });
    U.on('#art-batch-del', 'click', function () { batch('delete', '', '确认删除选中文章？'); });
    U.on('#art-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#art-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        if (a.target === '_blank') return;
        if (a.getAttribute('href') && a.getAttribute('href').indexOf('/admin/video/arts/') === 0) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-pub')) {
            if (!U.confirm('确认发布「' + (row.title || '') + '」？')) return;
            U.post('/admin/video/arts/save', {id: row.id, status: 1}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh();
                U.toast('已发布', 'ok');
            });
        }
        if (a.classList.contains('js-del')) {
            if (!U.confirm('删除「' + (row.title || '') + '」？')) return;
            U.post('/admin/video/arts/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh();
                U.toast('已删除', 'ok');
            });
        }
    });
})();
</script>
@endpush
