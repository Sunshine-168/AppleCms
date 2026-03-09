@extends('user.layout')

@section('title', '订单记录')

@section('user_content')
<div class="card">
    <div class="card-header">订单记录</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>订单号</th>
                        <th>金额</th>
                        <th>积分</th>
                        <th>状态</th>
                        <th>时间</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td>{{ $order->order_code }}</td>
                            <td>{{ $order->order_price }}</td>
                            <td>{{ $order->order_points }}</td>
                            <td>{{ $order->order_status == 1 ? '已支付' : '待支付' }}</td>
                            <td>{{ $order->order_time ? date('Y-m-d H:i:s', $order->order_time) : '-' }}</td>
                            <td><a href="{{ route('user.pay', ['order_code' => $order->order_code]) }}">查看</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center">暂无订单</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $orders->links() }}
    </div>
</div>
@endsection
