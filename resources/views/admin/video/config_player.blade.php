<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>{{ conf('name') }} - 播放器参数</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="stylesheet" href="{{ asset('static/admin/layui/css/layui.css') }}">
</head>
<body>
<div class="layui-fluid" style="padding:16px;">
  <div class="layui-card">
    <div class="layui-card-header">播放器参数</div>
    <div class="layui-card-body">
      <form class="layui-form" lay-filter="site-form" style="max-width:720px;">
        <div class="layui-form-item">
          <label class="layui-form-label">缓冲秒数</label>
          <div class="layui-input-block">
            <input type="number" name="play_buffer" value="{{ $site['play_buffer'] ?? 5 }}" class="layui-input">
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">地址编码</label>
          <div class="layui-input-block">
            <select name="play_encrypt">
              <option value="0" @selected(($site['play_encrypt'] ?? '0')==='0')>明文</option>
              <option value="1" @selected(($site['play_encrypt'] ?? '0')==='1')>前端 Base64</option>
            </select>
          </div>
        </div>
        <div class="layui-form-item">
          <div class="layui-input-block">
            <button type="button" class="layui-btn" id="site-save">保存</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
<script src="{{ asset('static/admin/layui/layui.js') }}"></script>
<script>
layui.use(['form','layer'], function(){
  var $ = layui.$, form = layui.form, layer = layui.layer;
  var csrf = $('meta[name=csrf-token]').attr('content');
  if (csrf) { $.ajaxSetup({headers:{'X-CSRF-TOKEN': csrf}}); }
  form.render();
  $('#site-save').on('click', function(){
    var data = {};
    $('form[lay-filter=site-form]').serializeArray().forEach(function(it){ data[it.name]=it.value; });
    $.post('/admin/video/settings', data, function(res){
      layer.msg((res && res.msg) ? res.msg : '完成', {icon: (res && res.code===0)?1:2});
    }, 'json');
  });
});
</script>
</body>
</html>
