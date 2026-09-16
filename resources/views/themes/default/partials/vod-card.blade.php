<article class="card">
    <a href="{{ $item->url }}">
        @if($item->cover)
            <img src="{{ $item->cover }}" alt="{{ $item->title }}">
        @else
            <img alt="{{ $item->title }}">
        @endif
        <div class="meta">
            <h3>{{ $item->title }}</h3>
            <div class="muted">{{ $item->year }} {{ $item->remarks }}</div>
        </div>
    </a>
</article>
