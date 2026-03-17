<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>{{ conf('name') }} - 标签管理</title>
  <meta name="renderer" content="webkit">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="stylesheet" href="{{ asset('static/admin/layui/css/layui.css') }}">
  <link rel="stylesheet" href="{{ asset('static/admin/style/admin.css') }}">
</head>
<body>

<div class="layui-fluid">
  <div class="layui-card">
    <div class="layui-card-body">
      <form class="layui-form" id="video-tag-search">
        <div class="layui-form-item">
          <div class="layui-inline">
            <input type="text" name="name" placeholder="标签名称" autocomplete="off" class="layui-input">
          </div>
          <div class="layui-inline">
            <button type="button" class="layui-btn" id="video-tag-search-btn">查询</button>
            <button type="reset" class="layui-btn layui-btn-primary" id="video-tag-reset-btn">重置</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <div class="layui-card">
    <div class="layui-card-body">
      <div class="layui-btn-container" style="margin-bottom:10px;">
        <button class="layui-btn layui-btn-sm" id="video-tag-add-btn">新增标签</button>
        <button class="layui-btn layui-btn-sm layui-btn-primary" id="video-tag-refresh-btn">刷新</button>
      </div>
      <table class="layui-table" id="video-tag-table" lay-filter="video-tag-table"></table>

      <script type="text/html" id="video-tag-rowbar">
        <a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>
        <a class="layui-btn layui-btn-xs layui-btn-danger" lay-event="del">删除</a>
      </script>
    </div>
  </div>
</div>

<div id="video-tag-dialog-tpl" style="display:none;">
  <div style="padding:18px 18px 0 0;">
    <form class="layui-form layui-form-pane" lay-filter="video-tag-form">
      <input type="hidden" name="id">
      <div class="layui-form-item">
        <label class="layui-form-label">名称</label>
        <div class="layui-input-block">
          <input type="text" name="name" autocomplete="off" class="layui-input">
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">状态</label>
        <div class="layui-input-block">
          <select name="status">
            <option value="1">启用</option>
            <option value="0">禁用</option>
          </select>
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">排序</label>
        <div class="layui-input-block">
          <input type="number" name="sort" autocomplete="off" class="layui-input" value="0">
        </div>
      </div>
    </form>
  </div>
</div>

<script src="{{ asset('static/admin/layui/layui.js') }}"></script>
<script>
layui.use(['layer','form','table'], function(){
  var $ = layui.$, layer = layui.layer, form = layui.form, table = layui.table;

  var csrfToken = $('meta[name=csrf-token]').attr('content');
  if (csrfToken) {
    $.ajaxSetup({ headers: {'X-CSRF-TOKEN': csrfToken} });
  }

  function formToObj($form){
    var o = {};
    ($form.serializeArray()||[]).forEach(function(it){
      o[it.name] = it.value;
    });
    return o;
  }

  function apiPost(url, data, ok){
    $.ajax({
      url: url,
      type: 'POST',
      data: data || {},
      dataType: 'json',
      success: function(res){
        if (res && res.code === 0) {
          ok && ok(res.data || {}, res);
          return;
        }
        layer.msg((res && res.msg) ? res.msg : '操作失败', {icon:2});
      },
      error: function(){
        layer.msg('网络错误', {icon:2});
      }
    });
  }

  table.render({
    elem:'#video-tag-table',
    id: 'video-tag-table',
    url:'/admin/video/tags/list',
    method:'get',
    page:true,
    parseData:function(res){
      var data = res && res.data ? res.data : {};
      return {
        code: res && typeof res.code === 'number' ? res.code : 1,
        msg: res && typeof res.msg === 'string' ? res.msg : '',
        count: data && typeof data.total === 'number' ? data.total : 0,
        data: data && Array.isArray(data.data) ? data.data : []
      };
    },
    cols:[[
      {field:'id',width:80,title:'ID',sort:true},
      {field:'name',title:'名称',minWidth:220},
      {field:'status',title:'状态',width:90,templet:function(d){
        if (typeof d.status === 'undefined' || d.status === null || d.status === '') { return '-'; }
        return String(d.status) === '1'
          ? '<span class="layui-badge layui-bg-green">启用</span>'
          : '<span class="layui-badge layui-bg-gray">禁用</span>';
      }},
      {field:'sort',title:'排序',width:90,sort:true},
      {field:'created_at_text',title:'创建时间',width:180},
      {title:'操作',toolbar:'#video-tag-rowbar',width:150}
    ]]
  });

  function openDialog(mode, row){
    row = row || {};
    var isEdit = mode === 'edit';
    var title = isEdit ? '编辑标签' : '新增标签';

    var idx = layer.open({
      type: 1,
      title: title,
      area: ['520px', '360px'],
      content: $('#video-tag-dialog-tpl').html(),
      btn: ['保存', '取消'],
      success: function(){
        form.val('video-tag-form', {
          id: row.id || '',
          name: row.name || '',
          status: (row.status !== undefined && row.status !== null) ? String(row.status) : '1',
          sort: row.sort !== undefined ? row.sort : 0
        });
        form.render();
      },
      yes: function(){
        var $form = $('.layui-layer-content').find('form[lay-filter="video-tag-form"]');
        var data = formToObj($form);
        if (isEdit) {
          data.id = row.id;
        } else {
          delete data.id;
        }
        apiPost('/admin/video/tags/save', data, function(){
          layer.close(idx);
          table.reload('video-tag-table');
          layer.msg('保存成功', {icon:1});
        });
      }
    });
  }

  $('#video-tag-search-btn').on('click', function(){
    table.reload('video-tag-table', {where: formToObj($('#video-tag-search')), page: {curr: 1}});
  });

  $('#video-tag-refresh-btn').on('click', function(){
    table.reload('video-tag-table');
  });

  $('#video-tag-add-btn').on('click', function(){
    openDialog('add', {});
  });

  table.on('tool(video-tag-table)', function(obj){
    var row = obj.data || {};
    if (obj.event === 'edit') {
      openDialog('edit', row);
      return;
    }
    if (obj.event === 'del') {
      layer.confirm('确认删除该标签？', function(index){
        apiPost('/admin/video/tags/delete', {id: row.id}, function(){
          layer.close(index);
          table.reload('video-tag-table');
          layer.msg('删除成功', {icon:1});
        });
      });
    }
  });
});
</script>
</body>
</html>
