<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>{{ conf('name') }} - 菜单管理</title>
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
      <form class="layui-form" lay-filter="menu-search">
        <div class="layui-form-item">
          <div class="layui-inline">
            <input type="text" name="name" placeholder="名称" autocomplete="off" class="layui-input">
          </div>
          <div class="layui-inline">
            <input type="text" name="code" placeholder="标识" autocomplete="off" class="layui-input">
          </div>
          <div class="layui-inline">
            <input type="text" name="api" placeholder="地址" autocomplete="off" class="layui-input">
          </div>
          <div class="layui-inline" style="width: 140px;">
            <select name="type">
              <option value="">全部类型</option>
              <option value="1">菜单</option>
              <option value="2">按钮</option>
              <option value="3">接口</option>
            </select>
          </div>
          <div class="layui-inline">
            <button class="layui-btn" lay-submit lay-filter="menu-search-btn">查询</button>
            <button type="reset" class="layui-btn layui-btn-primary" id="menu-reset-btn">重置</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <div class="layui-card">
    <div class="layui-card-body">
      <table id="menu-table" lay-filter="menu-table"></table>
    </div>
  </div>
</div>

<script type="text/html" id="menu-toolbar">
  <div class="layui-btn-container">
    <button class="layui-btn layui-btn-sm" lay-event="add">新增</button>
    <button class="layui-btn layui-btn-sm layui-btn-primary" lay-event="expandAll">展开全部</button>
    <button class="layui-btn layui-btn-sm layui-btn-primary" lay-event="collapseAll">收起全部</button>
    <button class="layui-btn layui-btn-sm layui-btn-primary" lay-event="refresh">刷新</button>
  </div>
</script>

