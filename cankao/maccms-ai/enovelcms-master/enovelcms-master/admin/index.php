<?php
/**
 * 控制台
 */
require_once __DIR__ . '/../includes/config.php';

if (!isAdmin()) {
    redirect(BASE_URL . '/admin/login.php');
}
verify_admin_csrf();

$message = '';
$error = '';

// 处理密码修改
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $oldPassword = input('old_password', '', 'POST');
    $newPassword = input('new_password', '', 'POST');
    $confirmPassword = input('confirm_password', '', 'POST');
    $captcha = input('captcha', '', 'POST');

    if (!verify_captcha($captcha)) {
        $error = $lang->get('captcha_error');
        clear_captcha();
    } else {
        clear_captcha();
        $adminId = $_SESSION['admin'];
        $admin = $db->fetch($db->query("SELECT password FROM admins WHERE id = ?", [$adminId]));

        if (!password_verify($oldPassword, $admin['password'])) {
            $error = '原密码错误';
        } elseif (strlen($newPassword) < 6) {
            $error = '新密码至少6位';
        } elseif ($newPassword !== $confirmPassword) {
            $error = '两次输入的新密码不一致';
        } else {
            $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
            $db->query("UPDATE admins SET password = ? WHERE id = ?", [$hashed, $adminId]);
            $message = '密码修改成功，请重新登录';
            unset($_SESSION['admin']);
            redirect(BASE_URL . '/admin/login.php');
        }
    }
}

$currentVersion = ENOVELCMS_VERSION;

// ========== 服务端缓存：版本检测 ==========
$cacheDir = ROOT_PATH . 'data/cache/';
if (!is_dir($cacheDir)) mkdir($cacheDir, 0755, true);

$cacheFile = $cacheDir . 'version_cache.json';
$latestVersion = $currentVersion;
$releaseDate = '';
$changelogUrl = '';
$downloadUrl = '';
$updateAvailable = false;
$versionCheckFailed = false;

// 检查缓存是否有效（1小时 = 3600秒）
if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < 3600)) {
    $cache = json_decode(file_get_contents($cacheFile), true);
    if ($cache && isset($cache['latest_version'])) {
        $latestVersion = $cache['latest_version'];
        $releaseDate = $cache['release_date'] ?? '';
        $changelogUrl = $cache['changelog_url'] ?? '';
        $downloadUrl = $cache['download_url'] ?? '';
        $updateAvailable = version_compare($latestVersion, $currentVersion, '>');
    }
} else {
    // 缓存过期或不存在，请求官网
    $officialApi = 'https://www.enovelcms.cn/api/main/version.php';
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $officialApi,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 3,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT => 'EnovelCms-Client/' . $currentVersion,
    ]);
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && $result) {
        $data = json_decode($result, true);
        if (isset($data['code']) && $data['code'] === 1) {
            $latestVersion = $data['data']['latest_version'] ?? $currentVersion;
            $releaseDate = $data['data']['release_date'] ?? '';
            $changelogUrl = $data['data']['changelog_url'] ?? '';
            $downloadUrl = $data['data']['download_url'] ?? '';
            $updateAvailable = version_compare($latestVersion, $currentVersion, '>');
            // 写入缓存
            file_put_contents($cacheFile, json_encode([
                'latest_version' => $latestVersion,
                'release_date' => $releaseDate,
                'changelog_url' => $changelogUrl,
                'download_url' => $downloadUrl,
            ]));
        } else {
            $versionCheckFailed = true;
        }
    } else {
        $versionCheckFailed = true;
        // 如果缓存文件存在但已过期，仍然使用旧缓存（降级策略）
        if (file_exists($cacheFile)) {
            $cache = json_decode(file_get_contents($cacheFile), true);
            if ($cache && isset($cache['latest_version'])) {
                $latestVersion = $cache['latest_version'];
                $releaseDate = $cache['release_date'] ?? '';
                $changelogUrl = $cache['changelog_url'] ?? '';
                $downloadUrl = $cache['download_url'] ?? '';
                $updateAvailable = version_compare($latestVersion, $currentVersion, '>');
                $versionCheckFailed = false; // 使用缓存视为成功
            }
        }
    }
}

// ========== 服务端缓存：官方公告 ==========
$noticeCacheFile = $cacheDir . 'notice_cache.json';
$notices = [];
$noticeFailed = false;

if (file_exists($noticeCacheFile) && (time() - filemtime($noticeCacheFile) < 3600)) {
    $cache = json_decode(file_get_contents($noticeCacheFile), true);
    if (is_array($cache)) {
        $notices = $cache;
    }
} else {
    $noticeApi = 'https://www.enovelcms.cn/api/crawl/notice.php';
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $noticeApi,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 3,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT => 'EnovelCms-Client/' . $currentVersion,
    ]);
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && $result) {
        $data = json_decode($result, true);
        if (is_array($data) && !empty($data)) {
            $notices = $data;
            file_put_contents($noticeCacheFile, json_encode($data));
        } else {
            $noticeFailed = true;
        }
    } else {
        $noticeFailed = true;
        // 使用旧缓存
        if (file_exists($noticeCacheFile)) {
            $cache = json_decode(file_get_contents($noticeCacheFile), true);
            if (is_array($cache)) {
                $notices = $cache;
                $noticeFailed = false;
            }
        }
    }
}

