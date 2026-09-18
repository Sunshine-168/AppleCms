@extends('themes.default.layout')
@section('content')
    @php
        $filters = $filters ?? ['board' => 'hits'];
        $board = (string) ($filters['board'] ?? 'hits');
    @endphp
    <h1>漫画排行</h1>
    @include('manga::partials.subnav')
    <div class="filter-row">
        <a href="{{ url('/manga/rank') }}"@if($board === 'hits') class="active"@endif>人气</a>
        <a href="{{ url('/manga/rank?board=new') }}"@if($board === 'new') class="active"@endif>新作</a>
        <a href="{{ url('/manga/rank?board=end') }}"@if($board === 'end') class="active"@endif>完结</a>
    </div>
    @if($list->isEmpty())
        <p class="muted">还没有可排行的漫画。</p>
    @else
        <ol class="manga-rank">
            @foreach($list as $row)
                @php $latest = is_array($row->latest_chapter ?? null) ? $row->latest_chapter : null; @endphp
                <li>
                    <a href="{{ url('/manga/'.$row->id) }}">{{ $row->title }}</a>
                    <span class="muted">{{ $row->serializeLabel() }} · 人气 {{ $row->hits }}</span>
                    @if(is_array($latest) && (int) ($latest['id'] ?? 0) > 0)
                        <a href="{{ url('/manga/'.$row->id.'/'.$latest['id']) }}">{{ $latest['name'] }}</a>
                    @endif
                </li>
            @endforeach
        </ol>
    @endif
@endsection
