@extends('themes.default.layout')
@section('content')
    <div class="member-page">
        <div class="list-head">
            <h1>查询订单</h1>
            <p class="muted">填写下单时的订单号（一般以 P 开头）。只能查自己账号的单。</p>
        </div>

        @if($error !== '')
            <p class="flash is-err">{{ $error }}</p>
        @endif

        <section class="member-card" style="max-width:480px">
            <form method="post" action="{{ url('/member/pay/lookup') }}" class="member-form">
                @csrf
                <label class="auth-field">
                    <span>订单号</span>
                    <input type="text" name="order_no" value="{{ $order_no }}" required placeholder="例如 P20260918120000ABCDEF" autocomplete="off">
                </label>
                <div class="auth-actions">
                    <button type="submit" class="btn-play btn-sm">查询</button>
                    <a class="btn-ghost" href="{{ url('/member/pay') }}">去充值</a>
                </div>
            </form>
        </section>

        <nav class="member-nav" style="margin-top:16px" aria-label="充值导航">
            <a href="{{ url('/member/pay/orders') }}">我的充值订单</a>
            <a href="{{ url('/member') }}">会员中心</a>
        </nav>
    </div>
@endsection
