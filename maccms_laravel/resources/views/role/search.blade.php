@extends('layouts.front')

@section('title', '角色搜索')

@section('content')
<h2 class="mb-4">角色搜索：{{ $wd }}</h2>

<div class="row">
    @forelse($roles as $role)
    <div class="col-md-3 col-sm-6 mb-4">
        <div class="card h-100">
            <a href="{{ route('role.detail', $role->role_id) }}">
                <img src="{{ $role->role_pic ?: asset('static/images/nopic.gif') }}" class="card-img-top" alt="{{ $role->role_name }}" style="height: 220px; object-fit: cover;">
            </a>
            <div class="card-body">
                <h5 class="card-title">
                    <a href="{{ route('role.detail', $role->role_id) }}" class="text-decoration-none text-dark">{{ $role->role_name }}</a>
                </h5>
                <p class="card-text text-muted">演员：{{ $role->role_actor ?: '-' }}</p>
            </div>
        </div>
    </div>
    @empty
    <div class="alert alert-warning">没有找到相关角色</div>
    @endforelse
</div>

<div class="d-flex justify-content-center mt-4">
    {{ $roles->appends(['wd' => $wd])->links() }}
</div>
@endsection
