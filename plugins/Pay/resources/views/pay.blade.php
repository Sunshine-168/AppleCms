@extends('themes.default.layout')
@section('content')
    <h1>支付</h1>
    <p>单号 {{ $order['order_no'] ?? '' }}</p>
    @if(($order['channel'] ?? '') === 'wechat')
        <p>请用微信扫描下面的付款码。到账后积分会加上，同一笔只加一次。</p>
        <p><code>{{ $order['code_url'] ?? '' }}</code></p>
        <p class="muted">本页不生成图片二维码。把链接放到微信「扫一扫 → 相册 / 链接」或商户工具里打开。</p>
    @elseif(($order['channel'] ?? '') === 'alipay')
        <p>正在跳转支付宝…</p>
        <form id="alipay-form" method="post" action="{{ $order['action'] ?? '' }}">
            @foreach(($order['fields'] ?? []) as $k => $v)
                <input type="hidden" name="{{ $k }}" value="{{ $v }}">
            @endforeach
        </form>
        <script>document.getElementById('alipay-form').submit();</script>
    @endif
    @if(! empty($order['id']))
        <p><a href="{{ url('/member/pay/'.$order['id']) }}">查看订单状态</a></p>
    @endif
    <p><a href="{{ url('/member') }}">返回会员中心</a></p>
@endsection
