<?php
/**
 * admin/novels.php - 小说管理（增加分页）
 */
require_once __DIR__ . '/../includes/config.php';

if (!isAdmin()) {
    redirect(BASE_URL . '/admin/login.php');
}
verify_admin_csrf();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)input('id', 0, 'POST');
    $title = input('title', '', 'POST');
    $author = input('author', '', 'POST');
    $category_id = (int)input('category_id', 0, 'POST');
    $description = input('description', '', 'POST');
    $status = (int)input('status', 0, 'POST');

    $cover = input('old_cover', '', 'POST');
    if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_ext)) {
            die('Invalid file type');
        }
        // 使用 getimagesize 检测图片内容（无需 fileinfo 扩展）
        $imageInfo = @getimagesize($_FILES['cover']['tmp_name']);
        if ($imageInfo === false) {
            die('Invalid image content');
        }
        $allowed_mime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($imageInfo['mime'], $allowed_mime)) {
            die('Invalid image content');
        }
        $cover = 'data/covers/' . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['cover']['tmp_name'], ROOT_PATH . $cover);
    }

    if ($id) {
        $db->query("UPDATE novels SET title=?, author=?, category_id=?, cover=?, description=?, status=? WHERE id=?",
            [$title, $author, $category_id, $cover, $description, $status, $id]);
        $message = '更新成功';
        updateNovelTotalWords($id);
    } else {
        $db->query("INSERT INTO novels (title, author, category_id, cover, description, status) VALUES (?,?,?,?,?,?)",
            [$title, $author, $category_id, $cover, $description, $status]);
        $newId = $db->lastInsertId();
        $message = '添加成功';
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $chapters = $db->fetchAll($db->query("SELECT file_path FROM chapters WHERE novel_id=?", [$id]));
    foreach ($chapters as $ch) {
        @unlink(ROOT_PATH . $ch['file_path']);
    }
    $db->query("DELETE FROM chapters WHERE novel_id=?", [$id]);
    $db->query("DELETE FROM volumes WHERE novel_id=?", [$id]);
    $db->query("DELETE FROM novels WHERE id=?", [$id]);
    redirect(BASE_URL . '/admin/novels.php');
}

if (isset($_GET['sync_words'])) {
    $novels = $db->fetchAll($db->query("SELECT id FROM novels"));
    foreach ($novels as $n) {
        updateNovelTotalWords($n['id']);
    }
    $message = '已同步所有小说总字数';
}

$categories = $db->fetchAll($db->query("SELECT * FROM categories ORDER BY sort"));

// 分页参数
$page = max(1, (int)input('page', 1, 'GET'));
$limit = 10;
$offset = ($page - 1) * $limit;

// 总记录数
$totalRows = $db->fetch($db->query("SELECT COUNT(*) as cnt FROM novels"))['cnt'];
$totalPages = ceil($totalRows / $limit);

