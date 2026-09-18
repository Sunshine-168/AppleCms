<?php
/**
 * 火车头采集器发布接口（增强版）
 * 支持 action: check_novel, list_chapters, add_novel, add_chapter, update_novel
 * 自动添加 source_id 和 source_chapter_id 字段
 */

require_once __DIR__ . '/../../includes/config.php';
require_once ROOT_PATH . 'includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

// ---------- 自动添加字段 ----------
function ensure_columns($db) {
    // 检查 novels 表 source_id
    $res = $db->query("SHOW COLUMNS FROM novels LIKE 'source_id'");
    if (!$db->fetch($res)) {
        $db->query("ALTER TABLE novels ADD COLUMN source_id VARCHAR(100) DEFAULT NULL AFTER author");
    }
    // 检查 chapters 表 source_chapter_id
    $res2 = $db->query("SHOW COLUMNS FROM chapters LIKE 'source_chapter_id'");
    if (!$db->fetch($res2)) {
        $db->query("ALTER TABLE chapters ADD COLUMN source_chapter_id VARCHAR(50) DEFAULT NULL AFTER source_url");
    }
}
ensure_columns($db);
// ------------------------------------------------

$apiKey = input('api_key', '', 'POST');
$storedKey = getSetting('locoy_api_key', $db);
if (empty($storedKey) || $apiKey !== $storedKey) {
    echo json_encode(['code' => 0, 'message' => 'API Key 无效或未配置']);
    exit;
}

$action = input('action', '', 'POST');
if (empty($action)) {
    echo json_encode(['code' => 0, 'message' => '缺少 action 参数']);
    exit;
}

try {
    switch ($action) {
        case 'check_novel':
            $result = handleCheckNovel();
            break;
        case 'list_chapters':
            $result = handleListChapters();
            break;
        case 'add_novel':
            $result = handleAddNovel();
            break;
        case 'add_chapter':
            $result = handleAddChapter();
            break;
        case 'update_novel':
            $result = handleUpdateNovel();
            break;
        default:
            $result = ['code' => 0, 'message' => '不支持的 action'];
    }
} catch (Exception $e) {
    $result = ['code' => 0, 'message' => '处理异常：' . $e->getMessage()];
}

echo json_encode($result, JSON_UNESCAPED_UNICODE);
exit;

// ========== 辅助函数 ==========
function getCategoryId($categoryName, $db) {
    if (empty($categoryName)) return 0;
    $mapJson = getSetting('locoy_category_map', $db) ?: '{"mappings":[],"default_category_id":0}';
    $mapData = json_decode($mapJson, true);
    if (!is_array($mapData)) $mapData = ['mappings' => [], 'default_category_id' => 0];
    $mappings = $mapData['mappings'] ?? [];
    $defaultCategoryId = $mapData['default_category_id'] ?? 0;

    if (isset($mappings[$categoryName])) return (int)$mappings[$categoryName];
    if ($defaultCategoryId > 0) return $defaultCategoryId;

    $cat = $db->fetch($db->query("SELECT id FROM categories WHERE name = ?", [$categoryName]));
    if ($cat) return $cat['id'];
    $db->query("INSERT INTO categories (name, sort) VALUES (?, 0)", [$categoryName]);
    return $db->lastInsertId();
}

// ========== Action 处理函数 ==========
function handleCheckNovel() {
    global $db;
    $title = trim(input('title', '', 'POST'));
    $author = trim(input('author', '', 'POST'));
    if (empty($title) || empty($author)) {
        return ['code' => 0, 'message' => '标题和作者不能为空'];
    }
    $novel = $db->fetch($db->query("SELECT id FROM novels WHERE title = ? AND author = ?", [$title, $author]));
    return [
        'code' => 1,
        'message' => '查询成功',
        'data' => ['exists' => !empty($novel), 'novel_id' => $novel ? $novel['id'] : null]
    ];
}

