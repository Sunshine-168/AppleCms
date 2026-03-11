<div class="p-3">
    @php
        $loginVerify = (string) config('maccms.user.login_verify', '0') === '1';
        $connect = (array) config('maccms.connect', []);
        $oauthItems = [
            'qq' => ['label' => 'QQ登录'],
            'weixin' => ['label' => '微信登录'],
        ];
    @endphp
    <form class="mac_login_form">
        @csrf
        <div class="mb-3">
            <label class="form-label">用户名</label>
            <input type="text" class="form-control" name="user_name">
        </div>
        <div class="mb-3">
            <label class="form-label">密码</label>
            <input type="password" class="form-control" name="user_pwd">
        </div>
        @if($loginVerify)
            <div class="mb-3">
                <label class="form-label">验证码</label>
                <div class="d-flex gap-2">
                    <input type="text" class="form-control" name="verify">
                    <img
                        src="{{ route('verify.index') }}"
                        alt="verify"
                        style="width: 120px; height: 40px; cursor: pointer;"
                        onclick="this.src='{{ route('verify.index') }}?t=' + Date.now()"
                    >
                </div>
            </div>
        @endif
        <button type="button" class="btn btn-primary login_form_submit">登录</button>
    </form>
    @if(collect($oauthItems)->contains(fn ($item, $key) => (string) data_get($connect, $key . '.status', '0') === '1'))
        <div class="mt-3 pt-3 border-top">
            <div class="text-muted small mb-2">第三方登录</div>
            <div class="d-flex gap-2 flex-wrap">
                @foreach($oauthItems as $key => $item)
                    @if((string) data_get($connect, $key . '.status', '0') === '1')
                        <a href="{{ route('user.oauth', ['type' => $key]) }}" class="btn btn-outline-secondary btn-sm">{{ $item['label'] }}</a>
                    @endif
                @endforeach
            </div>
        </div>
    @endif
</div>
