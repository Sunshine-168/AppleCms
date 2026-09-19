@extends('admin.layouts.inner')
@section('title', admin_t('gallery.title'))
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
        <span>{{ $desk === 'stats' ? admin_t('gallery.title_stats') : admin_t('gallery.title') }} <em id="gallery-count"></em></span>
        @if(in_array($desk, ['works', 'pending'], true))
            <span class="btn-split" role="group" aria-label="{{ admin_t('gallery.add_work_aria') }}">
                <a class="btn btn-sm" href="#gallery-work-compose-box">{{ admin_t('gallery.add_gallery') }}</a>
                <a class="btn btn-muted btn-sm" href="/admin/video/galleries/create{{ $desk === 'pending' ? '?desk=pending' : '' }}">{{ admin_t('ui.full_form') }}</a>
            </span>
        @elseif($desk === 'pics')
            <span class="btn-split" role="group" aria-label="{{ admin_t('gallery.add_pic_aria') }}">
                <a class="btn btn-sm" href="#gallery-pic-compose-box">{{ admin_t('ui.batch_add') }}</a>
                <a class="btn btn-muted btn-sm" href="/admin/video/gallery-pics/create">{{ admin_t('ui.single_form') }}</a>
            </span>
        @elseif($desk === 'types')
            <button type="button" class="btn btn-sm" id="add">{{ admin_t('ui.add_type') }}</button>
        @endif
    </div>
    <div class="card-body">
        <div class="queue-chips" id="gallery-desks">
            <a class="chip{{ $desk === 'works' ? ' active' : '' }}" href="/admin/video/galleries">{{ admin_t('gallery.title') }}</a>
            <a class="chip{{ $desk === 'pending' ? ' active' : '' }}" href="?desk=pending">{{ admin_t('ui.pending') }}</a>
            <a class="chip{{ $desk === 'pics' ? ' active' : '' }}" href="?desk=pics">{{ admin_t('ui.pics') }}</a>
            <a class="chip{{ $desk === 'types' ? ' active' : '' }}" href="?desk=types">{{ admin_t('ui.types') }}</a>
            <a class="chip{{ $desk === 'favors' ? ' active' : '' }}" href="?desk=favors">{{ admin_t('ui.favors') }}</a>
            <a class="chip{{ $desk === 'comments' ? ' active' : '' }}" href="?desk=comments">{{ admin_t('ui.comments') }}</a>
            <a class="chip{{ $desk === 'stats' ? ' active' : '' }}" href="?desk=stats">{{ admin_t('ui.stats') }}</a>
        </div>

        @if($desk === 'stats')
            <p class="muted recycle-lead">{{ admin_t('gallery.lead_stats') }}</p>
            <div class="stat-grid dash manga-stats-grid">
                <div class="stat-card"><span>{{ admin_t('gallery.title') }}</span><strong>{{ (int) ($worksStat['all'] ?? 0) }}</strong><span class="muted">{{ admin_t('ui.on_count', ['n' => (int) ($worksStat['show'] ?? 0)]) }} · {{ admin_t('ui.pending_count', ['n' => (int) ($worksStat['pending'] ?? 0)]) }}</span></div>
                <div class="stat-card"><span>{{ admin_t('gallery.hit_total') }}</span><strong>{{ (int) ($stats['hit_total'] ?? 0) }}</strong><span class="muted">{{ admin_t('ui.pics') }} {{ (int) ($stats['pic_total'] ?? 0) }}</span></div>
                <div class="stat-card"><span>{{ admin_t('gallery.week_pics') }}</span><strong>{{ (int) ($stats['week_pics'] ?? 0) }}</strong><span class="muted">{{ admin_t('ui.month_30') }} {{ (int) ($stats['month_pics'] ?? 0) }}</span></div>
                <div class="stat-card"><span>{{ admin_t('ui.favors') }}</span><strong>{{ (int) ($stats['favor_total'] ?? 0) }}</strong></div>
            </div>
            <div class="flink-stats-split manga-stats-split">
                <div>
                    <h3>{{ admin_t('ui.top_hits') }}</h3>
                    <ul class="plain-list">@forelse(($stats['top_hits'] ?? []) as $row)<li><a href="/admin/video/galleries?desk=works&q={{ urlencode($row['title']) }}">{{ $row['title'] }}</a> <em>{{ $row['hits'] }}</em></li>@empty<li class="muted">{{ admin_t('ui.none') }}</li>@endforelse</ul>
                </div>
                <div>
                    <h3>{{ admin_t('gallery.top_favors') }}</h3>
                    <ul class="plain-list">@forelse(($stats['top_favors'] ?? []) as $row)<li><a href="/admin/video/galleries?desk=works&q={{ urlencode($row['title']) }}">{{ $row['title'] }}</a> <em>{{ $row['favors'] }}</em></li>@empty<li class="muted">{{ admin_t('ui.none') }}</li>@endforelse</ul>
                </div>
            </div>
        @else
            @if(in_array($desk, ['works', 'pending'], true))
                <p class="muted recycle-lead">{{ admin_t('gallery.compose_work_lead') }}</p>
                <div class="tag-compose" id="gallery-work-compose-box">
                    <form class="tag-compose-form" id="gallery-work-compose" onsubmit="return false;">
                        <label class="tag-compose-label" for="gallery-work-quick">{{ admin_t('gallery.add_gallery') }}</label>
                        <div class="tag-compose-row">
                            <input id="gallery-work-quick" type="text" name="title" placeholder="{{ admin_t('gallery.ph_work') }}" aria-label="{{ admin_t('gallery.add_gallery') }}" autofocus>
                            <span class="btn-split" role="group">
                                <button class="btn" type="submit">{{ admin_t('ui.add') }}</button>
                                <a class="btn btn-muted" href="/admin/video/galleries/create{{ $desk === 'pending' ? '?desk=pending' : '' }}">{{ admin_t('ui.full_form') }}</a>
                            </span>
                        </div>
                        <p class="muted field-hint">{{ admin_t('gallery.hint_work') }}</p>
                    </form>
                </div>
            @elseif($desk === 'pics')
                <p class="muted recycle-lead">{{ admin_t('gallery.compose_pics_lead') }}</p>
                <div class="tag-compose" id="gallery-pic-compose-box">
                    <form class="tag-compose-form" id="gallery-pic-compose" onsubmit="return false;">
                        <label class="tag-compose-label" for="gallery-pic-urls">{{ admin_t('gallery.batch_pics') }}</label>
                        <div class="tag-compose-row">
                            <select name="gallery_id" aria-label="{{ admin_t('gallery.title') }}" required style="max-width:220px">
                                <option value="">{{ admin_t('gallery.select_gallery') }}</option>
                                @foreach($works as $w)
                                    <option value="{{ $w->id }}">{{ $w->title }}</option>
                                @endforeach
                            </select>
                            <button class="btn" type="submit">{{ admin_t('ui.add') }}</button>
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
                    <button type="button" class="chip active" data-status="">{{ admin_t('ui.all') }} {{ (int) ($commentQueues['all'] ?? 0) }}</button>
                    <button type="button" class="chip" data-status="0">{{ admin_t('ui.pending') }} {{ (int) ($commentQueues['pending'] ?? 0) }}</button>
                    <button type="button" class="chip" data-status="1">{{ admin_t('ui.visible') }} {{ (int) ($commentQueues['pass'] ?? 0) }}</button>
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
                <button type="button" class="btn btn-sm" id="gallery-search-btn">{{ admin_t('ui.search') }}</button>
                <button type="reset" class="btn btn-muted btn-sm" id="gallery-reset-btn">{{ admin_t('ui.reset') }}</button>
            </form>
            <div id="gallery-table"></div>
        @endif
    </div>
