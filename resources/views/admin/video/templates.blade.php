<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>{{ conf('name') }} - 模板编辑</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="stylesheet" href="{{ asset('static/admin/layui/css/layui.css') }}">
</head>
<body>
<div class="layui-fluid" style="padding:16px;">
  <div class="layui-row layui-col-space12">
    <div class="layui-col-md3">
      <div class="layui-card"><div class="layui-card-header">主题文件</div>
        <div class="layui-card-body" style="max-height:70vh;overflow:auto;">
          @foreach($files as $f)
            <p><a href="javascript:;" class="tpl-file" data-path="{{ $f['path'] }}">{{ $f['name'] }}</a></p>
          @endforeach
        </div>
      </div>
    </div>
    <div class="layui-col-md9">
      <div class="layui-card">
        <div class="layui-card-header">编辑 <span id="tpl-path"></span>
          <button class="layui-btn layui-btn-sm" id="tpl-save" style="float:right;">保存</button>
          <button class="layui-btn layui-btn-sm layui-btn-primary" id="tpl-backup" style="float:right;margin-right:8px;">备份</button>
          <button class="layui-btn layui-btn-sm layui-btn-warm" id="tpl-rollback" style="float:right;margin-right:8px;">回滚</button>
        </div>
        <div class="layui-card-body">
          <textarea id="tpl-content" class="layui-textarea" style="min-height:62vh;font-family:monospace;"></textarea>
        </div>
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
  var current = '';
  $('.tpl-file').on('click', function(){
    current = $(this).data('path');
    $('#tpl-path').text(current);
    $.get('/admin/video/templates/read', {path: current}, function(res){
      if(res && res.code===0){ $('#tpl-content').val(res.data.content||''); }
      else { layer.msg((res&&res.msg)||'读取失败',{icon:2}); }
    },'json');
  });
  $('#tpl-save').on('click', function(){
    if(!current){ layer.msg('请选择文件'); return; }
    layer.confirm('确认保存并覆盖主题文件？保存前会自动备份。', function(i){
      layer.close(i);
      $.post('/admin/video/templates/save', {path: current, content: $('#tpl-content').val()}, function(res){
        layer.msg((res&&res.msg)||'完成', {icon:(res&&res.code===0)?1:2});
      },'json');
    });
  });
  $('#tpl-backup').on('click', function(){
    if(!current){ layer.msg('请选择文件'); return; }
    $.post('/admin/video/templates/backup', {path: current}, function(res){
      layer.msg((res&&res.msg)||'完成', {icon:(res&&res.code===0)?1:2});
    },'json');
  });
  $('#tpl-rollback').on('click', function(){
    if(!current){ layer.msg('请选择文件'); return; }
    layer.confirm('回滚到最近一次备份？', function(i){
      layer.close(i);
      $.post('/admin/video/templates/rollback', {path: current}, function(res){
        if(res && res.code===0){
          $.get('/admin/video/templates/read', {path: current}, function(r){
            if(r && r.code===0){ $('#tpl-content').val(r.data.content||''); }
          },'json');
        }
        layer.msg((res&&res.msg)||'完成', {icon:(res&&res.code===0)?1:2});
      },'json');
    });
  });
});
</script>
</body>
</html>
