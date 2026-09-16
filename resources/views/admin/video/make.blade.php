<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>{{ conf('name') }} - 静态生成</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="stylesheet" href="{{ asset('static/admin/layui/css/layui.css') }}">
</head>
<body>
<div class="layui-fluid" style="padding:16px;">
  <div class="layui-card">
    <div class="layui-card-header">写出静态 HTML 到 public/html</div>
    <div class="layui-card-body">
      <p class="layui-word-aux">会请求前台页面并把结果写成文件：首页、分类、详情。可配合 Web 服务器把 html 目录当静态根。</p>
      <div class="layui-btn-container" style="margin-top:12px;">
        <button class="layui-btn" data-scope="index">生成首页</button>
        <button class="layui-btn layui-btn-normal" data-scope="type">生成分类</button>
        <button class="layui-btn layui-btn-warm" data-scope="detail">生成详情</button>
        <button class="layui-btn layui-btn-danger" data-scope="all">全部生成</button>
        <button class="layui-btn layui-btn-primary" id="hits-reset">重置日人气</button>
      </div>
      <pre id="make-result" class="layui-code" style="margin-top:16px;min-height:80px;"></pre>
    </div>
  </div>
</div>
<script src="{{ asset('static/admin/layui/layui.js') }}"></script>
<script>
layui.use(['layer'], function(){
  var $ = layui.$, layer = layui.layer;
  var csrf = $('meta[name=csrf-token]').attr('content');
  if (csrf) { $.ajaxSetup({headers:{'X-CSRF-TOKEN': csrf}}); }
  $('#hits-reset').on('click', function(){
    $.post('/admin/video/hits-reset', {}, function(res){
      layer.msg((res&&res.msg)||'完成', {icon:(res&&res.code===0)?1:2});
    },'json');
  });
  $('[data-scope]').on('click', function(){
    var scope = $(this).data('scope');
    var load = layer.load(1);
    $.post('/admin/video/make/run', {scope: scope}, function(res){
      layer.close(load);
      $('#make-result').text(JSON.stringify(res, null, 2));
      layer.msg((res&&res.msg)||'完成', {icon:(res&&res.code===0)?1:2, time:4000});
    },'json').fail(function(){ layer.close(load); layer.msg('失败',{icon:2}); });
  });
});
</script>
</body>
</html>
