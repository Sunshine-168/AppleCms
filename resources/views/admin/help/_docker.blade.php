<div class="card guide">
    <div class="pane">
        <h2>用 Docker 安装</h2>
        <p class="muted">适合本机已装 Docker Desktop。一条命令会编译 PHP 镜像并挂上项目目录。</p>

        <h3>1. 进入项目目录后启动</h3>
        <div class="code">
            <button type="button" class="copy" data-copy>复制</button>
            <pre><code>docker compose up --build</code></pre>
        </div>
        <p class="hint">第一次会编译镜像，需要几分钟。看到 <code>Development Server</code> 或监听 <code>8010</code> 就好了。</p>

        <h3>2. 安装数据库和管理员</h3>
        <p class="muted">另开终端，在项目目录执行其一：</p>
        <div class="code">
            <button type="button" class="copy" data-copy>复制</button>
            <pre><code>docker compose exec php php artisan video:install --password=YourPass#1</code></pre>
        </div>
        <p class="hint">也可以打开 <a href="http://127.0.0.1:8010/install">http://127.0.0.1:8010/install</a> 用网页装。SQLite 文件是容器内 <code>/var/www/html/database/database.sqlite</code>。</p>

        <h3>3. 打开网站</h3>
        <p class="muted">前台 <a href="http://127.0.0.1:8010/">http://127.0.0.1:8010/</a><br>后台 <a href="http://127.0.0.1:8010/admin/login">http://127.0.0.1:8010/admin/login</a></p>
        <p class="hint">没有内置演示邮箱。管理员就是安装时填写的账号。上线前改掉密码，并把 <code>APP_DEBUG</code> 设为 <code>false</code>。</p>

        <h3>常用命令</h3>
        <div class="code">
            <button type="button" class="copy" data-copy>复制</button>
            <pre><code>docker compose down
docker compose exec php php vendor/phpunit/phpunit/phpunit</code></pre>
        </div>
        <p class="hint">容器工作目录是 <code>/var/www/html</code>，项目文件会挂进去。服务名是 <code>php</code>，端口是 <strong>8010</strong>，不要用 8000。</p>
    </div>
</div>
