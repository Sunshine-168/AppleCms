<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>{{ conf('name') }} - 管理员列表</title>
  <meta name="renderer" content="webkit">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="stylesheet" href="{{ asset('static/admin/layui/css/layui.css') }}">
  <link rel="stylesheet" href="{{ asset('static/admin/style/admin.css') }}">
</head>
<body>

<div class="layui-fluid">
  <!-- 搜索表单 -->
  <div class="layui-card">
    <div class="layui-card-body">
      <form class="layui-form" id="sysuser-search">
        <div class="layui-form-item">
          <div class="layui-inline">
            <input type="text" name="username" placeholder="用户名" autocomplete="off" class="layui-input">
          </div>
          <div class="layui-inline">
            <button type="button" class="layui-btn" id="sysuser-search-btn">查询</button>
            <button type="reset" class="layui-btn layui-btn-primary">重置</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- 表格 -->
  <div class="layui-card">
    <div class="layui-card-body">
      <div class="layui-btn-container" style="margin-bottom:10px;">
        <button class="layui-btn layui-btn-sm" id="sysuser-add-btn">新增管理员</button>
        <button class="layui-btn layui-btn-sm layui-btn-primary" id="sysuser-refresh-btn">刷新</button>
      </div>
      <table class="layui-table" id="sysuser-table" lay-filter="sysuser-table"></table>

      <!-- 行操作模板 -->
      <script type="text/html" id="sysuser-rowbar">
        <a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>
        <a class="layui-btn layui-btn-xs layui-btn-danger" lay-event="del">删除</a>
      </script>
    </div>
  </div>
</div>

<!-- 弹窗模板 -->
<div id="sysuser-dialog-tpl" style="display:none;">
  <div style="padding:20px">
    <form class="layui-form layui-form-pane">
      <input type="hidden" name="id">
      <div class="layui-form-item">
        <label class="layui-form-label">用户名</label>
        <div class="layui-input-block">
          <input type="text" name="username" required class="layui-input">
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">邮箱</label>
        <div class="layui-input-block">
          <input type="text" name="email" class="layui-input">
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">备注</label>
        <div class="layui-input-block">
          <input type="text" name="remark" class="layui-input">
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">角色</label>
        <div class="layui-input-block">
          <select name="role">
            <option value="0">超级管理员</option>
            <option value="1">普通管理员</option>
          </select>
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">密码</label>
        <div class="layui-input-block">
          <input type="password" name="password" autocomplete="new-password" class="layui-input">
        </div>
      </div>
    </form>
  </div>
</div>

<script src="{{ asset('static/admin/layui/layui.js') }}"></script>
<script>
layui.use(['layer','form','table'], function(){
  var $ = layui.$,
      layer = layui.layer,
      form = layui.form,
      table = layui.table;

  var csrfToken = $('meta[name=csrf-token]').attr('content');
  $.ajaxSetup({ headers: {'X-CSRF-TOKEN': csrfToken} });

  function escapeHtml(value){
    return String(value||'').replace(/[&<>"']/g,function(s){
      return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[s];
    });
  }

  function apiPost(url, data, callback){
    $.post(url, data, function(res){
      if(res.code === 0){
        callback && callback(res);
      } else {
        layer.msg(res.msg || '操作失败',{icon:2});
      }
    },'json');
  }

  // 打开新增/编辑弹窗
  function openUserDialog(mode,row){
    row = row||{};
    var isEdit = mode==='edit';
    var content = $('#sysuser-dialog-tpl').html();

    layer.open({
      type:1,
      title: isEdit?'编辑管理员':'新增管理员',
      area:['480px','420px'],
      content:content,
      btn:['保存','取消'],
      success:function(layero){
        var $layer = $(layero);
        $layer.find('input[name=id]').val(row.id||'');
        $layer.find('input[name=username]').val(row.username||'');
        $layer.find('input[name=email]').val(row.email||'');
        $layer.find('input[name=remark]').val(row.remark||'');
        $layer.find('select[name=role]').val(row.role||1);
        form.render();
      },
      yes:function(index, layero){
        var $layer = $(layero);
        var id = $layer.find('input[name=id]').val();
        var username = $.trim($layer.find('input[name=username]').val());
        var password = $.trim($layer.find('input[name=password]').val());
        var email = $.trim($layer.find('input[name=email]').val());
        var remark = $.trim($layer.find('input[name=remark]').val());
        var role = $layer.find('select[name=role]').val();

        if(!username){layer.msg('请输入用户名');return;}

        if(isEdit){
          var data = {id:id,username:username,email:email,remark:remark,role:role};
          if(password) data.password = password;
          apiPost('/admin/user/update', data, function(){ layer.close(index); table.reload('sysuser-table'); layer.msg('保存成功',{icon:1}); });
        } else {
          apiPost('/admin/user/add',{username:username,password:password||'123456',email:email,remark:remark,role:role}, function(){ layer.close(index); table.reload('sysuser-table'); layer.msg('新增成功',{icon:1}); });
        }
      }
    });
  }

  // 渲染表格
  var tableIns = table.render({
    elem:'#sysuser-table',
    url:'/admin/user/list',
    method:'get',
    page:true,
    parseData:function(res){
      return {
        code: res.code,
        msg: res.msg,
        count: res.data.total||0,
        data: res.data.data||[]
      };
    },
    cols:[[
      {field:'id',width:80,title:'ID',sort:true},
      {field:'username',title:'用户名'},
      {field:'email',title:'邮箱'},
      {field:'remark',title:'备注'},
      {field:'role',width:120,title:'角色',templet:function(d){return d.role==0?'<span class="layui-badge layui-bg-blue">超级管理员</span>':'<span class="layui-badge layui-bg-gray">普通管理员</span>';}},
      {field:'login_ip',width:140,title:'登录IP'},
      {field:'login_time',width:180,title:'登录时间'},
      {title:'操作',toolbar:'#sysuser-rowbar',width:150}
    ]]
  });

  // 搜索
  $('#sysuser-search-btn').on('click', function(){
    table.reload('sysuser-table',{where:$('#sysuser-search').serializeJSON(),page:{curr:1}});
  });

  // 刷新
  $('#sysuser-refresh-btn').on('click', function(){ table.reload('sysuser-table'); });

  // 新增
  $('#sysuser-add-btn').on('click', function(){ openUserDialog('add'); });

  // 编辑/删除
  table.on('tool(sysuser-table)', function(obj){
    var data = obj.data;
    if(obj.event==='edit'){ openUserDialog('edit', data); }
    if(obj.event==='del'){
      layer.confirm('确定删除该管理员吗？', function(index){
        apiPost('/admin/user/delete',{id:data.id}, function(){ layer.close(index); table.reload('sysuser-table'); layer.msg('删除成功',{icon:1}); });
      });
    }
  });

});
</script>
</body>
</html>