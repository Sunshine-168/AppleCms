<?php
/**
 * admin/chapters.php - 章节管理（支持分卷，增加分页）
 */
require_once __DIR__ . '/../includes/config.php';

if (!isAdmin()) {
    redirect(BASE_URL . '/admin/login.php');
}
verify_admin_csrf();

function cleanContent($content) {
    $content = str_replace(["\r\n", "\r"], "\n", $content);
    $content = preg_replace('/\n{3,}/', "\n\n", $content);
    $lines = explode("\n", $content);
    foreach ($lines as &$line) $line = trim($line);
    $content = implode("\n", $lines);
    return trim($content);
}

$novel_id = (int)input('novel_id', 0, 'GET');
if (!$novel_id) {
    die('请先选择小说');
}

$novel = $db->fetch($db->query("SELECT * FROM novels WHERE id=?", [$novel_id]));
if (!$novel) {
    die('小说不存在');
}

$message = '';

// 获取所有卷（按 sort 排序）
$volumes = $db->fetchAll($db->query("SELECT * FROM volumes WHERE novel_id=? ORDER BY sort", [$novel_id]));
if (empty($volumes)) {
    // 如果没有卷，则创建一个默认卷
    $db->query("INSERT INTO volumes (novel_id, title, sort) VALUES (?, '默认卷', 0)", [$novel_id]);
    $volumes = $db->fetchAll($db->query("SELECT * FROM volumes WHERE novel_id=? ORDER BY sort", [$novel_id]));
}

$firstVolumeId = $volumes[0]['id'];

// 分页参数
$page = max(1, (int)input('page', 1, 'GET'));
$limit = 50;
$offset = ($page - 1) * $limit;

// 总章节数
$totalChapters = $db->fetch($db->query("SELECT COUNT(*) as cnt FROM chapters WHERE novel_id=?", [$novel_id]))['cnt'];
$totalPages = ceil($totalChapters / $limit);

// 获取当前页章节（带排序）
$chaptersRaw = $db->fetchAll($db->query("SELECT * FROM chapters WHERE novel_id=? ORDER BY sort LIMIT $offset, $limit", [$novel_id]));

// 按卷分组显示（仅对当前页的章节）
$chaptersByVolume = [];
$volumeMap = [];
foreach ($volumes as $vol) {
    $volumeMap[$vol['id']] = $vol;
    $chaptersByVolume[$vol['id']] = [
        'volume' => $vol,
        'chapters' => []
    ];
}
foreach ($chaptersRaw as $ch) {
    $vid = $ch['volume_id'];
    if (empty($vid) || $vid == 0) {
        $vid = $firstVolumeId;
        $ch['_temp_uncategorized'] = true;
    }
    if (isset($chaptersByVolume[$vid])) {
        $chaptersByVolume[$vid]['chapters'][] = $ch;
    } else {
        $chaptersByVolume[$firstVolumeId]['chapters'][] = $ch;
    }
}
// 移除空卷（当前页无章节的卷不显示）
foreach ($chaptersByVolume as $vid => $group) {
    if (empty($group['chapters']) && $vid != $firstVolumeId) {
        unset($chaptersByVolume[$vid]);
    }
}

