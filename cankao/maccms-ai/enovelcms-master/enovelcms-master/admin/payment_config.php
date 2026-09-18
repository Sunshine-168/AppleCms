<?php
/**
 * 支付配置
 */
require_once __DIR__ . '/../includes/config.php';

if (!isAdmin()) {
    redirect(BASE_URL . '/admin/login.php');
}
verify_admin_csrf();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $api_url = input('yipay_api_url', '', 'POST');
    $pid = input('yipay_pid', '', 'POST');
    $key = input('yipay_key', '', 'POST');
    
    $db->query("REPLACE INTO settings (`key`, `value`) VALUES ('yipay_api_url', ?)", [$api_url]);
    $db->query("REPLACE INTO settings (`key`, `value`) VALUES ('yipay_pid', ?)", [$pid]);
    $db->query("REPLACE INTO settings (`key`, `value`) VALUES ('yipay_key', ?)", [$key]);
    $message = '支付配置已保存';
}

$settings = [];
$res = $db->query("SELECT * FROM settings WHERE `key` LIKE 'yipay_%'");
while ($row = $db->fetch($res)) {
    $settings[$row['key']] = $row['value'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>支付配置</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="/assets/fontawesome/css/all.min.css">
</head>
<body>
<div class="admin-container">
    <?php include 'sidebar.php'; ?>
    <div class="content">
        <h1>易支付配置</h1>
        <?php if ($message): ?><div class="alert"><?= h($message) ?></div><?php endif; ?>
        <form method="post">
            <?= csrf_field() ?>
            <div><label>网关地址</label><input type="text" name="yipay_api_url" value="<?= h($settings['yipay_api_url'] ?? '') ?>" placeholder="例如：https://www.wsepay.cn/"></div>
            <div><label>商户ID (PID)</label><input type="text" name="yipay_pid" value="<?= h($settings['yipay_pid'] ?? '') ?>"></div>
            <div><label>商户密钥 (KEY)</label><input type="text" name="yipay_key" value="<?= h($settings['yipay_key'] ?? '') ?>"></div>
            <button type="submit">保存</button>
        </form>
        <p>异步通知地址：<?= BASE_URL ?>/api/pay_notify.php</p>
        <p>同步跳转地址：<?= BASE_URL ?>/api/pay_return.php</p>
    </div>
</div>
</body>
</html>