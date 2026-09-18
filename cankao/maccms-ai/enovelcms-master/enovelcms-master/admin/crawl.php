<?php
require_once __DIR__ . '/../includes/config.php';
if (!isAdmin()) redirect(BASE_URL . '/admin/login.php');
verify_admin_csrf();

// ========== 导出规则功能 ==========
if (isset($_GET['export']) && is_numeric($_GET['export'])) {
    $exportId = (int)$_GET['export'];
    $rule = $db->fetch($db->query("SELECT rule_key, name, site_url, charset, config, status FROM crawl_rules WHERE id=?", [$exportId]));
    if ($rule) {
        $rule['config'] = json_decode($rule['config'], true) ?: [];
        $exportData = [
            'rule_key' => $rule['rule_key'],
            'name' => $rule['name'],
            'site_url' => $rule['site_url'],
            'charset' => $rule['charset'],
            'config' => $rule['config'],
            'status' => $rule['status']
        ];
        $json = json_encode($exportData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $filename = preg_replace('/[^a-zA-Z0-9_\x{4e00}-\x{9fa5}]/u', '_', $rule['name']) . '_rule.json';
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($json));
        echo $json;
        exit;
    } else {
        die('规则不存在');
    }
}

$message = '';
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $db->query("DELETE FROM crawl_rules WHERE id=?", [$id]);
    redirect(BASE_URL . '/admin/crawl.php');
}

$rules = $db->fetchAll($db->query("SELECT * FROM crawl_rules ORDER BY id DESC"));
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>采集规则管理</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="/assets/fontawesome/css/all.min.css">
    <style>
        .rule-table th, .rule-table td { padding: 10px 12px; vertical-align: middle; }
        .rule-table code { background: #f1f5f9; padding: 2px 6px; border-radius: 6px; font-size: 0.8rem; }
        .btn-icon-group { display: flex; gap: 6px; flex-wrap: wrap; }
        .btn-icon-group a { white-space: nowrap; }
        @media (max-width: 768px) {
            .rule-table th, .rule-table td { padding: 6px 8px; font-size: 0.8rem; }
            .btn-icon-group a { font-size: 0.7rem; }
        }
    </style>
</head>
<body>
<div class="admin-container">
    <?php include 'sidebar.php'; ?>
    <div class="content">
        <h1><i class="fas fa-cloud-download-alt"></i> 采集规则管理</h1>
        <div style="margin-bottom:1rem;">
            <a href="crawl_edit.php" class="btn btn-primary"><i class="fas fa-plus"></i> 新增规则</a>
            <a href="crawl_import.php" class="btn btn-outline"><i class="fas fa-upload"></i> 上传规则文件</a>
            <a href="crawl_config.php" class="btn btn-outline"><i class="fas fa-train"></i> 采集器配置</a>
        </div>
        <div style="background: white; border-radius: 20px; border: 1px solid var(--gray-200); overflow-x: auto;">
            <table class="rule-table" style="width:100%; margin:0;">
                <thead>
                    <tr><th>ID</th><th>标识符</th><th>规则名称</th><th>网站</th><th>状态</th><th>操作</th></tr>
                </thead>
                <tbody>
                <?php foreach($rules as $r): ?>
                <tr>
                    <td><?= $r['id'] ?></td>
                    <td><code><?= h($r['rule_key']) ?></code></td>
                    <td><?= h($r['name']) ?></td>
                    <td><?= h($r['site_url']) ?></td>
                    <td><?= $r['status'] ? '启用' : '禁用' ?></td>
                    <td class="btn-icon-group">
                        <a href="crawl_edit.php?id=<?= $r['id'] ?>"><i class="fas fa-edit"></i> 编辑</a>
                        <a href="crawl_run.php?rule_id=<?= $r['id'] ?>" target="_blank"><i class="fas fa-play"></i> 采集</a>
                        <a href="single_crawl.php?rule_id=<?= $r['id'] ?>" target="_blank"><i class="fas fa-vial"></i> 单篇</a>
                        <a href="crawl_test.php?rule_id=<?= $r['id'] ?>" target="_blank"><i class="fas fa-flask"></i> 测试</a>
                        <a href="?export=<?= $r['id'] ?>"><i class="fas fa-download"></i> 导出</a>
                        <a href="?delete=<?= $r['id'] ?>" onclick="return confirm('确定删除？')" style="color:var(--danger);"><i class="fas fa-trash"></i> 删除</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>