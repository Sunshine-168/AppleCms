<div class="card guide">
    <div class="pane">
        <h2>Install with Docker</h2>
        <p class="muted">One command starts the app. Finish the admin account in the browser.</p>

        <h3>1. Start</h3>
        <div class="code">
            <button type="button" class="copy" data-copy>Copy</button>
            <pre><code>docker compose up --build</code></pre>
        </div>
        <p class="hint">The first build takes a few minutes. You are ready when port <code>8010</code> is listening.</p>

        <h3>2. Open the installer</h3>
        <p class="muted">Open <a href="http://127.0.0.1:8010/install">http://127.0.0.1:8010/install</a>, pick the file database, create an admin.</p>
        <p class="hint">Skip the browser and seed a demo site:</p>
        <div class="code">
            <button type="button" class="copy" data-copy>Copy</button>
            <pre><code>AUTO_INSTALL=1 INSTALL_PASSWORD=YourPass#1 docker compose up --build</code></pre>
        </div>
        <p class="hint">User <code>admin</code>, password from <code>INSTALL_PASSWORD</code>. SQLite is <code>/var/www/html/database/database.sqlite</code>.</p>

        <h3>3. Open the site</h3>
        <p class="muted">Front <a href="http://127.0.0.1:8010/">http://127.0.0.1:8010/</a><br>Admin <a href="http://127.0.0.1:8010/admin/login">http://127.0.0.1:8010/admin/login</a></p>
        <p class="hint">Change the password before going live. Set <code>APP_DEBUG=false</code>. Port is <strong>8010</strong>.</p>

        <h3>Useful commands</h3>
        <div class="code">
            <button type="button" class="copy" data-copy>Copy</button>
            <pre><code>docker compose down
docker compose exec php php artisan schedule:run</code></pre>
        </div>
    </div>
</div>
