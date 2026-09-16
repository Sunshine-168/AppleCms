<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>{{ conf('name') }} - {{ ['images'=>'远程图片','quality'=>'内容质量','players'=>'批量播放器','annex'=>'附件清理','recycle'=>'回收站','hub'=>'采集目录'][$tool] ?? '工具' }}</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="stylesheet" href="{{ asset('static/admin/layui/css/layui.css') }}">
</head>
<body>
<div class="layui-fluid" style="padding:16px;">
  <div class="layui-card">
    <div class="layui-card-header">
      @if($tool === 'images') 远程图片 / 坏图
      @elseif($tool === 'quality') 内容质量
      @elseif($tool === 'players') 批量更换播放器
      @elseif($tool === 'annex') 附件同步清理
      @elseif($tool === 'recycle') 回收站
      @else 采集资源目录
      @endif
    </div>
    <div class="layui-card-body">
      @if($tool === 'images')
        <p class="layui-word-aux">扫描影片封面：远程地址、无法访问的坏图。本地化会下载到 <code>/uploads/vod/</code>，并按站点设置写入水印。</p>
        <div class="layui-btn-container" style="margin:12px 0;">
          <button class="layui-btn" id="btn-scan">扫描</button>
          <button class="layui-btn layui-btn-normal" id="btn-local">本地化远程封面</button>
        </div>
        <pre id="out" class="layui-code" style="min-height:160px;"></pre>
      @elseif($tool === 'quality')
        <p class="layui-word-aux">统计无地址、无封面、无简介、无演员、重名、集数不足。</p>
        <div class="layui-btn-container" style="margin:12px 0;">
          <button class="layui-btn" id="btn-scan">开始体检</button>
          <a class="layui-btn layui-btn-primary" href="/admin/video?empty_url=1">无地址列表</a>
          <a class="layui-btn layui-btn-primary" href="/admin/video?empty_pic=1">无封面列表</a>
          <a class="layui-btn layui-btn-primary" href="/admin/video?repeat=1">重名列表</a>
        </div>
        <pre id="out" class="layui-code" style="min-height:160px;"></pre>
      @elseif($tool === 'players')
        <p class="layui-word-aux">按线路上的播放器标识批量改名或下线。现有播放器：
          @foreach($players as $p) <span class="layui-badge layui-bg-gray">{{ $p->code }}</span> @endforeach
        </p>
        <form class="layui-form" style="max-width:520px;margin-top:12px;">
          <div class="layui-form-item">
            <label class="layui-form-label">原标识</label>
            <div class="layui-input-block"><input type="text" id="from" class="layui-input" placeholder="如 dplayer"></div>
          </div>
          <div class="layui-form-item">
            <label class="layui-form-label">新标识</label>
            <div class="layui-input-block"><input type="text" id="to" class="layui-input" placeholder="下线时可空"></div>
          </div>
          <div class="layui-form-item">
            <div class="layui-input-block">
              <button type="button" class="layui-btn" id="btn-rename">替换标识</button>
              <button type="button" class="layui-btn layui-btn-danger" id="btn-off">下线该线路</button>
            </div>
          </div>
        </form>
        <pre id="out" class="layui-code" style="min-height:80px;"></pre>
      @elseif($tool === 'annex')
        <p class="layui-word-aux">对照影片/文章/演员封面，找出 <code>/uploads/vod/</code> 里未被引用的文件。</p>
        <div class="layui-btn-container" style="margin:12px 0;">
          <button class="layui-btn" id="btn-scan">扫描</button>
          <button class="layui-btn layui-btn-danger" id="btn-del">删除未引用</button>
        </div>
        <pre id="out" class="layui-code" style="min-height:160px;"></pre>
      @elseif($tool === 'recycle')
        <p class="layui-word-aux">删除的影片先进入回收站，可还原或彻底删除。</p>
        <div class="layui-btn-container" style="margin:12px 0;">
          <button class="layui-btn" id="btn-restore">还原选中</button>
          <button class="layui-btn layui-btn-danger" id="btn-purge">彻底删除</button>
        </div>
        <table id="rec-table" lay-filter="rec-table"></table>
      @else
        <p class="layui-word-aux">填写苹果 CMS 兼容接口地址，探测分类和样例。也可在「推荐资源」里保存常用源。</p>
        <div class="layui-form-item" style="max-width:640px;">
          <input type="text" id="hub-url" class="layui-input" placeholder="https://example.com/api.php/provide/vod/">
        </div>
        <div class="layui-btn-container">
          <button class="layui-btn" id="btn-probe">探测接口</button>
          <a class="layui-btn layui-btn-primary" href="/admin/video/unions">推荐资源</a>
          <a class="layui-btn layui-btn-primary" href="/admin/video/collects">采集源</a>
        </div>
        @if(count($unions))
          <p style="margin-top:12px;">已保存：</p>
          <ul>
            @foreach($unions as $u)
              <li><a href="javascript:;" class="hub-fill" data-url="{{ $u->api_url }}">{{ $u->name }}</a> <span class="layui-word-aux">{{ $u->api_url }}</span></li>
            @endforeach
          </ul>
        @endif
        <pre id="out" class="layui-code" style="min-height:160px;"></pre>
      @endif
    </div>
  </div>
