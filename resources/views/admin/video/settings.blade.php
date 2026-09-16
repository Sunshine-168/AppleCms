@extends('admin.layouts.inner')
@section('title', admin_t('page.settings'))

@section('plain')
<div class="card card-panel">
    <div class="card-header"><span>站点设置</span></div>
    <div class="card-body">
        <form id="site-form">
            <label>站点名称</label>
            <input type="text" name="site_title" value="{{ $site['title'] ?? '' }}">
            <label>关键词</label>
            <input type="text" name="site_keyword" value="{{ $site['keyword'] ?? '' }}">
            <label>描述</label>
            <textarea name="site_description">{{ $site['description'] ?? '' }}</textarea>
            <label>全页缓存</label>
            <select name="html_cache_enabled">
                <option value="0" @selected(!($site['html_cache_enabled'] ?? false))>关闭</option>
                <option value="1" @selected($site['html_cache_enabled'] ?? false)>开启</option>
            </select>
            <label>缓存秒数</label>
            <input type="number" name="html_cache_ttl" value="{{ $site['html_cache_ttl'] ?? 3600 }}">
            <label>伪静态</label>
            <select name="rewrite_mode">
                <option value="laravel" @selected(($site['rewrite_mode'] ?? 'laravel')==='laravel')>Laravel 路由</option>
                <option value="mac" @selected(($site['rewrite_mode'] ?? '')==='mac')>苹果风格 index.php/vod</option>
            </select>
            <label>后缀</label>
            <input type="text" name="rewrite_suffix" value="{{ $site['rewrite_suffix'] ?? '.html' }}">
            <p class="hint">Laravel：<code>/vod/123</code>　苹果：<code>/index.php/vod/detail/id/123.html</code></p>
            <label>百度 Token</label>
            <input type="text" name="baidu_push_token" value="{{ $site['baidu_push_token'] ?? '' }}" placeholder="站长平台推送 token">
            <label>神马 Token</label>
            <input type="text" name="shenma_push_token" value="{{ $site['shenma_push_token'] ?? '' }}">
            <label>必应 Key</label>
            <input type="text" name="bing_push_token" value="{{ $site['bing_push_token'] ?? '' }}">
            <label>ICP 备案</label>
            <input type="text" name="icp" value="{{ $site['icp'] ?? '' }}">
            <label>关闭站点</label>
            <select name="site_closed">
                <option value="0" @selected(($site['site_closed'] ?? '0')==='0')>开启访问</option>
                <option value="1" @selected(($site['site_closed'] ?? '0')==='1')>维护关闭</option>
            </select>
            <label>关闭提示</label>
            <input type="text" name="site_close_tip" value="{{ $site['site_close_tip'] ?? '' }}">
            <label>采集入库状态</label>
            <select name="collect_in_status">
                <option value="1" @selected(($site['collect_in_status'] ?? '1')==='1')>直接上架</option>
                <option value="0" @selected(($site['collect_in_status'] ?? '1')==='0')>入库待审</option>
            </select>
            <label>采集同步封面</label>
            <select name="collect_sync_pic">
                <option value="1" @selected(($site['collect_sync_pic'] ?? '1')==='1')>同步</option>
                <option value="0" @selected(($site['collect_sync_pic'] ?? '1')==='0')>不同步</option>
            </select>
            <label>站外入库密钥</label>
            <input type="text" name="inbound_key" value="{{ $site['inbound_key'] ?? '' }}" placeholder="POST /api.php/receive/vod">
            <label>资源接口密钥</label>
            <input type="text" name="provide_key" value="{{ $site['provide_key'] ?? '' }}" placeholder="非空时 /api.php/provide/vod 需带 key">
            <label>采集人气最小</label>
            <input type="number" name="collect_hits_min" value="{{ $site['collect_hits_min'] ?? 0 }}" placeholder="新建时随机人气下限，0 不随机">
            <label>采集人气最大</label>
            <input type="number" name="collect_hits_max" value="{{ $site['collect_hits_max'] ?? 0 }}">
            <label>封面本地化</label>
            <select name="collect_pic_local">
                <option value="0" @selected(($site['collect_pic_local'] ?? '0')==='0')>远程地址</option>
                <option value="1" @selected(($site['collect_pic_local'] ?? '0')==='1')>下载到本地</option>
            </select>
            <label>地区词库</label>
            <textarea name="collect_areawords" placeholder="每行 from=to 或 from,to，如 大陆=中国">{{ $site['collect_areawords'] ?? '' }}</textarea>
            <label>语言词库</label>
            <textarea name="collect_langwords" placeholder="每行 from=to 或 from,to">{{ $site['collect_langwords'] ?? '' }}</textarea>
            <label>SMTP 主机</label>
            <input type="text" name="smtp_host" value="{{ $site['smtp_host'] ?? '' }}">
            <label>SMTP 端口</label>
            <input type="number" name="smtp_port" value="{{ $site['smtp_port'] ?? 465 }}">
            <label>SMTP 账号</label>
            <input type="text" name="smtp_user" value="{{ $site['smtp_user'] ?? '' }}">
            <label>SMTP 密码</label>
            <input type="password" name="smtp_pass" value="{{ $site['smtp_pass'] ?? '' }}">
            <label>发件人</label>
            <input type="text" name="smtp_from" value="{{ $site['smtp_from'] ?? '' }}" placeholder="noreply@example.com">
            <label>测试邮箱</label>
            <div class="field-inline">
                <input type="email" id="test-mail-to" placeholder="收件邮箱">
                <button type="button" class="btn btn-muted" id="site-test-mail">发送测试邮件</button>
            </div>
            <label>允许注册</label>
            <select name="member_register">
                <option value="1" @selected(($site['member_register'] ?? '1')==='1')>允许</option>
                <option value="0" @selected(($site['member_register'] ?? '1')==='0')>关闭</option>
            </select>
            <label>评论需登录</label>
            <select name="member_comment_login">
                <option value="0" @selected(($site['member_comment_login'] ?? '0')==='0')>否</option>
                <option value="1" @selected(($site['member_comment_login'] ?? '0')==='1')>是</option>
            </select>
            <label>评论先审</label>
            <select name="comment_audit">
                <option value="0" @selected(($site['comment_audit'] ?? '0')==='0')>直接显示</option>
                <option value="1" @selected(($site['comment_audit'] ?? '0')==='1')>审核后显示</option>
            </select>
            <label>留言先审</label>
            <select name="gbook_audit">
                <option value="0" @selected(($site['gbook_audit'] ?? '0')==='0')>直接显示</option>
                <option value="1" @selected(($site['gbook_audit'] ?? '0')==='1')>审核后显示</option>
            </select>
            <label>试看秒数</label>
            <input type="number" name="trysee_seconds" value="{{ $site['trysee_seconds'] ?? 0 }}">
            <label>播放缓冲</label>
            <input type="number" name="play_buffer" value="{{ $site['play_buffer'] ?? 5 }}">
            <label>播放加密</label>
            <select name="play_encrypt">
                <option value="0" @selected(($site['play_encrypt'] ?? '0')==='0')>明文</option>
                <option value="1" @selected(($site['play_encrypt'] ?? '0')==='1')>前端 Base64</option>
            </select>
            <label>违禁词</label>
            <textarea name="banned_words" placeholder="逗号或换行">{{ $site['banned_words'] ?? '' }}</textarea>
            <label>影片标题模板</label>
            <input type="text" name="seo_title_vod" value="{{ $site['seo_title_vod'] ?? '' }}" placeholder="{name} - {site}">
            <label>分类标题模板</label>
            <input type="text" name="seo_title_type" value="{{ $site['seo_title_type'] ?? '' }}" placeholder="{type} - {site}">
            <label>筛选地区</label>
            <input type="text" name="filter_area" value="{{ $site['filter_area'] ?? '' }}">
            <label>筛选语言</label>
            <input type="text" name="filter_lang" value="{{ $site['filter_lang'] ?? '' }}">
            <label>筛选年代</label>
            <input type="text" name="filter_year" value="{{ $site['filter_year'] ?? '' }}">
            <label>附件存储</label>
            <select name="storage_disk">
                <option value="local" @selected(($site['storage_disk'] ?? 'local')==='local')>本地 public</option>
                <option value="s3" @selected(($site['storage_disk'] ?? '')==='s3')>S3/OSS/COS 兼容</option>
            </select>
            <label>S3 Key</label>
            <input type="text" name="s3_key" value="{{ $site['s3_key'] ?? '' }}">
            <label>S3 Secret</label>
            <input type="text" name="s3_secret" value="{{ $site['s3_secret'] ?? '' }}">
            <label>Region</label>
            <input type="text" name="s3_region" value="{{ $site['s3_region'] ?? '' }}">
            <label>Bucket</label>
            <input type="text" name="s3_bucket" value="{{ $site['s3_bucket'] ?? '' }}">
            <label>Endpoint</label>
            <input type="text" name="s3_endpoint" value="{{ $site['s3_endpoint'] ?? '' }}" placeholder="OSS/COS 自定义域名接口">
            <label>访问 URL</label>
            <input type="text" name="s3_url" value="{{ $site['s3_url'] ?? '' }}" placeholder="https://cdn.example.com">
            <div class="form-actions">
                <button type="button" class="btn" id="site-save">保存</button>
            </div>
        </form>
    </div>
</div>
<div class="card card-panel">
    <div class="card-header"><span>更多参数</span></div>
    <div class="card-body">
        <p class="hint">常用站点信息在上面保存。其余参数按类打开，完整清单在「全部功能」。</p>
        <div class="toolbar">
            <a class="btn btn-sm" href="/admin/video/config/seo">SEO</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/config/theme">主题</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/config/player">播放器</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/config/user">会员</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/config/email">邮件</a>
            @foreach(app(\App\Plugins\PluginHost::class)->settingsLinks() as $link)
                <a class="btn btn-muted btn-sm" href="{{ $link['url'] }}">{{ admin_t($link['label']) }}</a>
            @endforeach
            <a class="btn btn-muted btn-sm" href="/admin/video/config/ip">后台 IP</a>
            <a class="btn btn-muted btn-sm" href="/admin/plugins">{{ admin_t('nav.plugins') }}</a>
            <a class="btn btn-muted btn-sm" href="/admin/more">全部功能</a>
        </div>
    </div>
</div>
@endsection

@include('admin.partials.site-save')
