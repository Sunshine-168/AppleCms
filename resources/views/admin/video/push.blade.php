<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>{{ conf('name') }} - 搜索推送</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="stylesheet" href="{{ asset('static/admin/layui/css/layui.css') }}">
</head>
<body>
<div class="layui-fluid" style="padding:16px;">
  <div class="layui-card">
    <div class="layui-card-header">主动推送最新影片 URL</div>
    <div class="layui-card-body">
      <p class="layui-word-aux">Token 在站点设置中填写。增量 sitemap：<a href="/sitemap.xml?inc=1" target="_blank">/sitemap.xml?inc=1</a>（最近 48 小时）。</p>
      <div class="layui-btn-container" style="margin-top:12px;">
        <button class="layui-btn" data-engine="baidu">百度 50 条</button>
        <button class="layui-btn layui-btn-normal" data-engine="shenma">神马 50 条</button>
        <button class="layui-btn layui-btn-warm" data-engine="bing">必应 50 条</button>
      </div>
    </div>
  </div>
</div>
<script src="{{ asset('static/admin/layui/layui.js') }}"></script>
<script>
layui.use(['layer'], function(){
  var $ = layui.$, layer = layui.layer;
  var csrf = $('meta[name=csrf-token]').attr('content');
  if (csrf) { $.ajaxSetup({headers:{'X-CSRF-TOKEN': csrf}}); }
  $('[data-engine]').on('click', function(){
    $.post('/admin/video/push/run', {engine: $(this).data('engine'), limit:50}, function(res){
      layer.msg((res&&res.msg)||'完成', {icon:(res&&res.code===0)?1:2, time:4000});
    },'json');
  });
});
</script>
</body>
</html>
