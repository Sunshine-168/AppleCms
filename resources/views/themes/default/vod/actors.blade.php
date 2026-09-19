@extends('themes.default.layout')

@section('content')
    @vodBreadcrumb(['last' => '演员库'])
    <div class="list-head">
        <h1>演员库</h1>
        <p class="muted">影人资料，点进查看相关作品。</p>
    </div>
    @if($actors->isEmpty())
        <div class="list-empty">
            <p>还没有演员</p>
            <p><a class="btn-link" href="{{ url('/') }}">回首页</a></p>
        </div>
    @else
        <div class="grid">
            @foreach($actors as $item)
                <article class="vod-card">
                    @php $avatar = trim((string) ($item->avatar ?? '')); @endphp
                    <a class="vod-card-cover{{ $avatar === '' ? ' is-empty' : '' }}" href="{{ $item->url }}" title="{{ $item->name }}">
                        @if($avatar !== '')
                            <img src="{{ $avatar }}" alt="{{ $item->name }}" loading="lazy"
                                 onerror="var p=this.parentElement;this.remove();if(p)p.classList.add('is-empty');">
                        @endif
                        <span class="vod-card-empty">暂无头像</span>
                    </a>
                    <div class="vod-card-meta">
                        <h3><a href="{{ $item->url }}" title="{{ $item->name }}">{{ $item->name }}</a></h3>
                    </div>
                </article>
            @endforeach
        </div>
        @vodPaginate
    @endif
@endsection
