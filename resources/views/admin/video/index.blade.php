<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>{{ conf('name') }} - 视频管理</title>
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
      <form class="layui-form" lay-filter="video-search" id="video-search">
        <div class="layui-form-item">
          <div class="layui-inline">
            <input type="text" name="title" placeholder="标题" autocomplete="off" class="layui-input">
          </div>
          <div class="layui-inline">
            <select name="type_id" id="video-search-type">
              <option value="">分类</option>
            </select>
          </div>
          <div class="layui-inline">
            <select name="status">
              <option value="">状态</option>
              <option value="1">上架</option>
              <option value="0">下架</option>
            </select>
          </div>
          <div class="layui-inline">
            <select name="is_recommend">
              <option value="">推荐</option>
              <option value="1">是</option>
              <option value="0">否</option>
            </select>
          </div>
          <div class="layui-inline">
            <select name="is_hot">
              <option value="">热门</option>
              <option value="1">是</option>
              <option value="0">否</option>
            </select>
          </div>
          <div class="layui-inline">
            <select name="lock">
              <option value="">锁定</option>
              <option value="1">已锁</option>
              <option value="0">未锁</option>
            </select>
          </div>
          <div class="layui-inline">
            <input type="text" name="year" placeholder="年份" autocomplete="off" class="layui-input" style="width:90px;">
          </div>
          <div class="layui-inline">
            <input type="text" name="area" placeholder="地区" autocomplete="off" class="layui-input" style="width:90px;">
          </div>
          <div class="layui-inline">
            <input type="number" name="points_min" placeholder="积分≥" autocomplete="off" class="layui-input" style="width:90px;">
          </div>
          <div class="layui-inline">
            <select name="empty_url">
              <option value="">播放地址</option>
              <option value="1">无地址</option>
            </select>
          </div>
          <div class="layui-inline">
            <select name="repeat">
              <option value="">重名</option>
              <option value="1">仅重名</option>
            </select>
          </div>
          <div class="layui-inline">
            <select name="need_points">
              <option value="">积分片</option>
              <option value="1">需积分</option>
            </select>
          </div>
          <div class="layui-inline">
            <button type="button" class="layui-btn" id="video-search-btn">查询</button>
            <button type="reset" class="layui-btn layui-btn-primary" id="video-reset-btn">重置</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <div class="layui-card">
    <div class="layui-card-body">
      <div class="layui-btn-container" style="margin-bottom:10px;">
        <button class="layui-btn layui-btn-sm" id="video-add-btn">新增视频</button>
        <button class="layui-btn layui-btn-sm" id="video-batch-on">批量上架</button>
        <button class="layui-btn layui-btn-sm layui-btn-warm" id="video-batch-off">批量下架</button>
        <button class="layui-btn layui-btn-sm layui-btn-normal" id="video-batch-rec">批量推荐</button>
        <button class="layui-btn layui-btn-sm" id="video-batch-lock">批量锁定</button>
        <button class="layui-btn layui-btn-sm layui-btn-normal" id="video-batch-type">改分类</button>
        <button class="layui-btn layui-btn-sm" id="video-batch-points">改积分</button>
        <button class="layui-btn layui-btn-sm layui-btn-warm" id="video-batch-merge">合并重复</button>
        <button class="layui-btn layui-btn-sm" id="video-batch-replace-url">替换播放地址</button>
        <button class="layui-btn layui-btn-sm layui-btn-danger" id="video-batch-del">批量删除</button>
        <button class="layui-btn layui-btn-sm layui-btn-primary" id="video-refresh-btn">刷新</button>
        <a class="layui-btn layui-btn-sm layui-btn-warm" href="/admin/video?empty_url=1">无地址</a>
        <a class="layui-btn layui-btn-sm" href="/admin/video?status=0">待审</a>
        <a class="layui-btn layui-btn-sm" href="/admin/video?repeat=1">重名</a>
      </div>
      <table id="video-table" lay-filter="video-table"></table>
    </div>
  </div>
</div>