</div>
<script src="{{ asset('static/admin/layui/layui.js') }}"></script>
<script>
layui.use(['layer','table'], function(){
  var $ = layui.$, layer = layui.layer, table = layui.table;
  var csrf = $('meta[name=csrf-token]').attr('content');
  if (csrf) { $.ajaxSetup({headers:{'X-CSRF-TOKEN': csrf}}); }
  var tool = @json($tool);
  function post(action, extra, cb){
    var load = layer.load(1);
    $.post('/admin/video/tools/' + tool + '/run', Object.assign({action: action}, extra || {}), function(res){
      layer.close(load);
      if (cb) { cb(res); return; }
      $('#out').text(JSON.stringify((res && res.data) || res, null, 2));
      layer.msg((res && res.msg) || '完成', {icon: (res && res.code===0)?1:2});
    }, 'json').fail(function(){ layer.close(load); layer.msg('请求失败',{icon:2}); });
  }
  $('#btn-scan').on('click', function(){ post('scan'); });
  $('#btn-local').on('click', function(){ post('localize'); });
  $('#btn-del').on('click', function(){
    layer.confirm('确认删除未引用文件？', function(i){ layer.close(i); post('delete'); });
  });
  $('#btn-rename').on('click', function(){ post('replace', {from:$('#from').val(), to:$('#to').val(), mode:'rename'}); });
  $('#btn-off').on('click', function(){ post('replace', {from:$('#from').val(), mode:'disable'}); });
  $('#btn-probe').on('click', function(){ post('probe', {api_url:$('#hub-url').val()}); });
  $('.hub-fill').on('click', function(){ $('#hub-url').val($(this).data('url')); });
  if (tool === 'recycle') {
    table.render({
      elem: '#rec-table',
      url: '/admin/video/list',
      where: {trash: 1},
      page: true,
      cols: [[
        {type:'checkbox'},
        {field:'id', title:'ID', width:80},
        {field:'title', title:'标题'},
        {field:'type_name', title:'分类', width:120},
        {field:'deleted_at', title:'删除时间', width:160, templet: function(d){
          var t = parseInt(d.deleted_at || 0, 10);
          return t ? new Date(t*1000).toLocaleString() : '';
        }}
      ]]
    });
    function selected(){ return (table.checkStatus('rec-table').data || []).map(function(r){ return r.id; }); }
    $('#btn-restore').on('click', function(){
      var ids = selected();
      if (!ids.length) { layer.msg('请选择', {icon:2}); return; }
      post('restore', {ids: ids.join(',')}, function(res){
        layer.msg((res && res.msg) || '完成', {icon: (res && res.code===0)?1:2});
        table.reload('rec-table');
      });
    });
    $('#btn-purge').on('click', function(){
      var ids = selected();
      if (!ids.length) { layer.msg('请选择', {icon:2}); return; }
      layer.confirm('彻底删除后无法恢复', function(i){
        layer.close(i);
        post('purge', {ids: ids.join(',')}, function(res){
          layer.msg((res && res.msg) || '完成', {icon: (res && res.code===0)?1:2});
          table.reload('rec-table');
        });
      });
    });
  }
});
</script>
</body>
</html>
