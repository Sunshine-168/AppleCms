@extends('layouts.front')

@section('title', $website->website_name)

@section('content')
<div class="card mb-4">
    <div class="row g-0">
        <div class="col-md-4">
            <img src="{{ $website->website_pic ?: asset('static/images/nopic.gif') }}" class="img-fluid rounded-start" alt="{{ $website->website_name }}" style="width: 100%; max-height: 320px; object-fit: cover;">
        </div>
        <div class="col-md-8">
            <div class="card-body">
                <h2 class="card-title">{{ $website->website_name }}</h2>
                <p class="card-text"><strong>地区：</strong>{{ $website->website_area ?: '-' }}</p>
                <p class="card-text"><strong>语言：</strong>{{ $website->website_lang ?: '-' }}</p>
                <p class="card-text"><strong>备注：</strong>{{ $website->website_remarks ?: '-' }}</p>
                <p class="card-text"><strong>简介：</strong>{{ strip_tags($website->website_content) ?: '暂无简介' }}</p>
                @if($website->website_jumpurl)
                <a class="btn btn-primary" href="{{ $website->website_jumpurl }}" target="_blank" rel="noopener noreferrer">访问网站</a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
