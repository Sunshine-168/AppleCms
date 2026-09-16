@extends('admin.layouts.inner')
@section('title', '伪静态规则（当前 '.($mode ?? 'laravel').($suffix ?? '').'）')

@section('content')
    <p class="hint">把片段放到站点根配置里。Laravel 模式走 try_files；苹果模式额外保留 index.php/vod。</p>
    <h3>Nginx</h3>
    <pre class="code-block">{{ $nginx ?? '' }}</pre>
    <h3>Apache</h3>
    <pre class="code-block">{{ $apache ?? '' }}</pre>
@endsection