function handleListChapters() {
    global $db;
    $novelId = (int)input('novel_id', 0, 'POST');
    if ($novelId <= 0) return ['code' => 0, 'message' => '小说ID无效'];
    $chapters = $db->fetchAll($db->query(
        "SELECT id, title, sort, source_url, source_chapter_id, (SELECT title FROM volumes WHERE id=chapters.volume_id) as volume_title 
         FROM chapters WHERE novel_id = ? ORDER BY sort",
        [$novelId]
    ));
    return ['code' => 1, 'message' => '获取成功', 'data' => $chapters];
}

function handleAddNovel() {
    global $db;
    $title = trim(input('title', '', 'POST'));
    $author = trim(input('author', '', 'POST'));
    $category = trim(input('category', '', 'POST'));
    $description = trim(input('description', '', 'POST', false));
    $status = (int) input('status', 0, 'POST');
    $sourceId = trim(input('source_id', '', 'POST'));
    if (empty($title) || empty($author)) return ['code' => 0, 'message' => '标题和作者不能为空'];

    $categoryId = getCategoryId($category, $db);
    $coverPath = '';
    if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($ext, $allowed)) {
            $filename = uniqid() . '.' . $ext;
            $target = ROOT_PATH . 'data/covers/' . $filename;
            if (move_uploaded_file($_FILES['cover']['tmp_name'], $target)) {
                $coverPath = 'data/covers/' . $filename;
            }
        }
    }

    $exist = $db->fetch($db->query("SELECT id, cover FROM novels WHERE title = ? AND author = ?", [$title, $author]));
    if ($exist) {
        $updates = []; $params = [];
        if ($categoryId > 0) { $updates[] = "category_id = ?"; $params[] = $categoryId; }
        if (!empty($description)) { $updates[] = "description = ?"; $params[] = $description; }
        if ($status >= 0) { $updates[] = "status = ?"; $params[] = $status; }
        if (!empty($coverPath)) { $updates[] = "cover = ?"; $params[] = $coverPath; if (!empty($exist['cover']) && $exist['cover'] !== 'assets/images/default_cover.jpg') @unlink(ROOT_PATH . $exist['cover']); }
        if (!empty($sourceId)) { $updates[] = "source_id = ?"; $params[] = $sourceId; }
        if (!empty($updates)) { $params[] = $exist['id']; $db->query("UPDATE novels SET " . implode(', ', $updates) . " WHERE id = ?", $params); }
        $novelId = $exist['id'];
        $message = '小说已存在，信息已更新';
    } else {
        $cover = !empty($coverPath) ? $coverPath : 'assets/images/default_cover.jpg';
        $db->query("INSERT INTO novels (title, author, category_id, cover, description, status, source_id) VALUES (?,?,?,?,?,?,?)", [$title, $author, $categoryId, $cover, $description, $status, $sourceId]);
        $novelId = $db->lastInsertId();
        $message = '小说发布成功';
    }
    return ['code' => 1, 'message' => $message, 'data' => ['novel_id' => $novelId]];
}

function handleUpdateNovel() {
    global $db;
    $novelId = (int) input('novel_id', 0, 'POST');
    if ($novelId <= 0) return ['code' => 0, 'message' => '小说ID无效'];
    $title = trim(input('title', '', 'POST'));
    $author = trim(input('author', '', 'POST'));
    $category = trim(input('category', '', 'POST'));
    $description = trim(input('description', '', 'POST', false));
    $status = (int) input('status', -1, 'POST');
    $sourceId = trim(input('source_id', '', 'POST'));

    $categoryId = getCategoryId($category, $db);
    $updates = []; $params = [];
    if (!empty($title)) { $updates[] = "title = ?"; $params[] = $title; }
    if (!empty($author)) { $updates[] = "author = ?"; $params[] = $author; }
    if ($categoryId > 0) { $updates[] = "category_id = ?"; $params[] = $categoryId; }
    if (!empty($description)) { $updates[] = "description = ?"; $params[] = $description; }
    if ($status >= 0) { $updates[] = "status = ?"; $params[] = $status; }
    if (!empty($sourceId)) { $updates[] = "source_id = ?"; $params[] = $sourceId; }

    if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($ext, $allowed)) {
            $filename = uniqid() . '.' . $ext;
            $target = ROOT_PATH . 'data/covers/' . $filename;
            if (move_uploaded_file($_FILES['cover']['tmp_name'], $target)) {
                $coverPath = 'data/covers/' . $filename;
                $updates[] = "cover = ?"; $params[] = $coverPath;
                $old = $db->fetch($db->query("SELECT cover FROM novels WHERE id = ?", [$novelId]));
                if (!empty($old['cover']) && $old['cover'] !== 'assets/images/default_cover.jpg') @unlink(ROOT_PATH . $old['cover']);
            }
        }
    }
    if (empty($updates)) return ['code' => 0, 'message' => '没有要更新的字段'];
    $params[] = $novelId;
    $db->query("UPDATE novels SET " . implode(', ', $updates) . " WHERE id = ?", $params);
    return ['code' => 1, 'message' => '小说信息更新成功', 'data' => ['novel_id' => $novelId]];
}

