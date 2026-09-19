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

<div class="mall-page">
    <div class="mall-head">
        <div>
            <h1>积分商城</h1>
            <p class="muted">用观影积分兑换会员时长、卡密礼包或周边。没有在线支付。</p>
        </div>
        <div class="mall-balance">
            @auth('member')
                <div class="mall-balance-label">我的积分</div>
                <strong>{{ (int) ($points ?? 0) }}</strong>
                <a href="{{ url('/mall/orders') }}">我的兑换</a>
            @else
                <div class="mall-balance-label">积分兑换</div>
                <a class="btn-play btn-sm" href="{{ url('/member/login') }}">登录查看</a>
            @endauth
        </div>
    </div>

    <div class="mall-tabs" role="tablist">
        @foreach($tabs as $key => $label)
            <a href="{{ $key === '' ? url('/mall') : url('/mall?type='.$key) }}" class="mall-tab{{ $filterType === $key ? ' on' : '' }}">{{ $label }}</a>
        @endforeach
    </div>

    @if(!empty($hot) && $filterType === '')
        <section class="home-sec">
            <div class="sec-head"><h2>大家都在换</h2></div>
            <div class="grid mall-grid">
                @foreach($hot as $row)
                    @include('mall::partials.card', ['row' => $row, 'forceHot' => true])
                @endforeach
            </div>
        </section>
    @endif

    <section class="home-sec">
        <div class="sec-head">
            <h2>{{ $filterType === '' ? '全部商品' : ($tabs[$filterType] ?? '商品') }}</h2>
            @if(! $list->isEmpty())
                <span class="muted">{{ $list->total() }} 件</span>
            @endif
        </div>

        @if($list->isEmpty())
            <div class="list-empty">
                <p>还没有上架商品</p>
                <p class="muted">后台「积分商城」添加商品后会出现在这里。</p>
            </div>
        @else
            <div class="grid mall-grid">
                @foreach($list as $row)
                    @include('mall::partials.card', ['row' => $row])
                @endforeach
            </div>
            <div class="pager">{{ $list->withQueryString()->links() }}</div>
        @endif
    </section>
</div>
@endsection
