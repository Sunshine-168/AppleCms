<?php
/**
 * 编辑用户（金币、VIP）
 */
require_once __DIR__ . '/../includes/config.php';

if (!isAdmin()) {
    redirect(BASE_URL . '/admin/login.php');
}
verify_admin_csrf();
$userId = (int)input('id', 0, 'GET');
$user = $db->fetch($db->query("SELECT * FROM users WHERE id=?", [$userId]));
if (!$user) {
    die('用户不存在');
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gold = (int)input('gold', 0, 'POST');
    $vip_level = (int)input('vip_level', 0, 'POST');
    $vip_expire = input('vip_expire', null, 'POST');
    $vip_expire = $vip_expire ?: null;
    
    $db->query("UPDATE users SET gold=?, vip_level=?, vip_expire=? WHERE id=?", 
        [$gold, $vip_level, $vip_expire, $userId]);
    $message = '更新成功';
    $user = $db->fetch($db->query("SELECT * FROM users WHERE id=?", [$userId]));
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>编辑用户</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="/assets/fontawesome/css/all.min.css">
</head>
<body>
<div class="admin-container">
    <?php include 'sidebar.php'; ?>
    <div class="content">
        <h1>编辑用户：<?= h($user['username']) ?></h1>
        <?php if ($message): ?><div class="alert"><?= h($message) ?></div><?php endif; ?>
        <form method="post">
            <?= csrf_field() ?>
            <div><label>金币</label><input type="number" name="gold" value="<?= $user['gold'] ?>"></div>
            <div><label>VIP等级</label>
                <select name="vip_level">
                    <option value="0" <?= $user['vip_level']==0?'selected':'' ?>>普通用户</option>
                    <option value="1" <?= $user['vip_level']==1?'selected':'' ?>>VIP会员</option>
                </select>
            </div>
            <div><label>VIP到期时间</label><input type="datetime-local" name="vip_expire" value="<?= date('Y-m-d\TH:i', strtotime($user['vip_expire'])) ?>"></div>
            <button type="submit">保存修改</button>
        </form>
        <a href="users.php">返回用户列表</a>
    </div>
</div>
</body>
</html>