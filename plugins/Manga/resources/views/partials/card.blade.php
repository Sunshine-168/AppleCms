@php
    $latest = is_array($row->latest_chapter ?? null) ? $row->latest_chapter : null;
    $latestName = is_array($latest) ? (string) ($latest['name'] ?? '') : '';
    $latestId = is_array($latest) ? (int) ($latest['id'] ?? 0) : 0;
    $hasUpdate = (bool) ($row->has_update ?? false);
    $continueId = (int) ($row->continue_chapter_id ?? 0);
    $cover = trim((string) ($row->cover ?? ''));
    $badge = $latestName !== '' ? ('更新至 '.$latestName) : $row->serializeLabel();
    $subBits = array_filter([
        $row->serializeLabel(),
        ((int) ($row->chapter_count ?? 0) > 0) ? ((int) $row->chapter_count).'话' : null,
    ]);
@endphp
<article class="vod-card manga-card" data-manga-id="{{ $row->id }}">
    <a class="vod-card-cover{{ $cover === '' ? ' is-empty' : '' }}" href="{{ url('/manga/'.$row->id) }}" title="{{ $row->title }}">
        @if($cover !== '')
            <img src="{{ $cover }}" alt="{{ $row->title }}" loading="lazy"
                 onerror="var p=this.parentElement;this.remove();if(p)p.classList.add('is-empty');">
        @endif
        <span class="vod-card-empty">暂无封面</span>
        <span class="vod-card-play" aria-hidden="true"></span>
        @if((int) ($row->recommend ?? 0) === 1)
            <em class="vod-card-score">荐</em>
        @elseif($hasUpdate)
            <em class="vod-card-score">新</em>
        @endif
        @if($badge !== '')
            <em class="vod-card-badge">{{ $badge }}</em>
        @endif
    </a>
    <div class="vod-card-meta">
        <h3><a href="{{ url('/manga/'.$row->id) }}" title="{{ $row->title }}">{{ $row->title }}</a></h3>
        @if($subBits !== [])
            <p class="muted">{{ implode(' · ', $subBits) }}</p>
        @endif
        @if($continueId > 0)
            <p><a class="manga-card-continue" href="{{ url('/manga/'.$row->id.'/'.$continueId) }}">继续阅读</a></p>
        @elseif($latestId > 0)
            <p><a class="manga-card-continue muted" href="{{ url('/manga/'.$row->id.'/'.$latestId) }}">最新话</a></p>
        @else
            <p><a class="js-continue muted manga-card-continue" hidden href="#">继续阅读</a></p>
        @endif
    </div>
</article>
