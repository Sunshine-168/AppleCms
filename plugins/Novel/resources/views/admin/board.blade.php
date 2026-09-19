@extends('admin.layouts.inner')
@section('title', '小说')
@php
    $desk = in_array(request('desk', 'works'), ['works', 'pending', 'chapters', 'types', 'favors', 'comments', 'stats'], true)
        ? request('desk', 'works')
        : 'works';
    $stats = is_array($stats ?? null) ? $stats : [];
    $commentQueues = is_array($commentQueues ?? null) ? $commentQueues : ['all' => 0, 'pending' => 0, 'pass' => 0];
    $worksStat = is_array($stats['works'] ?? null) ? $stats['works'] : ['all' => 0, 'show' => 0, 'pending' => 0, 'off' => 0];
    $works = $works ?? collect();
    $types = $types ?? collect();
@endphp
@section('plain')
<div class="card card-panel desk-board" id="novel-board">
    <div class="card-header">
        <span>{{ $desk === 'stats' ? '小说统计' : '小说' }} <em id="novel-count"></em></span>
        @if(in_array($desk, ['works', 'pending'], true))
            <span class="btn-split" role="group" aria-label="添加作品">
                <a class="btn btn-sm" href="#novel-work-compose-box">新增作品</a>
                <a class="btn btn-muted btn-sm" href="/admin/video/novels/create{{ $desk === 'pending' ? '?desk=pending' : '' }}">完整表单</a>
            </span>
        @elseif($desk === 'chapters')
            <span class="btn-split" role="group" aria-label="添加章节">
                <a class="btn btn-sm" href="#novel-chapter-compose-box">新增章节</a>
                <a class="btn btn-muted btn-sm" href="/admin/video/novel-chapters/create">完整表单</a>
            </span>
        @elseif($desk === 'types')
            <button type="button" class="btn btn-sm" id="add">新增分类</button>
        @endif
    </div>
    <div class="card-body">
        <div class="queue-chips" id="novel-desks">
            <a class="chip{{ $desk === 'works' ? ' active' : '' }}" href="/admin/video/novels">作品</a>
            <a class="chip{{ $desk === 'pending' ? ' active' : '' }}" href="?desk=pending">待审</a>
            <a class="chip{{ $desk === 'chapters' ? ' active' : '' }}" href="?desk=chapters">章节</a>
            <a class="chip{{ $desk === 'types' ? ' active' : '' }}" href="?desk=types">分类</a>
            <a class="chip{{ $desk === 'favors' ? ' active' : '' }}" href="?desk=favors">书架</a>
            <a class="chip{{ $desk === 'comments' ? ' active' : '' }}" href="?desk=comments">评论</a>
            <a class="chip{{ $desk === 'stats' ? ' active' : '' }}" href="?desk=stats">统计</a>
        </div>

        @if($desk === 'stats')
            <p class="muted recycle-lead">阅读来自会员历史，人气为 hits，收藏来自书架。</p>
            <div class="stat-grid dash manga-stats-grid">
                <div class="stat-card"><span>作品</span><strong>{{ (int) ($worksStat['all'] ?? 0) }}</strong><span class="muted">上架 {{ (int) ($worksStat['show'] ?? 0) }} · 待审 {{ (int) ($worksStat['pending'] ?? 0) }}</span></div>
                <div class="stat-card"><span>今日阅读</span><strong>{{ (int) ($stats['today_reads'] ?? 0) }}</strong><span class="muted">近 7 日 {{ (int) ($stats['week_reads'] ?? 0) }}</span></div>
                <div class="stat-card"><span>近 7 日新章</span><strong>{{ (int) ($stats['week_chapters'] ?? 0) }}</strong><span class="muted">近 30 日 {{ (int) ($stats['month_chapters'] ?? 0) }}</span></div>
                <div class="stat-card"><span>书架收藏</span><strong>{{ (int) ($stats['favor_total'] ?? 0) }}</strong></div>
            </div>
            <div class="flink-stats-split manga-stats-split">
                <div>
                    <h3>人气 TOP</h3>
                    <ul class="plain-list">@forelse(($stats['top_hits'] ?? []) as $row)<li><a href="/admin/video/novels?desk=works&q={{ urlencode($row['title']) }}">{{ $row['title'] }}</a> <em>{{ $row['hits'] }}</em></li>@empty<li class="muted">暂无</li>@endforelse</ul>
                </div>
                <div>
                    <h3>收藏 TOP</h3>
                    <ul class="plain-list">@forelse(($stats['top_favors'] ?? []) as $row)<li><a href="/admin/video/novels?desk=works&q={{ urlencode($row['title']) }}">{{ $row['title'] }}</a> <em>{{ $row['favors'] }}</em></li>@empty<li class="muted">暂无</li>@endforelse</ul>
                </div>
            </div>
        @else
            @if(in_array($desk, ['works', 'pending'], true))
                <p class="muted recycle-lead">快捷填名称即可添加；作者、分类、标签、封面请用「完整表单」。</p>
                <div class="tag-compose" id="novel-work-compose-box">
                    <form class="tag-compose-form" id="novel-work-compose" onsubmit="return false;">
                        <label class="tag-compose-label" for="novel-work-quick">新增作品</label>
                        <div class="tag-compose-row">
                            <input id="novel-work-quick" type="text" name="title" placeholder="输入作品名" aria-label="新增作品" autofocus>
                            <span class="btn-split" role="group">
                                <button class="btn" type="submit">添加</button>
                                <a class="btn btn-muted" href="/admin/video/novels/create{{ $desk === 'pending' ? '?desk=pending' : '' }}">完整表单</a>
                            </span>
                        </div>
                        <p class="muted field-hint">回车可连续添加。待审台添加的作品会标成待审。</p>
                    </form>
                </div>
            @elseif($desk === 'chapters')
                <p class="muted recycle-lead">快捷只填章节名；正文、VIP、排序请用「完整表单」。</p>
                <div class="tag-compose" id="novel-chapter-compose-box">
                    <form class="tag-compose-form" id="novel-chapter-compose" onsubmit="return false;">
                        <label class="tag-compose-label" for="novel-chapter-quick">新增章节</label>
                        <div class="tag-compose-row">
                            <select name="novel_id" aria-label="作品" required style="max-width:200px">
                                <option value="">选择作品</option>
                                @foreach($works as $w)
                                    <option value="{{ $w->id }}">{{ $w->title }}</option>
                                @endforeach
                            </select>
                            <input id="novel-chapter-quick" type="text" name="name" placeholder="如 第一章" aria-label="新增章节" autofocus>
                            <span class="btn-split" role="group">
                                <button class="btn" type="submit">添加</button>
                                <a class="btn btn-muted" href="/admin/video/novel-chapters/create">完整表单</a>
                            </span>
                        </div>
                        <p class="muted field-hint">回车可连续添加。先选作品再填章节名。</p>
                    </form>
                </div>
            @elseif($desk === 'types')
                <p class="muted recycle-lead">分类挂在作品上，一部作品一个分类。</p>
            @elseif($desk === 'favors')
                <p class="muted recycle-lead">会员前台加入书架后出现，后台不能代收藏。删除只清记录，不影响作品。</p>
            @elseif($desk === 'comments')
                <p class="muted recycle-lead">前台小说详情页提交的评论。可审核或删除。</p>
            @endif

            @if($desk === 'comments')
                <div class="queue-chips" id="novel-comment-queues">
                    <button type="button" class="chip active" data-status="">全部 {{ (int) ($commentQueues['all'] ?? 0) }}</button>
                    <button type="button" class="chip" data-status="0">待审 {{ (int) ($commentQueues['pending'] ?? 0) }}</button>
                    <button type="button" class="chip" data-status="1">显示 {{ (int) ($commentQueues['pass'] ?? 0) }}</button>
                </div>
            @endif

            <form class="filter-bar" id="novel-search" onsubmit="return false;">
                <input type="search" name="q" placeholder="{{ $desk === 'chapters' ? '搜章节名' : ($desk === 'types' ? '搜分类名' : ($desk === 'favors' ? '搜会员或作品 ID' : ($desk === 'comments' ? '搜评论、作品' : '搜标题、作者、标签'))) }}" autocomplete="off" aria-label="搜索">
                @if($desk === 'chapters' && $works->isNotEmpty())
                    <select name="novel_id" aria-label="按作品筛选">
                        <option value="">全部作品</option>
                        @foreach($works as $w)
                            <option value="{{ $w->id }}">{{ $w->title }}</option>
                        @endforeach
                    </select>
                @endif
                <button type="button" class="btn btn-sm" id="novel-search-btn">查询</button>
                <button type="reset" class="btn btn-muted btn-sm" id="novel-reset-btn">重置</button>
            </form>
            <div id="novel-table"></div>
        @endif
    </div>
