@extends('themes.default.layout')
@section('content')
<div class="mall-head">
    <div>
        <h1>我的兑换</h1>
        <p class="muted"><a href="{{ url('/mall') }}">← 返回商城</a> · 当前积分 {{ (int) ($points ?? 0) }}</p>
    </div>
</div>

@if($list->isEmpty())
    <p class="muted">还没有兑换记录。<a href="{{ url('/mall') }}">去逛逛</a></p>
@else
    <ul class="list-plain mall-orders">
        @foreach($list as $row)
            @php
                $type = \Plugins\Mall\Services\MallService::normalizeType((string) ($row->goods_type ?? ''));
                $delivery = \Plugins\Mall\Services\MallService::decodeExt($row->delivery ?? '');
                $status = (int) ($row->status ?? 0);
                $statusLabel = $status === 2 ? '已完成' : ($status === 1 ? '待发货' : '已关闭');
                $code = strtoupper(trim((string) ($delivery['code'] ?? '')));
            @endphp
            <li>
                <div class="mall-order-row">
                    <strong>{{ $row->goods_name }}</strong>
                    <span class="muted">{{ date('Y-m-d H:i', (int) $row->created_at) }}</span>
                </div>
                <div class="muted">
                    {{ \Plugins\Mall\Services\MallService::typeLabel($type) }}
                    · {{ (int) $row->points }} 积分
                    · {{ $statusLabel }}
                </div>
                @if($type === 'vip')
                    <div class="muted">
                        时长 {{ \Plugins\Mall\Services\MallService::vipDaysLabel((int) ($delivery['days'] ?? 0)) }}
                        @if((int) ($delivery['expire_at'] ?? 0) > 0)
                            · 至 {{ date('Y-m-d H:i', (int) $delivery['expire_at']) }}
                        @endif
                    </div>
                @endif
                @if($code !== '')
                    <div class="mall-code">
                        卡密 <code>{{ $code }}</code>
                        @if((int) ($delivery['auto_credit'] ?? 0) === 1)
                            <span class="muted">（已到账）</span>
                        @else
                            <span class="muted">· 可在 <a href="{{ url('/member') }}">会员中心</a> 兑换</span>
                        @endif
                    </div>
                @endif
                @if($type === 'goods')
                    <div class="muted">
                        @if(trim((string) ($row->contact ?? '')) !== '')
                            联系：{{ $row->contact }}
                        @endif
                        @if(trim((string) ($row->address ?? '')) !== '')
                            · {{ $row->address }}
                        @endif
                        @if(trim((string) ($row->remark ?? '')) !== '')
                            · 备注 {{ $row->remark }}
                        @endif
                    </div>
                @endif
            </li>
        @endforeach
    </ul>
    <div class="pager">{{ $list->links() }}</div>
@endif
@endsection
