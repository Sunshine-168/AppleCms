@extends('themes.default.layout')
@section('content')
    @php
        $filters = $filters ?? ['day' => ''];
        $days = $days ?? [];
        $day = (string) ($filters['day'] ?? '');
    @endphp
    <h1>最近更新</h1>
    @include('manga::partials.subnav')
    @if($days !== [])
        <div class="filter-row">
            <a href="{{ url('/manga/update') }}"@if($day === '') class="active"@endif>全部</a>
            @foreach($days as $item)
                <a href="{{ url('/manga/update?day='.$item['value']) }}"@if($day === $item['value']) class="active"@endif>{{ $item['label'] }}</a>
            @endforeach
        </div>
    @endif
    @if($list->isEmpty())
        <p class="muted">这一天没有更新。</p>
    @else
        <div class="grid">
            @foreach($list as $row)
                @include('manga::partials.card', ['row' => $row])
            @endforeach
        </div>
        @if(method_exists($list, 'links'))
            <div class="pager">{{ $list->links() }}</div>
        @endif
    @endif
    @include('manga::partials.continue')
@endsection
