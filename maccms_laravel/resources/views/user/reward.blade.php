@extends('user.layout')

@section('title', '推广记录')

@section('user_content')
<div class="card">
    <div class="card-header">推广记录</div>
    <div class="card-body">
        <form method="get" class="row g-2 mb-3">
            <div class="col-auto">
                <select class="form-select" name="level">
                    <option value="1" {{ $level === '1' ? 'selected' : '' }}>一级分销</option>
                    <option value="2" {{ $level === '2' ? 'selected' : '' }}>二级分销</option>
                    <option value="3" {{ $level === '3' ? 'selected' : '' }}>三级分销</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">筛选</button>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>用户ID</th>
                        <th>用户名</th>
                        <th>积分</th>
                        <th>注册时间</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $item)
                        <tr>
                            <td>{{ $item->user_id }}</td>
                            <td>{{ $item->user_name }}</td>
                            <td>{{ $item->user_points }}</td>
                            <td>{{ $item->user_reg_time ? date('Y-m-d H:i:s', $item->user_reg_time) : '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center">暂无推广记录</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $users->appends(['level' => $level])->links() }}
    </div>
</div>
@endsection
