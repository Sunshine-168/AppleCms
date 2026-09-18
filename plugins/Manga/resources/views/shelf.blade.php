@extends('themes.default.layout')
@section('content')
    <h1>书架</h1>
    @include('manga::partials.subnav')
    @php
        $updated = $list->filter(static fn ($row) => (bool) ($row->has_update ?? false))->count();
    @endphp
    @if($list->isEmpty())
        <p class="muted">书架是空的。登录后在漫画详情加入。</p>
    @else
        @if($updated > 0)
            <p class="muted">有 {{ $updated }} 部作品更新了新章节。</p>
        @endif
        <div class="grid">
            @foreach($list as $row)
                @include('manga::partials.card', ['row' => $row])
            @endforeach
        </div>
    @endif
    @include('manga::partials.continue')
@endsection
