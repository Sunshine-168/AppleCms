@extends('admin.layouts.inner')
@section('title', $title ?? admin_t('page.tool_quality'))

@php
    $counts = is_array($counts ?? null) ? $counts : [];
    $c = fn (string $k) => (int) ($counts[$k] ?? 0);
    $focus = (string) ($focus ?? 'empty_url');
    $issues = [
        'empty_url' => ['label' => '无地址', 'hint' => '库里没有一集', 'query' => 'empty_url=1'],
        'empty_pic' => ['label' => '无封面', 'hint' => '封面地址是空的', 'query' => 'empty_pic=1'],
        'empty_content' => ['label' => '无简介', 'hint' => '简介还没写', 'query' => 'empty_content=1'],
        'no_actor' => ['label' => '无演员', 'hint' => '没挂演员库', 'query' => 'no_actor=1'],
        'repeat' => ['label' => '重名', 'hint' => '标题完全相同', 'query' => 'repeat=1'],
        'missing_ep' => ['label' => '集数不齐', 'hint' => '总集数大于已有剧集', 'query' => 'missing_ep=1'],
    ];
@endphp

@section('plain')
<div class="card card-panel quality-index" id="quality-index">
    <div class="card-header">
        <span>内容质量</span>
        <div>
            <button type="button" class="btn btn-muted btn-sm" id="quality-refresh-btn">刷新数字</button>
            <a class="btn btn-muted btn-sm" href="/admin/video">影片列表</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/tools/images">远程图片</a>
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">看片库缺口：无地址、无封面、无简介、无演员、重名、集数不齐。数字来自库，<strong>不会打分</strong>，也不改片子。坏链去播放失败，外链封面去远程图片。</p>
        <div class="quality-cards" id="quality-cards">
            @foreach($issues as $key => $issue)
                <button type="button" class="quality-card{{ $focus === $key ? ' is-on' : '' }}" data-issue="{{ $key }}" data-query="{{ $issue['query'] }}">
                    <strong>{{ $issue['label'] }}</strong>
                    <em data-count="{{ $key }}">{{ $c($key) }}</em>
                    <span>{{ $issue['hint'] }}@if($key === 'repeat' && $c('repeat_groups') > 0) · {{ $c('repeat_groups') }} 组@endif</span>
                </button>
            @endforeach
        </div>
        <p class="muted quality-note" id="quality-note">点一项看片子。要批量合并重名、改分类，到影片列表勾选后再操作。</p>
        <div class="filter-bar quality-toolbar">
            <input type="search" id="quality-q" placeholder="在这项里搜标题" autocomplete="off" aria-label="搜标题">
            <button type="button" class="btn btn-sm" id="quality-search-btn">查询</button>
            <a class="btn btn-muted btn-sm" id="quality-list-link" href="/admin/video?{{ $issues[$focus]['query'] ?? 'empty_url=1' }}">在影片列表打开</a>
        </div>
        <div id="quality-table"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var counts = @json($counts);
    var focus = @json($focus);
    var qInput = document.getElementById('quality-q');
    var listLink = document.getElementById('quality-list-link');
    var note = document.getElementById('quality-note');
    var HINTS = {
        empty_url: '没有一集。去线路里加播放地址。',
        empty_pic: '封面是空的。去编辑页上传，或到远程图片把外站图下回来。',
        empty_content: '简介还没写。去编辑页补。',
        no_actor: '没挂演员库。去编辑页填演员。不是后台角色。',
        repeat: '标题完全相同。到影片列表勾选后可以合并，线路会迁到留下的那部。',
        missing_ep: '填了总集数，剧集条数还没到。总集数是 0 的不算，因为不知道该有多少集。'
    };
    var EMPTY = {
        empty_url: '没有缺地址的片子',
        empty_pic: '没有缺封面的片子',
        empty_content: '没有缺简介的片子',
        no_actor: '没有缺演员的片子',
        repeat: '没有重名的片子',
        missing_ep: '没有集数不齐的片子'
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
            return have + ' / ' + total + ' 集';
        }
        if (focus === 'repeat') return '标题重复';
        if (focus === 'empty_url') return '没有剧集';
        if (focus === 'empty_pic') return '没封面';
        if (focus === 'empty_content') return '没简介';
        if (focus === 'no_actor') return '没演员';
        return '—';
    }
    function titleHtml(d) {
        var cover = String(d.cover || '').trim();
        var thumb = cover
            ? '<img class="vod-thumb" src="' + U.escape(cover) + '" alt="">'
            : '<span class="vod-thumb is-empty">无图</span>';
        var meta = U.escape(d.type_name || '未分类');
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
                return '<div class="list-empty"><p>片库还是空的</p><p class="muted">先接一个采集源，或手动加一部片子。</p><p><a class="btn btn-primary btn-sm" href="/admin/video/collects">去采集</a> <a class="btn btn-muted btn-sm" href="/admin/video/create">新增影片</a></p></div>';
            }
            if (where && where.title) {
                return '<div class="list-empty"><p>这项里没有这个标题</p><p><button type="button" class="btn btn-muted btn-sm" id="quality-empty-reset">清除搜索</button></p></div>';
            }
            return '<div class="list-empty"><p>' + U.escape(EMPTY[focus] || '没有片子') + '</p><p class="muted">点上面其它项，或到影片列表看全部。</p></div>';
        },
        onDraw: function () {
            var reset = document.getElementById('quality-empty-reset');
            if (reset) reset.addEventListener('click', function () { qInput.value = ''; table.reload(issueWhere()); });
        },
        cols: [
            {title: '影片', html: titleHtml},
            {title: '缺什么', width: 120, html: issueHtml},
            {title: '操作', cls: 'actions', html: function (d) {
                var id = encodeURIComponent(d.id);
                var html = '<a href="/admin/video/' + id + '/edit">编辑</a>';
                if (focus === 'empty_url' || focus === 'missing_ep') {
                    html += '<a href="/admin/video/sources?video_id=' + id + '">线路</a>';
                    html += '<a href="/admin/video/sources?video_id=' + id + '&open_episode=1">剧集</a>';
                } else if (focus === 'empty_pic') {
                    html += '<a href="/admin/video/tools/images">远程图片</a>';
                } else if (focus === 'no_actor') {
                    html += '<a href="/admin/video/actors">演员库</a>';
                } else if (focus === 'repeat') {
                    html += '<a href="/admin/video?repeat=1">去合并</a>';
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
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
            counts = res.data || counts;
            U.qa('#quality-cards [data-count]').forEach(function (em) {
                var key = em.getAttribute('data-count');
                em.textContent = String(parseInt(counts[key], 10) || 0);
            });
            table.reload(issueWhere());
            U.toast((res && res.msg) || '已刷新', 'ok');
        });
    });
})();
</script>
@endpush
