<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>{{ conf('name') }} - 站点设置</title>
  <meta name="renderer" content="webkit">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="stylesheet" href="{{ asset('static/admin/layui/css/layui.css') }}">
  <link rel="stylesheet" href="{{ asset('static/admin/style/admin.css') }}">
</head>
<body>
<div class="layui-fluid">
  <div class="layui-card">
    <div class="layui-card-header">站点设置</div>
    <div class="layui-card-body">
      <form class="layui-form" lay-filter="site-form" style="max-width:720px;">
        <div class="layui-form-item">
          <label class="layui-form-label">站点名称</label>
          <div class="layui-input-block">
            <input type="text" name="site_title" value="{{ $site['title'] ?? '' }}" class="layui-input">
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">关键词</label>
          <div class="layui-input-block">
            <input type="text" name="site_keyword" value="{{ $site['keyword'] ?? '' }}" class="layui-input">
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">描述</label>
          <div class="layui-input-block">
            <textarea name="site_description" class="layui-textarea">{{ $site['description'] ?? '' }}</textarea>
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">全页缓存</label>
          <div class="layui-input-block">
            <select name="html_cache_enabled">
              <option value="0" @selected(!($site['html_cache_enabled'] ?? false))>关闭</option>
              <option value="1" @selected($site['html_cache_enabled'] ?? false)>开启</option>
            </select>
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">缓存秒数</label>
          <div class="layui-input-block">
            <input type="number" name="html_cache_ttl" value="{{ $site['html_cache_ttl'] ?? 3600 }}" class="layui-input">
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
