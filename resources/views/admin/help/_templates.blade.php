@php
    $urls = $urls ?? [];
    $hl = $hl ?? fn (string $key, string $label) => e($label);
@endphp
<p class="muted">这里是前台 Blade 怎么写。在后台改文件走{!! $hl('templates', '模板') !!}（自带主题请先备份）。取影片和分类用 <code>@@vod*</code>，见「<a href="{{ $urls['tags'] ?? '#' }}">标签</a>」。不要在主题里写数据库查询。</p>

<h3>目录长什么样</h3>
<div class="code">
    <button type="button" class="copy" data-copy>复制</button>
    <pre><code>resources/views/themes/default/
  layout.blade.php           公共头尾（必须有 @@vodSeo）
  index/index.blade.php      首页
  vod/type.blade.php         分类列表
  vod/show.blade.php         筛选
  vod/detail.blade.php       影片详情
  vod/play.blade.php         播放页
  vod/search.blade.php
  vod/arts.blade.php         资讯
  vod/topics.blade.php       专题
  vod/actors.blade.php       演员
  member/                    登录、注册、会员中心
  partials/paginate.blade.php</code></pre>
</div>
<p class="hint">缺某个文件时，对应前台页会报错或空白。改默认主题前建议复制一份目录再在设置里切换。</p>

<h3>页面骨架</h3>
<div class="code">
    <button type="button" class="copy" data-copy>复制</button>
@verbatim
<pre><code>@extends('themes.default.layout')

@section('content')
    @vodBreadcrumb
    <h1>{{ $type->name }}</h1>

    @vod(['page' => true, 'num' => 24])
        <a href="{{ $item->url }}">{{ $item->name }}</a>
    @endvod

    @vodPaginate
@endsection</code></pre>
@endverbatim
</div>
<p class="muted">公共头里放 <code>@@vodSeo</code>。分类导航用 <code>@@vodType(['type' =&gt; 'top'])</code>。</p>

<h3>标签向导</h3>
<p class="muted">{!! $hl('wizard', '标签向导') !!}可以勾参数生成 <code>@@vod</code> / <code>@@manga</code> 等指令，复制进模板。插件启用后，向导里才会出现漫画、图集、小说、直播。</p>

<h3>二次开发</h3>
<p class="muted">业务放在 <code>app/Services</code>，不要改核心控制器里的入库逻辑。插件放 <code>plugins/插件名/</code>，用 <code>plugin.json</code> 声明菜单和标签。前台主题不要直接 <code>DB::table</code>。</p>
