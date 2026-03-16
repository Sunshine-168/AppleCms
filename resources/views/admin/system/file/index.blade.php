<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>{{ conf('name') }} - 附件管理</title>
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
    <div class="layui-card-body">
      <form class="layui-form" lay-filter="file-search">
        <div class="layui-form-item">
          <div class="layui-inline">
            <input type="text" name="keyword" placeholder="关键字（名称/类型/URL）" autocomplete="off" class="layui-input">
          </div>
          <div class="layui-inline">
            <button class="layui-btn" lay-submit lay-filter="file-search-btn">查询</button>
            <button type="reset" class="layui-btn layui-btn-primary" id="file-reset-btn">重置</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <div class="layui-card">
    <div class="layui-card-body">
      <table id="file-table" lay-filter="file-table"></table>
    </div>
  </div>
</div>

<script type="text/html" id="file-toolbar">
  <div class="layui-btn-container">
    <button class="layui-btn layui-btn-sm" id="upload-btn">上传文件</button>
    <button class="layui-btn layui-btn-sm layui-btn-danger" id="batch-del-btn">批量删除</button>
    <button class="layui-btn layui-btn-sm layui-btn-primary" lay-event="refresh">刷新</button>
  </div>
</script>

<script type="text/html" id="file-actions">
@verbatim
  <a class="layui-btn layui-btn-primary layui-btn-xs" lay-event="open">打开</a>
  <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="delete">删除</a>
@endverbatim
</script>

<script src="{{ asset('static/admin/layui/layui.js') }}"></script>
<script>
  layui.use(['table', 'form', 'layer', 'upload'], function () {
    var $ = layui.$;
    var table = layui.table;
    var form = layui.form;
    var layer = layui.layer;
    var upload = layui.upload;

    var csrfToken = $('meta[name=csrf-token]').attr('content');
    if (csrfToken) {
      $.ajaxSetup({headers: {'X-CSRF-TOKEN': csrfToken}});
    }

    function apiPost(url, data, callback) {
      data = data || {};
      if (csrfToken && typeof data === 'object' && data._token === undefined) {
        data._token = csrfToken;
      }
      $.post(url, data, function (res) {
        if (res && res.code === 0) {
          callback && callback(res);
          return;
        }
        layer.msg(res && res.msg ? res.msg : '操作失败', {icon: 2});
      }, 'json').fail(function () {
        layer.msg('请求失败', {icon: 2});
      });
    }

    function initUpload() {
      var $btn = $('#upload-btn');
      if ($btn.length === 0) {
        return;
      }
      if ($btn.data('uploadInited')) {
        return;
      }
      $btn.data('uploadInited', true);

      upload.render({
        elem: '#upload-btn',
        url: '/admin/system/attachments/upload',
        field: 'file',
        accept: 'file',
        exts: 'jpg|png|gif|heic|heif|jpeg|mp3|wav|ogg|m4a|aac|mp4|mov|avi|webm|pdf|doc|docx|xls|xlsx|ppt|pptx|txt|zip|rar|7z',
        headers: csrfToken ? {'X-CSRF-TOKEN': csrfToken} : {},
        data: csrfToken ? {_token: csrfToken} : {},
        done: function(res){
          if (res && res.code === 0) {
            layer.msg('上传成功', {icon: 1});
            table.reload('file-table');
          } else {
            layer.msg(res && res.msg ? res.msg : '上传失败', {icon: 2});
          }
        },
        error: function(){
          layer.msg('上传失败', {icon: 2});
        }
      });
    }

    table.render({
      elem: '#file-table',
      id: 'file-table',
      url: '/admin/system/attachments/list',
      method: 'get',
      page: true,
      toolbar: '#file-toolbar',
      defaultToolbar: [],
      done: function () {
        initUpload();
      },
      parseData: function (res) {
        var data = res && res.data ? res.data : {};
        return {
          code: res && typeof res.code === 'number' ? res.code : 1,
          msg: res && typeof res.msg === 'string' ? res.msg : '',
          count: data && typeof data.total === 'number' ? data.total : 0,
          data: data && Array.isArray(data.data) ? data.data : []
        };
      },
      cols: [[
        {type:'checkbox', fixed:'left', width: 48},
        {field: 'id', title: 'ID', width: 80, sort: true},
        {field: 'name', title: '名称', minWidth: 200},
        {field: 'mime', title: '类型', width: 140},
        {field: 'size_text', title: '大小', width: 120},
        {field: 'url', title: 'URL', minWidth: 260},
        {field: 'create_time', title: '上传时间', width: 180},
        {title: '操作', width: 160, toolbar: '#file-actions'}
      ]]
    });

    table.on('toolbar(file-table)', function (obj) {
      if (obj.event === 'refresh') {
        table.reload('file-table');
        return;
      }
    });

    $(document).on('click', '#batch-del-btn', function () {
      var check = table.checkStatus('file-table');
      var data = check.data || [];
      if (data.length === 0) {
        layer.msg('请勾选要删除的文件');
        return;
      }
      var ids = data.map(function (row) { return row.id; });
      layer.confirm('确认删除选中的文件？', function (index) {
        apiPost('/admin/system/attachments/delete', {ids: ids}, function () {
          layer.close(index);
          table.reload('file-table');
          layer.msg('删除成功', {icon: 1});
        });
      });
    });

    table.on('tool(file-table)', function (obj) {
      var data = obj.data || {};
      if (obj.event === 'open') {
        if (data.id) {
          window.open('/admin/system/attachments/open?id=' + data.id, '_blank');
        } else if (data.url) {
          window.open(data.url, '_blank');
        } else {
          layer.msg('无可用链接');
        }
        return;
      }
      if (obj.event === 'delete') {
        layer.confirm('确认删除该文件？', function (index) {
          apiPost('/admin/system/attachments/delete', {ids: [data.id]}, function () {
            layer.close(index);
            table.reload('file-table');
            layer.msg('删除成功', {icon: 1});
          });
        });
      }
    });

    form.on('submit(file-search-btn)', function (obj) {
      table.reload('file-table', {where: obj.field || {}, page: {curr: 1}});
      return false;
    });

    $('#file-reset-btn').on('click', function () {
      table.reload('file-table', {where: {}, page: {curr: 1}});
    });
  });
</script>
</body>
</html>
