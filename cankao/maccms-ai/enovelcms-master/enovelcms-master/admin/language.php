<?php
/**
 * 语言包管理
 */
require_once __DIR__ . '/../includes/config.php';

if (!isAdmin()) {
    redirect(BASE_URL . '/admin/login.php');
}
verify_admin_csrf();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_lang'])) {
    $langCode = trim(input('lang_code', '', 'POST'));
    $langName = trim(input('lang_name', '', 'POST'));
    if (empty($langCode) || empty($langName)) {
        $error = '语言代码和名称不能为空';
    } elseif (!preg_match('/^[a-z]{2}-[a-z]{2}$/', $langCode)) {
        $error = '语言代码格式错误，应为 xx-xx';
    } elseif (isset($_FILES['lang_file']) && $_FILES['lang_file']['error'] === UPLOAD_ERR_OK) {
        $result = installLanguagePackage($_FILES['lang_file'], $langCode, $langName);
        if ($result['success']) {
            $message = $result['message'];
        } else {
            $error = $result['message'];
        }
    } else {
        $error = '请上传语言文件';
    }
}

if (isset($_GET['delete_lang'])) {
    $code = $_GET['delete_lang'];
    $result = uninstallLanguagePackage($code);
    if ($result['success']) {
        $message = $result['message'];
    } else {
        $error = $result['message'];
    }
}

if (isset($_POST['set_default_lang'])) {
    $defaultLang = input('default_lang', '', 'POST');
    $available = getAvailableLanguages();
    if (isset($available[$defaultLang])) {
        $db->query("REPLACE INTO settings (`key`, `value`) VALUES ('site_lang', ?)", [$defaultLang]);
        $GLOBALS['lang']->set($defaultLang);
        $message = '默认语言已更新';
    } else {
        $error = '无效的语言代码';
    }
}

$availableLangs = getAvailableLanguages();
$currentDefault = getSetting('site_lang', $db);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>语言管理</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="/assets/fontawesome/css/all.min.css">
    <style>
        .lang-card {
            background: white;
            border-radius: 20px;
            border: 1px solid var(--gray-200);
            margin-bottom: 1.8rem;
            overflow: hidden;
        }
        .lang-card-header {
            background: var(--gray-50);
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            font-weight: 600;
        }
        .lang-card-body {
            padding: 1.5rem;
        }
        .form-inline {
            display: flex;
            gap: 1rem;
            align-items: flex-end;
            flex-wrap: wrap;
        }
        .current-badge {
            background: var(--success);
            color: white;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 0.7rem;
        }
    </style>
</head>
<body>
<div class="admin-container">
    <?php include 'sidebar.php'; ?>
    <div class="content">
        <h1><i class="fas fa-globe"></i> 语言管理</h1>
        <?php if ($message): ?><div class="alert"><?= h($message) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="error"><?= h($error) ?></div><?php endif; ?>

        <div class="lang-card">
            <div class="lang-card-header"><i class="fas fa-flag"></i> 设置默认语言</div>
            <div class="lang-card-body">
                <form method="post" class="form-inline">
                    <?= csrf_field() ?>   <!-- 添加 CSRF 令牌 -->
                    <select name="default_lang" style="padding:0.5rem 1rem; border-radius:8px; min-width:200px;">
                        <?php foreach ($availableLangs as $code => $name): ?>
                            <option value="<?= $code ?>" <?= ($currentDefault == $code) ? 'selected' : '' ?>><?= h($name) ?> (<?= $code ?>)</option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" name="set_default_lang" class="btn btn-primary"><i class="fas fa-check"></i> 设为默认</button>
                </form>
            </div>
        </div>

        <div class="lang-card">
            <div class="lang-card-header"><i class="fas fa-list"></i> 已安装语言包</div>
            <div class="lang-card-body" style="padding:0;">
                <table style="width:100%; margin:0;">
                    <thead><tr><th>语言代码</th><th>显示名称</th><th>状态</th><th>操作</th></tr></thead>
                    <tbody>
                    <?php foreach ($availableLangs as $code => $name): ?>
                        <tr>
                            <td><?= h($code) ?></td>
                            <td><?= h($name) ?></td>
                            <td><?= ($currentDefault == $code) ? '<span class="current-badge">当前默认</span>' : '' ?></td>
                            <td>
                                <?php if ($code != $currentDefault): ?>
                                    <a href="?delete_lang=<?= urlencode($code) ?>" onclick="return confirm('确定删除此语言包？')" style="color:var(--danger);"><i class="fas fa-trash"></i> 删除</a>
                                <?php else: ?>
                                    <span style="color:var(--gray-400);">默认不可删除</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="lang-card">
            <div class="lang-card-header"><i class="fas fa-upload"></i> 上传新语言包</div>
            <div class="lang-card-body">
                <form method="post" enctype="multipart/form-data">
                    <?= csrf_field() ?>   <!-- 添加 CSRF 令牌 -->
                    <div class="form-row" style="margin-bottom:1rem;">
                        <label style="width:120px;">语言代码</label>
                        <input type="text" name="lang_code" required pattern="[a-z]{2}-[a-z]{2}" style="width:250px;">
                    </div>
                    <div class="form-row" style="margin-bottom:1rem;">
                        <label style="width:120px;">显示名称</label>
                        <input type="text" name="lang_name" required style="width:250px;">
                    </div>
                    <div class="form-row" style="margin-bottom:1rem;">
                        <label style="width:120px;">语言文件(.php)</label>
                        <input type="file" name="lang_file" accept=".php" required>
                    </div>
                    <button type="submit" name="upload_lang" class="btn btn-primary"><i class="fas fa-upload"></i> 上传并安装</button>
                </form>
                <p class="help-text" style="margin-top:1rem;">语言文件应返回键值对数组，参考 includes/lang/zh-cn.php</p>
            </div>
        </div>
    </div>
</div>
</body>
</html>