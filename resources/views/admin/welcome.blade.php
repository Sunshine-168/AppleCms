<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>{{ conf('name') }} - 欢迎页</title>
  <meta name="renderer" content="webkit">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="stylesheet" href="{{ asset('static/admin/layui/css/layui.css') }}" media="all">
  <link rel="stylesheet" href="{{ asset('static/admin/style/admin.css') }}" media="all">
  <style>
    body { background: #f6f8fb; }
    .welcome-topbar { display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; }
    .welcome-title { display:flex; align-items:baseline; gap:12px; }
    .welcome-title h1 { font-size: 18px; font-weight: 600; margin:0; color:#111827; }
    .welcome-title .sub { color:#6b7280; font-size:12px; }
    .welcome-actions { display:flex; align-items:center; gap:10px; }
    .welcome-actions .meta { color:#9ca3af; font-size:12px; }
    .stat-grid .layui-card { border-radius: 10px; overflow:hidden; box-shadow: 0 6px 18px rgba(15, 23, 42, .06); transition: transform .15s ease, box-shadow .15s ease; }
    .stat-grid .layui-card:hover { transform: translateY(-2px); box-shadow: 0 10px 26px rgba(15, 23, 42, .09); }
    .stat-card { position:relative; padding: 14px 16px 14px 16px; }
    .stat-head { display:flex; align-items:center; justify-content:space-between; margin-bottom: 10px; }
    .stat-label { display:flex; align-items:center; gap:10px; color:#374151; font-size:13px; }
    .stat-icon { width:34px; height:34px; border-radius: 10px; display:flex; align-items:center; justify-content:center; color:#fff; }
    .stat-icon i { font-size:18px; }
    .stat-value { font-size: 28px; font-weight: 700; letter-spacing: .2px; color:#0f172a; line-height: 1.15; }
    .stat-foot { margin-top: 6px; display:flex; align-items:center; justify-content:space-between; color:#9ca3af; font-size:12px; }
    .stat-foot .hint { display:flex; align-items:center; gap:6px; }
    .stat-bar { position:absolute; left:0; top:0; height:4px; width:100%; opacity:.95; }
    .is-loading .stat-value { color: transparent; }
    .skeleton { position: relative; border-radius: 8px; background: linear-gradient(90deg, rgba(148,163,184,.18) 25%, rgba(148,163,184,.32) 37%, rgba(148,163,184,.18) 63%); background-size: 400% 100%; animation: sk 1.2s ease infinite; }
    .skeleton.value { height: 28px; width: 120px; }
    .skeleton.meta { height: 12px; width: 160px; border-radius: 999px; }
    @keyframes sk { 0% { background-position: 100% 0; } 100% { background-position: 0 0; } }
    .stat-grid .layui-col-xs12 { margin-bottom: 15px; }
  </style>
</head>
<body>
<div class="layui-fluid">
  <div class="welcome-topbar">
    <div class="welcome-title">
      <h1>数据概览</h1>
      <div class="sub">欢迎回来，祝你工作顺利</div>
    </div>
    <div class="welcome-actions">
      <div class="meta" id="stat-updated"><span class="skeleton meta"></span></div>
      <button class="layui-btn layui-btn-sm" id="stat-refresh-btn"><i class="layui-icon layui-icon-refresh-3"></i> 刷新</button>
    </div>
  </div>

  <div class="layui-row layui-col-space15 stat-grid" id="stat-grid">
    <div class="layui-col-xs12 layui-col-sm6 layui-col-md4">
      <div class="layui-card">
        <div class="stat-card" data-key="vod_total">
          <div class="stat-bar" style="background:linear-gradient(90deg,#60a5fa,#2563eb)"></div>
          <div class="stat-head">
            <div class="stat-label">
              <span class="stat-icon" style="background:linear-gradient(135deg,#60a5fa,#2563eb)"><i class="layui-icon layui-icon-video"></i></span>
              <span>视频总数</span>
            </div>
          </div>
          <div class="stat-value" id="stat-vod-total"><span class="skeleton value"></span></div>
          <div class="stat-foot">
            <div class="hint"><i class="layui-icon layui-icon-tips"></i><span>累计</span></div>
          </div>
        </div>
      </div>
    </div>
    <div class="layui-col-xs12 layui-col-sm6 layui-col-md4">
      <div class="layui-card">
        <div class="stat-card" data-key="vod_today">
          <div class="stat-bar" style="background:linear-gradient(90deg,#34d399,#10b981)"></div>
          <div class="stat-head">
            <div class="stat-label">
              <span class="stat-icon" style="background:linear-gradient(135deg,#34d399,#10b981)"><i class="layui-icon layui-icon-add-1"></i></span>
              <span>今日新增视频</span>
            </div>
          </div>
          <div class="stat-value" id="stat-vod-today"><span class="skeleton value"></span></div>
          <div class="stat-foot">
            <div class="hint"><i class="layui-icon layui-icon-time"></i><span>今日</span></div>
          </div>
        </div>
      </div>
    </div>
    <div class="layui-col-xs12 layui-col-sm6 layui-col-md4">
      <div class="layui-card">
        <div class="stat-card" data-key="article_total">
          <div class="stat-bar" style="background:linear-gradient(90deg,#fbbf24,#f59e0b)"></div>
          <div class="stat-head">
            <div class="stat-label">
              <span class="stat-icon" style="background:linear-gradient(135deg,#fbbf24,#f59e0b)"><i class="layui-icon layui-icon-read"></i></span>
              <span>评论总数</span>
            </div>
          </div>
          <div class="stat-value" id="stat-article-total"><span class="skeleton value"></span></div>
          <div class="stat-foot">
            <div class="hint"><i class="layui-icon layui-icon-tips"></i><span>累计</span></div>
          </div>
        </div>
      </div>
    </div>
    <div class="layui-col-xs12 layui-col-sm6 layui-col-md4">
      <div class="layui-card">
        <div class="stat-card" data-key="user_total">
          <div class="stat-bar" style="background:linear-gradient(90deg,#a78bfa,#7c3aed)"></div>
          <div class="stat-head">
            <div class="stat-label">
              <span class="stat-icon" style="background:linear-gradient(135deg,#a78bfa,#7c3aed)"><i class="layui-icon layui-icon-user"></i></span>
              <span>用户总数</span>
            </div>
          </div>
          <div class="stat-value" id="stat-user-total"><span class="skeleton value"></span></div>
          <div class="stat-foot">
            <div class="hint"><i class="layui-icon layui-icon-tips"></i><span>累计</span></div>
          </div>
        </div>
      </div>
    </div>
    <div class="layui-col-xs12 layui-col-sm6 layui-col-md4">
      <div class="layui-card">
        <div class="stat-card" data-key="visit_today">
          <div class="stat-bar" style="background:linear-gradient(90deg,#fb7185,#e11d48)"></div>
          <div class="stat-head">
            <div class="stat-label">
              <span class="stat-icon" style="background:linear-gradient(135deg,#fb7185,#e11d48)"><i class="layui-icon layui-icon-engine"></i></span>
              <span>今日访问量</span>
            </div>
          </div>
          <div class="stat-value" id="stat-visit-today"><span class="skeleton value"></span></div>
          <div class="stat-foot">
            <div class="hint"><i class="layui-icon layui-icon-time"></i><span>今日</span></div>
          </div>
        </div>
      </div>
    </div>
    <div class="layui-col-xs12 layui-col-sm6 layui-col-md4">
      <div class="layui-card">
        <div class="stat-card" data-key="play_today">
          <div class="stat-bar" style="background:linear-gradient(90deg,#22d3ee,#0891b2)"></div>
          <div class="stat-head">
            <div class="stat-label">
              <span class="stat-icon" style="background:linear-gradient(135deg,#22d3ee,#0891b2)"><i class="layui-icon layui-icon-play"></i></span>
              <span>今日播放量</span>
            </div>
          </div>
          <div class="stat-value" id="stat-play-today"><span class="skeleton value"></span></div>
          <div class="stat-foot">
            <div class="hint"><i class="layui-icon layui-icon-time"></i><span>今日</span></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="layui-card" style="margin-top:8px;">
    <div class="layui-card-header">影视后台</div>
    <div class="layui-card-body">
      <div class="layui-btn-container">
        <a class="layui-btn layui-btn-sm" href="/admin/video">影片</a>
        <a class="layui-btn layui-btn-sm" href="/admin/video/collects">采集</a>
        <a class="layui-btn layui-btn-sm" href="/admin/video/collect_logs">采集日志</a>
        <a class="layui-btn layui-btn-sm" href="/admin/video/audits">审核规则</a>
        <a class="layui-btn layui-btn-sm" href="/admin/video/playfails">播放失败</a>
        <a class="layui-btn layui-btn-sm" href="/admin/video/orders">订单</a>
        <a class="layui-btn layui-btn-sm" href="/admin/video/plogs">积分流水</a>
        <a class="layui-btn layui-btn-sm" href="/admin/video/groups">会员组</a>
        <a class="layui-btn layui-btn-sm" href="/admin/video/settings">站点设置</a>
        <a class="layui-btn layui-btn-sm" href="/admin/video/make">静态生成</a>
        <a class="layui-btn layui-btn-sm" href="/admin/video/templates">模板</a>
        <a class="layui-btn layui-btn-sm" href="/admin/video/push">推送</a>
        <a class="layui-btn layui-btn-sm" href="/admin/video/visits">统计</a>
        <a class="layui-btn layui-btn-sm" href="/admin/video/wizard">标签向导</a>
        <a class="layui-btn layui-btn-sm" href="/admin/video/roles">角色库</a>
        <a class="layui-btn layui-btn-sm" href="/admin/video/arts">文章</a>
        <a class="layui-btn layui-btn-sm" href="/admin/video/websites">网址</a>
        <a class="layui-btn layui-btn-sm" href="/admin/video/ulogs">访问日志</a>
        <a class="layui-btn layui-btn-sm" href="/admin/video/unions">推荐资源</a>
      </div>
      <p class="layui-word-aux">这些链接在后台 iframe 里打开对应菜单。首次请先完成 /install，再用管理员账号登录。</p>
    </div>
  </div>
</div>

<script src="{{ asset('static/admin/layui/layui.js') }}"></script>
<script>
layui.use(['jquery', 'layer'], function () {
  var $ = layui.$;
  var layer = layui.layer;

  function toText(val) {
    return (val === null || val === undefined || val === '') ? '--' : String(val);
  }

  function setUpdated() {
    var d = new Date();
    var pad = function (n) { return (n < 10 ? '0' : '') + n; };
    var text = d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + ' ' + pad(d.getHours()) + ':' + pad(d.getMinutes()) + ':' + pad(d.getSeconds());
    $('#stat-updated').text('更新于 ' + text);
  }

  function setLoading(loading) {
    $('#stat-refresh-btn').prop('disabled', !!loading);
    if (loading) {
      $('#stat-updated').html('<span class="skeleton meta"></span>');
      $('#stat-grid .stat-value').each(function () {
        $(this).html('<span class="skeleton value"></span>');
      });
    }
  }

  function render(data) {
    $('#stat-vod-total').text(toText(data.vod_total));
    $('#stat-vod-today').text(toText(data.vod_today));
    $('#stat-article-total').text(toText(data.comment_total ?? data.article_total));
    $('#stat-user-total').text(toText(data.user_total));
    $('#stat-visit-today').text(toText(data.visit_today));
    $('#stat-play-today').text(toText(data.play_today));
    setUpdated();
  }

  function loadStats() {
    setLoading(true);
    return $.ajax({
      url: '/admin/welcome/stats',
      method: 'get',
      dataType: 'json'
    }).done(function (res) {
      var data = res && res.data ? res.data : {};
      render(data);
    }).fail(function () {
      layer.msg('加载统计失败');
    }).always(function () {
      setLoading(false);
    });
  }

  $('#stat-refresh-btn').on('click', function () {
    loadStats();
  });

  loadStats();
});
</script>
</body>
</html>
