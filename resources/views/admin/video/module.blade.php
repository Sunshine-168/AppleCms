<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>{{ conf('name') }} - {{ $title }}</title>
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
        <button class="layui-btn layui-btn-sm" id="mod-add">新增</button>
        <button class="layui-btn layui-btn-sm layui-btn-primary" id="mod-refresh">刷新</button>
        @if($module === 'cards')
          <button class="layui-btn layui-btn-sm layui-btn-normal" id="mod-gen">批量生成</button>
        @endif
      </div>
      <table id="mod-table" lay-filter="mod-table"></table>
    </div>
  </div>
</div>
<script type="text/html" id="mod-rowbar">
  <a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>
  @if($module === 'topics')
  <a class="layui-btn layui-btn-xs layui-btn-normal" lay-event="bind">绑片</a>
  @endif
  @if($module === 'collect_tasks')
  <a class="layui-btn layui-btn-xs layui-btn-normal" lay-event="run">执行</a>
  @endif
  <a class="layui-btn layui-btn-xs layui-btn-danger" lay-event="del">删除</a>
</script>
<div id="mod-dialog" style="display:none;">
  <div style="padding:16px 16px 0 0;">
    <form class="layui-form layui-form-pane" lay-filter="mod-form">
      <input type="hidden" name="id">
      @foreach($fields as $field)
        <div class="layui-form-item">
          <label class="layui-form-label">{{ $field['label'] }}</label>
          <div class="layui-input-block">
            @if(($field['type'] ?? 'text') === 'textarea')
              <textarea name="{{ $field['name'] }}" class="layui-textarea"></textarea>
            @elseif(($field['type'] ?? '') === 'select')
              <select name="{{ $field['name'] }}">
                @foreach(($field['options'] ?? []) as $val => $lab)
                  <option value="{{ $val }}">{{ $lab }}</option>
                @endforeach
              </select>
            @else
              <input type="{{ ($field['type'] ?? 'text') === 'number' ? 'number' : 'text' }}" name="{{ $field['name'] }}" class="layui-input">
            @endif
          </div>
        </div>
      @endforeach
    </form>
  </div>
</div>
<script src="{{ asset('static/admin/layui/layui.js') }}"></script>
<script>
layui.use(['layer','form','table'], function(){
  var $ = layui.$, layer = layui.layer, form = layui.form, table = layui.table;
  var csrf = $('meta[name=csrf-token]').attr('content');
  if (csrf) { $.ajaxSetup({headers:{'X-CSRF-TOKEN': csrf}}); }
  var module = @json($module);
  var cols = @json($cols);
  var fields = @json($fields);
  var tableCols = [{field:'id', width:70, title:'ID', sort:true}];
  cols.forEach(function(c){
    if (c === 'id') return;
    tableCols.push({field:c, title:c, minWidth:120});
  });
  tableCols.push({title:'操作', toolbar:'#mod-rowbar', width: {{ in_array($module, ['topics','collect_tasks'], true) ? 220 : 150 }}});
  table.render({
    elem:'#mod-table', id:'mod-table', url:'/admin/video/'+module+'/list', page:true,
    parseData:function(res){
      var data = res.data || {};
      return {code:res.code, msg:res.msg, count:data.total||0, data:data.data||[]};
    },
    cols:[tableCols]
  });
  function open(row){
    row = row || {};
    var idx = layer.open({
      type:1, title: row.id ? '编辑' : '新增', area:['640px','70%'],
      content: $('#mod-dialog').html(), btn:['保存','取消'],
      success:function(){
        var val = {id: row.id || ''};
        fields.forEach(function(f){ val[f.name] = row[f.name] == null ? '' : row[f.name]; });
        form.val('mod-form', val); form.render();
      },
      yes:function(){
        var data = {};
        $('.layui-layer-content [lay-filter=mod-form]').serializeArray().forEach(function(it){ data[it.name]=it.value; });
        $.post('/admin/video/'+module+'/save', data, function(res){
          if(res && res.code===0){ layer.close(idx); table.reload('mod-table'); layer.msg('保存成功',{icon:1}); }
          else { layer.msg((res&&res.msg)||'失败',{icon:2}); }
        },'json');
      }
    });
  }
  $('#mod-add').on('click', function(){ open({}); });
  $('#mod-refresh').on('click', function(){ table.reload('mod-table'); });
  $('#mod-gen').on('click', function(){
    layer.prompt({title:'数量,积分', value:'10,100'}, function(val, index){
      var parts = String(val||'').split(/[,，\s]+/);
      layer.close(index);
      $.post('/admin/video/cards/generate', {count: parts[0]||10, points: parts[1]||100}, function(res){
        if(res && res.code===0){ table.reload('mod-table'); layer.msg(res.msg||'已生成',{icon:1}); }
        else { layer.msg((res&&res.msg)||'失败',{icon:2}); }
      },'json');
    });
  });
  table.on('tool(mod-table)', function(obj){
    if(obj.event==='edit'){ open(obj.data||{}); }
    if(obj.event==='bind'){
      $.get('/admin/video/topics/'+obj.data.id+'/videos', function(res){
        var ids = (res.data && res.data.video_ids) ? res.data.video_ids : '';
        layer.prompt({title:'影片ID，逗号分隔', value: ids, formType:2}, function(val, index){
          $.post('/admin/video/topics/'+obj.data.id+'/videos', {video_ids: val}, function(r){
            layer.close(index);
            layer.msg((r&&r.msg)||'完成', {icon:(r&&r.code===0)?1:2});
          },'json');
        });
      },'json');
    }
    if(obj.event==='run'){
      $.post('/admin/video/collect_tasks/run', {id: obj.data.id}, function(r){
        table.reload('mod-table');
        layer.msg((r&&r.msg)||'完成', {icon:(r&&r.code===0)?1:2});
      },'json');
    }
    if(obj.event==='del'){
      layer.confirm('确认删除？', function(i){
        $.post('/admin/video/'+module+'/delete', {id: obj.data.id}, function(res){
          layer.close(i);
          if(res && res.code===0){ table.reload('mod-table'); layer.msg('已删除',{icon:1}); }
          else { layer.msg((res&&res.msg)||'失败',{icon:2}); }
        },'json');
      });
    }
  });
});
</script>
</body>
</html>
