@extends('themes.default.layout')
@section('content')
    <h1>阅读历史</h1>
    @include('manga::partials.subnav')
    @php
        $accountHistory = is_array($accountHistory ?? null) ? $accountHistory : [];
        $loggedIn = (bool) ($loggedIn ?? false);
    @endphp
    @if($loggedIn)
        <h2>账号记录</h2>
        <p class="muted">登录后阅读会同步到账号，换设备也能续看。</p>
        @if($accountHistory === [])
            <p class="muted">账号里还没有记录。打开某一话后会出现在这里。</p>
        @else
            <ul class="list-plain">
                @foreach($accountHistory as $row)
                    <li>
                        <a href="{{ $row['url'] }}">{{ $row['title'] }}@if($row['name'] !== '') · {{ $row['name'] }}@endif</a>
                        <span class="muted">{{ date('Y-m-d H:i', (int) $row['updated_at']) }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
        <h2>本机记录</h2>
        <p class="muted">未登录时记在浏览器；清站点数据会一起没。</p>
    @else
        <p class="muted">未登录时记在这台浏览器。登录后会同步到账号。<a href="{{ url('/member/login') }}">去登录</a></p>
    @endif
    <p><button type="button" class="btn-link" id="manga-history-clear">清空本机记录</button></p>
    <ul class="list-plain" id="manga-history-list"></ul>
    <p class="muted" id="manga-history-empty">还没有本机阅读记录。</p>
@endsection
@push('scripts')
<script>
(function () {
    var key = 'manga_history';
    var list = document.getElementById('manga-history-list');
    var empty = document.getElementById('manga-history-empty');
    var clear = document.getElementById('manga-history-clear');
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
                at: Number(row.at || 0)
            };
        }).filter(function (row) { return Number(row.chapter) > 0; }).sort(function (a, b) { return b.at - a.at; });
        list.innerHTML = '';
        rows.forEach(function (row) {
            var li = document.createElement('li');
            var a = document.createElement('a');
            a.href = '/manga/' + encodeURIComponent(row.id) + '/' + encodeURIComponent(row.chapter);
            a.textContent = row.title + (row.name ? (' · ' + row.name) : '');
            li.appendChild(a);
            list.appendChild(li);
        });
        empty.hidden = rows.length > 0;
    }
    if (clear) {
        clear.addEventListener('click', function () {
            if (!window.confirm('清空这台浏览器里的漫画阅读记录？')) return;
            localStorage.removeItem(key);
            load();
        });
    }
    load();
})();
</script>
@endpush
