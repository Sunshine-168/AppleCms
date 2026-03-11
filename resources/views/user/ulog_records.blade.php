@extends('user.layout')

@section('title', $title)

@section('user_content')
<div class="card">
    <div class="card-header">{{ $title }}</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>模块</th>
                        <th>关联ID</th>
                        <th>类型</th>
                        <th>时间</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>{{ $log->ulog_id }}</td>
                            <td>{{ $log->ulog_mid }}</td>
                            <td>{{ $log->ulog_rid }}</td>
                            <td>{{ $log->ulog_type }}</td>
                            <td>{{ $log->ulog_time ? date('Y-m-d H:i:s', $log->ulog_time) : '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center">暂无记录</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $logs->links() }}
    </div>
</div>
@endsection
