@php
    $scheduleUrl = $scheduleUrl ?? route('admin.help', ['topic' => 'schedule']);
@endphp
<div class="card guide">
    <div class="pane">
        <h2>上线要准备什么</h2>
        <p class="muted">本机 <code>php artisan serve</code> 只适合试用。正式站点需要 PHP、数据库，以及 nginx（或同类）把请求指到 <code>public</code> 目录。</p>

        <h3>PHP 环境</h3>
        <p class="muted">需要 PHP <strong>8.4 或更高</strong>，并打开这些扩展：<code>pdo</code>、<code>mbstring</code>、<code>openssl</code>、<code>tokenizer</code>、<code>xml</code>、<code>ctype</code>、<code>json</code>、<code>fileinfo</code>、<code>curl</code>。用 MySQL 再开 <code>pdo_mysql</code>；用 SQLite 再开 <code>pdo_sqlite</code>。</p>
        <p class="muted">图片处理需要 <code>gd</code> 或 <code>imagick</code>，没有也能装，只是水印和缩略图不可用。</p>
        <p class="muted">建议在 <code>php.ini</code> 里：</p>
        <div class="code">
            <button type="button" class="copy" data-copy>复制</button>
            <pre><code>memory_limit = 256M
upload_max_filesize = 32M
post_max_size = 32M
max_execution_time = 60
date.timezone = Asia/Shanghai</code></pre>
        </div>
        <p class="hint">改完后重启 php-fpm 或网站服务。目录 <code>storage</code> 和 <code>bootstrap/cache</code> 必须可写，并执行过 <code>php artisan storage:link</code>。</p>

        <h3>MySQL</h3>
        <p class="muted">正式站点建议 MySQL 5.7 / 8.0 或 MariaDB 10.3+，字符集用 <code>utf8mb4</code>。先建空库，再把账号写进 <code>.env</code>。</p>
        <div class="code">
            <button type="button" class="copy" data-copy>复制</button>
            <pre><code>CREATE DATABASE video CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'video'@'127.0.0.1' IDENTIFIED BY '请换成强密码';
GRANT ALL ON video.* TO 'video'@'127.0.0.1';
FLUSH PRIVILEGES;</code></pre>
        </div>
        <div class="code">
            <button type="button" class="copy" data-copy>复制</button>
            <pre><code>DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=video
DB_USERNAME=video
DB_PASSWORD=请换成强密码</code></pre>
        </div>
        <p class="hint">试用可以继续用 SQLite（<code>DB_CONNECTION=sqlite</code>，文件 <code>database/database.sqlite</code>），不用另装数据库。改完 <code>.env</code> 后执行 <code>php artisan config:clear</code>。</p>

        <h3>nginx</h3>
        <p class="muted">网站根目录必须是项目里的 <code>public</code>，不要指到项目根。下面把 <code>/var/www/laravideo</code> 换成你的路径，<code>php8.4-fpm.sock</code> 换成本机 PHP-FPM 套接字。</p>
        <div class="code">
            <button type="button" class="copy" data-copy>复制</button>
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
        <p class="hint">第一行 <code>try_files</code> 会优先读后台生成的磁盘静态页（<code>public/html</code>）。没有文件时再交给 PHP。HTTPS 请在前面加证书或由面板处理。</p>
        <p class="muted">还要在 <code>.env</code> 写上 <code>APP_URL=https://www.example.com</code>，<code>APP_ENV=production</code>，<code>APP_DEBUG=false</code>。若站点在反向代理后面，再设 <code>TRUST_PROXIES=*</code>。</p>

        <h3>搜索引擎 robots.txt</h3>
        <p class="muted">前台地址是 <code>/robots.txt</code>。默认允许抓首页，禁止 <code>/admin</code>、<code>/install</code>、<code>/member</code>，并带上 sitemap。关站时整站是 <code>Disallow: /</code>。</p>
        <p class="muted">站点地图在 <code>/sitemap.xml</code>。还要挡住某几段路径时，到站点设置写补充规则。</p>
        <p class="muted">定时任务见「<a href="{{ $scheduleUrl }}">定时</a>」。</p>
    </div>
</div>
