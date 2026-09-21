<div class="card guide">
    <div class="pane">
        <h2>Install with Docker</h2>
        <p class="muted">Use this with Docker Desktop. One command builds the PHP image and mounts the project.</p>

        <h3>1. Start in the project directory</h3>
        <div class="code">
            <button type="button" class="copy" data-copy>Copy</button>
            <pre><code>docker compose up --build</code></pre>
        </div>
        <p class="hint">The first build takes a few minutes. You are ready when you see <code>Development Server</code> or port <code>8010</code>.</p>

        <h3>2. Install the database and admin</h3>
        <p class="muted">In another terminal, from the project directory:</p>
        <div class="code">
            <button type="button" class="copy" data-copy>Copy</button>
            <pre><code>docker compose exec php php artisan video:install --password=YourPass#1</code></pre>
        </div>
        <p class="hint">Or open <a href="http://127.0.0.1:8010/install">http://127.0.0.1:8010/install</a>. The SQLite file is <code>/var/www/html/database/database.sqlite</code> in the container.</p>

        <h3>3. Open the site</h3>
        <p class="muted">Front <a href="http://127.0.0.1:8010/">http://127.0.0.1:8010/</a><br>Admin <a href="http://127.0.0.1:8010/admin/login">http://127.0.0.1:8010/admin/login</a></p>
        <p class="hint">There are no bundled demo emails. Sign in with the account you created. Change the password before going live, and set <code>APP_DEBUG=false</code>.</p>

        <h3>Useful commands</h3>
        <div class="code">
            <button type="button" class="copy" data-copy>Copy</button>
            <pre><code>docker compose down
docker compose exec php php vendor/phpunit/phpunit/phpunit</code></pre>
        </div>
        <p class="hint">The container workdir is <code>/var/www/html</code>. The service name is <code>php</code>. The port is <strong>8010</strong>, not 8000.</p>
    </div>
</div>
