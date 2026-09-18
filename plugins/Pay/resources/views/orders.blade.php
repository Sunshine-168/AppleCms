@extends('themes.default.layout')
@section('content')
    <div class="mall-head">
        <div>
            <h1>我的充值订单</h1>
            <p class="muted"><a href="{{ url('/member/pay') }}">← 去充值</a> · <a href="{{ url('/member/pay/lookup') }}">订单号查询</a> · 当前积分 {{ (int) ($member->points ?? 0) }}</p>
        </div>
    </div>
    @php $labels = is_array($channelLabels ?? null) ? $channelLabels : []; @endphp
    @if($list->isEmpty())
        <p class="muted">还没有充值订单。<a href="{{ url('/member/pay') }}">去充值</a></p>
    @else
        <ul class="list-plain">
            @foreach($list as $row)
                @php
                    $st = (int) $row->status;
                    $stLabel = $st === 1 ? '已付' : ($st === 2 ? '关闭' : '待付');
                    $ch = (string) ($row->channel ?? '');
                    $chLabel = $labels[$ch] ?? ($ch !== '' ? $ch : '-');
                @endphp
                <li>
                    <div class="mall-order-row">
                        <strong><a href="{{ url('/member/pay/'.$row->id) }}">{{ $row->order_no }}</a></strong>
                        <span class="muted">{{ date('Y-m-d H:i', (int) $row->created_at) }}</span>
                    </div>
                    <div class="muted">
                        {{ number_format(((int) $row->amount) / 100, 2) }} 元
                        · {{ (int) $row->points }} 积分
                        · {{ $chLabel }}
                        · {{ $stLabel }}
                    </div>
                </li>
            @endforeach
        </ul>
        <div class="pager">{{ $list->links() }}</div>
    @endif
@endsection
