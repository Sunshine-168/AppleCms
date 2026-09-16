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
    <div class="layui-card-header">清理并重生页面缓存</div>
    <div class="layui-card-body">
      <p class="layui-word-aux">会清空 Blade 与全页 HTML 缓存。开启站点设置里的「全页缓存」后，访客访问会重新写入。</p>
      <button class="layui-btn" id="make-run">执行</button>
    </div>
  </div>
</div>
<script src="{{ asset('static/admin/layui/layui.js') }}"></script>
<script>
layui.use(['layer'], function(){
  var $ = layui.$, layer = layui.layer;
  var csrf = $('meta[name=csrf-token]').attr('content');
  if (csrf) { $.ajaxSetup({headers:{'X-CSRF-TOKEN': csrf}}); }
  $('#make-run').on('click', function(){
    $.post('/admin/video/make/run', {}, function(res){
      layer.msg((res&&res.msg)||'完成', {icon:(res&&res.code===0)?1:2});
    },'json');
  });
});
</script>
</body>
</html>
