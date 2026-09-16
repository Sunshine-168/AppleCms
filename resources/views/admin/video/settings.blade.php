<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>{{ conf('name') }} - 站点设置</title>
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
    <div class="layui-card-header">站点设置</div>
    <div class="layui-card-body">
      <form class="layui-form" lay-filter="site-form" style="max-width:720px;">
        <div class="layui-form-item">
          <label class="layui-form-label">站点名称</label>
          <div class="layui-input-block">
            <input type="text" name="site_title" value="{{ $site['title'] ?? '' }}" class="layui-input">
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">关键词</label>
          <div class="layui-input-block">
            <input type="text" name="site_keyword" value="{{ $site['keyword'] ?? '' }}" class="layui-input">
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">描述</label>
          <div class="layui-input-block">
            <textarea name="site_description" class="layui-textarea">{{ $site['description'] ?? '' }}</textarea>
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">全页缓存</label>
          <div class="layui-input-block">
            <select name="html_cache_enabled">
              <option value="0" @selected(!($site['html_cache_enabled'] ?? false))>关闭</option>
              <option value="1" @selected($site['html_cache_enabled'] ?? false)>开启</option>
            </select>
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">缓存秒数</label>
          <div class="layui-input-block">
            <input type="number" name="html_cache_ttl" value="{{ $site['html_cache_ttl'] ?? 3600 }}" class="layui-input">
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">伪静态</label>
          <div class="layui-input-block">
            <select name="rewrite_mode">
              <option value="laravel" @selected(($site['rewrite_mode'] ?? 'laravel')==='laravel')>Laravel 路由</option>
              <option value="mac" @selected(($site['rewrite_mode'] ?? '')==='mac')>苹果风格 index.php/vod</option>
            </select>
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">后缀</label>
          <div class="layui-input-block">
            <input type="text" name="rewrite_suffix" value="{{ $site['rewrite_suffix'] ?? '.html' }}" class="layui-input">
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">伪静态预览</label>
          <div class="layui-input-block">
            <p class="layui-word-aux">Laravel：<code>/vod/123</code>　苹果：<code>/index.php/vod/detail/id/123.html</code></p>
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">百度Token</label>
          <div class="layui-input-block">
            <input type="text" name="baidu_push_token" value="{{ $site['baidu_push_token'] ?? '' }}" class="layui-input" placeholder="站长平台推送 token">
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">神马Token</label>
          <div class="layui-input-block">
            <input type="text" name="shenma_push_token" value="{{ $site['shenma_push_token'] ?? '' }}" class="layui-input">
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">必应Key</label>
          <div class="layui-input-block">
            <input type="text" name="bing_push_token" value="{{ $site['bing_push_token'] ?? '' }}" class="layui-input">
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">微信商户号</label>
          <div class="layui-input-block">
            <input type="text" name="pay_wechat_mchid" value="{{ $site['pay_wechat_mchid'] ?? '' }}" class="layui-input" placeholder="配置后可对账；到账请在订单里改已付">
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">微信密钥</label>
          <div class="layui-input-block">
            <input type="text" name="pay_wechat_key" value="{{ $site['pay_wechat_key'] ?? '' }}" class="layui-input">
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">支付宝AppId</label>
          <div class="layui-input-block">
            <input type="text" name="pay_alipay_appid" value="{{ $site['pay_alipay_appid'] ?? '' }}" class="layui-input">
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">支付宝密钥</label>
          <div class="layui-input-block">
            <input type="text" name="pay_alipay_key" value="{{ $site['pay_alipay_key'] ?? '' }}" class="layui-input">
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">ICP备案</label>
          <div class="layui-input-block">
            <input type="text" name="icp" value="{{ $site['icp'] ?? '' }}" class="layui-input">
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">关闭站点</label>
          <div class="layui-input-block">
            <select name="site_closed">
              <option value="0" @selected(($site['site_closed'] ?? '0')==='0')>开启访问</option>
              <option value="1" @selected(($site['site_closed'] ?? '0')==='1')>维护关闭</option>
            </select>
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">关闭提示</label>
          <div class="layui-input-block">
            <input type="text" name="site_close_tip" value="{{ $site['site_close_tip'] ?? '' }}" class="layui-input">
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">采集入库状态</label>
          <div class="layui-input-block">
            <select name="collect_in_status">
              <option value="1" @selected(($site['collect_in_status'] ?? '1')==='1')>直接上架</option>
              <option value="0" @selected(($site['collect_in_status'] ?? '1')==='0')>入库待审</option>
            </select>
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">采集同步封面</label>
          <div class="layui-input-block">
            <select name="collect_sync_pic">
              <option value="1" @selected(($site['collect_sync_pic'] ?? '1')==='1')>同步</option>
              <option value="0" @selected(($site['collect_sync_pic'] ?? '1')==='0')>不同步</option>
            </select>
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">站外入库密钥</label>
          <div class="layui-input-block">
            <input type="text" name="inbound_key" value="{{ $site['inbound_key'] ?? '' }}" class="layui-input" placeholder="POST /api.php/receive/vod">
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">资源接口密钥</label>
          <div class="layui-input-block">
            <input type="text" name="provide_key" value="{{ $site['provide_key'] ?? '' }}" class="layui-input" placeholder="非空时 /api.php/provide/vod 需带 key">
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">采集人气最小</label>
          <div class="layui-input-block">
            <input type="number" name="collect_hits_min" value="{{ $site['collect_hits_min'] ?? 0 }}" class="layui-input" placeholder="新建时随机人气下限，0 不随机">
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">采集人气最大</label>
          <div class="layui-input-block">
            <input type="number" name="collect_hits_max" value="{{ $site['collect_hits_max'] ?? 0 }}" class="layui-input">
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">封面本地化</label>
          <div class="layui-input-block">
            <select name="collect_pic_local">
              <option value="0" @selected(($site['collect_pic_local'] ?? '0')==='0')>远程地址</option>
              <option value="1" @selected(($site['collect_pic_local'] ?? '0')==='1')>下载到本地</option>
            </select>
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">地区词库</label>
          <div class="layui-input-block">
            <textarea name="collect_areawords" class="layui-textarea" placeholder="每行 from=to 或 from,to，如 大陆=中国">{{ $site['collect_areawords'] ?? '' }}</textarea>
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">语言词库</label>
          <div class="layui-input-block">
            <textarea name="collect_langwords" class="layui-textarea" placeholder="每行 from=to 或 from,to">{{ $site['collect_langwords'] ?? '' }}</textarea>
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">SMTP主机</label>
          <div class="layui-input-block">
            <input type="text" name="smtp_host" value="{{ $site['smtp_host'] ?? '' }}" class="layui-input">
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">SMTP端口</label>
          <div class="layui-input-block">
            <input type="number" name="smtp_port" value="{{ $site['smtp_port'] ?? 465 }}" class="layui-input">
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">SMTP账号</label>
          <div class="layui-input-block">
            <input type="text" name="smtp_user" value="{{ $site['smtp_user'] ?? '' }}" class="layui-input">
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">SMTP密码</label>
          <div class="layui-input-block">
            <input type="password" name="smtp_pass" value="{{ $site['smtp_pass'] ?? '' }}" class="layui-input">
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">发件人</label>
          <div class="layui-input-block">
            <input type="text" name="smtp_from" value="{{ $site['smtp_from'] ?? '' }}" class="layui-input" placeholder="noreply@example.com">
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">测试邮箱</label>
          <div class="layui-input-block">
            <input type="email" id="test-mail-to" class="layui-input" placeholder="收件邮箱">
          </div>
        </div>
        <div class="layui-form-item">
          <div class="layui-input-block">
            <button type="button" class="layui-btn layui-btn-primary" id="site-test-mail">发送测试邮件</button>
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">允许注册</label>
          <div class="layui-input-block">
            <select name="member_register">
              <option value="1" @selected(($site['member_register'] ?? '1')==='1')>允许</option>
              <option value="0" @selected(($site['member_register'] ?? '1')==='0')>关闭</option>
            </select>
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">评论需登录</label>
          <div class="layui-input-block">
            <select name="member_comment_login">
              <option value="0" @selected(($site['member_comment_login'] ?? '0')==='0')>否</option>
              <option value="1" @selected(($site['member_comment_login'] ?? '0')==='1')>是</option>
            </select>
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">评论先审</label>
          <div class="layui-input-block">
            <select name="comment_audit">
              <option value="0" @selected(($site['comment_audit'] ?? '0')==='0')>直接显示</option>
              <option value="1" @selected(($site['comment_audit'] ?? '0')==='1')>审核后显示</option>
            </select>
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">留言先审</label>
          <div class="layui-input-block">
            <select name="gbook_audit">
              <option value="0" @selected(($site['gbook_audit'] ?? '0')==='0')>直接显示</option>
              <option value="1" @selected(($site['gbook_audit'] ?? '0')==='1')>审核后显示</option>
            </select>
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">弹幕</label>
          <div class="layui-input-block">
            <select name="danmaku_enabled">
              <option value="1" @selected(($site['danmaku_enabled'] ?? '1')==='1')>开启</option>
              <option value="0" @selected(($site['danmaku_enabled'] ?? '1')==='0')>关闭</option>
            </select>
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">弹幕需登录</label>
          <div class="layui-input-block">
            <select name="danmaku_login">
              <option value="0" @selected(($site['danmaku_login'] ?? '0')==='0')>否</option>
              <option value="1" @selected(($site['danmaku_login'] ?? '0')==='1')>是</option>
            </select>
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">试看秒数</label>
          <div class="layui-input-block">
            <input type="number" name="trysee_seconds" value="{{ $site['trysee_seconds'] ?? 0 }}" class="layui-input">
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">播放缓冲</label>
          <div class="layui-input-block">
            <input type="number" name="play_buffer" value="{{ $site['play_buffer'] ?? 5 }}" class="layui-input">
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">播放加密</label>
          <div class="layui-input-block">
            <select name="play_encrypt">
              <option value="0" @selected(($site['play_encrypt'] ?? '0')==='0')>明文</option>
              <option value="1" @selected(($site['play_encrypt'] ?? '0')==='1')>前端 Base64</option>
            </select>
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">违禁词</label>
          <div class="layui-input-block">
            <textarea name="banned_words" class="layui-textarea" placeholder="逗号或换行">{{ $site['banned_words'] ?? '' }}</textarea>
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">影片标题模板</label>
          <div class="layui-input-block">
            <input type="text" name="seo_title_vod" value="{{ $site['seo_title_vod'] ?? '' }}" class="layui-input" placeholder="{name} - {site}">
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">分类标题模板</label>
          <div class="layui-input-block">
            <input type="text" name="seo_title_type" value="{{ $site['seo_title_type'] ?? '' }}" class="layui-input" placeholder="{type} - {site}">
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">筛选地区</label>
          <div class="layui-input-block">
            <input type="text" name="filter_area" value="{{ $site['filter_area'] ?? '' }}" class="layui-input">
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">筛选语言</label>
          <div class="layui-input-block">
            <input type="text" name="filter_lang" value="{{ $site['filter_lang'] ?? '' }}" class="layui-input">
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">筛选年代</label>
          <div class="layui-input-block">
            <input type="text" name="filter_year" value="{{ $site['filter_year'] ?? '' }}" class="layui-input">
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">附件存储</label>
          <div class="layui-input-block">
            <select name="storage_disk">
              <option value="local" @selected(($site['storage_disk'] ?? 'local')==='local')>本地 public</option>
              <option value="s3" @selected(($site['storage_disk'] ?? '')==='s3')>S3/OSS/COS 兼容</option>
            </select>
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">S3 Key</label>
          <div class="layui-input-block"><input type="text" name="s3_key" value="{{ $site['s3_key'] ?? '' }}" class="layui-input"></div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">S3 Secret</label>
          <div class="layui-input-block"><input type="text" name="s3_secret" value="{{ $site['s3_secret'] ?? '' }}" class="layui-input"></div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">Region</label>
          <div class="layui-input-block"><input type="text" name="s3_region" value="{{ $site['s3_region'] ?? '' }}" class="layui-input"></div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">Bucket</label>
          <div class="layui-input-block"><input type="text" name="s3_bucket" value="{{ $site['s3_bucket'] ?? '' }}" class="layui-input"></div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">Endpoint</label>
          <div class="layui-input-block"><input type="text" name="s3_endpoint" value="{{ $site['s3_endpoint'] ?? '' }}" class="layui-input" placeholder="OSS/COS 自定义域名接口"></div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">访问URL</label>
          <div class="layui-input-block"><input type="text" name="s3_url" value="{{ $site['s3_url'] ?? '' }}" class="layui-input" placeholder="https://cdn.example.com"></div>
        </div>
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
  $('#site-test-mail').on('click', function(){
    var to = $.trim($('#test-mail-to').val() || '');
    if (!to) { layer.msg('请填写测试邮箱', {icon:2}); return; }
    var load = layer.load(1);
    $.post('/admin/video/settings/test-mail', {to: to}, function(res){
      layer.close(load);
      layer.msg((res && res.msg) ? res.msg : '完成', {icon: (res && res.code===0)?1:2, time:4000});
    }, 'json').fail(function(){ layer.close(load); layer.msg('发送失败',{icon:2}); });
  });
});
</script>
</body>
</html>
