<?php
/**
 * EnovelCms 1.5.5 安装程序
 */

// 环境检查
if (version_compare(PHP_VERSION, '8.0.0', '<') || version_compare(PHP_VERSION, '8.5.0', '>=')) {
    die('当前PHP版本为 ' . PHP_VERSION . '，EnovelCms 1.5.5 要求 PHP 8.0 ~ 8.4');
}
if (!extension_loaded('mbstring')) {
    die('缺少 mbstring 扩展，请安装并启用');
}

$install_root = realpath(__DIR__ . '/../') . '/';
$lock_file = $install_root . 'data/install.lock';

session_start();

if (file_exists($lock_file)) {
    ?>
    <!DOCTYPE html>
    <html>
    <head><meta charset="UTF-8"><title>网站已安装</title></head>
    <body style="font-family: Arial; text-align:center; padding:50px;">
        <h2>网站已安装</h2>
        <p><a href="/">进入首页</a> | <a href="/admin/">进入后台</a></p>
    </body>
    </html>
    <?php
    exit;
}

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$error = '';

// ==================== 步骤1：用户协议 ====================
if ($step == 1) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agree'])) {
        header('Location: install.php?step=2');
        exit;
    }
    ?>
    <!DOCTYPE html>
    <html>
    <head><meta charset="UTF-8"><title>EnovelCms 安装 - 用户协议</title>
    <style>
        body { font-family: 'Helvetica Neue', Arial, sans-serif; background: #f5f7fa; margin: 0; padding: 20px; }
        .container { max-width: 700px; margin: 50px auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; }
        .agreement-box { border: 1px solid #ddd; background: #fefefe; padding: 12px; height: 200px; overflow-y: scroll; margin: 15px 0; font-size: 12px; line-height: 1.5; color: #333; }
        .agree-check { margin: 20px 0; }
        button { background: #27ae60; color: #fff; padding: 12px 20px; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; width: 100%; }
        button:disabled { background: #ccc; cursor: not-allowed; }
    </style>
    <script>
        function toggleButton() {
            document.getElementById('nextBtn').disabled = !document.getElementById('agreeCheck').checked;
        }
    </script>
    </head>
    <body>
    <div class="container">
        <h2>EnovelCms 1.5.5 安装向导</h2>
        <h3>用户协议</h3>
        <div class="agreement-box">
            <strong>Enovel CMS 用户协议</strong><br><br>
            感谢您选择 Enovel 小说系统（以下简称“本软件”）。在安装或使用本软件前，请仔细阅读以下条款。一旦安装、复制或以其他方式使用本软件，即表示您同意接受本协议约束。<br><br>
            <strong>1. 许可范围</strong><br>
            您可以在遵守本协议的前提下，在单个网站域名下自由使用本软件，不得对软件进行反向工程、反编译或试图提取源代码（开源部分除外）。再尊重版权的前提下允许你基于本程序免费商用和二开，但是本程序官网不会提供任何二开技术支持。<br><br>
            <strong>2. 使用限制</strong><br>
            您不得利用本软件从事任何违法违规活动，包括但不限于传播色情、赌博、暴力、政治敏感内容或侵犯他人知识产权的内容。因使用本软件产生的任何法律后果由您自行承担，软件作者不承担连带责任。<br><br>
            <strong>3. 免责声明</strong><br>
            本软件按“现状”提供，不提供任何明示或暗示的担保。作者不对因使用本软件造成的任何数据丢失、业务中断或经济损失负责。建议您定期备份数据。<br><br>
            <strong>4. 保留权利</strong><br>
            本软件的知识产权（包括版权、商标权等）归原作者所有。未经授权不得去除或修改软件中的版权标识。<br><br>
            <strong>5. 其他</strong><br>
            本协议受中华人民共和国法律管辖。如有任何争议，双方应友好协商解决。作者保留随时更新本协议的权利，更新后继续使用即视为接受修改。<br><br>
            如果您不同意以上条款，请停止安装并删除所有相关文件。
        </div>
        <form method="post">
            <div class="agree-check">
                <input type="checkbox" name="agree" id="agreeCheck" value="1" onchange="toggleButton()" required>
                <label for="agreeCheck">我已经阅读并同意上述用户协议</label>
            </div>
            <button type="submit" id="nextBtn" disabled>下一步：配置安装</button>
        </form>
    </div>
    </body>
    </html>
    <?php
    exit;
}

// ==================== 步骤2：配置表单 + 旧数据处理 ====================
if ($step == 2) {
    // ---------- 处理 POST 提交 ----------
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // 处理选择界面提交（清除/放弃）
        if (isset($_POST['action']) && in_array($_POST['action'], ['install', 'abort'])) {
            $action = $_POST['action'];
            if (!isset($_SESSION['install_config'])) {
                header('Location: install.php?step=2');
                exit;
            }
            $config = $_SESSION['install_config'];

            if ($action === 'abort') {
                if (!is_dir($install_root . 'data')) mkdir($install_root . 'data', 0755, true);
                file_put_contents($lock_file, date('Y-m-d H:i:s') . "\n");
                unset($_SESSION['install_config']);
                header('Location: install.php?step=done&action=abort');
                exit;
            } else {
                $config['clear_data'] = 1;
                $_SESSION['install_config'] = $config;
                header('Location: install.php?step=3');
                exit;
            }
        }

        // 处理配置表单提交（初次提交）
        if (isset($_POST['db_host'])) {
            $db_host = trim($_POST['db_host']);
            $db_name = trim($_POST['db_name']);
            $db_user = trim($_POST['db_user']);
            $db_pass = $_POST['db_pass'];
            $site_name = trim($_POST['site_name']);
            $admin_user = trim($_POST['admin_user']);
            $admin_pass = $_POST['admin_pass'];

            if (empty($db_host) || empty($db_name) || empty($db_user)) {
                $error = '请填写完整的数据库信息';
            } elseif (strlen($admin_user) < 3) {
                $error = '管理员用户名至少3个字符';
            } elseif (strlen($admin_pass) < 6) {
                $error = '管理员密码至少6个字符';
            } else {
                $testConn = @new mysqli($db_host, $db_user, $db_pass);
                if ($testConn->connect_error) {
                    $error = '数据库连接失败：' . $testConn->connect_error;
                } else {
                    if (!$testConn->select_db($db_name)) {
                        $sql = "CREATE DATABASE IF NOT EXISTS `$db_name` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
                        if (!$testConn->query($sql)) {
                            $error = '创建数据库失败：' . $testConn->error;
                        } else {
                            $testConn->select_db($db_name);
                        }
                    }

                    if (empty($error)) {
                        $table_exists = false;
                        $result = $testConn->query("SHOW TABLES LIKE 'users'");
                        if ($result && $result->num_rows > 0) {
                            $table_exists = true;
                        }
                        $testConn->close();

                        $_SESSION['install_config'] = [
                            'db_host' => $db_host,
                            'db_name' => $db_name,
                            'db_user' => $db_user,
                            'db_pass' => $db_pass,
                            'site_name' => $site_name,
                            'admin_user' => $admin_user,
                            'admin_pass' => $admin_pass,
                            'clear_data' => 0
                        ];

                        if ($table_exists) {
                            header('Location: install.php?step=2&choice=1');
                            exit;
                        } else {
                            header('Location: install.php?step=3');
                            exit;
                        }
                    }
                }
            }
        }
    }

    // ---------- 显示页面 ----------
    $showChoice = isset($_GET['choice']) && $_GET['choice'] == 1 && isset($_SESSION['install_config']);

    if ($showChoice) {
        $config = $_SESSION['install_config'];
        ?>
        <!DOCTYPE html>
        <html>
        <head><meta charset="UTF-8"><title>检测到旧数据</title>
        <style>
            body { font-family: Arial; text-align:center; padding:50px; background:#f5f7fa; }
            .container { max-width:500px; margin:0 auto; background:#fff; padding:30px; border-radius:8px; box-shadow:0 2px 10px rgba(0,0,0,0.1); }
            h2 { color:#f0ad4e; }
            .btn-group { margin-top:30px; }
            .btn { display:inline-block; padding:12px 30px; border:none; border-radius:4px; font-size:16px; cursor:pointer; margin:0 10px; text-decoration:none; }
            .btn-install { background:#27ae60; color:#fff; }
            .btn-abort { background:#f0ad4e; color:#fff; }
        </style>
        </head>
        <body>
        <div class="container">
            <h2>⚠️ 检测到数据库已有数据</h2>
            <p>数据库 <strong><?php echo htmlspecialchars($config['db_name']); ?></strong> 中已存在表。</p>
            <p>请选择操作：</p>
            <form method="post">
                <input type="hidden" name="action" value="install">
                <button type="submit" class="btn btn-install">清除旧数据，重新安装</button>
            </form>
            <form method="post" style="margin-top:10px;">
                <input type="hidden" name="action" value="abort">
                <button type="submit" class="btn btn-abort">放弃安装（保留现有数据）</button>
            </form>
        </div>
        </body>
        </html>
        <?php
        exit;
    }

    // 显示配置表单
    $config = isset($_SESSION['install_config']) ? $_SESSION['install_config'] : [];
    $db_host = $config['db_host'] ?? 'localhost';
    $db_name = $config['db_name'] ?? 'enovel';
    $db_user = $config['db_user'] ?? 'root';
    $db_pass = $config['db_pass'] ?? '';
    $site_name = $config['site_name'] ?? 'EnovelCms';
    $admin_user = $config['admin_user'] ?? 'admin';
    $admin_pass = $config['admin_pass'] ?? '';
    ?>
    <!DOCTYPE html>
    <html>
    <head><meta charset="UTF-8"><title>EnovelCms 安装 - 数据库配置</title>
    <style>
        body { font-family: 'Helvetica Neue', Arial, sans-serif; background: #f5f7fa; margin: 0; padding: 20px; }
        .container { max-width: 700px; margin: 50px auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        label { display: block; margin: 15px 0 5px; font-weight: bold; }
        input[type="text"], input[type="password"] { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        .error { color: #e74c3c; background: #fadbd8; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        button { background: #27ae60; color: #fff; padding: 12px 20px; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; width: 100%; }
    </style>
    </head>
    <body>
    <div class="container">
        <h2>数据库与网站配置</h2>
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="post">
            <h3>数据库信息</h3>
            <label>数据库主机 *</label>
            <input type="text" name="db_host" value="<?php echo htmlspecialchars($db_host); ?>" required>
            <label>数据库名 *</label>
            <input type="text" name="db_name" value="<?php echo htmlspecialchars($db_name); ?>" required>
            <label>数据库用户名 *</label>
            <input type="text" name="db_user" value="<?php echo htmlspecialchars($db_user); ?>" required>
            <label>数据库密码</label>
            <input type="password" name="db_pass" value="<?php echo htmlspecialchars($db_pass); ?>">

            <h3>网站信息</h3>
            <label>网站名称</label>
            <input type="text" name="site_name" value="<?php echo htmlspecialchars($site_name); ?>">

            <h3>管理员账号</h3>
            <label>管理员用户名 *</label>
            <input type="text" name="admin_user" value="<?php echo htmlspecialchars($admin_user); ?>" required>
            <label>管理员密码 *</label>
            <input type="password" name="admin_pass" value="<?php echo htmlspecialchars($admin_pass); ?>" required>

            <button type="submit">开始安装</button>
        </form>
    </div>
    </body>
    </html>
    <?php
    exit;
}

// ==================== 步骤3：执行安装 ====================
if ($step == 3) {
    if (!isset($_SESSION['install_config'])) {
        header('Location: install.php?step=2');
        exit;
    }
    $config = $_SESSION['install_config'];

    $mysqli = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);
    if ($mysqli->connect_error) {
        die('数据库连接失败：' . $mysqli->connect_error);
    }
    $mysqli->set_charset('utf8mb4');

    if (!empty($config['clear_data'])) {
        $result = $mysqli->query("SHOW TABLES");
        $tables = [];
        while ($row = $result->fetch_array(MYSQLI_NUM)) {
            $tables[] = $row[0];
        }
        if (!empty($tables)) {
            $mysqli->query("SET FOREIGN_KEY_CHECKS = 0");
            foreach ($tables as $table) {
                $mysqli->query("DROP TABLE IF EXISTS `$table`");
            }
            $mysqli->query("SET FOREIGN_KEY_CHECKS = 1");
        }
    }

    $sqlFile = __DIR__ . '/install.sql';
    if (!file_exists($sqlFile)) {
        die('install.sql 文件不存在');
    }
    $sql = file_get_contents($sqlFile);
    $queries = array_filter(array_map('trim', explode(';', $sql)));
    foreach ($queries as $query) {
        if (!empty($query) && !$mysqli->query($query)) {
            die('执行SQL失败：' . $mysqli->error);
        }
    }
    $mysqli->close();

    // ========== 生成 config.php ==========
    $configTemplate = <<<'EOT'
<?php
if (session_status() === PHP_SESSION_NONE) session_start();
define('ROOT_PATH', realpath(__DIR__ . '/../') . '/');
define('BASE_URL', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']);

define('DB_HOST', 'DB_HOST_PLACEHOLDER');
define('DB_NAME', 'DB_NAME_PLACEHOLDER');
define('DB_USER', 'DB_USER_PLACEHOLDER');
define('DB_PASS', 'DB_PASS_PLACEHOLDER');
define('DB_CHARSET', 'utf8mb4');

$versionFile = ROOT_PATH . '/includes/version.php';
if (file_exists($versionFile)) {
    $version = include $versionFile;
    define('ENOVELCMS_VERSION', $version ?: '1.0.0');
} else {
    define('ENOVELCMS_VERSION', '1.0.0');
}

$dirs = [ROOT_PATH . 'data', ROOT_PATH . 'data/chapters', ROOT_PATH . 'data/covers', ROOT_PATH . 'assets/uploads/ads', ROOT_PATH . 'assets/uploads/logo'];
foreach ($dirs as $dir) { if (!is_dir($dir)) mkdir($dir, 0755, true); }

spl_autoload_register(function ($class) {
    $base_dir = ROOT_PATH . 'includes/';
    $file = $base_dir . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) { require $file; return true; }
    return false;
});

require_once ROOT_PATH . 'includes/functions.php';
$db = new Database();

$isAdminArea = (strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false);
$lang = new Language($isAdminArea ? 'backend' : 'frontend');
$GLOBALS['db'] = $db;
$GLOBALS['lang'] = $lang;
?>
EOT;
    // 替换数据库配置
    $configContent = str_replace(
        ['DB_HOST_PLACEHOLDER', 'DB_NAME_PLACEHOLDER', 'DB_USER_PLACEHOLDER', 'DB_PASS_PLACEHOLDER'],
        [$config['db_host'], $config['db_name'], $config['db_user'], $config['db_pass']],
        $configTemplate
    );

    if (file_put_contents($install_root . 'includes/config.php', $configContent) === false) {
        die('无法写入 config.php，请检查 includes 目录权限。');
    }

    require_once $install_root . 'includes/config.php';

    // 设置站点名称
    $db->query("REPLACE INTO settings (`key`, `value`) VALUES ('site_name', ?)", [$config['site_name']]);

    // 创建管理员
    $hashed = password_hash($config['admin_pass'], PASSWORD_DEFAULT);
    $exists = $db->fetch($db->query("SELECT id FROM admins LIMIT 1"));
    if ($exists) {
        $db->query("UPDATE admins SET password = ? WHERE id = ?", [$hashed, $exists['id']]);
    } else {
        $db->query("INSERT INTO admins (username, password) VALUES (?, ?)", [$config['admin_user'], $hashed]);
    }

    // 生成安装锁
    if (!is_dir($install_root . 'data')) mkdir($install_root . 'data', 0755, true);
    file_put_contents($lock_file, date('Y-m-d H:i:s') . "\n");

    // 备份 install.php
    $installPath = __DIR__ . '/install.php';
    if (file_exists($installPath)) {
        $newName = 'install_' . time() . '_' . substr(bin2hex(random_bytes(4)), 0, 8) . '.bak';
        rename($installPath, $install_root . 'data/' . $newName);
    }

    $admin_user = $config['admin_user'];
    $admin_pass = $config['admin_pass'];
    unset($_SESSION['install_config']);

    // 显示完成页
    ?>
    <!DOCTYPE html>
    <html>
    <head><meta charset="UTF-8"><title>EnovelCms 安装成功</title>
    <style>
        body { font-family: Arial; text-align:center; padding:50px; background:#f5f7fa; }
        .container { max-width:600px; margin:0 auto; background:#fff; padding:30px; border-radius:8px; box-shadow:0 2px 10px rgba(0,0,0,0.1); }
        h2 { color:#27ae60; }
        .info { background:#f9f9f9; padding:15px; border-radius:4px; text-align:left; margin:20px 0; }
        .info strong { display:inline-block; width:120px; }
        .links { margin-top:30px; }
        .links a { display:inline-block; margin:0 10px; padding:10px 20px; background:#27ae60; color:#fff; text-decoration:none; border-radius:4px; }
        .links a.admin { background:#3498db; }
    </style>
    </head>
    <body>
    <div class="container">
        <h2>🎉 安装成功！</h2>
        <p>管理员账号信息：</p>
        <div class="info">
            <p><strong>用户名：</strong> <?php echo htmlspecialchars($admin_user); ?></p>
            <p><strong>密码：</strong> <?php echo htmlspecialchars($admin_pass); ?></p>
            <p style="color:#e74c3c;">⚠️ 请妥善保管，建议登录后立即修改。</p>
        </div>
        <div class="links">
            <a href="/">进入首页</a>
            <a href="/admin/" class="admin">进入后台</a>
        </div>
    </div>
    </body>
    </html>
    <?php
    exit;
}

// ==================== 完成页（放弃安装） ====================
if ($step == 'done' && isset($_GET['action']) && $_GET['action'] === 'abort') {
    ?>
    <!DOCTYPE html>
    <html>
    <head><meta charset="UTF-8"><title>安装已放弃</title></head>
    <body style="font-family: Arial; text-align:center; padding:50px;">
        <h2>安装已放弃</h2>
        <p>已生成安装锁，网站将保持当前状态。</p>
        <p><a href="/">进入首页</a> | <a href="/admin/">进入后台</a></p>
    </body>
    </html>
    <?php
    exit;
}

// 其他情况回到步骤1
header('Location: install.php?step=1');
exit;