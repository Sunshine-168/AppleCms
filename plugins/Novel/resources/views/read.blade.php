@extends('themes.default.layout')
@section('content')
<p class="breadcrumb"><a href="{{ url('/novel') }}">小说</a> / <a href="{{ url('/novel/'.$novel->id) }}">{{ $novel->title }}</a> / {{ $chapter->name }}</p>
<nav class="manga-read-nav">@if($prev)<a class="btn-ghost" href="{{ url('/novel/'.$novel->id.'/'.$prev->id) }}">上一章</a>@endif<a class="btn-ghost" href="{{ url('/novel/'.$novel->id) }}">目录</a>@if($next)<a class="btn-ghost" href="{{ url('/novel/'.$novel->id.'/'.$next->id) }}">下一章</a>@endif<button type="button" class="btn-ghost" id="novel-font">字号</button></nav>
<article id="novel-reader" style="max-width:820px;margin:30px auto;font-size:18px;line-height:2"><h1>{{ $chapter->name }}</h1>{!! nl2br(e($chapter->content)) !!}</article>
<script>document.getElementById('novel-font').onclick=function(){var e=document.getElementById('novel-reader'),n=parseInt(getComputedStyle(e).fontSize);e.style.fontSize=(n>=24?16:n+2)+'px'};</script>
@endsection
