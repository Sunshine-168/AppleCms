<article class="card">
    <a href="{{ $item->url }}">
        @php $cover = trim((string) ($item->cover ?? '')) ?: trim((string) ($site['theme_lazy'] ?? '')); @endphp
        @if($cover)
            <img src="{{ $cover }}" alt="{{ $item->title }}">
        @else
            <img alt="{{ $item->title }}">
        @endif
        <div class="meta">
            <h3>{{ $item->title }}</h3>
            <div class="muted">{{ $item->year }} {{ $item->remarks }}</div>
        </div>
    </a>
</article>
