<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>安装 苹果v12</title>
    <link rel="stylesheet" href="{{ asset('css/install.css') }}?v={{ @filemtime(public_path('css/install.css')) ?: '1' }}">
</head>
<body>
@php
    $failN = (int) ($failN ?? 0);
    $warnN = (int) ($warnN ?? 0);
    $passN = (int) ($passN ?? 0);
    $requiredOk = (bool) ($requiredOk ?? false);
    $sqlitePath = (string) ($sqlitePath ?? 'database/database.sqlite');
    $checks = is_array($checks ?? null) ? $checks : [];
@endphp
<div class="wrap install-index">
    <div class="brand"><span class="logo">苹</span> 苹果v12 安装</div>
    <nav class="ways" aria-label="安装方式">
        <a class="{{ ($way ?? 'web') === 'web' ? 'is-on' : '' }}" href="{{ url('/install') }}">网页安装</a>
        <a class="{{ ($way ?? 'web') === 'laravel' ? 'is-on' : '' }}" href="{{ url('/install?way=laravel') }}">Laravel 命令行</a>
        <a class="{{ ($way ?? 'web') === 'docker' ? 'is-on' : '' }}" href="{{ url('/install?way=docker') }}">Docker</a>
        <a class="{{ ($way ?? 'web') === 'deploy' ? 'is-on' : '' }}" href="{{ url('/install?way=deploy') }}">上线部署</a>
    </nav>
    @if(($way ?? 'web') !== 'web')
    <div class="card install-guide">
        @include(match ($way) {
            'laravel' => 'admin.help._laravel',
            'docker' => 'admin.help._docker',
            default => 'admin.help._env',
        })
    </div>
    @else
    <div class="card">
        <aside class="rail">
            <h1>三步即可用</h1>
            <p class="lead">检查环境、选好数据库、创建管理员。装好就能进后台采集和发片。</p>
            <ol class="steps" id="installSteps">
                <li class="is-on" data-step="0"><span class="n">1</span><span>环境检查</span></li>
                <li data-step="1"><span class="n">2</span><span>数据库</span></li>
                <li data-step="2"><span class="n">3</span><span>网站和管理员</span></li>
            </ol>
        </aside>
        <div class="pane">
            <p class="flash" id="installFlash" hidden></p>
            <form method="post" action="{{ url('/install/task') }}" id="installForm">
                @csrf
                <input type="hidden" name="app_url" value="{{ rtrim(request()->getSchemeAndHttpHost(), '/') }}">
                <section data-panel="0">
                    <h2>运行环境</h2>
                    <p class="pane-lead">
                        @if($requiredOk)
                            {{ $passN }} 项通过@if($warnN > 0)，{{ $warnN }} 项建议补上@endif。可以继续。
                        @else
                            有 {{ $failN }} 项必须修好，才能下一步。
                        @endif
                    </p>
                    <ul class="install-checks">
                        @foreach($checks as $check)
                            <li class="{{ $check['ok'] ? 'is-ok' : ($check['required'] ? 'is-bad' : 'is-warn') }}">
                                <span class="check-name">{{ $check['label'] }}</span>
                                <span class="check-state">{{ $check['ok'] ? '通过' : ($check['required'] ? '必须修复' : '建议补上') }}</span>
                                <span class="check-detail">{{ $check['detail'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <p class="hint">完整 php.ini、nginx 和定时任务见 <a href="{{ url('/install?way=deploy') }}">上线部署</a>。</p>
                    <div class="actions">
                        <span class="muted">{{ $requiredOk ? '' : '先处理标成必须修复的项。' }}</span>
                        <button class="btn" type="button" data-next @disabled(! $requiredOk)>下一步：数据库</button>
                    </div>
                </section>
                <section data-panel="1" hidden>
                    <h2>数据存在哪里</h2>
                    <p class="pane-lead">试用选文件数据库。正式站点建议 MySQL，先建好空库再填连接。</p>
                    <div class="choice" id="dbChoice">
                        <label class="is-on">
                            <input type="radio" name="db_connection" value="sqlite" checked>
                            <strong>文件数据库</strong>
                            <span>SQLite，写入 {{ $sqlitePath }}，不用单独建库。</span>
                        </label>
                        <label>
                            <input type="radio" name="db_connection" value="mysql">
                            <strong>MySQL</strong>
                            <span>请先建好空库，再填主机和账号。</span>
                        </label>
                    </div>
                    <div id="mysqlFields" class="field-grid" hidden>
                        <div>
                            <label for="db_host">主机</label>
                            <input id="db_host" type="text" name="db_host" value="127.0.0.1" autocomplete="off">
                        </div>
                        <div>
                            <label for="db_port">端口</label>
                            <input id="db_port" type="text" name="db_port" value="3306" inputmode="numeric">
                        </div>
                        <div class="span-2">
                            <label for="db_database">数据库名</label>
                            <input id="db_database" type="text" name="db_database" placeholder="laravideo" autocomplete="off">
                        </div>
                        <div>
                            <label for="db_username">用户名</label>
                            <input id="db_username" type="text" name="db_username" value="root" autocomplete="off">
                        </div>
                        <div>
                            <label for="db_password">密码</label>
                            <input id="db_password" type="password" name="db_password" autocomplete="new-password">
                        </div>
                    </div>
                    <p class="hint" id="dbHint">选文件数据库可直接继续；MySQL 会先测一次连接。</p>
                    <div class="actions">
                        <button class="btn-muted" type="button" data-prev>上一步</button>
                        <button class="btn" type="button" data-next-db>测试并继续</button>
                    </div>
                </section>
                <section data-panel="2" hidden>
                    <h2>网站和管理员</h2>
                    <p class="pane-lead">后台账号只在这一步创建，密码至少 6 位。</p>
                    <div class="install-fields" id="accountFields">
                        <label for="site_name">网站名称</label>
                        <input id="site_name" type="text" name="site_name" value="苹果v12" required maxlength="80">
                        <div class="field-grid">
                            <div>
                                <label for="admin_name">后台账号</label>
                                <input id="admin_name" type="text" name="admin_name" value="admin" required minlength="2" maxlength="50" autocomplete="username">
                            </div>
                            <div>
                                <label for="admin_email">邮箱（可选）</label>
                                <input id="admin_email" type="email" name="admin_email" maxlength="120" autocomplete="email">
                            </div>
                            <div>
                                <label for="admin_password">密码（至少 6 位）</label>
                                <input id="admin_password" type="password" name="admin_password" required minlength="6" autocomplete="new-password">
                            </div>
                            <div>
                                <label for="admin_password_confirmation">再输一次密码</label>
                                <input id="admin_password_confirmation" type="password" name="admin_password_confirmation" required minlength="6" autocomplete="new-password">
                            </div>
                        </div>
                        <label class="checkline">
                            <input type="checkbox" name="seed_demo" value="1" checked>
                            <span>写入电影 / 电视剧等默认分类，并加一条示例影片，方便打开前台看效果。</span>
                        </label>
                    </div>
                    <div class="progress" id="installProgress" hidden>
                        <div class="bar"><i id="installBar"></i></div>
                        <ul>
                            <li data-phase="prepare">准备配置</li>
                            <li data-phase="migrate">建立数据表</li>
                            <li data-phase="account">创建管理员</li>
                            <li data-phase="demo">示例数据</li>
                            <li data-phase="finish">完成</li>
                        </ul>
                    </div>
                    <p class="flash is-bad" id="installError" hidden></p>
                    <div class="actions">
                        <button class="btn-muted" type="button" data-prev id="installBack">上一步</button>
                        <button class="btn" type="submit" id="installSubmit">开始安装</button>
                    </div>
                </section>
            </form>
        </div>
    </div>
    @endif
</div>
@if(($way ?? 'web') === 'web')
<script>
(function () {
    var form = document.getElementById('installForm');
    var panels = Array.prototype.slice.call(form.querySelectorAll('[data-panel]'));
    var steps = Array.prototype.slice.call(document.querySelectorAll('#installSteps li'));
    var step = 0;
    var running = false;
    var csrf = document.querySelector('meta[name="csrf-token"]').content;
    var mysql = document.getElementById('mysqlFields');
    var dbHint = document.getElementById('dbHint');
    var flash = document.getElementById('installFlash');
    var progress = document.getElementById('installProgress');
    var bar = document.getElementById('installBar');
    var submit = document.getElementById('installSubmit');
    var back = document.getElementById('installBack');
    var account = document.getElementById('accountFields');
    var errBox = document.getElementById('installError');
    var sqlitePath = @json($sqlitePath);
    function show(i) {
        if (running) return;
        step = i;
        panels.forEach(function (p, idx) { p.hidden = idx !== i; });
        steps.forEach(function (s, idx) {
            s.classList.toggle('is-on', idx === i);
            s.classList.toggle('is-done', idx < i);
        });
        hideFlash();
        if (errBox) errBox.hidden = true;
    }
    function dbType() {
        var on = form.querySelector('[name="db_connection"]:checked');
        return on ? on.value : 'sqlite';
    }
    function hideFlash() {
        flash.hidden = true;
        flash.textContent = '';
        flash.className = 'flash';
    }
    function showFlash(msg, ok) {
        flash.hidden = !msg;
        flash.textContent = msg || '';
        flash.className = 'flash ' + (ok ? 'is-ok' : 'is-bad');
    }
    function syncDb() {
        mysql.hidden = dbType() !== 'mysql';
        document.querySelectorAll('#dbChoice label').forEach(function (lab) {
            lab.classList.toggle('is-on', lab.querySelector('input').checked);
        });
        if (dbType() === 'sqlite') {
            dbHint.textContent = '将写入 ' + sqlitePath + '，不用单独建库。';
            dbHint.className = 'hint';
        } else {
            dbHint.textContent = '填好后点「测试并继续」，连不上不会进入下一步。';
            dbHint.className = 'hint';
        }
        hideFlash();
    }
    form.querySelectorAll('[name="db_connection"]').forEach(function (el) {
        el.addEventListener('change', syncDb);
    });
    syncDb();
    steps.forEach(function (s) {
        s.addEventListener('click', function () {
            var i = parseInt(s.getAttribute('data-step'), 10);
            if (!running && i <= step) show(i);
        });
    });
    form.querySelectorAll('[data-next]').forEach(function (btn) {
        btn.addEventListener('click', function () { show(step + 1); });
    });
    form.querySelectorAll('[data-prev]').forEach(function (btn) {
        btn.addEventListener('click', function () { show(Math.max(0, step - 1)); });
    });
    form.querySelector('[data-next-db]').addEventListener('click', async function () {
        var btn = this;
        btn.disabled = true;
        dbHint.textContent = '正在测试连接…';
        dbHint.className = 'hint';
        hideFlash();
        try {
            var res = await fetch(@json(url('/install/probe')), {
                method: 'POST',
                headers: {'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json'},
                body: JSON.stringify({
                    db_connection: dbType(),
                    db_host: form.db_host.value,
                    db_port: form.db_port.value,
                    db_database: form.db_database.value,
                    db_username: form.db_username.value,
                    db_password: form.db_password.value
                })
            });
            var data = await res.json();
            if (!res.ok || !data.ok) throw new Error(data.message || '连不上数据库');
            dbHint.textContent = data.message || '数据库可以连接';
            dbHint.className = 'hint is-ok';
            show(2);
        } catch (e) {
            dbHint.textContent = e.message || '连不上数据库';
            dbHint.className = 'hint is-bad';
            showFlash(e.message || '连不上数据库', false);
        }
        btn.disabled = false;
    });
    form.addEventListener('submit', async function (ev) {
        ev.preventDefault();
        if (running) return;
        var pass = form.admin_password.value;
        var again = form.admin_password_confirmation.value;
        if (pass.length < 6) {
            errBox.hidden = false;
            errBox.textContent = '密码至少 6 位';
            return;
        }
        if (pass !== again) {
            errBox.hidden = false;
            errBox.textContent = '两次密码不一致';
            form.admin_password_confirmation.focus();
            return;
        }
        running = true;
        submit.disabled = true;
        if (back) back.disabled = true;
        account.hidden = true;
        progress.hidden = false;
        errBox.hidden = true;
        form.classList.add('is-running');
        var fd = new FormData(form);
        var phases = ['prepare', 'migrate', 'account', 'demo', 'finish'];
        var taskUrl = @json(url('/install/task'));
        function sleep(ms) { return new Promise(function (resolve) { setTimeout(resolve, ms); }); }
        async function postPhase(body) {
            var last = new Error('这一步没有完成');
            for (var attempt = 0; attempt < 6; attempt++) {
                try {
                    var res = await fetch(taskUrl, {
                        method: 'POST',
                        headers: {'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
                        body: body
                    });
                    var data = await res.json();
                    if (!res.ok || data.ok === false) throw new Error(data.message || '这一步没有完成');
                    return data;
                } catch (e) {
                    last = e;
                    if (e && e.message && /安装失败/.test(e.message)) throw e;
                    await sleep(800 * (attempt + 1));
                }
            }
            throw last;
        }
        try {
            for (var i = 0; i < phases.length; i++) {
                var li = progress.querySelector('[data-phase="'+phases[i]+'"]');
                li.classList.add('is-on');
                bar.style.width = Math.round((i / phases.length) * 100) + '%';
                fd.set('phase', phases[i]);
                await postPhase(fd);
                li.classList.remove('is-on');
                li.classList.add('is-ok');
                bar.style.width = Math.round(((i + 1) / phases.length) * 100) + '%';
            }
            window.location.href = @json(url('/install/done'));
        } catch (e) {
            running = false;
            form.classList.remove('is-running');
            account.hidden = false;
            errBox.hidden = false;
            errBox.textContent = e.message || '安装失败';
            submit.disabled = false;
            if (back) back.disabled = false;
        }
    });
})();
</script>
@endif
<script>
document.querySelectorAll('[data-copy]').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var code = btn.parentElement && btn.parentElement.querySelector('code');
        if (!code) return;
        var text = code.innerText;
        function done() {
            btn.textContent = '已复制';
            setTimeout(function () { btn.textContent = '复制'; }, 1500);
        }
        function fallback() {
            var ta = document.createElement('textarea');
            ta.value = text;
            ta.setAttribute('readonly', '');
            ta.style.position = 'fixed';
            ta.style.left = '-9999px';
            document.body.appendChild(ta);
            ta.select();
            try { document.execCommand('copy'); done(); } catch (e) {}
            document.body.removeChild(ta);
        }
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(done).catch(fallback);
        } else {
            fallback();
        }
    });
});
</script>
</body>
</html>
