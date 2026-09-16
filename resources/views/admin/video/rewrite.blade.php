<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>{{ conf('name') }} - 伪静态规则</title>
  <link rel="stylesheet" href="{{ asset('static/admin/layui/css/layui.css') }}">
</head>
<body>
<div class="layui-fluid" style="padding:16px;">
  <div class="layui-card">
    <div class="layui-card-header">伪静态规则（当前 {{ $mode ?? 'laravel' }}{{ $suffix ?? '' }}）</div>
    <div class="layui-card-body">
      <p class="layui-word-aux">把片段放到站点根配置里。Laravel 模式走 try_files；苹果模式额外保留 index.php/vod。</p>
      <h3>Nginx</h3>
      <pre class="layui-code">{{ $nginx ?? '' }}</pre>
      <h3>Apache</h3>
      <pre class="layui-code">{{ $apache ?? '' }}</pre>
    </div>
  </div>
</div>
</body>
</html>
