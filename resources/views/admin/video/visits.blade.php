@extends('admin.layouts.inner')
@section('title', admin_t('page.stats'))

@section('plain')
<div class="card card-panel">
    <div class="card-header"><span>{{ admin_t('ui.today_pv_uv', ['pv' => $today['pv'] ?? 0, 'uv' => $today['uv'] ?? 0]) }}</span></div>
    <div class="card-body">
        <table class="data">
            <thead><tr><th>{{ admin_t('ui.date') }}</th><th>PV</th><th>UV</th></tr></thead>
            <tbody>
            @forelse($days ?? [] as $row)
                <tr><td>{{ $row['day'] ?? '' }}</td><td>{{ $row['pv'] ?? 0 }}</td><td>{{ $row['uv'] ?? 0 }}</td></tr>
            @empty
                <tr><td colspan="3"><div class="list-empty"><p>{{ admin_t('ui.no_chart_data') }}</p><p><a class="btn btn-muted btn-sm" href="/" target="_blank" rel="noopener">{{ admin_t('ui.open_front') }}</a></p></div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="split-2">
    <div class="card card-panel">
        <div class="card-header"><span>{{ admin_t('ui.by_video') }}</span></div>
        <div class="card-body">
            <table class="data">
                <thead><tr><th>{{ admin_t('ui.col_video_id') }}</th><th>PV</th></tr></thead>
                <tbody>
                @forelse($videos ?? [] as $row)
                    <tr><td>{{ $row['video_id'] ?? '' }}</td><td>{{ $row['pv'] ?? 0 }}</td></tr>
                @empty
                    <tr><td colspan="2"><div class="list-empty"><p>{{ admin_t('ui.no_chart_data') }}</p><p><a class="btn btn-muted btn-sm" href="/admin/video">{{ admin_t('ui.go_videos') }}</a></p></div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
