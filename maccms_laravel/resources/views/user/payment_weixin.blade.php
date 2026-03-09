@extends('user.layout')

@section('title', '微信支付')

@section('user_content')
<div class="card">
    <div class="card-header">{{ $paymentLabel ?? '微信支付' }}</div>
    <div class="card-body">
        <p>订单号：{{ $order->order_code }}</p>
        <p>订单金额：{{ $order->order_price }}</p>
        <p>请使用微信扫描下方二维码完成支付。</p>
        <div class="text-center my-4">
            <img src="{{ route('user.qrcode', ['data' => $paymentData['code_url'] ?? '']) }}" alt="微信支付二维码" class="img-fluid" style="max-width: 260px;">
        </div>
        <p class="text-muted small mb-0">支付完成后可返回订单页刷新状态。</p>
    </div>
</div>
@endsection
