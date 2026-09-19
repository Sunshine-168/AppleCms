@extends('admin.layouts.inner')
@section('title', admin_t('novel.title'))
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
        <span>{{ $desk === 'stats' ? admin_t('novel.title_stats') : admin_t('novel.title') }} <em id="novel-count"></em></span>
        @if(in_array($desk, ['works', 'pending'], true))
            <span class="btn-split" role="group" aria-label="{{ admin_t('novel.add_work_aria') }}">
                <a class="btn btn-sm" href="#novel-work-compose-box">{{ admin_t('ui.add_work') }}</a>
                <a class="btn btn-muted btn-sm" href="/admin/video/novels/create{{ $desk === 'pending' ? '?desk=pending' : '' }}">{{ admin_t('ui.full_form') }}</a>
            </span>
        @elseif($desk === 'chapters')
            <span class="btn-split" role="group" aria-label="{{ admin_t('novel.add_chapter_aria') }}">
                <a class="btn btn-sm" href="#novel-chapter-compose-box">{{ admin_t('ui.add_chapter') }}</a>
                <a class="btn btn-muted btn-sm" href="/admin/video/novel-chapters/create">{{ admin_t('ui.full_form') }}</a>
            </span>
        @elseif($desk === 'types')
            <button type="button" class="btn btn-sm" id="add">{{ admin_t('ui.add_type') }}</button>
        @endif
    </div>
    <div class="card-body">
        <div class="queue-chips" id="novel-desks">
            <a class="chip{{ $desk === 'works' ? ' active' : '' }}" href="/admin/video/novels">{{ admin_t('ui.works') }}</a>
            <a class="chip{{ $desk === 'pending' ? ' active' : '' }}" href="?desk=pending">{{ admin_t('ui.pending') }}</a>
            <a class="chip{{ $desk === 'chapters' ? ' active' : '' }}" href="?desk=chapters">{{ admin_t('ui.chapters') }}</a>
            <a class="chip{{ $desk === 'types' ? ' active' : '' }}" href="?desk=types">{{ admin_t('ui.types') }}</a>
            <a class="chip{{ $desk === 'favors' ? ' active' : '' }}" href="?desk=favors">{{ admin_t('ui.bookshelf') }}</a>
            <a class="chip{{ $desk === 'comments' ? ' active' : '' }}" href="?desk=comments">{{ admin_t('ui.comments') }}</a>
            <a class="chip{{ $desk === 'stats' ? ' active' : '' }}" href="?desk=stats">{{ admin_t('ui.stats') }}</a>
        </div>

        @if($desk === 'stats')
            <p class="muted recycle-lead">{{ admin_t('novel.lead_stats') }}</p>
            <div class="stat-grid dash manga-stats-grid">
                <div class="stat-card"><span>{{ admin_t('ui.works') }}</span><strong>{{ (int) ($worksStat['all'] ?? 0) }}</strong><span class="muted">{{ admin_t('ui.on_count', ['n' => (int) ($worksStat['show'] ?? 0)]) }} · {{ admin_t('ui.pending_count', ['n' => (int) ($worksStat['pending'] ?? 0)]) }}</span></div>
                <div class="stat-card"><span>{{ admin_t('novel.today_reads') }}</span><strong>{{ (int) ($stats['today_reads'] ?? 0) }}</strong><span class="muted">{{ admin_t('ui.week_7') }} {{ (int) ($stats['week_reads'] ?? 0) }}</span></div>
                <div class="stat-card"><span>{{ admin_t('novel.week_chapters') }}</span><strong>{{ (int) ($stats['week_chapters'] ?? 0) }}</strong><span class="muted">{{ admin_t('ui.month_30') }} {{ (int) ($stats['month_chapters'] ?? 0) }}</span></div>
                <div class="stat-card"><span>{{ admin_t('novel.bookshelf_total') }}</span><strong>{{ (int) ($stats['favor_total'] ?? 0) }}</strong></div>
            </div>
            <div class="flink-stats-split manga-stats-split">
                <div>
                    <h3>{{ admin_t('ui.top_hits') }}</h3>
                    <ul class="plain-list">@forelse(($stats['top_hits'] ?? []) as $row)<li><a href="/admin/video/novels?desk=works&q={{ urlencode($row['title']) }}">{{ $row['title'] }}</a> <em>{{ $row['hits'] }}</em></li>@empty<li class="muted">{{ admin_t('ui.none') }}</li>@endforelse</ul>
                </div>
                <div>
                    <h3>{{ admin_t('novel.top_favors') }}</h3>
                    <ul class="plain-list">@forelse(($stats['top_favors'] ?? []) as $row)<li><a href="/admin/video/novels?desk=works&q={{ urlencode($row['title']) }}">{{ $row['title'] }}</a> <em>{{ $row['favors'] }}</em></li>@empty<li class="muted">{{ admin_t('ui.none') }}</li>@endforelse</ul>
                </div>
            </div>
        @else
            @if(in_array($desk, ['works', 'pending'], true))
                <p class="muted recycle-lead">{{ admin_t('novel.compose_work_lead') }}</p>
                <div class="tag-compose" id="novel-work-compose-box">
                    <form class="tag-compose-form" id="novel-work-compose" onsubmit="return false;">
                        <label class="tag-compose-label" for="novel-work-quick">{{ admin_t('ui.add_work') }}</label>
                        <div class="tag-compose-row">
                            <input id="novel-work-quick" type="text" name="title" placeholder="{{ admin_t('novel.ph_work') }}" aria-label="{{ admin_t('ui.add_work') }}" autofocus>
                            <span class="btn-split" role="group">
                                <button class="btn" type="submit">{{ admin_t('ui.add') }}</button>
                                <a class="btn btn-muted" href="/admin/video/novels/create{{ $desk === 'pending' ? '?desk=pending' : '' }}">{{ admin_t('ui.full_form') }}</a>
                            </span>
                        </div>
                        <p class="muted field-hint">{{ admin_t('novel.hint_work') }}</p>
                    </form>
                </div>
            @elseif($desk === 'chapters')
                <p class="muted recycle-lead">{{ admin_t('novel.compose_chapter_lead') }}</p>
                <div class="tag-compose" id="novel-chapter-compose-box">
                    <form class="tag-compose-form" id="novel-chapter-compose" onsubmit="return false;">
                        <label class="tag-compose-label" for="novel-chapter-quick">{{ admin_t('ui.add_chapter') }}</label>
                        <div class="tag-compose-row">
                            <select name="novel_id" aria-label="{{ admin_t('ui.works') }}" required style="max-width:200px">
                                <option value="">{{ admin_t('novel.select_work') }}</option>
                                @foreach($works as $w)
                                    <option value="{{ $w->id }}">{{ $w->title }}</option>
                                @endforeach
                            </select>
                            <input id="novel-chapter-quick" type="text" name="name" placeholder="{{ admin_t('novel.ph_chapter') }}" aria-label="{{ admin_t('ui.add_chapter') }}" autofocus>
                            <span class="btn-split" role="group">
                                <button class="btn" type="submit">{{ admin_t('ui.add') }}</button>
                                <a class="btn btn-muted" href="/admin/video/novel-chapters/create">{{ admin_t('ui.full_form') }}</a>
                            </span>
                        </div>
                        <p class="muted field-hint">{{ admin_t('novel.hint_chapter') }}</p>
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
                    <button type="button" class="chip active" data-status="">{{ admin_t('ui.all') }} {{ (int) ($commentQueues['all'] ?? 0) }}</button>
                    <button type="button" class="chip" data-status="0">{{ admin_t('ui.pending') }} {{ (int) ($commentQueues['pending'] ?? 0) }}</button>
                    <button type="button" class="chip" data-status="1">{{ admin_t('ui.visible') }} {{ (int) ($commentQueues['pass'] ?? 0) }}</button>
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
                <button type="button" class="btn btn-sm" id="novel-search-btn">{{ admin_t('ui.search') }}</button>
                <button type="reset" class="btn btn-muted btn-sm" id="novel-reset-btn">{{ admin_t('ui.reset') }}</button>
            </form>
            <div id="novel-table"></div>
        @endif
    </div>
