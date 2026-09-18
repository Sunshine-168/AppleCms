<?php
/**
 * admin/volumes.php - 分卷管理（支持分页）
 */
require_once __DIR__ . '/../includes/config.php';

if (!isAdmin()) {
    redirect(BASE_URL . '/admin/login.php');
}
verify_admin_csrf();

$novel_id = (int)input('novel_id', 0, 'GET');
if (!$novel_id) {
    die('请先选择小说');
}

$novel = $db->fetch($db->query("SELECT * FROM novels WHERE id=?", [$novel_id]));
if (!$novel) {
    die('小说不存在');
}

$message = '';

// 处理添加/编辑
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)input('id', 0, 'POST');
    $title = trim(input('title', '', 'POST'));
    $sort = (int)input('sort', 0, 'POST');
    if (empty($title)) {
        $message = '分卷名称不能为空';
    } else {
        if ($id) {
            $db->query("UPDATE volumes SET title=?, sort=? WHERE id=? AND novel_id=?", [$title, $sort, $id, $novel_id]);
            $message = '更新成功';
        } else {
            $db->query("INSERT INTO volumes (novel_id, title, sort) VALUES (?,?,?)", [$novel_id, $title, $sort]);
            $message = '添加成功';
        }
    }
}

// 处理删除
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    // 检查该卷下是否有章节，如果有，先将章节移到默认卷或者禁止删除
    $chaptersCount = $db->fetch($db->query("SELECT COUNT(*) as cnt FROM chapters WHERE volume_id=?", [$id]))['cnt'];
    if ($chaptersCount > 0) {
        $message = '该分卷下还有章节，请先移动或删除章节后再删除分卷';
    } else {
        $db->query("DELETE FROM volumes WHERE id=? AND novel_id=?", [$id, $novel_id]);
        $message = '删除成功';
    }
    redirect(BASE_URL . "/admin/volumes.php?novel_id=$novel_id");
}

// 分页
$page = max(1, (int)input('page', 1, 'GET'));
$limit = 20;
$offset = ($page - 1) * $limit;
$totalVolumes = $db->fetch($db->query("SELECT COUNT(*) as cnt FROM volumes WHERE novel_id=?", [$novel_id]))['cnt'];
$totalPages = ceil($totalVolumes / $limit);
$volumes = $db->fetchAll($db->query("SELECT * FROM volumes WHERE novel_id=? ORDER BY sort LIMIT $offset, $limit", [$novel_id]));
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>分卷管理 - <?= h($novel['title']) ?></title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="/assets/fontawesome/css/all.min.css">
    <style>
        .vol-layout {
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 1.5rem;
        }
        .form-card, .list-card {
            background: white;
            border-radius: 20px;
            border: 1px solid var(--gray-200);
            overflow: hidden;
        }
        .card-header {
            background: var(--gray-50);
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            font-weight: 600;
        }
        .card-body {
            padding: 1.5rem;
        }
        .form-field {
            margin-bottom: 1rem;
        }
        .form-field label {
            display: block;
            font-weight: 600;
            font-size: 0.8rem;
            margin-bottom: 0.25rem;
        }
        .form-field input {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid var(--gray-300);
            border-radius: 8px;
        }
        .pagination {
            margin-top: 1rem;
            text-align: center;
        }
        .pagination a {
            display: inline-block;
            padding: 0.3rem 0.7rem;
            margin: 0 2px;
            border: 1px solid var(--gray-300);
            border-radius: 6px;
            text-decoration: none;
            color: var(--gray-700);
        }
        .pagination a.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        @media (max-width: 900px) {
            .vol-layout { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="admin-container">
    <?php include 'sidebar.php'; ?>
    <div class="content">
        <h1><i class="fas fa-layer-group"></i> 分卷管理：<?= h($novel['title']) ?></h1>
        <?php if ($message): ?><div class="alert"><?= h($message) ?></div><?php endif; ?>

        <div class="vol-layout">
            <div class="form-card">
                <div class="card-header" id="formTitle">添加分卷</div>
                <div class="card-body">
                    <form method="post" id="volForm">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" id="vol_id">
                        <div class="form-field">
                            <label>分卷名称</label>
                            <input type="text" name="title" id="title" required>
                        </div>
                        <div class="form-field">
                            <label>排序</label>
                            <input type="number" name="sort" id="sort" value="0">
                        </div>
                        <div style="text-align: right;">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> 保存</button>
                            <button type="button" id="cancelVolEdit" class="btn btn-outline" style="display:none;">取消</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="list-card">
                <div class="card-header">现有分卷（第<?= $page ?>页，共<?= $totalPages ?>页）</div>
                <div class="card-body" style="padding:0;">
                    <table style="width:100%; margin:0;">
                        <thead>
                            <tr><th>ID</th><th>名称</th><th>排序</th><th>操作</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach($volumes as $v): ?>
                        <tr>
                            <td><?= $v['id'] ?></td>
                            <td><?= h($v['title']) ?></td>
                            <td><?= $v['sort'] ?></td>
                            <td>
                                <a href="#" onclick="editVol(<?= htmlspecialchars(json_encode($v)) ?>)"><i class="fas fa-edit"></i> 编辑</a>
                                <a href="?novel_id=<?= $novel_id ?>&delete=<?= $v['id'] ?>" onclick="return confirm('确定删除？该分卷下的章节将被遗弃，建议先移动章节。')" style="color:var(--danger);"><i class="fas fa-trash"></i> 删除</a>
                             </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php if ($totalPages > 1): ?>
                <div class="card-body">
                    <div class="pagination">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="?novel_id=<?= $novel_id ?>&page=<?= $i ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
                        <?php endfor; ?>
                    </div>
                </div>
                <?php endif; ?>
                <div class="card-body" style="border-top:1px solid var(--gray-200); text-align:center;">
                    <a href="novels.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> 返回小说列表</a>
                    <a href="chapters.php?novel_id=<?= $novel_id ?>" class="btn btn-outline"><i class="fas fa-list"></i> 章节管理</a>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
function editVol(vol) {
    document.getElementById('vol_id').value = vol.id;
    document.getElementById('title').value = vol.title;
    document.getElementById('sort').value = vol.sort;
    document.getElementById('formTitle').innerText = '编辑分卷';
    document.getElementById('cancelVolEdit').style.display = 'inline-block';
}
document.getElementById('cancelVolEdit')?.addEventListener('click', function() {
    document.getElementById('volForm').reset();
    document.getElementById('vol_id').value = '';
    document.getElementById('formTitle').innerText = '添加分卷';
    this.style.display = 'none';
});
</script>
</body>
</html>