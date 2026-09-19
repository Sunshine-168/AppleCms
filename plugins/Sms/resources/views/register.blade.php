<div class="auth-sms">
    <label class="auth-field">
        <span>手机号</span>
        <input name="phone" type="tel" inputmode="numeric" placeholder="11 位手机号" value="{{ old('phone') }}" required maxlength="20">
    </label>
    <div class="auth-field auth-sms-code">
        <span>短信验证码</span>
        <div class="auth-sms-row">
            <input name="sms_code" inputmode="numeric" placeholder="6 位验证码" value="{{ old('sms_code') }}" required maxlength="8">
            <button type="button" class="btn-ghost" id="sms-send-btn">发送验证码</button>
        </div>
    </div>
</div>
<script>
(function () {
    var btn = document.getElementById('sms-send-btn');
    if (!btn) return;
    var csrf = document.querySelector('meta[name="csrf-token"]');
    var cool = 0;
    var timer = null;
    function tick() {
        if (cool <= 0) {
            btn.disabled = false;
            btn.textContent = '发送验证码';
            return;
        }
        btn.textContent = cool + 's 后重发';
        cool -= 1;
        timer = setTimeout(tick, 1000);
    }
    btn.addEventListener('click', function () {
        if (btn.disabled) return;
        var phone = (document.querySelector('input[name=phone]') || {}).value || '';
        if (!phone) {
            alert('请先填写手机号');
            return;
        }
        btn.disabled = true;
        fetch(@json(url('/sms/send')), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf ? csrf.content : '',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({phone: phone, scene: 'register'})
        }).then(function (r) { return r.json(); }).then(function (res) {
            alert((res && res.msg) || '完成');
            if (res && (res.code === 1 || res.ok === true || res.status === 'ok')) {
                cool = 60;
                tick();
            } else {
                btn.disabled = false;
            }
        }).catch(function () {
            alert('发送失败');
            btn.disabled = false;
        });
    });
})();
</script>
