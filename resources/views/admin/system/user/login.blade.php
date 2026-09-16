<!DOCTYPE html>
<html lang="{{ \App\Support\AdminUi::htmlLang() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ admin_t('auth.login_title') }} - {{ conf('name') ?: '苹果v12' }}</title>
    <link rel="stylesheet" href="{{ asset('css/admin-login.css') }}?v={{ @filemtime(public_path('css/admin-login.css')) ?: '1' }}">
</head>
<body class="mac-login">
@php $cap = \App\Support\Captcha::generate(); @endphp
<div class="mac-login-stage">
    <div class="mac-login-hero">
        <p class="mac-login-hello">{{ admin_t('auth.hello', ['name' => conf('flag') ?: '苹果v12']) }}</p>
        <p class="mac-login-hello-sub">{{ admin_t('auth.sub') }}</p>
        <ul class="mac-login-pitch">
            <li>{{ admin_t('auth.pitch_1') }}</li>
            <li>{{ admin_t('auth.pitch_2') }}</li>
            <li>{{ admin_t('auth.pitch_3') }}</li>
        </ul>
    </div>
    <div class="mac-login-card">
        <h1>{{ admin_t('auth.system') }}</h1>
        <form id="loginForm" method="post" action="/api/admin/login" data-fail="{{ admin_t('auth.fail') }}" data-network="{{ admin_t('auth.network') }}">
            <div class="mac-field">
                <label for="username">{{ admin_t('auth.username') }}</label>
                <div class="mac-control">
                    <svg class="mac-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="12" cy="8" r="3.2"/><path d="M5.5 19c1.4-3 3.8-4.5 6.5-4.5s5.1 1.5 6.5 4.5"/>
                    </svg>
                    <input type="text" id="username" name="username" required autofocus autocomplete="username" maxlength="32">
                </div>
            </div>
            <div class="mac-field">
                <label for="password">{{ admin_t('auth.password') }}</label>
                <div class="mac-control">
                    <svg class="mac-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/>
                    </svg>
                    <input type="password" id="password" name="password" required autocomplete="current-password" maxlength="32">
                </div>
            </div>
            <div class="mac-field">
                <label for="captcha">{{ admin_t('auth.captcha') }}</label>
                <div class="mac-captcha">
                    <div class="mac-control">
                        <svg class="mac-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M12 3 20 7v5c0 5-3.4 8.4-8 9.5C7.4 20.4 4 17 4 12V7l8-4z"/>
                        </svg>
                        <input type="text" id="captcha" name="captcha" required inputmode="numeric" autocomplete="off" maxlength="4">
                    </div>
                    <button type="button" class="mac-captcha-q" id="captchaLabel" title="{{ admin_t('auth.captcha_hint') }}">{{ $cap['question'] }}</button>
                </div>
            </div>
            <label class="mac-remember"><input type="checkbox" name="remember" value="1"> {{ admin_t('auth.remember') }}</label>
            <p class="mac-error" id="loginError" hidden></p>
            <button type="submit" class="mac-submit" id="loginBtn">{{ admin_t('auth.submit') }}</button>
        </form>
        <p class="login-links ui-switch-login">
            @foreach(\App\Support\AdminUi::options() as $code => $label)
                <a href="{{ request()->fullUrlWithQuery(['ui' => $code]) }}" class="{{ \App\Support\AdminUi::current() === $code ? 'is-on' : '' }}">{{ $label }}</a>
            @endforeach
        </p>
        <p class="mac-copy">© {{ date('Y') }} {{ conf('name') ?: '苹果v12' }}</p>
        <div class="mac-declare">
            <strong>{{ admin_t('auth.disclaimer') }}</strong>
            <p>{{ admin_t('auth.disclaimer_body') }}</p>
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
                location.href = '/admin/welcome';
                return;
            }
            showError((json && json.msg) || form.getAttribute('data-fail') || '');
            refreshCaptcha();
            input.focus();
        }).catch(function () {
            showError(form.getAttribute('data-network') || '');
            refreshCaptcha();
        }).finally(function () {
            btn.disabled = false;
        });
    });
})();
</script>
</body>
</html>
