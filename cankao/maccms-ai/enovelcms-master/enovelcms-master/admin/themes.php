<?php
/**
 * 主题管理
 */
require_once __DIR__ . '/../includes/config.php';

if (!isAdmin()) {
    redirect(BASE_URL . '/admin/login.php');
}
verify_admin_csrf();

$message = '';
$error = '';

// ---------- 获取主题列表 ----------
function getThemes() {
    $themes = [];
    $dir = ROOT_PATH . 'templates/';
    if (!is_dir($dir)) return $themes;
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item[0] === '.' || !is_dir($dir . $item)) continue;
        $xmlFile = $dir . $item . '/template.xml';
        if (!file_exists($xmlFile)) continue;
        $info = simplexml_load_file($xmlFile);
        if (!$info) continue;
        $themes[$item] = [
            'name' => (string)$info->name,
            'author' => (string)$info->author,
            'website' => (string)$info->website,
            'version' => (string)$info->version,
            'description' => (string)$info->description,
            'screenshot' => file_exists($dir . $item . '/screenshot.png') ? '/templates/' . $item . '/screenshot.png' : '',
        ];
    }
    return $themes;
}

$currentTheme = getActiveTheme();

// ---------- 切换主题 ----------
if (isset($_GET['activate'])) {
    $theme = $_GET['activate'];
    $all = getThemes();
    if (isset($all[$theme])) {
        $db->query("REPLACE INTO settings (`key`, `value`) VALUES ('active_theme', ?)", [$theme]);
        $message = '主题已切换到：' . h($theme);
        $currentTheme = $theme;
    } else {
        $error = '无效的主题';
    }
}

// ---------- 删除主题 ----------
if (isset($_GET['delete'])) {
    $theme = $_GET['delete'];
    if ($theme === $currentTheme) {
        $error = '不能删除当前正在使用的主题';
    } else {
        $all = getThemes();
        if (isset($all[$theme])) {
            $path = ROOT_PATH . 'templates/' . $theme;
            function delTree($dir) {
                $files = array_diff(scandir($dir), ['.', '..']);
                foreach ($files as $file) {
                    $path = $dir . '/' . $file;
                    is_dir($path) ? delTree($path) : unlink($path);
                }
                return rmdir($dir);
            }
            if (delTree($path)) {
                $message = '主题已删除：' . h($theme);
            } else {
                $error = '删除失败，请检查目录权限';
            }
        } else {
            $error = '主题不存在';
        }
    }
}

// ---------- 上传主题（ZIP） ----------
$uploadMsg = '';
$uploadError = '';

