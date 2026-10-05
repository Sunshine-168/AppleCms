<div class="card guide">
    <div class="pane">
        <h2>用 Docker 安装</h2>
        <p class="muted">本机已装 Docker。一条命令启动，浏览器里点完管理员即可。</p>

        <h3>1. 启动</h3>
        <div class="code">
            <button type="button" class="copy" data-copy>复制</button>
            <pre><code>docker compose up --build</code></pre>
        </div>
        <p class="hint">第一次编译镜像需要几分钟。看到监听 <code>8010</code> 就好了。</p>

        <h3>2. 打开安装页</h3>
        <p class="muted">浏览器打开 <a href="http://127.0.0.1:8010/install">http://127.0.0.1:8010/install</a>，选文件数据库，创建管理员。</p>
        <p class="hint">不想用网页、直接装好并导入示例：</p>
        <div class="code">
            <button type="button" class="copy" data-copy>复制</button>
            <pre><code>AUTO_INSTALL=1 INSTALL_PASSWORD=YourPass#1 docker compose up --build</code></pre>
        </div>
        <p class="hint">账号 <code>admin</code>，密码是 <code>INSTALL_PASSWORD</code>。SQLite 在容器内 <code>/var/www/html/database/database.sqlite</code>。</p>

        <h3>3. 打开网站</h3>
        <p class="muted">前台 <a href="http://127.0.0.1:8010/">http://127.0.0.1:8010/</a><br>后台 <a href="http://127.0.0.1:8010/admin/login">http://127.0.0.1:8010/admin/login</a></p>
        <p class="hint">上线前改掉密码，并把 <code>APP_DEBUG</code> 设为 <code>false</code>。端口是 <strong>8010</strong>。</p>

        <h3>常用命令</h3>
        <div class="code">
            <button type="button" class="copy" data-copy>复制</button>
            <pre><code>docker compose down
docker compose exec php php artisan schedule:run</code></pre>
        </div>
    </div>
</div>
