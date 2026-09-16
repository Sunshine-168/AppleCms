<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>{{ conf('name') }} - 内容接入</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="stylesheet" href="{{ asset('static/admin/layui/css/layui.css') }}">
</head>
<body>
<div class="layui-fluid" style="padding:16px;">
  <div class="layui-card">
    <div class="layui-card-header">内容接入</div>
    <div class="layui-card-body">
      <form class="layui-form" lay-filter="site-form" style="max-width:720px;">
        <div class="layui-form-item">
          <label class="layui-form-label">站外入库密钥</label>
          <div class="layui-input-block">
            <input type="text" name="inbound_key" value="{{ $site['inbound_key'] ?? '' }}" class="layui-input" placeholder="POST /api.php/receive/vod">
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">地区词库</label>
          <div class="layui-input-block">
            <textarea name="collect_areawords" class="layui-textarea" placeholder="每行 from=to 或 from,to，如 大陆=中国">{{ $site['collect_areawords'] ?? '' }}</textarea>
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">语言词库</label>
          <div class="layui-input-block">
            <textarea name="collect_langwords" class="layui-textarea" placeholder="每行 from=to 或 from,to">{{ $site['collect_langwords'] ?? '' }}</textarea>
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">先入临时表</label>
          <div class="layui-input-block">
            <select name="collect_to_temp">
              <option value="0" @selected(($site['collect_to_temp'] ?? '0')==='0')>直接入库</option>
              <option value="1" @selected(($site['collect_to_temp'] ?? '0')==='1')>写入临时表再审核转入</option>
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