// 获取当前请求是否带 overwrite 参数
$overwrite = isset($_GET['overwrite']) && $_GET['overwrite'] === '1';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['theme_zip']) && $_FILES['theme_zip']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['theme_zip'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($ext !== 'zip') {
        $uploadError = '只允许上传 ZIP 格式的文件';
    } else {
        // 创建临时目录
        $tempDir = ROOT_PATH . 'data/temp/';
        if (!is_dir($tempDir)) mkdir($tempDir, 0755, true);

        $zipPath = $tempDir . uniqid() . '.zip';
        if (!move_uploaded_file($file['tmp_name'], $zipPath)) {
            $uploadError = 'ZIP 文件保存失败';
        } else {
            // 解压到临时提取目录
            $extractDir = $tempDir . 'extract_' . uniqid();
            mkdir($extractDir, 0755, true);

            $zip = new ZipArchive();
            if ($zip->open($zipPath) !== true) {
                $uploadError = '无法打开 ZIP 文件';
                @unlink($zipPath);
            } else {
                $zip->extractTo($extractDir);
                $zip->close();
                @unlink($zipPath); // 删除原始 ZIP

                // 扫描提取目录，寻找包含 template.xml 的子目录（第一层）
                $themeDir = null;
                $items = scandir($extractDir);
                foreach ($items as $item) {
                    if ($item === '.' || $item === '..') continue;
                    $fullPath = $extractDir . '/' . $item;
                    if (is_dir($fullPath) && file_exists($fullPath . '/template.xml')) {
                        $themeDir = $item;
                        break;
                    }
                }

                if (!$themeDir) {
                    // 如果根目录直接包含 template.xml，则将整个提取目录视为主题目录
                    if (file_exists($extractDir . '/template.xml')) {
                        $themeDir = '';
                    } else {
                        $uploadError = 'ZIP 中未找到有效的主题（缺少 template.xml）';
                        delTree($extractDir);
                    }
                }

                if ($themeDir !== null) {
                    // 确定主题目标路径
                    $targetDir = ROOT_PATH . 'templates/' . $themeDir;
                    $sourceDir = $themeDir === '' ? $extractDir : $extractDir . '/' . $themeDir;

                    // 检查是否已存在
                    if (is_dir($targetDir)) {
                        if ($overwrite) {
                            // 删除旧目录
                            delTree($targetDir);
                            // 移动新目录
                            if (rename($sourceDir, $targetDir)) {
                                $uploadMsg = '主题已覆盖更新：' . h($themeDir);
                                // 删除提取目录
                                if ($themeDir !== '') delTree($extractDir);
                                else {
                                    // 如果根目录直接是主题，则删除提取目录（已移动）
                                    // 但 rename 后原目录不存在了，无需额外删除
                                }
                                // 清理提取目录（如果为空）
                                @rmdir($extractDir);
                            } else {
                                $uploadError = '覆盖失败，请检查目录权限';
                            }
                        } else {
                            // 存在且未指定覆盖，提示用户
                            $uploadError = '主题 "' . $themeDir . '" 已存在，是否覆盖？';
                            // 存储临时目录路径以便后续覆盖
                            $_SESSION['pending_theme_zip'] = [
                                'source' => $sourceDir,
                                'target' => $targetDir,
                                'theme'  => $themeDir,
                                'extract_dir' => $extractDir,
                            ];
                            // 不删除提取目录，等待用户确认
                            // 同时保留源目录
                        }
                    } else {
                        // 直接移动
                        if (rename($sourceDir, $targetDir)) {
                            $uploadMsg = '主题安装成功：' . h($themeDir);
                            if ($themeDir !== '') delTree($extractDir);
                            else @rmdir($extractDir);
                        } else {
                            $uploadError = '移动主题目录失败，请检查权限';
                        }
                    }

                    // 如果上传成功或失败且没有待覆盖提示，清理提取目录
                    if (empty($uploadError) || strpos($uploadError, '是否覆盖') === false) {
                        if (isset($extractDir) && is_dir($extractDir)) {
                            delTree($extractDir);
                        }
                    }
                }
            }
        }
    }
}

// 如果用户确认覆盖（携带 overwrite=1 且存在会话数据）
if (isset($_GET['confirm_overwrite']) && $_GET['confirm_overwrite'] === '1' && isset($_SESSION['pending_theme_zip'])) {
    $pending = $_SESSION['pending_theme_zip'];
    if (isset($pending['source'], $pending['target'], $pending['extract_dir'])) {
        // 删除旧目录
        if (is_dir($pending['target'])) {
            delTree($pending['target']);
        }
        // 移动新目录
        if (rename($pending['source'], $pending['target'])) {
            $uploadMsg = '主题已覆盖更新：' . h($pending['theme']);
        } else {
            $uploadError = '覆盖失败，请检查权限';
        }
        // 清理提取目录
        if (is_dir($pending['extract_dir'])) {
            delTree($pending['extract_dir']);
        }
        unset($_SESSION['pending_theme_zip']);
    }
}

// 如果有待覆盖提示，显示给用户
$pendingOverwrite = isset($_SESSION['pending_theme_zip']) && !empty($_SESSION['pending_theme_zip']);

