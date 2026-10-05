@php
    $deployUrl = $deployUrl ?? route('admin.help', ['topic' => 'env']);
    $webInstallUrl = $webInstallUrl ?? url('/install');
@endphp
<div class="card guide">
    <div class="pane">
        <h2>用 Shell 安装</h2>
        <p class="muted">适合本机或宝塔已有 PHP 8.4+。默认 SQLite，不用另装数据库。一条命令就能装完。</p>

        <h3>Linux / 宝塔</h3>
        <div class="code">
            <button type="button" class="copy" data-copy>复制</button>
            <pre><code>bash install.sh</code></pre>
        </div>
        <p class="hint">自定义密码：<code>bash install.sh YourPass#1</code>。会装依赖、建库、导入示例分类。后台账号 <code>admin</code>。</p>

        <h3>Windows</h3>
        <div class="code">
            <button type="button" class="copy" data-copy>复制</button>
            <pre><code>.\install.ps1 YourPass#1</code></pre>
        </div>

        <h3>装好后试用</h3>
        <div class="code">
            <button type="button" class="copy" data-copy>复制</button>
            <pre><code>php artisan serve --host=127.0.0.1 --port=8010</code></pre>
        </div>
        <p class="muted">前台 <a href="http://127.0.0.1:8010/">http://127.0.0.1:8010/</a>，后台 <a href="http://127.0.0.1:8010/admin/login">http://127.0.0.1:8010/admin/login</a>。</p>

        <h3>等价的手动命令</h3>
        <div class="code">
            <button type="button" class="copy" data-copy>复制</button>
            <pre><code>composer install
cp .env.example .env
php artisan key:generate
php artisan video:install --password=YourPass#1 --demo</code></pre>
        </div>
        <p class="hint">也可以不跑脚本，执行 <code>composer install</code> 后打开 <a href="{{ $webInstallUrl }}">网页安装</a>。上线 nginx / 宝塔见 <a href="{{ $deployUrl }}">环境</a>。</p>
    </div>
</div>
