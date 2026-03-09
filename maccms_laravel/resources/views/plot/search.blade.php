@extends('layouts.front')

@section('title', '剧情搜索')

@section('content')
<h2 class="mb-4">剧情搜索：{{ $wd }}</h2>

<div class="list-group">
    @forelse($videos as $video)
    <a href="{{ route('plot.detail', $video->vod_id) }}" class="list-group-item list-group-item-action">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <strong>{{ $video->vod_name }}</strong>
                <div class="text-muted small">{{ \Illuminate\Support\Str::limit(strip_tags($video->vod_plot_name), 80) }}</div>
            </div>
            <span class="badge bg-secondary">{{ date('Y-m-d', $video->vod_time) }}</span>
        </div>
    </a>
    @empty
    <div class="alert alert-warning">没有找到相关剧情</div>
    @endforelse
</div>

<div class="d-flex justify-content-center mt-4">
    {{ $videos->appends(['wd' => $wd])->links() }}
</div>
@endsection