</div>
<template id="type-form">@include('gallery::admin.type_form')</template>
@endsection
@php
    $galleryJsLang = [
        'gallery' => admin_t('gallery.title'),
        'pics' => admin_t('ui.pics'),
        'types' => admin_t('ui.types'),
        'actions' => admin_t('ui.actions'),
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
        'alias' => admin_t('ui.alias'),
        'time' => admin_t('ui.time'),
        'content' => admin_t('ui.content'),
        'nickname' => admin_t('ui.nickname'),
        'authors' => admin_t('ui.authors'),
        'tags' => admin_t('ui.tags'),
        'title_label' => admin_t('ui.title_label'),
        'no_match' => admin_t('ui.no_match'),
        'clear_filter' => admin_t('ui.clear_filter'),
        'cancel_favor' => admin_t('gallery.cancel_favor'),
        'empty_works' => admin_t('gallery.empty_works'),
        'empty_works_hint' => admin_t('gallery.empty_works_hint'),
        'empty_pics' => admin_t('gallery.empty_pics'),
        'empty_pics_hint' => admin_t('gallery.empty_pics_hint'),
        'empty_types' => admin_t('gallery.empty_types'),
        'empty_favors' => admin_t('gallery.empty_favors'),
        'empty_favors_hint' => admin_t('gallery.empty_favors_hint'),
        'empty_comments' => admin_t('gallery.empty_comments'),
        'empty_comments_hint' => admin_t('gallery.empty_comments_hint'),
        'need_title' => admin_t('gallery.need_title'),
        'need_urls' => admin_t('gallery.need_urls'),
        'select_gallery' => admin_t('gallery.select_gallery'),
    ];