</div>
<template id="type-form">@include('novel::admin.type_form')</template>
@endsection
@php
    $novelJsLang = [
        'works' => admin_t('ui.works'),
        'chapters' => admin_t('ui.chapters'),
        'types' => admin_t('ui.types'),
        'actions' => admin_t('ui.actions'),
        'sort' => admin_t('ui.sort'),
        'edit' => admin_t('ui.edit'),
        'delete' => admin_t('ui.delete'),
        'add_type' => admin_t('ui.add_type'),
        'fail' => admin_t('ui.fail'),
        'saved' => admin_t('ui.saved'),
        'added' => admin_t('ui.added'),
        'front' => admin_t('ui.front'),
        'hits' => admin_t('ui.hits'),
        'favors' => admin_t('ui.favors'),
        'status' => admin_t('ui.status'),
        'on' => admin_t('ui.on'),
        'off' => admin_t('ui.off'),
        'pending' => admin_t('ui.pending'),
        'visible' => admin_t('ui.visible'),
        'member' => admin_t('ui.member'),
        'free' => admin_t('ui.free'),
        'alias' => admin_t('ui.alias'),
        'time' => admin_t('ui.time'),
        'content' => admin_t('ui.content'),
        'nickname' => admin_t('ui.nickname'),
        'authors' => admin_t('ui.authors'),
        'tags' => admin_t('ui.tags'),
        'no_match' => admin_t('ui.no_match'),
        'clear_filter' => admin_t('ui.clear_filter'),
        'col_access' => admin_t('novel.col_access'),
        'cancel_favor' => admin_t('novel.cancel_favor'),
        'empty_works' => admin_t('novel.empty_works'),
        'empty_works_hint' => admin_t('novel.empty_works_hint'),
        'empty_chapters' => admin_t('novel.empty_chapters'),
        'empty_chapters_hint' => admin_t('novel.empty_chapters_hint'),
        'empty_types' => admin_t('novel.empty_types'),
        'empty_favors' => admin_t('novel.empty_favors'),
        'empty_favors_hint' => admin_t('novel.empty_favors_hint'),
        'empty_comments' => admin_t('novel.empty_comments'),
        'empty_comments_hint' => admin_t('novel.empty_comments_hint'),
        'need_work_title' => admin_t('novel.need_work_title'),
        'need_chapter_name' => admin_t('novel.need_chapter_name'),
        'select_work' => admin_t('novel.select_work'),
    ];
