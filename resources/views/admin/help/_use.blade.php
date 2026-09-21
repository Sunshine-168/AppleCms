@php
    $urls = $urls ?? [];
    $hl = $hl ?? fn (string $key, string $label) => e($label);
@endphp
<p class="muted">前台长什么样由<strong>模板</strong>决定，片库由<strong>分类和影片</strong>决定。侧栏每一项见「<a href="{{ $urls['helpAdmin'] ?? '#' }}">后台</a>」，改 Blade 见「<a href="{{ $urls['helpTemplates'] ?? '#' }}">模板</a>」，取数据见「<a href="{{ $urls['tags'] ?? '#' }}">标签</a>」。</p>

<div class="help-jumps">
    <a href="{{ $urls['settings'] ?? '/admin/video/settings' }}">改站名</a>
    <a href="{{ $urls['types'] ?? '/admin/video/types' }}">建分类</a>
    <a href="{{ $urls['videos'] ?? '/admin/video' }}">加影片</a>
    <a href="{{ $urls['collects'] ?? '/admin/video/collects' }}">采集入库</a>
    <a href="{{ $urls['cache'] ?? '/admin/system/tools/cache' }}">清缓存</a>
    <a href="{{ $urls['front'] ?? url('/') }}" target="_blank" rel="noopener">看前台</a>
</div>

<h3>第一次建议按这个顺序</h3>
<ol class="help-steps">
    <li>{!! $hl('settings', '站点设置') !!} 里改网站名称、关键词、一句话介绍。</li>
    <li>{!! $hl('types', '分类') !!} 建影片分类（电影、电视剧、综艺）。分类决定前台列表和筛选。</li>
    <li>{!! $hl('videos', '影片') !!} 手动加片，或到{!! $hl('collects', '采集') !!}接资源站入库。</li>
    <li>{!! $hl('players', '播放器') !!} 确认播放地址能播。线路和解析在这里配。</li>
    <li>打开前台看效果。还是旧样子，到{!! $hl('cache', '缓存') !!}清空。</li>
    <li>访问量大再开{!! $hl('make', '静态生成') !!}，写出 <code>public/html</code>。漫画、图集、小说、直播在{!! $hl('plugins', '插件') !!}里开关。</li>
</ol>
<p class="hint">安装时勾了演示数据，前台已经有分类和影片，改文案即可。空站则从分类开始建。侧栏没有的项是当前账号没权限。说明不跟权限走，所以留在系统最底下。</p>

<h3>侧栏对应什么</h3>
<table class="help-doc">
    <thead>
        <tr><th>位置</th><th>做什么</th></tr>
    </thead>
    <tbody>
        <tr><td>工作台</td><td>{!! $hl('dashboard', '仪表盘') !!}、{!! $hl('stats', '统计') !!}、常用入口。{!! $hl('plugins', '插件') !!}和搜功能也在这里。</td></tr>
        <tr><td>影片</td><td>{!! $hl('videos', '片库') !!}、{!! $hl('types', '分类') !!}、{!! $hl('topics', '专题') !!}、{!! $hl('actors', '演员') !!}、评论。片要挂分类，前台列表才出得来。</td></tr>
        <tr><td>采集</td><td>{!! $hl('collects', '采集源') !!}。接苹果接口，先试抓再定时。自动跑需要配 cron，见「<a href="{{ $urls['scheduleHelp'] ?? '#' }}">定时</a>」。</td></tr>
        <tr><td>会员</td><td>{!! $hl('members', '会员') !!}、分组、卡密、订单。前台 <code>/member</code> 注册登录。</td></tr>
        <tr><td>站点</td><td>{!! $hl('settings', '设置') !!}、{!! $hl('templates', '模板') !!}、{!! $hl('players', '播放器') !!}、{!! $hl('make', '静态生成') !!}、{!! $hl('rewrite', '伪静态') !!}。</td></tr>
        <tr><td>系统</td><td>管理员、角色、日志、备份、缓存、定时，以及本页说明。</td></tr>
    </tbody>
</table>

<h3>分类和影片</h3>
<p class="muted">分类是频道。影片挂一个主分类（决定网址），也可以勾扩展分类，出现在多个列表里。状态要「已审」，发布时间已到，前台才看得到。</p>
<p class="muted">播放地址按线路写。一条线路对应一种播放器（iframe、DPlayer 等）。解析接口在{!! $hl('players', '播放器') !!}里配，不要把密钥写进模板。</p>
<p class="muted">资讯、专题、演员是片库旁边的内容，不是分类的替代。漫画 / 图集 / 小说 / 直播是插件，启用后顶栏会多一栏。</p>

<h3>采集、静态页、插件</h3>
<p class="muted">{!! $hl('collects', '采集') !!}只拉你有权使用的源。先绑定分类，再试抓。到点自动采要系统每分钟跑 <code>schedule:run</code>，见「定时」。</p>
<p class="muted">{!! $hl('make', '静态生成') !!}把页面写成 <code>public/html</code> 文件。nginx 要优先读这些文件，写法见「<a href="{{ $urls['env'] ?? '#' }}">环境</a>」。漫画分类等要用路径地址（如 <code>/manga/type/1</code>），不能靠查询参数静态化。</p>
<p class="muted">{!! $hl('plugins', '插件') !!}没有商店。zip 自己上传，关掉也能先填密钥；启用后才出现前台和后台入口。AI SEO 在{!! $hl('ai', 'AI 写内容') !!}。</p>

<h3>常见问题</h3>
<div class="help-faq">
    <details>
        <summary>换了模板，前台还是旧样子</summary>
        <p>页面缓存还在。到{!! $hl('cache', '缓存') !!}点清空，或删掉 <code>storage/framework/views</code> 再刷新。</p>
    </details>
    <details>
        <summary>写了影片前台看不到</summary>
        <p>确认已<strong>审核通过</strong>，发布时间已到，且挂在对的分类。草稿和待审不会出现在列表里。</p>
    </details>
    <details>
        <summary>采集不自动跑</summary>
        <p>本机 <code>php artisan serve</code> 不会自己执行定时。正式站要每分钟跑 <code>php artisan schedule:run</code>，见「<a href="{{ $urls['scheduleHelp'] ?? '#' }}">定时</a>」。后台也可以点立即采集。</p>
    </details>
    <details>
        <summary>播放页没画面</summary>
        <p>核对播放地址、播放器类型和解析接口。线路空着的片子详情页能开、播放页打不开。广告插件挡住播放器时先关掉广告位试试。</p>
    </details>
    <details>
        <summary>不想让搜索引擎抓某几页</summary>
        <p>前台 <code>/robots.txt</code> 默认禁止 <code>/admin</code>、<code>/install</code>、<code>/member</code>。还要挡路径时到{!! $hl('settings', '站点设置') !!}写补充规则。站点地图在 <code>/sitemap.xml</code>，也可在{!! $hl('make', '静态生成') !!}里生成。</p>
    </details>
    <details>
        <summary>漫画、图集、小说、直播菜单没有</summary>
        <p>到{!! $hl('plugins', '插件') !!}启用对应插件。关掉后顶栏和侧栏都会收起来，数据还在。</p>
    </details>
</div>
