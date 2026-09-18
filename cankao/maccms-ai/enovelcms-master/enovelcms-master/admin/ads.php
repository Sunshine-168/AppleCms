<?php
/**
 * 广告管理 + 关键词链接替换
 */
require_once __DIR__ . '/../includes/config.php';

if (!isAdmin()) {
    redirect(BASE_URL . '/admin/login.php');
}
verify_admin_csrf();
$columns = $db->fetchAll($db->query("SHOW COLUMNS FROM ads"));
$hasHtmlCode = false;
foreach ($columns as $col) {
    if ($col['Field'] == 'html_code') $hasHtmlCode = true;
}
if (!$hasHtmlCode) {
    $db->query("ALTER TABLE ads ADD COLUMN html_code TEXT AFTER link");
}

$message = '';

// 处理普通广告
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ad_submit'])) {
    $id = (int)input('id', 0, 'POST');
    $name = input('name', '', 'POST');
    $position = input('position', '', 'POST');
    $html_code = input('html_code', '', 'POST', false);
    $sort = (int)input('sort', 0, 'POST');
    $enabled = isset($_POST['enabled']) ? 1 : 0;
    
    if ($id) {
        $db->query("UPDATE ads SET name=?, position=?, html_code=?, sort=?, enabled=? WHERE id=?",
            [$name, $position, $html_code, $sort, $enabled, $id]);
        $message = '更新成功';
    } else {
        $db->query("INSERT INTO ads (name, position, html_code, sort, enabled) VALUES (?,?,?,?,?)",
            [$name, $position, $html_code, $sort, $enabled]);
        $message = '添加成功';
    }
}

// 处理关键词链接替换配置
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_keyword_links'])) {
    $keywordLinks = trim(input('keyword_links', '', 'POST', false));
    $db->query("REPLACE INTO settings (`key`, `value`) VALUES ('keyword_links', ?)", [$keywordLinks]);
    $message = '关键词链接配置已保存';
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $db->query("DELETE FROM ads WHERE id=?", [$id]);
    redirect(BASE_URL . '/admin/ads.php');
}

$ads = $db->fetchAll($db->query("SELECT * FROM ads ORDER BY sort"));

// 获取已保存的关键词链接配置
$keywordLinksSetting = getSetting('keyword_links', $db);
$keywordLinks = $keywordLinksSetting ?: '';

