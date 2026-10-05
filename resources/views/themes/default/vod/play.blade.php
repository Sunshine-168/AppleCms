@extends('themes.default.layout')

@section('content')
@php
    $epName = trim((string) ($episode?->display_name ?? ''));
    $lineName = trim((string) ($source?->display_name ?? $source?->name ?? ''));
@endphp
    @vodBreadcrumb
    <div class="play-head">
        <h1 class="play-title">
            <a href="{{ $video->url }}">{{ $video->title }}</a>
            @if($epName !== '')
                <span class="muted">{{ $epName }}</span>
            @endif
        </h1>
        <p class="play-sub muted">
            @if($lineName !== '')线路 {{ $lineName }} · @endif
            <a href="{{ $video->url }}">影片详情</a>
        </p>
    </div>

    @if(!empty($site['theme_play_notice']))
        <p class="muted play-notice">{{ $site['theme_play_notice'] }}</p>
    @endif
    @if(($trysee ?? 0) > 0)
        <p class="play-tip">试看 {{ (int) $trysee }} 秒，完整播放需积分或会员</p>
    @endif

    <div class="player{{ ($episode && $episode->url) ? '' : ' is-empty' }}">
        @if($episode && $episode->url)
            <iframe class="player-frame" src="{{ vod_url('player', ['id' => $video->id, 'sid' => $source?->id, 'nid' => $episode?->id]) }}" allowfullscreen allow="autoplay; fullscreen"></iframe>
        @else
            <div class="player-empty">
                <p>暂无播放地址</p>
                <p class="muted">请切换线路，或回到详情页查看是否已入库。</p>
                <p><a class="btn-ghost" href="{{ $video->url }}">返回详情</a></p>
            </div>
        @endif
    </div>

    @includeIf('chatroom::panel')
    @if(\Illuminate\Support\Facades\View::exists('advert::player'))
        @include('advert::player')
    @else
        @vodAd(['slot' => 'play'])
            <div class="desc">{!! $item->content !!}</div>
        @endvodAd
    @endif

    <div class="play-tools detail-actions">
        <button type="button" class="btn-ghost" id="play-fail">播放报错</button>
        <a class="btn-ghost" href="{{ $video->url }}">影片详情</a>
        @include('themes.default.partials.share-link')
    </div>

    <section class="play-panel">
        <div class="sec-head"><h2>线路</h2></div>
        <div class="lines">
            @vodSource(['type' => 'play'])
                <a class="{{ ($source?->id ?? 0) === $item->id ? 'on' : '' }}" href="{{ vod_url('play', ['id' => $video->id, 'sid' => $item->id]) }}">{{ $item->display_name ?? $item->name }}</a>
            @endvodSource
        </div>
    </section>

    <section class="play-panel">
        <div class="sec-head">
            <h2>剧集</h2>
            @php
                $epCount = 0;
                try {
                    $epCount = (int) $video->episodes()->count();
                } catch (\Throwable) {
                    $epCount = 0;
                }
            @endphp
            @if($epCount > 0)
                <span class="muted">共 {{ $epCount }} 集</span>
            @endif
        </div>
        <div class="eps">
            @vodEpisode
                <a class="{{ ($episode?->id ?? 0) === $item->id ? 'on' : '' }}" href="{{ $item->play_url }}">{{ $item->display_name }}</a>
            @endvodEpisode
        </div>
    </section>

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
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},
            body: JSON.stringify({
                video_id: {{ (int) $video->id }},
                source_id: {{ (int) ($source?->id ?? 0) }},
                episode_id: {{ (int) ($episode?->id ?? 0) }},
                url: location.href,
                content: '播放失败'
            })
        }).then(function(r){ return r.json().catch(function(){ return null; }); }).then(function(res){
            vodResult(res, '提交失败');
        }).catch(function(){ vodToast('网络异常，请重试', 'err'); });
    });
    var tryseeSeconds = {{ (int) ($trysee ?? 0) }};
    if (tryseeSeconds > 0) {
        setTimeout(function(){
            var frame = document.querySelector('.player-frame');
            if (frame) { frame.remove(); }
            var box = document.querySelector('.player');
            if (box) {
                box.classList.add('is-empty');
                box.innerHTML = '<div class="player-empty"><p>试看已结束</p><p class="muted">请充值或升级会员后观看完整影片</p></div>';
            }
        }, tryseeSeconds * 1000);
    }
    </script>
@endsection