<script type="text/html" id="menu-actions">
@verbatim
  {{# if(d.type==1){ }}
  <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="addChild">新增子级</a>
  {{# } }}
  <a class="layui-btn layui-btn-primary layui-btn-xs" lay-event="edit">编辑</a>
  <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="delete">删除</a>
@endverbatim
</script>

<script type="text/html" id="menu-name-tpl">
@verbatim
  {{# var p=''; for(var i=0;i<(d.level||0);i++){ p+='— '; } }}
  <i class="layui-icon menu-toggle-icon" data-id="{{d.id}}" style="cursor:pointer;display:inline-block;width:16px;"></i>
  <span>{{ p }}{{ d.name||'' }}</span>
@endverbatim
</script>

<script type="text/html" id="menu-type-tpl">
@verbatim
  {{# if(d.type==1){ }}
    <span class="layui-badge layui-bg-blue">菜单</span>
  {{# } else if(d.type==2){ }}
    <span class="layui-badge layui-bg-green">按钮</span>
  {{# } else { }}
    <span class="layui-badge">接口</span>
  {{# } }}
@endverbatim
</script>

<script type="text/html" id="menu-parent-tpl">
@verbatim
  <span title="PID: {{d.pid}}">{{ d.parent_name||'顶级' }}</span>
@endverbatim
</script>

<script type="text/html" id="menu-code-tpl">
@verbatim
  <span class="menu-copy" data-text="{{d.code||''}}" title="点击复制">{{ d.code||'' }}</span>
@endverbatim
</script>

<script type="text/html" id="menu-api-tpl">
@verbatim
  <span class="menu-copy" data-text="{{d.api||''}}" title="点击复制">{{ d.api||'' }}</span>
@endverbatim
</script>

<script type="text/html" id="menu-method-tpl">
@verbatim
  {{# var m = (d.method||'').toUpperCase(); }}
  {{# if(!m){ }}
    <span>-</span>
  {{# } else if(m==='GET'){ }}
    <span class="layui-badge layui-bg-blue">{{ m }}</span>
  {{# } else if(m==='POST'){ }}
    <span class="layui-badge layui-bg-green">{{ m }}</span>
  {{# } else { }}
    <span class="layui-badge">{{ m }}</span>
  {{# } }}
@endverbatim
</script>

<script type="text/html" id="menu-dialog-tpl">
  <div style="padding:16px 18px 0 0;">
    <form class="layui-form" lay-filter="menu-form">
      <input type="hidden" name="id" value="">
      <div class="layui-form-item">
        <label class="layui-form-label">类型</label>
        <div class="layui-input-block">
          <select name="type" lay-filter="menu-type">
            <option value="1">菜单</option>
            <option value="2">按钮</option>
            <option value="3">接口</option>
          </select>
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">父级</label>
        <div class="layui-input-block">
          <select name="pid" id="menu-pid-select">
            <option value="0">顶级</option>
          </select>
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">名称</label>
        <div class="layui-input-block">
          <input type="text" name="name" autocomplete="off" class="layui-input">
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">地址</label>
        <div class="layui-input-block">
          <input type="text" name="api" autocomplete="off" class="layui-input" placeholder="/admin/system/menus">
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">方法</label>
        <div class="layui-input-block">
          <input type="text" name="method" autocomplete="off" class="layui-input" placeholder="GET/POST">
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">标识</label>
        <div class="layui-input-block">
          <input type="text" name="code" autocomplete="off" class="layui-input" placeholder="为空则根据地址生成">
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">图标</label>
        <div class="layui-input-block">
          <input type="text" name="icon" autocomplete="off" class="layui-input" placeholder="layui-icon-xxx">
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

    function codeFromApi(api) {
      api = String(api || '').trim();
      if (!api) return '';
      if (api.indexOf('route:') === 0) return api;
      api = api.replace(/^https?:\/\/[^/]+/i, '');
      api = api.replace(/^\/+/, '');
      if (!api) return '';
      return api.replace(/\/+/g, '/').replace(/\//g, '.');
    }

    function loadParentOptions(done) {
      $.getJSON('/admin/system/menus/parents', {type: 1}, function (res) {
        if (res && res.code === 0 && res.data) {
          done && done(res.data);
          return;
        }
        done && done([{id: 0, name: '顶级'}]);
      }).fail(function () {
        done && done([{id: 0, name: '顶级'}]);
      });
    }

    function copyText(text) {
      text = String(text || '');
      if (!text) {
        layer.msg('内容为空', {icon: 0});
        return;
      }
      if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(function () {
          layer.msg('已复制', {icon: 1});
        }).catch(function () {
          fallbackCopy(text);
        });
        return;
      }
      fallbackCopy(text);
    }

    function fallbackCopy(text) {
      var $tmp = $('<textarea readonly></textarea>').css({
        position: 'fixed',
        left: '-9999px',
        top: '-9999px',
        opacity: 0
      }).val(text);
      $('body').append($tmp);
      $tmp[0].select();
      try {
        document.execCommand('copy');
        layer.msg('已复制', {icon: 1});
      } catch (e) {
        layer.msg('复制失败', {icon: 2});
      }
      $tmp.remove();
    }

    var collapsedMap = {};
    function applyCollapse(data) {
      var $view = $('.layui-table-view');
      if ($view.length === 0) return;
      var $tbody = $view.find('.layui-table-body tbody');
      if ($tbody.length === 0) return;
      var $rows = $tbody.find('tr');
      if ($rows.length === 0) return;

      var hasChild = [];
      for (var i = 0; i < data.length; i++) {
        var curLevel = parseInt(data[i].level || 0, 10);
        var nextLevel = (i + 1 < data.length) ? parseInt(data[i + 1].level || 0, 10) : -1;
        hasChild[i] = nextLevel > curLevel;
      }

      var stack = [];
      for (var idx = 0; idx < data.length; idx++) {
        var row = data[idx] || {};
        var level = parseInt(row.level || 0, 10);
        while (stack.length > 0 && stack[stack.length - 1].level >= level) {
          stack.pop();
        }

        var hidden = false;
        for (var s = 0; s < stack.length; s++) {
          if (stack[s].collapsed) {
            hidden = true;
            break;
          }
        }

        var $tr = $tbody.find('tr[data-index="' + idx + '"]');
        if (hidden) {
          $tr.hide();
        } else {
          $tr.show();
        }

        var $icon = $tr.find('.menu-toggle-icon[data-id="' + row.id + '"]');
        if (hasChild[idx]) {
          var isCollapsed = !!collapsedMap[row.id];
          $icon.removeClass('layui-icon-triangle-d layui-icon-triangle-r');
          $icon.addClass(isCollapsed ? 'layui-icon-triangle-r' : 'layui-icon-triangle-d');
          $icon.attr('title', isCollapsed ? '展开' : '收起');
        } else {
          $icon.removeClass('layui-icon-triangle-d layui-icon-triangle-r');
          $icon.attr('title', '');
        }

        if (hasChild[idx]) {
          stack.push({level: level, collapsed: !!collapsedMap[row.id]});
        }
      }
    }

    table.render({
      id: 'menu-table',
      elem: '#menu-table',
      url: '/admin/system/menus/list',
      method: 'get',
      toolbar: '#menu-toolbar',
      defaultToolbar: [],
      page: false,
      parseData: function (res) {
        return {
          code: res.code,
          msg: res.msg,
          count: res.data && res.data.total ? res.data.total : 0,
          data: res.data && res.data.data ? res.data.data : []
        };
      },
      cols: [[
        {field: 'id', width: 80, title: 'ID', sort: true},
        {field: 'name', title: '名称', templet: '#menu-name-tpl', minWidth: 220},
        {field: 'type', width: 90, title: '类型', templet: '#menu-type-tpl'},
        {field: 'parent_name', title: '父级', templet: '#menu-parent-tpl', minWidth: 140},
        {field: 'code', title: '标识', templet: '#menu-code-tpl', minWidth: 200},
        {field: 'api', title: '地址', templet: '#menu-api-tpl', minWidth: 240},
        {field: 'method', width: 90, title: '方法', templet: '#menu-method-tpl'},
        {field: 'icon', width: 140, title: '图标'},
        {field: 'sort', width: 90, title: '排序', sort: true},
        {fixed: 'right', title: '操作', toolbar: '#menu-actions', width: 220}
      ]],
      done: function (res) {
        var data = (res && res.data) ? res.data : [];
        applyCollapse(data);
        var $view = $('.layui-table-view');
        $view.off('click.menuToggle').on('click.menuToggle', '.menu-toggle-icon', function (e) {
          e.preventDefault();
          var id = $(this).attr('data-id');
          if (!id) return;
          if (collapsedMap[id]) {
            delete collapsedMap[id];
          } else {
            collapsedMap[id] = true;
          }
          applyCollapse(data);
        });

        $view.off('click.menuCopy').on('click.menuCopy', '.menu-copy', function (e) {
          e.preventDefault();
          var text = $(this).attr('data-text');
          copyText(text);
        });
      }
    });

    form.on('submit(menu-search-btn)', function (data) {
      table.reload('menu-table', {where: data.field});
      return false;
    });

    $('#menu-reset-btn').on('click', function () {
      setTimeout(function () {
        table.reload('menu-table', {where: {}});
      }, 0);
    });

    table.on('toolbar(menu-table)', function (obj) {
      if (obj.event === 'add') {
        openForm({});
      } else if (obj.event === 'expandAll') {
        collapsedMap = {};
        var cache = table.cache && table.cache['menu-table'] ? table.cache['menu-table'] : [];
        applyCollapse(cache);
      } else if (obj.event === 'collapseAll') {
        var cache2 = table.cache && table.cache['menu-table'] ? table.cache['menu-table'] : [];
        var tmp = {};
        for (var i = 0; i < cache2.length; i++) {
          var curLevel = parseInt(cache2[i].level || 0, 10);
          var nextLevel = (i + 1 < cache2.length) ? parseInt(cache2[i + 1].level || 0, 10) : -1;
          if (nextLevel > curLevel) {
            tmp[cache2[i].id] = true;
          }
        }
        collapsedMap = tmp;
        applyCollapse(cache2);
      } else if (obj.event === 'refresh') {
        table.reload('menu-table');
      }
    });

    table.on('tool(menu-table)', function (obj) {
      var data = obj.data || {};
      if (obj.event === 'addChild') {
        var childType = (data.level != null && parseInt(data.level || 0, 10) >= 1) ? 2 : 1;
        openForm({pid: data.id, type: childType});
      } else if (obj.event === 'edit') {
        openForm(data);
      } else if (obj.event === 'delete') {
        layer.confirm('确认删除？（会级联删除子项）', function (index) {
          apiPost('/admin/system/menus/delete', {id: data.id}, function () {
            layer.close(index);
            table.reload('menu-table');
          });
        });
      }
    });

    function openForm(data) {
      data = data || {};
      var isEdit = !!data.id;
      var content = $('#menu-dialog-tpl').html();

      layer.open({
        type: 1,
        title: isEdit ? '编辑' : '新增',
        area: ['720px', '610px'],
        content: content,
        btn: ['保存', '取消'],
        success: function (layero) {
          var t = data.type != null ? String(data.type) : '1';
          var pid = data.pid != null ? String(data.pid) : '0';

          form.val('menu-form', {
            id: data.id || '',
            type: t,
            pid: pid,
            name: data.name || '',
            api: data.api || '',
            method: data.method || '',
            code: data.code || '',
            icon: data.icon || '',
            sort: data.sort != null ? String(data.sort) : '0'
          });

          loadParentOptions(function (opts) {
            var html = '';
            for (var i = 0; i < opts.length; i++) {
              var o = opts[i] || {};
              var val = String(o.id != null ? o.id : 0);
              var txt = String(o.name != null ? o.name : '');
              html += '<option value="' + val + '">' + txt + '</option>';
            }
            $(layero).find('#menu-pid-select').html(html);
            $(layero).find('#menu-pid-select').val(pid);
            form.render('select', 'menu-form');
          });

          $(layero).find('input[name=api]').on('blur', function () {
            var api = $(this).val();
            var code = $(layero).find('input[name=code]').val();
            if (!code) {
              $(layero).find('input[name=code]').val(codeFromApi(api));
            }
          });
        },
        yes: function (index, layero) {
          var $layer = $(layero);
          var field = {
            id: $layer.find('input[name=id]').val() || '',
            type: $layer.find('select[name=type]').val() || '1',
            pid: $layer.find('select[name=pid]').val() || '0',
            name: $.trim($layer.find('input[name=name]').val() || ''),
            api: $.trim($layer.find('input[name=api]').val() || ''),
            method: $.trim($layer.find('input[name=method]').val() || ''),
            code: $.trim($layer.find('input[name=code]').val() || ''),
            icon: $.trim($layer.find('input[name=icon]').val() || ''),
            sort: $layer.find('input[name=sort]').val() || '0'
          };
          field.pid = parseInt(field.pid || '0', 10);
          field.type = parseInt(field.type || '1', 10);
          field.sort = parseInt(field.sort || '0', 10);
          if (field.method) {
            field.method = String(field.method).toUpperCase();
          }
          if (!field.code && field.api) {
            field.code = codeFromApi(field.api);
          }

          var url = isEdit ? '/admin/system/menus/update' : '/admin/system/menus/add';
          apiPost(url, field, function () {
            layer.close(index);
            table.reload('menu-table');
          });
        }
      });
    }
  });
</script>
</body>
</html>
