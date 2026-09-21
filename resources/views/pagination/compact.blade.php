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
<nav class="pagination" aria-label="{{ admin_t('ui.pager') }}">
    @if ($hasTotal)
        <span>{{ admin_t('ui.pager_total', ['n' => $total]) }}</span>
    @endif
    @if ($current > 1)
        <a href="{{ $paginator->previousPageUrl() }}" rel="prev">{{ admin_t('ui.prev_page') }}</a>
    @endif
    <span>{{ $last !== null ? $current.'/'.$last : $current }}</span>
    @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" rel="next">{{ admin_t('ui.next_page') }}</a>
    @endif
</nav>
@endif
