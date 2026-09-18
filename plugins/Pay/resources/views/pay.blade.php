@extends('themes.default.layout')
@section('content')
    <h1>支付</h1>
    <p>单号 {{ $order['order_no'] ?? '' }}</p>
    @if(($order['channel'] ?? '') === 'wechat')
        <p>请用微信扫描下面的付款码。到账后积分会加上，同一笔只加一次。</p>
        <p><code>{{ $order['code_url'] ?? '' }}</code></p>
        <p class="muted">本页不生成图片二维码。把链接放到微信「扫一扫」或商户工具里打开。</p>
    @elseif(($order['channel'] ?? '') === 'alipay')
        <p>正在跳转支付宝…</p>
        <form id="alipay-form" method="post" action="{{ $order['action'] ?? '' }}">
            @foreach(($order['fields'] ?? []) as $k => $v)
                <input type="hidden" name="{{ $k }}" value="{{ $v }}">
            @endforeach
        </form>
        <script>document.getElementById('alipay-form').submit();</script>
    @elseif(! empty($order['pay_url']))
        <p>正在跳转收银台…</p>
        @if(($order['mode'] ?? '') === 'qrcode' && ! empty($order['code_url']))
            <p>也可扫码：<code>{{ $order['code_url'] }}</code></p>
        @endif
        <p><a class="btn" href="{{ $order['pay_url'] }}">若未自动跳转，点这里打开</a></p>
        <script>location.href = @json($order['pay_url']);</script>
    @else
        <p class="muted">没有支付链接，请返回重试。</p>
    @endif
    @if(! empty($order['id']))
        <p><a href="{{ url('/member/pay/'.$order['id']) }}">查看订单状态</a></p>
    @endif
    <p><a href="{{ url('/member') }}">返回会员中心</a></p>
@endsection
