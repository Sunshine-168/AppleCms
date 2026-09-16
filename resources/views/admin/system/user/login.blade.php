<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>后台登录 - {{ conf('name') ?: '苹果v12' }}</title>
    <link rel="stylesheet" href="{{ asset('css/admin-login.css') }}?v={{ @filemtime(public_path('css/admin-login.css')) ?: '1' }}">
</head>
<body class="mac-login">
@php $cap = \App\Support\Captcha::generate(); @endphp
<div class="mac-login-stage">
    <div class="mac-login-hero">
        <p class="mac-login-hello">欢迎使用{{ conf('flag') ?: '苹果v12' }}</p>
        <p class="mac-login-hello-sub">影视内容管理后台，登录后即可采集、审核与发布影片。</p>
    </div>
    <div class="mac-login-card">
        <h1>系统管理</h1>
        <form id="loginForm" method="post" action="/api/admin/login">
            <div class="mac-field">
                <label for="username">账号</label>
                <div class="mac-control">
                    <svg class="mac-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="12" cy="8" r="3.2"/><path d="M5.5 19c1.4-3 3.8-4.5 6.5-4.5s5.1 1.5 6.5 4.5"/>
                    </svg>
                    <input type="text" id="username" name="username" required autofocus autocomplete="username" maxlength="32">
                </div>
            </div>
            <div class="mac-field">
                <label for="password">密码</label>
                <div class="mac-control">
                    <svg class="mac-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/>
                    </svg>
                    <input type="password" id="password" name="password" required autocomplete="current-password" maxlength="32">
                </div>
            </div>
            <div class="mac-field">
                <label for="captcha">验证码</label>
                <div class="mac-captcha">
                    <div class="mac-control">
                        <svg class="mac-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M12 3 20 7v5c0 5-3.4 8.4-8 9.5C7.4 20.4 4 17 4 12V7l8-4z"/>
                        </svg>
                        <input type="text" id="captcha" name="captcha" required inputmode="numeric" autocomplete="off" maxlength="4">
                    </div>
                    <button type="button" class="mac-captcha-q" id="captchaLabel" title="点击换一题">{{ $cap['question'] }}</button>
                </div>
            </div>
            <label class="mac-remember"><input type="checkbox" name="remember" value="1"> 记住登录</label>
            <p class="mac-error" id="loginError" hidden></p>
            <button type="submit" class="mac-submit" id="loginBtn">立即登录</button>
        </form>
        <p class="mac-copy">© {{ date('Y') }} {{ conf('name') ?: '苹果v12' }}</p>
        <div class="mac-declare">
            <strong>免责声明</strong>
            <p>本程序开源且永久免费，无任何内置数据。请在遵守当地法律法规的前提下使用；用户发布的内容由使用者自行负责。自由！平等！分享！开源！</p>
        </div>
    </div>
</div>
<script>
(function () {
    var form = document.getElementById('loginForm');
    var label = document.getElementById('captchaLabel');
    var input = document.getElementById('captcha');
    var err = document.getElementById('loginError');
    var btn = document.getElementById('loginBtn');

    function refreshCaptcha() {
        fetch('/admin/captcha', {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        }).then(function (res) { return res.json(); }).then(function (json) {
            var question = json && json.data && json.data.question;
            if (question) label.textContent = question;
            input.value = '';
        }).catch(function () {});
    }

    function showError(text) {
        err.hidden = !text;
        err.textContent = text || '';
    }

    label.addEventListener('click', refreshCaptcha);

    form.addEventListener('submit', function (ev) {
        ev.preventDefault();
        showError('');
        btn.disabled = true;
        fetch('/api/admin/login', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams(new FormData(form))
        }).then(function (res) { return res.json(); }).then(function (json) {
            if (json && Number(json.code) === 0) {
                location.href = '/admin';
                return;
            }
            showError((json && json.msg) || '登录失败');
            refreshCaptcha();
            input.focus();
        }).catch(function () {
            showError('网络异常，请重试');
            refreshCaptcha();
        }).finally(function () {
            btn.disabled = false;
        });
    });
})();
</script>
</body>
</html>
