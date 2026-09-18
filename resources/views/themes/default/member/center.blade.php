@extends('themes.default.layout')
@section('content')
    <h1>{{ $member->name }} 的会员中心</h1>
    <p class="muted">积分：{{ $member->points }}</p>
    @if(isset($invites) && $invites->isNotEmpty())
        <p>邀请码：
            @foreach($invites as $row)
                <code>{{ $row->code }}</code>
                @if((int)$row->status === 1)（未用）@else（已用）@endif
            @endforeach
        </p>
    @endif
    <form method="post" action="{{ url('/member/invite/generate') }}">
        @csrf
        <button type="submit">生成邀请码</button>
    </form>
    <p>
        <a href="{{ url('/member/activity') }}">用户活动</a>
        · <a href="{{ url('/member/favorites') }}">我的收藏</a>
        · <a href="{{ url('/member/history') }}">观看历史</a>
        · <a href="{{ url('/member/inbox') }}">站内信</a>
        @includeIf('mall::member')
        @includeIf('pay::member')
        @includeIf('coupon::member')
    </p>
    <h2>卡密充值</h2>
    <form method="post" action="{{ url('/member/redeem') }}">
        @csrf
        <input name="code" placeholder="输入积分卡密" required>
        <button type="submit">兑换</button>
    </form>
    <h2>修改密码</h2>
    <form method="post" action="{{ url('/member/password') }}">
        @csrf
        <p><input type="password" name="old_password" placeholder="原密码" required></p>
        <p><input type="password" name="password" placeholder="新密码" required></p>
        <p><button type="submit">保存密码</button></p>
    </form>
    <form method="post" action="{{ url('/member/logout') }}">@csrf<button type="submit">退出</button></form>
@endsection
