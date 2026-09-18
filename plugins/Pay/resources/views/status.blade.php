@extends('themes.default.layout')
@section('content')
    <h1>订单 {{ $order->order_no }}</h1>
    @php
        $st = (int) $order->status;
        $yuan = number_format(((int) $order->amount) / 100, 2);
        $channelLabel = (string) ($channelLabel ?? $order->channel ?? '');
    @endphp
    <p>
        金额 <strong>{{ $yuan }}</strong> 元
        · 积分 <strong>{{ (int) $order->points }}</strong>
        · 渠道 {{ $channelLabel }}
    </p>
    <p>
        @if($st === 1)
            <span class="flash is-ok">已付到账</span>
            @if(! empty($order->trade_no))
                <span class="muted">流水 {{ $order->trade_no }}</span>
            @endif
            @if((int) ($order->paid_at ?? 0) > 0)
                <span class="muted">· {{ date('Y-m-d H:i', (int) $order->paid_at) }}</span>
            @endif
        @elseif($st === 2)
            <span class="flash is-err">已关闭</span>
        @else
            <span class="muted">待付。付完后请稍等回调；也可在后台「订单」补录已付。</span>
        @endif
    </p>
    <p class="muted">到账后积分会出现在会员中心，流水在积分明细里，并会收到站内信。</p>
    <p>
        <a href="{{ url('/member/pay/orders') }}">我的充值订单</a>
        · <a href="{{ url('/member/pay/lookup') }}">订单号查询</a>
        · <a href="{{ url('/member/pay') }}">继续充值</a>
        · <a href="{{ url('/member') }}">会员中心</a>
        · <a href="{{ url('/member/inbox') }}">站内信</a>
    </p>
@endsection
