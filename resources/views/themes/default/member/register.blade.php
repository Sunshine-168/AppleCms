@extends('themes.default.layout')
@section('content')
    <h1>会员注册</h1>
    @if($errors->any())<p class="muted">{{ $errors->first() }}</p>@endif
    <form method="post" action="{{ url('/member/register') }}">
        @csrf
        <p><input name="name" placeholder="昵称" value="{{ old('name') }}" required></p>
        <p><input name="email" type="email" placeholder="邮箱" value="{{ old('email') }}" required></p>
        <p><input name="password" type="password" placeholder="密码" required></p>
        <p><input name="invite" placeholder="邀请码（选填）" value="{{ old('invite') }}"></p>
        <p><button type="submit">注册</button> <a href="{{ url('/member/login') }}">去登录</a></p>
    </form>
@endsection
