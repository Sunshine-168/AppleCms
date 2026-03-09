<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>{{ __('install.title') }}</title>
    <link rel="stylesheet" href="{{ asset('static/layui/css/layui.css') }}">
    <link rel="stylesheet" href="{{ asset('static/css/admin_style.css') }}">
    <link rel="stylesheet" href="{{ asset('static/css/install.css') }}">
    <script type="text/javascript" src="{{ asset('static/layui/layui.js') }}"></script>
    <script>
        var ROOT_PATH = "{{ url('/') }}", ADMIN_PATH = "{{ request()->getPathInfo() }}";
    </script>
</head>
<body>
<div class="header">
    <h1>{{ __('install.header') }} {{ __('install.header1') }}</h1>
</div>
