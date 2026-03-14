

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>登入 - layuiAdmin</title>
  <meta name="renderer" content="webkit">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
  <base href="/views/user/">
  <link rel="stylesheet" href="/layuiadmin/layui/css/layui.css" media="all">
  <link rel="stylesheet" href="/layuiadmin/style/admin.css" media="all">
  <link rel="stylesheet" href="/layuiadmin/style/login.css" media="all">
</head>
<body>

  <div class="layadmin-user-login layadmin-user-display-show" id="LAY-user-login" style="display: none;">

    <div class="layadmin-user-login-main">
      <div class="layadmin-user-login-box layadmin-user-login-header">
        <h2>HKCMS</h2>
        <p>HKCMS 官方出品的单页面后台管理模板系统</p>
      </div>
      <div class="layadmin-user-login-box layadmin-user-login-body layui-form">
        <div class="layui-form-item">
          <label class="layadmin-user-login-icon layui-icon layui-icon-username" for="LAY-user-login-username"></label>
          <input type="text" name="username" id="LAY-user-login-username" lay-verify="required" placeholder="用户名" class="layui-input">
        </div>
        <div class="layui-form-item">
          <label class="layadmin-user-login-icon layui-icon layui-icon-password" for="LAY-user-login-password"></label>
          <input type="password" name="password" id="LAY-user-login-password" lay-verify="required" placeholder="密码" class="layui-input">
        </div>
        <div class="layui-form-item">
          <label class="layadmin-user-login-icon layui-icon layui-icon-vercode" for="LAY-user-login-vercode"></label>
          <input type="text" name="vscode" id="LAY-user-login-vercode" placeholder="谷歌验证码（可选）" class="layui-input">
        </div>
        <div class="layui-form-item" style="margin-bottom: 20px;">
          <input type="checkbox" name="remember" lay-skin="primary" title="记住密码">
        </div>

        <div class="layui-form-item">
          <button class="layui-btn layui-btn-fluid" lay-submit lay-filter="LAY-user-login-submit">登 入</button>
        </div>
      </div>
    </div>

    <div class="layui-trans layadmin-user-login-footer"></div>

  </div>

  <script src="/layuiadmin/layui/layui.js"></script>
  <script>
  layui.config({
    base: '/layuiadmin/'
  }).extend({
    index: 'lib/index'
  }).use(['index', 'user'], function(){
    var $ = layui.$
    ,setter = layui.setter
    ,admin = layui.admin
    ,form = layui.form
    ,router = layui.router()
    ,search = router.search;

    form.render();
    
    if (layui.view && typeof layui.view.popup === 'function') {
      layui.view.error = function (content, options) {
        var text = String(content || '');
        text = text.replace(/<br\s*\/?>/gi, '\n').replace(/<[^>]*>/g, '').replace(/^\s*Error[:：]\s*/i, '');
        var lines = text.split('\n').map(function (s) { return s.trim(); }).filter(function (s) { return s && !/^URL[:：]/i.test(s); });
        return layer.msg(lines[0] || '操作失败', $.extend({
          offset: 'lb',
          shade: 0,
          anim: 6,
          skin: 'hk-toast',
          icon: 2,
          time: 2000
        }, options));
      };
    }

    form.on('submit(LAY-user-login-submit)', function(obj){
      admin.req({
        type: 'post'
        ,url: '/api/admin/login'
        ,data: obj.field
        ,done: function(res){
          layui.data(setter.tableName, {
            key: setter.request.tokenName
            ,value: res.data.token
          });

          layer.msg('登入成功', {
            offset: 'lb'
            ,icon: 1
            ,skin: 'hk-toast'
            ,time: 1000
          }, function(){
            location.href = '/admin';
          });
        }
      });
    });
    
  });
  </script>
</body>
</html>

<?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views/admin/login.blade.php ENDPATH**/ ?>