@endphp
@push('scripts')
<script>
(function () {
    var U = AdminUi, desk = @json($desk), url = '/admin/video/galleries', L = @json($galleryJsLang);
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
            return '<div class="list-empty"><p>' + L.no_match + '</p><p><button type="button" class="btn btn-muted btn-sm" id="gallery-empty-reset">' + L.clear_filter + '</button></p></div>';
        }
        if (desk === 'favors') return '<div class="list-empty"><p>' + L.empty_favors + '</p><p class="muted">' + L.empty_favors_hint + '</p></div>';
        if (desk === 'comments') return '<div class="list-empty"><p>' + L.empty_comments + '</p><p class="muted">' + L.empty_comments_hint + '</p></div>';
        if (desk === 'pics') return '<div class="list-empty"><p>' + L.empty_pics + '</p><p class="muted">' + L.empty_pics_hint + '</p></div>';
        if (desk === 'types') return '<div class="list-empty"><p>' + L.empty_types + '</p></div>';
        return '<div class="list-empty"><p>' + L.empty_works + '</p><p class="muted">' + L.empty_works_hint + '</p></div>';
    }

    var cols = desk === 'pics'
        ? [
            { title: L.gallery, html: function (d) { return U.escape(d.gallery_title || d.gallery_id); } },
            { title: L.pics, html: function (d) { return '<a class="btn-link" href="/admin/video/gallery-pics/' + d.id + '/edit">' + U.escape(d.url || '') + '</a>'; } },
            { title: L.title_label, width: 140, html: function (d) { return U.escape(d.title || '—'); } },
            { title: L.actions, cls: 'actions', html: function (d) { return '<a class="btn-link" href="/admin/video/gallery-pics/' + d.id + '/edit">' + L.edit + '</a> <a href="#" class="btn-link js-del">' + L.delete + '</a>'; } }
        ]
        : desk === 'types'
        ? [
            { title: L.types, html: function (d) { return '<a href="#" class="btn-link js-edit">' + U.escape(d.name || '') + '</a>'; } },
            { title: L.alias, html: function (d) { return U.escape(d.slug || ''); } },
            { title: L.actions, cls: 'actions', html: function () { return '<a href="#" class="btn-link js-edit">' + L.edit + '</a> <a href="#" class="btn-link js-del">' + L.delete + '</a>'; } }
        ]
        : desk === 'favors'
        ? [
            { title: L.member, width: 100, html: function (d) { return U.escape(String(d.member_id)); } },
            { title: L.gallery, html: function (d) { return U.escape(d.gallery_title || String(d.gallery_id)); } },
            { title: L.actions, cls: 'actions', html: function () { return '<a href="#" class="btn-link js-del">' + L.cancel_favor + '</a>'; } }
        ]
        : desk === 'comments'
        ? [
            { title: L.content, html: function (d) { return U.escape(d.content || ''); } },
            { title: L.gallery, html: function (d) { return U.escape(d.gallery_title || String(d.gallery_id)); } },
            { title: L.nickname, width: 100, html: function (d) { return U.escape(d.author_name || ''); } },
            { title: L.status, width: 90, html: function (d) { return String(d.status) === '1' ? L.visible : L.pending; } },
            { title: L.time, width: 140, html: function (d) { return U.escape(d.created_label || ''); } },
            { title: L.actions, cls: 'actions', html: function (d) {
                return '<a href="/gallery/' + encodeURIComponent(d.gallery_id || '') + '" class="btn-link" target="_blank" rel="noopener">' + L.front + '</a> <a href="#" class="btn-link js-del">' + L.delete + '</a>';
            } }
        ]
        : [
            { title: L.gallery, html: function (d) { return '<a class="btn-link entry-row-title" href="/admin/video/galleries/' + d.id + '/edit">' + U.escape(d.title || '') + '</a>'; } },
            { title: L.authors, width: 120, html: function (d) { return U.escape(d.author || '—'); } },
            { title: L.tags, html: function (d) { return U.escape(d.tags || '—'); } },
            { title: L.hits, width: 70, html: function (d) { return U.escape(String(d.hits || 0)); } },
            { title: L.favors, width: 70, html: function (d) { return U.escape(String(d.favor_count || 0)); } },
            { title: L.status, width: 90, html: function (d) {
                if (String(d.yid) === '1') return '<span class="badge badge-warn">' + L.pending + '</span>';
                return String(d.status) === '1' ? L.on : L.off;
            } },
            { title: L.actions, cls: 'actions', html: function (d) {
                return '<a href="/admin/video/galleries?desk=pics" class="btn-link">' + L.pics + '</a> <a class="btn-link" href="/admin/video/galleries/' + d.id + '/edit">' + L.edit + '</a> <a href="/gallery/' + d.id + '" class="btn-link" target="_blank" rel="noopener">' + L.front + '</a> <a href="#" class="btn-link js-del">' + L.delete + '</a>';
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
            title: row.id ? L.edit + ' · ' + L.types : L.add_type,
            content: document.getElementById('type-form').innerHTML,
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

    function quickWork(form) {
        var d = U.formData(form);
        d.desk = 'works';
        d.status = 1;
        d.yid = desk === 'pending' ? 1 : 0;
        if (!d.title) { U.toast(L.need_title, 'err'); return; }
        U.post(url + '/save', d).then(function (r) {
            if (!r || r.code !== 0) { U.toast((r && r.msg) || L.fail, 'err'); return; }
            form.reset();
            var focus = form.querySelector('input[type=text]');
            if (focus) focus.focus();
            table.refresh();
            U.toast((r && r.msg) || L.added, 'ok');
        });
    }

    function quickPics(form) {
        var d = U.formData(form);
        d.desk = 'pics';
        if (!d.gallery_id) { U.toast(L.select_gallery, 'err'); return; }
        if (!String(d.urls || '').trim()) { U.toast(L.need_urls, 'err'); return; }
        U.post(url + '/save', d).then(function (r) {
            if (!r || r.code !== 0) { U.toast((r && r.msg) || L.fail, 'err'); return; }
            var keep = form.querySelector('[name=gallery_id]');
            var keepVal = keep ? keep.value : '';
            form.reset();
            if (keep && keepVal) keep.value = keepVal;
            table.refresh();
            U.toast((r && r.msg) || L.added, 'ok');
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
