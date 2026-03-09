@extends('user.layout')

@section('title', '订单详情')

@section('user_content')
<div class="card">
    <div class="card-header">订单详情</div>
    <div class="card-body">
        @if($order)
            <p>订单号：{{ $order->order_code }}</p>
            <p>订单金额：{{ $order->order_price }}</p>
            <p>兑换积分：{{ $order->order_points }}</p>
            <p>支付状态：{{ $order->order_status == 1 ? '已支付' : '待支付' }}</p>
            <p>支付方式：{{ $order->order_pay_type ?: '-' }}</p>
        @else
            <p>未找到订单。</p>
        @endif
    </div>
</div>
@endsection
