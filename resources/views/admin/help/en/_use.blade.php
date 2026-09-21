@php
    $urls = $urls ?? [];
    $hl = $hl ?? fn (string $key, string $label) => e($label);
@endphp
<p class="muted">The <strong>theme</strong> decides how the site looks. <strong>Categories and videos</strong> fill the library. See <a href="{{ $urls['helpAdmin'] ?? '#' }}">Admin</a> for each sidebar item, <a href="{{ $urls['helpTemplates'] ?? '#' }}">Templates</a> for Blade files, and <a href="{{ $urls['tags'] ?? '#' }}">Tags</a> for data tags.</p>

<div class="help-jumps">
    <a href="{{ $urls['settings'] ?? '/admin/video/settings' }}">Site name</a>
    <a href="{{ $urls['types'] ?? '/admin/video/types' }}">Add a category</a>
    <a href="{{ $urls['videos'] ?? '/admin/video' }}">Add a video</a>
    <a href="{{ $urls['collects'] ?? '/admin/video/collects' }}">Collect</a>
    <a href="{{ $urls['cache'] ?? '/admin/system/tools/cache' }}">Clear cache</a>
    <a href="{{ $urls['front'] ?? url('/') }}" target="_blank" rel="noopener">View site</a>
</div>

<h3>Suggested first steps</h3>
<ol class="help-steps">
    <li>In {!! $hl('settings', 'Settings') !!}, set the site name, keywords, and a short intro.</li>
    <li>In {!! $hl('types', 'Categories') !!}, add video categories (movies, series, shows). Categories drive lists and filters.</li>
    <li>In {!! $hl('videos', 'Videos') !!}, add titles by hand, or ingest a feed in {!! $hl('collects', 'Collect') !!}.</li>
    <li>In {!! $hl('players', 'Players') !!}, confirm playback URLs work. Lines and parsers live here.</li>
    <li>Open the front. If it still looks old, clear {!! $hl('cache', 'Cache') !!}.</li>
    <li>For heavy traffic, turn on {!! $hl('make', 'static HTML') !!} under <code>public/html</code>. Manga, galleries, novels, and live TV are {!! $hl('plugins', 'plugins') !!}.</li>
</ol>
<p class="hint">If you installed demo data, categories and videos are already there — just edit the copy. On an empty site, start with categories. Missing sidebar items mean this account has no permission. Help stays at the bottom of System so every account can open it.</p>

<h3>What the sidebar is for</h3>
<table class="help-doc">
    <thead>
        <tr><th>Area</th><th>What you do</th></tr>
    </thead>
    <tbody>
        <tr><td>Workbench</td><td>{!! $hl('dashboard', 'Dashboard') !!}, {!! $hl('stats', 'stats') !!}, shortcuts. {!! $hl('plugins', 'Plugins') !!} and search live here too.</td></tr>
        <tr><td>Videos</td><td>{!! $hl('videos', 'Library') !!}, {!! $hl('types', 'categories') !!}, {!! $hl('topics', 'topics') !!}, {!! $hl('actors', 'actors') !!}, comments. A title needs a category to show on the front.</td></tr>
        <tr><td>Collect</td><td>{!! $hl('collects', 'Feeds') !!}. Connect an AppleCMS API, try a fetch, then schedule. Cron is under <a href="{{ $urls['scheduleHelp'] ?? '#' }}">Schedule</a>.</td></tr>
        <tr><td>Members</td><td>{!! $hl('members', 'Members') !!}, groups, cards, orders. Front sign-in is <code>/member</code>.</td></tr>
        <tr><td>Site</td><td>{!! $hl('settings', 'Settings') !!}, {!! $hl('templates', 'templates') !!}, {!! $hl('players', 'players') !!}, {!! $hl('make', 'static HTML') !!}, {!! $hl('rewrite', 'pretty URLs') !!}.</td></tr>
        <tr><td>System</td><td>Admins, roles, logs, backups, cache, scheduler, and this help.</td></tr>
    </tbody>
</table>

<h3>Categories and videos</h3>
<p class="muted">Categories are channels. A video has one primary category (that sets the URL) and can also appear in extra categories. Status must be approved and the publish time must have arrived.</p>
<p class="muted">Playback URLs are grouped by line. Each line uses a player (iframe, DPlayer, …). Parsers are in {!! $hl('players', 'Players') !!}; do not put secrets in the theme.</p>
<p class="muted">Articles, topics, and actors sit beside the library. Manga / gallery / novel / live are plugins; turning one on adds a top-bar workspace.</p>

<h3>Collect, static pages, plugins</h3>
<p class="muted">{!! $hl('collects', 'Collect') !!} only from sources you have the right to use. Bind categories, then try a fetch. Auto-run needs <code>schedule:run</code> every minute; see Schedule.</p>
<p class="muted">{!! $hl('make', 'Static HTML') !!} writes pages under <code>public/html</code>. nginx must prefer those files; see <a href="{{ $urls['env'] ?? '#' }}">Environment</a>. Plugin category pages need path URLs such as <code>/manga/type/1</code> — extra query strings cannot be staticized.</p>
<p class="muted">{!! $hl('plugins', 'Plugins') !!} have no store. Upload a zip yourself. You can fill keys while a plugin is off; menus appear only after you enable it. AI SEO is {!! $hl('ai', 'AI content') !!}.</p>

<h3>FAQ</h3>
<div class="help-faq">
    <details>
        <summary>I changed the theme and the front still looks old</summary>
        <p>Cached pages. Clear {!! $hl('cache', 'Cache') !!}, or delete <code>storage/framework/views</code> and refresh.</p>
    </details>
    <details>
        <summary>I added a video but it is not on the front</summary>
        <p>It must be <strong>approved</strong>, the publish time must have passed, and it must sit in the right category. Drafts and pending items stay off lists.</p>
    </details>
    <details>
        <summary>Collect does not run by itself</summary>
        <p><code>php artisan serve</code> does not fire the scheduler. Production needs <code>php artisan schedule:run</code> every minute; see <a href="{{ $urls['scheduleHelp'] ?? '#' }}">Schedule</a>. You can also click “collect now” in admin.</p>
    </details>
    <details>
        <summary>The play page has no picture</summary>
        <p>Check the play URL, player type, and parser. A title with empty lines can open the detail page but not play. If an ad slot covers the player, turn that slot off and retry.</p>
    </details>
    <details>
        <summary>I do not want search engines on some pages</summary>
        <p>Front <code>/robots.txt</code> already blocks <code>/admin</code>, <code>/install</code>, and <code>/member</code>. Extra paths go in {!! $hl('settings', 'Settings') !!}. The sitemap is <code>/sitemap.xml</code>; you can also build it under {!! $hl('make', 'static HTML') !!}.</p>
    </details>
    <details>
        <summary>No manga, gallery, novel, or live menu</summary>
        <p>Enable the plugin under {!! $hl('plugins', 'Plugins') !!}. Turning it off hides the workspace; the data stays.</p>
    </details>
</div>