// 广告位置列表
$positions = [
    'home_banner' => '首页轮播图',
    'home_sidebar_top' => '首页侧边栏顶部',
    'home_sidebar_bottom' => '首页侧边栏底部',
    'novel_detail_top' => '小说详情页顶部',
    'novel_detail_bottom' => '小说详情页底部',
    'read_top' => '阅读页顶部',
    'read_bottom' => '阅读页底部',
    'library_top' => '书库页顶部',
    'rank_top' => '排行榜顶部'
];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>广告管理</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="/assets/fontawesome/css/all.min.css">
    <style>
        .ad-layout {
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 1.5rem;
        }
        .form-card, .list-card, .keyword-card {
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
        .form-field input, .form-field select, .form-field textarea {
            width: 100%;
            padding: 0.5rem 0.75rem;
            border: 1px solid var(--gray-300);
            border-radius: 8px;
        }
        .form-field textarea {
            font-family: monospace;
            min-height: 120px;
        }
        .help-text {
            font-size: 0.7rem;
            color: var(--gray-500);
            margin-top: 0.25rem;
        }
        @media (max-width: 900px) {
            .ad-layout { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="admin-container">
    <?php include 'sidebar.php'; ?>
    <div class="content">
        <h1><i class="fas fa-ad"></i> 广告管理</h1>
        <?php if ($message): ?><div class="alert"><?= h($message) ?></div><?php endif; ?>
        
        <div class="ad-layout">
            <div class="form-card">
                <div class="card-header" id="formTitle">添加广告</div>
                <div class="card-body">
                    <form method="post" id="adForm">
                        <?= csrf_field() ?>
                        <input type="hidden" name="ad_submit" value="1">
                        <input type="hidden" name="id" id="ad_id">
                        <div class="form-field">
                            <label>广告名称</label>
                            <input type="text" name="name" id="name" required>
                        </div>
                        <div class="form-field">
                            <label>广告位置</label>
                            <select name="position" id="position">
                                <?php foreach ($positions as $val => $label): ?>
                                    <option value="<?= $val ?>"><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-field">
                            <label>HTML代码</label>
                            <textarea name="html_code" id="html_code" placeholder="<a href='...'><img src='...'></a> 或自定义HTML"></textarea>
                            <div class="help-text">支持任意HTML/JS/CSS代码，例如广告联盟代码、图片链接等。</div>
                        </div>
                        <div class="form-field">
                            <label>排序</label>
                            <input type="number" name="sort" id="sort" value="0">
                        </div>
                        <div class="form-field">
                            <label>
                                <input type="checkbox" name="enabled" id="enabled" value="1" checked> 启用
                            </label>
                        </div>
                        <div style="text-align: right;">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> 保存</button>
                            <button type="button" id="cancelAdEdit" class="btn btn-outline" style="display:none;">取消</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="list-card">
                <div class="card-header">现有广告</div>
                <div class="card-body" style="padding:0;">
                    <table style="width:100%; margin:0;">
                        <thead>
                            <tr><th>ID</th><th>名称</th><th>位置</th><th>排序</th><th>状态</th><th>操作</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach($ads as $ad): ?>
                        <tr>
                            <td><?= $ad['id'] ?></td>
                            <td><?= h($ad['name']) ?></td>
                            <td><?= $positions[$ad['position']] ?? $ad['position'] ?></td>
                            <td><?= $ad['sort'] ?></td>
                            <td><?= $ad['enabled'] ? '启用' : '禁用' ?></td>
                            <td>
                                <a href="#" onclick="editAd(<?= htmlspecialchars(json_encode($ad)) ?>)"><i class="fas fa-edit"></i> 编辑</a>
                                <a href="?delete=<?= $ad['id'] ?>" onclick="return confirm('确定删除？')" style="color:var(--danger);"><i class="fas fa-trash"></i> 删除</a>
                             </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 关键词链接替换配置 -->
        <div class="keyword-card" style="margin-top: 1.5rem;">
            <div class="card-header"><i class="fas fa-link"></i> 关键词自动链接替换（章节页生效）</div>
            <div class="card-body">
                <form method="post">
                    <?= csrf_field() ?>
                    <div class="form-field">
                        <label>规则（每行一个，格式：关键词|URL）</label>
                        <textarea name="keyword_links" rows="8" placeholder="小说网|https://example.com
小说|https://example2.com"><?= h($keywordLinks) ?></textarea>
                        <div class="help-text">在章节内容中，自动将“关键词”替换为指向URL的超链接（新窗口打开）。注意关键词区分大小写。</div>
                    </div>
                    <button type="submit" name="save_keyword_links" class="btn btn-primary"><i class="fas fa-save"></i> 保存链接规则</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
function editAd(ad) {
    document.getElementById('ad_id').value = ad.id;
    document.getElementById('name').value = ad.name;
    document.getElementById('position').value = ad.position;
    document.getElementById('sort').value = ad.sort;
    document.getElementById('enabled').checked = ad.enabled == 1;
    document.getElementById('html_code').value = ad.html_code || '';
    document.getElementById('formTitle').innerText = '编辑广告';
    document.getElementById('cancelAdEdit').style.display = 'inline-block';
}
document.getElementById('cancelAdEdit')?.addEventListener('click', function() {
    document.getElementById('adForm').reset();
    document.getElementById('ad_id').value = '';
    document.getElementById('formTitle').innerText = '添加广告';
    this.style.display = 'none';
    document.getElementById('html_code').value = '';
});
</script>
</body>
</html>