<?php
/**
 * 管理员登录页面
 */
require_once __DIR__ . '/../includes/config.php';

if (isAdmin()) {
    redirect(BASE_URL . '/admin/index.php');
}
verify_admin_csrf();
$error = '';

$logFile = ROOT_PATH . 'data/admin/login.log';
$lockFile = ROOT_PATH . 'data/admin/login_lock.txt';

if (!is_dir(dirname($logFile))) {
    mkdir(dirname($logFile), 0755, true);
}

function isLocked() {
    global $lockFile;
    if (!file_exists($lockFile)) return false;
    $data = file_get_contents($lockFile);
    $lock = json_decode($data, true);
    if (!$lock || !isset($lock['until'])) return false;
    if (time() < $lock['until']) return true;
    @unlink($lockFile);
    return false;
}

function recordLoginAttempt($success) {
    global $logFile, $lockFile;
    $now = time();
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $entry = [
        'time' => date('Y-m-d H:i:s', $now),
        'ip'   => $ip,
        'success' => $success
    ];
    file_put_contents($logFile, json_encode($entry) . "\n", FILE_APPEND | LOCK_EX);

    if (!$success) {
        $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $cutoff = $now - 600;
        $failures = 0;
        foreach (array_reverse($lines) as $line) {
            $log = json_decode($line, true);
            if ($log && isset($log['time']) && strtotime($log['time']) > $cutoff && empty($log['success'])) {
                $failures++;
            } else {
                break;
            }
        }
        if ($failures >= 3) {
            $lockData = ['until' => $now + 1800, 'message' => 'Too many login failures'];
            file_put_contents($lockFile, json_encode($lockData), LOCK_EX);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isLocked()) {
        $error = '登录已锁定，请30分钟后再试';
    } else {
        $username = input('username', '', 'POST');
        $password = input('password', '', 'POST');
        $captcha = input('captcha', '', 'POST');

        if (!verify_captcha($captcha)) {
            $error = '验证码错误';
            clear_captcha();
            recordLoginAttempt(false);
        } else {
            clear_captcha();
            $admin = $db->fetch($db->query("SELECT * FROM admins WHERE username = ?", [$username]));
            if ($admin && password_verify($password, $admin['password'])) {
                session_regenerate_id(true);
                $_SESSION['admin'] = $admin['id'];
                recordLoginAttempt(true);
                redirect(BASE_URL . '/admin/index.php');
            } else {
                $error = $lang->get('login_failed');
                recordLoginAttempt(false);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>后台登录</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="/assets/fontawesome/css/all.min.css">
    <style>
        .captcha-row {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .captcha-row input {
            flex: 1;
        }
        .captcha-img {
            height: 40px;
            cursor: pointer;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>小说后台管理</h2>
        <?php if ($error): ?>
            <p class="error"><?= h($error) ?></p>
        <?php endif; ?>
        <form method="post">
            <?= csrf_field() ?>
            <input type="text" name="username" placeholder="用户名" required>
            <input type="password" name="password" placeholder="密码" required>
            <div class="captcha-row">
                <input type="text" name="captcha" placeholder="验证码" required>
                <img src="/api/captcha.php" class="captcha-img" onclick="this.src='/api/captcha.php?'+Math.random()" alt="验证码">
            </div>
            <button type="submit">登录</button>
        </form>
    </div>
</body>
</html>