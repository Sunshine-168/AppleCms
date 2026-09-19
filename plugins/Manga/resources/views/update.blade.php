@extends('themes.default.layout')
@section('content')
    @php
        $filters = $filters ?? ['day' => ''];
        $days = $days ?? [];
        $day = (string) ($filters['day'] ?? '');
    @endphp
    <div class="list-head manga-head">
        <h1>最近更新</h1>
        <p class="muted">按日期看看又上了哪些新话。</p>
    </div>
    @include('manga::partials.subnav')
    @if($days !== [])
        <div class="filters">
            <div class="filter-row">
                <span class="filter-label">日期</span>
                <div class="filter-choices">
                    <a href="{{ url('/manga/update') }}"@if($day === '') class="active"@endif>全部</a>
                    @foreach($days as $item)
                        <a href="{{ url('/manga/update?day='.$item['value']) }}"@if($day === $item['value']) class="active"@endif>{{ $item['label'] }}</a>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
    @if($list->isEmpty())
        <div class="list-empty">
            <p>{{ $day !== '' ? '这一天没有更新' : '最近没有更新' }}</p>
            <p><a class="btn-link" href="{{ url('/manga') }}">去逛逛漫画</a></p>
        </div>
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
