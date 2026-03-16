<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>{{ conf('name') }} - 字段管理</title>
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
      <form class="layui-form" lay-filter="dict-search">
        <div class="layui-form-item">
          <div class="layui-inline">
            <input type="text" name="dict_type" placeholder="类型" autocomplete="off" class="layui-input">
          </div>
          <div class="layui-inline">
            <input type="text" name="dict_key" placeholder="KEY" autocomplete="off" class="layui-input">
          </div>
          <div class="layui-inline">
            <input type="text" name="label" placeholder="名称" autocomplete="off" class="layui-input">
          </div>
          <div class="layui-inline">
            <button class="layui-btn" lay-submit lay-filter="dict-search-btn">查询</button>
            <button type="reset" class="layui-btn layui-btn-primary" id="dict-reset-btn">重置</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <div class="layui-card">
    <div class="layui-card-body">
      <table id="dict-table" lay-filter="dict-table"></table>
    </div>
  </div>
</div>

<script type="text/html" id="dict-toolbar">
  <div class="layui-btn-container">
    <button class="layui-btn layui-btn-sm" lay-event="add">新增字段</button>
    <button class="layui-btn layui-btn-sm layui-btn-primary" lay-event="refresh">刷新</button>
  </div>
</script>

<script type="text/html" id="dict-actions">
@verbatim
  <a class="layui-btn layui-btn-primary layui-btn-xs" lay-event="edit">编辑</a>
  <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="delete">删除</a>
@endverbatim
</script>

<script type="text/html" id="dict-status-tpl">
@verbatim
  <input type="checkbox" lay-skin="switch" lay-filter="dict-status" data-id="{{d.id}}" lay-text="启用|禁用" {{ d.status==0?'checked':'' }}>
@endverbatim
</script>

