@extends('admin.layouts.inner')
@section('title', $title)

@section('plain')
<div class="card card-panel accesslog-index list-desk">
    <div class="card-header">
        <span>访问风控 <em id="accesslog-count"></em></span>
        <div>
            <a class="btn btn-muted btn-sm" href="/admin/video/botlogs">爬虫日志</a>
            <a class="btn btn-muted btn-sm" href="/admin/stats/logs">访问明细</a>
            <a class="btn btn-muted btn-sm" href="/admin/stats/spiders">蜘蛛统计</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/config/ip">IP 白名单</a>
        </div>
    </div>
    <div class="card-body">
        @include('admin.video.partials.accesslog_board', [
            'queues' => $queues ?? [],
            'access_ip' => $access_ip ?? '',
            'show_header_links' => false,
        ])
    </div>
</div>
@endsection

@push('scripts')
@include('admin.video.partials.accesslog_board_scripts')
@endpush
