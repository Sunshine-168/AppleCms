@extends('admin.layouts.inner')
@section('title', '图集')
@php
    $desk = in_array(request('desk', 'works'), ['works', 'pending', 'pics', 'types', 'favors', 'comments', 'stats'], true)
        ? request('desk', 'works')
        : 'works';
    $stats = is_array($stats ?? null) ? $stats : [];
    $commentQueues = is_array($commentQueues ?? null) ? $commentQueues : ['all' => 0, 'pending' => 0, 'pass' => 0];
    $worksStat = is_array($stats['works'] ?? null) ? $stats['works'] : ['all' => 0, 'show' => 0, 'pending' => 0, 'off' => 0];
    $works = $works ?? collect();
    $types = $types ?? collect();
@endphp
@section('plain')
<div class="card card-panel desk-board" id="gallery-board">
    <div class="card-header">
        <span>{{ $desk === 'stats' ? '图集统计' : '图集' }} <em id="gallery-count"></em></span>
        @if(in_array($desk, ['works', 'pending'], true))
            <span class="btn-split" role="group" aria-label="添加图集">
                <a class="btn btn-sm" href="#gallery-work-compose-box">新增图集</a>
                <a class="btn btn-muted btn-sm" href="/admin/video/galleries/create{{ $desk === 'pending' ? '?desk=pending' : '' }}">完整表单</a>
            </span>
        @elseif($desk === 'pics')
            <span class="btn-split" role="group" aria-label="添加图片">
                <a class="btn btn-sm" href="#gallery-pic-compose-box">批量添加</a>
                <a class="btn btn-muted btn-sm" href="/admin/video/gallery-pics/create">单张表单</a>
            </span>
        @elseif($desk === 'types')
            <button type="button" class="btn btn-sm" id="add">新增分类</button>
        @endif
    </div>
    <div class="card-body">
        <div class="queue-chips" id="gallery-desks">
            <a class="chip{{ $desk === 'works' ? ' active' : '' }}" href="/admin/video/galleries">图集</a>
            <a class="chip{{ $desk === 'pending' ? ' active' : '' }}" href="?desk=pending">待审</a>
            <a class="chip{{ $desk === 'pics' ? ' active' : '' }}" href="?desk=pics">图片</a>
            <a class="chip{{ $desk === 'types' ? ' active' : '' }}" href="?desk=types">分类</a>
            <a class="chip{{ $desk === 'favors' ? ' active' : '' }}" href="?desk=favors">收藏</a>
            <a class="chip{{ $desk === 'comments' ? ' active' : '' }}" href="?desk=comments">评论</a>
            <a class="chip{{ $desk === 'stats' ? ' active' : '' }}" href="?desk=stats">统计</a>
        </div>

        @if($desk === 'stats')
            <p class="muted recycle-lead">人气为 hits，收藏来自会员收藏夹，图片数来自图库。</p>
            <div class="stat-grid dash manga-stats-grid">
                <div class="stat-card"><span>图集</span><strong>{{ (int) ($worksStat['all'] ?? 0) }}</strong><span class="muted">上架 {{ (int) ($worksStat['show'] ?? 0) }} · 待审 {{ (int) ($worksStat['pending'] ?? 0) }}</span></div>
                <div class="stat-card"><span>总人气</span><strong>{{ (int) ($stats['hit_total'] ?? 0) }}</strong><span class="muted">图片 {{ (int) ($stats['pic_total'] ?? 0) }}</span></div>
                <div class="stat-card"><span>近 7 日新图</span><strong>{{ (int) ($stats['week_pics'] ?? 0) }}</strong><span class="muted">近 30 日 {{ (int) ($stats['month_pics'] ?? 0) }}</span></div>
                <div class="stat-card"><span>收藏</span><strong>{{ (int) ($stats['favor_total'] ?? 0) }}</strong></div>
            </div>
            <div class="flink-stats-split manga-stats-split">
                <div>
                    <h3>人气 TOP</h3>
                    <ul class="plain-list">@forelse(($stats['top_hits'] ?? []) as $row)<li><a href="/admin/video/galleries?desk=works&q={{ urlencode($row['title']) }}">{{ $row['title'] }}</a> <em>{{ $row['hits'] }}</em></li>@empty<li class="muted">暂无</li>@endforelse</ul>
                </div>
                <div>
                    <h3>收藏 TOP</h3>
                    <ul class="plain-list">@forelse(($stats['top_favors'] ?? []) as $row)<li><a href="/admin/video/galleries?desk=works&q={{ urlencode($row['title']) }}">{{ $row['title'] }}</a> <em>{{ $row['favors'] }}</em></li>@empty<li class="muted">暂无</li>@endforelse</ul>
                </div>
            </div>
        @else
            @if(in_array($desk, ['works', 'pending'], true))
                <p class="muted recycle-lead">快捷填名称即可添加；作者、模特、标签、封面请用「完整表单」。</p>
                <div class="tag-compose" id="gallery-work-compose-box">
                    <form class="tag-compose-form" id="gallery-work-compose" onsubmit="return false;">
                        <label class="tag-compose-label" for="gallery-work-quick">新增图集</label>
                        <div class="tag-compose-row">
                            <input id="gallery-work-quick" type="text" name="title" placeholder="输入图集名" aria-label="新增图集" autofocus>
                            <span class="btn-split" role="group">
                                <button class="btn" type="submit">添加</button>
                                <a class="btn btn-muted" href="/admin/video/galleries/create{{ $desk === 'pending' ? '?desk=pending' : '' }}">完整表单</a>
                            </span>
                        </div>
                        <p class="muted field-hint">回车可连续添加。待审台添加的图集会标成待审。</p>
                    </form>
                </div>
            @elseif($desk === 'pics')
                <p class="muted recycle-lead">批量粘贴图片地址；远程 http(s) 会下载到本地，本地路径可直接粘贴。单张标题、排序请点「单张表单」或行内编辑。</p>
                <div class="tag-compose" id="gallery-pic-compose-box">
                    <form class="tag-compose-form" id="gallery-pic-compose" onsubmit="return false;">
                        <label class="tag-compose-label" for="gallery-pic-urls">批量添加图片</label>
                        <div class="tag-compose-row">
                            <select name="gallery_id" aria-label="图集" required style="max-width:220px">
                                <option value="">选择图集</option>
                                @foreach($works as $w)
                                    <option value="{{ $w->id }}">{{ $w->title }}</option>
                                @endforeach
                            </select>
                            <button class="btn" type="submit">添加</button>
                        </div>
                        <textarea id="gallery-pic-urls" name="urls" rows="4" placeholder="每行一个图片地址（http:// 会下载到本地，或直接贴 /upload/...）" aria-label="图片地址" style="width:100%;margin-top:8px;box-sizing:border-box"></textarea>
                        <p class="muted field-hint">先选图集，再粘贴多行地址。远程链接自动下载；空行会跳过。</p>
                    </form>
                </div>
            @elseif($desk === 'types')
                <p class="muted recycle-lead">分类挂在图集上，一部图集一个分类。</p>
            @elseif($desk === 'favors')
                <p class="muted recycle-lead">会员前台收藏后出现，后台不能代收藏。删除只清记录，不影响图集。</p>
            @elseif($desk === 'comments')
                <p class="muted recycle-lead">前台图集详情页提交的评论。可审核或删除。</p>
            @endif

            @if($desk === 'comments')
                <div class="queue-chips" id="gallery-comment-queues">
                    <button type="button" class="chip active" data-status="">全部 {{ (int) ($commentQueues['all'] ?? 0) }}</button>
                    <button type="button" class="chip" data-status="0">待审 {{ (int) ($commentQueues['pending'] ?? 0) }}</button>
                    <button type="button" class="chip" data-status="1">显示 {{ (int) ($commentQueues['pass'] ?? 0) }}</button>
                </div>
            @endif

            <form class="filter-bar" id="gallery-search" onsubmit="return false;">
                <input type="search" name="q" placeholder="{{ $desk === 'pics' ? '搜图片地址' : ($desk === 'types' ? '搜分类名' : ($desk === 'favors' ? '搜会员或图集 ID' : ($desk === 'comments' ? '搜评论、图集' : '搜标题、作者、标签'))) }}" autocomplete="off" aria-label="搜索">
                @if($desk === 'pics' && $works->isNotEmpty())
                    <select name="gallery_id" aria-label="按图集筛选">
                        <option value="">全部图集</option>
                        @foreach($works as $w)
                            <option value="{{ $w->id }}">{{ $w->title }}</option>
                        @endforeach
                    </select>
                @endif
                <button type="button" class="btn btn-sm" id="gallery-search-btn">查询</button>
                <button type="reset" class="btn btn-muted btn-sm" id="gallery-reset-btn">重置</button>
            </form>
            <div id="gallery-table"></div>
        @endif
    </div>
