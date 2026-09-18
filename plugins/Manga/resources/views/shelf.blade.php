@extends('themes.default.layout')
@section('content')
    <h1>书架</h1>
    @include('manga::partials.subnav')
    @if($list->isEmpty())
        <p class="muted">书架是空的。登录后在漫画详情加入。</p>
    @else
        <div class="grid">
            @foreach($list as $row)
                @include('manga::partials.card', ['row' => $row])
            @endforeach
        </div>
    @endif
    @include('manga::partials.continue')
@endsection
