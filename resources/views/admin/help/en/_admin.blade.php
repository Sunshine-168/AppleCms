@php
    $urls = $urls ?? [];
    $hl = $hl ?? fn (string $key, string $label) => e($label);
@endphp
<p class="muted">Every admin screen, in sidebar order. Each page also has a one-line hint at the top. Start with <a href="{{ route('admin.help') }}">Getting started</a>. Skin changes are under <a href="{{ $urls['helpTemplates'] ?? '#' }}">Templates</a>.</p>

<h3>Workbench</h3>
<table class="help-doc">
    <thead>
        <tr><th>Page</th><th>What it does</th></tr>
    </thead>
    <tbody>
        <tr><td>{!! $hl('dashboard', 'Dashboard') !!}</td><td>Pending videos, today’s collect, member snapshot. Pin your own shortcuts.</td></tr>
        <tr><td>{!! $hl('stats', 'Stats') !!}</td><td>Front PV / UV / IP, popular pages, referrers, and spiders. Admin paths are not counted.</td></tr>
        <tr><td>{!! $hl('plugins', 'Plugins') !!}</td><td>Local plugin switches. No store. Upload a zip; menus appear after enable.</td></tr>
    </tbody>
</table>

<h3>Videos</h3>
<table class="help-doc">
    <thead>
        <tr><th>Page</th><th>What it does</th></tr>
    </thead>
    <tbody>
        <tr><td>{!! $hl('videos', 'Videos') !!}</td><td>The library: title, cover, synopsis, play lines, downloads. Must be approved to list. Batch edit category, player, flags.</td></tr>
        <tr><td>{!! $hl('types', 'Categories') !!}</td><td>Channel tree. Drives list pages and filters. SEO title, template, nav visibility.</td></tr>
        <tr><td>{!! $hl('topics', 'Topics') !!}</td><td>Group several titles (e.g. “New Year”). Front path <code>/topics</code>.</td></tr>
        <tr><td>{!! $hl('actors', 'Actors') !!}</td><td>Cast library. Names on a video try to match rows here.</td></tr>
        <tr><td>{!! $hl('comments', 'Comments') !!}</td><td>Comments under a video. Moderate or delete. Switch is in Settings.</td></tr>
        <tr><td>{!! $hl('arts', 'Articles') !!}</td><td>News column, not the video library. Own categories and tags.</td></tr>
    </tbody>
</table>

<h3>Collect</h3>
<table class="help-doc">
    <thead>
        <tr><th>Page</th><th>What it does</th></tr>
    </thead>
    <tbody>
        <tr><td>{!! $hl('collects', 'Collect sources') !!}</td><td>AppleCMS APIs. Bind categories, then try a fetch. Only sources you may use. Auto-run: <a href="{{ $urls['scheduleHelp'] ?? '#' }}">Schedule</a>.</td></tr>
    </tbody>
</table>

<h3>Members and site</h3>
<table class="help-doc">
    <thead>
        <tr><th>Page</th><th>What it does</th></tr>
    </thead>
    <tbody>
        <tr><td>{!! $hl('members', 'Members') !!}</td><td>Front users. Points, cards, groups, and orders share this workspace.</td></tr>
        <tr><td>{!! $hl('settings', 'Settings') !!}</td><td>Site name, keywords, logo, comments, mail, member rules, AI SEO. Theme phrases and player options are tabs here too.</td></tr>
        <tr><td>{!! $hl('templates', 'Templates') !!}</td><td>Edit Blade under <code>resources/views/themes/</code>. See <a href="{{ $urls['helpTemplates'] ?? '#' }}">Templates</a>. The tag wizard is {!! $hl('wizard', 'Tag wizard') !!}.</td></tr>
        <tr><td>{!! $hl('players', 'Players') !!}</td><td>Which player a line uses, and parser endpoints. Keep keys out of the theme.</td></tr>
        <tr><td>{!! $hl('make', 'Static HTML') !!}</td><td>Write <code>public/html</code>. Batch by category, today, or plugin content. nginx: <a href="{{ $urls['env'] ?? '#' }}">Environment</a>.</td></tr>
        <tr><td>{!! $hl('rewrite', 'Pretty URLs') !!}</td><td>Front path rules to copy into nginx / Apache.</td></tr>
        <tr><td>{!! $hl('push', 'Search ping') !!}</td><td>Ping new URLs to Baidu and similar. Needs a token.</td></tr>
        <tr><td>{!! $hl('ai', 'AI SEO') !!}</td><td>OpenAI-compatible APIs (DeepSeek first) write synopsis and title / keywords / description. An empty key fails honestly.</td></tr>
    </tbody>
</table>

<h3>System</h3>
<table class="help-doc">
    <thead>
        <tr><th>Page</th><th>What it does</th></tr>
    </thead>
    <tbody>
        <tr><td>{!! $hl('admins', 'Admins') !!}</td><td>Back-office accounts. User id 1 can do everything.</td></tr>
        <tr><td>{!! $hl('roles', 'Roles') !!}</td><td>Which pages a role may open. Missing sidebar items mean no permission.</td></tr>
        <tr><td>{!! $hl('menus', 'Menus') !!}</td><td>Admin pages and APIs. Leave this unless you extend the product.</td></tr>
        <tr><td>{!! $hl('logs', 'Logs') !!}</td><td>Who signed in and what they changed. Not a video revision history.</td></tr>
        <tr><td>{!! $hl('cache', 'Cache') !!}</td><td>Clear views, config, and page cache.</td></tr>
        <tr><td>{!! $hl('schedule', 'Scheduler') !!}</td><td>Site-defined cron rows. Collect and stats still need OS cron to wake Laravel; see Schedule.</td></tr>
        <tr><td>{!! $hl('database', 'Database') !!}</td><td>Backup, restore, run SQL. Back up before you change data.</td></tr>
        <tr><td>Help</td><td>This page. Everyone can open it, so it sits at the bottom of System.</td></tr>
    </tbody>
</table>
<p class="hint">Template directives: <a href="{{ $urls['tags'] ?? '#' }}">Tags</a>. Install and nginx are under Install.</p>
