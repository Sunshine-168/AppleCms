@php
    $urls = $urls ?? [];
    $hl = $hl ?? fn (string $key, string $label) => e($label);
@endphp
<p class="muted">The front uses <code>@@vod*</code> only. Loop tags need a matching end directive. The loop variable defaults to <code>$item</code>; nested loops must set <code>'as' =&gt; 'alias'</code>. File layout: <a href="{{ $urls['helpTemplates'] ?? '#' }}">Templates</a>. {!! $hl('wizard', 'Tag wizard') !!} can tick options and copy the snippet.</p>

<h3>Common examples</h3>
<div class="code">
    <button type="button" class="copy" data-copy>Copy</button>
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

<h3>Category list</h3>
<div class="code">
    <button type="button" class="copy" data-copy>Copy</button>
@verbatim
<pre><code>@vod(['page' => true, 'num' => 24])
    <article>
        <h3><a href="{{ $item->url }}">{{ $item->name }}</a></h3>
    </article>
@endvod
@vodPaginate</code></pre>
@endverbatim
</div>

<h3>Detail</h3>
<div class="code">
    <button type="button" class="copy" data-copy>Copy</button>
@verbatim
<pre><code>@vodBreadcrumb
<h1>{{ $video->name }}</h1>
<p>{!! $video->content !!}</p>
<p>Prev: @vodPrev  Next: @vodNext</p></code></pre>
@endverbatim
</div>

<h3>Nested loops (must rename)</h3>
<div class="code">
    <button type="button" class="copy" data-copy>Copy</button>
@verbatim
<pre><code>@vodType(['type' => 'top', 'as' => 'cat'])
    <h2>{{ $cat->name }}</h2>
    @vod(['typeid' => $cat->id, 'num' => 8, 'as' => 'row'])
        <a href="{{ $row->url }}">{{ $row->name }}</a>
    @endvod
@endvodType</code></pre>
@endverbatim
</div>

<h3>Directive list</h3>
<table class="help-doc">
    <thead>
        <tr><th>Directive</th><th>Role</th><th>Common args</th></tr>
    </thead>
    <tbody>
        <tr><td><code>@@vodSeo</code></td><td>title / keywords / description</td><td>No args; put in <code>&lt;head&gt;</code></td></tr>
        <tr><td><code>@@vodType</code></td><td>Category loop</td><td><code>type=top|son</code>, <code>num</code></td></tr>
        <tr><td><code>@@vod</code></td><td>Video list</td><td><code>typeid</code>, <code>flag</code>, <code>num</code>, <code>page</code>, <code>order</code></td></tr>
        <tr><td><code>@@vodFilter</code></td><td>Filters</td><td>Year, area, and similar query params</td></tr>
        <tr><td><code>@@vodPaginate</code></td><td>Pager (with <code>page=&gt;true</code>)</td><td>Place under the list</td></tr>
        <tr><td><code>@@vodArt</code></td><td>Articles</td><td><code>num</code>, <code>page</code></td></tr>
        <tr><td><code>@@vodTopic</code></td><td>Topics</td><td><code>num</code></td></tr>
        <tr><td><code>@@vodActor</code></td><td>Actors</td><td><code>num</code></td></tr>
        <tr><td><code>@@vodComment</code></td><td>Comments</td><td>Current video page</td></tr>
        <tr><td><code>@@vodSlide</code></td><td>Slides</td><td><code>slot</code>, <code>num</code></td></tr>
        <tr><td><code>@@manga</code> / <code>@@gallery</code> / <code>@@novel</code> / <code>@@live</code></td><td>Plugin loops</td><td>Only after that plugin is on</td></tr>
    </tbody>
</table>
<p class="hint">After a paged list, add <code>@@vodPaginate</code>. Plugin tags and type paths are in the wizard; disk HTML cannot carry extra query strings.</p>
