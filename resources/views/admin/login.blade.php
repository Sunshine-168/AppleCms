<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>登入 - layuiAdmin</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="{{ asset('static/admin/layui/css/layui.css') }}" media="all">
    <link rel="stylesheet" href="{{ asset('static/admin/style/admin.css') }}" media="all">
    <link rel="stylesheet" href="{{ asset('static/admin/style/login.css') }}" media="all">
    <style>
        body {
            font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, "Apple Color Emoji",
                "Segoe UI Emoji";
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            background-color: #040611;
            background-image:
                radial-gradient(1200px 800px at 15% 10%, rgba(99, 102, 241, 0.25), rgba(0, 0, 0, 0) 60%),
                radial-gradient(980px 720px at 85% 18%, rgba(59, 130, 246, 0.16), rgba(0, 0, 0, 0) 62%),
                radial-gradient(1100px 900px at 50% 112%, rgba(236, 72, 153, 0.10), rgba(0, 0, 0, 0) 62%),
                linear-gradient(180deg, #040611, #050a18, #060b20);
            background-attachment: fixed;
        }
        body::before {
            content: "";
            position: fixed;
            inset: 0;
            pointer-events: none;
            background-image:
                radial-gradient(1px 1px at 10% 20%, rgba(255, 255, 255, 0.65), rgba(0, 0, 0, 0)),
                radial-gradient(1px 1px at 20% 80%, rgba(255, 255, 255, 0.55), rgba(0, 0, 0, 0)),
                radial-gradient(1px 1px at 30% 30%, rgba(255, 255, 255, 0.75), rgba(0, 0, 0, 0)),
                radial-gradient(1px 1px at 40% 70%, rgba(255, 255, 255, 0.45), rgba(0, 0, 0, 0)),
                radial-gradient(1px 1px at 55% 15%, rgba(255, 255, 255, 0.60), rgba(0, 0, 0, 0)),
                radial-gradient(1px 1px at 65% 85%, rgba(255, 255, 255, 0.50), rgba(0, 0, 0, 0)),
                radial-gradient(1px 1px at 78% 28%, rgba(255, 255, 255, 0.70), rgba(0, 0, 0, 0)),
                radial-gradient(1px 1px at 88% 72%, rgba(255, 255, 255, 0.45), rgba(0, 0, 0, 0)),
                radial-gradient(circle at 1px 1px, rgba(255, 255, 255, 0.10) 1px, rgba(0, 0, 0, 0) 0);
            background-size: 100% 100%, 100% 100%, 100% 100%, 100% 100%, 100% 100%, 100% 100%, 100% 100%, 100% 100%, 28px 28px;
            opacity: 0.35;
            mix-blend-mode: screen;
            animation: hkStarsTwinkle 9s ease-in-out infinite alternate;
        }
        body::after {
            content: "";
            position: fixed;
            inset: 0;
            pointer-events: none;
            background-image:
                radial-gradient(1100px 720px at 52% 38%, rgba(120, 140, 255, 0.12), rgba(0, 0, 0, 0) 70%),
                radial-gradient(1000px 680px at 46% 46%, rgba(200, 120, 255, 0.08), rgba(0, 0, 0, 0) 72%),
                linear-gradient(120deg, rgba(255, 255, 255, 0) 34%, rgba(210, 220, 255, 0.10) 46%, rgba(255, 255, 255, 0) 62%),
                radial-gradient(1400px 900px at 50% 10%, rgba(0, 0, 0, 0), rgba(0, 0, 0, 0.55));
            filter: blur(0.6px);
            opacity: 0.75;
            animation: hkGalaxyDrift 48s linear infinite;
        }
        @keyframes hkStarsTwinkle {
            from { opacity: 0.45; }
            to { opacity: 0.70; }
        }
        @keyframes hkGalaxyDrift {
            from { transform: translate3d(0, 0, 0); }
            to { transform: translate3d(-4%, 3%, 0); }
        }
        @media (prefers-reduced-motion: reduce) {
            body::before, body::after { animation: none !important; }
        }
        .layadmin-user-login {
            padding: 96px 0;
        }
        .layadmin-user-login-main {
            width: 420px;
            background: rgba(255, 255, 255, 0.96);
            border-radius: 14px;
            border: 1px solid rgba(255, 255, 255, 0.35);
            box-shadow: 0 28px 80px rgba(0, 0, 0, 0.35);
            backdrop-filter: blur(10px);
        }
        .layadmin-user-login-box {
            padding: 28px;
        }
        .layadmin-user-login-header {
            padding-bottom: 6px;
        }
        .layadmin-user-login-header h2 {
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 28px;
            letter-spacing: 0.6px;
            color: #0f172a;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        .layadmin-user-login-header h2::before {
            content: "";
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: linear-gradient(135deg, #2563eb, #a855f7, #22c55e);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }
        .layadmin-user-login-header p {
            color: rgba(15, 23, 42, 0.62);
        }
        .layadmin-user-login-icon {
            width: 42px;
            line-height: 42px;
            color: rgba(15, 23, 42, 0.45);
        }
        .layadmin-user-login-body .layui-form-item .layui-input {
            height: 44px;
            line-height: 44px;
            border-radius: 10px;
            padding-left: 42px;
            border-color: rgba(148, 163, 184, 0.55);
        }
        .layadmin-user-login-body .layui-form-item .layui-input:focus {
            border-color: rgba(37, 99, 235, 0.7);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
        }
        #captcha-img {
            height: 44px;
            border-radius: 10px;
            background: rgba(15, 23, 42, 0.03);
            border: 1px solid rgba(148, 163, 184, 0.55);
        }
        .layadmin-user-login-body .layui-btn.layui-btn-fluid {
            height: 44px;
            border-radius: 10px;
            background: linear-gradient(135deg, #2563eb, #7c3aed, #db2777);
            border: 0;
            box-shadow: 0 14px 30px rgba(37, 99, 235, 0.22);
        }
        .layadmin-user-login-body .layui-btn.layui-btn-fluid:hover {
            filter: brightness(1.02);
        }
        .layadmin-user-login-body .layui-form-item[style*="margin-bottom"] {
            margin-bottom: 18px !important;
        }
        .layadmin-user-login-footer {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 16px;
            padding: 0 16px;
            text-align: center;
            color: rgba(255, 255, 255, 0.62);
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.35);
        }
    </style>
</head>
<body>

<div class="layadmin-user-login layadmin-user-display-show" id="LAY-user-login" style="display: none;">

    <div class="layadmin-user-login-main">
        <div class="layadmin-user-login-box layadmin-user-login-header">
            <h2> {{conf('flag')}} </h2>
            <p>  {{conf('author')}} 官方出品的单页面后台管理模板系统</p>
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
                <div class="layui-row">
                    <div class="layui-col-xs7">
                        <label class="layadmin-user-login-icon layui-icon layui-icon-vercode" for="LAY-user-login-vercode"></label>
                        <input type="text" name="vscode" id="LAY-user-login-vercode" lay-verify="required" placeholder="验证码" class="layui-input">
                    </div>
                    <div class="layui-col-xs5" style="padding-left: 10px;">
                        <img id="captcha-img" src="/admin/captcha" style="width: 100%; cursor: pointer;">
                    </div>
                </div>
            </div>
            <div class="layui-form-item" style="margin-bottom: 20px;">
                <input type="checkbox" name="remember" lay-skin="primary" title="记住密码">
            </div>

            <div class="layui-form-item">
                <button class="layui-btn layui-btn-fluid" lay-submit lay-filter="LAY-user-login-submit">登 入</button>
            </div>

        </div>
    </div>

    <div class="layui-trans layadmin-user-login-footer">
        © {{ date('Y') }} 星河影视管理系统
    </div>


</div>

<script src="{{ asset('static/admin/layui/layui.js') }}"></script>
<script>
    layui.config({
        base: '{{ asset('static/admin') }}/' //静态资源所在路径
    }).extend({
        index: 'lib/index' //主入口模块
    }).use(['index', 'user'], function(){
        var $ = layui.$
            ,setter = layui.setter
            ,admin = layui.admin
            ,form = layui.form
            ,layer = layui.layer
            ,router = layui.router()
            ,search = router.search;

        form.render();

        function refreshCaptcha() {
            $('#captcha-img').attr('src', '/admin/captcha?_=' + Date.now());
        }

        $('#captcha-img').on('click', refreshCaptcha);
        refreshCaptcha();

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

        //提交
        form.on('submit(LAY-user-login-submit)', function(obj){

            //请求登入接口
            admin.req({
                type: 'post'
                ,url: '/api/admin/login'
                ,data: obj.field
                ,done: function(res){
                    refreshCaptcha();

                    //请求成功后，写入 access_token
                    layui.data(setter.tableName, {
                        key: setter.request.tokenName
                        ,value: res.data.token
                    });

                    //登入成功的提示与跳转
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

