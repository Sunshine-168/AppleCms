@extends('themes.default.layout')

@section('content')
    @vodBreadcrumb(['last' => '网址导航'])
    <h1>网址导航</h1>
    <form class="search" method="get" action="{{ vod_url('websites') }}">
        <input type="search" name="wd" value="{{ request('wd') }}" placeholder="搜网址">
        <button type="submit">搜索</button>
    </form>
    <ul>
        @foreach($list as $item)
            <li>
                <a href="{{ vod_url('website', ['id' => $item->id]) }}">{{ $item->name }}</a>
                <a href="{{ $item->url }}" target="_blank" rel="nofollow">打开</a>
                {{ $item->blurb }}
            </li>
        @endforeach
    </ul>
@endsection
