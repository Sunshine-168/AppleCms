@extends('themes.default.layout')
@section('content')
    <h1>在线充值</h1>
    <p class="muted">1 元 = 100 积分。可接微信 / 支付宝官方，或易支付、DfPay 等聚合通道。优惠券只减实付现金，积分按套餐原价到账。</p>
    @if($errors->any())
        <p class="flash is-err">{{ $errors->first() }}</p>
    @endif
    @php
        $coupons = is_array($coupons ?? null) ? $coupons : [];
        $channels = is_array($channels ?? null) ? $channels : [];
    @endphp
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
    <p><a href="{{ url('/member') }}">返回会员中心</a></p>
@endsection
