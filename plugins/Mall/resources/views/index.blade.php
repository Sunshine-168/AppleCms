@extends('themes.default.layout')
@section('content')
@php
    $filterType = (string) ($filterType ?? '');
    $tabs = [
        '' => '全部',
        'vip' => '会员时长',
        'card' => '积分卡密',
        'goods' => '实物周边',
    ];
@endphp
<div class="mall-head">
    <div>
        <h1>积分商城</h1>
        <p class="muted">用观影积分兑换会员时长、卡密礼包或周边。没有在线支付。</p>
    </div>
    <div class="mall-balance">
        @auth('member')
            <div>我的积分 <strong>{{ (int) ($points ?? 0) }}</strong></div>
            <a href="{{ url('/mall/orders') }}">我的兑换</a>
        @else
            <a href="{{ url('/member/login') }}">登录查看积分</a>
        @endauth
    </div>
</div>

<div class="filter-row mall-tabs">
    @foreach($tabs as $key => $label)
        <a href="{{ $key === '' ? url('/mall') : url('/mall?type='.$key) }}" class="{{ $filterType === $key ? 'active' : '' }}">{{ $label }}</a>
    @endforeach
</div>

@if(!empty($hot) && $filterType === '')
    <h2 class="mall-sec">大家都在换</h2>
    <div class="grid mall-grid">
        @foreach($hot as $row)
            <a class="card" href="{{ url('/mall/'.$row->id) }}">
                @if($row->cover)
                    <img src="{{ $row->cover }}" alt="{{ $row->name }}">
                @endif
                <div class="meta">
                    <h3>{{ $row->name }}</h3>
                    <div class="muted">
                        <span class="mall-tag">热门</span>
                        {{ \Plugins\Mall\Services\MallService::typeLabel($row->type ?? '') }}
                        · {{ (int) $row->points }} 积分
                    </div>
                </div>
            </a>
        @endforeach
    </div>
@endif

<h2 class="mall-sec">{{ $filterType === '' ? '全部商品' : ($tabs[$filterType] ?? '商品') }}</h2>
@if($list->isEmpty())
    <p class="muted">还没有上架商品。</p>
@else
    <div class="grid mall-grid">
        @foreach($list as $row)
            <a class="card" href="{{ url('/mall/'.$row->id) }}">
                @if($row->cover)
                    <img src="{{ $row->cover }}" alt="{{ $row->name }}">
                @endif
                <div class="meta">
                    <h3>{{ $row->name }}</h3>
                    <div class="muted">
                        @if((int) ($row->is_hot ?? 0) === 1)<span class="mall-tag">热门</span>@endif
                        {{ \Plugins\Mall\Services\MallService::typeLabel($row->type ?? '') }}
                        · {{ (int) $row->points }} 积分
                        · 剩 {{ (int) $row->stock }}
                    </div>
                </div>
            </a>
        @endforeach
    </div>
    <div class="pager">{{ $list->withQueryString()->links() }}</div>
@endif
@endsection
