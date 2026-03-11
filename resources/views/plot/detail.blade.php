@extends('layouts.front')

@section('title', $video->vod_name . ' 剧情')

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <h2 class="card-title">{{ $video->vod_name }}</h2>
        <p class="text-muted">剧情更新时间：{{ date('Y-m-d H:i', $video->vod_time) }}</p>
        <a class="btn btn-outline-primary btn-sm mb-3" href="{{ route('vod.detail', $video->vod_id) }}">查看视频详情</a>

        @forelse($plotList as $plot)
        <div class="mb-4">
            <h5>{{ $plot['name'] }}</h5>
            <div class="text-muted">{{ strip_tags($plot['detail']) ?: '暂无内容' }}</div>
        </div>
        @empty
        <div class="alert alert-info">暂无剧情内容</div>
        @endforelse
    </div>
</div>
@endsection
