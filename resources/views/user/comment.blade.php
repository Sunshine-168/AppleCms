@extends('user.layout')

@section('title', '我的评论')

@section('user_content')
<div class="card">
    <div class="card-header">我的评论</div>
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
                    @forelse($comments as $comment)
                        <tr>
                            <td>{{ $comment->comment_id }}</td>
                            <td>{{ $comment->comment_content }}</td>
                            <td>{{ $comment->comment_status == 1 ? '已通过' : '待审核' }}</td>
                            <td>{{ $comment->comment_time ? date('Y-m-d H:i:s', $comment->comment_time) : '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center">暂无评论</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $comments->links() }}
    </div>
</div>
@endsection
