@php
    $urls = $urls ?? [];
    $hl = $hl ?? fn (string $key, string $label) => e($label);
@endphp
<p class="muted">How front Blade files work. Edit them in {!! $hl('templates', 'Templates') !!} (back up the shipped theme first). Load videos with <code>@@vod*</code>; see <a href="{{ $urls['tags'] ?? '#' }}">Tags</a>. Do not query the database from a theme.</p>

<h3>What the folder looks like</h3>
<div class="code">
    <button type="button" class="copy" data-copy>Copy</button>
    <pre><code>resources/views/themes/default/
  layout.blade.php           Shared chrome (must include @@vodSeo)
  index/index.blade.php      Home
  vod/type.blade.php         Category list
  vod/show.blade.php         Filters
  vod/detail.blade.php       Detail
  vod/play.blade.php         Player
  vod/search.blade.php
  vod/arts.blade.php         Articles
  vod/topics.blade.php       Topics
  vod/actors.blade.php       Actors
  member/                    Sign-in, register, member center
  partials/paginate.blade.php</code></pre>
</div>
<p class="hint">A missing file blanks that front page. Copy the folder before you heavily edit the default theme, then switch in Settings.</p>

<h3>Page skeleton</h3>
<div class="code">
    <button type="button" class="copy" data-copy>Copy</button>
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
<p class="muted">Put <code>@@vodSeo</code> in the layout head. Category nav: <code>@@vodType(['type' =&gt; 'top'])</code>.</p>

<h3>Tag wizard</h3>
<p class="muted">{!! $hl('wizard', 'Tag wizard') !!} builds <code>@@vod</code> / <code>@@manga</code> snippets you can paste. Manga, gallery, novel, and live appear there only after those plugins are on.</p>

<h3>Extending</h3>
<p class="muted">Put business in <code>app/Services</code>. Plugins live in <code>plugins/{id}/</code> with a <code>plugin.json</code>. Themes should not call <code>DB::table</code>.</p>
