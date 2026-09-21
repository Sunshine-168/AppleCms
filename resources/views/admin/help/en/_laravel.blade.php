@php
    $deployUrl = $deployUrl ?? route('admin.help', ['topic' => 'env']);
    $webInstallUrl = $webInstallUrl ?? url('/install');
@endphp
<div class="card guide">
    <div class="pane">
        <h2>Install from the Laravel CLI</h2>
        <p class="muted">Use this when PHP 8.4+ and Composer are already on the machine. SQLite is the default; no extra database is required.</p>

        <h3>1. Prepare the code</h3>
        <div class="code">
            <button type="button" class="copy" data-copy>Copy</button>
            <pre><code>composer install
cp .env.example .env
php artisan key:generate</code></pre>
        </div>
        <p class="hint">On Windows, the second line is <code>copy .env.example .env</code>.</p>

        <h3>2. Database (optional)</h3>
        <p class="muted">SQLite is already set (<code>database/database.sqlite</code>). For MySQL 5.7+ / 8.0, create an empty utf8mb4 database and edit <code>.env</code>:</p>
        <div class="code">
            <button type="button" class="copy" data-copy>Copy</button>
            <pre><code>DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=video
DB_USERNAME=video
DB_PASSWORD=use-a-strong-password</code></pre>
        </div>
        <p class="hint">Example: <code>CREATE DATABASE video CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;</code>. PHP needs <code>pdo_mysql</code>. Production nginx: <a href="{{ $deployUrl }}">Environment</a>.</p>

        <h3>3. Install</h3>
        <div class="code">
            <button type="button" class="copy" data-copy>Copy</button>
            <pre><code>php artisan video:install --password=YourPass#1</code></pre>
        </div>
        <p class="hint">Add <code>--demo</code> for sample categories and videos. You can also pass <code>--name=My site --admin=admin</code>.</p>

        <h3>4. Serve</h3>
        <div class="code">
            <button type="button" class="copy" data-copy>Copy</button>
            <pre><code>php artisan serve --host=127.0.0.1 --port=8010</code></pre>
        </div>
        <p class="muted">Open <a href="http://127.0.0.1:8010/">http://127.0.0.1:8010/</a>, admin <a href="http://127.0.0.1:8010/admin/login">http://127.0.0.1:8010/admin/login</a>, and sign in with the account you just created.</p>
        <p class="hint">You can skip <code>video:install</code> and finish in the browser at <a href="{{ $webInstallUrl }}">web install</a>.</p>
        <p class="hint">Do not edit core files for small customizations. Template tags are under Help → Tags.</p>
    </div>
</div>
