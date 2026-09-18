@php
    $latest = is_array($row->latest_chapter ?? null) ? $row->latest_chapter : null;
    $latestName = is_array($latest) ? (string) ($latest['name'] ?? '') : '';
    $latestId = is_array($latest) ? (int) ($latest['id'] ?? 0) : 0;
@endphp
<article class="card" data-manga-id="{{ $row->id }}">
    <a href="{{ url('/manga/'.$row->id) }}">
        @if($row->cover)
            <img src="{{ $row->cover }}" alt="{{ $row->title }}">
        @endif
        <div class="meta">
            <h3>{{ $row->title }}</h3>
        </div>
    </a>
    <div class="meta manga-card-sub">
        <span class="muted">{{ $row->serializeLabel() }}@if((int) ($row->chapter_count ?? 0) > 0) · {{ (int) $row->chapter_count }}话@endif</span>
        @if($latestName !== '' && $latestId > 0)
            <a href="{{ url('/manga/'.$row->id.'/'.$latestId) }}">{{ $latestName }}</a>
        @elseif($row->author)
            <a href="{{ url('/manga?author='.urlencode((string) $row->author)) }}">{{ $row->author }}</a>
        @endif
        <a class="js-continue muted" hidden href="#">继续阅读</a>
    </div>
</article>