</div>
<template id="type-form">@include('gallery::admin.type_form')</template>
@endsection
@push('scripts')
<script>
(function () {
    var U = AdminUi, desk = @json($desk), url = '/admin/video/galleries';
    if (desk === 'stats' || !U) return;
    var countEl = document.getElementById('gallery-count');
    var search = document.getElementById('gallery-search');

    function where() {
        var w = Object.assign({ desk: desk, limit: 20 }, U.formData(search));
        Object.keys(w).forEach(function (k) { if (w[k] === '') delete w[k]; });
        return w;
    }
    function isFiltered(w) {
        return Object.keys(w || {}).some(function (k) {
            return k !== 'desk' && k !== 'limit' && w[k] !== '' && w[k] != null;
        });
    }
    function emptyHtml(_p, w) {
        if (isFiltered(w)) {
            return '<div class="list-empty"><p>没有符合条件的记录</p><p><button type="button" class="btn btn-muted btn-sm" id="gallery-empty-reset">清除筛选</button></p></div>';
        }
        if (desk === 'favors') return '<div class="list-empty"><p>还没有收藏记录</p><p class="muted">会员在前台点「收藏图集」后出现。</p></div>';
        if (desk === 'comments') return '<div class="list-empty"><p>还没有评论</p><p class="muted">会员在前台图集详情页提交后出现。</p></div>';
        if (desk === 'pics') return '<div class="list-empty"><p>还没有图片</p><p class="muted">上方选图集后粘贴多行地址即可批量添加。</p></div>';
        if (desk === 'types') return '<div class="list-empty"><p>还没有分类</p></div>';
        return '<div class="list-empty"><p>还没有图集</p><p class="muted">上方输入名称回车即可添加。</p></div>';
    }

    var cols = desk === 'pics'
        ? [
            { title: '图集', html: function (d) { return U.escape(d.gallery_title || d.gallery_id); } },
            { title: '图片', html: function (d) { return '<a class="btn-link" href="/admin/video/gallery-pics/' + d.id + '/edit">' + U.escape(d.url || '') + '</a>'; } },
            { title: '标题', width: 140, html: function (d) { return U.escape(d.title || '—'); } },
            { title: '操作', cls: 'actions', html: function (d) { return '<a class="btn-link" href="/admin/video/gallery-pics/' + d.id + '/edit">编辑</a> <a href="#" class="btn-link js-del">删除</a>'; } }
        ]
        : desk === 'types'
        ? [
            { title: '分类', html: function (d) { return '<a href="#" class="btn-link js-edit">' + U.escape(d.name || '') + '</a>'; } },
            { title: '别名', html: function (d) { return U.escape(d.slug || ''); } },
            { title: '操作', cls: 'actions', html: function () { return '<a href="#" class="btn-link js-edit">编辑</a> <a href="#" class="btn-link js-del">删除</a>'; } }
        ]
        : desk === 'favors'
        ? [
            { title: '会员', width: 100, html: function (d) { return U.escape(String(d.member_id)); } },
            { title: '图集', html: function (d) { return U.escape(d.gallery_title || String(d.gallery_id)); } },
            { title: '操作', cls: 'actions', html: function () { return '<a href="#" class="btn-link js-del">取消</a>'; } }
        ]
        : desk === 'comments'
        ? [
            { title: '内容', html: function (d) { return U.escape(d.content || ''); } },
            { title: '图集', html: function (d) { return U.escape(d.gallery_title || String(d.gallery_id)); } },
            { title: '昵称', width: 100, html: function (d) { return U.escape(d.author_name || ''); } },
            { title: '状态', width: 90, html: function (d) { return String(d.status) === '1' ? '显示' : '待审'; } },
            { title: '时间', width: 140, html: function (d) { return U.escape(d.created_label || ''); } },
            { title: '操作', cls: 'actions', html: function (d) {
                return '<a href="/gallery/' + encodeURIComponent(d.gallery_id || '') + '" class="btn-link" target="_blank" rel="noopener">前台</a> <a href="#" class="btn-link js-del">删除</a>';
            } }
        ]
        : [
            { title: '图集', html: function (d) { return '<a class="btn-link entry-row-title" href="/admin/video/galleries/' + d.id + '/edit">' + U.escape(d.title || '') + '</a>'; } },
            { title: '作者', width: 120, html: function (d) { return U.escape(d.author || '—'); } },
            { title: '标签', html: function (d) { return U.escape(d.tags || '—'); } },
            { title: '人气', width: 70, html: function (d) { return U.escape(String(d.hits || 0)); } },
            { title: '收藏', width: 70, html: function (d) { return U.escape(String(d.favor_count || 0)); } },
            { title: '状态', width: 90, html: function (d) {
                if (String(d.yid) === '1') return '<span class="badge badge-warn">待审</span>';
                return String(d.status) === '1' ? '上架' : '下架';
            } },
            { title: '操作', cls: 'actions', html: function (d) {
                return '<a href="/admin/video/galleries?desk=pics" class="btn-link">图片</a> <a class="btn-link" href="/admin/video/galleries/' + d.id + '/edit">编辑</a> <a href="/gallery/' + d.id + '" class="btn-link" target="_blank" rel="noopener">前台</a> <a href="#" class="btn-link js-del">删除</a>';
            } }
        ];

    var table = U.table({
        el: '#gallery-table',
        url: url + '/list',
        where: where(),
        cols: cols,
        emptyHtml: emptyHtml,
        onDraw: function (_wrap, _list, parsed) {
            if (countEl) countEl.textContent = parsed && parsed.total != null ? '· ' + parsed.total : '';
            var reset = document.getElementById('gallery-empty-reset');
            if (reset) reset.addEventListener('click', function () {
                search.reset();
                table.reload(where());
            });
        }
    });

    function openForm(row) {
        row = row || {};
        U.dialog({
            title: row.id ? '编辑分类' : '新增分类',
            content: document.getElementById('type-form').innerHTML,
            onOpen: function (box) { U.fillForm(box.querySelector('form'), row); },
            onSave: function (box) {
                var d = U.formData(box.querySelector('form'));
                d.desk = 'types';
                if (row.id) d.id = row.id;
                return U.post(url + '/save', d).then(function (r) {
                    if (!r || r.code !== 0) { U.toast((r && r.msg) || '失败', 'err'); return false; }
                    table.refresh();
                    U.toast((r && r.msg) || '已保存', 'ok');
                });
            }
        });
    }

    function quickWork(form) {
        var d = U.formData(form);
        d.desk = 'works';
        d.status = 1;
        d.yid = desk === 'pending' ? 1 : 0;
        if (!d.title) { U.toast('请填写图集名', 'err'); return; }
        U.post(url + '/save', d).then(function (r) {
            if (!r || r.code !== 0) { U.toast((r && r.msg) || '失败', 'err'); return; }
            form.reset();
            var focus = form.querySelector('input[type=text]');
            if (focus) focus.focus();
            table.refresh();
            U.toast((r && r.msg) || '已添加', 'ok');
        });
    }

    function quickPics(form) {
        var d = U.formData(form);
        d.desk = 'pics';
        if (!d.gallery_id) { U.toast('请选择图集', 'err'); return; }
        if (!String(d.urls || '').trim()) { U.toast('请粘贴图片地址', 'err'); return; }
        U.post(url + '/save', d).then(function (r) {
            if (!r || r.code !== 0) { U.toast((r && r.msg) || '失败', 'err'); return; }
            var keep = form.querySelector('[name=gallery_id]');
            var keepVal = keep ? keep.value : '';
            form.reset();
            if (keep && keepVal) keep.value = keepVal;
            table.refresh();
            U.toast((r && r.msg) || '已添加', 'ok');
        });
    }

    U.on('#add', 'click', function () { openForm(); });
    U.on('#gallery-work-compose', 'submit', function (e) { e.preventDefault(); quickWork(e.target); });
    U.on('#gallery-pic-compose', 'submit', function (e) { e.preventDefault(); quickPics(e.target); });
    U.on('#gallery-search-btn', 'click', function () { table.reload(where()); });
    U.on('#gallery-reset-btn', 'click', function () {
        setTimeout(function () { table.reload(where()); }, 0);
    });
    var commentQueues = document.getElementById('gallery-comment-queues');
    if (commentQueues) {
        commentQueues.addEventListener('click', function (e) {
            var chip = e.target.closest('.chip');
            if (!chip) return;
            Array.prototype.forEach.call(commentQueues.querySelectorAll('.chip'), function (c) { c.classList.remove('active'); });
            chip.classList.add('active');
            var statusInput = search.querySelector('[name=status]');
            if (!statusInput) {
                statusInput = document.createElement('input');
                statusInput.type = 'hidden';
                statusInput.name = 'status';
                search.appendChild(statusInput);
            }
            statusInput.value = chip.getAttribute('data-status') || '';
            table.reload(where());
        });
    }
    U.on('#gallery-search', 'keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); table.reload(where()); }
    });
    U.on('#gallery-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        var href = a.getAttribute('href') || '';
        if (href && href !== '#' && href.indexOf('javascript:') !== 0 && !a.classList.contains('js-edit') && !a.classList.contains('js-del')) return;
        e.preventDefault();
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        if (a.classList.contains('js-edit')) openForm(row);
        if (a.classList.contains('js-del') && U.confirm(desk === 'favors' ? '取消这条收藏？' : '确认删除？')) {
            U.post(url + '/delete', { id: row.id, desk: desk }).then(function (r) {
                if (!r || r.code !== 0) { U.toast((r && r.msg) || '失败', 'err'); return; }
                table.refresh();
            });
        }
    });
})();
</script>
@endpush
