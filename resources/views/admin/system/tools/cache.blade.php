<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>{{ conf('name') }} - 缓存管理</title>
  <meta name="renderer" content="webkit">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="stylesheet" href="{{ asset('static/admin/layui/css/layui.css') }}" media="all">
  <link rel="stylesheet" href="{{ asset('static/admin/style/admin.css') }}" media="all">
</head>
<body>
<div class="layui-fluid">
  <div class="layui-card">
    <div class="layui-card-header">缓存信息</div>
    <div class="layui-card-body">
      <table class="layui-table" lay-size="sm">
        <colgroup>
          <col width="180">
          <col>
        </colgroup>
        <tbody>
        <tr>
          <td>默认 Store</td>
          <td id="cache-default-store">-</td>
        </tr>
        <tr>
          <td>驱动</td>
          <td id="cache-driver">-</td>
        </tr>
        <tr>
          <td>前缀</td>
          <td id="cache-prefix">-</td>
        </tr>
        <tr>
          <td>数据库表</td>
          <td id="cache-db-table">-</td>
        </tr>
        <tr>
          <td>数据库缓存条数</td>
          <td id="cache-db-count">-</td>
        </tr>
        </tbody>
      </table>
      <div class="layui-btn-container">
        <button class="layui-btn layui-btn-sm layui-btn-primary" id="cache-refresh-btn">刷新</button>
        <button class="layui-btn layui-btn-sm layui-btn-danger" id="cache-flush-btn">清空缓存</button>
      </div>
    </div>
  </div>

  <div class="layui-card">
    <div class="layui-card-header">框架缓存命令</div>
    <div class="layui-card-body">
      <div class="layui-btn-container">
        <button class="layui-btn layui-btn-sm" data-cmd="optimize:clear">optimize:clear</button>
        <button class="layui-btn layui-btn-sm layui-btn-primary" data-cmd="cache:clear">cache:clear</button>
        <button class="layui-btn layui-btn-sm layui-btn-primary" data-cmd="config:clear">config:clear</button>
        <button class="layui-btn layui-btn-sm layui-btn-primary" data-cmd="route:clear">route:clear</button>
        <button class="layui-btn layui-btn-sm layui-btn-primary" data-cmd="view:clear">view:clear</button>
        <button class="layui-btn layui-btn-sm layui-btn-primary" data-cmd="event:clear">event:clear</button>
      </div>
      <div class="layui-btn-container" style="margin-top:10px;">
        <button class="layui-btn layui-btn-sm layui-btn-warm" data-cmd="config:cache">config:cache</button>
        <button class="layui-btn layui-btn-sm layui-btn-warm" data-cmd="route:cache">route:cache</button>
        <button class="layui-btn layui-btn-sm layui-btn-warm" data-cmd="view:cache">view:cache</button>
        <button class="layui-btn layui-btn-sm layui-btn-warm" data-cmd="event:cache">event:cache</button>
      </div>
      <div style="margin-top:12px;">
        <pre id="cache-cmd-output" style="background:#0b1020;color:#e5e7eb;padding:12px;border-radius:6px;white-space:pre-wrap;word-break:break-all;min-height:120px;"></pre>
      </div>
    </div>
  </div>
</div>

<script src="{{ asset('static/admin/layui/layui.js') }}"></script>
<script>
  layui.use(['layer'], function () {
    var $ = layui.$;
    var layer = layui.layer;

    var csrfToken = $('meta[name=csrf-token]').attr('content');
    if (csrfToken) {
      $.ajaxSetup({headers: {'X-CSRF-TOKEN': csrfToken}});
    }

    function setText(id, val) {
      $(id).text(val === null || val === undefined || val === '' ? '-' : String(val));
    }

    function refreshInfo() {
      $.get('/admin/system/tools/cache/info', function (res) {
        if (!res || res.code !== 0) {
          layer.msg(res && res.msg ? res.msg : '获取失败');
          return;
        }
        var d = res.data || {};
        setText('#cache-default-store', d.default_store);
        setText('#cache-driver', d.driver);
        setText('#cache-prefix', d.prefix);
        setText('#cache-db-table', d.database_table);
        setText('#cache-db-count', d.database_count);
      }, 'json').fail(function () {
        layer.msg('请求失败');
      });
    }

    $('#cache-refresh-btn').on('click', function () {
      refreshInfo();
    });

    $('#cache-flush-btn').on('click', function () {
      layer.confirm('确认清空缓存？', function (index) {
        layer.close(index);
        var idx = layer.load(1);
        $.post('/admin/system/tools/cache/flush', {}, function (res) {
          layer.close(idx);
          if (!res || res.code !== 0) {
            layer.msg(res && res.msg ? res.msg : '清空失败');
            return;
          }
          layer.msg('清空成功', {icon: 1});
          refreshInfo();
        }, 'json').fail(function () {
          layer.close(idx);
          layer.msg('请求失败');
        });
      });
    });

    $('[data-cmd]').on('click', function () {
      var cmd = $(this).attr('data-cmd') || '';
      if (!cmd) return;
      var idx = layer.load(1);
      $('#cache-cmd-output').text('');
      $.post('/admin/system/tools/cache/run', {command: cmd}, function (res) {
        layer.close(idx);
        if (!res || res.code !== 0) {
          layer.msg(res && res.msg ? res.msg : '执行失败');
          return;
        }
        var out = res.data && res.data.output ? res.data.output : '';
        $('#cache-cmd-output').text(out);
        layer.msg('执行成功', {icon: 1});
        refreshInfo();
      }, 'json').fail(function () {
        layer.close(idx);
        layer.msg('请求失败');
      });
    });

    refreshInfo();
  });
</script>
</body>
</html>

