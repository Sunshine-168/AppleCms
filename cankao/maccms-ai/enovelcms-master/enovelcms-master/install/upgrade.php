<?php
/**
 * EnovelCms 升级程序 1.5.1 → 1.5.5
 * 修正：步骤4验证配置非空，否则报错
 */
if (isset($_GET['clear'])) {
    session_start();
    session_unset();
    session_destroy();
    header('Location: upgrade.php');
    exit;
}

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('max_execution_time', '0');
ini_set('memory_limit', '1G');
mb_internal_encoding('UTF-8');

session_set_cookie_params([
    'lifetime' => 3600,
    'path' => '/',
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();
session_regenerate_id(true);

define('ROOT_PATH', rtrim(realpath(__DIR__ . '/..'), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
define('INSTALL_PATH', __DIR__ . DIRECTORY_SEPARATOR);
define('LOCK_FILE', ROOT_PATH . 'data' . DIRECTORY_SEPARATOR . 'upgrade.lock');
define('LOG_FILE', ROOT_PATH . 'data' . DIRECTORY_SEPARATOR . 'upgrade_runtime.log');
define('BACKUP_DIR', ROOT_PATH . 'old' . DIRECTORY_SEPARATOR);
define('ZIP_PACK', INSTALL_PATH . 'upgrade.zip');

function writeLog($msg) {
    file_put_contents(LOG_FILE, '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL, FILE_APPEND | LOCK_EX);
}

function rrmdir($dir) {
    if (!is_dir($dir)) return;
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($files as $file) {
        if ($file->isDir()) rmdir($file->getRealPath());
        else unlink($file->getRealPath());
    }
    rmdir($dir);
}

function getDbConfigFromFile($configPath) {
    $config = ['host' => '', 'name' => '', 'user' => '', 'pass' => ''];
    if (!file_exists($configPath)) return $config;
    $lines = file($configPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (preg_match("/define\s*\(\s*['\"]DB_HOST['\"]\s*,\s*['\"]([^'\"]*)['\"]\s*\)/i", $line, $m)) $config['host'] = $m[1];
        elseif (preg_match("/define\s*\(\s*['\"]DB_NAME['\"]\s*,\s*['\"]([^'\"]*)['\"]\s*\)/i", $line, $m)) $config['name'] = $m[1];
        elseif (preg_match("/define\s*\(\s*['\"]DB_USER['\"]\s*,\s*['\"]([^'\"]*)['\"]\s*\)/i", $line, $m)) $config['user'] = $m[1];
        elseif (preg_match("/define\s*\(\s*['\"]DB_PASS['\"]\s*,\s*['\"]([^'\"]*)['\"]\s*\)/i", $line, $m)) $config['pass'] = $m[1];
    }
    return $config;
}

function writeDbConfigToFile($configPath, $config) {
    if (!file_exists($configPath)) return false;
    $lines = file($configPath, FILE_IGNORE_NEW_LINES);
    $newLines = [];
    foreach ($lines as $line) {
        if (preg_match("/define\s*\(\s*['\"]DB_HOST['\"]\s*,\s*['\"][^'\"]*['\"]\s*\)/i", $line)) {
            $newLines[] = "define('DB_HOST', '{$config['host']}');";
        } elseif (preg_match("/define\s*\(\s*['\"]DB_NAME['\"]\s*,\s*['\"][^'\"]*['\"]\s*\)/i", $line)) {
            $newLines[] = "define('DB_NAME', '{$config['name']}');";
        } elseif (preg_match("/define\s*\(\s*['\"]DB_USER['\"]\s*,\s*['\"][^'\"]*['\"]\s*\)/i", $line)) {
            $newLines[] = "define('DB_USER', '{$config['user']}');";
        } elseif (preg_match("/define\s*\(\s*['\"]DB_PASS['\"]\s*,\s*['\"][^'\"]*['\"]\s*\)/i", $line)) {
            $newLines[] = "define('DB_PASS', '{$config['pass']}');";
        } else {
            $newLines[] = $line;
        }
    }
    return file_put_contents($configPath, implode("\n", $newLines), LOCK_EX) !== false;
}

if (!isset($_SESSION['upgrade_authorized']) || $_SESSION['upgrade_authorized'] !== true) {
    $_SESSION['upgrade_authorized'] = false;
    $_SESSION['upgrade_client_ip'] = '';
    $_SESSION['upgrade_step'] = 0;
    $_SESSION['upgrade_logs'] = [];
    $_SESSION['upgrade_error'] = '';
    $_SESSION['upgrade_backup_file'] = '';
    $_SESSION['upgrade_db_config'] = [];
    $_SESSION['upgrade_progress'] = [
        'backup_done' => false,
        'db_backup_done' => false,
        'delete_done' => false,
        'extract_done' => false,
        'config_done' => false,
        'sql_done' => false,
        'lock_done' => false,
    ];
}

$preCheckErrors = [];
if (file_exists(LOCK_FILE)) {
    $preCheckErrors[] = '升级锁定已生效，如需重新升级请手动删除 data/upgrade.lock 文件';
}
if (!is_dir(BACKUP_DIR) && !mkdir(BACKUP_DIR, 0755, true)) {
    $preCheckErrors[] = '无法创建备份目录 old/，请检查权限';
}
if (!class_exists('ZipArchive')) {
    $preCheckErrors[] = '缺少 ZipArchive PHP 扩展，无法解压/压缩';
}
if (!file_exists(ZIP_PACK)) {
    $preCheckErrors[] = 'install/upgrade.zip 升级包不存在，请上传';
}
$configFile = ROOT_PATH . 'includes/config.php';
if (!file_exists($configFile)) {
    $preCheckErrors[] = 'includes/config.php 文件不存在，请确保站点已安装';
}

$ip = $_SERVER['REMOTE_ADDR'] ?? '';
if ($_SESSION['upgrade_authorized'] && $_SESSION['upgrade_client_ip'] !== $ip) {
    session_destroy();
    header('Location: upgrade.php?clear=1');
    exit;
}

if (isset($_GET['reset'])) {
    if (isset($_GET['confirm'])) {
        session_destroy();
        header('Location: upgrade.php?clear=1');
        exit;
    } else {
        echo '<script>if(confirm("确认重置所有升级进度？")) location.href="?reset=1&confirm=1";</script>';
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    if (!empty($preCheckErrors)) {
        $_SESSION['upgrade_error'] = '环境预检失败：' . implode(' | ', $preCheckErrors);
        header('Location: upgrade.php');
        exit;
    }
    $user = trim($_POST['admin_user'] ?? '');
    $pass = $_POST['admin_pass'] ?? '';
    if (empty($user) || empty($pass)) {
        $_SESSION['upgrade_error'] = '请输入管理员用户名和密码';
        header('Location: upgrade.php');
        exit;
    }
    try {
        require_once $configFile;
        $stmt = $db->query("SELECT id, username, password FROM admins WHERE username = ?", [$user]);
        $admin = $db->fetch($stmt);
        if (!$admin || !password_verify($pass, $admin['password'])) {
            throw new Exception('管理员用户名或密码错误');
        }
        $_SESSION['upgrade_authorized'] = true;
        $_SESSION['upgrade_client_ip'] = $ip;
        $_SESSION['upgrade_step'] = 1;
        $_SESSION['upgrade_logs'] = ['✅ 管理员验证通过，升级开始'];
        $_SESSION['upgrade_error'] = '';
        writeLog("管理员 {$user} 登录升级程序");
    } catch (Exception $e) {
        $_SESSION['upgrade_error'] = $e->getMessage();
        writeLog("登录失败：" . $e->getMessage());
    }
    header('Location: upgrade.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['do_step'])) {
    if (!$_SESSION['upgrade_authorized']) {
        $_SESSION['upgrade_error'] = '未授权操作';
        header('Location: upgrade.php?clear=1');
        exit;
    }
    if (!empty($preCheckErrors)) {
        $_SESSION['upgrade_error'] = '环境预检失败：' . implode(' | ', $preCheckErrors);
        header('Location: upgrade.php');
        exit;
    }
    $step = (int)$_POST['do_step'];
    try {
        $logs = &$_SESSION['upgrade_logs'];
        $progress = &$_SESSION['upgrade_progress'];
        switch ($step) {
            case 1:
                if ($progress['backup_done']) throw new Exception('文件备份已完成');
                $logs[] = '📦 步骤1：开始全站文件备份...';
                writeLog('开始文件备份');
                $timestamp = date('Ymd_His');
                $backupName = "backup_{$timestamp}.zip";
                $backupFull = BACKUP_DIR . $backupName;
                $_SESSION['upgrade_backup_file'] = $backupFull;
                $zip = new ZipArchive();
                if ($zip->open($backupFull, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                    throw new Exception('无法创建备份压缩包');
                }
                $excludeDirs = ['data', 'old', 'install'];
                $excludeFiles = ['.htaccess', '.user.ini', 'upgrade.lock'];
                $iter = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator(ROOT_PATH, RecursiveDirectoryIterator::SKIP_DOTS)
                );
                foreach ($iter as $file) {
                    $rel = str_replace(ROOT_PATH, '', $file->getRealPath());
                    $first = explode(DIRECTORY_SEPARATOR, $rel)[0] ?? '';
                    if (in_array($first, $excludeDirs)) continue;
                    if (in_array(basename($rel), $excludeFiles)) continue;
                    if ($file->isFile()) $zip->addFile($file->getRealPath(), $rel);
                }
                $zip->close();
                $size = round(filesize($backupFull) / 1024 / 1024, 2);
                $logs[] = "✅ 文件备份完成：{$backupName}（{$size} MB）";
                $progress['backup_done'] = true;
                $_SESSION['upgrade_step'] = 2;
                writeLog('文件备份完成');
                break;

            case 2:
                if (!$progress['backup_done']) throw new Exception('请先完成文件备份');
                if ($progress['db_backup_done']) throw new Exception('数据库备份已完成');
                $logs[] = '📀 步骤2：导出数据库完整备份...';
                writeLog('开始数据库备份');
                $cfg = getDbConfigFromFile($configFile);
                if (empty($cfg['host']) || empty($cfg['name']) || empty($cfg['user'])) {
                    throw new Exception('无法从 config.php 读取数据库配置');
                }
                $dbFile = BACKUP_DIR . 'db_backup_' . date('Ymd_His') . '.sql';
                $logs[] = 'ℹ️ 使用 PHP 逐表导出数据库...';
                require_once $configFile;
                $tables = [];
                $res = $db->query("SHOW TABLES");
                while ($row = $db->fetch($res)) {
                    $tables[] = array_values($row)[0];
                }
                $sql = "-- EnovelCms 数据库备份\n-- 时间：" . date('Y-m-d H:i:s') . "\n\n";
                foreach ($tables as $table) {
                    $create = $db->fetch($db->query("SHOW CREATE TABLE `$table`"));
                    $sql .= $create['Create Table'] . ";\n\n";
                    $rows = $db->fetchAll($db->query("SELECT * FROM `$table`"));
                    if (!empty($rows)) {
                        $sql .= "INSERT INTO `$table` VALUES\n";
                        $vals = [];
                        foreach ($rows as $row) {
                            $esc = array_map(function($v) {
                                return $v === null ? 'NULL' : "'" . addslashes($v) . "'";
                            }, array_values($row));
                            $vals[] = "(" . implode(',', $esc) . ")";
                        }
                        $sql .= implode(",\n", $vals) . ";\n\n";
                    }
                }
                if (file_put_contents($dbFile, $sql, LOCK_EX) === false) {
                    throw new Exception('写入数据库备份文件失败');
                }
                $zip = new ZipArchive();
                if ($zip->open($_SESSION['upgrade_backup_file']) === true) {
                    $zip->addFile($dbFile, 'database_backup.sql');
                    $zip->close();
                }
                $logs[] = "✅ 数据库备份完成（PHP 导出）";
                $progress['db_backup_done'] = true;
                $_SESSION['upgrade_step'] = 3;
                writeLog('数据库备份完成');
                break;

            case 3:
                if (!$progress['db_backup_done']) throw new Exception('请先完成数据库备份');
                if ($progress['delete_done']) throw new Exception('清理已完成');
                $logs[] = '🗑️ 步骤3：清理旧版本目录（templates、user）...';
                writeLog('开始清理旧目录');
                $cleanList = INSTALL_PATH . 'upgrade.txt';
                if (!file_exists($cleanList)) {
                    $logs[] = 'ℹ️ 未找到 upgrade.txt，跳过清理';
                } else {
                    $lines = file($cleanList, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                    foreach ($lines as $dir) {
                        $dir = trim($dir);
                        if (empty($dir)) continue;
                        $target = ROOT_PATH . $dir;
                        if (is_dir($target)) {
                            rrmdir($target);
                            $logs[] = "✅ 已删除目录：{$dir}";
                        } else {
                            $logs[] = "ℹ️ 目录不存在，跳过：{$dir}";
                        }
                    }
                }
                $progress['delete_done'] = true;
                $_SESSION['upgrade_step'] = 4;
                writeLog('旧目录清理完成');
                break;

            // ========== 步骤4：先读取配置（验证非空），再解压 ==========
            case 4:
                if (!$progress['delete_done']) throw new Exception('请先完成旧目录清理');
                if ($progress['extract_done']) throw new Exception('升级包已解压');
                $logs[] = '📂 步骤4：缓存数据库配置并解压升级包...';
                writeLog('开始步骤4：缓存配置+解压');
                // 读取当前 config.php（此时还是旧的）
                $cfg = getDbConfigFromFile($configFile);
                if (empty($cfg['host']) || empty($cfg['name']) || empty($cfg['user'])) {
                    throw new Exception('无法从当前 config.php 读取数据库配置（配置为空），请检查文件或手动恢复备份');
                }
                $_SESSION['upgrade_db_config'] = $cfg;
                $logs[] = "✅ 已缓存数据库配置（DB_HOST: {$cfg['host']}, DB_NAME: {$cfg['name']}, DB_USER: {$cfg['user']}）";
                // 解压升级包
                $zip = new ZipArchive();
                if ($zip->open(ZIP_PACK) !== true) {
                    throw new Exception('无法打开 upgrade.zip');
                }
                if (!$zip->extractTo(ROOT_PATH)) {
                    throw new Exception('解压失败，请检查根目录权限');
                }
                $zip->close();
                $logs[] = '✅ 升级包解压完成';
                $progress['extract_done'] = true;
                $_SESSION['upgrade_step'] = 5;
                writeLog('步骤4完成：配置已缓存，升级包已解压');
                break;

            // ========== 步骤5：从 Session 恢复配置 ==========
            case 5:
                if (!$progress['extract_done']) throw new Exception('请先解压升级包');
                if ($progress['config_done']) throw new Exception('配置已恢复');
                $logs[] = '⚙️ 步骤5：将缓存的数据库配置写回新版 config.php...';
                writeLog('开始恢复配置');
                if (empty($_SESSION['upgrade_db_config'])) {
                    throw new Exception('未找到缓存的数据库配置，请重新执行步骤4');
                }
                $cfg = $_SESSION['upgrade_db_config'];
                if (!writeDbConfigToFile($configFile, $cfg)) {
                    throw new Exception('写入 config.php 失败，请检查文件权限');
                }
                chmod($configFile, 0644);
                $logs[] = "✅ 数据库配置已恢复（DB_HOST: {$cfg['host']}, DB_NAME: {$cfg['name']}, DB_USER: {$cfg['user']}）";
                $progress['config_done'] = true;
                $_SESSION['upgrade_step'] = 6;
                writeLog('配置恢复完成');
                break;

            case 6:
                if (!$progress['config_done']) throw new Exception('请先恢复数据库配置');
                if ($progress['sql_done']) throw new Exception('SQL已执行');
                $logs[] = '📝 步骤6：执行数据库结构升级...';
                writeLog('开始执行SQL升级');
                require_once $configFile;
                $alterQueries = [];
                $res = $db->query("SHOW COLUMNS FROM novels LIKE 'source_id'");
                if (!$db->fetch($res)) {
                    $alterQueries[] = "ALTER TABLE novels ADD COLUMN source_id VARCHAR(100) DEFAULT NULL AFTER author";
                }
                $res = $db->query("SHOW COLUMNS FROM chapters LIKE 'source_chapter_id'");
                if (!$db->fetch($res)) {
                    $alterQueries[] = "ALTER TABLE chapters ADD COLUMN source_chapter_id VARCHAR(50) DEFAULT NULL AFTER source_url";
                }
                $settings = [
                    'active_theme' => 'default',
                    'register_mode' => 'normal',
                    'locoy_api_key' => '',
                    'locoy_category_map' => '{"mappings":[],"default_category_id":0}'
                ];
                foreach ($settings as $key => $val) {
                    $res = $db->query("SELECT 1 FROM settings WHERE `key` = ?", [$key]);
                    if (!$db->fetch($res)) {
                        $db->query("INSERT INTO settings (`key`, `value`) VALUES (?, ?)", [$key, $val]);
                    }
                }
                foreach ($alterQueries as $sql) {
                    $db->query($sql);
                    $logs[] = "✅ 执行：{$sql}";
                }
                if (empty($alterQueries)) {
                    $logs[] = 'ℹ️ 所有字段已存在，无需修改';
                }
                $progress['sql_done'] = true;
                $_SESSION['upgrade_step'] = 7;
                writeLog('SQL升级完成');
                break;

            case 7:
                if (!$progress['sql_done']) throw new Exception('请先执行SQL升级');
                if ($progress['lock_done']) throw new Exception('已锁定');
                $logs[] = '🔒 步骤7：生成升级锁定文件...';
                file_put_contents(LOCK_FILE, "升级完成时间：" . date('Y-m-d H:i:s') . "\n版本 1.5.1 → 1.5.5");
                chmod(LOCK_FILE, 0644);
                @unlink(ZIP_PACK);
                @unlink(INSTALL_PATH . 'upgrade.sql');
                $logs[] = '🎉 全部升级步骤完成！';
                $progress['lock_done'] = true;
                $_SESSION['upgrade_step'] = 8;
                writeLog('升级锁定完成');
                break;

            default:
                throw new Exception('无效步骤');
        }
        $_SESSION['upgrade_error'] = '';
    } catch (Exception $e) {
        $_SESSION['upgrade_error'] = $e->getMessage();
        $_SESSION['upgrade_logs'][] = '❌ 错误：' . $e->getMessage();
        writeLog('错误：' . $e->getMessage());
    }
    header('Location: upgrade.php');
    exit;
}

$authorized = $_SESSION['upgrade_authorized'];
$currentStep = $_SESSION['upgrade_step'];
$logs = $_SESSION['upgrade_logs'] ?? [];
$error = $_SESSION['upgrade_error'] ?? '';
$progress = $_SESSION['upgrade_progress'];
$isComplete = $progress['lock_done'] ?? false;
$curVer = '1.5.1';
$verFile = ROOT_PATH . 'includes/version.php';
if (file_exists($verFile)) {
    $curVer = include $verFile;
}
$targetVer = '1.5.5';
$stepMap = [
    1 => '1.全站文件备份',
    2 => '2.数据库备份',
    3 => '3.清理旧目录',
    4 => '4.缓存配置+解压',
    5 => '5.恢复数据库配置',
    6 => '6.执行SQL升级',
    7 => '7.锁定升级'
];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>EnovelCms 升级程序</title>
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:"Microsoft Yahei",sans-serif;background:#f4f6f9;padding:20px;color:#333}
        .wrap{max-width:900px;margin:0 auto;background:#fff;border-radius:10px;padding:30px;box-shadow:0 2px 15px rgba(0,0,0,0.08)}
        h1{text-align:center;margin-bottom:8px}
        .ver-tip{text-align:center;color:#6b7280;margin-bottom:20px}
        .ip-info{text-align:right;font-size:13px;color:#999}
        .pre-err{background:#fee2e2;border:1px solid #fecdd3;padding:15px;border-radius:6px;margin-bottom:20px;color:#dc2626}
        .warning-block{background:#fffbeb;border-left:4px solid #f59e0b;padding:16px;margin:20px 0;border-radius:4px}
        .warning-block h4{color:#d97706;margin-bottom:8px}
        .success-box{background:#dcfce7;border:1px solid #bbf7d0;padding:20px;border-radius:6px;text-align:center;margin:20px 0}
        .error-box{background:#fee2e2;border:1px solid #fecdd3;padding:15px;border-radius:6px;margin:15px 0;color:#dc2626}
        .step-bar{display:flex;gap:6px;margin:25px 0}
        .step-item{flex:1;text-align:center;padding:10px 4px;border-bottom:3px solid #e5e7eb;font-size:13px;color:#9ca3af}
        .step-item.active{border-color:#3b82f6;color:#1d4ed8;font-weight:bold}
        .step-item.done{border-color:#10b981;color:#047857}
        .log-panel{background:#1e293b;color:#e2e8f0;padding:15px;border-radius:6px;max-height:420px;overflow-y:auto;font-family:Consolas,monospace;font-size:13px;white-space:pre-wrap;margin:20px 0}
        .log-success{color:#34d399}
        .log-error{color:#f87171}
        .log-info{color:#60a5fa}
        .log-warn{color:#fbbf24}
        .form-box{margin:20px 0;border:1px solid #ddd;padding:20px;border-radius:8px;background:#fafafa}
        .form-item{margin-bottom:16px}
        .form-item label{display:block;margin-bottom:6px;font-weight:500}
        .form-item input{width:100%;padding:11px 14px;border:1px solid #d1d5db;border-radius:6px;font-size:15px}
        .btn{padding:12px 24px;border:none;border-radius:6px;font-size:15px;cursor:pointer;transition:0.2s;display:inline-block;text-decoration:none}
        .btn-main{background:#10b981;color:#fff}
        .btn-main:hover{background:#059669}
        .btn-blue{background:#3b82f6;color:#fff}
        .btn-blue:hover{background:#2563eb}
        .btn-danger{background:#ef4444;color:#fff}
        .btn-danger:hover{background:#dc2626}
        .action-row{text-align:center;margin-top:25px;display:flex;gap:12px;justify-content:center;flex-wrap:wrap}
        .clear-tip{text-align:center;margin:10px 0;font-size:14px}
        .clear-tip a{color:#3b82f6}
    </style>
</head>
<body>
<div class="wrap">
    <div class="ip-info">IP：<?= htmlspecialchars($ip) ?></div>
    <h1>EnovelCms 一键升级程序</h1>
    <div class="ver-tip">
        当前版本：<strong><?= htmlspecialchars($curVer) ?></strong> &nbsp;→&nbsp; 目标：<strong style="color:#059669"><?= $targetVer ?></strong>
    </div>
    <div class="clear-tip">
        <a href="?clear=1">强制清空升级缓存（解决登录框不显示）</a>
    </div>

    <?php if (!$isComplete && !empty($preCheckErrors)): ?>
        <div class="pre-err">
            <strong>⚠️ 环境预检失败：</strong><br>
            <?php foreach ($preCheckErrors as $e): ?>
                · <?= htmlspecialchars($e) ?><br>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($isComplete): ?>
        <div class="success-box">
            <h2 style="color:#047857">🎉 升级全部完成！</h2>
            <p>站点已成功升级至 <?= $targetVer ?> 版本，数据完整保留</p>
            <div style="margin-top:20px;display:flex;gap:15px;justify-content:center;flex-wrap:wrap">
                <a href="/" class="btn btn-blue">访问网站首页</a>
                <a href="/admin/" class="btn btn-main">进入管理后台</a>
                <a href="?reset=1" class="btn btn-danger">重置升级（重新执行）</a>
            </div>
        </div>
        <?php exit; ?>
    <?php endif; ?>

    <div class="warning-block">
        <h4>⚠️ 升级注意事项</h4>
        <p>1. 升级前会自动备份全站文件+数据库，备份包存放于 <code>old/</code> 目录，请下载留存。</p>
        <p>2. 升级过程请勿关闭页面，若中断可点击“重置升级”重新执行。</p>
        <p>3. 锁定文件 <code>data/upgrade.lock</code> 存在时无法重复升级，删除后可重新运行。</p>
        <p>4. 若报错，查看 <code>data/upgrade_runtime.log</code> 获取详细日志。</p>
    </div>

    <?php if ($error): ?>
        <div class="error-box">❌ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($authorized): ?>
        <div class="step-bar">
            <?php foreach ($stepMap as $s => $label):
                $cls = '';
                if ($s == $currentStep) $cls = 'active';
                elseif ($s < $currentStep) $cls = 'done';
            ?>
                <div class="step-item <?= $cls ?>"><?= $label ?></div>
            <?php endforeach; ?>
        </div>
        <div class="log-panel" id="logBox">
            <?php if (empty($logs)): ?>
                <div class="log-info">⏳ 点击下方按钮开始执行升级步骤</div>
            <?php else:
                foreach ($logs as $msg):
                    $cls = 'log-info';
                    if (strpos($msg, '✅') !== false) $cls = 'log-success';
                    elseif (strpos($msg, '❌') !== false) $cls = 'log-error';
                    elseif (strpos($msg, '⚠️') !== false || strpos($msg, 'ℹ️') !== false) $cls = 'log-warn';
            ?>
                <div class="<?= $cls ?>"><?= htmlspecialchars($msg) ?></div>
            <?php endforeach; endif; ?>
        </div>
        <div class="action-row">
            <?php if ($currentStep >= 1 && $currentStep <= 7):
                $stepFinish = false;
                $map = [1=>'backup_done',2=>'db_backup_done',3=>'delete_done',4=>'extract_done',5=>'config_done',6=>'sql_done',7=>'lock_done'];
                $stepFinish = $progress[$map[$currentStep]] ?? false;
                $stepName = $stepMap[$currentStep];
                if (!$stepFinish):
            ?>
                <form method="post">
                    <input type="hidden" name="do_step" value="<?= $currentStep ?>">
                    <button type="submit" class="btn btn-main">执行当前步骤：<?= $stepName ?></button>
                </form>
                <?php else: ?>
                    <a href="upgrade.php" class="btn btn-blue">刷新页面进入下一步</a>
                <?php endif; ?>
                <a href="?reset=1" class="btn btn-danger">重置升级进度</a>
            <?php endif; ?>
        </div>
        <script>document.getElementById('logBox').scrollTop = document.getElementById('logBox').scrollHeight;</script>
    <?php else: ?>
        <div class="form-box">
            <h3>管理员登录验证</h3>
            <form method="post">
                <div class="form-item">
                    <label>管理员用户名</label>
                    <input type="text" name="admin_user" required autocomplete="off">
                </div>
                <div class="form-item">
                    <label>管理员密码</label>
                    <input type="password" name="admin_pass" required autocomplete="off">
                </div>
                <button type="submit" name="login" class="btn btn-main">登录并开始升级</button>
            </form>
        </div>
    <?php endif; ?>
</div>
</body>
</html>