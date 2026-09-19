<article class="vod-card">
    @php
        $cover = trim((string) ($item->cover ?? '')) ?: trim((string) ($site['theme_lazy'] ?? ''));
        $remarks = trim((string) ($item->remarks ?? ''));
        $year = trim((string) ($item->year ?? ''));
        $typeName = trim((string) ($item->type->name ?? $item->type_name ?? ''));
        $score = (float) ($item->score ?? 0);
        $sub = trim(implode(' · ', array_filter([$year !== '' ? $year : null, $typeName !== '' ? $typeName : null])));
    @endphp
    <a class="vod-card-cover{{ $cover === '' ? ' is-empty' : '' }}" href="{{ $item->url }}" title="{{ $item->title }}">
        @if($cover !== '')
            <img src="{{ $cover }}" alt="{{ $item->title }}" loading="lazy"
                 onerror="var p=this.parentElement;this.remove();if(p)p.classList.add('is-empty');">
        @endif
        <span class="vod-card-empty">暂无封面</span>
        <span class="vod-card-play" aria-hidden="true"></span>
        @if($score >= 1)
            <em class="vod-card-score">{{ number_format($score, $score == floor($score) ? 0 : 1) }}</em>
        @endif
        @if($remarks !== '')
            <em class="vod-card-badge">{{ $remarks }}</em>
        @endif
    </a>
    <div class="vod-card-meta">
        <h3><a href="{{ $item->url }}" title="{{ $item->title }}">{{ $item->title }}</a></h3>
        @if($sub !== '')
            <p class="muted">{{ $sub }}</p>
        @elseif($remarks !== '')
            <p class="muted">{{ $remarks }}</p>
        @endif
    </div>
</article>
