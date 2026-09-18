@extends('themes.default.layout')
@section('content')
    <h1>积分商城</h1>
    <p class="muted">用会员积分兑换。没有在线支付，会员组和卡密当场发放，实物由管理员发货。</p>
    @if($list->isEmpty())
        <p class="muted">还没有上架商品。</p>
    @else
        <div class="grid">
            @foreach($list as $row)
                <a class="card" href="{{ url('/mall/'.$row->id) }}">
                    @if($row->cover)
                        <img src="{{ $row->cover }}" alt="{{ $row->name }}">
                    @endif
                    <div class="meta">
                        <h3>{{ $row->name }}</h3>
                        <div class="muted">{{ \Plugins\Mall\Services\MallService::typeLabel($row->type ?? '') }} · {{ (int) $row->points }} 积分 · 剩 {{ (int) $row->stock }}</div>
                    </div>
                </a>
            @endforeach
        </div>
        <div class="pager">{{ $list->links() }}</div>
    @endif
@endsection
