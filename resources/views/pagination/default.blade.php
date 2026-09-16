@if ($paginator->hasPages())
    <nav>
        @if ($paginator->onFirstPage())
            <span class="muted">上一页</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}">上一页</a>
        @endif

        @php
            $start = max(1, $paginator->currentPage() - 2);
            $end = min($paginator->lastPage(), $paginator->currentPage() + 2);
        @endphp
        @foreach ($paginator->getUrlRange($start, $end) as $page => $url)
            <a href="{{ $url }}" class="{{ $page === $paginator->currentPage() ? 'on' : '' }}">{{ $page }}</a>
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}">下一页</a>
        @else
            <span class="muted">下一页</span>
        @endif
    </nav>
@endif
