@extends('themes.default.layout')

@section('content')
    @vodBreadcrumb
    <h1>{{ $video->title }} 下载</h1>
    <div class="lines">
        @vodSource(['type' => 'down'])
            <a class="{{ ($source?->id ?? 0) === $item->id ? 'on' : '' }}" href="{{ vod_url('down', ['id' => $video->id, 'sid' => $item->id]) }}">{{ $item->name }}</a>
        @endvodSource
    </div>
    <div class="eps">
        @if($source)
            @foreach($source->episodes as $ep)
                <a href="{{ $ep->url }}" target="_blank" rel="nofollow">{{ $ep->display_name }}</a>
            @endforeach
        @else
            <p class="muted">暂无下载地址</p>
        @endif
    </div>
@endsection
