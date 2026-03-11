@extends('user.layout')

@section('title', '我的留言')

@section('user_content')
<div class="card">
    <div class="card-header">我的留言</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>内容</th>
                        <th>状态</th>
                        <th>时间</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($gbooks as $gbook)
                        <tr>
                            <td>{{ $gbook->gbook_id }}</td>
                            <td>{{ $gbook->gbook_content }}</td>
                            <td>{{ $gbook->gbook_status == 1 ? '已通过' : '待审核' }}</td>
                            <td>{{ $gbook->gbook_time ? date('Y-m-d H:i:s', $gbook->gbook_time) : '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center">暂无留言</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $gbooks->links() }}
    </div>
</div>
@endsection
