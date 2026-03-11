@extends('layouts.front')

@section('title', '剧情列表')

@section('content')
<h2 class="mb-4">剧情列表</h2>

<form class="d-flex mb-4" action="{{ route('plot.search') }}" method="GET">
    <input class="form-control me-2" type="search" name="wd" placeholder="搜索剧情">
    <button class="btn btn-outline-success" type="submit">搜索</button>
</form>

<div class="list-group">
    @foreach($videos as $video)
    <a href="{{ route('plot.detail', $video->vod_id) }}" class="list-group-item list-group-item-action">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <strong>{{ $video->vod_name }}</strong>
                <div class="text-muted small">{{ \Illuminate\Support\Str::limit(strip_tags($video->vod_plot_name), 80) }}</div>
            </div>
            <span class="badge bg-secondary">{{ date('Y-m-d', $video->vod_time) }}</span>
        </div>
    </a>
    @endforeach
</div>

<div class="d-flex justify-content-center mt-4">
    {{ $videos->links() }}
</div>
@endsection
