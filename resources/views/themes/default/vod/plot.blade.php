@extends('themes.default.layout')

@section('content')
    @if(!empty($plot))
        @vodBreadcrumb(['last' => $plot->title ?: '剧情'])
        <h1>{{ $plot->title ?: ('第'.$plot->episode_num.'集剧情') }}</h1>
        @if(!empty($video))
            <p class="muted"><a href="{{ $video->url }}">{{ $video->title }}</a> · 第{{ $plot->episode_num }}集</p>
        @endif
        <article class="desc">{!! nl2br(e($plot->content)) !!}</article>
    @else
        @vodBreadcrumb(['last' => '分集剧情'])
        <h1>分集剧情</h1>
        <form class="search" method="get" action="{{ vod_url('plots') }}" style="margin-bottom:16px;">
            <input type="search" name="video_id" value="{{ $videoId ?? request('video_id') }}" placeholder="影片ID">
            <button type="submit">筛选</button>
        </form>
        <ul>
            @forelse($plots as $item)
                <li>
                    <a href="{{ vod_url('plot', ['id' => $item->id]) }}">{{ $item->title ?: ('第'.$item->episode_num.'集') }}</a>
                    <span class="muted">影片 {{ $item->video_id }} · 第{{ $item->episode_num }}集</span>
                </li>
            @empty
                <li class="muted">暂无剧情</li>
            @endforelse
        </ul>
        @vodPaginate
    @endif
@endsection
