@extends('themes.default.layout')
@section('content')
    @php
        $coupons = is_array($coupons ?? null) ? $coupons : [];
        $channels = is_array($channels ?? null) ? $channels : [];
    @endphp

    <div class="member-page">
        <header class="member-hero">
            <div class="member-hero-main">
                <p class="member-eyebrow muted">在线充值</p>
                <h1>积分充值</h1>
                <p class="muted">1 元 = 100 积分。下单后保存订单号，付款页可复制，也可凭单号查询。</p>
            </div>
        </header>

        <nav class="member-nav" aria-label="充值导航">
            <a href="{{ url('/member/pay/orders') }}">我的充值订单</a>
            <a href="{{ url('/member/pay/lookup') }}">订单号查询</a>
            <a href="{{ url('/member/coupons') }}">优惠券</a>
            <a href="{{ url('/member') }}">会员中心</a>
        </nav>

        @if($errors->any())
            <p class="flash is-err">{{ $errors->first() }}</p>
        @endif

        <div class="member-grid">
            <section class="member-card">
                <div class="sec-head"><h2>查单号</h2></div>
                <form method="post" action="{{ url('/member/pay/lookup') }}" class="member-form">
                    @csrf
                    <label class="auth-field">
                        <span>订单号</span>
                        <input type="text" name="order_no" placeholder="粘贴订单号，如 P2026…" autocomplete="off">
                    </label>
                    <div class="auth-actions">
                        <button type="submit" class="btn-ghost">{{ admin_t('ui.search') }}</button>
                    </div>
                </form>
            </section>

            <section class="member-card">
                <div class="sec-head"><h2>选套餐</h2></div>
                @if($coupons !== [])
                    <p class="muted" style="margin-top:0">
                        已领可用：
                        @foreach($coupons as $row)
                            {{ $row['name'] }}（{{ $row['type_label'] }} {{ $row['value'] }}）@if(! $loop->last)、@endif
                        @endforeach
                    </p>
                @endif
                @if($channels === [])
                    <p class="muted">未配置支付参数。暂无可用支付方式。请在后台配置微信 / 支付宝密钥，或在「支付通道」接入易支付 / DfPay。</p>
                @else
                    <form method="post" action="{{ url('/member/pay') }}" class="member-form">
                        @csrf
                        <label class="auth-field">
                            <span>金额</span>
                            <select name="amount_yuan">
                                @foreach($packages as $n)
                                    <option value="{{ $n }}" @selected((string) old('amount_yuan') === (string) $n)>{{ $n }} 元 / {{ $n * 100 }} 积分</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="auth-field">
                            <span>支付方式</span>
                            <select name="channel">
                                @foreach($channels as $ch)
                                    <option value="{{ $ch['value'] }}" @selected(old('channel', $channels[0]['value'] ?? '') === $ch['value'])>
                                        {{ $ch['title'] }}
                                    </option>
                                @endforeach
                            </select>
                        </label>
                        @if($coupons !== [])
                            <label class="auth-field">
                                <span>优惠券</span>
                                <select name="coupon_user_id">
                                    <option value="0">不使用</option>
                                    @foreach($coupons as $row)
                                        <option value="{{ $row['coupon_user_id'] }}" @selected((string) old('coupon_user_id') === (string) $row['coupon_user_id'])>
                                            {{ $row['name'] }} · {{ $row['type_label'] }} {{ $row['value'] }} · 门槛 {{ $row['min_price'] }} 元
                                        </option>
                                    @endforeach
                                </select>
                            </label>
                            <p class="muted" style="margin:0;font-size:12px">实付按券算，积分按套餐原价到账。全额抵成 0 元不成单。</p>
                        @endif
                        <div class="auth-actions">
                            <button type="submit" class="btn-play btn-sm">去支付</button>
                        </div>
                    </form>
                @endif
            </section>
        </div>
    </div>
@endsection
