@php
    $page = $page ?? 'index';
    $filters = $filters ?? ['type' => 0, 'serialize' => '', 'recommend' => '', 'wd' => '', 'order' => 'new', 'board' => 'hits'];
    $listUrl = $listUrl ?? fn (array $over = []) => url('/manga');
@endphp
<nav class="manga-sub">
    <a href="{{ url('/manga') }}"@if($page === 'index') class="on"@endif>全部</a>
    <a href="{{ url('/manga/rank') }}"@if($page === 'rank') class="on"@endif>排行</a>
    <a href="{{ url('/manga/update') }}"@if($page === 'update') class="on"@endif>更新</a>
    <a href="{{ url('/manga/shelf') }}"@if($page === 'shelf') class="on"@endif>书架</a>
    <a href="{{ url('/manga/history') }}"@if($page === 'history') class="on"@endif>历史</a>
</nav>
