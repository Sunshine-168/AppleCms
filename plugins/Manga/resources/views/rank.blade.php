@extends('themes.default.layout')
@section('content')
    @php
        $filters = $filters ?? ['board' => 'hits'];
        $board = (string) ($filters['board'] ?? 'hits');
    @endphp
    <div class="list-head manga-head">
        <h1>漫画排行</h1>
        <p class="muted">按人气、收藏和新作看看大家在追什么。</p>
    </div>
    @include('manga::partials.subnav')
    <div class="filters">
        <div class="filter-row">
            <span class="filter-label">榜单</span>
            <div class="filter-choices">
                <a href="{{ url('/manga/rank') }}"@if($board === 'hits') class="active"@endif>人气</a>
                <a href="{{ url('/manga/rank?board=favor') }}"@if($board === 'favor') class="active"@endif>收藏</a>
                <a href="{{ url('/manga/rank?board=new') }}"@if($board === 'new') class="active"@endif>新作</a>
                <a href="{{ url('/manga/rank?board=end') }}"@if($board === 'end') class="active"@endif>完结</a>
            </div>
        </div>
    </div>
    @if($list->isEmpty())
        <div class="list-empty">
            <p>{{ $board === 'favor' ? '还没有书架收藏可排行' : '还没有可排行的漫画' }}</p>
            <p><a class="btn-link" href="{{ url('/manga') }}">去逛逛漫画</a></p>
        </div>
    @else
        <ol class="manga-rank">
            @foreach($list as $i => $row)
                @php $latest = is_array($row->latest_chapter ?? null) ? $row->latest_chapter : null; @endphp
                <li>
                    <span class="manga-rank-n">{{ $i + 1 }}</span>
                    <div class="manga-rank-body">
                        <a class="manga-rank-title" href="{{ url('/manga/'.$row->id) }}">{{ $row->title }}</a>
                        <span class="muted">
                            {{ $row->serializeLabel() }}
                            @if($board === 'favor')
                                · 收藏 {{ (int) ($row->favor_count ?? 0) }}
                            @else
                                · 人气 {{ $row->hits }}
                            @endif
                        </span>
                    </div>
                    @if(is_array($latest) && (int) ($latest['id'] ?? 0) > 0)
                        <a class="manga-rank-ep" href="{{ url('/manga/'.$row->id.'/'.$latest['id']) }}">{{ $latest['name'] }}</a>
                    @endif
                </li>
            @endforeach
        </ol>
    @endif
@endsection
