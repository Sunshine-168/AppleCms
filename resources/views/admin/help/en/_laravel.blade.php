@php
    $deployUrl = $deployUrl ?? route('admin.help', ['topic' => 'env']);
    $webInstallUrl = $webInstallUrl ?? url('/install');
@endphp
<div class="card guide">
    <div class="pane">
        <h2>Install with the shell</h2>
        <p class="muted">Use this when PHP 8.4+ is already on the machine. SQLite is the default. One command is enough.</p>

        <h3>Linux / BaoTa</h3>
        <div class="code">
            <button type="button" class="copy" data-copy>Copy</button>
            <pre><code>bash install.sh</code></pre>
        </div>
        <p class="hint">Custom password: <code>bash install.sh YourPass#1</code>. Admin user is <code>admin</code>.</p>

        <h3>Windows</h3>
        <div class="code">
            <button type="button" class="copy" data-copy>Copy</button>
            <pre><code>.\install.ps1 YourPass#1</code></pre>
        </div>

        <h3>Try it</h3>
        <div class="code">
            <button type="button" class="copy" data-copy>Copy</button>
            <pre><code>php artisan serve --host=127.0.0.1 --port=8010</code></pre>
        </div>
        <p class="muted">Front <a href="http://127.0.0.1:8010/">http://127.0.0.1:8010/</a>, admin <a href="http://127.0.0.1:8010/admin/login">http://127.0.0.1:8010/admin/login</a>.</p>

        <h3>Manual equivalent</h3>
        <div class="code">
            <button type="button" class="copy" data-copy>Copy</button>
            <pre><code>composer install
cp .env.example .env
php artisan key:generate
php artisan video:install --password=YourPass#1 --demo</code></pre>
        </div>
        <p class="hint">Or run <code>composer install</code> and finish in the browser at <a href="{{ $webInstallUrl }}">web install</a>. Production: <a href="{{ $deployUrl }}">Environment</a>.</p>
    </div>
</div>
