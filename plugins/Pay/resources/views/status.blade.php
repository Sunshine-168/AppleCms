@extends('themes.default.layout')
@section('content')
    <h1>订单 {{ $order->order_no }}</h1>
    <p>金额 {{ number_format(((int) $order->amount) / 100, 2) }} 元 · {{ (int) $order->points }} 积分</p>
    <p>
        @if((int) $order->status === 1)
            已付
        @elseif((int) $order->status === 2)
            已关闭
        @else
            待付。付完后请稍等回调；后台「订单」也可补录已付。
        @endif
    </p>
    <p><a href="{{ url('/member') }}">返回会员中心</a></p>
@endsection
