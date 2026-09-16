@extends('themes.default.layout')
@section('content')
    <h1>{{ $title }}</h1>
    <p><a href="{{ url('/member') }}">返回会员中心</a></p>
    <div class="grid">
        @foreach($videos as $item)
            @include('themes.default.partials.vod-card')
        @endforeach
    </div>
@endsection
