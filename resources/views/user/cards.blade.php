@extends('user.layout')

@section('title', '充值卡记录')

@section('user_content')
<div class="card">
    <div class="card-header">充值卡记录</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>卡号</th>
                        <th>积分</th>
                        <th>使用状态</th>
                        <th>使用时间</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cards as $card)
                        <tr>
                            <td>{{ $card->card_no }}</td>
                            <td>{{ $card->card_points }}</td>
                            <td>{{ $card->card_use_status == 1 ? '已使用' : '未使用' }}</td>
                            <td>{{ $card->card_use_time ? date('Y-m-d H:i:s', $card->card_use_time) : '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center">暂无记录</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $cards->links() }}
    </div>
</div>
@endsection
