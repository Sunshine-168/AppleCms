@extends('themes.default.layout')
@section('content')
    <h1>会员登录</h1>
    <form method="post" action="{{ url('/member/login') }}">
        @csrf
        <p><input class="lay-like" name="email" type="email" placeholder="邮箱" value="{{ old('email') }}" required></p>
        <p><input name="password" type="password" placeholder="密码" required></p>
        <p><button type="submit">登录</button> <a href="{{ url('/member/register') }}">注册</a></p>
        @includeIf('connect::buttons')
    </form>
@endsection
