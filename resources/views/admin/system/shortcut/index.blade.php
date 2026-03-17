<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>{{ conf('name') }} - 系统快捷</title>
  <meta name="renderer" content="webkit">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="stylesheet" href="{{ asset('static/admin/layui/css/layui.css') }}" media="all">
  <link rel="stylesheet" href="{{ asset('static/admin/style/admin.css') }}" media="all">
  <style>
    body { background: #f6f8fb; }
    .page-topbar { display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; }
    .page-title { display:flex; align-items:baseline; gap:12px; }
    .page-title h1 { font-size: 18px; font-weight: 600; margin:0; color:#111827; }
    .page-title .sub { color:#6b7280; font-size:12px; }
    .page-actions { display:flex; align-items:center; gap:10px; }
    .page-actions .meta { color:#9ca3af; font-size:12px; }
    .quick-item { display:flex; align-items:center; gap:12px; padding: 14px 14px; background:#fff; border-radius: 10px; box-shadow: 0 6px 18px rgba(15, 23, 42, .06); transition: transform .15s ease, box-shadow .15s ease; }
    .quick-item:hover { transform: translateY(-2px); box-shadow: 0 10px 26px rgba(15, 23, 42, .09); }
    .quick-icon { width: 38px; height: 38px; border-radius: 12px; display:flex; align-items:center; justify-content:center; color:#fff; flex: 0 0 auto; }
    .quick-icon i { font-size: 18px; }
    .quick-main { display:flex; flex-direction:column; gap:4px; min-width: 0; }
    .quick-title { font-size: 14px; font-weight: 600; color:#0f172a; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .quick-desc { font-size: 12px; color:#94a3b8; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .skeleton { position: relative; border-radius: 8px; background: linear-gradient(90deg, rgba(148,163,184,.18) 25%, rgba(148,163,184,.32) 37%, rgba(148,163,184,.18) 63%); background-size: 400% 100%; animation: sk 1.2s ease infinite; }
    .skeleton.card { height: 70px; }
    .skeleton.meta { height: 12px; width: 160px; border-radius: 999px; }
    @keyframes sk { 0% { background-position: 100% 0; } 100% { background-position: 0 0; } }
    .layui-col-xs12 { margin-bottom: 15px; }
  </style>
</head>
<body>
<div class="layui-fluid">
  <div class="page-topbar">
    <div class="page-title">
      <h1>系统快捷</h1>
      <div class="sub">常用系统功能入口</div>
    </div>
    <div class="page-actions">
      <div class="meta" id="shortcut-updated"><span class="skeleton meta"></span></div>
      <button class="layui-btn layui-btn-sm" id="shortcut-refresh-btn"><i class="layui-icon layui-icon-refresh-3"></i> 刷新</button>
    </div>
  </div>

  <div class="layui-row layui-col-space15" id="shortcut-grid">
    <div class="layui-col-xs12 layui-col-sm6 layui-col-md4"><div class="skeleton card"></div></div>
    <div class="layui-col-xs12 layui-col-sm6 layui-col-md4"><div class="skeleton card"></div></div>
    <div class="layui-col-xs12 layui-col-sm6 layui-col-md4"><div class="skeleton card"></div></div>
    <div class="layui-col-xs12 layui-col-sm6 layui-col-md4"><div class="skeleton card"></div></div>
    <div class="layui-col-xs12 layui-col-sm6 layui-col-md4"><div class="skeleton card"></div></div>
    <div class="layui-col-xs12 layui-col-sm6 layui-col-md4"><div class="skeleton card"></div></div>
  </div>
</div>

<script src="{{ asset('static/admin/layui/layui.js') }}"></script>
<script>
layui.use(['jquery', 'layer'], function () {
  var $ = layui.$;
  var layer = layui.layer;

  function setUpdated() {
    var d = new Date();
    var pad = function (n) { return (n < 10 ? '0' : '') + n; };
    var text = d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + ' ' + pad(d.getHours()) + ':' + pad(d.getMinutes()) + ':' + pad(d.getSeconds());
    $('#shortcut-updated').text('更新于 ' + text);
  }

  function openTab(url, title) {
    if (!url) {
      return;
    }
    try {
      if (window.parent && window.parent.layui && window.parent.layui.index && window.parent.layui.index.openTabsPage) {
        window.parent.layui.index.openTabsPage(String(url), String(title || ''));
        return;
      }
    } catch (e) {}
    window.location.href = String(url);
  }

  function render(list) {
    var $grid = $('#shortcut-grid');
    $grid.empty();

    for (var i = 0; i < list.length; i++) {
      var it = list[i] || {};
      var url = it.url || '';
      var title = it.title || '';
      var desc = it.desc || '';
      var icon = it.icon || '';
      var color = it.color || 'linear-gradient(135deg,#60a5fa,#2563eb)';

      var html = ''
        + '<div class="layui-col-xs12 layui-col-sm6 layui-col-md4">'
        +   '<a href="javascript:;" class="quick-item" data-url="' + String(url).replace(/"/g, '&quot;') + '" data-title="' + String(title).replace(/"/g, '&quot;') + '">'
        +     '<span class="quick-icon" style="background:' + color + '"><i class="layui-icon layui-icon-' + icon + '"></i></span>'
        +     '<span class="quick-main">'
        +       '<span class="quick-title">' + title + '</span>'
        +       '<span class="quick-desc">' + desc + '</span>'
        +     '</span>'
        +   '</a>'
        + '</div>';
      $grid.append(html);
    }

    $grid.find('.quick-item').on('click', function () {
      openTab($(this).data('url'), $(this).data('title'));
    });
  }

  function loadList() {
    $('#shortcut-refresh-btn').prop('disabled', true);
    $('#shortcut-updated').html('<span class="skeleton meta"></span>');
    return $.ajax({
      url: '/admin/system/shortcut/list',
      method: 'get',
      dataType: 'json'
    }).done(function (res) {
      var list = res && res.data && res.data.data ? res.data.data : [];
      if (!Array.isArray(list)) {
        list = [];
      }
      render(list);
      setUpdated();
    }).fail(function () {
      layer.msg('加载失败');
    }).always(function () {
      $('#shortcut-refresh-btn').prop('disabled', false);
    });
  }

  $('#shortcut-refresh-btn').on('click', function () {
    loadList();
  });

  loadList();
});
</script>
</body>
</html>

