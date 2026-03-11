@extends('user.layout')

@section('title', '会员升级')

@section('user_content')
<div class="card">
    <div class="card-header">会员升级</div>
    <div class="card-body">
        <p>当前积分：{{ $user->user_points }}</p>
        <form method="post" action="{{ route('user.upgrade') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">会员组</label>
                <select class="form-select" name="group_id">
                    @foreach($groups as $group)
                        <option value="{{ $group->group_id }}">{{ $group->group_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">时长</label>
                <select class="form-select" name="long">
                    <option value="day">天</option>
                    <option value="week">周</option>
                    <option value="month">月</option>
                    <option value="year">年</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">升级</button>
        </form>
    </div>
</div>
@endsection
