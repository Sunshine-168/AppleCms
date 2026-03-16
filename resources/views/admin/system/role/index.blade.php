<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>{{ conf('name') }} - 角色管理</title>
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
      <form class="layui-form" lay-filter="role-search">
        <div class="layui-form-item">
          <div class="layui-inline">
            <input type="text" name="name" placeholder="角色名称" autocomplete="off" class="layui-input">
          </div>
          <div class="layui-inline">
            <input type="text" name="code" placeholder="角色标识" autocomplete="off" class="layui-input">
          </div>
          <div class="layui-inline">
            <select name="status">
              <option value="">状态</option>
              <option value="1">启用</option>
              <option value="0">禁用</option>
            </select>
          </div>
          <div class="layui-inline">
            <button class="layui-btn" lay-submit lay-filter="role-search-btn">查询</button>
            <button type="reset" class="layui-btn layui-btn-primary" id="role-reset-btn">重置</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <div class="layui-card">
    <div class="layui-card-body">
      <table id="role-table" lay-filter="role-table"></table>
    </div>
  </div>
</div>

<script type="text/html" id="role-toolbar">
  <div class="layui-btn-container">
    <button class="layui-btn layui-btn-sm" lay-event="add">新增角色</button>
    <button class="layui-btn layui-btn-sm layui-btn-primary" lay-event="refresh">刷新</button>
  </div>
</script>

<script type="text/html" id="role-actions">
@verbatim
  <a class="layui-btn layui-btn-primary layui-btn-xs" lay-event="edit">编辑</a>
  <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="delete">删除</a>
@endverbatim
</script>

<script type="text/html" id="role-dialog-tpl">
  <div style="padding:16px 18px 0 0;">
    <form class="layui-form" lay-filter="role-form">
      <input type="hidden" name="id" value="">
      <div class="layui-form-item">
        <label class="layui-form-label">名称</label>
        <div class="layui-input-block">
          <input type="text" name="name" placeholder="角色名称" autocomplete="off" class="layui-input">
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">标识</label>
        <div class="layui-input-block">
          <input type="text" name="code" placeholder="唯一标识" autocomplete="off" class="layui-input">
        </div>
      </div>
      <div class="layui-form-item layui-form-text">
        <label class="layui-form-label">备注</label>
        <div class="layui-input-block">
          <textarea name="remark" placeholder="可选" class="layui-textarea" style="min-height: 60px;"></textarea>
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">状态</label>
        <div class="layui-input-block">
          <input type="radio" name="status" value="1" title="启用" checked>
          <input type="radio" name="status" value="0" title="禁用">
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">排序</label>
        <div class="layui-input-block">
          <input type="number" name="sort" value="0" autocomplete="off" class="layui-input">
        </div>
      </div>
    </form>
  </div>
</script>

<script src="{{ asset('static/admin/layui/layui.js') }}"></script>
<script>
  layui.use(['table', 'form', 'layer'], function () {
    var $ = layui.$;
    var table = layui.table;
    var form = layui.form;
    var layer = layui.layer;

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

    function openForm(data) {
      data = data || {};
      var isEdit = !!data.id;
      var content = $('#role-dialog-tpl').html();

      layer.open({
        type: 1,
        title: isEdit ? '编辑角色' : '新增角色',
        area: ['640px', '480px'],
        content: content,
        btn: ['保存', '取消'],
        success: function (layero) {
          var $layer = $(layero);
          $layer.find('input[name=id]').val(data.id || '');
          $layer.find('input[name=name]').val(data.name || '');
          $layer.find('input[name=code]').val(data.code || '');
          $layer.find('textarea[name=remark]').val(data.remark || '');
          $layer.find('input[name=sort]').val(data.sort || 0);
          var statusVal = (data.status == 0 ? '0' : '1');
          $layer.find('input[name=status][value="' + statusVal + '"]').prop('checked', true);
          form.render();
        },
        yes: function (index, layero) {
          var $layer = $(layero);
          var id = parseInt($layer.find('input[name=id]').val() || '0', 10);
          var name = $.trim($layer.find('input[name=name]').val());
          var code = $.trim($layer.find('input[name=code]').val());
          var remark = $.trim($layer.find('textarea[name=remark]').val());
          var status = $layer.find('input[name=status]:checked').val();
          var sort = parseInt($layer.find('input[name=sort]').val() || '0', 10);

          if (!name) {
            layer.msg('请输入角色名称');
            return;
          }
          if (!code) {
            layer.msg('请输入角色标识');
            return;
          }

          var url = id > 0 ? '/admin/system/roles/update' : '/admin/system/roles/add';
          var payload = {name: name, code: code, remark: remark, status: status, sort: sort};
          if (id > 0) {
            payload.id = id;
          }

          var loading = layer.load(1);
          apiPost(url, payload, function () {
            layer.close(loading);
            layer.close(index);
            table.reload('role-table');
            layer.msg('保存成功', {icon: 1});
          });
        }
      });
    }

    table.render({
      elem: '#role-table',
      id: 'role-table',
      url: '/admin/system/roles/list',
      method: 'get',
      page: true,
      toolbar: '#role-toolbar',
      defaultToolbar: [],
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
        {field: 'id', title: 'ID', width: 80, sort: true},
        {field: 'name', title: '名称', minWidth: 160},
        {field: 'code', title: '标识', minWidth: 160},
        {field: 'status', title: '状态', width: 90, templet: function (d) { return d.status == 1 ? '<span class="layui-badge layui-bg-green">启用</span>' : '<span class="layui-badge layui-bg-gray">禁用</span>'; }},
        {field: 'sort', title: '排序', width: 90, sort: true},
        {field: 'remark', title: '备注', minWidth: 200},
        {field: 'create_time', title: '创建时间', width: 180},
        {field: 'update_time', title: '更新时间', width: 180},
        {title: '操作', width: 140, toolbar: '#role-actions'}
      ]]
    });

    table.on('toolbar(role-table)', function (obj) {
      if (obj.event === 'refresh') {
        table.reload('role-table');
        return;
      }
      if (obj.event === 'add') {
        openForm({});
      }
    });

    table.on('tool(role-table)', function (obj) {
      var data = obj.data || {};
      if (obj.event === 'edit') {
        openForm(data);
        return;
      }
      if (obj.event === 'delete') {
        layer.confirm('确认删除该角色？', function (index) {
          apiPost('/admin/system/roles/delete', {id: data.id}, function () {
            layer.close(index);
            table.reload('role-table');
            layer.msg('删除成功', {icon: 1});
          });
        });
      }
    });

    form.on('submit(role-search-btn)', function (obj) {
      table.reload('role-table', {where: obj.field || {}, page: {curr: 1}});
      return false;
    });

    $('#role-reset-btn').on('click', function () {
      table.reload('role-table', {where: {}, page: {curr: 1}});
    });
  });
</script>
</body>
</html>
