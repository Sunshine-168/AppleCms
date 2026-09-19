@extends('themes.default.layout')
@section('content')
<div class="list-head"><h1>阅读历史</h1></div>
@include('novel::partials.subnav')
@forelse($list as $row)
@if($row->novel)<p><a href="{{ url('/novel/'.$row->novel_id.'/'.$row->chapter_id) }}">{{ $row->novel->title }} · {{ $row->chapter?->name }}</a></p>@endif
@empty<p class="muted">还没有阅读记录。</p>@endforelse
@endsection
