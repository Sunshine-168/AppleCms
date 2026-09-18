<?php
/**
 * SEO 设置管理
 */
require_once __DIR__ . '/../includes/config.php';

if (!isAdmin()) {
    redirect(BASE_URL . '/admin/login.php');
}
verify_admin_csrf();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_seo'])) {
    foreach ($_POST['seo'] as $page => $data) {
        $title = trim($data['title'] ?? '');
        $keywords = trim($data['keywords'] ?? '');
        $description = trim($data['description'] ?? '');
        $db->query("REPLACE INTO seo_settings (page, title, keywords, description) VALUES (?, ?, ?, ?)",
            [$page, $title, $keywords, $description]);
    }
    $message = 'SEO 设置已保存';
}

$seoList = [];
$res = $db->query("SELECT * FROM seo_settings");
while ($row = $db->fetch($res)) {
    $seoList[$row['page']] = $row;
}

$pages = [
    'home' => '首页',
    'library' => '书库页',
    'rank' => '排行榜页',
    'author' => '作者列表页',
    'novel_detail' => '小说详情页',
    'read' => '章节阅读页',
    'search' => '搜索页'
];

$variables = [
    '{site_name}' => '网站名称',
    '{site_keywords}' => '网站关键词',
    '{site_description}' => '网站描述',
    '{novel_title}' => '小说标题',
    '{author}' => '作者',
    '{category_name}' => '分类名称',
    '{status}' => '连载状态',
    '{chapter_title}' => '章节标题',
    '{keyword}' => '搜索关键词',
    // 书库页专用
    '{page}' => '当前页码',
    '{total_pages}' => '总页数',
    '{category_id}' => '分类ID',
    '{order_by}' => '排序方式',
    // 小说详情页专用
    '{total_words}' => '小说总字数',
    '{total_chapters}' => '总章节数',
    '{views}' => '点击量',
    '{favorites}' => '收藏数',
    '{description_preview}' => '简介前150字',
    // 阅读页专用
    '{chapter_word_count}' => '本章字数',
    '{chapter_intro}' => '章节内容前150字',
    '{chapter_views}' => '小说点击量',
    // 排行榜页专用
    '{rank_type}' => '排行榜类型（views/words/favorites）',
];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>SEO 设置</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="/assets/fontawesome/css/all.min.css">
    <style>
        .seo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .seo-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            border: 1px solid var(--gray-200);
            overflow: hidden;
            transition: all 0.2s;
        }
        .seo-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            transform: translateY(-2px);
        }
        .seo-card-header {
            background: var(--gray-50);
            padding: 0.9rem 1.2rem;
            border-bottom: 1px solid var(--gray-200);
            font-weight: 600;
            font-size: 1rem;
            color: var(--gray-700);
        }
        .seo-card-body {
            padding: 1.2rem;
        }
        .seo-field {
            margin-bottom: 1rem;
        }
        .seo-field label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--gray-500);
            margin-bottom: 0.25rem;
            letter-spacing: 0.3px;
        }
        .seo-field input,
        .seo-field textarea {
            width: 100%;
            padding: 0.5rem 0.75rem;
            border: 1px solid var(--gray-300);
            border-radius: 8px;
            font-size: 0.85rem;
            transition: 0.2s;
        }
        .seo-field input:focus,
        .seo-field textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(59,130,246,0.1);
        }
        .variable-hint {
            background: var(--gray-50);
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            font-size: 0.85rem;
            border-left: 4px solid var(--primary);
        }
        .variable-hint code {
            background: #e2e8f0;
            padding: 2px 6px;
            border-radius: 6px;
            font-size: 0.8rem;
        }
        .btn-save {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.6rem 1.5rem;
            border-radius: 30px;
            font-weight: 500;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-save:hover {
            background: var(--primary-dark);
        }
    </style>
</head>
<body>
<div class="admin-container">
    <?php include 'sidebar.php'; ?>
    <div class="content">
        <h1><i class="fas fa-search"></i> SEO 设置</h1>
        <?php if ($message): ?><div class="alert"><?= h($message) ?></div><?php endif; ?>

        <div class="variable-hint">
            <i class="fas fa-info-circle"></i> <strong>可用变量：</strong>
            <?php foreach ($variables as $var => $desc): ?>
                <code><?= $var ?></code> = <?= $desc ?> &nbsp;&nbsp;
            <?php endforeach; ?>
        </div>

        <form method="post">
            <?= csrf_field() ?>
            <div class="seo-grid">
                <?php foreach ($pages as $page => $name):
                    $seo = $seoList[$page] ?? ['title' => '', 'keywords' => '', 'description' => ''];
                ?>
                    <div class="seo-card">
                        <div class="seo-card-header"><?= h($name) ?> <span style="font-size:0.7rem; color:#9ca3af;">(<?= $page ?>)</span></div>
                        <div class="seo-card-body">
                            <div class="seo-field">
                                <label>标题</label>
                                <input type="text" name="seo[<?= $page ?>][title]" value="<?= h($seo['title']) ?>" placeholder="例如：{site_name} - 首页">
                            </div>
                            <div class="seo-field">
                                <label>关键词</label>
                                <input type="text" name="seo[<?= $page ?>][keywords]" value="<?= h($seo['keywords']) ?>">
                            </div>
                            <div class="seo-field">
                                <label>描述</label>
                                <textarea name="seo[<?= $page ?>][description]" rows="2"><?= h($seo['description']) ?></textarea>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div style="text-align: right;">
                <button type="submit" name="save_seo" class="btn-save"><i class="fas fa-save"></i> 保存所有 SEO 设置</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>