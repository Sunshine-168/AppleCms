@extends('themes.default.layout')

@section('content')
    @vodBreadcrumb(['last' => $topic->name])
    @if($topic->cover)
        <p><img src="{{ $topic->cover }}" alt="{{ $topic->name }}"></p>
    @endif
    <h1>{{ $topic->name }}</h1>
    @if($topic->sub)
        <p class="muted">{{ $topic->sub }}</p>
    @endif
    <p class="desc">{{ $topic->blurb }}</p>
    @if($topic->content)
        <div class="content">{!! $topic->content !!}</div>
    @endif
    <p>本专题共 {{ $videos instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator ? $videos->total() : $videos->count() }} 部影片</p>
    <div class="grid">
        @foreach($videos as $item)
            @include('themes.default.partials.vod-card')
        @endforeach
    </div>
    @vodPaginate
    @if(isset($arts) && count($arts))
        <h2>相关文章</h2>
        <ul class="list-plain">
            @foreach($arts as $art)
                <li><a href="{{ $art->url }}">{{ $art->title }}</a></li>
            @endforeach
        </ul>
    @endif
@endsection
