@php
    $urls = $urls ?? [];
    $hl = $hl ?? fn (string $key, string $label) => e($label);
@endphp
<h2>Schedule and queue</h2>
<p class="muted">Collect, hit resets, and stats prune do not run by themselves. The OS must wake Laravel every minute. PHP / nginx: <a href="{{ $urls['env'] ?? '#' }}">Environment</a>. Feeds live in {!! $hl('collects', 'Collect') !!}. Extra site commands can be registered on the {!! $hl('schedule', 'Scheduler') !!} page.</p>

<h3>What the scheduler does</h3>
<table class="help-doc">
    <thead>
        <tr><th>Command</th><th>When</th><th>Notes</th></tr>
    </thead>
    <tbody>
        <tr><td><code>video:collect-due</code></td><td>Every minute</td><td>Each feed uses its own interval</td></tr>
        <tr><td><code>video:publish-due</code></td><td>Every minute</td><td>Publish titles whose time has arrived</td></tr>
        <tr><td><code>video:hits-reset</code></td><td>Daily 00:05</td><td>Reset day / week / month hits</td></tr>
        <tr><td><code>stats:prune</code></td><td>Daily 03:20</td><td>Drop old visit rows</td></tr>
        <tr><td><code>monitor:tick</code></td><td>Every minute</td><td>Runtime samples</td></tr>
    </tbody>
</table>
<p class="hint"><code>php artisan serve</code> and Docker trials can skip this. Production needs the cron line below, or you only collect from the admin button.</p>

<h3>Minimum production cron</h3>
<div class="code">
    <button type="button" class="copy" data-copy>Copy</button>
<pre><code>* * * * * cd /var/www/laravideo && php artisan schedule:run >> /dev/null 2>&amp;1</code></pre>
</div>
<p class="hint">Change the path. Linux: crontab. Windows: Task Scheduler, same <code>php artisan schedule:run</code> every minute.</p>

<h3>Manual commands</h3>
<div class="code">
    <button type="button" class="copy" data-copy>Copy</button>
<pre><code>php artisan video:collect-due
php artisan video:collect --all
php artisan video:hits-reset
php artisan video:html-make
php artisan stats:prune</code></pre>
</div>
<p class="muted">One feed: <code>php artisan video:collect {id}</code>. Disk HTML can also be started from {!! $hl('make', 'Static HTML') !!}.</p>