<script type="text/html" id="video-rowbar">
  <a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>
  <a class="layui-btn layui-btn-xs layui-btn-normal" lay-event="sources">线路</a>
  <a class="layui-btn layui-btn-xs layui-btn-warm" lay-event="episodes">剧集</a>
  <a class="layui-btn layui-btn-xs layui-btn-danger" lay-event="del">删除</a>
</script>

<script type="text/html" id="video-dialog-tpl">
  <div style="padding:15px;">
    <form class="layui-form" lay-filter="video-form" id="video-form">
      <input type="hidden" name="id" value="">
      <div class="layui-form-item">
        <label class="layui-form-label">标题</label>
        <div class="layui-input-block">
          <input type="text" name="title" autocomplete="off" class="layui-input">
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">副标题</label>
        <div class="layui-input-block">
          <input type="text" name="subtitle" autocomplete="off" class="layui-input">
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">分类</label>
        <div class="layui-input-block">
          <select name="type_id" id="video-form-type">
            <option value="">请选择</option>
          </select>
        </div>
      </div>
      <div class="layui-form-item">
        <div class="layui-inline">
          <label class="layui-form-label">年份</label>
          <div class="layui-input-inline">
            <input type="text" name="year" autocomplete="off" class="layui-input">
          </div>
        </div>
        <div class="layui-inline">
          <label class="layui-form-label">地区</label>
          <div class="layui-input-inline">
            <input type="text" name="area" autocomplete="off" class="layui-input">
          </div>
        </div>
        <div class="layui-inline">
          <label class="layui-form-label">语言</label>
          <div class="layui-input-inline">
            <input type="text" name="lang" autocomplete="off" class="layui-input">
          </div>
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">导演</label>
        <div class="layui-input-block">
          <input type="text" name="director" autocomplete="off" class="layui-input">
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">备注</label>
        <div class="layui-input-block">
          <input type="text" name="remarks" autocomplete="off" class="layui-input">
        </div>
      </div>
      <div class="layui-form-item">
        <div class="layui-inline">
          <label class="layui-form-label">点播积分</label>
          <div class="layui-input-inline">
            <input type="number" name="points" autocomplete="off" class="layui-input" value="0">
          </div>
        </div>
        <div class="layui-inline">
          <label class="layui-form-label">锁定</label>
          <div class="layui-input-inline">
            <select name="lock">
              <option value="0">否</option>
              <option value="1">是</option>
            </select>
          </div>
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">封面</label>
        <div class="layui-input-block">
          <div class="layui-input-inline" style="width: calc(100% - 92px);">
            <input type="text" name="cover" autocomplete="off" class="layui-input video-cover-input" placeholder="图片URL">
          </div>
          <div class="layui-input-inline" style="width: 80px;">
            <button type="button" class="layui-btn layui-btn-primary video-cover-upload-btn">上传</button>
          </div>
          <div style="padding-top: 8px;">
            <img class="video-cover-preview" src="" style="max-width: 160px; max-height: 90px; border-radius: 4px; display: none;">
          </div>
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">横幅</label>
        <div class="layui-input-block">
          <div class="layui-input-inline" style="width: calc(100% - 92px);">
            <input type="text" name="banner" autocomplete="off" class="layui-input video-banner-input" placeholder="图片URL">
          </div>
          <div class="layui-input-inline" style="width: 80px;">
            <button type="button" class="layui-btn layui-btn-primary video-banner-upload-btn">上传</button>
          </div>
          <div style="padding-top: 8px;">
            <img class="video-banner-preview" src="" style="max-width: 240px; max-height: 90px; border-radius: 4px; display: none;">
          </div>
        </div>
      </div>
      <div class="layui-form-item">
        <div class="layui-inline">
          <label class="layui-form-label">评分</label>
          <div class="layui-input-inline">
            <input type="number" name="score" value="0" step="0.1" autocomplete="off" class="layui-input">
          </div>
        </div>
        <div class="layui-inline">
          <label class="layui-form-label">排序</label>
          <div class="layui-input-inline">
            <input type="number" name="sort" value="0" autocomplete="off" class="layui-input">
          </div>
        </div>
      </div>
      <div class="layui-form-item">
        <div class="layui-inline">
          <label class="layui-form-label">状态</label>
          <div class="layui-input-inline">
            <select name="status">
              <option value="1">上架</option>
              <option value="0">下架</option>
            </select>
          </div>
        </div>
        <div class="layui-inline">
          <label class="layui-form-label">推荐</label>
          <div class="layui-input-inline">
            <select name="is_recommend">
              <option value="0">否</option>
              <option value="1">是</option>
            </select>
          </div>
        </div>
        <div class="layui-inline">
          <label class="layui-form-label">热门</label>
          <div class="layui-input-inline">
            <select name="is_hot">
              <option value="0">否</option>
              <option value="1">是</option>
            </select>
          </div>
        </div>
      </div>

      <div class="layui-form-item">
        <div class="layui-inline">
          <label class="layui-form-label">采集源</label>
          <div class="layui-input-inline">
            <select name="collect_source_id" id="video-form-collect-source">
              <option value="">无</option>
            </select>
          </div>
        </div>
        <div class="layui-inline">
          <label class="layui-form-label">采集ID</label>
          <div class="layui-input-inline">
            <input type="text" name="collect_id" autocomplete="off" class="layui-input">
          </div>
        </div>
      </div>

      <div class="layui-form-item">
        <label class="layui-form-label">标签</label>
        <div class="layui-input-block">
          <input type="text" name="tags_text" autocomplete="off" class="layui-input">
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">主演</label>
        <div class="layui-input-block">
          <input type="text" name="actors_text" autocomplete="off" class="layui-input">
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">简介</label>
        <div class="layui-input-block">
          <textarea name="description" class="layui-textarea" style="min-height:120px;"></textarea>
        </div>
      </div>
    </form>
  </div>
