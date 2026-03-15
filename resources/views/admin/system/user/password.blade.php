<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{conf('name')}}</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('static/admin/layui/css/layui.css') }}" media="all">
    <link rel="stylesheet" href="{{ asset('static/admin/style/admin.css') }}" media="all">
</head>
<body>
<div class="layui-fluid">
    <div class="layui-card">
        <div class="layui-card-body">
            <form class="layui-form" lay-filter="pwd-form" style="max-width: 560px;">
                <div class="layui-form-item">
                    <label class="layui-form-label">当前密码</label>
                    <div class="layui-input-block">
                        <input type="password" name="current_password" required lay-verify="required" placeholder="请输入当前密码" autocomplete="off" class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">新密码</label>
                    <div class="layui-input-block">
                        <input type="password" name="new_password" required lay-verify="required" placeholder="请输入新密码" autocomplete="off" class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">确认新密码</label>
                    <div class="layui-input-block">
                        <input type="password" name="confirm_password" required lay-verify="required" placeholder="请再次输入新密码" autocomplete="off" class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <div class="layui-input-block">
                        <button class="layui-btn layui-btn-danger" lay-submit lay-filter="pwd-submit">保存</button>
                        <button type="reset" class="layui-btn layui-btn-primary">重置</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="{{ asset('static/admin/layui/layui.js') }}"></script>
<script>
    layui.use(['form', 'layer'], function () {
        var $ = layui.$;
        var form = layui.form;
        var layer = layui.layer;

        var csrfToken = $('meta[name=csrf-token]').attr('content');
        if (csrfToken) {
            $.ajaxSetup({headers: {'X-CSRF-TOKEN': csrfToken}});
        }

        form.on('submit(pwd-submit)', function (obj) {
            var data = obj.field || {};

            var currentPassword = $.trim(data.current_password || '');
            var newPassword = $.trim(data.new_password || '');
            var confirmPassword = $.trim(data.confirm_password || '');

            if (!currentPassword) {
                layer.msg('请输入当前密码');
                return false;
            }
            if (!newPassword) {
                layer.msg('请输入新密码');
                return false;
            }
            if (newPassword.length < 6) {
                layer.msg('新密码至少6位');
                return false;
            }
            if (newPassword !== confirmPassword) {
                layer.msg('两次新密码不一致');
                return false;
            }

            var idx = layer.load(1);
            $.post('/admin/set/user/password', data, function (res) {
                layer.close(idx);
                if (!res || res.code !== 0) {
                    layer.msg(res && res.msg ? res.msg : '修改失败');
                    return;
                }
                layer.msg('修改成功', {icon: 1});
                $('input[name=current_password]').val('');
                $('input[name=new_password]').val('');
                $('input[name=confirm_password]').val('');
            }, 'json').fail(function () {
                layer.close(idx);
                layer.msg('请求失败');
            });

            return false;
        });
    });
</script>
</body>
</html>
