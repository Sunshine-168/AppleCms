<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? '后台管理' }} - {{ __('admin.admin/public/head/title') }}</title>
    <link rel="stylesheet" href="{{ asset('static/layui/css/layui.css') }}">
    <link rel="stylesheet" href="{{ asset('static/css/admin_style.css') }}?v={{ config('maccms.version.code', '10') }}">
    <script type="text/javascript" src="{{ asset('static/js/jquery.js') }}"></script>
    <script type="text/javascript" src="{{ asset('static/layui/layui.js') }}"></script>
    <script>
        var ROOT_PATH="{{ url('/') }}", ADMIN_PATH="{{ url('admin') }}", MAC_VERSION="v10";
    </script>
</head>
<body>
