<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>{{ conf('name') }} - 剧集管理</title>
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
      <div class="layui-btn-container" style="margin-bottom:10px;">
        <button class="layui-btn layui-btn-sm" id="episode-add-btn">新增剧集</button>
        <button class="layui-btn layui-btn-sm layui-btn-primary" id="episode-refresh-btn">刷新</button>
      </div>
      <table id="episode-table" lay-filter="episode-table"></table>
    </div>
  </div>
</div>

<script type="text/html" id="episode-rowbar">
  <a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>
  <a class="layui-btn layui-btn-xs layui-btn-danger" lay-event="del">删除</a>
</script>

<script type="text/html" id="episode-dialog-tpl">
  <div style="padding:15px;">
    <form class="layui-form" id="episode-form" lay-filter="episode-form">
      <input type="hidden" name="id" value="">
      <input type="hidden" name="source_id" value="{{ (int)($sourceId ?? 0) }}">
      <div class="layui-form-item">
        <div class="layui-inline">
          <label class="layui-form-label">集序号</label>
          <div class="layui-input-inline">
            <input type="number" name="episode_num" value="1" autocomplete="off" class="layui-input">
          </div>
        </div>
        <div class="layui-inline">
          <label class="layui-form-label">标题</label>
          <div class="layui-input-inline">
            <input type="text" name="episode_name" autocomplete="off" class="layui-input">
          </div>
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">地址</label>
        <div class="layui-input-block">
          <input type="text" name="url" autocomplete="off" class="layui-input">
        </div>
      </div>
      <div class="layui-form-item">
        <div class="layui-inline">
          <label class="layui-form-label">时长</label>
          <div class="layui-input-inline">
            <input type="number" name="duration" value="0" autocomplete="off" class="layui-input">
          </div>
        </div>
        <div class="layui-inline">
          <label class="layui-form-label">状态</label>
          <div class="layui-input-inline">
            <select name="status">
              <option value="1">可用</option>
              <option value="0">不可用</option>
            </select>
          </div>
        </div>
        <div class="layui-inline">
          <label class="layui-form-label">排序</label>
          <div class="layui-input-inline">
            <input type="number" name="sort" value="0" autocomplete="off" class="layui-input">
          </div>
        </div>
      </div>
    </form>
  </div>
</script>

<script src="{{ asset('static/admin/layui/layui.js') }}"></script>
<script>
layui.use(['layer','form','table'], function(){
  var $ = layui.$, layer = layui.layer, form = layui.form, table = layui.table;
  var sourceId = {{ (int)($sourceId ?? 0) }};

  var csrfToken = $('meta[name=csrf-token]').attr('content');
  if (csrfToken) {
    $.ajaxSetup({ headers: {'X-CSRF-TOKEN': csrfToken} });
  }

  function apiPost(url, data, ok){
    $.post(url, data || {}, function(res){
      if(res && res.code === 0){
        ok && ok(res);
      } else {
        layer.msg(res && res.msg ? res.msg : '操作失败', {icon:2});
      }
    }, 'json').fail(function(){ layer.msg('请求失败', {icon:2}); });
  }

  function formToObj($form){
    var arr = $form.serializeArray();
    var obj = {};
    for(var i=0;i<arr.length;i++){
      obj[arr[i].name] = arr[i].value;
    }
    return obj;
  }

  var tableIns = table.render({
    elem:'#episode-table',
    url:'/admin/video/episodes/list',
    method:'get',
    where:{source_id:sourceId},
    page:true,
    parseData:function(res){
      return {
        code: res.code,
        msg: res.msg,
        count: res.data.total || 0,
        data: res.data.data || []
      };
    },
    cols:[[
      {field:'id', width:80, title:'ID', sort:true},
      {field:'episode_num', width:90, title:'集'},
      {field:'episode_name', width:160, title:'标题'},
      {field:'url', title:'地址', minWidth:260},
      {field:'duration', width:90, title:'时长'},
      {field:'status', width:90, title:'状态', templet:function(d){
        return String(d.status) === '1' ? '<span class="layui-badge layui-bg-green">可用</span>' : '<span class="layui-badge">不可用</span>';
      }},
      {field:'sort', width:90, title:'排序'},
      {field:'updated_at_text', width:180, title:'更新时间'},
      {title:'操作', toolbar:'#episode-rowbar', width:140}
    ]]
  });

  function openEpisodeDialog(mode, row){
    row = row || {};
    var isEdit = mode === 'edit';
    layer.open({
      type:1,
      title: isEdit ? '编辑剧集' : '新增剧集',
      area:['760px','420px'],
      content: $('#episode-dialog-tpl').html(),
      btn:['保存','取消'],
      success:function(layero){
        var $layer = $(layero);
        var $form = $layer.find('#episode-form');
        $form.find('input[name=id]').val(isEdit ? (row.id || '') : '');
        $form.find('input[name=source_id]').val(String(sourceId));
        $form.find('input[name=episode_num]').val(row.episode_num == null ? 1 : row.episode_num);
        $form.find('input[name=episode_name]').val(row.episode_name || '');
        $form.find('input[name=url]').val(row.url || '');
        $form.find('input[name=duration]').val(row.duration == null ? 0 : row.duration);
        $form.find('select[name=status]').val(String(row.status == null ? 1 : row.status));
        $form.find('input[name=sort]').val(row.sort == null ? 0 : row.sort);
        form.render();
      },
      yes:function(index, layero){
        var data = formToObj($(layero).find('#episode-form'));
        if(!data.url){ layer.msg('请输入播放地址',{icon:2}); return; }
        apiPost('/admin/video/episodes/save', data, function(){
          layer.close(index);
          table.reload('episode-table');
          layer.msg('保存成功',{icon:1});
        });
      }
    });
  }

  $('#episode-add-btn').on('click', function(){ openEpisodeDialog('add'); });
  $('#episode-refresh-btn').on('click', function(){ table.reload('episode-table'); });

  table.on('tool(episode-table)', function(obj){
    var row = obj.data || {};
    if(obj.event === 'edit'){ openEpisodeDialog('edit', row); }
    if(obj.event === 'del'){
      layer.confirm('确定删除该剧集吗？', function(i){
        apiPost('/admin/video/episodes/delete', {id: row.id}, function(){
          layer.close(i);
          table.reload('episode-table');
          layer.msg('删除成功',{icon:1});
        });
      });
    }
  });
});
</script>
</body>
</html>

