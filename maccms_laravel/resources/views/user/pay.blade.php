@extends('user.layout')

@section('title', '订单支付')

@section('user_content')
<div class="card">
    <div class="card-header">订单支付</div>
    <div class="card-body">
        <p>订单号：{{ $order->order_code }}</p>
        <p>订单金额：{{ $order->order_price }}</p>
        <p>兑换积分：{{ $order->order_points }}</p>
        <p>状态：{{ $order->order_status == 1 ? '已支付' : '待支付' }}</p>
        @if($order->order_status != 1)
            @if(!empty($paymentMethods))
                <form method="post" action="{{ route('user.gopay') }}">
                    @csrf
                    <input type="hidden" name="order_id" value="{{ $order->order_id }}">
                    <div class="mb-3">
                        <label class="form-label">支付方式</label>
                        <select class="form-select" name="payment">
                            @foreach($paymentMethods as $paymentKey => $paymentLabel)
                                <option value="{{ $paymentKey }}">{{ $paymentLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-text mb-3">仅显示当前已在配置中启用的支付接口。</div>
                    <button type="submit" class="btn btn-primary">前往支付</button>
                </form>
            @else
                <div class="alert alert-warning mb-0">当前没有已启用的支付接口，请先在后台配置支付参数。</div>
            @endif
        @endif
    </div>
</div>
@endsection
