@extends('user.layout')

@section('title', '资料修改')

@section('user_content')
<div class="card">
    <div class="card-header">资料修改</div>
    <div class="card-body">
        <form method="post" action="{{ route('user.info') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">昵称</label>
                <input type="text" class="form-control" name="user_nick_name" value="{{ $user->user_nick_name }}">
            </div>
            <div class="mb-3">
                <label class="form-label">QQ</label>
                <input type="text" class="form-control" name="user_qq" value="{{ $user->user_qq }}">
            </div>
            <div class="mb-3">
                <label class="form-label">安全问题</label>
                <input type="text" class="form-control" name="user_question" value="{{ $user->user_question }}">
            </div>
            <div class="mb-3">
                <label class="form-label">安全答案</label>
                <input type="text" class="form-control" name="user_answer" value="{{ $user->user_answer }}">
            </div>
            <div class="mb-3">
                <label class="form-label">原密码</label>
                <input type="password" class="form-control" name="user_pwd">
            </div>
            <div class="mb-3">
                <label class="form-label">新密码</label>
                <input type="password" class="form-control" name="user_pwd1">
            </div>
            <div class="mb-3">
                <label class="form-label">确认新密码</label>
                <input type="password" class="form-control" name="user_pwd2">
            </div>
            <button type="submit" class="btn btn-primary">保存</button>
        </form>
    </div>
</div>
@endsection
