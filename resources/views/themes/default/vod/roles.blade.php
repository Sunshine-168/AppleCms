@extends('themes.default.layout')

@section('content')
    @vodBreadcrumb(['last' => '角色'])
    <h1>角色</h1>
    <form class="search" method="get" action="{{ vod_url('roles') }}">
        <input type="search" name="wd" value="{{ request('wd') }}" placeholder="搜角色">
        <button type="submit">搜索</button>
    </form>
    <div class="grid">
        @foreach($roles as $item)
            <article class="card">
                <a href="{{ $item->url ?? vod_url('role', ['id' => $item->id]) }}">
                    @if($item->cover)
                        <img src="{{ $item->cover }}" alt="{{ $item->name }}">
                    @else
                        <img alt="{{ $item->name }}">
                    @endif
                    <div class="meta">
                        <h3>{{ $item->name }}</h3>
                    </div>
                </a>
            </article>
        @endforeach
    </div>
    @vodPaginate
@endsection
