@extends('admin.layouts.inner')
@section('title', admin_t('page.settings'))

@php
    $s = $site ?? [];
    $tab = $tab ?? 'site';
    $tabs = [
        'site' => '网站',
        'look' => '外观',
        'interact' => '评论',
        'more' => '更多',
    ];
    $on = fn (string $k, string $d = '0') => (string) ($s[$k] ?? $d) === '1';
    $logo = trim((string) ($s['theme_logo'] ?? ''));
    $pluginLinks = $pluginLinks ?? [];
@endphp

@section('plain')
<div class="card card-panel">
    <div class="card-header"><span>站点设置</span></div>
    <div class="card-body">
        <p class="muted recycle-lead">改网站名字、Logo 和评论规则。缓存、采集、发信、密钥在「更多」里。</p>
        <form class="settings-page" id="site-form">
            <div class="tabs settings-tabs" id="settingsTabs">
                @foreach($tabs as $key => $label)
                    <button type="button" class="{{ $tab === $key ? 'active' : '' }}" data-tab="{{ $key }}">{{ $label }}</button>
                @endforeach
            </div>

            <div class="settings-pane{{ $tab === 'site' ? ' active' : '' }}" data-pane="site">
                <label for="site_title">网站名称</label>
                <input id="site_title" type="text" name="site_title" value="{{ $s['site_title'] ?? ($s['title'] ?? '') }}" placeholder="会出现在浏览器标题和页脚">

                <label for="site_description">一句话介绍</label>
                <textarea id="site_description" name="site_description" rows="3">{{ $s['site_description'] ?? ($s['description'] ?? '') }}</textarea>
                <p class="muted field-hint">搜索引擎和分享卡片会用到。尽量写清这是什么影视站。</p>

                <label for="site_keyword">搜索关键词</label>
                <input id="site_keyword" type="text" name="site_keyword" value="{{ $s['site_keyword'] ?? ($s['keyword'] ?? '') }}" placeholder="用逗号分开，如 电影,电视剧">
                <p class="muted field-hint">给搜索引擎看，前台访客一般看不到。</p>

                <input type="hidden" name="site_closed" value="0">
                <label class="inline">
                    <input type="checkbox" name="site_closed" value="1" @checked($on('site_closed'))>
                    暂时关闭网站
                </label>
                <label for="site_close_tip">关站时访客看到的说明</label>
                <input id="site_close_tip" type="text" name="site_close_tip" value="{{ $s['site_close_tip'] ?? '站点维护中' }}">

                <label for="icp">备案号</label>
                <input id="icp" type="text" name="icp" value="{{ $s['icp'] ?? '' }}" placeholder="京ICP备00000000号-1">
            </div>

            <div class="settings-pane{{ $tab === 'look' ? ' active' : '' }}" data-pane="look">
                <p class="muted field-hint">改 Logo 和主色，不用改页面文件。图标、导航、页头代码在「<a href="/admin/video/templates">模板 → 外观</a>」。皮肤文件在「<a href="/admin/video/templates?desk=files">模板 → 文件</a>」。</p>

                <label for="theme_logo">网站 Logo</label>
                <div class="settings-file-preview" id="logo-preview">
                    <div class="settings-file-thumb{{ $logo === '' ? ' is-empty' : '' }}" id="logo-thumb">
                        <img id="logo-img" src="{{ $logo }}" alt="" @if($logo === '') hidden @endif>
                        <span class="settings-file-empty muted" id="logo-empty" @if($logo !== '') hidden @endif>还没有 Logo</span>
                    </div>
                    <input id="theme_logo" type="text" name="theme_logo" value="{{ $logo }}" placeholder="图片地址，或点上传">
                    <button type="button" class="btn btn-muted btn-sm" id="logo-upload">上传图片</button>
                </div>
                <p class="muted field-hint">出现在前台页头。建议用透明底的横图。</p>

                <label for="theme_primary">主色</label>
                <input id="theme_primary" type="text" name="theme_primary" value="{{ $s['theme_primary'] ?? '' }}" placeholder="#1b4f72" maxlength="7">
                <p class="muted field-hint">如 #1b4f72。留空则用模板自带配色。</p>

                <label for="watermark_text">封面水印</label>
                <input id="watermark_text" type="text" name="watermark_text" value="{{ $s['watermark_text'] ?? '' }}" placeholder="© 本站">
                <p class="muted field-hint">采集封面下载到本地时写在右下角。留空则不加。</p>
            </div>

            <div class="settings-pane{{ $tab === 'interact' ? ' active' : '' }}" data-pane="interact">
                <div class="theme-nav-toggles">
                <input type="hidden" name="member_register" value="0">
                <label class="inline">
                    <input type="checkbox" name="member_register" value="1" @checked($on('member_register', '1'))>
                    允许前台注册
                </label>
                <input type="hidden" name="member_invite" value="0">
                <label class="inline">
                    <input type="checkbox" name="member_invite" value="1" @checked($on('member_invite'))>
                    注册必须填邀请码
                </label>
                <input type="hidden" name="member_comment_login" value="0">
                <label class="inline">
                    <input type="checkbox" name="member_comment_login" value="1" @checked($on('member_comment_login'))>
                    评论必须先登录
                </label>
                <input type="hidden" name="comment_audit" value="0">
                <label class="inline">
                    <input type="checkbox" name="comment_audit" value="1" @checked($on('comment_audit'))>
                    新评论要先审再显示
                </label>
                <input type="hidden" name="gbook_audit" value="0">
                <label class="inline">
                    <input type="checkbox" name="gbook_audit" value="1" @checked($on('gbook_audit'))>
                    留言要先审再显示
                </label>
                </div>

                <label for="trysee_seconds">试看秒数</label>
                <input id="trysee_seconds" type="number" name="trysee_seconds" min="0" value="{{ $s['trysee_seconds'] ?? 0 }}">
                <p class="muted field-hint">积分不够时能看几秒。会员组里也可以单独设。0 表示不单独给试看。</p>

                <label for="banned_words">屏蔽词</label>
                <textarea id="banned_words" name="banned_words" rows="4" placeholder="逗号或换行">{{ $s['banned_words'] ?? '' }}</textarea>
                <p class="muted field-hint">评论和留言里出现这些词会被拦住。</p>
            </div>

            <div class="settings-pane{{ $tab === 'more' ? ' active' : '' }}" data-pane="more">
                <p class="muted field-hint">一般不用天天改。改影片链接方式后，旧地址可能打不开。</p>

                <h3>影片地址</h3>
                <label for="rewrite_mode">链接怎么写</label>
                <select id="rewrite_mode" name="rewrite_mode">
                    <option value="laravel" @selected(($s['rewrite_mode'] ?? 'laravel') === 'laravel')>本站路由 /vod/123</option>
                    <option value="mac" @selected(($s['rewrite_mode'] ?? '') === 'mac')>苹果风格 /index.php/vod/detail/id/123.html</option>
                </select>
                <label for="rewrite_suffix">后缀</label>
                <input id="rewrite_suffix" type="text" name="rewrite_suffix" value="{{ $s['rewrite_suffix'] ?? '.html' }}">
                <p class="muted field-hint">苹果风格才会用到后缀。本站路由一般是 <code>/vod/123</code>。服务器要抄的 Nginx / Apache 在「<a href="/admin/video/rewrite">伪静态</a>」。</p>

                <h3>页面缓存</h3>
                <p class="muted field-hint">全页缓存和磁盘静态页请到 <a href="/admin/video/make">静态生成</a> 里开关、预热和写出文件。</p>

                <h3>采集入库</h3>
                <p class="muted field-hint">直接入库还是先待审、封面、人气、地区对照，都在「<a href="/admin/video/config/collect">内容接入</a>」里改。</p>

                <h3>播放</h3>
                <label for="play_buffer">开始缓冲（秒）</label>
                <input id="play_buffer" type="number" name="play_buffer" min="0" value="{{ $s['play_buffer'] ?? 5 }}">
                <input type="hidden" name="play_encrypt" value="0">
                <label class="inline">
                    <input type="checkbox" name="play_encrypt" value="1" @checked($on('play_encrypt'))>
                    播放地址前端编码
                </label>
                <p class="muted field-hint">只是页面里用 Base64，防不了会看源码的人。</p>

                <h3>发信</h3>
                <p class="muted field-hint">找回密码、通知会用到。密码留空表示不改已保存的值。</p>
                <label for="smtp_host">邮件服务器</label>
                <input id="smtp_host" type="text" name="smtp_host" value="{{ $s['smtp_host'] ?? '' }}" placeholder="smtp.example.com">
                <div class="settings-two">
                    <div>
                        <label for="smtp_port">端口</label>
                        <input id="smtp_port" type="number" name="smtp_port" value="{{ $s['smtp_port'] ?? 465 }}">
                    </div>
                </div>
                <p class="muted field-hint">465 一般用 SSL，587 用 TLS。</p>
                <label for="smtp_user">登录账号</label>
                <input id="smtp_user" type="text" name="smtp_user" value="{{ $s['smtp_user'] ?? '' }}">
                <label for="smtp_pass">密码</label>
                <input id="smtp_pass" type="password" name="smtp_pass" value="" autocomplete="new-password" placeholder="{{ trim((string) ($s['smtp_pass'] ?? '')) !== '' ? '已保存，留空不改' : '' }}">
                <label for="smtp_from">发件人邮箱</label>
                <input id="smtp_from" type="email" name="smtp_from" value="{{ $s['smtp_from'] ?? '' }}" placeholder="noreply@example.com">
                <label for="test-mail-to">测试邮箱</label>
                <div class="field-inline">
                    <input type="email" id="test-mail-to" placeholder="收件邮箱">
                    <button type="button" class="btn btn-muted" id="site-test-mail">发送测试邮件</button>
                </div>

                <h3>标题模板</h3>
                <label for="seo_title_vod">影片页</label>
                <input id="seo_title_vod" type="text" name="seo_title_vod" value="{{ $s['seo_title_vod'] ?? '' }}" placeholder="{name} - {site}">
                <label for="seo_title_type">分类页</label>
                <input id="seo_title_type" type="text" name="seo_title_type" value="{{ $s['seo_title_type'] ?? '' }}" placeholder="{type} - {site}">
                <label for="seo_title_play">播放页</label>
                <input id="seo_title_play" type="text" name="seo_title_play" value="{{ $s['seo_title_play'] ?? '' }}" placeholder="{name} 在线播放 - {site}">
                <p class="muted field-hint">可用 <code>{name}</code> <code>{type}</code> <code>{site}</code>。</p>

                <h3>前台筛选</h3>
                <label for="filter_area">地区</label>
                <input id="filter_area" type="text" name="filter_area" value="{{ $s['filter_area'] ?? '' }}">
                <label for="filter_lang">语言</label>
                <input id="filter_lang" type="text" name="filter_lang" value="{{ $s['filter_lang'] ?? '' }}">
                <label for="filter_year">年代</label>
                <input id="filter_year" type="text" name="filter_year" value="{{ $s['filter_year'] ?? '' }}">
                <p class="muted field-hint">逗号分开。字典里「地区 / 语言 / 年份」有启用项时，前台筛选用字典，这里当后备。<a href="/admin/system/dicts">去字典按条维护</a>。</p>

                <h3>统计代码</h3>
                <textarea id="analytics_code" name="analytics_code" rows="4" placeholder="把统计平台给的代码贴在这里">{{ $s['analytics_code'] ?? '' }}</textarea>
                <p class="muted field-hint">会插到前台页面底部，如百度统计。</p>

                <h3>附件存储</h3>
                <label for="storage_disk">存在哪里</label>
                <select id="storage_disk" name="storage_disk">
                    <option value="local" @selected(($s['storage_disk'] ?? 'local') === 'local')>本站磁盘</option>
                    <option value="s3" @selected(($s['storage_disk'] ?? '') === 's3')>对象存储（S3 / OSS / COS）</option>
                </select>
                <details class="settings-details">
                    <summary>对象存储参数</summary>
                    <label for="s3_key">Access Key</label>
                    <input id="s3_key" type="text" name="s3_key" value="{{ $s['s3_key'] ?? '' }}">
                    <label for="s3_secret">Secret</label>
                    <input id="s3_secret" type="password" name="s3_secret" value="" autocomplete="new-password" placeholder="{{ trim((string) ($s['s3_secret'] ?? '')) !== '' ? '已保存，留空不改' : '' }}">
                    <label for="s3_region">Region</label>
                    <input id="s3_region" type="text" name="s3_region" value="{{ $s['s3_region'] ?? '' }}">
                    <label for="s3_bucket">Bucket</label>
                    <input id="s3_bucket" type="text" name="s3_bucket" value="{{ $s['s3_bucket'] ?? '' }}">
                    <label for="s3_endpoint">接口地址</label>
                    <input id="s3_endpoint" type="text" name="s3_endpoint" value="{{ $s['s3_endpoint'] ?? '' }}" placeholder="OSS / COS 的 Endpoint">
                    <label for="s3_url">访问域名</label>
                    <input id="s3_url" type="url" name="s3_url" value="{{ $s['s3_url'] ?? '' }}" placeholder="https://cdn.example.com">
                    <p class="muted field-hint">图片会改到这个域名。留空则用接口默认地址。</p>
                </details>

                <details class="settings-details">
                    <summary>入库与资源接口</summary>
                    <p class="muted field-hint">别人来拉本站片子在「<a href="/admin/video/config/api">开放 API</a>」。别人 POST 片子进来在「<a href="/admin/video/config/interface">入库接口</a>」。你去拉别人在「<a href="/admin/video/collects">采集源</a>」。</p>
                </details>

                <details class="settings-details">
                    <summary>搜索引擎推送</summary>
                    <p class="muted field-hint">百度 / 神马 / 必应的 Token 和推送在「<a href="/admin/video/push">搜索推送</a>」里。sitemap 也在那页复制。</p>
                </details>

                <details class="settings-details">
                    <summary>上传</summary>
                    <label for="upload_ext">允许的扩展名</label>
                    <input id="upload_ext" type="text" name="upload_ext" value="{{ $s['upload_ext'] ?? '' }}">
                    <label for="upload_max_mb">最大体积（MB）</label>
                    <input id="upload_max_mb" type="number" name="upload_max_mb" min="1" value="{{ $s['upload_max_mb'] ?? 8 }}">
                    <p class="muted field-hint">后台登录来源限制在「<a href="/admin/video/config/ip">后台 IP 白名单</a>」里改，避免在这里误存把自己锁出去。</p>
                </details>

                @if($pluginLinks !== [])
                    <h3>插件参数</h3>
                    <p class="muted field-hint">支付、短信这些在插件里单独配。</p>
                    <div class="toolbar">
                        @foreach($pluginLinks as $link)
                            <a class="btn btn-muted btn-sm" href="{{ $link['url'] }}">{{ admin_t($link['label']) }}</a>
                        @endforeach
                        <a class="btn btn-muted btn-sm" href="/admin/plugins">插件</a>
                    </div>
                @endif
            </div>

            <div class="form-actions settings-save">
                <button type="button" class="btn" id="site-save">{{ admin_t('page.save') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection

@include('admin.partials.site-save')

@push('scripts')
<script>
(function () {
    var tabs = document.getElementById('settingsTabs');
    if (tabs) {
        tabs.querySelectorAll('[data-tab]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var tab = btn.getAttribute('data-tab') || 'site';
                tabs.querySelectorAll('[data-tab]').forEach(function (item) {
                    item.classList.toggle('active', item === btn);
                });
                document.querySelectorAll('.settings-pane').forEach(function (pane) {
                    pane.classList.toggle('active', pane.getAttribute('data-pane') === tab);
                });
                var url = new URL(window.location.href);
                if (tab === 'site') url.searchParams.delete('tab');
                else url.searchParams.set('tab', tab);
                history.replaceState(null, '', url);
            });
        });
    }

    var input = document.getElementById('theme_logo');
    var img = document.getElementById('logo-img');
    var empty = document.getElementById('logo-empty');
    var thumb = document.getElementById('logo-thumb');
    function showLogo(url) {
        url = String(url || '').trim();
        if (!img || !empty || !thumb) return;
        if (!url) {
            img.removeAttribute('src');
            img.hidden = true;
            empty.hidden = false;
            thumb.classList.add('is-empty');
            return;
        }
        img.hidden = false;
        empty.hidden = true;
        thumb.classList.remove('is-empty');
        if (img.getAttribute('src') !== url) img.src = url;
    }
    if (input) {
        input.addEventListener('input', function () { showLogo(input.value); });
    }
    var up = document.getElementById('logo-upload');
    if (up && window.AdminUi) {
        up.addEventListener('click', function () {
            AdminUi.pickFile('image/*').then(function (file) {
                if (!file) return;
                AdminUi.loading(true);
                return AdminUi.upload(file).then(function (res) {
                    AdminUi.loading(false);
                    if (res && res.code === 0 && res.data && res.data.url) {
                        input.value = res.data.url;
                        showLogo(res.data.url);
                        AdminUi.toast('上传成功', 'ok');
                    } else {
                        AdminUi.toast((res && res.msg) || '上传失败', 'err');
                    }
                });
            });
        });
    }
})();
</script>
@endpush
