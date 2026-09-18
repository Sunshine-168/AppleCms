<?php
require_once __DIR__ . '/../includes/config.php';

if (!isAdmin()) {
    redirect(BASE_URL . '/admin/login.php');
}
verify_admin_csrf();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_block'])) {
    $id = (int)input('id', 0, 'POST');
    $name = trim(input('name', '', 'POST'));
    $title = trim(input('title', '', 'POST'));
    $num = (int)input('num', 10, 'POST');
    $category_id = (int)input('category_id', 0, 'POST');
    $status = input('status', 'all', 'POST');
    $min_words = (int)input('min_words', 0, 'POST');
    $max_words = (int)input('max_words', 0, 'POST');
    $order_by = input('order_by', 'views', 'POST');
    $time_range = input('time_range', 'all', 'POST');
    $cache_time = (int)input('cache_time', 300, 'POST');
    $custom_ids = trim(input('custom_ids', '', 'POST'));
    
    if (empty($name)) {
        $error = 'Diy榜单名称不能为空';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $name)) {
        $error = '名称只能包含字母、数字和下划线';
    } else {
        $exist = $db->fetch($db->query("SELECT id FROM diy_blocks WHERE name = ? AND id != ?", [$name, $id]));
        if ($exist) {
            $error = 'Diy榜单名称已存在';
        } else {
            if ($id) {
                $db->query("UPDATE diy_blocks SET name=?, title=?, num=?, category_id=?, status=?, min_words=?, max_words=?, order_by=?, time_range=?, cache_time=?, custom_ids=?, updated_at=NULL WHERE id=?", 
                    [$name, $title, $num, $category_id, $status, $min_words, $max_words, $order_by, $time_range, $cache_time, $custom_ids, $id]);
                $message = '更新成功，缓存将在下次访问时重新生成';
            } else {
                $db->query("INSERT INTO diy_blocks (name, title, num, category_id, status, min_words, max_words, order_by, time_range, cache_time, custom_ids) VALUES (?,?,?,?,?,?,?,?,?,?,?)",
                    [$name, $title, $num, $category_id, $status, $min_words, $max_words, $order_by, $time_range, $cache_time, $custom_ids]);
                $message = '添加成功';
            }
            $db->query("UPDATE diy_blocks SET data = NULL, updated_at = NULL WHERE name = ?", [$name]);
        }
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $db->query("DELETE FROM diy_blocks WHERE id = ?", [$id]);
    redirect(BASE_URL . '/admin/diy_blocks.php');
}

if (isset($_GET['refresh'])) {
    $id = (int)$_GET['refresh'];
    $db->query("UPDATE diy_blocks SET updated_at = NULL WHERE id = ?", [$id]);
    redirect(BASE_URL . '/admin/diy_blocks.php');
}

$blocks = $db->fetchAll($db->query("SELECT * FROM diy_blocks ORDER BY id DESC"));
$categories = $db->fetchAll($db->query("SELECT * FROM categories ORDER BY sort"));

