<p>
    <input name="phone" placeholder="手机号" value="{{ old('phone') }}" required>
    <input name="sms_code" placeholder="短信验证码" value="{{ old('sms_code') }}" required>
    <button type="button" id="sms-send-btn">发送验证码</button>
</p>
<script>
(function () {
    var btn = document.getElementById('sms-send-btn');
    if (!btn) return;
    var csrf = document.querySelector('meta[name="csrf-token"]');
    btn.addEventListener('click', function () {
        var phone = (document.querySelector('input[name=phone]') || {}).value || '';
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
        }).catch(function () { alert('发送失败'); });
    });
})();
</script>
