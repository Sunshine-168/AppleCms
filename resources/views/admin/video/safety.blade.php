<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>{{ conf('name') }} - 挂马扫描</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="stylesheet" href="{{ asset('static/admin/layui/css/layui.css') }}">
</head>
<body>
<div class="layui-fluid" style="padding:16px;">
  <div class="layui-card">
    <div class="layui-card-header">挂马扫描</div>
    <div class="layui-card-body">
      <p class="layui-word-aux">扫描 <code>app/</code> 与 <code>public/</code> 下 PHP 文件中的危险函数调用，只报告文件与行号。</p>
      <div class="layui-btn-container" style="margin-top:12px;">
        <button class="layui-btn layui-btn-danger" id="safety-scan">开始扫描</button>
      </div>
      <pre id="safety-result" class="layui-code" style="margin-top:16px;min-height:120px;"></pre>
    </div>
  </div>
</div>
<script src="{{ asset('static/admin/layui/layui.js') }}"></script>
<script>
layui.use(['layer'], function(){
  var $ = layui.$, layer = layui.layer;
  var csrf = $('meta[name=csrf-token]').attr('content');
  if (csrf) { $.ajaxSetup({headers:{'X-CSRF-TOKEN': csrf}}); }
  $('#safety-scan').on('click', function(){
    var load = layer.load(1);
    $.post('/admin/video/safety/scan', {}, function(res){
      layer.close(load);
      var rows = (res && res.data && res.data.hits) ? res.data.hits : [];
      $('#safety-result').text(rows.length ? rows.join('\n') : ((res && res.msg) || '未发现可疑调用'));
      layer.msg((res && res.msg) || '完成', {icon: (res && res.code===0)?1:2, time:3000});
    }, 'json').fail(function(){ layer.close(load); layer.msg('扫描失败',{icon:2}); });
  });
});
</script>
</body>
</html>
