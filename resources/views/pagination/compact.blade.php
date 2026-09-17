@php
    $hasTotal = method_exists($paginator, 'total');
    $total = $hasTotal ? (int) $paginator->total() : $paginator->count();
    $current = max(1, (int) $paginator->currentPage());
    $last = method_exists($paginator, 'lastPage') ? max(1, (int) $paginator->lastPage()) : null;
    $show = $hasTotal
        ? $total > 0
        : ($paginator->count() > 0 || $current > 1 || $paginator->hasMorePages());
@endphp
@if ($show)
<nav class="pagination" aria-label="分页">
    @if ($hasTotal)
        <span>共{{ $total }}条</span>
    @endif
    @if ($current > 1)
        <a href="{{ $paginator->previousPageUrl() }}" rel="prev">上一页</a>
    @endif
    <span>{{ $last !== null ? $current.'/'.$last : $current }}</span>
    @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" rel="next">下一页</a>
    @endif
</nav>
@endif