$themes = getThemes();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>主题管理</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="/assets/fontawesome/css/all.min.css">
    <style>
        /* 页面顶部 */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .page-header h1 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--gray-800);
        }
        .page-header .subtitle {
            color: var(--gray-500);
            font-size: 0.9rem;
        }
        .page-header .subtitle i {
            margin-right: 4px;
        }

        /* 上传区域 */
        .upload-section {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid #e9edf4;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .upload-section h3 {
            margin-top: 0;
            font-size: 1.1rem;
        }
        .upload-form {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 1rem;
        }
        .upload-form input[type="file"] {
            flex: 1;
            min-width: 200px;
        }
        .upload-form .btn {
            padding: 0.5rem 1.5rem;
        }

        /* 主题卡片网格 */
        .theme-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.8rem;
            margin-top: 1rem;
        }

        .theme-card {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid #e9edf4;
            overflow: hidden;
            transition: all 0.25s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
            display: flex;
            flex-direction: column;
        }
        .theme-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.07);
            border-color: #d0d7e5;
        }

        .theme-screenshot {
            width: 100%;
            aspect-ratio: 4 / 3;
            background: #f4f6fa;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
            border-bottom: 1px solid #e9edf4;
        }
        .theme-screenshot img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            background: #f4f6fa;
            transition: transform 0.3s ease;
        }
        .theme-card:hover .theme-screenshot img {
            transform: scale(1.02);
        }
        .theme-screenshot .no-image {
            color: #b0b9c9;
            font-size: 0.9rem;
            text-align: center;
            padding: 1rem;
        }
        .theme-screenshot .no-image i {
            font-size: 3rem;
            display: block;
            margin-bottom: 0.5rem;
            color: #cdd5e0;
        }

        .theme-current-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            background: #10b981;
            color: #fff;
            font-size: 0.7rem;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 30px;
            letter-spacing: 0.3px;
            box-shadow: 0 2px 6px rgba(16, 185, 129, 0.3);
            z-index: 2;
        }

        .theme-info {
            padding: 1.2rem 1.4rem 0.8rem;
            flex: 1;
        }
        .theme-info h3 {
            margin: 0 0 0.25rem 0;
            font-size: 1.15rem;
            font-weight: 600;
            color: #1e293b;
        }
        .theme-info .meta {
            font-size: 0.8rem;
            color: #7a879a;
            margin-bottom: 0.6rem;
        }
        .theme-info .meta a {
            color: #3b82f6;
            text-decoration: none;
        }
        .theme-info .meta a:hover {
            text-decoration: underline;
        }
        .theme-info .desc {
            font-size: 0.85rem;
            color: #475569;
            line-height: 1.5;
            margin: 0.6rem 0 0.2rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .theme-actions {
            padding: 0.8rem 1.4rem 1.2rem;
            border-top: 1px solid #eef2f6;
            display: flex;
            gap: 0.6rem;
            flex-wrap: wrap;
            background: #fafcff;
        }
        .theme-actions .btn {
            font-size: 0.8rem;
            padding: 0.35rem 1rem;
            border-radius: 30px;
            font-weight: 500;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-primary {
            background: #3b82f6;
            color: #fff;
        }
        .btn-primary:hover {
            background: #2563eb;
            box-shadow: 0 4px 10px rgba(59, 130, 246, 0.25);
            transform: translateY(-1px);
        }
        .btn-danger {
            background: #ef4444;
            color: #fff;
        }
        .btn-danger:hover {
            background: #dc2626;
            box-shadow: 0 4px 10px rgba(239, 68, 68, 0.25);
            transform: translateY(-1px);
        }
        .btn-outline {
            background: #f1f4f9;
            color: #475569;
            border: 1px solid #dce2ec;
        }
        .btn-outline:hover {
            background: #e5eaf0;
            border-color: #bcc6d4;
        }
        .btn-outline[disabled] {
            opacity: 0.6;
            cursor: not-allowed;
            pointer-events: none;
        }
        .btn-success {
            background: #10b981;
            color: #fff;
        }
        .btn-success:hover {
            background: #059669;
        }

        .theme-card.current-theme {
            border-color: #10b981;
            box-shadow: 0 4px 16px rgba(16, 185, 129, 0.10);
            background: #fafffe;
        }
        .theme-card.current-theme .theme-screenshot {
            border-bottom-color: #10b981;
        }

        .info-tip {
            background: #f8faff;
            border: 1px solid #e4eaf2;
            border-radius: 12px;
            padding: 0.8rem 1.2rem;
            margin-bottom: 1.5rem;
            color: #4b5a6e;
            font-size: 0.9rem;
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
        }
        .info-tip i {
            color: #3b82f6;
            margin-right: 6px;
        }
        .info-tip span {
            white-space: nowrap;
        }

        .overwrite-warning {
            background: #fef3c7;
            border: 1px solid #f59e0b;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
        }

        @media (max-width: 768px) {
            .theme-grid {
                grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
                gap: 1.2rem;
            }
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }
            .info-tip {
                flex-direction: column;
                gap: 0.5rem;
            }
            .upload-form {
                flex-direction: column;
                align-items: stretch;
            }
        }
        @media (max-width: 480px) {
            .theme-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<div class="admin-container">
    <?php include 'sidebar.php'; ?>
    <div class="content">
        <!-- 顶部 -->
        <div class="page-header">
            <h1><i class="fas fa-paint-brush" style="color: #3b82f6; margin-right: 10px;"></i>主题管理</h1>
            <div class="subtitle">
                <i class="fas fa-info-circle"></i> 当前使用：<strong><?= h($currentTheme) ?></strong>
            </div>
        </div>

        <!-- 提示信息 -->
        <div class="info-tip">
            <span><i class="fas fa-folder-open"></i> 主题目录：<code>templates/</code></span>
            <span><i class="fas fa-file-code"></i> 必须包含 <code>template.xml</code></span>
            <span><i class="fas fa-image"></i> 预览图：<code>screenshot.png</code> (推荐 400×300)</span>
            <span><i class="fas fa-file-archive"></i> 支持上传 <code>.zip</code> 安装包</span>
        </div>

        <!-- 消息显示 -->
        <?php if ($message): ?><div class="alert"><?= h($message) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="error"><?= h($error) ?></div><?php endif; ?>
        <?php if ($uploadMsg): ?><div class="alert"><?= h($uploadMsg) ?></div><?php endif; ?>
        <?php if ($uploadError): ?>
            <?php if (strpos($uploadError, '是否覆盖') !== false): ?>
                <div class="overwrite-warning">
                    <strong><i class="fas fa-exclamation-triangle"></i> <?= h($uploadError) ?></strong>
                    <br><br>
                    <a href="?confirm_overwrite=1" class="btn btn-success"><i class="fas fa-check"></i> 确定覆盖</a>
                    <a href="themes.php" class="btn btn-outline"><i class="fas fa-times"></i> 取消</a>
                    <span style="margin-left: 1rem; color: #6b7280; font-size: 0.9rem;">点击“确定覆盖”将删除旧主题并安装新版本</span>
                </div>
            <?php else: ?>
                <div class="error"><?= h($uploadError) ?></div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- 上传区域 -->
        <div class="upload-section">
            <h3><i class="fas fa-upload"></i> 上传主题 (ZIP)</h3>
            <form method="post" enctype="multipart/form-data" class="upload-form">
                <?= csrf_field() ?>
                <input type="file" name="theme_zip" accept=".zip" required>
                <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> 上传并安装</button>
            </form>
            <p style="margin-top: 0.5rem; font-size: 0.85rem; color: #6b7280;">
                <i class="fas fa-info-circle"></i> ZIP 包内应包含一个文件夹（文件夹名即主题名），该文件夹下须有 <code>template.xml</code> 及其他主题文件。
            </p>
        </div>

        <!-- 主题卡片列表 -->
        <div class="theme-grid">
            <?php foreach ($themes as $id => $info): ?>
                <?php $isCurrent = ($id === $currentTheme); ?>
                <div class="theme-card <?= $isCurrent ? 'current-theme' : '' ?>">
                    <div class="theme-screenshot">
                        <?php if ($info['screenshot']): ?>
                            <img src="<?= $info['screenshot'] ?>" alt="<?= h($info['name']) ?>">
                        <?php else: ?>
                            <div class="no-image">
                                <i class="fas fa-image"></i>
                                无预览图
                            </div>
                        <?php endif; ?>
                        <?php if ($isCurrent): ?>
                            <span class="theme-current-badge"><i class="fas fa-check-circle"></i> 当前</span>
                        <?php endif; ?>
                    </div>
                    <div class="theme-info">
                        <h3><?= h($info['name']) ?></h3>
                        <div class="meta">
                            <span><i class="fas fa-user"></i> <?= h($info['author']) ?></span>
                            <?php if ($info['website']): ?>
                                | <a href="<?= h($info['website']) ?>" target="_blank"><i class="fas fa-external-link-alt"></i> 官网</a>
                            <?php endif; ?>
                            <span style="margin-left: 10px;"><i class="fas fa-code-branch"></i> v<?= h($info['version']) ?></span>
                        </div>
                        <div class="desc"><?= h($info['description']) ?></div>
                    </div>
                    <div class="theme-actions">
                        <?php if ($isCurrent): ?>
                            <span class="btn btn-outline" disabled><i class="fas fa-check-circle" style="color: #10b981;"></i> 使用中</span>
                        <?php else: ?>
                            <a href="?activate=<?= urlencode($id) ?>" class="btn btn-primary" onclick="return confirm('确定启用“<?= h($info['name']) ?>”主题吗？')"><i class="fas fa-check"></i> 启用</a>
                            <a href="?delete=<?= urlencode($id) ?>" class="btn btn-danger" onclick="return confirm('确定删除“<?= h($info['name']) ?>”主题吗？\n此操作不可恢复！')"><i class="fas fa-trash-alt"></i> 删除</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
</body>
</html>