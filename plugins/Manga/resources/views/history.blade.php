@extends('themes.default.layout')
@section('content')
    <h1>阅读历史</h1>
    @include('manga::partials.subnav')
    <p class="muted">记在这台浏览器里，不是账号同步。清站点数据会一起没。</p>
    <p><button type="button" class="btn-link" id="manga-history-clear">清空本机记录</button></p>
    <ul class="list-plain" id="manga-history-list"></ul>
    <p class="muted" id="manga-history-empty">还没有阅读记录。打开某一话后会出现在这里。</p>
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
