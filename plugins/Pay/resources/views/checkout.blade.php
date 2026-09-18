@extends('themes.default.layout')
@section('content')
    <h1>在线充值</h1>
    <p class="muted">1 元 = 100 积分。下单后会生成订单号，付款页可复制。也可凭订单号查询状态。</p>
    @if($errors->any())
        <p class="flash is-err">{{ $errors->first() }}</p>
    @endif
    @php
        $coupons = is_array($coupons ?? null) ? $coupons : [];
        $channels = is_array($channels ?? null) ? $channels : [];
    @endphp

    <form method="post" action="{{ url('/member/pay/lookup') }}" class="mall-buy" style="margin-bottom:20px">
        @csrf
        <label>用订单号查询</label>
        <div class="field-inline" style="display:flex;gap:8px;max-width:420px">
            <input type="text" name="order_no" placeholder="粘贴订单号，如 P2026…" autocomplete="off" style="flex:1">
            <button type="submit">查询</button>
        </div>
    </form>

    @if($coupons !== [])
        <p>
            已领可用：
            @foreach($coupons as $row)
                {{ $row['name'] }}（{{ $row['type_label'] }} {{ $row['value'] }} · 门槛 {{ $row['min_price'] }} 元）@if(! $loop->last)、@endif
            @endforeach
        </p>
    @endif
    @if($channels === [])
        <p>暂无可用支付方式。后台配置微信 / 支付宝密钥，或在「支付通道」接入易支付 / DfPay。</p>
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
                <label>支付方式</label>
                <select name="channel">
                    @foreach($channels as $ch)
                        <option value="{{ $ch['value'] }}" @selected(old('channel', $channels[0]['value'] ?? '') === $ch['value'])>
                            {{ $ch['title'] }}
                        </option>
                    @endforeach
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
                <p class="muted">实付按券算，积分按套餐原价到账。全额抵成 0 元不成单。</p>
            @endif
            <p><button type="submit">去支付</button></p>
        </form>
    @endif
    <p>
        <a href="{{ url('/member/pay/orders') }}">我的充值订单</a>
        · <a href="{{ url('/member/pay/lookup') }}">订单号查询</a>
        · <a href="{{ url('/member') }}">返回会员中心</a>
    </p>
@endsection
