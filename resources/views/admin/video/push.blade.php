<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>{{ conf('name') }} - 百度推送</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="stylesheet" href="{{ asset('static/admin/layui/css/layui.css') }}">
</head>
<body>
<div class="layui-fluid" style="padding:16px;">
  <div class="layui-card">
    <div class="layui-card-header">主动推送最新影片 URL</div>
    <div class="layui-card-body">
      <p class="layui-word-aux">Token 在站点设置中填写。每次最多 200 条。</p>
      <button class="layui-btn" id="push-run">立即推送 50 条</button>
    </div>
  </div>
</div>
<script src="{{ asset('static/admin/layui/layui.js') }}"></script>
<script>
layui.use(['layer'], function(){
  var $ = layui.$, layer = layui.layer;
  var csrf = $('meta[name=csrf-token]').attr('content');
  if (csrf) { $.ajaxSetup({headers:{'X-CSRF-TOKEN': csrf}}); }
  $('#push-run').on('click', function(){
    $.post('/admin/video/push/run', {limit:50}, function(res){
      layer.msg((res&&res.msg)||'完成', {icon:(res&&res.code===0)?1:2, time:4000});
    },'json');
  });
});
</script>
</body>
</html>
