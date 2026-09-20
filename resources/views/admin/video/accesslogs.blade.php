@extends('admin.layouts.inner')
@section('title', $title)

@section('plain')
<div class="card card-panel accesslog-index list-desk">
    <div class="card-header">
        <span>{{ admin_t('ui.access_risk') }} <em id="accesslog-count"></em></span>
        <div>
            <a class="btn btn-muted btn-sm" href="/admin/video/botlogs">{{ admin_t('ui.botlogs') }}</a>
            <a class="btn btn-muted btn-sm" href="/admin/stats/logs">{{ admin_t('ui.visit_detail') }}</a>
            <a class="btn btn-muted btn-sm" href="/admin/stats/spiders">{{ admin_t('ui.spider_stats') }}</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/config/ip">{{ admin_t('nav.config_ip') }}</a>
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
