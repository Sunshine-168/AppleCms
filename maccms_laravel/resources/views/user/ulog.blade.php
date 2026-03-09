@extends('layouts.front')

@section('title', 'My Logs')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-md-3">
            <div class="list-group">
                <a href="{{ route('user.index') }}" class="list-group-item list-group-item-action">Profile</a>
                <a href="{{ route('user.ulog', ['type' => 1]) }}" class="list-group-item list-group-item-action {{ request('type') == 1 ? 'active' : '' }}">Browse History</a>
                <a href="{{ route('user.ulog', ['type' => 2]) }}" class="list-group-item list-group-item-action {{ request('type') == 2 ? 'active' : '' }}">Favorites</a>
                <a href="{{ route('user.ulog', ['type' => 4]) }}" class="list-group-item list-group-item-action {{ request('type') == 4 ? 'active' : '' }}">Play History</a>
                <a href="{{ route('user.ulog', ['type' => 5]) }}" class="list-group-item list-group-item-action {{ request('type') == 5 ? 'active' : '' }}">Download History</a>
                <a href="/user/logout" class="list-group-item list-group-item-action text-danger">Logout</a>
            </div>
        </div>
        <div class="col-md-9">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Logs</span>
                    <button class="btn btn-sm btn-danger" onclick="clearLogs()">Clear All</button>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        @forelse($logs as $log)
                            @php
                                $item = $log->data;
                            @endphp
                            @if($item)
                                <a href="{{ $log->ulog_mid == 1 ? route('vod.detail', $item->vod_id) : route('art.detail', $item->art_id) }}" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h5 class="mb-1">{{ $log->ulog_mid == 1 ? $item->vod_name : $item->art_name }}</h5>
                                        <small>{{ date('Y-m-d H:i', $log->ulog_time) }}</small>
                                    </div>
                                    <small class="text-muted">{{ $log->ulog_mid == 1 ? 'Video' : 'Article' }}</small>
                                </a>
                            @else
                                <div class="list-group-item">Content deleted</div>
                            @endif
                        @empty
                            <div class="list-group-item">No logs found.</div>
                        @endforelse
                    </div>
                    <div class="mt-3">
                        {{ $logs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="clear-form" action="{{ route('user.ulog.delete') }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="type" value="{{ request('type') }}">
    <input type="hidden" name="all" value="1">
</form>

<script>
    function clearLogs() {
        if(confirm('Are you sure you want to clear all logs of this type?')) {
            document.getElementById('clear-form').submit();
        }
    }
</script>
@endsection
