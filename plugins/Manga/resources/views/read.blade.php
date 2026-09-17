@extends('themes.default.layout')
@section('content')
    <p class="breadcrumb">
        <a href="{{ url('/manga') }}">漫画</a> /
        <a href="{{ url('/manga/'.$manga->id) }}">{{ $manga->title }}</a> /
        {{ $chapter->name ?: ('第'.$chapter->id.'话') }}
    </p>
    <h1>{{ $manga->title }} · {{ $chapter->name ?: ('第'.$chapter->id.'话') }}</h1>
    @forelse($pics as $src)
        <p style="margin:0 0 8px"><img src="{{ $src }}" alt="" style="display:block;width:100%;max-width:900px;margin:0 auto"></p>
    @empty
        <p class="muted">这一话还没有图片地址。</p>
    @endforelse
@endsection