</div>
<template id="type-form">@include('novel::admin.type_form')</template>
@endsection
@push('scripts')
<script>
(function () {
    var U = AdminUi, desk = @json($desk), url = '/admin/video/novels';
    if (desk === 'stats' || !U) return;
    var countEl = document.getElementById('novel-count');
    var search = document.getElementById('novel-search');

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
            return '<div class="list-empty"><p>没有符合条件的记录</p><p><button type="button" class="btn btn-muted btn-sm" id="novel-empty-reset">清除筛选</button></p></div>';
        }
        if (desk === 'favors') return '<div class="list-empty"><p>还没有书架记录</p><p class="muted">会员在前台点「加入书架」后出现。</p></div>';
        if (desk === 'comments') return '<div class="list-empty"><p>还没有评论</p><p class="muted">会员在前台小说详情页提交后出现。</p></div>';
        if (desk === 'chapters') return '<div class="list-empty"><p>还没有章节</p><p class="muted">上方快捷添加章节名，或打开完整表单写正文。</p></div>';
        if (desk === 'types') return '<div class="list-empty"><p>还没有分类</p></div>';
        return '<div class="list-empty"><p>还没有作品</p><p class="muted">上方输入名称回车即可添加。</p></div>';
    }

    var cols = desk === 'chapters'
        ? [
            { title: '作品', html: function (d) { return U.escape(d.novel_title || d.novel_id); } },
            { title: '章节', html: function (d) { return '<a class="btn-link" href="/admin/video/novel-chapters/' + d.id + '/edit">' + U.escape(d.name || '') + '</a>'; } },
            { title: '排序', width: 70, html: function (d) { return U.escape(String(d.sort || 0)); } },
            { title: '权限', width: 70, html: function (d) { return String(d.vip) === '1' ? '<span class="badge">VIP</span>' : '免费'; } },
            { title: '操作', cls: 'actions', html: function (d) { return '<a class="btn-link" href="/admin/video/novel-chapters/' + d.id + '/edit">编辑</a> <a href="#" class="btn-link js-del">删除</a>'; } }
        ]
        : desk === 'types'
        ? [
            { title: '分类', html: function (d) { return '<a href="#" class="btn-link js-edit">' + U.escape(d.name || '') + '</a>'; } },
            { title: '别名', html: function (d) { return U.escape(d.slug || ''); } },
            { title: '排序', width: 70, html: function (d) { return U.escape(String(d.sort || 0)); } },
            { title: '操作', cls: 'actions', html: function () { return '<a href="#" class="btn-link js-edit">编辑</a> <a href="#" class="btn-link js-del">删除</a>'; } }
        ]
        : desk === 'favors'
        ? [
            { title: '会员', width: 100, html: function (d) { return U.escape(String(d.member_id)); } },
            { title: '作品', html: function (d) { return U.escape(d.novel_title || String(d.novel_id)); } },
            { title: '操作', cls: 'actions', html: function () { return '<a href="#" class="btn-link js-del">取消</a>'; } }
        ]
        : desk === 'comments'
        ? [
            { title: '内容', html: function (d) { return U.escape(d.content || ''); } },
            { title: '作品', html: function (d) { return U.escape(d.novel_title || String(d.novel_id)); } },
            { title: '昵称', width: 100, html: function (d) { return U.escape(d.author_name || ''); } },
            { title: '状态', width: 90, html: function (d) { return String(d.status) === '1' ? '显示' : '待审'; } },
            { title: '时间', width: 140, html: function (d) { return U.escape(d.created_label || ''); } },
            { title: '操作', cls: 'actions', html: function (d) {
                return '<a href="/novel/' + encodeURIComponent(d.novel_id || '') + '" class="btn-link" target="_blank" rel="noopener">前台</a> <a href="#" class="btn-link js-del">删除</a>';
            } }
        ]
        : [
            { title: '作品', html: function (d) { return '<a class="btn-link entry-row-title" href="/admin/video/novels/' + d.id + '/edit">' + U.escape(d.title || '') + '</a>'; } },
            { title: '作者', width: 120, html: function (d) { return U.escape(d.author || '—'); } },
            { title: '标签', html: function (d) { return U.escape(d.tags || '—'); } },
            { title: '人气', width: 70, html: function (d) { return U.escape(String(d.hits || 0)); } },
            { title: '收藏', width: 70, html: function (d) { return U.escape(String(d.favor_count || 0)); } },
            { title: '状态', width: 90, html: function (d) {
                if (String(d.yid) === '1') return '<span class="badge badge-warn">待审</span>';
                return String(d.status) === '1' ? '上架' : '下架';
            } },
            { title: '操作', cls: 'actions', html: function (d) {
                return '<a href="/admin/video/novels?desk=chapters" class="btn-link">章节</a> <a class="btn-link" href="/admin/video/novels/' + d.id + '/edit">编辑</a> <a href="/novel/' + d.id + '" class="btn-link" target="_blank" rel="noopener">前台</a> <a href="#" class="btn-link js-del">删除</a>';
            } }
        ];

    var table = U.table({
        el: '#novel-table',
        url: url + '/list',
        where: where(),
        cols: cols,
        emptyHtml: emptyHtml,
        onDraw: function (_wrap, _list, parsed) {
            if (countEl) countEl.textContent = parsed && parsed.total != null ? '· ' + parsed.total : '';
            var reset = document.getElementById('novel-empty-reset');
            if (reset) reset.addEventListener('click', function () {
                search.reset();
                table.reload(where());
            });
        }
    });

    function formId() {
        return 'type-form';
    }
    function openForm(row) {
        row = row || {};
        U.dialog({
            title: row.id ? '编辑分类' : '新增分类',
            content: document.getElementById(formId()).innerHTML,
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

    function quickSave(form) {
        var d = U.formData(form);
        d.desk = desk === 'pending' ? 'works' : desk;
        if (desk === 'pending') d.yid = 1;
        if (desk === 'works' || desk === 'pending') {
            d.status = 1;
            if (d.yid == null) d.yid = desk === 'pending' ? 1 : 0;
        }
        if (!d.title && !d.name) { U.toast(desk === 'chapters' ? '请填写章节名' : '请填写作品名', 'err'); return; }
        if (desk === 'chapters' && !d.novel_id) { U.toast('请选择作品', 'err'); return; }
        U.post(url + '/save', d).then(function (r) {
            if (!r || r.code !== 0) { U.toast((r && r.msg) || '失败', 'err'); return; }
            var keep = form.querySelector('[name=novel_id]');
            var keepVal = keep ? keep.value : '';
            form.reset();
            if (keep && keepVal) keep.value = keepVal;
            var focus = form.querySelector('input[type=text]');
            if (focus) focus.focus();
            table.refresh();
            U.toast((r && r.msg) || '已添加', 'ok');
        });
    }

    U.on('#add', 'click', function () { openForm(); });
    U.on('#novel-work-compose', 'submit', function (e) { e.preventDefault(); quickSave(e.target); });
    U.on('#novel-chapter-compose', 'submit', function (e) { e.preventDefault(); quickSave(e.target); });
    U.on('#novel-search-btn', 'click', function () { table.reload(where()); });
    U.on('#novel-reset-btn', 'click', function () {
        setTimeout(function () { table.reload(where()); }, 0);
    });
    var commentQueues = document.getElementById('novel-comment-queues');
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
    U.on('#novel-search', 'keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); table.reload(where()); }
    });
    U.on('#novel-table', 'click', function (e) {
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
