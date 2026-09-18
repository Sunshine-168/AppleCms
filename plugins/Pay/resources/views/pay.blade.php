@extends('themes.default.layout')
@section('content')
    @php $orderNo = (string) ($order['order_no'] ?? ''); @endphp
    <h1>去支付</h1>
    <div class="flash is-ok" style="display:flex;flex-wrap:wrap;gap:10px;align-items:center">
        <span>订单号</span>
        <code id="pay-order-no" style="font-size:16px;letter-spacing:.04em">{{ $orderNo }}</code>
        @if($orderNo !== '')
            <button type="button" class="btn btn-sm" id="pay-copy-no">复制单号</button>
        @endif
    </div>
    <p class="muted">请保存订单号。付款后可在「订单号查询 / 我的充值订单」查看是否到账。</p>

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
    <p>
        <a href="{{ url('/member/pay/lookup') }}">订单号查询</a>
        · <a href="{{ url('/member/pay/orders') }}">我的充值订单</a>
        · <a href="{{ url('/member') }}">返回会员中心</a>
    </p>
@endsection

@push('scripts')
<script>
(function () {
    var btn = document.getElementById('pay-copy-no');
    var el = document.getElementById('pay-order-no');
    if (!btn || !el) return;
    btn.addEventListener('click', function () {
        var text = el.textContent || '';
        if (!text) return;
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(function () { btn.textContent = '已复制'; });
            return;
        }
        var ta = document.createElement('textarea');
        ta.value = text;
        document.body.appendChild(ta);
        ta.select();
        try { document.execCommand('copy'); btn.textContent = '已复制'; } catch (e) {}
        document.body.removeChild(ta);
    });
})();
</script>
@endpush