// 分页查询
$novels = $db->fetchAll($db->query("SELECT n.*, c.name as category_name FROM novels n LEFT JOIN categories c ON n.category_id=c.id ORDER BY n.id DESC LIMIT $offset, $limit"));
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>小说管理</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="/assets/fontawesome/css/all.min.css">
    <style>
        .novel-layout {
            display: grid;
            grid-template-columns: 1fr 1.8fr;
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
            font-size: 1rem;
        }
        .card-body {
            padding: 1.5rem;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem 1.5rem;
        }
        .form-field-full {
            grid-column: span 2;
        }
        .form-field label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
            color: var(--gray-700);
        }
        .form-field input, .form-field select, .form-field textarea {
            width: 100%;
            padding: 0.5rem 0.75rem;
            border: 1px solid var(--gray-300);
            border-radius: 8px;
        }
        .cover-preview {
            margin-top: 0.5rem;
            max-height: 80px;
            border-radius: 8px;
        }
        .pagination {
            margin-top: 1.5rem;
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
            .novel-layout { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="admin-container">
    <?php include 'sidebar.php'; ?>
    <div class="content">
        <h1><i class="fas fa-book"></i> 小说管理</h1>
        <?php if ($message): ?><div class="alert"><?= h($message) ?></div><?php endif; ?>
        <div class="novel-layout">
            <div class="form-card">
                <div class="card-header" id="formTitle">添加小说</div>
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data" id="novelForm">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" id="novel_id">
                        <div class="form-grid">
                            <div class="form-field">
                                <label>标题</label>
                                <input type="text" name="title" id="title" required>
                            </div>
                            <div class="form-field">
                                <label>作者</label>
                                <input type="text" name="author" id="author">
                            </div>
                            <div class="form-field">
                                <label>分类</label>
                                <select name="category_id" id="category_id">
                                    <?php foreach($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>"><?= h($cat['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-field">
                                <label>状态</label>
                                <select name="status" id="status">
                                    <option value="0">连载中</option>
                                    <option value="1">已完结</option>
                                </select>
                            </div>
                            <div class="form-field form-field-full">
                                <label>封面</label>
                                <input type="file" name="cover" accept="image/*" id="coverFile">
                                <input type="hidden" name="old_cover" id="old_cover">
                                <div id="coverPreview"></div>
                            </div>
                            <div class="form-field form-field-full">
                                <label>简介</label>
                                <textarea name="description" id="description" rows="4"></textarea>
                            </div>
                        </div>
                        <div style="text-align: right; margin-top: 1rem;">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> 保存</button>
                            <button type="button" id="cancelEdit" class="btn btn-outline" style="display:none;">取消</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="list-card">
                <div class="card-header">
                    小说列表
                    <a href="?sync_words=1" class="btn btn-sm" style="float:right;" onclick="return confirm('同步所有小说总字数？')"><i class="fas fa-sync-alt"></i> 同步字数</a>
                </div>
                <div class="card-body" style="padding:0;">
                    <table style="width:100%; margin:0;">
                        <thead>
                            <tr><th>ID</th><th>封面</th><th>标题</th><th>作者</th><th>分类</th><th>字数</th><th>状态</th><th>操作</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach($novels as $n): ?>
                        <tr>
                            <td><?= $n['id'] ?></td>
                            <td><img src="../<?= h($n['cover'] ?: 'assets/images/default_cover.jpg') ?>" height="40" style="border-radius:6px;"></td>
                            <td><?= h($n['title']) ?></td>
                            <td><?= h($n['author']) ?></td>
                            <td><?= h($n['category_name']) ?></td>
                            <td><?= number_format($n['total_words']) ?>字</td>
                            <td><?= $n['status'] ? '完结' : '连载' ?></td>
                            <td>
                                <a href="chapters.php?novel_id=<?= $n['id'] ?>"><i class="fas fa-list"></i> 章节</a>
                                <a href="volumes.php?novel_id=<?= $n['id'] ?>"><i class="fas fa-layer-group"></i> 分卷</a>
                                <a href="#" onclick="editNovel(<?= htmlspecialchars(json_encode($n)) ?>)"><i class="fas fa-edit"></i> 编辑</a>
                                <a href="?delete=<?= $n['id'] ?>" onclick="return confirm('确定删除？')" style="color:var(--danger);"><i class="fas fa-trash"></i> 删除</a>
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
                            <a href="?page=<?= $i ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
                        <?php endfor; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<script>
function editNovel(novel) {
    document.getElementById('novel_id').value = novel.id;
    document.getElementById('title').value = novel.title;
    document.getElementById('author').value = novel.author;
    document.getElementById('category_id').value = novel.category_id;
    document.getElementById('description').value = novel.description;
    document.getElementById('status').value = novel.status;
    document.getElementById('old_cover').value = novel.cover;
    document.getElementById('formTitle').innerText = '编辑小说';
    document.getElementById('cancelEdit').style.display = 'inline-block';
    if (novel.cover) {
        document.getElementById('coverPreview').innerHTML = '<img src="../' + novel.cover + '" class="cover-preview">';
    } else {
        document.getElementById('coverPreview').innerHTML = '';
    }
}
document.getElementById('cancelEdit')?.addEventListener('click', function() {
    document.getElementById('novelForm').reset();
    document.getElementById('novel_id').value = '';
    document.getElementById('formTitle').innerText = '添加小说';
    this.style.display = 'none';
    document.getElementById('coverPreview').innerHTML = '';
});
document.getElementById('coverFile')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(ev) {
            document.getElementById('coverPreview').innerHTML = '<img src="' + ev.target.result + '" class="cover-preview">';
        };
        reader.readAsDataURL(file);
    }
});
</script>
</body>
</html>