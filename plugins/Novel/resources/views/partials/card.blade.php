<article class="vod-card manga-card">
    <a class="vod-card-cover{{ trim((string) $row->cover) === '' ? ' is-empty' : '' }}" href="{{ url('/novel/'.$row->id) }}">
        @if($row->cover)<img src="{{ $row->cover }}" alt="{{ $row->title }}" loading="lazy">@endif
        <span class="vod-card-empty">暂无封面</span>
        <em class="vod-card-badge">{{ (int) $row->serialize === 1 ? '完结' : '连载' }}</em>
    </a>
    <div class="vod-card-meta">
        <h3><a href="{{ url('/novel/'.$row->id) }}">{{ $row->title }}</a></h3>
        <p class="muted">{{ $row->author ?: '佚名' }} · 人气 {{ (int) $row->hits }}@if(!empty($row->favor_count)) · 收藏 {{ (int) $row->favor_count }}@endif</p>
        @if(!empty($row->tag_list))
            <p class="muted">{{ implode(' · ', array_slice($row->tag_list, 0, 4)) }}</p>
        @endif
    </div>
</article>