</script>

<script src="{{ asset('static/admin/layui/layui.js') }}"></script>
<script>
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

  var typeOptionsCache = null;
  function loadTypeOptions(cb){
    if(typeOptionsCache){ cb && cb(typeOptionsCache); return; }
    apiGet('/admin/video/types/options', {}, function(res){
      typeOptionsCache = res.data || [];
      cb && cb(typeOptionsCache);
    });
  }

  var collectOptionsCache = null;
  function loadCollectOptions(cb){
    if(collectOptionsCache){ cb && cb(collectOptionsCache); return; }
    apiGet('/admin/video/collect/options', {}, function(res){
      collectOptionsCache = res.data || [];
      cb && cb(collectOptionsCache);
    });
  }

  function renderTypeSelect($select, options, selected){
    var html = '<option value="">请选择</option>';
    for(var i=0;i<options.length;i++){
      var o = options[i] || {};
      html += '<option value="'+ escapeHtml(o.id) +'">'+ escapeHtml(o.name || '') +'</option>';
    }
    $select.html(html);
    if(selected !== undefined && selected !== null && selected !== ''){
      $select.val(String(selected));
    } else {
      $select.val('');
    }
    form.render('select');
  }

  function renderCollectSelect($select, options, selected){
    var html = '<option value="">无</option>';
    for(var i=0;i<options.length;i++){
      var o = options[i] || {};
      var name = escapeHtml(o.name || '');
      if(String(o.status) === '0'){ name = name + '（禁用）'; }
      html += '<option value="'+ escapeHtml(o.id) +'">'+ name +'</option>';
    }
    $select.html(html);
    if(selected !== undefined && selected !== null && selected !== ''){
      $select.val(String(selected));
    } else {
      $select.val('');
    }
    form.render('select');
  }

  function preloadSearchType(){
    loadTypeOptions(function(options){
      renderTypeSelect($('#video-search-type'), options, '');
    });
  }
  preloadSearchType();

  var qs = new URLSearchParams(location.search);
  var tableIns = table.render({
    elem:'#video-table',
    url:'/admin/video/list',
    method:'get',
    where: {
      empty_url: qs.get('empty_url') || '',
      repeat: qs.get('repeat') || '',
      need_points: qs.get('need_points') || '',
      status: qs.get('status') || ''
    },
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
      {type:'checkbox', width:48},
      {field:'id', width:80, title:'ID', sort:true},
      {field:'title', title:'标题', minWidth:200},
      {field:'type_name', width:140, title:'分类'},
      {field:'score', width:90, title:'评分'},
      {field:'points', width:80, title:'积分'},
      {field:'year', width:80, title:'年份'},
      {field:'status', width:90, title:'状态', templet:function(d){
        return String(d.status) === '1' ? '<span class="layui-badge layui-bg-green">上架</span>' : '<span class="layui-badge">下架</span>';
      }},
      {field:'is_recommend', width:90, title:'推荐', templet:function(d){
        return String(d.is_recommend) === '1' ? '<span class="layui-badge layui-bg-blue">是</span>' : '<span class="layui-badge layui-bg-gray">否</span>';
      }},
      {field:'is_hot', width:90, title:'热门', templet:function(d){
        return String(d.is_hot) === '1' ? '<span class="layui-badge layui-bg-orange">是</span>' : '<span class="layui-badge layui-bg-gray">否</span>';
      }},
      {field:'updated_at_text', width:180, title:'更新时间'},
      {title:'操作', toolbar:'#video-rowbar', width:220}
    ]]
  });

  function openVideoDialog(mode, row){
    row = row || {};
    var isEdit = mode === 'edit';
    var content = $('#video-dialog-tpl').html();

    layer.open({
      type:1,
      title: isEdit ? '编辑视频' : '新增视频',
      area:['780px','680px'],
      content: content,
      btn:['保存','取消'],
      success:function(layero){
        var $layer = $(layero);
        var $form = $layer.find('#video-form');

        $form.find('input[name=id]').val(isEdit ? (row.id || '') : '');
        $form.find('input[name=title]').val(row.title || '');
        $form.find('input[name=subtitle]').val(row.subtitle || '');
        $form.find('input[name=cover]').val(row.cover || '');
        $form.find('input[name=banner]').val(row.banner || '');
        $form.find('input[name=year]').val(row.year || '');
        $form.find('input[name=area]').val(row.area || '');
        $form.find('input[name=lang]').val(row.lang || '');
        $form.find('input[name=director]').val(row.director || '');
        $form.find('input[name=remarks]').val(row.remarks || '');
        $form.find('input[name=points]').val(row.points == null ? 0 : row.points);
        $form.find('input[name=score]').val(row.score == null ? 0 : row.score);
        $form.find('input[name=sort]').val(row.sort == null ? 0 : row.sort);
        $form.find('textarea[name=description]').val(row.description || '');
        $form.find('input[name=collect_id]').val(row.collect_id || '');
        $form.find('input[name=tags_text]').val(row.tags_text || '');
        $form.find('input[name=actors_text]').val(row.actors_text || '');

        $form.find('select[name=status]').val(String(row.status == null ? 1 : row.status));
        $form.find('select[name=is_recommend]').val(String(row.is_recommend == null ? 0 : row.is_recommend));
        $form.find('select[name=is_hot]').val(String(row.is_hot == null ? 0 : row.is_hot));
        $form.find('select[name=lock]').val(String(row.lock == null ? 0 : row.lock));

        loadTypeOptions(function(options){
          renderTypeSelect($layer.find('#video-form-type'), options, row.type_id);
        });
        loadCollectOptions(function(options){
          renderCollectSelect($layer.find('#video-form-collect-source'), options, row.collect_source_id);
        });

        form.render();

        function initImageField(field){
          var $input = $form.find('input[name=' + field + ']');
          var $btn = $form.find('.video-' + field + '-upload-btn');
          var $preview = $form.find('.video-' + field + '-preview');

          function syncPreview(url){
            url = $.trim(url || '');
            if (url) {
              $preview.attr('src', url).show();
            } else {
              $preview.hide().attr('src', '');
            }
          }

          syncPreview($input.val());
          $input.off('input.' + field).on('input.' + field, function(){
            syncPreview(this.value);
          });

          if ($btn.length && !$btn.data('uploadInited')) {
            $btn.data('uploadInited', true);
            upload.render({
              elem: $btn[0],
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
                    $input.val(url);
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
        }

        initImageField('cover');
        initImageField('banner');
      },
      yes:function(index, layero){
        var $layer = $(layero);
        var $form = $layer.find('#video-form');
        var data = formToObj($form);
        if(!data.title){ layer.msg('请输入标题',{icon:2}); return; }
        apiPost('/admin/video/save', data, function(){
          layer.close(index);
          table.reload('video-table');
          layer.msg('保存成功',{icon:1});
        });
      }
    });
  }

  function openEditDialog(row){
    apiGet('/admin/video/info', {id: row.id}, function(res){
      openVideoDialog('edit', res.data || row);
    });
  }

  $('#video-search-btn').on('click', function(){
    table.reload('video-table', {where: formToObj($('#video-search')), page:{curr:1}});
  });

  $('#video-reset-btn').on('click', function(){
    setTimeout(function(){
      form.render();
      table.reload('video-table', {where: {}, page:{curr:1}});
    }, 0);
  });

  $('#video-refresh-btn').on('click', function(){ table.reload('video-table'); });
  $('#video-add-btn').on('click', function(){ openVideoDialog('add'); });

  function selectedIds(){
    return (table.checkStatus('video-table').data || []).map(function(r){ return r.id; });
  }
  function batch(action, value, confirmText){
    var ids = selectedIds();
    if (!ids.length) { layer.msg('请选择数据', {icon:2}); return; }
    var run = function(){
      apiPost('/admin/video/batch', {ids: ids.join(','), action: action, value: value}, function(){
        table.reload('video-table');
        layer.msg('操作成功', {icon:1});
      });
    };
    if (confirmText) {
      layer.confirm(confirmText, function(i){ layer.close(i); run(); });
      return;
    }
    run();
  }
  $('#video-batch-on').on('click', function(){ batch('status', 1); });
  $('#video-batch-off').on('click', function(){ batch('status', 0); });
  $('#video-batch-rec').on('click', function(){ batch('recommend', 1); });
  $('#video-batch-lock').on('click', function(){ batch('lock', 1); });
  $('#video-batch-type').on('click', function(){
    layer.prompt({title:'目标分类ID', formType:0}, function(val, i){ layer.close(i); batch('type', val); });
  });
  $('#video-batch-points').on('click', function(){
    layer.prompt({title:'积分', value:'0', formType:0}, function(val, i){ layer.close(i); batch('points', val); });
  });
  $('#video-batch-merge').on('click', function(){
    var ids = selectedIds();
    if (ids.length < 2) { layer.msg('请至少选两部', {icon:2}); return; }
    layer.prompt({title:'保留的影片ID', value: String(Math.min.apply(null, ids)), formType:0}, function(val, i){
      layer.close(i);
      batch('merge', val, '确认把选中影片合并到 ID '+val+'？线路会迁过去，其余片删除。');
    });
  });
  $('#video-batch-replace-url').on('click', function(){
    layer.prompt({title:'替换播放地址 from|to', formType:0}, function(val, i){
      layer.close(i);
      batch('replace_url', val, '确认替换选中影片的播放地址？');
    });
  });
  $('#video-batch-del').on('click', function(){ batch('delete', '', '确认删除选中视频？'); });

  table.on('tool(video-table)', function(obj){
    var row = obj.data || {};
    if(obj.event === 'edit'){ openEditDialog(row); }
    if(obj.event === 'del'){
      layer.confirm('确定删除该视频吗？', function(i){
        apiPost('/admin/video/delete', {id: row.id}, function(){
          layer.close(i);
          table.reload('video-table');
          layer.msg('删除成功',{icon:1});
        });
      });
    }
    if(obj.event === 'sources'){
      layer.open({
        type:2,
        title:'线路管理 - ' + escapeHtml(row.title || ''),
        area:['95%','95%'],
        maxmin:true,
        content:'/admin/video/sources?video_id=' + encodeURIComponent(row.id)
      });
    }
    if(obj.event === 'episodes'){
      layer.open({
        type:2,
        title:'剧集管理 - ' + escapeHtml(row.title || ''),
        area:['95%','95%'],
        maxmin:true,
        content:'/admin/video/sources?video_id=' + encodeURIComponent(row.id) + '&open_episode=1'
      });
    }
  });
});
</script>
</body>
</html>
