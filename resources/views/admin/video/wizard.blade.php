@extends('admin.layouts.inner')
@section('title', '标签向导')

@section('content')
<p class="hint">Blade 标签片段（对齐 LaraCMS，不是苹果 {maccms:vod}）。只能改主题目录下的 blade。保存前会备份。</p>
@verbatim
<pre class="code-block">
@vod(['by'=>'hits','num'=>12])
  <a href="{{ $item->url }}">{{ $item->title }}</a>
@endvod

@vodType(['type'=>'top'])
  <a href="{{ $item->url }}">{{ $item->name }}</a>
@endvodType

@vodAd(['slot'=>'play'])
  {!! $item->content !!}
@endvodAd

@vodComment(['num'=>20])
  <p>{{ $item->author_name }}：{{ $item->content }}</p>
@endvodComment
</pre>
@endverbatim
@endsection