function handleAddChapter() {
    global $db;
    $novelId = (int) input('novel_id', 0, 'POST');
    $novelTitle = trim(input('novel_title', '', 'POST'));
    $author = trim(input('author', '', 'POST'));
    $volumeName = trim(input('volume', '', 'POST')) ?: '默认卷';
    $chapterTitle = trim(input('chapter_title', '', 'POST'));
    $content = input('content', '', 'POST', false);
    $sort = (int) input('sort', 0, 'POST');
    $sourceUrl = trim(input('source_url', '', 'POST'));
    $sourceChapterId = trim(input('source_chapter_id', '', 'POST'));

    if (empty($chapterTitle)) return ['code' => 0, 'message' => '章节标题不能为空'];

    if ($novelId <= 0) {
        if (empty($novelTitle) || empty($author)) return ['code' => 0, 'message' => '请提供 novel_id 或 (novel_title + author)'];
        $novel = $db->fetch($db->query("SELECT id FROM novels WHERE title = ? AND author = ?", [$novelTitle, $author]));
        if (!$novel) return ['code' => 0, 'message' => '未找到匹配的小说，请先发布小说'];
        $novelId = $novel['id'];
    } else {
        $novel = $db->fetch($db->query("SELECT id FROM novels WHERE id = ?", [$novelId]));
        if (!$novel) return ['code' => 0, 'message' => '小说ID无效'];
    }

    $volume = $db->fetch($db->query("SELECT id FROM volumes WHERE novel_id = ? AND title = ?", [$novelId, $volumeName]));
    if ($volume) $volumeId = $volume['id'];
    else { $db->query("INSERT INTO volumes (novel_id, title, sort) VALUES (?, ?, 0)", [$novelId, $volumeName]); $volumeId = $db->lastInsertId(); }

    // 检查是否已存在（通过 source_chapter_id 快速判定）
    if (!empty($sourceChapterId)) {
        $exist = $db->fetch($db->query("SELECT id FROM chapters WHERE novel_id = ? AND source_chapter_id = ?", [$novelId, $sourceChapterId]));
        if ($exist) return ['code' => 0, 'message' => '章节已存在（source_chapter_id匹配）', 'data' => ['chapter_id' => $exist['id']]];
    }

    if ($sort <= 0) {
        $maxSort = $db->fetch($db->query("SELECT MAX(sort) as max FROM chapters WHERE novel_id = ?", [$novelId]))['max'] ?? 0;
        $sort = $maxSort + 1;
    }

    $filePath = getChapterFilePath($novelId);
    $fullPath = ROOT_PATH . $filePath;
    if (!is_dir(dirname($fullPath))) mkdir(dirname($fullPath), 0755, true);
    file_put_contents($fullPath, $content);
    $wordCount = smartWordCount($content);
    $db->query("INSERT INTO chapters (novel_id, volume_id, title, word_count, file_path, sort, source_url, source_chapter_id) VALUES (?,?,?,?,?,?,?,?)",
        [$novelId, $volumeId, $chapterTitle, $wordCount, $filePath, $sort, $sourceUrl, $sourceChapterId]);
    $chapterId = $db->lastInsertId();
    updateNovelTotalWords($novelId);
    return ['code' => 1, 'message' => '章节发布成功', 'data' => ['chapter_id' => $chapterId]];
}