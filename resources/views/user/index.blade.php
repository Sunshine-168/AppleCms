@extends('user.layout')

@section('title', '用户中心')

@section('user_content')
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex align-items-center">
            <div class="me-3">
                <img
                    src="{{ str_starts_with((string) $user->user_portrait, 'http') ? $user->user_portrait : ($user->user_portrait ? asset($user->user_portrait) : asset('static_new/images/touxiang.png')) }}"
                    alt="portrait"
                    class="rounded-circle border"
                    style="width: 84px; height: 84px; object-fit: cover;"
                >
            </div>
            <div>
                <h4 class="mb-1">{{ $user->user_nick_name ?: $user->user_name }}</h4>
                <div class="text-muted">账号：{{ $user->user_name }}</div>
                <div class="text-muted">会员组：{{ $user->group->group_name ?? '游客' }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">账户信息</div>
            <div class="card-body">
                <p class="mb-2"><strong>积分：</strong>{{ $user->user_points }}</p>
                <p class="mb-2"><strong>冻结积分：</strong>{{ $user->user_points_froze ?? 0 }}</p>
                <p class="mb-2"><strong>到期时间：</strong>{{ !empty($user->user_end_time) ? date('Y-m-d H:i:s', $user->user_end_time) : '未设置' }}</p>
                <p class="mb-0"><strong>累计登录：</strong>{{ $user->user_login_num }} 次</p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">最近登录</div>
            <div class="card-body">
                <p class="mb-2"><strong>本次登录时间：</strong>{{ !empty($user->user_login_time) ? date('Y-m-d H:i:s', $user->user_login_time) : '-' }}</p>
                <p class="mb-2"><strong>本次登录 IP：</strong>{{ !empty($user->user_login_ip) ? long2ip((int) $user->user_login_ip) : '-' }}</p>
                <p class="mb-2"><strong>上次登录时间：</strong>{{ !empty($user->user_last_login_time) ? date('Y-m-d H:i:s', $user->user_last_login_time) : '-' }}</p>
                <p class="mb-0"><strong>上次登录 IP：</strong>{{ !empty($user->user_last_login_ip) ? long2ip((int) $user->user_last_login_ip) : '-' }}</p>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">快捷入口</div>
    <div class="card-body d-flex flex-wrap gap-2">
        <a href="{{ route('user.info') }}" class="btn btn-outline-primary">修改资料</a>
        <a href="{{ route('user.portrait') }}" class="btn btn-outline-primary">上传头像</a>
        <a href="{{ route('user.buy') }}" class="btn btn-outline-primary">在线充值</a>
        <a href="{{ route('user.orders') }}" class="btn btn-outline-primary">订单记录</a>
        <a href="{{ route('user.plog') }}" class="btn btn-outline-primary">积分记录</a>
        <a href="{{ route('logout') }}" class="btn btn-outline-danger">退出登录</a>
    </div>
</div>
@endsection
