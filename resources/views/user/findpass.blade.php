@extends('layouts.front')

@section('title', '找回密码')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">安全问题找回密码</div>
            <div class="card-body">
                <form method="post" action="{{ route('user.findpass') }}">
                    @csrf
                    <div class="mb-3"><input class="form-control" name="user_name" value="{{ $param['user_name'] ?? '' }}" placeholder="用户名"></div>
                    <div class="mb-3"><input class="form-control" name="user_question" value="{{ $param['user_question'] ?? '' }}" placeholder="安全问题"></div>
                    <div class="mb-3"><input class="form-control" name="user_answer" value="{{ $param['user_answer'] ?? '' }}" placeholder="安全答案"></div>
                    <div class="mb-3"><input type="password" class="form-control" name="user_pwd" placeholder="新密码"></div>
                    <div class="mb-3"><input type="password" class="form-control" name="user_pwd2" placeholder="确认新密码"></div>
                    <div class="mb-3"><input class="form-control" name="verify" placeholder="验证码"></div>
                    <button type="submit" class="btn btn-primary w-100">提交</button>
                </form>
            </div>
            <div class="card-footer text-center">
                <a href="{{ route('user.findpass_msg') }}" class="text-decoration-none">改用验证码找回</a>
            </div>
        </div>
    </div>
</div>
@endsection
