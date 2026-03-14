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
            <ul class="layui-nav layui-nav-tree" lay-shrink="all" lay-filter="layadmin-system-side-menu" id="LAY-system-side-menu">
            </ul>
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
    }).use(['index', 'element'], function () {
        var $ = layui.$;
        var element = layui.element;
        var menuUrl = '{{ asset('static/admin/json/menu.js') }}';

        function isArray(value) {
            return Object.prototype.toString.call(value) === '[object Array]';
        }

        function escapeHtml(value) {
            var str = value === undefined || value === null ? '' : String(value);
            return str
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function normalizeLayHref(item, pathSegments) {
            if (item && typeof item.jump === 'string' && item.jump.length > 0) return item.jump;
            var name = item && typeof item.name === 'string' ? item.name : '';
            var segments = pathSegments.slice();
            if (name) segments.push(name);
            return segments.length ? segments.join('/') : '';
        }

        function buildDdList(items, pathSegments) {
            var html = '';
            var list = items || [];
            for (var i = 0; i < list.length; i++) {
                var item = list[i] || {};
                var title = escapeHtml(item.title || '');
                var hasChildren = isArray(item.list) && item.list.length > 0;
                var nextPath = pathSegments.slice();
                if (item && typeof item.name === 'string' && item.name) nextPath.push(item.name);

                if (hasChildren) {
                    html += '<dd>';
                    html += '<a href="javascript:;">' + title + '</a>';
                    html += '<dl class="layui-nav-child">' + buildDdList(item.list, nextPath) + '</dl>';
                    html += '</dd>';
                } else {
                    var layHref = normalizeLayHref(item, pathSegments);
                    html += '<dd><a' + (layHref ? ' lay-href="' + escapeHtml(layHref) + '"' : '') + ' lay-text="' + title + '">' + title + '</a></dd>';
                }
            }
            return html;
        }

        function buildTopMenu(items) {
            var html = '';
            var list = items || [];
            for (var i = 0; i < list.length; i++) {
                var item = list[i] || {};
                var title = escapeHtml(item.title || '');
                var icon = escapeHtml(item.icon || '');
                var hasChildren = isArray(item.list) && item.list.length > 0;
                var classes = 'layui-nav-item' + (item.spread ? ' layui-nav-itemed' : '');
                var nextPath = [];
                if (item && typeof item.name === 'string' && item.name) nextPath.push(item.name);

                if (hasChildren) {
                    html += '<li class="' + classes + '">';
                    html += '<a href="javascript:;" lay-tips="' + title + '" lay-direction="2">';
                    html += (icon ? '<i class="layui-icon ' + icon + '"></i>' : '');
                    html += '<cite>' + title + '</cite>';
                    html += '</a>';
                    html += '<dl class="layui-nav-child">' + buildDdList(item.list, nextPath) + '</dl>';
                    html += '</li>';
                } else {
                    var layHref = normalizeLayHref(item, []);
                    html += '<li class="' + classes + '">';
                    html += '<a' + (layHref ? ' lay-href="' + escapeHtml(layHref) + '"' : '') + ' lay-text="' + title + '">';
                    html += (icon ? '<i class="layui-icon ' + icon + '"></i>' : '');
                    html += '<cite>' + title + '</cite>';
                    html += '</a>';
                    html += '</li>';
                }
            }
            return html;
        }

        $.ajax({
            url: menuUrl,
            dataType: 'json',
            cache: false,
            success: function (res) {
                var items = res && isArray(res.data) ? res.data : [];
                $('#LAY-system-side-menu').html(buildTopMenu(items));
                element.render('nav', 'layadmin-system-side-menu');
            },
            error: function () {
                element.render('nav', 'layadmin-system-side-menu');
            }
        });
    });
</script>
</body>
</html>
