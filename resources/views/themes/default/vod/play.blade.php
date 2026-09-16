@extends('themes.default.layout')

@section('content')
    @vodBreadcrumb
    <h1>{{ $video->title }} {{ $episode?->display_name }}</h1>
    @if(session('error'))<p class="muted">{{ session('error') }}</p>@endif
    <div class="player">
        @if($episode && $episode->url)
        <iframe class="player-frame" src="{{ vod_url('player', ['id' => $video->id, 'sid' => $source?->id, 'nid' => $episode?->id]) }}" allowfullscreen></iframe>
        @else
            <p class="muted">暂无播放地址</p>
        @endif
    </div>
    <div class="lines">
        @vodSource(['type' => 'play'])
            <a class="{{ ($source?->id ?? 0) === $item->id ? 'on' : '' }}" href="{{ vod_url('play', ['id' => $video->id, 'sid' => $item->id]) }}">{{ $item->name }}</a>
        @endvodSource
    </div>
    <div class="eps">
        @vodEpisode
            <a class="{{ ($episode?->id ?? 0) === $item->id ? 'on' : '' }}" href="{{ $item->play_url }}">{{ $item->display_name }}</a>
        @endvodEpisode
    </div>
    <script>
    (function(){
        try {
            var key = 'vod_guest_history';
            var list = JSON.parse(localStorage.getItem(key) || '[]');
            var row = {id: {{ (int) $video->id }}, title: @json($video->title), url: @json(url()->current()), t: Date.now()};
            list = list.filter(function(it){ return String(it.id) !== String(row.id); });
            list.unshift(row);
            localStorage.setItem(key, JSON.stringify(list.slice(0, 30)));
        } catch (e) {}
    })();
    </script>
@endsection
