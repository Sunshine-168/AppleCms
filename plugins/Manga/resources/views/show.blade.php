@extends('themes.default.layout')
@section('content')
    <p class="breadcrumb"><a href="{{ url('/manga') }}">漫画</a> / {{ $manga->title }}</p>
    <div class="person">
        @if($manga->cover)
            <img src="{{ $manga->cover }}" alt="{{ $manga->title }}">
        @endif
        <div>
            <h1>{{ $manga->title }}</h1>
            @if($manga->author)<p class="muted">作者 {{ $manga->author }}</p>@endif
            @if($manga->remarks)<p class="muted">{{ $manga->remarks }}</p>@endif
            @if($manga->content)<div class="desc">{!! nl2br(e($manga->content)) !!}</div>@endif
        </div>
    </div>
    <h2>章节</h2>
    @if($manga->chapters->isEmpty())
        <p class="muted">还没有章节。</p>
    @else
        <div class="eps">
            @foreach($manga->chapters as $ep)
                <a href="{{ url('/manga/'.$manga->id.'/'.$ep->id) }}">{{ $ep->name ?: ('第'.$ep->id.'话') }}</a>
            @endforeach
        </div>
    @endif
@endsection
