@php
    $urls = $urls ?? [];
    $hl = $hl ?? fn (string $key, string $label) => e($label);
@endphp
<h2>定时任务和队列</h2>
<p class="muted">采集、点击重置、统计清理不会自己跑，要靠系统每分钟叫醒 Laravel。PHP / nginx 见「<a href="{{ $urls['env'] ?? '#' }}">环境</a>」。采集源在{!! $hl('collects', '采集') !!}里配，间隔到了才会真正拉取。后台{!! $hl('schedule', '定时任务') !!}页可以再登记站点自己的命令。</p>

<h3>调度会做什么</h3>
<table class="help-doc">
    <thead>
        <tr><th>任务</th><th>何时</th><th>说明</th></tr>
    </thead>
    <tbody>
        <tr><td><code>video:collect-due</code></td><td>每分钟</td><td>各采集源按自己的间隔决定这次要不要抓</td></tr>
        <tr><td><code>video:publish-due</code></td><td>每分钟</td><td>到点发布定时入库的影片</td></tr>
        <tr><td><code>video:hits-reset</code></td><td>每天 00:05</td><td>重置日/周/月点击</td></tr>
        <tr><td><code>stats:prune</code></td><td>每天 03:20</td><td>删掉过期的访问记录</td></tr>
        <tr><td><code>monitor:tick</code></td><td>每分钟</td><td>运行监控采样</td></tr>
    </tbody>
</table>
<p class="hint">本机 <code>php artisan serve</code> 或 Docker 试用可以不配。正式站至少要有下面那一行，路径已按当前服务器填好。</p>

<h3>推荐：上线最小配置</h3>
<div class="code">
    <button type="button" class="copy" data-copy>复制</button>
<pre><code>{{ \App\Support\PhpCli::scheduleCronLine() }}</code></pre>
</div>
<p class="hint">Linux / 宝塔把这一行加进计划任务，每分钟执行。Windows 用任务计划程序跑同一条命令（不要前面的 <code>* * * * *</code>）。若日志出现 <code>pcntl_signal</code>，是 PHP CLI 把该函数禁了；本程序已兼容，把站点文件更新后再跑 <code>schedule:run</code> 即可。</p>

<h3>手动命令</h3>
<div class="code">
    <button type="button" class="copy" data-copy>复制</button>
<pre><code>php artisan video:collect-due
php artisan video:collect --all
php artisan video:hits-reset
php artisan video:html-make
php artisan stats:prune</code></pre>
</div>
<p class="muted">单源采集：<code>php artisan video:collect {id}</code>。静态页生成也可在{!! $hl('make', '静态生成') !!}里点。</p>
