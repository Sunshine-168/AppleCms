<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>{{ conf('name') }} - 采集源管理</title>
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
      <form class="layui-form" id="collect-source-search">
        <div class="layui-form-item">
          <div class="layui-inline">
            <input type="text" name="name" placeholder="采集源名称" autocomplete="off" class="layui-input">
          </div>
          <div class="layui-inline" style="width: 140px;">
            <select name="status">
              <option value="">全部状态</option>
              <option value="1">启用</option>
              <option value="0">禁用</option>
            </select>
          </div>
          <div class="layui-inline">
            <button type="button" class="layui-btn" id="collect-source-search-btn">查询</button>
            <button type="reset" class="layui-btn layui-btn-primary" id="collect-source-reset-btn">重置</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <div class="layui-card">
    <div class="layui-card-body">
      <div class="layui-btn-container" style="margin-bottom:10px;">
        <button class="layui-btn layui-btn-sm" id="collect-source-add-btn">新增采集源</button>
        <button class="layui-btn layui-btn-sm layui-btn-primary" id="collect-source-refresh-btn">刷新</button>
      </div>
      <table class="layui-table" id="collect-source-table" lay-filter="collect-source-table"></table>

      <script type="text/html" id="collect-source-rowbar">
        <a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>
        <a class="layui-btn layui-btn-xs layui-btn-normal" lay-event="bind">绑定</a>
        <a class="layui-btn layui-btn-xs layui-btn-warm" lay-event="run">采集</a>
        <a class="layui-btn layui-btn-xs layui-btn-danger" lay-event="del">删除</a>
      </script>
    </div>
  </div>
</div>

<div id="collect-source-dialog-tpl" style="display:none;">
  <div style="padding:18px 18px 0 0;">
    <form class="layui-form layui-form-pane" lay-filter="collect-source-form">
      <input type="hidden" name="id">
      <div class="layui-form-item">
        <label class="layui-form-label">名称</label>
        <div class="layui-input-block">
          <input type="text" name="name" autocomplete="off" class="layui-input">
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">接口地址</label>
        <div class="layui-input-block">
          <input type="text" name="api_url" autocomplete="off" class="layui-input" placeholder="https://xxx/api.php/provide/vod/">
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">格式</label>
        <div class="layui-input-block">
          <select name="api_type">
            <option value="auto">自动</option>
            <option value="json">JSON</option>
            <option value="xml">XML</option>
          </select>
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">附加参数</label>
        <div class="layui-input-block">
          <input type="text" name="param" autocomplete="off" class="layui-input" placeholder="ac=list 以外的固定参数">
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

  table.render({
    elem:'#collect-source-table',
    id: 'collect-source-table',
    url:'/admin/video/collects/list',
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
      {field:'name',title:'名称',minWidth:140},
      {field:'api_url',title:'接口',minWidth:220},
      {field:'api_type',title:'格式',width:80},
      {field:'status',title:'状态',width:90,templet:function(d){
        if (typeof d.status === 'undefined' || d.status === null || d.status === '') { return '-'; }
        return String(d.status) === '1'
          ? '<span class="layui-badge layui-bg-green">启用</span>'
          : '<span class="layui-badge layui-bg-gray">禁用</span>';
      }},
      {field:'sort',title:'排序',width:90,sort:true},
      {field:'created_at_text',title:'创建时间',width:180},
      {field:'updated_at_text',title:'更新时间',width:180},
      {title:'操作',toolbar:'#collect-source-rowbar',width:260}
    ]]
  });

  function openDialog(mode, row){
    row = row || {};
    var isEdit = mode === 'edit';
    var title = isEdit ? '编辑采集源' : '新增采集源';

    var idx = layer.open({
      type: 1,
      title: title,
      area: ['640px', '520px'],
      content: $('#collect-source-dialog-tpl').html(),
      btn: ['保存', '取消'],
      success: function(){
        form.val('collect-source-form', {
          id: row.id || '',
          name: row.name || '',
          api_url: row.api_url || '',
          api_type: row.api_type || 'auto',
          param: row.param || '',
          status: (row.status !== undefined && row.status !== null) ? String(row.status) : '1',
          sort: row.sort !== undefined ? row.sort : 0
        });
        form.render();
      },
      yes: function(){
        var $form = $('.layui-layer-content').find('form[lay-filter="collect-source-form"]');
        var data = formToObj($form);
        if (isEdit) {
          data.id = row.id;
        } else {
          delete data.id;
        }
        apiPost('/admin/video/collects/save', data, function(){
          layer.close(idx);
          table.reload('collect-source-table');
          layer.msg('保存成功', {icon:1});
        });
      }
    });
  }

  $('#collect-source-search-btn').on('click', function(){
    table.reload('collect-source-table', {where: formToObj($('#collect-source-search')), page: {curr: 1}});
  });

  $('#collect-source-refresh-btn').on('click', function(){
    table.reload('collect-source-table');
  });

  $('#collect-source-add-btn').on('click', function(){
    openDialog('add', {});
  });

  table.on('tool(collect-source-table)', function(obj){
    var row = obj.data || {};
    if (obj.event === 'edit') {
      openDialog('edit', row);
      return;
    }
    if (obj.event === 'bind') {
      apiGet('/admin/video/collects/classes', {id: row.id}, function(data){
        var types = data.types || [];
        var locals = data.local_types || [];
        var html = '<div style="padding:12px;max-height:420px;overflow:auto;"><table class="layui-table"><thead><tr><th>资源分类</th><th>绑定本地</th></tr></thead><tbody>';
        types.forEach(function(t){
          html += '<tr><td>'+ escapeHtml(t.name) +' ('+ t.remote_id +')</td><td><select data-remote="'+ t.remote_id +'"><option value="0">不采集</option>';
          locals.forEach(function(l){
            var sel = String(l.id) === String(t.local_id) ? ' selected' : '';
            html += '<option value="'+ l.id +'"'+ sel +'>'+ escapeHtml(l.name) +'</option>';
          });
          html += '</select></td></tr>';
        });
        html += '</tbody></table></div>';
        layer.open({
          type:1, title:'绑定分类 - '+ escapeHtml(row.name||''), area:['560px','520px'], content: html,
          btn:['保存','取消'],
          yes: function(index, layero){
            var bind = {};
            $(layero).find('select[data-remote]').each(function(){
              bind[$(this).data('remote')] = $(this).val();
            });
            apiPost('/admin/video/collects/bind', {id: row.id, bind: bind}, function(){
              layer.close(index);
              layer.msg('绑定已保存', {icon:1});
            });
          }
        });
      });
      return;
    }
    if (obj.event === 'run') {
      layer.prompt({title:'起始页,采集页数,小时(0全部)', value:'1,1,24', formType:0}, function(val, index){
        layer.close(index);
        var parts = String(val||'1').split(/[,，\s]+/);
        var load = layer.load(1);
        apiPost('/admin/video/collects/run', {
          id: row.id,
          page: parts[0] || 1,
          pages: parts[1] || 1,
          hours: parts[2] || 0
        }, function(data, res){
          layer.close(load);
          table.reload('collect-source-table');
          layer.msg((res && res.msg) ? res.msg : '采集完成', {icon:1, time: 3000});
        });
      });
      return;
    }
    if (obj.event === 'del') {
      layer.confirm('确认删除该采集源？', function(index){
        apiPost('/admin/video/collects/delete', {id: row.id}, function(){
          layer.close(index);
          table.reload('collect-source-table');
          layer.msg('删除成功', {icon:1});
        });
      });
    }
  });
});
</script>
</body>
</html>
