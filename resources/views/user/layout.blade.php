@extends('layouts.front')

@section('content')
<div class="row">
    <div class="col-md-3 mb-4">
        @php
            $sideUser = auth()->user();
            $portrait = $sideUser
                ? (str_starts_with((string) $sideUser->user_portrait, 'http')
                    ? $sideUser->user_portrait
                    : ($sideUser->user_portrait ? asset($sideUser->user_portrait) : asset('static_new/images/touxiang.png')))
                : asset('static_new/images/touxiang.png');
        @endphp
        @if($sideUser)
            <div class="card mb-3">
                <div class="card-body text-center">
                    <img src="{{ $portrait }}" alt="portrait" class="rounded-circle border mb-3" style="width: 72px; height: 72px; object-fit: cover;">
                    <div class="fw-bold">{{ $sideUser->user_nick_name ?: $sideUser->user_name }}</div>
                    <div class="text-muted small">会员组：{{ $sideUser->group->group_name ?? '游客' }}</div>
                    <div class="text-muted small">积分：{{ $sideUser->user_points }}</div>
                </div>
            </div>
        @endif
        <div class="list-group">
            <a href="{{ route('user.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('user.index') ? 'active' : '' }}">用户中心</a>
            <a href="{{ route('user.info') }}" class="list-group-item list-group-item-action {{ request()->routeIs('user.info') ? 'active' : '' }}">资料修改</a>
            <a href="{{ route('user.portrait') }}" class="list-group-item list-group-item-action {{ request()->routeIs('user.portrait') ? 'active' : '' }}">头像上传</a>
            <a href="{{ route('user.buy') }}" class="list-group-item list-group-item-action {{ request()->routeIs('user.buy') ? 'active' : '' }}">在线充值</a>
            <a href="{{ route('user.orders') }}" class="list-group-item list-group-item-action {{ request()->routeIs('user.orders') ? 'active' : '' }}">订单记录</a>
            <a href="{{ route('user.cards') }}" class="list-group-item list-group-item-action {{ request()->routeIs('user.cards') ? 'active' : '' }}">充值卡</a>
            <a href="{{ route('user.plog') }}" class="list-group-item list-group-item-action {{ request()->routeIs('user.plog') ? 'active' : '' }}">积分记录</a>
            <a href="{{ route('user.cash') }}" class="list-group-item list-group-item-action {{ request()->routeIs('user.cash') ? 'active' : '' }}">提现记录</a>
            <a href="{{ route('user.reward') }}" class="list-group-item list-group-item-action {{ request()->routeIs('user.reward') ? 'active' : '' }}">推广记录</a>
            <a href="{{ route('user.upgrade') }}" class="list-group-item list-group-item-action {{ request()->routeIs('user.upgrade') ? 'active' : '' }}">会员升级</a>
            <a href="{{ route('user.plays') }}" class="list-group-item list-group-item-action {{ request()->routeIs('user.plays') ? 'active' : '' }}">播放记录</a>
            <a href="{{ route('user.downs') }}" class="list-group-item list-group-item-action {{ request()->routeIs('user.downs') ? 'active' : '' }}">下载记录</a>
            <a href="{{ route('user.favs') }}" class="list-group-item list-group-item-action {{ request()->routeIs('user.favs') ? 'active' : '' }}">收藏记录</a>
            <a href="{{ route('user.comment') }}" class="list-group-item list-group-item-action {{ request()->routeIs('user.comment') ? 'active' : '' }}">我的评论</a>
            <a href="{{ route('user.gbook') }}" class="list-group-item list-group-item-action {{ request()->routeIs('user.gbook') ? 'active' : '' }}">我的留言</a>
            <a href="{{ route('user.popedom') }}" class="list-group-item list-group-item-action {{ request()->routeIs('user.popedom') ? 'active' : '' }}">权限查看</a>
            <a href="{{ route('logout') }}" class="list-group-item list-group-item-action text-danger">退出登录</a>
        </div>
    </div>
    <div class="col-md-9">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">{{ $errors->first('msg') ?: $errors->first() }}</div>
        @endif
        @yield('user_content')
    </div>
</div>
@endsection
