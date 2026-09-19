@extends('themes.default.layout')
@section('content')
    @php
        $accountHistory = is_array($accountHistory ?? null) ? $accountHistory : [];
        $loggedIn = (bool) ($loggedIn ?? false);
        $accountIds = [];
        foreach ($accountHistory as $row) {
            $mid = (int) ($row['manga_id'] ?? 0);
            if ($mid > 0) {
                $accountIds[] = $mid;
            }
        }
    @endphp
    <div class="list-head manga-head">
        <h1>阅读历史</h1>
        <p class="muted">
            @if($loggedIn)
                登录后阅读会同步到账号，换设备也能续看。
            @else
                未登录时记在这台浏览器。<a href="{{ url('/member/login') }}">去登录</a>
            @endif
        </p>
    </div>
    @include('manga::partials.subnav')

    @if($loggedIn)
        <section class="member-card manga-history-card">
            <div class="sec-head"><h2>账号记录</h2></div>
            @if($accountHistory === [])
                <p class="muted">账号里还没有记录。打开某一话后会出现在这里。</p>
            @else
                <ul class="manga-history-list">
                    @foreach($accountHistory as $row)
                        <li>
                            <a href="{{ $row['url'] }}">{{ $row['title'] }}@if($row['name'] !== '') · {{ $row['name'] }}@endif</a>
                            <time class="muted">{{ date('Y-m-d H:i', (int) $row['updated_at']) }}</time>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
        <details class="manga-local-history member-card">
            <summary>本机记录</summary>
            <p class="muted">未登录时记在浏览器；与账号重复的会自动隐藏。清站点数据会一起没。</p>
            <p class="manga-history-actions">
                <button type="button" class="btn-link" id="manga-history-clear" hidden>清空本机记录</button>
            </p>
            <ul class="manga-history-list" id="manga-history-list"></ul>
            <p class="muted" id="manga-history-empty">还没有本机阅读记录。</p>
        </details>
    @else
        <section class="member-card manga-history-card">
            <p class="manga-history-actions">
                <button type="button" class="btn-link" id="manga-history-clear" hidden>清空本机记录</button>
            </p>
            <ul class="manga-history-list" id="manga-history-list"></ul>
            <p class="muted" id="manga-history-empty">还没有本机阅读记录。</p>
        </section>
    @endif
    <p class="muted" style="margin-top:16px"><a class="btn-link" href="{{ url('/manga') }}">去逛逛漫画</a></p>
@endsection
@push('scripts')
<script>
(function () {
    var key = 'manga_history';
    var list = document.getElementById('manga-history-list');
    var empty = document.getElementById('manga-history-empty');
    var clear = document.getElementById('manga-history-clear');
    var skip = @json($accountIds);
    var skipMap = {};
    (skip || []).forEach(function (id) { skipMap[String(id)] = 1; });
    if (!list) return;
    function fmt(ts) {
        ts = Number(ts || 0);
        if (ts < 1) return '';
        if (ts < 1e12) ts = ts * 1000;
        var d = new Date(ts);
        if (isNaN(d.getTime())) return '';
        var p = function (n) { return n < 10 ? ('0' + n) : String(n); };
        return d.getFullYear() + '-' + p(d.getMonth() + 1) + '-' + p(d.getDate()) + ' ' + p(d.getHours()) + ':' + p(d.getMinutes());
    }
    function load() {
        var map = {};
        try { map = JSON.parse(localStorage.getItem(key) || '{}') || {}; } catch (e) { map = {}; }
        var rows = Object.keys(map).map(function (id) {
            var row = map[id] || {};
            return {
                id: id,
                title: row.title || ('漫画 #' + id),
                name: row.name || '',
                chapter: row.chapter || 0,
                page: Number(row.page || 0),
                at: Number(row.at || 0)
            };
        }).filter(function (row) {
            return Number(row.chapter) > 0 && !skipMap[String(row.id)];
        }).sort(function (a, b) { return b.at - a.at; });
        list.innerHTML = '';
        rows.forEach(function (row) {
            var li = document.createElement('li');
            var a = document.createElement('a');
            a.href = '/manga/' + encodeURIComponent(row.id) + '/' + encodeURIComponent(row.chapter);
            a.textContent = row.title + (row.name ? (' · ' + row.name) : '');
            li.appendChild(a);
            if (row.page > 0) {
                var page = document.createElement('span');
                page.className = 'muted';
                page.textContent = '第' + (row.page + 1) + '页';
                li.appendChild(page);
            }
            var when = fmt(row.at);
            if (when) {
                var span = document.createElement('time');
                span.className = 'muted';
                span.textContent = when;
                li.appendChild(span);
            }
            list.appendChild(li);
        });
        empty.hidden = rows.length > 0;
        if (clear) clear.hidden = rows.length === 0;
    }
    if (clear) {
        clear.addEventListener('click', function () {
            if (!window.confirm('清空这台浏览器里的漫画阅读记录？')) return;
            localStorage.removeItem(key);
            try { localStorage.removeItem('manga_progress'); } catch (e) {}
            load();
        });
    }
    load();
})();
</script>
@endpush
