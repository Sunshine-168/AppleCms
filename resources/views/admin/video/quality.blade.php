@extends('admin.layouts.inner')
@section('title', $title ?? admin_t('page.tool_quality'))

@php
    $counts = is_array($counts ?? null) ? $counts : [];
    $c = fn (string $k) => (int) ($counts[$k] ?? 0);
    $focus = (string) ($focus ?? 'empty_url');
    $issues = [
        'empty_url' => ['label' => admin_t('ui.no_url'), 'hint' => admin_t('ui.hint_empty_url'), 'query' => 'empty_url=1'],
        'empty_pic' => ['label' => admin_t('ui.no_cover'), 'hint' => admin_t('ui.hint_empty_pic'), 'query' => 'empty_pic=1'],
        'empty_content' => ['label' => admin_t('ui.no_intro'), 'hint' => admin_t('ui.hint_empty_content'), 'query' => 'empty_content=1'],
        'no_actor' => ['label' => admin_t('ui.no_actor'), 'hint' => admin_t('ui.hint_no_actor'), 'query' => 'no_actor=1'],
        'repeat' => ['label' => admin_t('ui.duplicate'), 'hint' => admin_t('ui.hint_repeat'), 'query' => 'repeat=1'],
        'missing_ep' => ['label' => admin_t('ui.missing_ep'), 'hint' => admin_t('ui.hint_missing_ep'), 'query' => 'missing_ep=1'],
    ];
    $qualityJsLang = [
        'hint_empty_url' => admin_t('ui.quality_hint_empty_url'),
        'hint_empty_pic' => admin_t('ui.quality_hint_empty_pic'),
        'hint_empty_content' => admin_t('ui.quality_hint_empty_content'),
        'hint_no_actor' => admin_t('ui.quality_hint_no_actor'),
        'hint_repeat' => admin_t('ui.quality_hint_repeat'),
        'hint_missing_ep' => admin_t('ui.quality_hint_missing_ep'),
        'empty_empty_url' => admin_t('ui.empty_issue_empty_url'),
        'empty_empty_pic' => admin_t('ui.empty_issue_empty_pic'),
        'empty_empty_content' => admin_t('ui.empty_issue_empty_content'),
        'empty_no_actor' => admin_t('ui.empty_issue_no_actor'),
        'empty_repeat' => admin_t('ui.empty_issue_repeat'),
        'empty_missing_ep' => admin_t('ui.empty_issue_missing_ep'),
        'empty_videos' => admin_t('ui.empty_videos'),
        'empty_videos_hint' => admin_t('ui.empty_videos_hint'),
        'go_collect' => admin_t('ui.go_collect'),
        'add_video' => admin_t('ui.add_video'),
        'no_match_title' => admin_t('ui.no_match_quality_title'),
        'clear_search' => admin_t('ui.clear_search'),
        'empty_fallback' => admin_t('ui.empty_issue_fallback'),
        'empty_hint' => admin_t('ui.empty_issue_hint'),
        'ep_have' => admin_t('ui.ep_have_total', ['have' => '__HAVE__', 'total' => '__TOTAL__']),
        'issue_repeat' => admin_t('ui.issue_repeat'),
        'issue_empty_url' => admin_t('ui.issue_empty_url'),
        'issue_empty_pic' => admin_t('ui.issue_empty_pic'),
        'issue_empty_content' => admin_t('ui.issue_empty_content'),
        'issue_no_actor' => admin_t('ui.issue_no_actor'),
        'no_image' => admin_t('ui.no_image'),
        'uncategorized' => admin_t('ui.uncategorized'),
        'line' => admin_t('ui.line'),
        'episodes' => admin_t('ui.episodes'),
        'remote_images' => admin_t('ui.remote_images'),
        'actors_lib' => admin_t('ui.actors_lib'),
        'go_merge' => admin_t('ui.go_merge'),
        'fail' => admin_t('ui.fail'),
        'refreshed' => admin_t('ui.refreshed'),
        'edit' => admin_t('ui.edit'),
    ];
@endphp

