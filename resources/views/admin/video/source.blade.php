<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>{{ conf('name') }} - 线路管理</title>
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
        <button class="layui-btn layui-btn-sm" id="source-add-btn">新增线路</button>
        <button class="layui-btn layui-btn-sm layui-btn-primary" id="source-refresh-btn">刷新</button>
      </div>
      <table id="source-table" lay-filter="source-table"></table>
    </div>
  </div>
</div>

<script type="text/html" id="source-rowbar">
  <a class="layui-btn layui-btn-xs layui-btn-warm" lay-event="episodes">剧集</a>
  <a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>
  <a class="layui-btn layui-btn-xs layui-btn-primary" lay-event="offline">下线</a>
  <a class="layui-btn layui-btn-xs layui-btn-danger" lay-event="del">删除</a>
</script>

<script type="text/html" id="source-dialog-tpl">
  <div style="padding:15px;">
    <form class="layui-form" id="source-form" lay-filter="source-form">
      <input type="hidden" name="id" value="">
      <input type="hidden" name="video_id" value="{{ (int)($videoId ?? 0) }}">
      <div class="layui-form-item">
        <label class="layui-form-label">线路名</label>
        <div class="layui-input-block">
          <input type="text" name="name" autocomplete="off" class="layui-input">
        </div>
      </div>
      <div class="layui-form-item">
        <div class="layui-inline">
          <label class="layui-form-label">类型</label>
          <div class="layui-input-inline">
            <select name="type">
              <option value="m3u8">m3u8</option>
              <option value="mp4">mp4</option>
              <option value="parse">parse</option>
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
  var videoId = {{ (int)($videoId ?? 0) }};

  var csrfToken = $('meta[name=csrf-token]').attr('content');
  if (csrfToken) {
    $.ajaxSetup({ headers: {'X-CSRF-TOKEN': csrfToken} });
  }

  function escapeHtml(value){
    return String(value||'').replace(/[&<>"']/g,function(s){
      return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[s];
    });
  }

  function apiGet(url, data, ok){
    $.get(url, data || {}, function(res){
      if(res && res.code === 0){
        ok && ok(res);
      } else {
        layer.msg(res && res.msg ? res.msg : '请求失败', {icon:2});
      }
    }, 'json').fail(function(){ layer.msg('请求失败', {icon:2}); });
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

  function getQueryParam(key){
    var search = String(window.location.search || '').replace(/^\?/, '');
    if(!search){ return ''; }
    var parts = search.split('&');
    for(var i=0;i<parts.length;i++){
      var kv = parts[i].split('=');
      if(decodeURIComponent(kv[0] || '') === key){
        return decodeURIComponent(kv[1] || '');
      }
    }
    return '';
  }

  var openEpisodeOnce = getQueryParam('open_episode') === '1';

  var tableIns = table.render({
    elem:'#source-table',
    url:'/admin/video/sources/list',
    method:'get',
    where:{video_id:videoId},
    page:true,
    parseData:function(res){
      return {
        code: res.code,
        msg: res.msg,
        count: res.data.total || 0,
        data: res.data.data || []
      };
    },
    done:function(res){
      if(!openEpisodeOnce){ return; }
      openEpisodeOnce = false;
      var rows = res && res.data ? res.data : [];
      if(!rows || !rows.length){ return; }
      if(rows.length === 1){
        var first = rows[0] || {};
        if(!first.id){ return; }
        layer.open({
          type:2,
          title:'剧集管理 - ' + escapeHtml(first.name || ''),
          area:['95%','95%'],
          maxmin:true,
          content:'/admin/video/episodes?source_id=' + encodeURIComponent(first.id)
        });
        return;
      }

      var html = '<div style="padding:15px;">';
      html += '<table id="choose-source-table" lay-filter="choose-source-table"></table>';
      html += '</div>';

      layer.open({
        type:1,
        title:'选择线路',
        area:['760px','520px'],
        content: html,
        success:function(layero, index){
          table.render({
            elem: $(layero).find('#choose-source-table'),
            data: rows,
            page: false,
            limit: 200,
            cols: [[
              {field:'id', width:80, title:'ID', sort:true},
              {field:'name', title:'线路名', minWidth:220},
              {field:'type', width:100, title:'类型'},
              {field:'episode_total', width:110, title:'剧集数'},
              {field:'sort', width:90, title:'排序'},
              {field:'updated_at_text', width:180, title:'更新时间'},
              {title:'操作', width:100, align:'center', templet:function(){
                return '<a class="layui-btn layui-btn-xs layui-btn-normal" lay-event="choose">选择</a>';
              }}
            ]]
          });

          table.on('tool(choose-source-table)', function(obj){
            if(obj.event !== 'choose'){ return; }
            var r = obj.data || {};
            if(!r.id){ return; }
            layer.close(index);
            layer.open({
              type:2,
              title:'剧集管理 - ' + escapeHtml(r.name || ''),
              area:['95%','95%'],
              maxmin:true,
              content:'/admin/video/episodes?source_id=' + encodeURIComponent(r.id)
            });
          });
        }
      });
    },
    cols:[[
      {field:'id', width:80, title:'ID', sort:true},
      {field:'name', title:'线路名', minWidth:180},
      {field:'type', width:100, title:'类型'},
      {field:'episode_total', width:110, title:'剧集数'},
      {field:'sort', width:90, title:'排序'},
      {field:'updated_at_text', width:180, title:'更新时间'},
      {title:'操作', toolbar:'#source-rowbar', width:260}
    ]]
  });

  function openSourceDialog(mode, row){
    row = row || {};
    var isEdit = mode === 'edit';
    layer.open({
      type:1,
      title: isEdit ? '编辑线路' : '新增线路',
      area:['520px','320px'],
      content: $('#source-dialog-tpl').html(),
      btn:['保存','取消'],
      success:function(layero){
        var $layer = $(layero);
        var $form = $layer.find('#source-form');
        $form.find('input[name=id]').val(isEdit ? (row.id || '') : '');
        $form.find('input[name=video_id]').val(String(videoId));
        $form.find('input[name=name]').val(row.name || '');
        $form.find('input[name=sort]').val(row.sort == null ? 0 : row.sort);
        $form.find('select[name=type]').val(String(row.type || 'm3u8'));
        form.render();
      },
      yes:function(index, layero){
        var data = formToObj($(layero).find('#source-form'));
        if(!data.name){ layer.msg('请输入线路名',{icon:2}); return; }
        apiPost('/admin/video/sources/save', data, function(){
          layer.close(index);
          table.reload('source-table');
          layer.msg('保存成功',{icon:1});
        });
      }
    });
  }

  $('#source-add-btn').on('click', function(){ openSourceDialog('add'); });
  $('#source-refresh-btn').on('click', function(){ table.reload('source-table'); });

  table.on('tool(source-table)', function(obj){
    var row = obj.data || {};
    if(obj.event === 'edit'){ openSourceDialog('edit', row); }
    if(obj.event === 'offline'){
      layer.confirm('确认下线该线路？前台将不再播放。', function(i){
        apiPost('/admin/video/sources/disable', {id: row.id}, function(){
          layer.close(i);
          table.reload('source-table');
          layer.msg('已下线',{icon:1});
        });
      });
    }
    if(obj.event === 'del'){
      layer.confirm('确定删除该线路吗？', function(i){
        apiPost('/admin/video/sources/delete', {id: row.id}, function(){
          layer.close(i);
          table.reload('source-table');
          layer.msg('删除成功',{icon:1});
        });
      });
    }
    if(obj.event === 'episodes'){
      layer.open({
        type:2,
        title:'剧集管理 - ' + escapeHtml(row.name || ''),
        area:['95%','95%'],
        maxmin:true,
        content:'/admin/video/episodes?source_id=' + encodeURIComponent(row.id)
      });
    }
  });
});
</script>
</body>
</html>
