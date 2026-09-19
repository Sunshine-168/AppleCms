@extends('themes.default.layout')
@section('content')
    <div class="member-page">
        <div class="mall-head">
            <div>
                <h1>我的充值订单</h1>
                <p class="muted">当前积分 {{ (int) ($member->points ?? 0) }}</p>
            </div>
            <div class="mall-balance">
                <div class="mall-balance-label">积分</div>
                <strong>{{ (int) ($member->points ?? 0) }}</strong>
            </div>
        </div>

        <nav class="member-nav" aria-label="充值导航">
            <a href="{{ url('/member/pay') }}">去充值</a>
            <a href="{{ url('/member/pay/lookup') }}">订单号查询</a>
            <a href="{{ url('/member') }}">会员中心</a>
        </nav>

        @php $labels = is_array($channelLabels ?? null) ? $channelLabels : []; @endphp
        @if($list->isEmpty())
            <div class="list-empty">
                <p>还没有充值订单</p>
                <p><a class="btn-link" href="{{ url('/member/pay') }}">去充值</a></p>
            </div>
        @else
            <ul class="mall-orders">
                @foreach($list as $row)
                    @php
                        $st = (int) $row->status;
                        $stLabel = $st === 1 ? '已付' : ($st === 2 ? '关闭' : '待付');
                        $ch = (string) ($row->channel ?? '');
                        $chLabel = $labels[$ch] ?? ($ch !== '' ? $ch : '-');
                    @endphp
                    <li class="mall-order-card">
                        <div class="mall-order-row">
                            <strong><a href="{{ url('/member/pay/'.$row->id) }}">{{ $row->order_no }}</a></strong>
                            <span class="mall-order-status status-{{ $st === 1 ? '2' : ($st === 0 ? '1' : '0') }}">{{ $stLabel }}</span>
                        </div>
                        <div class="muted">
                            {{ number_format(((int) $row->amount) / 100, 2) }} 元
                            · {{ (int) $row->points }} 积分
                            · {{ $chLabel }}
                            · {{ date('Y-m-d H:i', (int) $row->created_at) }}
                        </div>
                    </li>
                @endforeach
            </ul>
            <div class="pager">{{ $list->links() }}</div>
        @endif
    </div>
@endsection
