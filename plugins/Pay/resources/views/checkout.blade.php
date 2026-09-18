@extends('themes.default.layout')
@section('content')
    <h1>在线充值</h1>
    <p class="muted">1 元 = 100 积分。没有配密钥会直接失败，不会记成已付。优惠券只减实付现金，积分按套餐原价到账。积分商城不抵。</p>
    @if($errors->any())
        <p class="muted">{{ $errors->first() }}</p>
    @endif
    @php $coupons = is_array($coupons ?? null) ? $coupons : []; @endphp
    @if($coupons !== [])
        <p>
            已领可用：
            @foreach($coupons as $row)
                {{ $row['name'] }}（{{ $row['type_label'] }} {{ $row['value'] }} · 门槛 {{ $row['min_price'] }} 元）@if(! $loop->last)、@endif
            @endforeach
        </p>
    @endif
    @if(! $wechatReady && ! $alipayReady)
        <p>未配置支付参数。后台「插件 → 在线支付」填微信 / 支付宝密钥后再试。有券也下不了单。</p>
    @else
        <form method="post" action="{{ url('/member/pay') }}">
            @csrf
            <p>
                <label>金额</label>
                <select name="amount_yuan">
                    @foreach($packages as $n)
                        <option value="{{ $n }}" @selected((string) old('amount_yuan') === (string) $n)>{{ $n }} 元 / {{ $n * 100 }} 积分</option>
                    @endforeach
                </select>
            </p>
            <p>
                <label>渠道</label>
                <select name="channel">
                    @if($wechatReady)
                        <option value="wechat" @selected(old('channel', 'wechat') === 'wechat')>微信</option>
                    @endif
                    @if($alipayReady)
                        <option value="alipay" @selected(old('channel') === 'alipay')>支付宝</option>
                    @endif
                </select>
            </p>
            @if($coupons !== [])
                <p>
                    <label>优惠券</label>
                    <select name="coupon_user_id">
                        <option value="0">不使用</option>
                        @foreach($coupons as $row)
                            <option value="{{ $row['coupon_user_id'] }}" @selected((string) old('coupon_user_id') === (string) $row['coupon_user_id'])>
                                {{ $row['name'] }} · {{ $row['type_label'] }} {{ $row['value'] }} · 门槛 {{ $row['min_price'] }} 元
                            </option>
                        @endforeach
                    </select>
                </p>
                <p class="muted">实付按券算，积分按套餐原价到账。未达门槛或券不对场景会失败。全额抵成 0 元不成单。</p>
            @endif
            <p><button type="submit">去支付</button></p>
        </form>
        @if($wechatReady)
            <p class="muted">微信回调：{{ url('/pay/notify/wechat') }}</p>
        @endif
        @if($alipayReady)
            <p class="muted">支付宝回调：{{ url('/pay/notify/alipay') }}</p>
        @endif
    @endif
    <p><a href="{{ url('/member') }}">返回会员中心</a></p>
@endsection
