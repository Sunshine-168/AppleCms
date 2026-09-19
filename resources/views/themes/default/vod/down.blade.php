@extends('themes.default.layout')

@section('content')
    @vodBreadcrumb
    <div class="list-head">
        <h1>{{ $video->title }}</h1>
        <p class="muted">下载线路 · <a href="{{ $video->url }}">影片详情</a> · <a href="{{ $video->play_url }}">在线播放</a></p>
    </div>

    <section class="play-panel">
        <div class="sec-head"><h2>线路</h2></div>
        <div class="lines">
            @vodSource(['type' => 'down'])
                <a class="{{ ($source?->id ?? 0) === $item->id ? 'on' : '' }}" href="{{ vod_url('down', ['id' => $video->id, 'sid' => $item->id]) }}">{{ $item->name }}</a>
            @endvodSource
        </div>
    </section>

    @php $groups = $downSources ?? collect(); @endphp
    @forelse($groups as $item)
        <section class="play-panel">
            <div class="sec-head"><h2>{{ $item->name }}</h2></div>
            <div class="eps">
                @foreach($item->episodes as $ep)
                    <a href="{{ $ep->down_url ?? $ep->url }}" target="_blank" rel="nofollow">{{ $ep->display_name }}</a>
                @endforeach
            </div>
        </section>
    @empty
        <section class="play-panel">
            <div class="sec-head"><h2>下载列表</h2></div>
            <div class="eps">
                @if($source)
                    @foreach($source->episodes as $ep)
                        <a href="{{ $ep->down_url ?? $ep->url }}" target="_blank" rel="nofollow">{{ $ep->display_name }}</a>
                    @endforeach
                @else
                    <p class="muted">暂无下载地址</p>
                @endif
            </div>
        </section>
    @endforelse
@endsection
