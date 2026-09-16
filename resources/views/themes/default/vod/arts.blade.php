@extends('themes.default.layout')

@section('content')
    @vodBreadcrumb(['last' => '资讯'])
    <h1>资讯</h1>
    <form class="search" method="get" action="{{ vod_url('arts') }}">
        <input type="search" name="wd" value="{{ request('wd') }}" placeholder="搜资讯">
        <button type="submit">搜索</button>
    </form>
    <ul class="list-plain">
        @foreach($arts as $item)
            <li>
                <a href="{{ $item->url }}">{{ $item->title }}</a>
                <span class="muted">{{ date('Y-m-d', (int) $item->created_at) }}</span>
            </li>
        @endforeach
    </ul>
    @vodPaginate
@endsection
