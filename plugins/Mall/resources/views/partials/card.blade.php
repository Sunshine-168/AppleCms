@php
    $cover = trim((string) ($row->cover ?? ''));
    $typeLabel = \Plugins\Mall\Services\MallService::typeLabel($row->type ?? '');
    $hot = !empty($forceHot) || (int) ($row->is_hot ?? 0) === 1;
@endphp
<article class="mall-card">
    <a class="mall-card-cover{{ $cover === '' ? ' is-empty' : '' }}" href="{{ url('/mall/'.$row->id) }}" title="{{ $row->name }}">
        @if($cover !== '')
            <img src="{{ $cover }}" alt="{{ $row->name }}" loading="lazy"
                 onerror="var p=this.parentElement;this.remove();if(p)p.classList.add('is-empty');">
        @endif
        <span class="mall-card-empty">暂无封面</span>
        @if($hot)<em class="mall-card-badge">热门</em>@endif
        <em class="mall-card-points">{{ (int) $row->points }} 积分</em>
    </a>
    <div class="mall-card-meta">
        <h3><a href="{{ url('/mall/'.$row->id) }}">{{ $row->name }}</a></h3>
        <p class="muted">{{ $typeLabel }} · 剩 {{ (int) $row->stock }}</p>
    </div>
</article>
