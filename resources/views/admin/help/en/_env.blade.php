@php
    $scheduleUrl = $scheduleUrl ?? route('admin.help', ['topic' => 'schedule']);
@endphp
<div class="card guide">
    <div class="pane">
        <h2>What you need to go live</h2>
        <p class="muted"><code>php artisan serve</code> is for trying the site. Production needs PHP, a database, and nginx (or similar) pointing at the <code>public</code> directory.</p>

        <h3>PHP</h3>
        <p class="muted">PHP <strong>8.4 or newer</strong>, with <code>pdo</code>, <code>mbstring</code>, <code>openssl</code>, <code>tokenizer</code>, <code>xml</code>, <code>ctype</code>, <code>json</code>, <code>fileinfo</code>, <code>curl</code>. Add <code>pdo_mysql</code> for MySQL or <code>pdo_sqlite</code> for SQLite.</p>
        <p class="muted">Watermarks and thumbs need <code>gd</code> or <code>imagick</code>. The site still installs without them.</p>
        <p class="muted">Suggested <code>php.ini</code>:</p>
        <div class="code">
            <button type="button" class="copy" data-copy>Copy</button>
            <pre><code>memory_limit = 256M
upload_max_filesize = 32M
post_max_size = 32M
max_execution_time = 60
date.timezone = Asia/Shanghai</code></pre>
        </div>
        <p class="hint">Restart php-fpm afterwards. <code>storage</code> and <code>bootstrap/cache</code> must be writable. Run <code>php artisan storage:link</code>.</p>

        <h3>MySQL</h3>
        <p class="muted">Prefer MySQL 5.7 / 8.0 or MariaDB 10.3+, charset <code>utf8mb4</code>. Create an empty database, then put the account in <code>.env</code>.</p>
        <div class="code">
            <button type="button" class="copy" data-copy>Copy</button>
            <pre><code>CREATE DATABASE video CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'video'@'127.0.0.1' IDENTIFIED BY 'use-a-strong-password';
GRANT ALL ON video.* TO 'video'@'127.0.0.1';
FLUSH PRIVILEGES;</code></pre>
        </div>
        <div class="code">
            <button type="button" class="copy" data-copy>Copy</button>
            <pre><code>DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=video
DB_USERNAME=video
DB_PASSWORD=use-a-strong-password</code></pre>
        </div>
        <p class="hint">Trials can stay on SQLite (<code>DB_CONNECTION=sqlite</code>, file <code>database/database.sqlite</code>). After editing <code>.env</code>, run <code>php artisan config:clear</code>.</p>

        <h3>nginx</h3>
        <p class="muted">The document root must be <code>public</code>, not the project root. Replace <code>/var/www/laravideo</code> and the php-fpm socket.</p>
        <div class="code">
            <button type="button" class="copy" data-copy>Copy</button>
            <pre><code>server {
    listen 80;
    server_name www.example.com;
    root /var/www/laravideo/public;
    index index.php;
    charset utf-8;
    client_max_body_size 32m;

    location / {
        try_files /html$uri/index.html /html$uri $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}</code></pre>
        </div>
        <p class="hint">The first <code>try_files</code> line prefers disk HTML under <code>public/html</code>. Missing files fall through to PHP.</p>
        <p class="muted">Set <code>APP_URL</code>, <code>APP_ENV=production</code>, <code>APP_DEBUG=false</code>. Behind a reverse proxy, set <code>TRUST_PROXIES=*</code>.</p>

        <h3>Search engines and robots.txt</h3>
        <p class="muted">Front path <code>/robots.txt</code> allows the home page, blocks <code>/admin</code>, <code>/install</code>, <code>/member</code>, and lists the sitemap. Closed sites send <code>Disallow: /</code>.</p>
        <p class="muted">Sitemap: <code>/sitemap.xml</code>. Extra blocked paths go in Settings. Cron: <a href="{{ $scheduleUrl }}">Schedule</a>.</p>
    </div>
</div>