@endphp
@push('scripts')
<script>
(function () {
    var U = AdminUi, desk = @json($desk), url = '/admin/video/novels', L = @json($novelJsLang);
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
            return '<div class="list-empty"><p>' + L.no_match + '</p><p><button type="button" class="btn btn-muted btn-sm" id="novel-empty-reset">' + L.clear_filter + '</button></p></div>';
        }
        if (desk === 'favors') return '<div class="list-empty"><p>' + L.empty_favors + '</p><p class="muted">' + L.empty_favors_hint + '</p></div>';
        if (desk === 'comments') return '<div class="list-empty"><p>' + L.empty_comments + '</p><p class="muted">' + L.empty_comments_hint + '</p></div>';
        if (desk === 'chapters') return '<div class="list-empty"><p>' + L.empty_chapters + '</p><p class="muted">' + L.empty_chapters_hint + '</p></div>';
        if (desk === 'types') return '<div class="list-empty"><p>' + L.empty_types + '</p></div>';
        return '<div class="list-empty"><p>' + L.empty_works + '</p><p class="muted">' + L.empty_works_hint + '</p></div>';
    }

    var cols = desk === 'chapters'
        ? [
            { title: L.works, html: function (d) { return U.escape(d.novel_title || d.novel_id); } },
            { title: L.chapters, html: function (d) { return '<a class="btn-link" href="/admin/video/novel-chapters/' + d.id + '/edit">' + U.escape(d.name || '') + '</a>'; } },
            { title: L.sort, width: 70, html: function (d) { return U.escape(String(d.sort || 0)); } },
            { title: L.col_access, width: 70, html: function (d) { return String(d.vip) === '1' ? '<span class="badge">VIP</span>' : L.free; } },
            { title: L.actions, cls: 'actions', html: function (d) { return '<a class="btn-link" href="/admin/video/novel-chapters/' + d.id + '/edit">' + L.edit + '</a> <a href="#" class="btn-link js-del">' + L.delete + '</a>'; } }
        ]
        : desk === 'types'
        ? [
            { title: L.types, html: function (d) { return '<a href="#" class="btn-link js-edit">' + U.escape(d.name || '') + '</a>'; } },
            { title: L.alias, html: function (d) { return U.escape(d.slug || ''); } },
            { title: L.sort, width: 70, html: function (d) { return U.escape(String(d.sort || 0)); } },
            { title: L.actions, cls: 'actions', html: function () { return '<a href="#" class="btn-link js-edit">' + L.edit + '</a> <a href="#" class="btn-link js-del">' + L.delete + '</a>'; } }
        ]
        : desk === 'favors'
        ? [
            { title: L.member, width: 100, html: function (d) { return U.escape(String(d.member_id)); } },
            { title: L.works, html: function (d) { return U.escape(d.novel_title || String(d.novel_id)); } },
            { title: L.actions, cls: 'actions', html: function () { return '<a href="#" class="btn-link js-del">' + L.cancel_favor + '</a>'; } }
        ]
        : desk === 'comments'
        ? [
            { title: L.content, html: function (d) { return U.escape(d.content || ''); } },
            { title: L.works, html: function (d) { return U.escape(d.novel_title || String(d.novel_id)); } },
            { title: L.nickname, width: 100, html: function (d) { return U.escape(d.author_name || ''); } },
            { title: L.status, width: 90, html: function (d) { return String(d.status) === '1' ? L.visible : L.pending; } },
            { title: L.time, width: 140, html: function (d) { return U.escape(d.created_label || ''); } },
            { title: L.actions, cls: 'actions', html: function (d) {
                return '<a href="/novel/' + encodeURIComponent(d.novel_id || '') + '" class="btn-link" target="_blank" rel="noopener">' + L.front + '</a> <a href="#" class="btn-link js-del">' + L.delete + '</a>';
            } }
        ]
        : [
            { title: L.works, html: function (d) { return '<a class="btn-link entry-row-title" href="/admin/video/novels/' + d.id + '/edit">' + U.escape(d.title || '') + '</a>'; } },
            { title: L.authors, width: 120, html: function (d) { return U.escape(d.author || '—'); } },
            { title: L.tags, html: function (d) { return U.escape(d.tags || '—'); } },
            { title: L.hits, width: 70, html: function (d) { return U.escape(String(d.hits || 0)); } },
            { title: L.favors, width: 70, html: function (d) { return U.escape(String(d.favor_count || 0)); } },
            { title: L.status, width: 90, html: function (d) {
                if (String(d.yid) === '1') return '<span class="badge badge-warn">' + L.pending + '</span>';
                return String(d.status) === '1' ? L.on : L.off;
            } },
            { title: L.actions, cls: 'actions', html: function (d) {
                return '<a href="/admin/video/novels?desk=chapters" class="btn-link">' + L.chapters + '</a> <a class="btn-link" href="/admin/video/novels/' + d.id + '/edit">' + L.edit + '</a> <a href="/novel/' + d.id + '" class="btn-link" target="_blank" rel="noopener">' + L.front + '</a> <a href="#" class="btn-link js-del">' + L.delete + '</a>';
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
            title: row.id ? L.edit + ' · ' + L.types : L.add_type,
            content: document.getElementById(formId()).innerHTML,
            onOpen: function (box) { U.fillForm(box.querySelector('form'), row); },
            onSave: function (box) {
                var d = U.formData(box.querySelector('form'));
                d.desk = 'types';
                if (row.id) d.id = row.id;
                return U.post(url + '/save', d).then(function (r) {
                    if (!r || r.code !== 0) { U.toast((r && r.msg) || L.fail, 'err'); return false; }
                    table.refresh();
                    U.toast((r && r.msg) || L.saved, 'ok');
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
        if (!d.title && !d.name) { U.toast(desk === 'chapters' ? L.need_chapter_name : L.need_work_title, 'err'); return; }
        if (desk === 'chapters' && !d.novel_id) { U.toast(L.select_work, 'err'); return; }
        U.post(url + '/save', d).then(function (r) {
            if (!r || r.code !== 0) { U.toast((r && r.msg) || L.fail, 'err'); return; }
            var keep = form.querySelector('[name=novel_id]');
            var keepVal = keep ? keep.value : '';
            form.reset();
            if (keep && keepVal) keep.value = keepVal;
            var focus = form.querySelector('input[type=text]');
            if (focus) focus.focus();
            table.refresh();
            U.toast((r && r.msg) || L.added, 'ok');
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