// 统计信息
$novelCount = $db->fetch($db->query("SELECT COUNT(*) as cnt FROM novels"))['cnt'];
$userCount = $db->fetch($db->query("SELECT COUNT(*) as cnt FROM users"))['cnt'];
$chapterCount = $db->fetch($db->query("SELECT COUNT(*) as cnt FROM chapters"))['cnt'];
$blockCount = $db->fetch($db->query("SELECT COUNT(*) as cnt FROM diy_blocks"))['cnt'];
$totalViews = $db->fetch($db->query("SELECT SUM(views) as total FROM novels"))['total'] ?? 0;
$totalFavorites = $db->fetch($db->query("SELECT SUM(favorites) as total FROM novels"))['total'] ?? 0;
$todaySignCount = $db->fetch($db->query("SELECT COUNT(DISTINCT user_id) as cnt FROM gold_logs WHERE type = 'sign' AND DATE(created_at) = CURDATE()"))['cnt'] ?? 0;
$todaySignGold = $db->fetch($db->query("SELECT SUM(gold_change) as total FROM gold_logs WHERE type = 'sign' AND DATE(created_at) = CURDATE()"))['total'] ?? 0;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>管理后台</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="/assets/fontawesome/css/all.min.css">
    <style>
        .stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .stat-box {
            background: white;
            border-radius: 20px;
            padding: 1rem;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            border: 1px solid var(--gray-200);
            transition: transform 0.2s;
        }
        .stat-box:hover { transform: translateY(-2px); }
        .stat-number { font-size: 1.8rem; font-weight: bold; color: var(--primary); }
        .stat-label { font-size: 0.8rem; color: var(--gray-600); }
        @media (max-width: 768px) {
            .stats { grid-template-columns: repeat(2, 1fr); gap: 0.8rem; }
            .stat-number { font-size: 1.4rem; }
        }
        @media (max-width: 480px) { .stats { grid-template-columns: 1fr; gap: 0.6rem; } }

        .version-info {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 2rem;
            padding: 0.8rem 1.2rem;
            background: #f8faff;
            border-radius: 12px;
            border: 1px solid #e4eaf2;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
        }
        .version-info .version-current { color: #1e293b; }
        .version-info .version-latest { color: #475569; }
        .version-info .version-latest a {
            color: #3b82f6;
            text-decoration: none;
            margin-left: 0.5rem;
        }
        .version-info .version-latest a:hover { text-decoration: underline; }

        .btn-primary.btn-sm {
            background: var(--primary);
            color: #ffffff !important;
            padding: 0.2rem 0.8rem;
            border-radius: 30px;
            font-size: 0.75rem;
            text-decoration: none;
            display: inline-block;
            border: none;
            cursor: pointer;
        }
        .btn-primary.btn-sm:hover {
            background: var(--primary-dark);
            color: #ffffff !important;
            text-decoration: none;
        }

        .dashboard-two-col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-top: 1.5rem;
        }
        .admin-card {
            background: white;
            border-radius: 20px;
            border: 1px solid var(--gray-200);
            overflow: hidden;
        }
        .admin-card-header {
            background: var(--gray-50);
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            font-weight: 600;
        }
        .admin-card-body { padding: 1.5rem; }

        .form-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        .form-row label { width: 100px; font-weight: 500; }
        .form-row input {
            flex: 1;
            min-width: 200px;
            padding: 0.5rem;
            border: 1px solid var(--gray-300);
            border-radius: 8px;
        }
        .captcha-row {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .captcha-img { height: 38px; cursor: pointer; border-radius: 6px; }
        .btn-primary {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.5rem 1.2rem;
            border-radius: 30px;
            cursor: pointer;
        }
        .alert { background: #d4edda; color: #155724; padding: 0.7rem; border-radius: 10px; margin-bottom: 1rem; }
        .error { background: #f8d7da; color: #721c24; padding: 0.7rem; border-radius: 10px; margin-bottom: 1rem; }
        @media (max-width: 640px) {
            .form-row { flex-direction: column; align-items: flex-start; }
            .form-row label { width: auto; }
            .form-row input { width: 100%; min-width: auto; }
        }

        .notice-list {
            max-height: 300px;
            overflow-y: auto;
            padding-right: 6px;
        }
        .notice-list::-webkit-scrollbar { width: 4px; }
        .notice-list::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 4px; }
        .notice-item {
            padding: 0.8rem 0.5rem;
            border-bottom: 1px solid var(--gray-200);
            font-size: 0.9rem;
            line-height: 1.5;
        }
        .notice-item:last-child { border-bottom: none; }
        .notice-item a { color: var(--primary); text-decoration: none; }
        .notice-item a:hover { text-decoration: underline; }
        .notice-empty { color: var(--gray-400); text-align: center; padding: 2rem 0; }
        .notice-loading { text-align: center; padding: 1rem 0; color: var(--gray-500); }

        @media (max-width: 900px) {
            .dashboard-two-col { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>
        <div class="content">
            <h1><i class="fas fa-tachometer-alt"></i> 控制台</h1>

            <!-- 版本信息条 -->
            <div class="version-info">
                <span class="version-current"><i class="fas fa-code-branch"></i> 当前版本：<strong><?= $currentVersion ?></strong></span>
                <span class="version-latest">
                    <?php if ($updateAvailable): ?>
                        <span style="color: #f59e0b;">
                            <i class="fas fa-exclamation-triangle"></i> 发现新版本：<strong><?= $latestVersion ?></strong>
                            <?php if ($releaseDate): ?>（发布于 <?= $releaseDate ?>）<?php endif; ?>
                            <?php if ($changelogUrl): ?>
                                <a href="<?= $changelogUrl ?>" target="_blank">查看更新日志</a>
                            <?php endif; ?>
                            <?php if ($downloadUrl): ?>
                                <a href="<?= $downloadUrl ?>" target="_blank" class="btn btn-primary btn-sm">立即下载</a>
                            <?php endif; ?>
                        </span>
                    <?php else: ?>
                        <span style="color: #10b981;"><i class="fas fa-check-circle"></i> 已是最新版本</span>
                        <?php if ($versionCheckFailed): ?>
                            <span style="color: #94a3b8; margin-left: 1rem;"><i class="fas fa-exclamation-circle"></i> 无法连接更新服务器</span>
                        <?php endif; ?>
                    <?php endif; ?>
                </span>
            </div>

            <?php if ($message): ?>
                <div class="alert"><?= h($message) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="error"><?= h($error) ?></div>
            <?php endif; ?>

            <!-- 统计卡片 -->
            <div class="stats">
                <div class="stat-box">
                    <div class="stat-number"><?= number_format($novelCount) ?></div>
                    <div class="stat-label">小说数量</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number"><?= number_format($userCount) ?></div>
                    <div class="stat-label">用户数量</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number"><?= number_format($chapterCount) ?></div>
                    <div class="stat-label">章节数量</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number"><?= number_format($blockCount) ?></div>
                    <div class="stat-label">Diy缓存块</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number"><?= number_format($totalViews) ?></div>
                    <div class="stat-label">总阅读次数</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number"><?= number_format($totalFavorites) ?></div>
                    <div class="stat-label">总收藏数</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number"><?= number_format($todaySignCount) ?></div>
                    <div class="stat-label">今日签到人次</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number"><?= number_format($todaySignGold) ?></div>
                    <div class="stat-label">今日赠送金币</div>
                </div>
            </div>

            <div class="dashboard-two-col">
                <!-- 修改管理员密码 -->
                <div class="admin-card">
                    <div class="admin-card-header"><i class="fas fa-key"></i> 修改管理员密码</div>
                    <div class="admin-card-body">
                        <form method="post">
                            <?= csrf_field() ?>
                            <div class="form-row">
                                <label>原密码</label>
                                <input type="password" name="old_password" required>
                            </div>
                            <div class="form-row">
                                <label>新密码</label>
                                <input type="password" name="new_password" required>
                            </div>
                            <div class="form-row">
                                <label>确认新密码</label>
                                <input type="password" name="confirm_password" required>
                            </div>
                            <div class="form-row">
                                <label>验证码</label>
                                <div class="captcha-row">
                                    <input type="text" name="captcha" required style="width:120px;">
                                    <img src="/api/captcha.php" class="captcha-img" onclick="this.src='/api/captcha.php?'+Math.random()" alt="验证码">
                                </div>
                            </div>
                            <div class="form-row">
                                <label></label>
                                <button type="submit" name="change_password" class="btn-primary"><i class="fas fa-save"></i> 修改密码</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- 官方公告 -->
                <div class="admin-card">
                    <div class="admin-card-header"><i class="fas fa-bullhorn"></i> 官方公告</div>
                    <div class="admin-card-body">
                        <div id="noticeContainer">
                            <?php if (!empty($notices)): ?>
                                <div class="notice-list">
                                    <?php foreach ($notices as $item): ?>
                                        <?php 
                                            $text = $item['text'] ?? '';
                                            $url = $item['url'] ?? '';
                                            $content = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
                                            if ($url) {
                                                $content = '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" target="_blank">' . $content . '</a>';
                                            }
                                        ?>
                                        <div class="notice-item"><?= $content ?></div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="notice-empty"><i class="fas fa-exclamation-circle"></i> <?= $noticeFailed ? '公告加载失败' : '暂无公告' ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>