@extends('themes.default.layout')
@section('content')
<div class="list-head"><h1>小说书架</h1></div>
@include('novel::partials.subnav')
<div class="grid">@forelse($list as $row) @include('novel::partials.card', ['row'=>$row]) @empty <p class="muted">书架是空的。</p> @endforelse</div>
@endsection