@section('plain')
<div class="card card-panel quality-index" id="quality-index">
    <div class="card-header">
        <span>{{ admin_t('ui.content_quality') }}</span>
        <div>
            <button type="button" class="btn btn-muted btn-sm" id="quality-refresh-btn">{{ admin_t('ui.refresh_counts') }}</button>
            <a class="btn btn-muted btn-sm" href="/admin/video">{{ admin_t('ui.video_list') }}</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/tools/images">{{ admin_t('ui.remote_images') }}</a>
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">{{ admin_t('ui.quality_lead_before') }}<strong>{{ admin_t('ui.quality_lead_strong') }}</strong>{{ admin_t('ui.quality_lead_after') }}</p>
        <div class="quality-cards" id="quality-cards">
            @foreach($issues as $key => $issue)
                <button type="button" class="quality-card{{ $focus === $key ? ' is-on' : '' }}" data-issue="{{ $key }}" data-query="{{ $issue['query'] }}">
                    <strong>{{ $issue['label'] }}</strong>
                    <em data-count="{{ $key }}">{{ $c($key) }}</em>
                    <span>{{ $issue['hint'] }}@if($key === 'repeat' && $c('repeat_groups') > 0) · {{ admin_t('ui.groups_n', ['n' => $c('repeat_groups')]) }}@endif</span>
                </button>
            @endforeach
        </div>
        <p class="muted quality-note" id="quality-note">{{ admin_t('ui.quality_note') }}</p>
        <div class="filter-bar quality-toolbar">
            <input type="search" id="quality-q" placeholder="{{ admin_t('ui.ph_quality_title') }}" autocomplete="off" aria-label="{{ admin_t('ui.aria_quality_title') }}">
            <button type="button" class="btn btn-sm" id="quality-search-btn">{{ admin_t('ui.search') }}</button>
            <a class="btn btn-muted btn-sm" id="quality-list-link" href="/admin/video?{{ $issues[$focus]['query'] ?? 'empty_url=1' }}">{{ admin_t('ui.open_in_video_list') }}</a>
        </div>
        <div id="quality-table"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($qualityJsLang, JSON_UNESCAPED_UNICODE);
    var counts = @json($counts, JSON_UNESCAPED_UNICODE);
    var focus = @json($focus, JSON_UNESCAPED_UNICODE);
    var qInput = document.getElementById('quality-q');
    var listLink = document.getElementById('quality-list-link');
    var note = document.getElementById('quality-note');
    var HINTS = {
        empty_url: L.hint_empty_url,
        empty_pic: L.hint_empty_pic,
        empty_content: L.hint_empty_content,
        no_actor: L.hint_no_actor,
        repeat: L.hint_repeat,
        missing_ep: L.hint_missing_ep
    };
    var EMPTY = {
        empty_url: L.empty_empty_url,
        empty_pic: L.empty_empty_pic,
        empty_content: L.empty_empty_content,
        no_actor: L.empty_no_actor,
        repeat: L.empty_repeat,
        missing_ep: L.empty_missing_ep
    };
    var QUERY = {
        empty_url: 'empty_url=1',
        empty_pic: 'empty_pic=1',
        empty_content: 'empty_content=1',
        no_actor: 'no_actor=1',
        repeat: 'repeat=1',
        missing_ep: 'missing_ep=1'
    };

    function issueWhere() {
        var where = {limit: 20};
        where[focus] = 1;
        var kw = (qInput.value || '').trim();
        if (kw) where.title = kw;
        return where;
    }
    function markCards() {
        U.qa('#quality-cards .quality-card').forEach(function (card) {
            card.classList.toggle('is-on', card.getAttribute('data-issue') === focus);
        });
        listLink.href = '/admin/video?' + (QUERY[focus] || 'empty_url=1');
        note.textContent = HINTS[focus] || '';
    }
    function issueHtml(d) {
        if (focus === 'missing_ep') {
            var have = parseInt(d.episode_count, 10) || 0;
            var total = parseInt(d.total, 10) || 0;
            return L.ep_have.replace('__HAVE__', have).replace('__TOTAL__', total);
        }
        if (focus === 'repeat') return L.issue_repeat;
        if (focus === 'empty_url') return L.issue_empty_url;
        if (focus === 'empty_pic') return L.issue_empty_pic;
        if (focus === 'empty_content') return L.issue_empty_content;
        if (focus === 'no_actor') return L.issue_no_actor;
        return '—';
    }
    function titleHtml(d) {
        var cover = String(d.cover || '').trim();
        var thumb = cover
            ? '<img class="vod-thumb" src="' + U.escape(cover) + '" alt="">'
            : '<span class="vod-thumb is-empty">' + U.escape(L.no_image) + '</span>';
        var meta = U.escape(d.type_name || L.uncategorized);
        if (d.year) meta += ' · ' + U.escape(d.year);
        return '<div class="vod-cell">' + thumb + '<div><a class="vod-title" href="/admin/video/' + encodeURIComponent(d.id) + '/edit">' + U.escape(d.title || '') + '</a>'
            + '<div class="muted">' + meta + '</div></div></div>';
    }

    var table = U.table({
        el: '#quality-table',
        url: '/admin/video/list',
        where: issueWhere(),
        emptyHtml: function (_parsed, where) {
            var all = parseInt(counts.all, 10) || 0;
            if (all < 1) {
                return '<div class="list-empty"><p>' + U.escape(L.empty_videos) + '</p><p class="muted">' + U.escape(L.empty_videos_hint) + '</p><p><a class="btn btn-primary btn-sm" href="/admin/video/collects">' + U.escape(L.go_collect) + '</a> <a class="btn btn-muted btn-sm" href="/admin/video/create">' + U.escape(L.add_video) + '</a></p></div>';
            }
            if (where && where.title) {
                return '<div class="list-empty"><p>' + U.escape(L.no_match_title) + '</p><p><button type="button" class="btn btn-muted btn-sm" id="quality-empty-reset">' + U.escape(L.clear_search) + '</button></p></div>';
            }
            return '<div class="list-empty"><p>' + U.escape(EMPTY[focus] || L.empty_fallback) + '</p><p class="muted">' + U.escape(L.empty_hint) + '</p></div>';
        },
        onDraw: function () {
            var reset = document.getElementById('quality-empty-reset');
            if (reset) reset.addEventListener('click', function () { qInput.value = ''; table.reload(issueWhere()); });
        },
        cols: [
            {title: AdminUi.t('videos'), html: titleHtml},
            {title: AdminUi.t('missing'), width: 120, html: issueHtml},
            {title: AdminUi.t('actions'), cls: 'actions', html: function (d) {
                var id = encodeURIComponent(d.id);
                var html = '<a href="/admin/video/' + id + '/edit">' + U.escape(L.edit) + '</a>';
                if (focus === 'empty_url' || focus === 'missing_ep') {
                    html += '<a href="/admin/video/sources?video_id=' + id + '">' + U.escape(L.line) + '</a>';
                    html += '<a href="/admin/video/sources?video_id=' + id + '&open_episode=1">' + U.escape(L.episodes) + '</a>';
                } else if (focus === 'empty_pic') {
                    html += '<a href="/admin/video/tools/images">' + U.escape(L.remote_images) + '</a>';
                } else if (focus === 'no_actor') {
                    html += '<a href="/admin/video/actors">' + U.escape(L.actors_lib) + '</a>';
                } else if (focus === 'repeat') {
                    html += '<a href="/admin/video?repeat=1">' + U.escape(L.go_merge) + '</a>';
                }
                return html;
            }}
        ]
    });
    markCards();

    document.getElementById('quality-cards').addEventListener('click', function (e) {
        var card = e.target.closest('[data-issue]');
        if (!card) return;
        focus = card.getAttribute('data-issue') || 'empty_url';
        qInput.value = '';
        markCards();
        table.reload(issueWhere());
    });
    U.on('#quality-search-btn', 'click', function () { table.reload(issueWhere()); });
    qInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); table.reload(issueWhere()); }
    });
    U.on('#quality-refresh-btn', 'click', function () {
        U.post('/admin/video/tools/quality/run', {action: 'scan'}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
            counts = res.data || counts;
            U.qa('#quality-cards [data-count]').forEach(function (em) {
                var key = em.getAttribute('data-count');
                em.textContent = String(parseInt(counts[key], 10) || 0);
            });
            table.reload(issueWhere());
            U.toast((res && res.msg) || L.refreshed, 'ok');
        });
    });
})();
</script>
@endpush
