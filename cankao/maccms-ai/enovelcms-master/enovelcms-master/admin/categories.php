<?php
/**
 * 分类管理
 */
require_once __DIR__ . '/../includes/config.php';

if (!isAdmin()) {
    redirect(BASE_URL . '/admin/login.php');
}
verify_admin_csrf();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)input('id', 0, 'POST');
    $name = input('name', '', 'POST');
    $sort = (int)input('sort', 0, 'POST');
    
    if ($id) {
        $db->query("UPDATE categories SET name=?, sort=? WHERE id=?", [$name, $sort, $id]);
        $message = '更新成功';
    } else {
        $db->query("INSERT INTO categories (name, sort) VALUES (?, ?)", [$name, $sort]);
        $message = '添加成功';
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $db->query("DELETE FROM categories WHERE id=?", [$id]);
    redirect(BASE_URL . '/admin/categories.php');
}

$categories = $db->fetchAll($db->query("SELECT * FROM categories ORDER BY sort"));
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>分类管理</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="/assets/fontawesome/css/all.min.css">
</head>
<body>
<div class="admin-container">
    <?php include 'sidebar.php'; ?>
    <div class="content">
        <h1>分类管理</h1>
        <?php if ($message): ?><div class="alert"><?= h($message) ?></div><?php endif; ?>
        <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="id" id="cat_id">
            <div><label>分类名称</label><input type="text" name="name" id="name" required></div>
            <div><label>排序</label><input type="number" name="sort" id="sort" value="0"></div>
            <button type="submit">保存</button>
        </form>
        <table>
            <thead><tr><th>ID</th><th>名称</th><th>排序</th><th>操作</th></tr></thead>
            <tbody>
            <?php foreach($categories as $c): ?>
            <tr>
                <td><?= $c['id'] ?></td>
                <td><?= h($c['name']) ?></td>
                <td><?= $c['sort'] ?></td>
                <td>
                    <a href="#" onclick="editCat(<?= htmlspecialchars(json_encode($c)) ?>)">编辑</a>
                    <a href="?delete=<?= $c['id'] ?>" onclick="return confirm('确定删除？')">删除</a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<script>
function editCat(cat) {
    document.getElementById('cat_id').value = cat.id;
    document.getElementById('name').value = cat.name;
    document.getElementById('sort').value = cat.sort;
}
</script>
</body>
</html>