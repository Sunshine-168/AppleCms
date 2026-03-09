@extends('layouts.front')

@section('title', '验证码找回密码')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <h3 class="mb-4 text-center">验证码找回密码</h3>
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">第一步：发送验证码</h5>
                <form method="post" action="{{ route('user.findpass_msg') }}">
                    @csrf
                    <div class="mb-3">
                        <select class="form-select" name="ac">
                            <option value="email" @selected(($param['ac'] ?? 'email') === 'email')>邮箱</option>
                            <option value="phone" @selected(($param['ac'] ?? '') === 'phone')>手机</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <input class="form-control" name="to" value="{{ $param['to'] ?? '' }}" placeholder="邮箱或手机号">
                    </div>
                    <button type="submit" class="btn btn-outline-primary">发送验证码</button>
                </form>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">第二步：重置密码</h5>
                <form method="post" action="{{ route('user.findpass_reset') }}">
                    @csrf
                    <div class="mb-3">
                        <select class="form-select" name="ac">
                            <option value="email" @selected(($param['ac'] ?? 'email') === 'email')>邮箱</option>
                            <option value="phone" @selected(($param['ac'] ?? '') === 'phone')>手机</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <input class="form-control" name="to" value="{{ $param['to'] ?? '' }}" placeholder="邮箱或手机号">
                    </div>
                    <div class="mb-3">
                        <input class="form-control" name="code" placeholder="验证码">
                    </div>
                    <div class="mb-3">
                        <input type="password" class="form-control" name="user_pwd" placeholder="新密码">
                    </div>
                    <div class="mb-3">
                        <input type="password" class="form-control" name="user_pwd2" placeholder="确认新密码">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">重置密码</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
