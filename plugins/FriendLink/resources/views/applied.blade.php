@extends('themes.default.layout')

@section('content')
    <h1>申请已提交</h1>
    <p>{{ $msg }}</p>
    <p>修改令牌（只显示这一次，请自行保存）：</p>
    <p><code>{{ $token }}</code></p>
    <p class="muted">有令牌且后台允许自助修改时，可打开 <a href="{{ url('/links/edit/'.$token) }}">修改页</a>。</p>
@endsection
