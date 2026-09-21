@php
    $urls = $urls ?? [];
    $hl = $hl ?? fn (string $key, string $label) => e($label);
@endphp
<p class="muted">按侧栏把后台每一项说一遍。各功能页顶部也有一句说明。先上手看「<a href="{{ route('admin.help') }}">怎么用</a>」，改皮看「<a href="{{ $urls['helpTemplates'] ?? '#' }}">模板</a>」。</p>

<h3>工作台</h3>
<table class="help-doc">
    <thead>
        <tr><th>页面</th><th>做什么</th></tr>
    </thead>
    <tbody>
        <tr><td>{!! $hl('dashboard', '仪表盘') !!}</td><td>待审影片、今日采集、会员概况。常用入口可自己钉。</td></tr>
        <tr><td>{!! $hl('stats', '统计') !!}</td><td>前台访问的 PV / UV / IP、热门页、来路。另有蜘蛛抓取。后台路径不记。</td></tr>
        <tr><td>{!! $hl('plugins', '插件') !!}</td><td>本地插件开关。没有商店。zip 自己传；启用后才出菜单。</td></tr>
    </tbody>
</table>

<h3>影片</h3>
<table class="help-doc">
    <thead>
        <tr><th>页面</th><th>做什么</th></tr>
    </thead>
    <tbody>
        <tr><td>{!! $hl('videos', '影片') !!}</td><td>片库。标题、封面、简介、播放线路、下载地址。要审核通过前台才列出。可批量改分类、播放器、推荐属性。</td></tr>
        <tr><td>{!! $hl('types', '分类') !!}</td><td>频道结构。决定列表页和筛选。可设 SEO 标题、模板、是否在导航显示。</td></tr>
        <tr><td>{!! $hl('topics', '专题') !!}</td><td>把若干部片子打成一组（如「春节档」）。前台 <code>/topics</code>。</td></tr>
        <tr><td>{!! $hl('actors', '演员') !!}</td><td>影人库。影片里填的主演会尽量对上这里。</td></tr>
        <tr><td>{!! $hl('comments', '评论') !!}</td><td>影片下的评论。可审核、删除。开关在站点设置。</td></tr>
        <tr><td>{!! $hl('arts', '文章') !!}</td><td>资讯栏目，不是片库。有自己的分类和标签。</td></tr>
    </tbody>
</table>

<h3>采集</h3>
<table class="help-doc">
    <thead>
        <tr><th>页面</th><th>做什么</th></tr>
    </thead>
    <tbody>
        <tr><td>{!! $hl('collects', '采集源') !!}</td><td>苹果 CMS 接口。先绑分类，再试抓。只采集你有权使用的源。自动跑见「<a href="{{ $urls['scheduleHelp'] ?? '#' }}">定时</a>」。</td></tr>
    </tbody>
</table>

<h3>会员与站点</h3>
<table class="help-doc">
    <thead>
        <tr><th>页面</th><th>做什么</th></tr>
    </thead>
    <tbody>
        <tr><td>{!! $hl('members', '会员') !!}</td><td>前台注册用户。积分、卡密、分组、订单在同一栏。</td></tr>
        <tr><td>{!! $hl('settings', '站点设置') !!}</td><td>站名、关键词、Logo、评论、发信、会员规则、AI SEO。外观短句和播放器参数也在设置里分页。</td></tr>
        <tr><td>{!! $hl('templates', '模板') !!}</td><td>改 <code>resources/views/themes/</code> 里的 Blade。详见「<a href="{{ $urls['helpTemplates'] ?? '#' }}">模板</a>」。标签向导在{!! $hl('wizard', '标签向导') !!}。</td></tr>
        <tr><td>{!! $hl('players', '播放器') !!}</td><td>线路用哪种播放器、解析接口。密钥不要写进模板。</td></tr>
        <tr><td>{!! $hl('make', '静态生成') !!}</td><td>写出 <code>public/html</code>。可按分类、当天、插件内容分批。nginx 写法见「<a href="{{ $urls['env'] ?? '#' }}">环境</a>」。</td></tr>
        <tr><td>{!! $hl('rewrite', '伪静态') !!}</td><td>前台路径规则说明，给 nginx / Apache 抄。</td></tr>
        <tr><td>{!! $hl('push', '搜索推送') !!}</td><td>把新地址推给百度等。要填 token。</td></tr>
        <tr><td>{!! $hl('ai', 'AI SEO') !!}</td><td>用 DeepSeek 等兼容接口写简介和 title / keywords / description。密钥空着会失败，不会偷偷用别人的。</td></tr>
    </tbody>
</table>

<h3>系统</h3>
<table class="help-doc">
    <thead>
        <tr><th>页面</th><th>做什么</th></tr>
    </thead>
    <tbody>
        <tr><td>{!! $hl('admins', '管理员') !!}</td><td>后台账号。创始人（编号 1）能做所有事。</td></tr>
        <tr><td>{!! $hl('roles', '角色') !!}</td><td>勾能进哪些页。侧栏没有的项是当前账号没权限。</td></tr>
        <tr><td>{!! $hl('menus', '菜单') !!}</td><td>后台页和接口登记。一般不用改。</td></tr>
        <tr><td>{!! $hl('logs', '日志') !!}</td><td>谁登录过、改过什么。不能当影片修订用。</td></tr>
        <tr><td>{!! $hl('cache', '缓存') !!}</td><td>清模板、配置、页面缓存。</td></tr>
        <tr><td>{!! $hl('schedule', '定时任务') !!}</td><td>后台登记的计划任务。系统级采集/统计仍要 cron 叫醒 Laravel，见「定时」。</td></tr>
        <tr><td>{!! $hl('database', '数据库') !!}</td><td>备份、恢复、跑 SQL。改数据先备份。</td></tr>
        <tr><td>说明</td><td>本页。人人都能打开，不跟运营权限走，所以放在系统最底下。</td></tr>
    </tbody>
</table>
<p class="hint">模板指令见「<a href="{{ $urls['tags'] ?? '#' }}">标签</a>」。安装和 nginx 见「安装」分组。</p>
