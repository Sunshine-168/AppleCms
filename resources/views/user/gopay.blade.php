@extends('user.layout')

@section('title', '支付跳转')

@section('user_content')
<div class="card">
    <div class="card-header">支付跳转</div>
    <div class="card-body">
        <p>订单号：{{ $order->order_code }}</p>
        <p>支付方式：{{ $payment ?: '未选择' }}</p>
        <p>当前 Laravel 版本已保留订单入口，但第三方支付页面尚未完全迁移。</p>
    </div>
</div>
@endsection
