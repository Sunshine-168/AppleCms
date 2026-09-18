<?php
/**
 * 系统设置
 */
require_once __DIR__ . '/../includes/config.php';

if (!isAdmin()) {
    redirect(BASE_URL . '/admin/login.php');
}
verify_admin_csrf();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['site_logo']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (in_array($ext, $allowed)) {
        $filename = 'logo_' . time() . '.' . $ext;
        $target = ROOT_PATH . 'assets/uploads/logo/' . $filename;
        if (move_uploaded_file($_FILES['site_logo']['tmp_name'], $target)) {
            $_POST['site_logo'] = '/assets/uploads/logo/' . $filename;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST as $key => $value) {
        if ($key != 'submit' && $key != 'MAX_FILE_SIZE') {
            $value = input($key, '', 'POST');
            $db->query("REPLACE INTO settings (`key`, `value`) VALUES (?, ?)", [$key, $value]);
        }
    }
    $message = '设置已保存';
}

$settings = [];
$res = $db->query("SELECT * FROM settings");
while ($row = $db->fetch($res)) {
    $settings[$row['key']] = $row['value'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>系统设置</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="/assets/fontawesome/css/all.min.css">
    <style>
        .settings-group {
            background: white;
            border-radius: 20px;
            border: 1px solid var(--gray-200);
            margin-bottom: 1.8rem;
            overflow: hidden;
        }
        .settings-group-header {
            background: var(--gray-50);
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            font-weight: 600;
            font-size: 1.1rem;
        }
        .settings-group-body {
            padding: 1.5rem;
        }
        .form-row {
            display: grid;
            grid-template-columns: 200px 1fr;
            margin-bottom: 1.2rem;
            align-items: start;
        }
        .form-row label {
            font-weight: 500;
            color: var(--gray-700);
            padding-top: 0.5rem;
        }
        .form-row input, .form-row select, .form-row textarea {
            width: 100%;
            max-width: 450px;
            padding: 0.5rem 0.75rem;
            border: 1px solid var(--gray-300);
            border-radius: 8px;
        }
        .form-row textarea {
            max-width: 100%;
            height: 80px;
        }
        .help-text {
            font-size: 0.7rem;
            color: var(--gray-500);
            margin-top: 0.25rem;
        }
        .logo-preview {
            margin-top: 0.5rem;
            max-height: 60px;
        }
        hr {
            margin: 0;
            border-color: var(--gray-200);
        }
        @media (max-width: 700px) {
            .form-row {
                grid-template-columns: 1fr;
                gap: 0.3rem;
            }
        }
    </style>
</head>
<body>
<div class="admin-container">
    <?php include 'sidebar.php'; ?>
    <div class="content">
        <h1><i class="fas fa-cog"></i> 系统设置</h1>
        <?php if ($message): ?><div class="alert"><?= h($message) ?></div><?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <!-- 基本设置 -->
            <div class="settings-group">
                <div class="settings-group-header"><i class="fas fa-globe"></i> 基本设置</div>
                <div class="settings-group-body">
                    <div class="form-row">
                        <label>网站名称</label>
                        <input type="text" name="site_name" value="<?= h($settings['site_name'] ?? '') ?>">
                    </div>
                    <!-- 新增：注册登录模式 -->
                    <div class="form-row">
                        <label>注册登录模式</label>
                        <div>
                            <select name="register_mode">
                                <option value="normal" <?= ($settings['register_mode'] ?? 'normal') == 'normal' ? 'selected' : '' ?>>普通模式（仅用户名密码）</option>
                                <option value="email" <?= ($settings['register_mode'] ?? 'normal') == 'email' ? 'selected' : '' ?>>邮箱模式（需邮箱验证，支持邮箱登录）</option>
                            </select>
                            <div class="help-text">普通模式：注册只需用户名+密码；邮箱模式：注册需邮箱验证，登录可用用户名或邮箱。</div>
                        </div>
                    </div>
                    <div class="form-row">
                        <label>书库每页数量</label>
                        <div><input type="number" name="library_per_page" value="<?= h($settings['library_per_page'] ?? 20) ?>" min="1" max="100"><div class="help-text">书库列表中每页显示的小说数量</div></div>
                    </div>
                    <div class="form-row">
                        <label>统计代码</label>
                        <textarea name="analytics_code"><?= h($settings['analytics_code'] ?? '') ?></textarea>
                    </div>
                    <div class="form-row">
                        <label>网站Logo</label>
                        <div>
                            <input type="file" name="site_logo" accept="image/*">
                            <?php if (!empty($settings['site_logo'])): ?>
                                <div class="logo-preview"><img src="<?= h($settings['site_logo']) ?>" style="max-height: 60px;"></div>
                            <?php endif; ?>
                            <input type="hidden" name="site_logo" value="<?= h($settings['site_logo'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- 金币签到设置 -->
            <div class="settings-group">
                <div class="settings-group-header"><i class="fas fa-coins"></i> 金币签到设置</div>
                <div class="settings-group-body">
                    <div class="form-row"><label>基础签到金币</label><input type="number" name="sign_base_gold" value="<?= h($settings['sign_base_gold'] ?? 10) ?>"></div>
                    <div class="form-row"><label>连续签到奖励百分比(%)</label><input type="number" name="sign_continue_percent" value="<?= h($settings['sign_continue_percent'] ?? 5) ?>"></div>
                    <div class="form-row"><label>连续奖励最小金币</label><input type="number" name="sign_min_continue_bonus" value="<?= h($settings['sign_min_continue_bonus'] ?? 1) ?>"></div>
                    <div class="form-row"><label>连续奖励最大金币</label><input type="number" name="sign_max_continue_bonus" value="<?= h($settings['sign_max_continue_bonus'] ?? 100) ?>"></div>
                </div>
            </div>

            <!-- VIP设置 -->
            <div class="settings-group">
                <div class="settings-group-header"><i class="fas fa-crown"></i> VIP设置（按月）</div>
                <div class="settings-group-body">
                    <div class="form-row"><label>VIP人民币价格(元/月)</label><input type="number" name="vip_price_per_month" value="<?= h($settings['vip_price_per_month'] ?? 30) ?>" step="0.01"></div>
                    <div class="form-row"><label>VIP金币价格(金币/月)</label><input type="number" name="vip_gold_per_month" value="<?= h($settings['vip_gold_per_month'] ?? 3000) ?>"></div>
                </div>
            </div>

            <!-- 邮件服务器设置 -->
            <div class="settings-group">
                <div class="settings-group-header"><i class="fas fa-envelope"></i> 邮件服务器设置</div>
                <div class="settings-group-body">
                    <div class="form-row"><label>SMTP服务器</label><input type="text" name="smtp_host" value="<?= h($settings['smtp_host'] ?? '') ?>"></div>
                    <div class="form-row"><label>SMTP端口</label><input type="number" name="smtp_port" value="<?= h($settings['smtp_port'] ?? 25) ?>"></div>
                    <div class="form-row"><label>加密方式</label><select name="smtp_secure"><option value="">无</option><option value="ssl" <?= ($settings['smtp_secure'] ?? '') == 'ssl' ? 'selected' : '' ?>>SSL</option><option value="tls" <?= ($settings['smtp_secure'] ?? '') == 'tls' ? 'selected' : '' ?>>TLS</option></select></div>
                    <div class="form-row"><label>SMTP账号</label><input type="text" name="smtp_user" value="<?= h($settings['smtp_user'] ?? '') ?>"></div>
                    <div class="form-row"><label>SMTP密码</label><input type="password" name="smtp_pass" value="<?= h($settings['smtp_pass'] ?? '') ?>"></div>
                    <div class="form-row"><label>发件邮箱</label><input type="email" name="smtp_from" value="<?= h($settings['smtp_from'] ?? '') ?>"></div>
                    <div class="form-row"><label>发件人名称</label><input type="text" name="smtp_fromname" value="<?= h($settings['smtp_fromname'] ?? 'EnovelCms') ?>"></div>
                </div>
            </div>

            <div style="text-align: right;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> 保存所有设置</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>