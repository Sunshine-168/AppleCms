<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>后台管理</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="{{ asset('static/admin/layui/css/layui.css') }}" media="all">
    <link rel="stylesheet" href="{{ asset('static/admin/style/admin.css') }}" media="all">
</head>
<body class="layui-layout-body">

<div id="LAY_app" class="layui-layout layui-layout-admin">
    <div class="layui-header">
        <ul class="layui-nav layui-layout-left" lay-filter="layadmin-layout-left">
            <li class="layui-nav-item layadmin-flexible" lay-unselect>
                <a href="javascript:;" layadmin-event="flexible" title="侧边伸缩">
                    <i class="layui-icon layui-icon-shrink-right" id="LAY_app_flexible"></i>
                </a>
            </li>
            <li class="layui-nav-item" lay-unselect>
                <a href="javascript:;" layadmin-event="refresh" title="刷新">
                    <i class="layui-icon layui-icon-refresh-3"></i>
                </a>
            </li>
        </ul>
        <ul class="layui-nav layui-layout-right" lay-filter="layadmin-layout-right">
            <li class="layui-nav-item" lay-unselect>
                <a href="javascript:;">
                    <cite>管理员</cite>
                </a>
                <dl class="layui-nav-child">
                    <dd><a layadmin-event="logout">退出</a></dd>
                </dl>
            </li>
        </ul>
    </div>

    <div class="layui-side layui-side-menu">
        <div class="layui-side-scroll">
            <div class="layui-logo" lay-href="javascript:;">
                <span>XHCMS</span>
            </div>
            <ul class="layui-nav layui-nav-tree" lay-shrink="all" lay-filter="layadmin-system-side-menu" id="LAY-system-side-menu"></ul>
        </div>
    </div>

    <div class="layui-body">
        <div class="layui-tab" lay-filter="layadmin-layout-tabs" lay-allowclose="true">
            <ul class="layui-tab-title" id="LAY_app_tabsheader">
                <li class="layui-this" lay-id="{{ url('/static/admin/tpl/system/about.html') }}" lay-attr="system/about">
                    <i class="layui-icon layui-icon-home"></i>
                </li>
            </ul>
            <div class="layui-tab-content" id="LAY_app_body">
                <div class="layadmin-tabsbody-item layui-show">
                    <iframe src="{{ url('/static/admin/tpl/system/about.html') }}" frameborder="0" class="layadmin-iframe"></iframe>
                </div>
            </div>
        </div>
    </div>

    <div class="layadmin-body-shade" layadmin-event="shade"></div>
</div>

<script src="{{ asset('static/admin/layui/layui.js') }}"></script>
<script>
    layui.config({
        base: '{{ asset('static/admin') }}/'
    }).extend({
        index: 'lib/index'
    }).use('index');
</script>
</body>
</html>

