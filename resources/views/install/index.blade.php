<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>安装 LaraVideo</title>
    <link rel="stylesheet" href="{{ asset('css/install.css') }}">
</head>
<body>
<div class="wrap">
    <div class="brand"><span class="logo">V</span> 影视系统安装</div>
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
            <form method="post" action="{{ url('/install/task') }}" id="installForm">
                @csrf
                <input type="hidden" name="app_url" value="{{ rtrim(request()->getSchemeAndHttpHost(), '/') }}">
                <section data-panel="0">
                    <h2>运行环境</h2>
                    <table class="checks">
                        @foreach($checks as $check)
                            <tr>
                                <td>{{ $check['label'] }}</td>
                                <td class="{{ $check['ok'] ? 'ok' : ($check['required'] ? 'bad' : 'warn') }}">
                                    {{ $check['ok'] ? '通过' : ($check['required'] ? '未通过' : '建议补上') }}
                                    · {{ $check['detail'] }}
                                </td>
                            </tr>
                        @endforeach
                    </table>
                    <div class="actions">
                        <span></span>
                        <button class="btn" type="button" data-next @disabled(! $requiredOk)>下一步：数据库</button>
                    </div>
                </section>
                <section data-panel="1" hidden>
                    <h2>数据存在哪里</h2>
                    <div class="choice" id="dbChoice">
                        <label class="is-on">
                            <input type="radio" name="db_connection" value="sqlite" checked>
                            <strong>文件数据库</strong>
                            <span>SQLite，适合先试用。</span>
                        </label>
                        <label>
                            <input type="radio" name="db_connection" value="mysql">
                            <strong>MySQL</strong>
                            <span>请先建好空库再填连接信息。</span>
                        </label>
                    </div>
                    <div id="mysqlFields" hidden>
                        <label>主机</label>
                        <input type="text" name="db_host" value="127.0.0.1">
                        <label>端口</label>
                        <input type="text" name="db_port" value="3306">
                        <label>数据库名</label>
                        <input type="text" name="db_database" placeholder="laravideo">
                        <label>用户名</label>
                        <input type="text" name="db_username" value="root">
                        <label>密码</label>
                        <input type="password" name="db_password" autocomplete="new-password">
                    </div>
                    <p class="hint" id="dbHint">{{ $dbOk ? '当前配置已经能连上数据库。' : ('当前还连不上：'.$dbError) }}</p>
                    <div class="actions">
                        <button class="btn-muted" type="button" data-prev>上一步</button>
                        <button class="btn" type="button" data-next-db>下一步：管理员</button>
                    </div>
                </section>
                <section data-panel="2" hidden>
                    <h2>网站和管理员</h2>
                    <label>网站名称</label>
                    <input type="text" name="site_name" value="星河影视" required>
                    <label>后台账号</label>
                    <input type="text" name="admin_name" value="admin" required minlength="2">
                    <label>邮箱（可选）</label>
                    <input type="email" name="admin_email">
                    <label>密码（至少 6 位）</label>
                    <input type="password" name="admin_password" required minlength="6" autocomplete="new-password">
                    <label>再输一次密码</label>
                    <input type="password" name="admin_password_confirmation" required minlength="6" autocomplete="new-password">
                    <label class="checkline">
                        <input type="checkbox" name="seed_demo" value="1" checked>
                        <span>写入电影/电视剧等默认分类，并加一条示例影片。</span>
                    </label>
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
                    <div class="actions">
                        <button class="btn-muted" type="button" data-prev>上一步</button>
                        <button class="btn" type="submit" id="installSubmit">开始安装</button>
                    </div>
                </section>
            </form>
        </div>
    </div>
</div>
<script>
(function () {
    var form = document.getElementById('installForm');
    var panels = Array.prototype.slice.call(form.querySelectorAll('[data-panel]'));
    var steps = Array.prototype.slice.call(document.querySelectorAll('#installSteps li'));
    var step = 0;
    var csrf = document.querySelector('meta[name="csrf-token"]').content;
    var mysql = document.getElementById('mysqlFields');
    var dbHint = document.getElementById('dbHint');
    var progress = document.getElementById('installProgress');
    var bar = document.getElementById('installBar');
    var submit = document.getElementById('installSubmit');
    function show(i) {
        step = i;
        panels.forEach(function (p, idx) { p.hidden = idx !== i; });
        steps.forEach(function (s, idx) {
            s.classList.toggle('is-on', idx === i);
            s.classList.toggle('is-done', idx < i);
        });
    }
    function dbType() {
        var on = form.querySelector('[name="db_connection"]:checked');
        return on ? on.value : 'sqlite';
    }
    function syncDb() {
        mysql.hidden = dbType() !== 'mysql';
        document.querySelectorAll('#dbChoice label').forEach(function (lab) {
            lab.classList.toggle('is-on', lab.querySelector('input').checked);
        });
    }
    form.querySelectorAll('[name="db_connection"]').forEach(function (el) {
        el.addEventListener('change', syncDb);
    });
    syncDb();
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
            dbHint.textContent = data.message;
            show(2);
        } catch (e) {
            dbHint.textContent = e.message;
        }
        btn.disabled = false;
    });
    form.addEventListener('submit', async function (ev) {
        ev.preventDefault();
        submit.disabled = true;
        progress.hidden = false;
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
            alert(e.message || '安装失败');
            submit.disabled = false;
        }
    });
})();
</script>
</body>
</html>
