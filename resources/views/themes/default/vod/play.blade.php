@extends('themes.default.layout')

@section('content')
    @vodBreadcrumb
    <h1>{{ $video->title }} {{ $episode?->display_name }}</h1>
    @if(session('error'))<p class="muted">{{ session('error') }}</p>@endif
    @if(($trysee ?? 0) > 0)<p class="muted">试看 {{ (int) $trysee }} 秒，完整播放需积分</p>@endif
    <div class="player">
        @if($episode && $episode->url)
        <iframe class="player-frame" src="{{ vod_url('player', ['id' => $video->id, 'sid' => $source?->id, 'nid' => $episode?->id]) }}" allowfullscreen></iframe>
        @else
            <p class="muted">暂无播放地址</p>
        @endif
    </div>
    @vodAd(['slot' => 'play'])
        <div class="desc">{!! $item->content !!}</div>
    @endvodAd
    <p>
        <button type="button" id="play-fail">播放报错</button>
    </p>
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
    document.getElementById('play-fail')?.addEventListener('click', function(){
        fetch(@json(url('/play/fail')), {
            method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content},
            body: JSON.stringify({
                video_id: {{ (int) $video->id }},
                source_id: {{ (int) ($source?->id ?? 0) }},
                episode_id: {{ (int) ($episode?->id ?? 0) }},
                url: location.href,
                content: '播放失败'
            })
        }).then(function(r){ return r.json(); }).then(function(res){ alert(res.msg || '已提交'); }).catch(function(){ alert('提交失败'); });
    });
    var tryseeSeconds = {{ (int) ($trysee ?? 0) }};
    if (tryseeSeconds > 0) {
        setTimeout(function(){
            var frame = document.querySelector('.player-frame');
            if (frame) { frame.remove(); }
            var box = document.querySelector('.player');
            if (box) { box.innerHTML = '<p class="muted">试看已结束，请充值或升级会员后观看完整影片</p>'; }
        }, tryseeSeconds * 1000);
    }
    </script>
@endsection
