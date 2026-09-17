@extends('themes.default.layout')
@section('content')
    <p class="breadcrumb"><a href="{{ url('/mall') }}">积分商城</a> / {{ $goods->name }}</p>
    <div class="person">
        @if($goods->cover)
            <img src="{{ $goods->cover }}" alt="{{ $goods->name }}">
        @endif
        <div>
            <h1>{{ $goods->name }}</h1>
            <p>{{ (int) $goods->points }} 积分 · 库存 {{ (int) $goods->stock }}</p>
            @if($goods->hint)<div class="desc">{!! nl2br(e($goods->hint)) !!}</div>@endif
            @auth('member')
                @if((int) $goods->stock > 0)
                    <form method="post" action="{{ url('/mall/'.$goods->id.'/buy') }}">
                        @csrf
                        <button type="submit">兑换</button>
                    </form>
                @else
                    <p class="muted">已兑完</p>
                @endif
            @else
                <p><a href="{{ url('/member/login') }}">登录后兑换</a></p>
            @endauth
        </div>
    </div>
@endsection
