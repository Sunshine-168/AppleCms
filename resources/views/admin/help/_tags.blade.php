@php
    $urls = $urls ?? [];
    $hl = $hl ?? fn (string $key, string $label) => e($label);
@endphp
<p class="muted">前台只用 <code>@@vod*</code>。循环类标签要成对写结束指令。循环变量默认 <code>$item</code>，嵌套时必须用 <code>'as' =&gt; '别名'</code>。改文件放哪见「<a href="{{ $urls['helpTemplates'] ?? '#' }}">模板</a>」。后台{!! $hl('wizard', '标签向导') !!}能勾参数生成代码。</p>

<h3>常用例子</h3>
<div class="code">
    <button type="button" class="copy" data-copy>复制</button>
@verbatim
<pre><code>@vodType(['type' => 'top'])
    <a href="{{ $item->url }}">{{ $item->name }}</a>
@endvodType

@vod(['flag' => 'recommend', 'num' => 12])
    <a href="{{ $item->url }}">{{ $item->name }}</a>
@endvod

@vodArt(['num' => 6])
    <a href="{{ $item->url }}">{{ $item->name }}</a>
@endvodArt</code></pre>
@endverbatim
</div>

<h3>分类列表页</h3>
<div class="code">
    <button type="button" class="copy" data-copy>复制</button>
@verbatim
<pre><code>@vod(['page' => true, 'num' => 24])
    <article>
        <h3><a href="{{ $item->url }}">{{ $item->name }}</a></h3>
    </article>
@endvod
@vodPaginate</code></pre>
@endverbatim
</div>

<h3>详情页</h3>
<div class="code">
    <button type="button" class="copy" data-copy>复制</button>
@verbatim
<pre><code>@vodBreadcrumb
<h1>{{ $video->name }}</h1>
<p>{!! $video->content !!}</p>
<p>上一部：@vodPrev 下一部：@vodNext</p></code></pre>
@endverbatim
</div>

<h3>嵌套循环（必须改名）</h3>
<div class="code">
    <button type="button" class="copy" data-copy>复制</button>
@verbatim
<pre><code>@vodType(['type' => 'top', 'as' => 'cat'])
    <h2>{{ $cat->name }}</h2>
    @vod(['typeid' => $cat->id, 'num' => 8, 'as' => 'row'])
        <a href="{{ $row->url }}">{{ $row->name }}</a>
    @endvod
@endvodType</code></pre>
@endverbatim
</div>

<h3>指令一览</h3>
<table class="help-doc">
    <thead>
        <tr><th>指令</th><th>作用</th><th>常用参数</th></tr>
    </thead>
    <tbody>
        <tr><td><code>@@vodSeo</code></td><td>输出 title / keywords / description</td><td>不用参数，放在 <code>&lt;head&gt;</code></td></tr>
        <tr><td><code>@@vodType</code></td><td>分类循环</td><td><code>type=top|son</code>，<code>num</code></td></tr>
        <tr><td><code>@@vod</code></td><td>影片列表</td><td><code>typeid</code>，<code>flag</code>，<code>num</code>，<code>page</code>，<code>order</code></td></tr>
        <tr><td><code>@@vodFilter</code></td><td>筛选</td><td>配合地址栏年份、地区等</td></tr>
        <tr><td><code>@@vodPaginate</code></td><td>分页（配合 <code>page=&gt;true</code>）</td><td>贴在列表下面</td></tr>
        <tr><td><code>@@vodArt</code></td><td>资讯列表</td><td><code>num</code>，<code>page</code></td></tr>
        <tr><td><code>@@vodTopic</code></td><td>专题</td><td><code>num</code></td></tr>
        <tr><td><code>@@vodActor</code></td><td>演员</td><td><code>num</code></td></tr>
        <tr><td><code>@@vodComment</code></td><td>评论</td><td>当前影片页</td></tr>
        <tr><td><code>@@vodSlide</code></td><td>幻灯</td><td><code>slot</code>，<code>num</code></td></tr>
        <tr><td><code>@@manga</code> / <code>@@gallery</code> / <code>@@novel</code> / <code>@@live</code></td><td>插件内容循环</td><td>对应插件启用后才有</td></tr>
    </tbody>
</table>
<p class="hint">分页后下面要加 <code>@@vodPaginate</code>。插件标签和分类页路径见标签向导；磁盘静态 HTML 不能带额外查询参数。</p>
