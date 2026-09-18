@extends('themes.default.layout')
@if($next ?? null)
@push('head')
    <link rel="prefetch" href="{{ url('/manga/'.$manga->id.'/'.$next->id) }}">
@endpush
@endif
@section('content')
    @php
        $prev = $prev ?? null;
        $next = $next ?? null;
        $epName = $chapter->name ?: ('第'.$chapter->id.'话');
        $pics = $pics ?? [];
    @endphp
    <p class="breadcrumb">
        <a href="{{ url('/manga') }}">漫画</a> /
        <a href="{{ url('/manga/'.$manga->id) }}">{{ $manga->title }}</a> /
        {{ $epName }}
    </p>
    <h1>{{ $manga->title }} · {{ $epName }}</h1>
    <p class="manga-read-nav is-sticky">
        @if($prev)
            <a id="manga-prev" href="{{ url('/manga/'.$manga->id.'/'.$prev->id) }}">上一话 {{ $prev->name ?: ('第'.$prev->id.'话') }}</a>
        @else
            <span class="muted">没有上一话</span>
        @endif
        <a href="{{ url('/manga/'.$manga->id) }}">目录</a>
        <button type="button" class="btn-link" id="manga-night">夜间</button>
        @if($next)
            <a id="manga-next" href="{{ url('/manga/'.$manga->id.'/'.$next->id) }}">下一话 {{ $next->name ?: ('第'.$next->id.'话') }}</a>
        @else
            <span class="muted">没有下一话</span>
        @endif
    </p>
    @if($manga->chapters->isNotEmpty())
        <details class="manga-chapter-drawer">
            <summary>本话目录</summary>
            <div class="eps">
                @foreach($manga->chapters as $ep)
                    <a href="{{ url('/manga/'.$manga->id.'/'.$ep->id) }}"@if((int) $ep->id === (int) $chapter->id) class="on"@endif>{{ $ep->name ?: ('第'.$ep->id.'话') }}</a>
                @endforeach
            </div>
        </details>
    @endif
    <div class="manga-read">
        @forelse($pics as $src)
            <p><img src="{{ $src }}" alt="" loading="lazy"></p>
        @empty
            <p class="muted">这一话还没有图片地址。</p>
        @endforelse
    </div>
    <p class="manga-read-nav">
        @if($prev)
            <a href="{{ url('/manga/'.$manga->id.'/'.$prev->id) }}">上一话</a>
        @else
            <span class="muted">没有上一话</span>
        @endif
        <a href="{{ url('/manga/'.$manga->id) }}">目录</a>
        @if($next)
            <a href="{{ url('/manga/'.$manga->id.'/'.$next->id) }}">下一话</a>
        @else
            <span class="muted">没有下一话</span>
        @endif
    </p>
    <button type="button" class="manga-gotop" id="manga-gotop" hidden>顶部</button>
@endsection
@push('scripts')
<script>
(function () {
    try {
        var key = 'manga_history';
        var map = JSON.parse(localStorage.getItem(key) || '{}');
        map[String(@json((int) $manga->id))] = {
            chapter: @json((int) $chapter->id),
            name: @json($epName),
            title: @json((string) $manga->title),
            at: Date.now()
        };
        localStorage.setItem(key, JSON.stringify(map));
    } catch (e) {}
    var nightKey = 'manga_night';
    var nightBtn = document.getElementById('manga-night');
    function applyNight(on) {
        document.body.classList.toggle('manga-night', !!on);
        if (nightBtn) nightBtn.textContent = on ? '日间' : '夜间';
    }
    applyNight(localStorage.getItem(nightKey) === '1');
    if (nightBtn) {
        nightBtn.addEventListener('click', function () {
            var on = !document.body.classList.contains('manga-night');
            localStorage.setItem(nightKey, on ? '1' : '0');
            applyNight(on);
        });
    }
    var prev = document.getElementById('manga-prev');
    var next = document.getElementById('manga-next');
    document.addEventListener('keydown', function (e) {
        if (e.target && /input|textarea/i.test(e.target.tagName)) return;
        if (e.key === 'ArrowLeft' && prev) location.href = prev.href;
        if (e.key === 'ArrowRight' && next) location.href = next.href;
    });
    var topBtn = document.getElementById('manga-gotop');
    window.addEventListener('scroll', function () {
        if (topBtn) topBtn.hidden = window.scrollY < 400;
    });
    if (topBtn) topBtn.addEventListener('click', function () { window.scrollTo({ top: 0, behavior: 'smooth' }); });
})();
</script>
@endpush
