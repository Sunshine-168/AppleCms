@extends('themes.default.layout')
@section('content')
    <div class="member-page">
        <div class="list-head">
            <h1>优惠券</h1>
            <p class="muted">领取后在现金充值里用。积分商城不抵。每人每种券 1 张。全额抵成 0 元下不了单。</p>
        </div>

        <nav class="member-nav" aria-label="优惠券导航">
            <a href="{{ url('/member/pay') }}">去充值</a>
            <a href="{{ url('/member') }}">会员中心</a>
        </nav>

        @if(session('status'))
            <p class="flash is-ok">{{ session('status') }}</p>
        @endif
        @if($errors->any())
            <p class="flash is-err">{{ $errors->first() }}</p>
        @endif

        <section class="member-card" style="margin-bottom:14px">
            <div class="sec-head"><h2>可领</h2></div>
            @forelse($shop as $row)
                <article class="coupon-card">
                    <div class="coupon-main">
                        <strong>{{ $row['name'] }}</strong>
                        <p class="muted">
                            {{ $row['type_label'] }} {{ $row['value'] }} · {{ $row['scene_label'] }}
                            · 门槛 {{ $row['min_price'] }} 元 · 剩余 {{ $row['left'] }}
                            · {{ $row['end_label'] }} · {{ $row['target_label'] }}
                        </p>
                    </div>
                    <div class="coupon-act">
                        @if(! empty($row['mine']))
                            <span class="muted">已领取</span>
                        @elseif((int) ($row['left'] ?? 0) < 1)
                            <span class="muted">已领完</span>
                        @else
                            <form method="post" action="{{ url('/member/coupons/'.$row['id'].'/receive') }}">
                                @csrf
                                <button type="submit" class="btn-play btn-sm">领取</button>
                            </form>
                        @endif
                    </div>
                </article>
            @empty
                <p class="muted">现在没有可领的券。</p>
            @endforelse
        </section>

        <section class="member-card">
            <div class="sec-head"><h2>我的券</h2></div>
            @forelse($wallet as $row)
                <article class="coupon-card">
                    <div class="coupon-main">
                        <strong>{{ $row['name'] }}</strong>
                        <p class="muted">
                            {{ $row['type_label'] }} {{ $row['value'] }}
                            · {{ $row['scene_label'] }}
                            · {{ (int) ($row['ticket_status'] ?? 0) === 1 ? '已用' : '未用' }}
                            @if(! empty($row['order_no'])) · 订单 {{ $row['order_no'] }} @endif
                        </p>
                    </div>
                </article>
            @empty
                <p class="muted">还没有领过券。</p>
            @endforelse
        </section>
    </div>
@endsection
