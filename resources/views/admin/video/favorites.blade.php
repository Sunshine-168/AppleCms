@extends('admin.layouts.inner')
@section('title', $title)

@php
    $queues = $queues ?? ['all' => 0, 'today' => 0, 'missing' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
    $memberId = (int) ($memberId ?? 0);
    $videoId = (int) ($videoId ?? 0);
    $memberName = (string) ($memberName ?? '');
    $videoTitle = (string) ($videoTitle ?? '');
@endphp

@section('plain')
<div class="card card-panel fav-index">
    <div class="card-header">
        <span>收藏 <em id="fav-count"></em></span>
        <div>
            <a class="btn btn-muted btn-sm" href="/admin/video/members">会员</a>
            <a class="btn btn-muted btn-sm" href="/admin/video">影片</a>
            <a class="btn btn-muted btn-sm" href="/member/favorites" target="_blank" rel="noopener">前台收藏</a>
        </div>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="fav-search" onsubmit="return false;">
            <input type="hidden" name="today">
            <input type="hidden" name="missing">
            <input type="hidden" name="member_id" value="{{ $memberId > 0 ? $memberId : '' }}">
            <input type="hidden" name="video_id" value="{{ $videoId > 0 ? $videoId : '' }}">
            <input type="search" name="q" placeholder="搜会员、片名或 ID" autocomplete="off" aria-label="搜索收藏">
            <button type="button" class="btn btn-sm" id="fav-search-btn">查询</button>
            <button type="reset" class="btn btn-muted btn-sm" id="fav-reset-btn">重置</button>
        </form>
        <div class="queue-chips" id="fav-queues">
            <button type="button" class="chip" data-queue="">全部@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="today" data-value="1">今天@if($q('today') > 0)<em>{{ $q('today') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="missing" data-value="1">影片已删@if($q('missing') > 0)<em>{{ $q('missing') }}</em>@endif</button>
        </div>
        @if($memberId > 0 || $videoId > 0)
            <p class="muted recycle-lead" id="fav-focus">
                @if($memberId > 0)
                    正在看会员 {{ $memberName !== '' ? $memberName : ('#'.$memberId) }} 的收藏。
                @endif
                @if($videoId > 0)
                    正在看影片 {{ $videoTitle !== '' ? $videoTitle : ('#'.$videoId) }} 被谁收藏。
                @endif
                <button type="button" class="btn-link" id="fav-clear-focus">看全部</button>
            </p>
        @else
            <p class="muted recycle-lead">会员在影片页点收藏后出现。后台不能代收藏。删除只影响此人的收藏夹，不删影片。</p>
        @endif
        <div class="batch-bar" id="fav-batch" hidden>
            <strong id="fav-batch-count">已选 0 条</strong>
            <button type="button" class="btn btn-danger btn-sm" id="fav-batch-del">取消收藏</button>
            <button type="button" class="btn btn-muted btn-sm" id="fav-batch-clear">取消选择</button>
        </div>
        <div id="fav-table"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var form = document.getElementById('fav-search');
    var batchBar = document.getElementById('fav-batch');
    var batchCount = document.getElementById('fav-batch-count');
    var countEl = document.getElementById('fav-count');
    var QUEUE_KEYS = ['today', 'missing'];

    function cleanWhere(data) {
        var out = {};
        Object.keys(data || {}).forEach(function (k) { if (data[k] !== '') out[k] = data[k]; });
        return out;
    }
    function queryWhere() {
        return Object.assign({limit: 20}, cleanWhere(U.formData(form)));
    }
    function isFiltered(where) {
        return Object.keys(where || {}).some(function (k) { return k !== 'limit' && where[k] !== ''; });
    }
    function markChips() {
        var today = form.today.value;
        var missing = form.missing.value;
        U.qa('#fav-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = false;
            if (key === '' && today === '' && missing === '') on = true;
            else if (key === 'today' && missing === '' && today === val) on = true;
            else if (key === 'missing' && today === '' && missing === val) on = true;
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
    function filmHtml(d) {
        var title = d.video_title || '';
        if (parseInt(d.video_missing, 10) === 1 || !title) {
            return '<span class="muted">影片已删' + (d.video_id ? ' · #' + U.escape(d.video_id) : '') + '</span>';
        }
        return '<a href="/vod/' + encodeURIComponent(d.video_id) + '" target="_blank" rel="noopener">' + U.escape(title) + '</a>';
    }
    function memberHtml(d) {
        var name = d.member_name || '';
        if (parseInt(d.member_missing, 10) === 1 || (!name && !d.member_id)) {
            return '<span class="muted">会员已删' + (d.member_id ? ' · #' + U.escape(d.member_id) : '') + '</span>';
        }
        var label = name || ('会员 #' + d.member_id);
        var html = '<a href="/admin/video/members?q=' + encodeURIComponent(d.member_id) + '">' + U.escape(label) + '</a>';
        if (d.member_email) html += '<div class="muted">' + U.escape(d.member_email) + '</div>';
        return html;
    }

    var table = U.table({
        el: '#fav-table',
        queueKeys: QUEUE_KEYS,
        countEl: countEl,
        url: '/admin/video/favorites/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>没有符合条件的收藏</p><p><button type="button" class="btn btn-muted btn-sm" id="fav-empty-reset">清除筛选</button></p></div>';
            }
            return '<div class="list-empty"><p>还没有收藏</p><p class="muted">会员登录后在影片页点收藏，记录会出现在这里。后台不能代收藏。</p></div>';
        },
        onDraw: function (_wrap, list) {
            var reset = document.getElementById('fav-empty-reset');
            if (reset) reset.addEventListener('click', function () {
                form.reset();
                QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
                if (form.member_id) form.member_id.value = '';
                if (form.video_id) form.video_id.value = '';
                runSearch();
            });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = '已选 ' + ids.length + ' 条';
        },
        cols: [
            {check: true, width: 36},
            {title: '影片', html: filmHtml},
            {title: '会员', html: memberHtml},
            {title: '收藏时间', width: 150, html: function (d) { return U.escape(d.created_at_text || ''); }},
            {title: '操作', cls: 'actions', html: function (d) {
                var html = '';
                if (d.video_id && parseInt(d.video_missing, 10) !== 1) {
                    html += '<a class="btn-link" href="/vod/' + encodeURIComponent(d.video_id) + '" target="_blank" rel="noopener">前台</a>';
                    html += '<a class="btn-link" href="/admin/video?q=' + encodeURIComponent(d.video_id) + '">影片</a>';
                }
                if (d.member_id && parseInt(d.member_missing, 10) !== 1) {
                    html += '<a class="btn-link" href="/admin/video/members?q=' + encodeURIComponent(d.member_id) + '">会员</a>';
                }
                html += '<a href="#" class="btn-link js-del">取消收藏</a>';
                return html;
            }}
        ]
    });
    markChips();

    function selectedIds() { return table.selectedIds(); }
    function batchDel() {
        var ids = selectedIds();
        if (!ids.length) { U.toast('请先勾选收藏', 'err'); return; }
        if (!U.confirm('取消这 ' + ids.length + ' 条收藏？不会删影片。')) return;
        U.post('/admin/video/favorites/batch', {ids: ids.join(','), action: 'delete'}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '操作失败', 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || '已取消', 'ok');
        });
    }

    U.on('#fav-search-btn', 'click', runSearch);
    U.on('#fav-reset-btn', 'click', function () { setTimeout(function () {
        QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
        runSearch();
    }, 0); });
    U.on('#fav-clear-focus', 'click', function () {
        if (form.member_id) form.member_id.value = '';
        if (form.video_id) form.video_id.value = '';
        if (history.replaceState) history.replaceState({}, '', '/admin/video/favorites');
        var lead = document.getElementById('fav-focus');
        if (lead) lead.innerHTML = '会员在影片页点收藏才会出现。后台不能代收藏，删掉只影响这个人的收藏夹，不会删影片。';
        runSearch();
    });
    document.getElementById('fav-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#fav-batch-del', 'click', batchDel);
    U.on('#fav-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#fav-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        var href = a.getAttribute('href') || '';
        if (href && href !== '#' && href.indexOf('javascript:') !== 0) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-del')) {
            var label = row.video_title || ('影片 #' + (row.video_id || ''));
            if (!U.confirm('取消「' + label + '」的这条收藏？不会删影片。')) return;
            U.post('/admin/video/favorites/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh();
                U.toast('已取消收藏', 'ok');
            });
        }
    });
})();
</script>
@endpush
