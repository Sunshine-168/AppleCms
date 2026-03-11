@extends('user.layout')

@section('title', '绑定账号')

@section('user_content')
<div class="card mb-3">
    <div class="card-header">绑定邮箱/手机</div>
    <div class="card-body">
        <p class="text-muted mb-0">当前邮箱：{{ $user->user_email ?: '未绑定' }}，当前手机：{{ $user->user_phone ?: '未绑定' }}</p>
    </div>
</div>
<div class="card mb-3">
    <div class="card-body">
        <h5 class="card-title">第一步：发送验证码</h5>
        <form method="post" action="{{ route('user.bindmsg') }}">
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
        <h5 class="card-title">第二步：提交绑定</h5>
        <form method="post" action="{{ route('user.bind') }}">
            @csrf
            <div class="mb-3">
                <select class="form-select" name="ac">
                    <option value="email" @selected(($param['ac'] ?? 'email') === 'email')>邮箱</option>
                    <option value="phone" @selected(($param['ac'] ?? '') === 'phone')>手机</option>
                </select>
            </div>
            <div class="mb-3"><input class="form-control" name="to" value="{{ $param['to'] ?? '' }}" placeholder="邮箱或手机号"></div>
            <div class="mb-3"><input class="form-control" name="code" placeholder="验证码"></div>
            <button type="submit" class="btn btn-primary">绑定</button>
        </form>
    </div>
</div>
@endsection
