@extends('admin.layouts.inner')
@section('title', $title)

@php
    $types = $types ?? [];
    $tags = $tags ?? [];
    $queues = $queues ?? ['all' => 0, 'published' => 0, 'draft' => 0, 'pending' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
    $filterType = (string) request()->query('type_id', '');
    $filterTagId = (string) request()->query('tag_id', '');
    $filterTag = $filterTag ?? null;
    $looseCount = (int) ($looseCount ?? 0);
    $recycleCount = (int) ($recycleCount ?? 0);
    $writeHref = ($filterType !== '' && $filterType !== '0')
        ? '/admin/video/arts/create?type_id='.(int) $filterType
        : '/admin/video/arts/create';
@endphp

@section('plain')
<div class="art-workbench">
<aside class="art-cat-rail" id="art-cat-rail">
    <div class="art-cat-rail-head">
        <span>栏目</span>
        <a href="/admin/video/art-types">管理</a>
    </div>
    <a class="art-cat-item" data-type="" href="/admin/video/arts">全部<em>{{ $q('all') }}</em></a>
    <a class="art-cat-item" data-type="0" href="/admin/video/arts?type_id=0">未分栏<em>{{ $looseCount }}</em></a>
    @forelse($types as $type)
        <a class="art-cat-item" data-type="{{ $type['id'] }}" href="/admin/video/arts?type_id={{ $type['id'] }}" style="padding-left: {{ 10 + (int) ($type['depth'] ?? 0) * 14 }}px">
            <span>{{ $type['title'] ?? $type['name'] }}</span>
            <em>{{ (int) ($type['art_count'] ?? 0) }}</em>
        </a>
    @empty
        <p class="muted art-cat-empty">还没有栏目。<a href="/admin/video/art-types/create">新建栏目</a></p>
    @endforelse
</aside>
<div class="art-workbench-main">
<div class="card card-panel art-index">
    <div class="card-header">
        <span>文章 <em id="art-count"></em></span>
        <div>
            <a class="btn btn-muted btn-sm" href="/admin/video/art-types">栏目</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/art-tags">标签</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/art-recycle">回收站@if($recycleCount > 0) {{ $recycleCount }}@endif</a>
            <a class="btn btn-sm" id="art-write" href="{{ $writeHref }}">写文章</a>
        </div>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="art-search" onsubmit="return false;">
            <input type="hidden" name="queue" value="">
            <input type="search" name="q" placeholder="搜索标题、正文或 ID" autocomplete="off" aria-label="搜索文章">
            <select name="type_id" aria-label="栏目">
                <option value="">全部栏目</option>
                <option value="0" @selected($filterType === '0')>未分栏</option>
                @foreach($types as $type)
                    <option value="{{ $type['id'] }}" @selected($filterType === (string) $type['id'])>{{ $type['name'] }}</option>
                @endforeach
            </select>
            <select name="status">
                <option value="">状态</option>
                <option value="1">已发布</option>
                <option value="0">草稿</option>
            </select>
            <select name="flag" aria-label="推荐属性">
                <option value="">推荐属性</option>
                <option value="top">置顶</option>
                <option value="recommend">推荐</option>
                <option value="hot">热门</option>
            </select>
            @if($tags !== [])
                <select name="tag_id" aria-label="标签">
                    <option value="">全部标签</option>
                    @foreach($tags as $tag)
                        <option value="{{ $tag['id'] }}" @selected($filterTagId === (string) $tag['id'])>{{ $tag['name'] }}</option>
                    @endforeach
                </select>
            @endif
            <button type="button" class="btn btn-sm" id="art-search-btn">搜索</button>
            <button type="reset" class="btn btn-muted btn-sm" id="art-reset-btn">重置</button>
        </form>
        <div class="queue-chips" id="art-queues">
            <button type="button" class="chip" data-queue="">全部@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="published">已发布@if($q('published') > 0)<em>{{ $q('published') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="draft">草稿@if($q('draft') > 0)<em>{{ $q('draft') }}</em>@endif</button>
            @if($q('pending') > 0)
                <button type="button" class="chip" data-queue="pending">定时<em>{{ $q('pending') }}</em></button>
            @endif
            @if($filterTag)
                <a class="chip active" href="/admin/video/arts">标签 {{ $filterTag['name'] }} ×</a>
            @endif
        </div>
        <p class="muted recycle-lead">站内资讯，不是影片。栏目只给文章用，和影片分类分开。删除先进回收站，可还原。</p>
        <div class="batch-bar" id="art-batch" hidden>
            <strong id="art-batch-count">已选 0 篇</strong>
            <button type="button" class="btn btn-sm" id="art-batch-on">发布到前台</button>
            <button type="button" class="btn btn-muted btn-sm" id="art-batch-off">改回草稿</button>
            <button type="button" class="btn btn-muted btn-sm" id="art-batch-rec">设为推荐</button>
            <button type="button" class="btn btn-muted btn-sm" id="art-batch-unrec">取消推荐</button>
            <select id="art-batch-type" class="batch-select" aria-label="目标栏目">
                <option value="">选择栏目</option>
                <option value="0">未分栏</option>
                @foreach($types as $type)
                    <option value="{{ $type['id'] }}">{{ $type['name'] }}</option>
                @endforeach
            </select>
            <button type="button" class="btn btn-muted btn-sm" id="art-batch-move">移动过去</button>
            <button type="button" class="btn btn-muted btn-sm" id="art-batch-copy">复制一份</button>
            <button type="button" class="btn btn-danger btn-sm" id="art-batch-del">删除</button>
            <button type="button" class="btn btn-muted btn-sm" id="art-batch-clear">取消选择</button>
        </div>
        <div id="art-table"></div>
    </div>
</div>
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
        var queue = form.queue ? form.queue.value : '';
        U.qa('#art-queues .chip').forEach(function (chip) {
            var val = chip.getAttribute('data-queue') || '';
            chip.classList.toggle('active', val === queue);
        });
    }
    function applyQueue(value) {
        if (form.queue) form.queue.value = value || '';
        form.status.value = '';
        runSearch();
    }
    function runSearch() {
        table.reload(queryWhere());
        markChips();
        syncRail();
    }
    function syncRail() {
        var val = form.type_id ? String(form.type_id.value || '') : '';
        U.qa('#art-cat-rail [data-type]').forEach(function (el) {
            el.classList.toggle('is-on', (el.getAttribute('data-type') || '') === val);
        });
        var write = document.getElementById('art-write');
        if (write) {
            write.href = (val && val !== '0')
                ? '/admin/video/arts/create?type_id=' + encodeURIComponent(val)
                : '/admin/video/arts/create';
        }
        var url = '/admin/video/arts';
        var qs = [];
        if (val !== '') qs.push('type_id=' + encodeURIComponent(val));
        var tag = form.tag_id ? String(form.tag_id.value || '') : '';
        if (tag !== '') qs.push('tag_id=' + encodeURIComponent(tag));
        if (qs.length) url += '?' + qs.join('&');
        if (history.replaceState) history.replaceState(null, '', url);
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
    function flagBadges(d) {
        var label = String(d.flags_label || '').trim();
        if (!label) return '';
        return label.split('/').map(function (s) {
            s = String(s || '').trim();
            return s ? ' <span class="badge badge-ok">' + U.escape(s) + '</span>' : '';
        }).join('');
    }
    function titleHtml(d) {
        var cover = String(d.cover || '').trim();
        var thumb = cover
            ? '<img class="vod-thumb" src="' + U.escape(cover) + '" alt="">'
            : '';
        var listed = d.listed === true || d.listed === 1 || d.listed === '1';
        var badge = '';
        if (String(d.status) !== '1') {
            badge = '<span class="badge badge-off">草稿</span>';
        } else if (!listed) {
            badge = '<span class="badge badge-warn">定时</span>';
        }
        var meta = (d.type_name ? U.escape(d.type_name) : '未分栏') + ' · #' + U.escape(d.id);
        if (parseInt(d.hits, 10) > 0) meta += ' · ' + U.escape(d.hits) + ' 次';
        if (d.tag_label) meta += ' · ' + U.escape(d.tag_label);
        return '<div class="vod-cell">' + thumb + '<div class="entry-row-title-line"><a class="entry-row-title" href="/admin/video/arts/' + encodeURIComponent(d.id) + '/edit">' + U.escape(d.title || '无标题') + '</a> ' + badge + flagBadges(d) + '</div>'
            + '<div class="entry-row-meta">' + meta + '</div></div>';
    }

    var table = U.table({
        el: '#art-table',
        url: '/admin/video/arts/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                var tid = String(where.type_id || '');
                var write = (tid && tid !== '0')
                    ? '<a class="btn btn-primary btn-sm" href="/admin/video/arts/create?type_id=' + encodeURIComponent(tid) + '">写到这个栏目</a>'
                    : '<a class="btn btn-primary btn-sm" href="/admin/video/arts/create">写文章</a>';
                return '<div class="list-empty"><p>没有符合条件的内容。</p><p><button type="button" class="btn btn-muted btn-sm" id="art-empty-reset">清除筛选</button> ' + write + '</p></div>';
            }
            if (!hasTypes) {
                return '<div class="list-empty"><p>还没有内容。</p><p class="muted">先去栏目里建文章栏目，再回来写稿。</p><p><a class="btn btn-muted btn-sm" href="/admin/video/art-types">去建栏目</a> <a class="btn btn-primary btn-sm" href="/admin/video/arts/create">写文章</a></p></div>';
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
                var listed = d.listed === true || d.listed === 1 || d.listed === '1';
                var html = '';
                if (listed) {
                    html += '<a href="' + U.escape(href) + '" target="_blank" rel="noopener" class="btn-link">前台</a>';
                } else if (String(d.status) !== '1') {
                    html += '<a href="#" class="btn-link js-pub">发布</a>';
                }
                html += '<a href="/admin/video/arts/' + encodeURIComponent(d.id) + '/edit" class="btn-link">编辑</a>';
                html += '<a href="#" class="btn-link js-copy">复制一份</a>';
                html += '<a href="#" class="btn-link js-del">删除</a>';
                return html;
            }}
        ]
    });
    markChips();
    syncRail();

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
    if (form.status) {
        form.status.addEventListener('change', function () {
            if (form.queue) form.queue.value = '';
        });
    }
    if (form.type_id) {
        form.type_id.addEventListener('change', runSearch);
    }
    if (form.tag_id) {
        form.tag_id.addEventListener('change', runSearch);
    }
    var rail = document.getElementById('art-cat-rail');
    if (rail) {
        rail.addEventListener('click', function (e) {
            var a = e.target.closest('[data-type]');
            if (!a || !form.type_id) return;
            e.preventDefault();
            form.type_id.value = a.getAttribute('data-type') || '';
            runSearch();
        });
    }
    document.getElementById('art-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '');
    });
    U.on('#art-batch-on', 'click', function () { batch('status', 1, '将已选文章发布到前台？'); });
    U.on('#art-batch-off', 'click', function () { batch('status', 0, '将已选文章改回草稿？'); });
    U.on('#art-batch-rec', 'click', function () { batch('flag_recommend', '', '将已选文章设为推荐？'); });
    U.on('#art-batch-unrec', 'click', function () { batch('unflag_recommend', '', '取消已选文章的推荐？'); });
    U.on('#art-batch-move', 'click', function () {
        var val = document.getElementById('art-batch-type').value;
        if (!val) { U.toast('请先选择要换到的栏目，再点「移动」', 'err'); return; }
        batch('type', val, '移动到所选栏目？');
    });
    U.on('#art-batch-copy', 'click', function () { batch('copy', '', '将已选文章复制一份为草稿？'); });
    U.on('#art-batch-del', 'click', function () { batch('delete', '', '删除后进入回收站，可还原。确认删除选中文章？'); });
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
        if (a.classList.contains('js-copy')) {
            if (!U.confirm('复制「' + (row.title || '') + '」一份为草稿？')) return;
            U.post('/admin/video/arts/batch', {ids: String(row.id), action: 'copy'}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh();
                U.toast((res && res.msg) || '已复制为草稿', 'ok');
            });
        }
        if (a.classList.contains('js-del')) {
            if (!U.confirm('删除「' + (row.title || '') + '」后进入回收站，可还原。确定？')) return;
            U.post('/admin/video/arts/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh();
                U.toast((res && res.msg) || '已移入回收站', 'ok');
            });
        }
    });
})();
</script>
@endpush