<script type="text/html" id="dict-dialog-tpl">
  <div style="padding:16px 18px 0 0;">
    <form class="layui-form" lay-filter="dict-form">
      <input type="hidden" name="id" value="">
      <div class="layui-form-item">
        <label class="layui-form-label">类型</label>
        <div class="layui-input-block">
          <input type="text" name="dict_type" placeholder="如 site_config" autocomplete="off" class="layui-input">
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">KEY</label>
        <div class="layui-input-block">
          <input type="text" name="dict_key" placeholder="如 usdt_rate" autocomplete="off" class="layui-input">
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">名称</label>
        <div class="layui-input-block">
          <input type="text" name="label" placeholder="展示名称" autocomplete="off" class="layui-input">
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">类型</label>
        <div class="layui-input-block">
          <select name="value_type" lay-filter="dict-value-type">
            <option value="0">string</option>
            <option value="1">int</option>
            <option value="2">float</option>
            <option value="3">json</option>
            <option value="4">array</option>
            <option value="5">enum</option>
            <option value="6">text</option>
          </select>
        </div>
      </div>

      <div class="layui-form-item" id="dict-value-string-box">
        <label class="layui-form-label">值</label>
        <div class="layui-input-block">
          <input type="text" name="dict_value" autocomplete="off" class="layui-input">
        </div>
      </div>

      <div class="layui-form-item layui-form-text" id="dict-value-text-box" style="display:none;">
        <label class="layui-form-label">值</label>
        <div class="layui-input-block">
          <textarea name="dict_value_text" class="layui-textarea" style="min-height: 120px;"></textarea>
        </div>
      </div>

      <div class="layui-form-item layui-form-text" id="dict-enum-limit-box" style="display:none;">
        <label class="layui-form-label">枚举</label>
        <div class="layui-input-block">
          <textarea name="enum_limit" class="layui-textarea" placeholder='如 ["a","b"]' style="min-height: 90px;"></textarea>
        </div>
      </div>

      <div class="layui-form-item">
        <label class="layui-form-label">状态</label>
        <div class="layui-input-block">
          <input type="radio" name="status" value="0" title="启用" checked>
          <input type="radio" name="status" value="1" title="禁用">
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">排序</label>
        <div class="layui-input-block">
          <input type="number" name="sort" value="0" autocomplete="off" class="layui-input">
        </div>
      </div>
      <div class="layui-form-item layui-form-text">
        <label class="layui-form-label">备注</label>
        <div class="layui-input-block">
          <textarea name="remark" class="layui-textarea" style="min-height: 60px;"></textarea>
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

    function toggleValueTypeUI($layer, valueType) {
      var t = String(valueType || '0');
      var showText = (t === '6' || t === '3' || t === '4' || t === '5');
      $layer.find('#dict-value-string-box').toggle(!showText);
      $layer.find('#dict-value-text-box').toggle(showText);
      $layer.find('#dict-enum-limit-box').toggle(t === '5');
    }

    function openForm(data) {
      data = data || {};
      var isEdit = !!data.id;
      var content = $('#dict-dialog-tpl').html();

      layer.open({
        type: 1,
        title: isEdit ? '编辑字段' : '新增字段',
        area: ['720px', '560px'],
        content: content,
        btn: ['保存', '取消'],
        success: function (layero) {
          var $layer = $(layero);
          $layer.find('input[name=id]').val(data.id || '');
          $layer.find('input[name=dict_type]').val(data.dict_type || '');
          $layer.find('input[name=dict_key]').val(data.dict_key || '');
          $layer.find('input[name=label]').val(data.label || '');
          $layer.find('select[name=value_type]').val((data.value_type != null ? String(data.value_type) : '0'));
          $layer.find('input[name=sort]').val(data.sort || 0);
          var statusVal = (data.status == 1 ? '1' : '0');
          $layer.find('input[name=status][value="' + statusVal + '"]').prop('checked', true);
          $layer.find('textarea[name=remark]').val(data.remark || '');

          var vt = (data.value_type != null ? String(data.value_type) : '0');
          toggleValueTypeUI($layer, vt);

          var dictValue = data.dict_value != null ? String(data.dict_value) : '';
          if (vt === '6' || vt === '3' || vt === '4' || vt === '5') {
            $layer.find('textarea[name=dict_value_text]').val(dictValue);
          } else {
            $layer.find('input[name=dict_value]').val(dictValue);
          }

          var enumText = '';
          if (data.enum_limit != null) {
            try {
              enumText = JSON.stringify(data.enum_limit);
            } catch (e) {
              enumText = '';
            }
          }
          $layer.find('textarea[name=enum_limit]').val(enumText);

          form.render();
        },
        yes: function (index, layero) {
          var $layer = $(layero);
          var id = parseInt($layer.find('input[name=id]').val() || '0', 10);
          var dictType = $.trim($layer.find('input[name=dict_type]').val());
          var dictKey = $.trim($layer.find('input[name=dict_key]').val());
          var label = $.trim($layer.find('input[name=label]').val());
          var valueType = $layer.find('select[name=value_type]').val();
          var status = $layer.find('input[name=status]:checked').val();
          var sort = parseInt($layer.find('input[name=sort]').val() || '0', 10);
          var remark = $.trim($layer.find('textarea[name=remark]').val());

          if (!dictType) { layer.msg('请输入类型'); return; }
          if (!dictKey) { layer.msg('请输入KEY'); return; }

          var vt = String(valueType || '0');
          var dictValue;
          var enumLimit;

          if (vt === '6' || vt === '3' || vt === '4' || vt === '5') {
            dictValue = $.trim($layer.find('textarea[name=dict_value_text]').val());
          } else {
            dictValue = $.trim($layer.find('input[name=dict_value]').val());
          }

          if (vt === '3' || vt === '4' || vt === '5') {
            try {
              dictValue = JSON.stringify(JSON.parse(dictValue || 'null'));
            } catch (e) {
              layer.msg('值不是合法JSON');
              return;
            }
          }

          if (vt === '5') {
            var enumText = $.trim($layer.find('textarea[name=enum_limit]').val());
            try {
              enumLimit = JSON.stringify(JSON.parse(enumText || '[]'));
            } catch (e) {
              layer.msg('枚举不是合法JSON数组');
              return;
            }
          }

          var url = id > 0 ? '/admin/system/dicts/update' : '/admin/system/dicts/add';
          var payload = {
            dict_type: dictType,
            dict_key: dictKey,
            label: label,
            value_type: vt,
            dict_value: dictValue,
            enum_limit: enumLimit,
            status: status,
            sort: sort,
            remark: remark
          };
          if (id > 0) {
            payload.id = id;
          }

          var loading = layer.load(1);
          apiPost(url, payload, function () {
            layer.close(loading);
            layer.close(index);
            table.reload('dict-table');
            layer.msg('保存成功', {icon: 1});
          });
        }
      });
    }

    table.render({
      elem: '#dict-table',
      id: 'dict-table',
      url: '/admin/system/dicts/list',
      method: 'get',
      page: true,
      toolbar: '#dict-toolbar',
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
        {field: 'dict_type', title: '类型', minWidth: 140},
        {field: 'dict_key', title: 'KEY', minWidth: 160},
        {field: 'label', title: '名称', minWidth: 160},
        {field: 'value_type', title: '值类型', width: 90},
        {field: 'dict_value', title: '值', minWidth: 240},
        {field: 'status', title: '状态', width: 100, templet: '#dict-status-tpl'},
        {field: 'sort', title: '排序', width: 90, sort: true},
        {field: 'remark', title: '备注', minWidth: 200},
        {field: 'update_time', title: '更新时间', width: 180},
        {field: 'create_time', title: '创建时间', width: 180},
        {title: '操作', width: 140, toolbar: '#dict-actions'}
      ]]
    });

    table.on('toolbar(dict-table)', function (obj) {
      if (obj.event === 'refresh') {
        table.reload('dict-table');
        return;
      }
      if (obj.event === 'add') {
        openForm({});
      }
    });

    table.on('tool(dict-table)', function (obj) {
      var data = obj.data || {};
      if (obj.event === 'edit') {
        openForm(data);
        return;
      }
      if (obj.event === 'delete') {
        layer.confirm('确认删除该字段？', function (index) {
          apiPost('/admin/system/dicts/delete', {id: data.id}, function () {
            layer.close(index);
            table.reload('dict-table');
            layer.msg('删除成功', {icon: 1});
          });
        });
      }
    });

    form.on('submit(dict-search-btn)', function (obj) {
      table.reload('dict-table', {where: obj.field || {}, page: {curr: 1}});
      return false;
    });

    $('#dict-reset-btn').on('click', function () {
      table.reload('dict-table', {where: {}, page: {curr: 1}});
    });

    form.on('select(dict-value-type)', function (obj) {
      var $layer = $(obj.elem).closest('.layui-layer-content');
      if ($layer.length === 0) {
        return;
      }
      toggleValueTypeUI($layer, obj.value);
    });

    form.on('switch(dict-status)', function (obj) {
      var id = $(obj.elem).data('id');
      var status = obj.elem.checked ? 0 : 1;
      apiPost('/admin/system/dicts/state', {id: id, status: status}, function () {
        layer.msg('状态已更新', {icon: 1});
      });
    });
  });
</script>
</body>
</html>
