@extends('themes.default.layout')
@section('content')
    <h1>优惠券</h1>
    <p class="muted">领取后在现金充值里用。积分商城不抵。每人每种券 1 张。全额抵成 0 元下不了单。</p>
    @if(session('status'))
        <p>{{ session('status') }}</p>
    @endif
    @if($errors->any())
        <p class="muted">{{ $errors->first() }}</p>
    @endif

    <h2>可领</h2>
    @forelse($shop as $row)
        <div class="card" style="margin-bottom:12px">
            <p><strong>{{ $row['name'] }}</strong> · {{ $row['type_label'] }} {{ $row['value'] }} · {{ $row['scene_label'] }}</p>
            <p class="muted">门槛 {{ $row['min_price'] }} 元 · 剩余 {{ $row['left'] }} · {{ $row['end_label'] }} · {{ $row['target_label'] }}</p>
            @if(! empty($row['mine']))
                <p>已领取</p>
            @elseif((int) ($row['left'] ?? 0) < 1)
                <p>已领完</p>
            @else
                <form method="post" action="{{ url('/member/coupons/'.$row['id'].'/receive') }}">
                    @csrf
                    <button type="submit">领取</button>
                </form>
            @endif
        </div>
    @empty
        <p class="muted">现在没有可领的券。</p>
    @endforelse

    <h2>我的券</h2>
    @forelse($wallet as $row)
        <p>
            {{ $row['name'] }}
            · {{ $row['type_label'] }} {{ $row['value'] }}
            · {{ $row['scene_label'] }}
            · {{ (int) ($row['ticket_status'] ?? 0) === 1 ? '已用' : '未用' }}
            @if(! empty($row['order_no'])) · 订单 {{ $row['order_no'] }} @endif
        </p>
    @empty
        <p class="muted">还没有领过券。</p>
    @endforelse
    <p><a href="{{ url('/member') }}">返回会员中心</a></p>
@endsection
