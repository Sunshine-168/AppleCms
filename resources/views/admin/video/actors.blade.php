<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>{{ conf('name') }} - 演员管理</title>
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
      <form class="layui-form" id="actor-search">
        <div class="layui-form-item">
          <div class="layui-inline">
            <input type="text" name="name" placeholder="演员名称" autocomplete="off" class="layui-input">
          </div>
          <div class="layui-inline">
            <button type="button" class="layui-btn" id="actor-search-btn">查询</button>
            <button type="reset" class="layui-btn layui-btn-primary" id="actor-reset-btn">重置</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <div class="layui-card">
    <div class="layui-card-body">
      <div class="layui-btn-container" style="margin-bottom:10px;">
        <button class="layui-btn layui-btn-sm" id="actor-add-btn">新增演员</button>
        <button class="layui-btn layui-btn-sm layui-btn-primary" id="actor-refresh-btn">刷新</button>
      </div>
      <table class="layui-table" id="actor-table" lay-filter="actor-table"></table>

      <script type="text/html" id="actor-rowbar">
        <a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>
        <a class="layui-btn layui-btn-xs layui-btn-danger" lay-event="del">删除</a>
      </script>
    </div>
  </div>
</div>

<div id="actor-dialog-tpl" style="display:none;">
  <div style="padding:18px 18px 0 0;">
    <form class="layui-form layui-form-pane" lay-filter="actor-form">
      <input type="hidden" name="id">
      <div class="layui-form-item">
        <label class="layui-form-label">名称</label>
        <div class="layui-input-block">
          <input type="text" name="name" autocomplete="off" class="layui-input">
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">头像</label>
        <div class="layui-input-block">
          <div class="layui-input-inline" style="width: calc(100% - 92px);">
            <input type="text" name="avatar" autocomplete="off" class="layui-input actor-avatar-input" placeholder="图片URL">
          </div>
          <div class="layui-input-inline" style="width: 80px;">
            <button type="button" class="layui-btn layui-btn-primary actor-avatar-upload-btn">上传</button>
          </div>
          <div style="padding-top: 8px;">
            <img class="actor-avatar-preview" src="" style="max-width: 80px; max-height: 80px; border-radius: 4px; display: none;">
          </div>
        </div>
      </div>
  
    </form>
  </div>
</div>

<script src="{{ asset('static/admin/layui/layui.all.js') }}"></script>
<script>
if (!window.layui) {
  var el = document.getElementById('actor-table');
  if (el) {
    el.outerHTML = '<div style="padding:12px;color:#FF5722;">静态资源加载失败：layui 未加载</div>';
  }
} else {
layui.use(['layer','form','table','upload'], function(){
  var $ = layui.$, layer = layui.layer, form = layui.form, table = layui.table, upload = layui.upload;

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
    elem:'#actor-table',
    id: 'actor-table',
    url:'/admin/video/actors/list',
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
      {field:'avatar',title:'头像',width:90,align:'center',templet:function(d){
        var url = d.avatar || '';
        if (!url) { return '-'; }
        return '<img src="'+escapeHtml(url)+'" style="width:32px;height:32px;border-radius:50%;object-fit:cover;" onerror="this.style.display=\'none\'">';
      }},
      {field:'created_at_text',title:'创建时间',width:180},
      {title:'操作',toolbar:'#actor-rowbar',width:150}
    ]]
  });

  function openDialog(mode, row){
    row = row || {};
    var isEdit = mode === 'edit';
    var title = isEdit ? '编辑演员' : '新增演员';

    layer.open({
      type: 1,
      title: title,
      area: ['560px', '420px'],
      content: $('#actor-dialog-tpl').html(),
      btn: ['保存', '取消'],
      success: function(layero){
        var $layer = $(layero);
        var $avatarInput = $layer.find('input[name=avatar]');
        var $preview = $layer.find('.actor-avatar-preview');
        var $uploadBtn = $layer.find('.actor-avatar-upload-btn');

        function syncPreview(url){
          url = $.trim(url || '');
          if (url) {
            $preview.attr('src', url).show();
          } else {
            $preview.hide().attr('src', '');
          }
        }

        form.val('actor-form', {
          id: row.id || '',
          name: row.name || '',
          avatar: row.avatar || '',
        });
        form.render();

        syncPreview($avatarInput.val());
        $avatarInput.off('input.actor').on('input.actor', function(){
          syncPreview(this.value);
        });

        if ($uploadBtn.length && !$uploadBtn.data('uploadInited')) {
          $uploadBtn.data('uploadInited', true);
          upload.render({
            elem: $uploadBtn[0],
            url: '/admin/system/attachments/upload',
            field: 'file',
            accept: 'images',
            acceptMime: 'image/*',
            exts: 'jpg|jpeg|png|gif|webp',
            size: 10240,
            headers: csrfToken ? {'X-CSRF-TOKEN': csrfToken} : {},
            data: csrfToken ? {_token: csrfToken} : {},
            before: function(obj){
              obj.preview(function(index, file, result){
                $preview.attr('src', result).show();
              });
              layer.load(1, {shade: 0.15});
            },
            done: function(res){
              layer.closeAll('loading');
              if (res && res.code === 0) {
                var url = res.data && res.data.url ? String(res.data.url) : '';
                if (url) {
                  $avatarInput.val(url);
                  syncPreview(url);
                  layer.msg('上传成功', {icon: 1});
                  return;
                }
                layer.msg('上传成功，但未返回URL', {icon: 2});
                return;
              }
              layer.msg(res && res.msg ? res.msg : '上传失败', {icon: 2});
            },
            error: function(){
              layer.closeAll('loading');
              layer.msg('上传失败', {icon: 2});
            }
          });
        }
      },
      yes: function(index, layero){
        var $layer = $(layero);
        var $form = $layer.find('form[lay-filter="actor-form"]');
        var data = formToObj($form);
        if (isEdit) {
          data.id = row.id;
        } else {
          delete data.id;
        }
        apiPost('/admin/video/actors/save', data, function(){
          layer.close(index);
          table.reload('actor-table');
          layer.msg('保存成功', {icon:1});
        });
      }
    });
  }

  $('#actor-search-btn').on('click', function(){
    table.reload('actor-table', {where: formToObj($('#actor-search')), page: {curr: 1}});
  });

  $('#actor-refresh-btn').on('click', function(){
    table.reload('actor-table');
  });

  $('#actor-add-btn').on('click', function(){
    openDialog('add', {});
  });

  table.on('tool(actor-table)', function(obj){
    var row = obj.data || {};
    if (obj.event === 'edit') {
      openDialog('edit', row);
      return;
    }
    if (obj.event === 'del') {
      layer.confirm('确认删除该演员？', function(index){
        apiPost('/admin/video/actors/delete', {id: row.id}, function(){
          layer.close(index);
          table.reload('actor-table');
          layer.msg('删除成功', {icon:1});
        });
      });
    }
  });
});
}
</script>
</body>
</html>
