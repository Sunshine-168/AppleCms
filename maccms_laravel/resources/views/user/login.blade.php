@extends('layouts.front')

@section('title', '用户登录')

@section('content')
@php
    $loginVerify = (string) config('maccms.user.login_verify', '0') === '1';
    $connect = (array) config('maccms.connect', []);
    $oauthItems = [
        'qq' => ['label' => 'QQ登录'],
        'weixin' => ['label' => '微信登录'],
    ];
@endphp
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">用户登录</div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form action="{{ url('/user/login') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="user_name" class="form-label">用户名/邮箱</label>
                        <input type="text" class="form-control" id="user_name" name="user_name" value="{{ old('user_name') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="user_pwd" class="form-label">密码</label>
                        <input type="password" class="form-control" id="user_pwd" name="user_pwd" required>
                    </div>
                    @if($loginVerify)
                        <div class="mb-3">
                            <label for="verify" class="form-label">验证码</label>
                            <div class="d-flex gap-2">
                                <input type="text" class="form-control" id="verify" name="verify" required>
                                <img
                                    src="{{ route('verify.index') }}"
                                    alt="verify"
                                    style="width: 120px; height: 40px; cursor: pointer;"
                                    onclick="this.src='{{ route('verify.index') }}?t=' + Date.now()"
                                >
                            </div>
                        </div>
                    @endif
                    <button type="submit" class="btn btn-primary w-100">登录</button>
                </form>
                @if(collect($oauthItems)->contains(fn ($item, $key) => (string) data_get($connect, $key . '.status', '0') === '1'))
                    <div class="mt-4 pt-3 border-top">
                        <div class="text-muted small mb-2">第三方登录</div>
                        <div class="d-flex gap-2 flex-wrap">
                            @foreach($oauthItems as $key => $item)
                                @if((string) data_get($connect, $key . '.status', '0') === '1')
                                    <a href="{{ route('user.oauth', ['type' => $key]) }}" class="btn btn-outline-secondary">{{ $item['label'] }}</a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif
                <div class="d-flex justify-content-between mt-3">
                    <a href="{{ route('user.reg') }}" class="text-decoration-none">注册账号</a>
                    <a href="{{ route('user.findpass') }}" class="text-decoration-none">安全问题找回</a>
                    <a href="{{ route('user.findpass_msg') }}" class="text-decoration-none">验证码找回</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
