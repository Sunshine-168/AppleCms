@extends('layouts.front')

@section('title', '用户注册')

@section('content')
@php
    $verifyMode = (($userConfig['reg_phone_sms'] ?? '0') === '1') ? 'phone' : (((($userConfig['reg_email_sms'] ?? '0') === '1')) ? 'email' : '');
    $verifyLabel = $verifyMode === 'phone' ? '手机号' : '邮箱';
@endphp
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">用户注册</div>
            <div class="card-body">
                @if($verifyMode !== '')
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title">发送{{ $verifyLabel }}验证码</h5>
                            <form method="post" action="{{ route('user.reg_msg') }}">
                                @csrf
                                <input type="hidden" name="ac" value="{{ $verifyMode }}">
                                <div class="mb-3">
                                    <input type="text" class="form-control" name="to" value="{{ $param['to'] ?? '' }}" placeholder="请输入{{ $verifyLabel }}">
                                </div>
                                <button type="submit" class="btn btn-outline-primary">发送验证码</button>
                            </form>
                        </div>
                    </div>
                @endif
                <form method="post" action="{{ route('user.reg') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">用户名</label>
                        <input type="text" class="form-control" name="user_name" value="{{ $param['user_name'] ?? '' }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">密码</label>
                        <input type="password" class="form-control" name="user_pwd">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">确认密码</label>
                        <input type="password" class="form-control" name="user_pwd2">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">邀请码</label>
                        <input type="text" class="form-control" name="uid" value="{{ $param['uid'] ?? '' }}">
                    </div>
                    @if($verifyMode !== '')
                        <input type="hidden" name="ac" value="{{ $verifyMode }}">
                        <div class="mb-3">
                            <label class="form-label">{{ $verifyLabel }}</label>
                            <input type="text" class="form-control" name="to" value="{{ $param['to'] ?? '' }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">验证码</label>
                            <input type="text" class="form-control" name="code">
                        </div>
                    @endif
                    <button type="submit" class="btn btn-primary w-100">注册</button>
                </form>
            </div>
            <div class="card-footer text-center">
                <a href="{{ route('login') }}" class="text-decoration-none">已有账号？立即登录</a>
            </div>
        </div>
    </div>
</div>
@endsection
