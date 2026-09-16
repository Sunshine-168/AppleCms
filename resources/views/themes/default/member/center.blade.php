@extends('themes.default.layout')
@section('content')
    <h1>{{ $member->name }} 的会员中心</h1>
    @if(session('status'))<p>{{ session('status') }}</p>@endif
    @if($errors->any())<p class="muted">{{ $errors->first() }}</p>@endif
    <p class="muted">积分：{{ $member->points }}</p>
    <p>
        <a href="{{ url('/member/favorites') }}">我的收藏</a>
        · <a href="{{ url('/member/history') }}">观看历史</a>
        · <a href="{{ url('/member/inbox') }}">站内信</a>
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
