<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', conf('name'))</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ @filemtime(public_path('css/admin.css')) ?: '1' }}">
    @stack('styles')
</head>
<body class="iframe-body">
<div class="dash-iframe">
    @hasSection('plain')
        @yield('plain')
    @else
        <div class="card card-panel">
            <div class="card-header">
                <span>@yield('title')</span>
                <div>@yield('header_actions')</div>
            </div>
            <div class="card-body">
                @yield('content')
            </div>
        </div>
        @yield('after')
    @endif
</div>
<script src="{{ asset('js/admin-ui.js') }}?v={{ @filemtime(public_path('js/admin-ui.js')) ?: '1' }}"></script>
@stack('scripts')
</body>
</html>