// 处理 POST 提交（添加/编辑章节）
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)input('id', 0, 'POST');
    $title = input('title', '', 'POST');
    $volume_id = (int)input('volume_id', 0, 'POST');
    $sort = (int)input('sort', 0, 'POST');
    $content = input('content', '', 'POST', false);
    $content = cleanContent($content);
    $newWordCount = smartWordCount($content, $GLOBALS['lang']->current());

    if ($volume_id <= 0 && !empty($volumes)) {
        $volume_id = $volumes[0]['id'];
    }

    if ($id) {
        $old = $db->fetch($db->query("SELECT file_path, word_count FROM chapters WHERE id=?", [$id]));
        $oldWordCount = $old['word_count'];
        $filePath = $old['file_path'];
        file_put_contents(ROOT_PATH . $filePath, $content);
        $db->query("UPDATE chapters SET title=?, volume_id=?, word_count=?, sort=? WHERE id=?", 
            [$title, $volume_id, $newWordCount, $sort, $id]);
        $totalWords = $db->fetch($db->query("SELECT SUM(word_count) as total FROM chapters WHERE novel_id=?", [$novel_id]))['total'] ?? 0;
        $db->query("UPDATE novels SET total_words = ? WHERE id=?", [$totalWords, $novel_id]);
        $message = '更新成功';
    } else {
        $filePath = getChapterFilePath($novel_id);
        $fullPath = ROOT_PATH . $filePath;
        $dir = dirname($fullPath);
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        file_put_contents($fullPath, $content);
        $db->query("INSERT INTO chapters (novel_id, volume_id, title, word_count, file_path, sort) VALUES (?,?,?,?,?,?)",
            [$novel_id, $volume_id, $title, $newWordCount, $filePath, $sort]);
        updateNovelTotalWords($novel_id);
        $message = '添加成功';
    }
    // 刷新并保持分页位置
    redirect(BASE_URL . "/admin/chapters.php?novel_id=$novel_id&page=$page");
}

