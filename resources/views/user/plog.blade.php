@extends('user.layout')

@section('title', '积分记录')

@section('user_content')
<div class="card">
    <div class="card-header">积分记录</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>类型</th>
                        <th>积分</th>
                        <th>备注</th>
                        <th>时间</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>{{ $log->plog_type }}</td>
                            <td>{{ $log->plog_points }}</td>
                            <td>{{ $log->plog_remarks }}</td>
                            <td>{{ $log->plog_time ? date('Y-m-d H:i:s', $log->plog_time) : '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center">暂无积分记录</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $logs->links() }}
    </div>
</div>
@endsection
