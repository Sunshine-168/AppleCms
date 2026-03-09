@extends('layouts.front')

@section('title', '网址列表')

@section('content')
<h2 class="mb-4">网址列表</h2>

<form class="d-flex mb-4" action="{{ route('website.search') }}" method="GET">
    <input class="form-control me-2" type="search" name="wd" placeholder="搜索网址">
    <button class="btn btn-outline-success" type="submit">搜索</button>
</form>

<div class="row">
    @foreach($websites as $website)
    <div class="col-md-3 col-sm-6 mb-4">
        <div class="card h-100">
            <a href="{{ route('website.detail', $website->website_id) }}">
                <img src="{{ $website->website_pic ?: asset('static/images/nopic.gif') }}" class="card-img-top" alt="{{ $website->website_name }}" style="height: 180px; object-fit: cover;">
            </a>
            <div class="card-body">
                <h5 class="card-title">
                    <a href="{{ route('website.detail', $website->website_id) }}" class="text-decoration-none text-dark">{{ $website->website_name }}</a>
                </h5>
                <p class="card-text text-muted">{{ \Illuminate\Support\Str::limit(strip_tags($website->website_blurb), 60) }}</p>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="d-flex justify-content-center mt-4">
    {{ $websites->links() }}
</div>
@endsection
