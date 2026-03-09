@extends('layouts.front')

@section('title', $type->type_name)

@section('content')
<h2 class="mb-4">分类：{{ $type->type_name }}</h2>

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
