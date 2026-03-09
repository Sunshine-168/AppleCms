@extends('layouts.front')

@section('title', '角色列表')

@section('content')
<h2 class="mb-4">角色列表</h2>

<form class="d-flex mb-4" action="{{ route('role.search') }}" method="GET">
    <input class="form-control me-2" type="search" name="wd" placeholder="搜索角色或演员">
    <button class="btn btn-outline-success" type="submit">搜索</button>
</form>

<div class="row">
    @foreach($roles as $role)
    <div class="col-md-3 col-sm-6 mb-4">
        <div class="card h-100">
            <a href="{{ route('role.detail', $role->role_id) }}">
                <img src="{{ $role->role_pic ?: asset('static/images/nopic.gif') }}" class="card-img-top" alt="{{ $role->role_name }}" style="height: 220px; object-fit: cover;">
            </a>
            <div class="card-body">
                <h5 class="card-title">
                    <a href="{{ route('role.detail', $role->role_id) }}" class="text-decoration-none text-dark">{{ $role->role_name }}</a>
                </h5>
                <p class="card-text text-muted mb-1">演员：{{ $role->role_actor ?: '-' }}</p>
                <small class="text-muted">{{ date('Y-m-d', $role->role_time) }}</small>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="d-flex justify-content-center mt-4">
    {{ $roles->links() }}
</div>
@endsection
