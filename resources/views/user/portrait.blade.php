@extends('user.layout')

@section('title', '头像上传')

@section('user_content')
<div class="card">
    <div class="card-header">头像上传</div>
    <div class="card-body">
        @if($user->user_portrait)
            <div class="mb-3">
                <img src="{{ asset($user->user_portrait) }}" alt="portrait" style="max-width: 120px;">
            </div>
        @endif
        <form method="post" action="{{ route('user.portrait') }}" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <input type="file" class="form-control" name="file">
            </div>
            <button type="submit" class="btn btn-primary">上传头像</button>
        </form>
    </div>
</div>
@endsection
