@extends('themes.default.layout')

@section('content')
    @if(!empty($plot))
        @vodBreadcrumb(['last' => $plot->title ?: '剧情'])
        <div class="list-head">
            <h1>{{ $plot->title ?: ('第'.$plot->episode_num.'集剧情') }}</h1>
            @if(!empty($video))
                <p class="muted"><a href="{{ $video->url }}">{{ $video->title }}</a> · 第{{ $plot->episode_num }}集</p>
            @endif
        </div>
        <article class="plot-article detail-comment-box">
            <div class="desc detail-desc">{{ $plot->content }}</div>
        </article>
        <p class="near-nav">
            <a class="btn-ghost" href="{{ vod_url('plots') }}?video_id={{ (int) $plot->video_id }}">同片剧情</a>
            @if(!empty($video))
                <a class="btn-play" href="{{ $video->play_url }}">去播放</a>
            @endif
        </p>
    @else
        @vodBreadcrumb(['last' => '分集剧情'])
        <div class="list-head">
            <h1>分集剧情</h1>
            <p class="muted">按影片查看分集剧情简介。</p>
        </div>
        <form class="search list-search" method="get" action="{{ vod_url('plots') }}">
            <input type="search" name="video_id" value="{{ $videoId ?? request('video_id') }}" placeholder="影片 ID，可空" aria-label="影片ID">
            <button type="submit" class="btn-ghost">筛选</button>
        </form>
        @if($plots->isEmpty())
            <div class="list-empty">
                <p>暂无剧情</p>
                <p><a class="btn-link" href="{{ url('/') }}">去逛逛</a></p>
            </div>
        @else
            <ul class="plot-list">
                @foreach($plots as $item)
                    <li>
                        <a class="plot-list-title" href="{{ vod_url('plot', ['id' => $item->id]) }}">{{ $item->title ?: ('第'.$item->episode_num.'集') }}</a>
                        <span class="muted">影片 #{{ $item->video_id }} · 第{{ $item->episode_num }}集</span>
                    </li>
                @endforeach
            </ul>
            @vodPaginate
        @endif
    @endif
@endsection
