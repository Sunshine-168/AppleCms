@extends('themes.default.layout')
@section('content')
    <div class="member-page">
        <div class="list-head">
            <h1>{{ $title }}</h1>
            <p class="muted"><a href="{{ url('/member') }}">返回会员中心</a></p>
        </div>
        @if($videos->isEmpty())
            <div class="list-empty">
                <p>{{ $title === '我的收藏' ? '还没有收藏影片' : '还没有观看记录' }}</p>
                <p class="muted">去首页挑几部看看吧。</p>
                <p><a class="btn-link" href="{{ url('/') }}">去逛逛</a></p>
            </div>
        @else
            <div class="grid">
                @foreach($videos as $item)
                    @include('themes.default.partials.vod-card')
                @endforeach
            </div>
        @endif
    </div>
@endsection
