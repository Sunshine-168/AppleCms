<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>{{ conf('name') }} - 邮件设置</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="stylesheet" href="{{ asset('static/admin/layui/css/layui.css') }}">
</head>
<body>
<div class="layui-fluid" style="padding:16px;">
  <div class="layui-card">
    <div class="layui-card-header">邮件设置</div>
    <div class="layui-card-body">
      <form class="layui-form" lay-filter="site-form" style="max-width:720px;">
        <div class="layui-form-item">
          <label class="layui-form-label">SMTP主机</label>
          <div class="layui-input-block">
            <input type="text" name="smtp_host" value="{{ $site['smtp_host'] ?? '' }}" class="layui-input">
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">SMTP端口</label>
          <div class="layui-input-block">
            <input type="number" name="smtp_port" value="{{ $site['smtp_port'] ?? 465 }}" class="layui-input">
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">SMTP账号</label>
          <div class="layui-input-block">
            <input type="text" name="smtp_user" value="{{ $site['smtp_user'] ?? '' }}" class="layui-input">
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">SMTP密码</label>
          <div class="layui-input-block">
            <input type="password" name="smtp_pass" value="{{ $site['smtp_pass'] ?? '' }}" class="layui-input">
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">发件人</label>
          <div class="layui-input-block">
            <input type="text" name="smtp_from" value="{{ $site['smtp_from'] ?? '' }}" class="layui-input" placeholder="noreply@example.com">
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">测试邮箱</label>
          <div class="layui-input-block">
            <input type="email" id="test-mail-to" class="layui-input" placeholder="收件邮箱">
          </div>
        </div>
        <div class="layui-form-item">
          <div class="layui-input-block">
            <button type="button" class="layui-btn" id="site-save">保存</button>
            <button type="button" class="layui-btn layui-btn-primary" id="site-test-mail">发送测试邮件</button>
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
  $('#site-test-mail').on('click', function(){
    var to = $.trim($('#test-mail-to').val() || '');
    if (!to) { layer.msg('请填写收件邮箱', {icon:2}); return; }
    $.post('/admin/video/settings/test-mail', {to: to}, function(res){
      layer.msg((res && res.msg) ? res.msg : '完成', {icon: (res && res.code===0)?1:2});
    }, 'json');
  });
});
</script>
</body>
</html>
