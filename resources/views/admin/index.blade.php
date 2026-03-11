@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard</h1>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Welcome to Maccms Laravel Migration</h5>
                <p class="card-text">
                    You are logged in as {{ session('admin_name') }}.
                </p>
                <p>
                    Use the sidebar to navigate to different modules.
                </p>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h5>Videos</h5>
                <p>Manage videos and types</p>
                <a href="{{ route('admin.vod.index') }}" class="btn btn-light btn-sm">Go</a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5>Articles</h5>
                <p>Manage articles</p>
                <a href="{{ route('admin.art.index') }}" class="btn btn-light btn-sm">Go</a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h5>Actors</h5>
                <p>Manage actors</p>
                <a href="{{ route('admin.actor.index') }}" class="btn btn-light btn-sm">Go</a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <h5>Addons</h5>
                <p>Manage plugins</p>
                <a href="{{ route('admin.addon.index') }}" class="btn btn-light btn-sm">Go</a>
            </div>
        </div>
    </div>
</div>
@endsection
