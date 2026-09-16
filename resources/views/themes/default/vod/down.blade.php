@extends('themes.default.layout')

@section('content')
    @vodBreadcrumb
    <h1>{{ $video->title }} 下载</h1>
    <div class="lines">
        @vodSource(['type' => 'down'])
            <a class="{{ ($source?->id ?? 0) === $item->id ? 'on' : '' }}" href="{{ vod_url('down', ['id' => $video->id, 'sid' => $item->id]) }}">{{ $item->name }}</a>
        @endvodSource
    </div>
    @php $groups = $downSources ?? collect(); @endphp
    @forelse($groups as $item)
        <h3>{{ $item->name }}</h3>
        <div class="eps">
            @foreach($item->episodes as $ep)
                <a href="{{ $ep->down_url ?? $ep->url }}" target="_blank" rel="nofollow">{{ $ep->display_name }}</a>
            @endforeach
        </div>
    @empty
        <div class="eps">
            @if($source)
                @foreach($source->episodes as $ep)
                    <a href="{{ $ep->down_url ?? $ep->url }}" target="_blank" rel="nofollow">{{ $ep->display_name }}</a>
                @endforeach
            @else
                <p class="muted">暂无下载地址</p>
            @endif
        </div>
    @endforelse
@endsection
