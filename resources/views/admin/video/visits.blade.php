<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>{{ conf('name') }} - 访问统计</title>
  <link rel="stylesheet" href="{{ asset('static/admin/layui/css/layui.css') }}">
</head>
<body>
<div class="layui-fluid" style="padding:16px;">
  <div class="layui-card">
    <div class="layui-card-header">今日 PV {{ $today['pv'] ?? 0 }} / UV {{ $today['uv'] ?? 0 }}</div>
    <div class="layui-card-body">
      <table class="layui-table">
        <thead><tr><th>日期</th><th>PV</th><th>UV</th></tr></thead>
        <tbody>
        @foreach($days ?? [] as $row)
          <tr><td>{{ $row['day'] ?? '' }}</td><td>{{ $row['pv'] ?? 0 }}</td><td>{{ $row['uv'] ?? 0 }}</td></tr>
        @endforeach
        </tbody>
      </table>
    </div>
  </div>
  <div class="layui-row layui-col-space12">
    <div class="layui-col-md6">
      <div class="layui-card">
        <div class="layui-card-header">按影片</div>
        <div class="layui-card-body">
          <table class="layui-table">
            <thead><tr><th>影片ID</th><th>PV</th></tr></thead>
            <tbody>
            @foreach($videos ?? [] as $row)
              <tr><td>{{ $row['video_id'] ?? '' }}</td><td>{{ $row['pv'] ?? 0 }}</td></tr>
            @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="layui-col-md6">
      <div class="layui-card">
        <div class="layui-card-header">按分类</div>
        <div class="layui-card-body">
          <table class="layui-table">
            <thead><tr><th>分类ID</th><th>PV</th></tr></thead>
            <tbody>
            @foreach($types ?? [] as $row)
              <tr><td>{{ $row['type_id'] ?? '' }}</td><td>{{ $row['pv'] ?? 0 }}</td></tr>
            @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
