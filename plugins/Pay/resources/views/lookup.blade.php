@extends('themes.default.layout')
@section('content')
    <h1>查询订单</h1>
    <p class="muted">填写下单时的订单号（一般以 P 开头）。只能查自己账号的单。</p>
    @if($error !== '')
        <p class="flash is-err">{{ $error }}</p>
    @endif
    <form method="post" action="{{ url('/member/pay/lookup') }}" class="mall-buy">
        @csrf
        <label>订单号</label>
        <input type="text" name="order_no" value="{{ $order_no }}" required placeholder="例如 P20260918120000ABCDEF" autocomplete="off">
        <button type="submit">查询</button>
    </form>
    <p>
        <a href="{{ url('/member/pay/orders') }}">我的充值订单</a>
        · <a href="{{ url('/member/pay') }}">去充值</a>
        · <a href="{{ url('/member') }}">会员中心</a>
    </p>
@endsection
