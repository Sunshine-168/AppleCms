<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>{{ conf('name') }} - 标签向导</title>
  <link rel="stylesheet" href="{{ asset('static/admin/layui/css/layui.css') }}">
</head>
<body>
<div class="layui-fluid" style="padding:16px;">
  <div class="layui-card">
    <div class="layui-card-header">Blade 标签片段（对齐 LaraCMS，不是苹果 {maccms:vod}）</div>
    <div class="layui-card-body">
@verbatim
<pre class="layui-code">
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
      <p class="layui-word-aux">只能改主题目录下的 blade。保存前会备份。</p>
    </div>
  </div>
</div>
</body>
</html>
