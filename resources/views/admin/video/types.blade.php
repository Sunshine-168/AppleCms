<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>{{ conf('name') }} - 分类管理</title>
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
      <form class="layui-form" id="video-type-search">
        <div class="layui-form-item">
          <div class="layui-inline">
            <input type="text" name="name" placeholder="分类名称" autocomplete="off" class="layui-input">
          </div>
          <div class="layui-inline" style="width: 220px;">
            <select name="parent_id" id="video-type-parent-search">
              <option value="">全部父级</option>
              <option value="0">顶级</option>
            </select>
          </div>
          <div class="layui-inline">
            <button type="button" class="layui-btn" id="video-type-search-btn">查询</button>
            <button type="reset" class="layui-btn layui-btn-primary" id="video-type-reset-btn">重置</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <div class="layui-card">
    <div class="layui-card-body">
      <div class="layui-btn-container" style="margin-bottom:10px;">
        <button class="layui-btn layui-btn-sm" id="video-type-add-btn">新增分类</button>
        <button class="layui-btn layui-btn-sm layui-btn-primary" id="video-type-refresh-btn">刷新</button>
      </div>
      <table class="layui-table" id="video-type-table" lay-filter="video-type-table"></table>

      <script type="text/html" id="video-type-rowbar">
        <a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>
        <a class="layui-btn layui-btn-xs layui-btn-danger" lay-event="del">删除</a>
      </script>
    </div>
  </div>
</div>

<div id="video-type-dialog-tpl" style="display:none;">
  <div style="padding:18px 18px 0 0;">
    <form class="layui-form layui-form-pane" lay-filter="video-type-form">
      <input type="hidden" name="id">
      <div class="layui-form-item">
        <label class="layui-form-label">名称</label>
        <div class="layui-input-block">
          <input type="text" name="name" autocomplete="off" class="layui-input">
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">父级</label>
        <div class="layui-input-block">
          <select name="parent_id" id="video-type-parent-form">
            <option value="0">顶级</option>
          </select>
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">模型</label>
        <div class="layui-input-block">
          <select name="mid">
            <option value="1">视频</option>
            <option value="2">文章</option>
            <option value="3">网址</option>
          </select>
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">排序</label>
        <div class="layui-input-block">
          <input type="number" name="sort" autocomplete="off" class="layui-input" value="0">
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

  function escapeHtml(value){
    return String(value||'').replace(/[&<>"']/g,function(s){
      return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[s];
    });
  }

  function formToObj($form){
    var o = {};
    ($form.serializeArray()||[]).forEach(function(it){
      o[it.name] = it.value;
    });
    return o;
  }

  function apiGet(url, data, ok){
    $.ajax({
      url: url,
      type: 'GET',
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

  function loadParentOptions(done){
    apiGet('/admin/video/types/options', {}, function(list){
      list = Array.isArray(list) ? list : [];
      var opts = '<option value="0">顶级</option>';
      for (var i=0;i<list.length;i++){
        var r = list[i] || {};
        if (!r.id) { continue; }
        opts += '<option value="'+escapeHtml(r.id)+'">'+escapeHtml(r.name||'')+'</option>';
      }
      $('#video-type-parent-form').html(opts);
      var searchOpts = '<option value="">全部父级</option>' + opts;
      $('#video-type-parent-search').html(searchOpts);
      form.render('select');
      done && done();
    });
  }

  var tableIns = table.render({
    elem:'#video-type-table',
    id: 'video-type-table',
    url:'/admin/video/types/list',
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
      {field:'name',title:'名称',minWidth:160},
      {field:'parent_name',title:'父级',minWidth:140},
      {field:'mid',title:'模型',width:90,templet:function(d){
        if (String(d.mid) === '2') return '文章';
        if (String(d.mid) === '3') return '网址';
        return '视频';
      }},
      {field:'sort',title:'排序',width:90,sort:true},
      {field:'status',title:'状态',width:90,templet:function(d){
        if (typeof d.status === 'undefined' || d.status === null || d.status === '') { return '-'; }
        return String(d.status) === '1'
          ? '<span class="layui-badge layui-bg-green">启用</span>'
          : '<span class="layui-badge layui-bg-gray">禁用</span>';
      }},
      {field:'created_at_text',title:'创建时间',width:180},
      {field:'updated_at_text',title:'更新时间',width:180},
      {title:'操作',toolbar:'#video-type-rowbar',width:150}
    ]]
  });

  function openDialog(mode, row){
    row = row || {};
    var isEdit = mode === 'edit';
    var title = isEdit ? '编辑分类' : '新增分类';

    var idx = layer.open({
      type: 1,
      title: title,
      area: ['520px', '500px'],
      content: $('#video-type-dialog-tpl').html(),
      btn: ['保存', '取消'],
      success: function(layero){
        loadParentOptions(function(){
          var init = {
            id: row.id || '',
            name: row.name || '',
            parent_id: (row.parent_id !== undefined && row.parent_id !== null) ? String(row.parent_id) : '0',
            mid: (row.mid !== undefined && row.mid !== null) ? String(row.mid) : '1',
            sort: row.sort !== undefined ? row.sort : 0,
            status: (row.status !== undefined && row.status !== null) ? String(row.status) : '1'
          };
          form.val('video-type-form', init);
          form.render();
        });
      },
      yes: function(){
        var $form = $('.layui-layer-content').find('form[lay-filter="video-type-form"]');
        var data = formToObj($form);
        if (isEdit) {
          data.id = row.id;
        } else {
          delete data.id;
        }
        apiPost('/admin/video/types/save', data, function(){
          layer.close(idx);
          table.reload('video-type-table');
          layer.msg('保存成功', {icon:1});
        });
      }
    });
  }

  $('#video-type-search-btn').on('click', function(){
    table.reload('video-type-table', {where: formToObj($('#video-type-search')), page: {curr: 1}});
  });

  $('#video-type-refresh-btn').on('click', function(){
    table.reload('video-type-table');
  });

  $('#video-type-add-btn').on('click', function(){
    openDialog('add', {});
  });

  table.on('tool(video-type-table)', function(obj){
    var row = obj.data || {};
    if (obj.event === 'edit') {
      openDialog('edit', row);
      return;
    }
    if (obj.event === 'del') {
      layer.confirm('确认删除该分类？', function(index){
        apiPost('/admin/video/types/delete', {id: row.id}, function(){
          layer.close(index);
          table.reload('video-type-table');
          layer.msg('删除成功', {icon:1});
        });
      });
    }
  });

  loadParentOptions();
});
</script>
</body>
</html>