// 处理删除
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $ch = $db->fetch($db->query("SELECT file_path FROM chapters WHERE id=?", [$id]));
    if ($ch && $ch['file_path']) {
        @unlink(ROOT_PATH . $ch['file_path']);
    }
    $db->query("DELETE FROM chapters WHERE id=?", [$id]);
    updateNovelTotalWords($novel_id);
    redirect(BASE_URL . "/admin/chapters.php?novel_id=$novel_id&page=$page");
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>章节管理 - <?= h($novel['title']) ?></title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="/assets/fontawesome/css/all.min.css">
    <style>
        .chapter-layout {
            display: grid;
            grid-template-columns: 1fr 1.2fr;
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
            margin-bottom: 1.2rem;
        }
        .form-field label {
            display: block;
            font-weight: 600;
            font-size: 0.8rem;
            margin-bottom: 0.25rem;
        }
        .form-field input, .form-field textarea, .form-field select {
            width: 100%;
            padding: 0.5rem 0.75rem;
            border: 1px solid var(--gray-300);
            border-radius: 8px;
        }
        .form-field textarea {
            font-family: monospace;
            resize: vertical;
        }
        .chapter-table table {
            width: 100%;
        }
        .chapter-table th, .chapter-table td {
            padding: 0.75rem;
        }
        .volume-badge {
            display: inline-block;
            background: var(--primary);
            color: white;
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            font-size: 0.75rem;
        }
        .uncategorized-badge {
            background: #ff9800;
            margin-left: 5px;
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
            .chapter-layout { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="admin-container">
    <?php include 'sidebar.php'; ?>
    <div class="content">
        <h1><i class="fas fa-list-ul"></i> 章节管理：<?= h($novel['title']) ?> (总字数: <?= number_format($novel['total_words']) ?>字)</h1>
        <?php if ($message): ?><div class="alert"><?= h($message) ?></div><?php endif; ?>
        
        <div class="chapter-layout">
            <!-- 表单区 -->
            <div class="form-card">
                <div class="card-header" id="formTitle">添加章节</div>
                <div class="card-body">
                    <form method="post" id="chapterForm">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" id="chapter_id">
                        <div class="form-field">
                            <label>所属卷</label>
                            <select name="volume_id" id="volume_id">
                                <?php foreach ($volumes as $vol): ?>
                                <option value="<?= $vol['id'] ?>"><?= h($vol['title']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-field">
                            <label>章节标题</label>
                            <input type="text" name="title" id="title" required>
                        </div>
                        <div class="form-field">
                            <label>排序</label>
                            <input type="number" name="sort" id="sort" value="0">
                        </div>
                        <div class="form-field">
                            <label>内容（支持HTML）</label>
                            <textarea name="content" id="content" rows="12" required></textarea>
                        </div>
                        <div style="text-align: right;">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> 保存</button>
                            <button type="button" id="cancelChapterEdit" class="btn btn-outline" style="display:none;">取消</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- 列表区（按卷分组 + 分页） -->
            <div class="list-card chapter-table">
                <div class="card-header">章节列表（第<?= $page ?>页，共<?= $totalPages ?>页）</div>
                <div class="card-body" style="padding:0;">
                    <?php foreach ($chaptersByVolume as $volId => $group): ?>
                    <div style="background: var(--gray-50); padding: 0.5rem 1rem; font-weight:600; border-top:1px solid var(--gray-200);">
                        <span class="volume-badge"><?= h($group['volume']['title']) ?></span>
                        <?php if ($volId == $firstVolumeId && !empty(array_filter($group['chapters'], function($c) { return !empty($c['_temp_uncategorized']); }))): ?>
                            <span class="volume-badge uncategorized-badge" style="background:#ff9800;">含无分卷章节</span>
                        <?php endif; ?>
                    </div>
                    <table style="width:100%; margin:0;">
                        <thead>
                            <tr><th>ID</th><th>标题</th><th>字数</th><th>排序</th><th>操作</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach($group['chapters'] as $c): ?>
                        <tr>
                            <td><?= $c['id'] ?></td>
                            <td><?= h($c['title']) ?>
                                <?php if (!empty($c['_temp_uncategorized'])): ?>
                                    <span class="volume-badge uncategorized-badge" style="background:#ff9800; font-size:0.7rem;">无分卷</span>
                                <?php endif; ?>
                             </td>
                            <td><?= number_format($c['word_count']) ?>字</td>
                            <td><?= $c['sort'] ?></td>
                            <td>
                                <a href="#" class="edit-chapter" data-id="<?= $c['id'] ?>" data-title="<?= h($c['title']) ?>" data-sort="<?= $c['sort'] ?>" data-volume="<?= $c['volume_id'] ?>"><i class="fas fa-edit"></i> 编辑</a>
                                <a href="?novel_id=<?= $novel_id ?>&delete=<?= $c['id'] ?>&page=<?= $page ?>" onclick="return confirm('确定删除？')" style="color:var(--danger);"><i class="fas fa-trash"></i> 删除</a>
                              </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endforeach; ?>
                </div>
                <?php if ($totalPages > 1): ?>
                <div class="card-body" style="border-top:1px solid var(--gray-200); text-align:center;">
                    <div class="pagination">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="?novel_id=<?= $novel_id ?>&page=<?= $i ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
                        <?php endfor; ?>
                    </div>
                </div>
                <?php endif; ?>
                <div class="card-body" style="border-top:1px solid var(--gray-200); text-align:center;">
                    <a href="novels.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> 返回小说列表</a>
                    <a href="volumes.php?novel_id=<?= $novel_id ?>" class="btn btn-outline"><i class="fas fa-layer-group"></i> 管理分卷</a>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
document.querySelectorAll('.edit-chapter').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const id = this.dataset.id;
        const title = this.dataset.title;
        const sort = this.dataset.sort;
        let volume = this.dataset.volume;
        if (!volume || volume == 0) {
            volume = document.getElementById('volume_id').options[0]?.value || '';
        }
        document.getElementById('chapter_id').value = id;
        document.getElementById('title').value = title;
        document.getElementById('sort').value = sort;
        if (volume) document.getElementById('volume_id').value = volume;
        document.getElementById('formTitle').innerText = '编辑章节';
        document.getElementById('cancelChapterEdit').style.display = 'inline-block';
        document.getElementById('content').value = '加载中...';
        fetch(`/api/ajax/get_chapter_content.php?id=${id}`)
            .then(res => res.json())
            .then(data => {
                if (data.code === 1) {
                    document.getElementById('content').value = data.content;
                } else {
                    alert('加载失败：' + data.message);
                    document.getElementById('content').value = '';
                }
            })
            .catch(err => {
                alert('网络错误，无法加载内容');
                document.getElementById('content').value = '';
            });
    });
});
document.getElementById('cancelChapterEdit')?.addEventListener('click', function() {
    document.getElementById('chapterForm').reset();
    document.getElementById('chapter_id').value = '';
    document.getElementById('formTitle').innerText = '添加章节';
    this.style.display = 'none';
    document.getElementById('content').value = '';
});
</script>
</body>
</html>