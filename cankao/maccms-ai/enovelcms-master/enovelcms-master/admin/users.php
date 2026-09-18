<?php
/**
 * 用户管理
 */
require_once __DIR__ . '/../includes/config.php';

if (!isAdmin()) {
    redirect(BASE_URL . '/admin/login.php');
}

$users = $db->fetchAll($db->query("SELECT id, username, email, vip_level, gold, created_at FROM users ORDER BY id DESC"));
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>用户管理</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="/assets/fontawesome/css/all.min.css">
</head>
<body>
<div class="admin-container">
    <?php include 'sidebar.php'; ?>
    <div class="content">
        <h1>用户管理</h1>
        <table>
            <thead><tr><th>ID</th><th>用户名</th><th>邮箱</th><th>VIP</th><th>金币</th><th>注册时间</th><th>操作</th></tr></thead>
            <tbody>
            <?php foreach($users as $u): ?>
            <tr>
                <td><?= $u['id'] ?></td>
                <td><?= h($u['username']) ?></td>
                <td><?= h($u['email']) ?></td>
                <td><?= $u['vip_level'] ? '是' : '否' ?></td>
                <td><?= (int)$u['gold'] ?></td>
                <td><?= $u['created_at'] ?></td>
                <td><a href="edit_user.php?id=<?= $u['id'] ?>">编辑</a></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>