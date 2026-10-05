@php
    $p = $paginator ?? null;
    $last = ($p && method_exists($p, 'lastPage')) ? max(1, (int) $p->lastPage()) : 0;
    $current = $p ? max(1, (int) $p->currentPage()) : 1;
@endphp
@if($p && $last > 1)
<nav class="pager" aria-label="分页">
    @if($p->onFirstPage())
        <span class="is-off">上一页</span>
    @else
        <a href="{{ $p->previousPageUrl() }}" rel="prev">上一页</a>
    @endif
    @php
        $from = max(1, $current - 2);
        $to = min($last, $current + 2);
        if ($to - $from < 4) {
            $from = max(1, $to - 4);
            $to = min($last, $from + 4);
        }
    @endphp
    @if($from > 1)
        <a href="{{ $p->url(1) }}">1</a>
        @if($from > 2)<span class="is-gap">…</span>@endif
    @endif
    @for($i = $from; $i <= $to; $i++)
        @if($i === $current)
            <span class="on" aria-current="page">{{ $i }}</span>
        @else
            <a href="{{ $p->url($i) }}">{{ $i }}</a>
        @endif
    @endfor
    @if($to < $last)
        @if($to < $last - 1)<span class="is-gap">…</span>@endif
        <a href="{{ $p->url($last) }}">{{ $last }}</a>
    @endif
    @if($p->hasMorePages())
        <a href="{{ $p->nextPageUrl() }}" rel="next">下一页</a>
    @else
        <span class="is-off">下一页</span>
    @endif
</nav>
@endif
