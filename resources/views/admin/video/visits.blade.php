@extends('admin.layouts.inner')
@section('title', '访问统计')

@section('plain')
<div class="card card-panel">
    <div class="card-header"><span>今日 PV {{ $today['pv'] ?? 0 }} / UV {{ $today['uv'] ?? 0 }}</span></div>
    <div class="card-body">
        <table class="data">
            <thead><tr><th>日期</th><th>PV</th><th>UV</th></tr></thead>
            <tbody>
            @forelse($days ?? [] as $row)
                <tr><td>{{ $row['day'] ?? '' }}</td><td>{{ $row['pv'] ?? 0 }}</td><td>{{ $row['uv'] ?? 0 }}</td></tr>
            @empty
                <tr><td colspan="3"><div class="list-empty"><p>暂无数据</p></div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="split-2">
    <div class="card card-panel">
        <div class="card-header"><span>按影片</span></div>
        <div class="card-body">
            <table class="data">
                <thead><tr><th>影片ID</th><th>PV</th></tr></thead>
                <tbody>
                @forelse($videos ?? [] as $row)
                    <tr><td>{{ $row['video_id'] ?? '' }}</td><td>{{ $row['pv'] ?? 0 }}</td></tr>
                @empty
                    <tr><td colspan="2"><div class="list-empty"><p>暂无数据</p></div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card card-panel">
        <div class="card-header"><span>按分类</span></div>
        <div class="card-body">
            <table class="data">
                <thead><tr><th>分类ID</th><th>PV</th></tr></thead>
                <tbody>
                @forelse($types ?? [] as $row)
                    <tr><td>{{ $row['type_id'] ?? '' }}</td><td>{{ $row['pv'] ?? 0 }}</td></tr>
                @empty
                    <tr><td colspan="2"><div class="list-empty"><p>暂无数据</p></div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
