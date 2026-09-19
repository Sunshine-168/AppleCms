@php
    $page = $page ?? 'index';
@endphp
<nav class="manga-sub" aria-label="漫画栏目">
    <a href="{{ url('/manga') }}"@if($page === 'index') class="on"@endif>全部</a>
    <a href="{{ url('/manga/rank') }}"@if($page === 'rank') class="on"@endif>排行</a>
    <a href="{{ url('/manga/update') }}"@if($page === 'update') class="on"@endif>更新</a>
    <a href="{{ url('/manga/shelf') }}"@if($page === 'shelf') class="on"@endif>书架</a>
    <a href="{{ url('/manga/history') }}"@if($page === 'history') class="on"@endif>历史</a>
</nav>
@once
@push('scripts')
<script>
(function () {
    try {
        if (localStorage.getItem('manga_night') === '1') {
            document.body.classList.add('manga-night');
        }
    } catch (e) {}
})();
</script>
@endpush
@endonce
