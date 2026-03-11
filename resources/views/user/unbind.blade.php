@extends('user.layout')

@section('title', '解除绑定')

@section('user_content')
<div class="card">
    <div class="card-header">解除绑定</div>
    <div class="card-body">
        <p class="text-muted">当前邮箱：{{ $user->user_email ?: '未绑定' }}，当前手机：{{ $user->user_phone ?: '未绑定' }}</p>
        <form method="post" action="{{ route('user.unbind') }}">
            @csrf
            <div class="mb-3">
                <select class="form-select" name="ac">
                    <option value="email">邮箱</option>
                    <option value="phone">手机</option>
                </select>
            </div>
            <button type="submit" class="btn btn-danger">解除绑定</button>
        </form>
    </div>
</div>
@endsection