$editBlock = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $editBlock = $db->fetch($db->query("SELECT * FROM diy_blocks WHERE id = ?", [$editId]));
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Diy榜单管理</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="/assets/fontawesome/css/all.min.css">
    <style>
        .diy-layout {
            display: grid;
            grid-template-columns: 1fr 1.6fr;
            gap: 1.5rem;
        }
        .form-card, .list-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            border: 1px solid var(--gray-200);
            overflow: hidden;
        }
        .card-header {
            background: var(--gray-50);
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            font-weight: 600;
            font-size: 1.1rem;
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
            color: var(--gray-600);
        }
        .form-field input, .form-field select, .form-field textarea {
            width: 100%;
            padding: 0.5rem 0.75rem;
            border: 1px solid var(--gray-300);
            border-radius: 8px;
        }
        .help-text {
            font-size: 0.7rem;
            color: var(--gray-400);
            margin-top: 0.25rem;
        }
        .table-actions a {
            margin-right: 10px;
        }
        @media (max-width: 900px) {
            .diy-layout { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="admin-container">
    <?php include 'sidebar.php'; ?>
    <div class="content">
        <h1><i class="fas fa-chart-line"></i> Diy榜单管理</h1>
        <?php if ($message): ?><div class="alert"><?= h($message) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="error"><?= h($error) ?></div><?php endif; ?>

        <div class="diy-layout">
            <div class="form-card">
                <div class="card-header"><?= $editBlock ? '编辑榜单' : '添加新榜单' ?></div>
                <div class="card-body">
                    <form method="post" id="blockForm">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= $editBlock['id'] ?? 0 ?>">
                        <div class="form-grid">
                            <div class="form-field">
                                <label>榜单标识符 *</label>
                                <input type="text" name="name" value="<?= h($editBlock['name'] ?? '') ?>" required pattern="[A-Za-z0-9_]+">
                                <div class="help-text">唯一标识，DiyString('名称') 中使用的名称</div>
                            </div>
                            <div class="form-field">
                                <label>显示标题</label>
                                <input type="text" name="title" value="<?= h($editBlock['title'] ?? '') ?>" placeholder="例如：周点击榜">
                                <div class="help-text">前端展示的标题，留空则使用标识符</div>
                            </div>
                            <div class="form-field">
                                <label>数据数量</label>
                                <input type="number" name="num" value="<?= $editBlock['num'] ?? 10 ?>" min="1" max="100">
                            </div>
                            <div class="form-field">
                                <label>分类</label>
                                <select name="category_id">
                                    <option value="0">全部</option>
                                    <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= ($editBlock['category_id'] ?? 0) == $cat['id'] ? 'selected' : '' ?>><?= h($cat['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-field">
                                <label>连载状态</label>
                                <select name="status">
                                    <option value="all" <?= ($editBlock['status'] ?? 'all') == 'all' ? 'selected' : '' ?>>全部</option>
                                    <option value="ongoing" <?= ($editBlock['status'] ?? '') == 'ongoing' ? 'selected' : '' ?>>连载中</option>
                                    <option value="finished" <?= ($editBlock['status'] ?? '') == 'finished' ? 'selected' : '' ?>>已完结</option>
                                </select>
                            </div>
                            <div class="form-field">
                                <label>排序方式</label>
                                <select name="order_by" id="order_by">
                                    <option value="views" <?= ($editBlock['order_by'] ?? 'views') == 'views' ? 'selected' : '' ?>>点击最多</option>
                                    <option value="new" <?= ($editBlock['order_by'] ?? '') == 'new' ? 'selected' : '' ?>>最新入库</option>
                                    <option value="update" <?= ($editBlock['order_by'] ?? '') == 'update' ? 'selected' : '' ?>>最近更新</option>
                                    <option value="favorites" <?= ($editBlock['order_by'] ?? '') == 'favorites' ? 'selected' : '' ?>>收藏最多</option>
                                    <option value="chapters" <?= ($editBlock['order_by'] ?? '') == 'chapters' ? 'selected' : '' ?>>章节最多</option>
                                    <option value="words" <?= ($editBlock['order_by'] ?? '') == 'words' ? 'selected' : '' ?>>字数最多</option>
                                    <option value="random" <?= ($editBlock['order_by'] ?? '') == 'random' ? 'selected' : '' ?>>随机排序</option>
                                    <option value="custom" <?= ($editBlock['order_by'] ?? '') == 'custom' ? 'selected' : '' ?>>自定义顺序</option>
                                </select>
                            </div>
                            <div class="form-field">
                                <label>时间段</label>
                                <select name="time_range">
                                    <option value="all" <?= ($editBlock['time_range'] ?? 'all') == 'all' ? 'selected' : '' ?>>所有时间</option>
                                    <option value="today" <?= ($editBlock['time_range'] ?? '') == 'today' ? 'selected' : '' ?>>本日</option>
                                    <option value="week" <?= ($editBlock['time_range'] ?? '') == 'week' ? 'selected' : '' ?>>本周</option>
                                    <option value="month" <?= ($editBlock['time_range'] ?? '') == 'month' ? 'selected' : '' ?>>本月</option>
                                    <option value="year" <?= ($editBlock['time_range'] ?? '') == 'year' ? 'selected' : '' ?>>本年</option>
                                </select>
                            </div>
                            <div class="form-field">
                                <label>缓存时间(秒)</label>
                                <input type="number" name="cache_time" value="<?= $editBlock['cache_time'] ?? 300 ?>" min="0">
                                <div class="help-text">0表示不缓存</div>
                            </div>
                            <div class="form-field form-field-full">
                                <label>字数范围</label>
                                <div style="display: flex; gap: 8px;">
                                    <input type="number" name="min_words" value="<?= $editBlock['min_words'] ?? 0 ?>" placeholder="最小字数">
                                    <span>~</span>
                                    <input type="number" name="max_words" value="<?= $editBlock['max_words'] ?? 0 ?>" placeholder="最大字数(0不限)">
                                </div>
                            </div>
                            <div class="form-field form-field-full" id="customIdsField" style="<?= ($editBlock['order_by'] ?? '') == 'custom' ? '' : 'display:none;' ?>">
                                <label>自定义小说ID列表</label>
                                <input type="text" name="custom_ids" value="<?= h($editBlock['custom_ids'] ?? '') ?>" placeholder="例如：123,456,789">
                                <div class="help-text">仅当排序选择“自定义顺序”时生效，按输入的顺序展示小说，超出部分按ID降序补充。</div>
                            </div>
                        </div>
                        <div style="margin-top: 1.5rem; text-align: right;">
                            <button type="submit" name="save_block" class="btn btn-primary"><i class="fas fa-save"></i> 保存</button>
                            <?php if ($editBlock): ?>
                                <a href="diy_blocks.php" class="btn btn-outline" style="margin-left: 8px;"><i class="fas fa-times"></i> 取消</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <div class="list-card">
                <div class="card-header">现有榜单</div>
                <div class="card-body" style="padding: 0;">
                    <table style="width:100%; margin:0;">
                        <thead>
                            <tr><th>ID</th><th>标题</th><th>数量</th><th>排序</th><th>缓存(s)</th><th>操作</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($blocks as $b): ?>
                        <tr>
                            <td><?= $b['id'] ?></a>
                            <td><?= h($b['title'] ?: $b['name']) ?></a>
                            <td><?= $b['num'] ?></a>
                            <td><?= $b['order_by'] ?></a>
                            <td><?= $b['cache_time'] ?></a>
                            <td>
                                <a href="?edit=<?= $b['id'] ?>"><i class="fas fa-edit"></i> 编辑</a>
                                <a href="?refresh=<?= $b['id'] ?>" onclick="return confirm('刷新缓存？')"><i class="fas fa-sync-alt"></i> 刷新</a>
                                <a href="?delete=<?= $b['id'] ?>" onclick="return confirm('确定删除？')" style="color:var(--danger);"><i class="fas fa-trash"></i> 删除</a>
                              </a>
                          </a>
                        <?php endforeach; ?>
                        </tbody>
                     </a>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
document.getElementById('order_by').addEventListener('change', function() {
    var customField = document.getElementById('customIdsField');
    if (this.value === 'custom') {
        customField.style.display = 'block';
    } else {
        customField.style.display = 'none';
    }
});
</script>
</body>
</html>