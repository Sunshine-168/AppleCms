@extends('layouts.front')

@section('title', $role->role_name)

@section('content')
<div class="card mb-4">
    <div class="row g-0">
        <div class="col-md-3">
            <img src="{{ $role->role_pic ?: asset('static/images/nopic.gif') }}" class="img-fluid rounded-start" alt="{{ $role->role_name }}" style="width: 100%; max-height: 360px; object-fit: cover;">
        </div>
        <div class="col-md-9">
            <div class="card-body">
                <h2 class="card-title">{{ $role->role_name }}</h2>
                <p class="card-text"><strong>演员：</strong>{{ $role->role_actor ?: '-' }}</p>
                <p class="card-text"><strong>更新时间：</strong>{{ date('Y-m-d H:i', $role->role_time) }}</p>
                <p class="card-text"><strong>简介：</strong>{{ strip_tags($role->role_content) ?: '暂无简介' }}</p>
            </div>
        </div>
    </div>
</div>

<h3 class="mb-3">相关作品</h3>
@if($relatedVod->isEmpty())
    <div class="alert alert-info">暂无相关作品</div>
@else
<div class="row">
    @foreach($relatedVod as $video)
    <div class="col-md-2 col-sm-4 mb-4">
        <div class="card h-100">
            <a href="{{ route('vod.detail', $video->vod_id) }}">
                <img src="{{ $video->vod_pic ?: asset('static/images/nopic.gif') }}" class="card-img-top" alt="{{ $video->vod_name }}" style="height: 180px; object-fit: cover;">
            </a>
            <div class="card-body text-center p-2">
                <a href="{{ route('vod.detail', $video->vod_id) }}" class="text-decoration-none text-dark">{{ $video->vod_name }}</a>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif
@endsection
