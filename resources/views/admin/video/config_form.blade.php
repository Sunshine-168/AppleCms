<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>{{ conf('name') }} - {{ $title }}</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="stylesheet" href="{{ asset('static/admin/layui/css/layui.css') }}">
</head>
<body>
<div class="layui-fluid" style="padding:16px;">
  <div class="layui-card">
    <div class="layui-card-header">{{ $title }}</div>
    <div class="layui-card-body">
      <form class="layui-form" lay-filter="site-form" style="max-width:720px;">
        @foreach($fields as $field)
          <div class="layui-form-item">
            <label class="layui-form-label">{{ $field['label'] }}</label>
            <div class="layui-input-block">
              @if(($field['type'] ?? 'text') === 'textarea')
                <textarea name="{{ $field['name'] }}" class="layui-textarea" placeholder="{{ $field['placeholder'] ?? '' }}">{{ $site[$field['name']] ?? '' }}</textarea>
              @elseif(($field['type'] ?? '') === 'select')
                <select name="{{ $field['name'] }}">
                  @foreach(($field['options'] ?? []) as $val => $lab)
                    <option value="{{ $val }}" @selected((string)($site[$field['name']] ?? '') === (string)$val)>{{ $lab }}</option>
                  @endforeach
                </select>
              @else
                <input type="text" name="{{ $field['name'] }}" value="{{ $site[$field['name']] ?? '' }}" class="layui-input" placeholder="{{ $field['placeholder'] ?? '' }}">
              @endif
            </div>
          </div>
        @endforeach
        <div class="layui-form-item">
          <div class="layui-input-block">
            <button type="button" class="layui-btn" id="site-save">保存</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
<script src="{{ asset('static/admin/layui/layui.js') }}"></script>
<script>
layui.use(['form','layer'], function(){
  var $ = layui.$, form = layui.form, layer = layui.layer;
  var csrf = $('meta[name=csrf-token]').attr('content');
  if (csrf) { $.ajaxSetup({headers:{'X-CSRF-TOKEN': csrf}}); }
  form.render();
  $('#site-save').on('click', function(){
    var data = {};
    $('form[lay-filter=site-form]').serializeArray().forEach(function(it){ data[it.name]=it.value; });
    $.post('/admin/video/settings', data, function(res){
      layer.msg((res && res.msg) ? res.msg : '完成', {icon: (res && res.code===0)?1:2});
    }, 'json');
  });
});
</script>
</body>
</html>
