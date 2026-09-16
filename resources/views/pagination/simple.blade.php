@if ($paginator->hasPages())
    <nav>
        @if ($paginator->onFirstPage())
            <span class="muted">上一页</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}">上一页</a>
        @endif
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}">下一页</a>
        @else
            <span class="muted">下一页</span>
        @endif
    </nav>
@endif
