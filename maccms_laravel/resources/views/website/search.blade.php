@extends('layouts.front')

@section('title', '网址搜索')

@section('content')
<h2 class="mb-4">网址搜索：{{ $wd }}</h2>

<div class="row">
    @forelse($websites as $website)
    <div class="col-md-3 col-sm-6 mb-4">
        <div class="card h-100">
            <a href="{{ route('website.detail', $website->website_id) }}">
                <img src="{{ $website->website_pic ?: asset('static/images/nopic.gif') }}" class="card-img-top" alt="{{ $website->website_name }}" style="height: 180px; object-fit: cover;">
            </a>
            <div class="card-body">
                <h5 class="card-title">
                    <a href="{{ route('website.detail', $website->website_id) }}" class="text-decoration-none text-dark">{{ $website->website_name }}</a>
                </h5>
            </div>
        </div>
    </div>
    @empty
    <div class="alert alert-warning">没有找到相关网址</div>
    @endforelse
</div>

<div class="d-flex justify-content-center mt-4">
    {{ $websites->appends(['wd' => $wd])->links() }}
</div>
@endsection
