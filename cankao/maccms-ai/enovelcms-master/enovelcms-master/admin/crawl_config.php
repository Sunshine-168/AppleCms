<?php
/**
 * EnovelCms采集器配置
 * 管理 API Key 和分类映射
 */
require_once __DIR__ . '/../includes/config.php';

if (!isAdmin()) {
    redirect(BASE_URL . '/admin/login.php');
}
verify_admin_csrf();

$message = '';
$error = '';

// 获取当前配置
$apiKey = getSetting('locoy_api_key', $db) ?: '';
$mapJson = getSetting('locoy_category_map', $db) ?: '{}';
$mapData = json_decode($mapJson, true);
if (!is_array($mapData)) {
    $mapData = ['mappings' => [], 'default_category_id' => 0];
}
if (!isset($mapData['mappings'])) $mapData['mappings'] = [];
if (!isset($mapData['default_category_id'])) $mapData['default_category_id'] = 0;

// 获取所有分类
$categories = $db->fetchAll($db->query("SELECT id, name FROM categories ORDER BY sort"));

// 处理保存
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_api_key'])) {
        $newKey = trim(input('api_key', '', 'POST'));
        $db->query("REPLACE INTO settings (`key`, `value`) VALUES ('locoy_api_key', ?)", [$newKey]);
        $message = 'API Key 已保存';
        $apiKey = $newKey;
    }

    if (isset($_POST['save_mapping'])) {
        $mappings = [];
        if (isset($_POST['mapping_from']) && is_array($_POST['mapping_from'])) {
            foreach ($_POST['mapping_from'] as $i => $from) {
                $from = trim($from);
                $to = (int)($_POST['mapping_to'][$i] ?? 0);
                if (!empty($from) && $to > 0) {
                    $mappings[$from] = $to;
                }
            }
        }
        $defaultCategoryId = (int)($_POST['default_category_id'] ?? 0);
        $newMapData = [
            'mappings' => $mappings,
            'default_category_id' => $defaultCategoryId
        ];
        $db->query("REPLACE INTO settings (`key`, `value`) VALUES ('locoy_category_map', ?)", [json_encode($newMapData)]);
        $message = '分类映射已保存';
        // 刷新数据
        $mapData = $newMapData;
    }

    if (isset($_POST['clear_mapping'])) {
        $db->query("REPLACE INTO settings (`key`, `value`) VALUES ('locoy_category_map', '{\"mappings\":[],\"default_category_id\":0}')");
        $message = '分类映射已清空';
        $mapData = ['mappings' => [], 'default_category_id' => 0];
    }
}

$mappings = $mapData['mappings'] ?? [];
$defaultCategoryId = $mapData['default_category_id'] ?? 0;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EnovelCms采集器配置</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="/assets/fontawesome/css/all.min.css">
    <style>
        .config-card {
            background: white;
            border-radius: 20px;
            border: 1px solid var(--gray-200);
            margin-bottom: 2rem;
            overflow: hidden;
        }
        .config-card-header {
            background: var(--gray-50);
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            font-weight: 600;
        }
        .config-card-body {
            padding: 1.5rem;
        }
        .form-group {
            margin-bottom: 1.2rem;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 0.3rem;
        }
        .form-group input, .form-group select {
            width: 100%;
            max-width: 450px;
            padding: 0.5rem 0.75rem;
            border: 1px solid var(--gray-300);
            border-radius: 8px;
        }
        .mapping-row {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-bottom: 0.6rem;
            flex-wrap: wrap;
        }
        .mapping-row input {
            flex: 1;
            min-width: 150px;
            padding: 0.4rem 0.6rem;
        }
        .mapping-row select {
            flex: 1;
            min-width: 150px;
            padding: 0.4rem 0.6rem;
        }
        .mapping-row .btn-remove {
            background: none;
            border: none;
            color: var(--danger);
            cursor: pointer;
            font-size: 1.2rem;
        }
        .btn-sm {
            padding: 0.3rem 0.8rem;
            font-size: 0.8rem;
        }
        .help-text {
            font-size: 0.8rem;
            color: var(--gray-500);
            margin-top: 0.2rem;
        }

        .doc-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
            margin: 0.8rem 0 1.2rem;
        }
        .doc-table th, .doc-table td {
            border: 1px solid var(--gray-200);
            padding: 0.7rem 0.9rem;
            text-align: left;
            vertical-align: top;
            line-height: 1.6;
        }
        .doc-table th {
            background: var(--gray-50);
            font-weight: 600;
        }
        .doc-table code {
            background: #f1f5f9;
            padding: 0.15rem 0.45rem;
            border-radius: 4px;
            font-size: 0.85rem;
        }
        .doc-example {
            background: #1e293b;
            color: #e2e8f0;
            padding: 1rem;
            border-radius: 8px;
            overflow-x: auto;
            font-family: 'Courier New', monospace;
            font-size: 0.85rem;
            margin: 0.5rem 0 1rem 0;
            line-height: 1.7;
        }
        .doc-example .hljs-comment { color: #94a3b8; }
        .doc-example .hljs-keyword { color: #f472b6; }
        .doc-example .hljs-string { color: #a3e635; }
        .doc-section-title {
            font-weight: 600;
            margin-top: 1.8rem;
            margin-bottom: 0.8rem;
            font-size: 1.05rem;
            color: var(--gray-700);
            padding-bottom: 0.4rem;
            border-bottom: 1px solid #eee;
        }
        .doc-section-title i {
            color: var(--primary);
            margin-right: 8px;
        }
        .global-param-wrap {
            margin: 1rem 0 1.5rem;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 12px;
        }
        .global-param-card {
            background: #f8fafc;
            border-radius: 10px;
            padding: 12px 16px;
            border: 1px solid #e2e8f0;
        }
        .global-param-card .param-name {
            font-weight: 700;
            font-size: 0.95rem;
            margin-bottom: 4px;
        }
        .global-param-card .param-desc {
            font-size: 0.88rem;
            color: #475569;
            line-height: 1.55;
        }
        .param-required-tag {
            color: #dc2626;
            font-weight: bold;
            margin-left: 6px;
        }
        .doc-param-list {
            list-style: none;
            padding-left: 0;
            margin: 0.6rem 0 1.2rem;
        }
        .doc-param-list li {
            padding: 0.4rem 0;
            border-bottom: 1px solid var(--gray-100);
            line-height: 1.6;
        }
        .doc-param-list .param-name {
            font-weight: 600;
            color: var(--gray-700);
        }
        .doc-param-list .param-type {
            color: var(--gray-500);
            font-size: 0.8rem;
        }
        .doc-param-list .param-desc {
            color: var(--gray-600);
        }
        .doc-param-list .param-required {
            color: var(--danger);
            font-weight: 600;
        }
        .doc-param-list .param-optional {
            color: var(--gray-500);
        }
        .config-card-body p {
            line-height: 1.7;
            margin: 0.6rem 0;
        }
        .config-card-body ul {
            line-height: 1.75;
            padding-left: 1.4rem;
            margin: 0.6rem 0 1rem;
        }

        .info-item {
            display: flex;
            align-items: center;
            margin: 1rem 0;
            flex-wrap: wrap;
            gap: 12px;
        }
        .info-label {
            min-width: 110px;
            font-weight: 600;
            font-size: 0.98rem;
            color: #222;
        }
        .info-text {
            flex: 1;
            color: #333;
            line-height: 1.6;
        }
        .download-btn-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 16px;
            background-color: #2563eb;
            color: #fff !important;
            text-decoration: none !important;
            border-radius: 8px;
            transition: background-color 0.2s ease;
        }
        .download-btn-link:hover {
            background-color: #1d4ed8;
        }
        .config-card-body a {
            text-decoration: none;
        }
    </style>
</head>
<body>
<div class="admin-container">
    <?php include 'sidebar.php'; ?>
    <div class="content">
        <h1><i class="fas fa-train"></i> EnovelCms采集器配置</h1>
        <?php if ($message): ?>
            <div class="alert"><?= h($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="error"><?= h($error) ?></div>
        <?php endif; ?>

        <!-- API Key 配置 -->
        <div class="config-card">
            <div class="config-card-header"><i class="fas fa-key"></i> API 密钥</div>
            <div class="config-card-body">
                <form method="post">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label>API Key</label>
                        <input type="text" name="api_key" value="<?= h($apiKey) ?>" placeholder="请设置一个安全密钥">
                        <div class="help-text">采集器发布接口需要使用此密钥进行身份验证，请妥善保管。</div>
                    </div>
                    <button type="submit" name="save_api_key" class="btn btn-primary"><i class="fas fa-save"></i> 保存密钥</button>
                </form>
            </div>
        </div>

        <!-- 分类映射配置 -->
        <div class="config-card">
            <div class="config-card-header"><i class="fas fa-tags"></i> 分类映射</div>
            <div class="config-card-body">
                <form method="post" id="mappingForm">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label>默认分类（未匹配时使用）</label>
                        <select name="default_category_id">
                            <option value="0">-- 不设置（自动创建新分类） --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= $defaultCategoryId == $cat['id'] ? 'selected' : '' ?>>
                                    <?= h($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="help-text">当采集的分类名称不在映射表中时，将归入此分类；若不选，则自动创建新分类。</div>
                    </div>

                    <div class="form-group">
                        <label>映射规则（源分类 → 本站分类）</label>
                        <div id="mappingContainer">
                            <?php foreach ($mappings as $from => $to): ?>
                                <div class="mapping-row">
                                    <input type="text" name="mapping_from[]" value="<?= h($from) ?>" placeholder="采集分类名称">
                                    <select name="mapping_to[]">
                                        <option value="">-- 选择 --</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?= $cat['id'] ?>" <?= $to == $cat['id'] ? 'selected' : '' ?>>
                                                <?= h($cat['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="button" class="btn-remove" onclick="this.parentNode.remove()"><i class="fas fa-times-circle"></i></button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" id="addMappingBtn" class="btn-outline btn-sm"><i class="fas fa-plus"></i> 添加映射</button>
                    </div>

                    <div style="display: flex; gap: 10px; margin-top: 1rem;">
                        <button type="submit" name="save_mapping" class="btn btn-primary"><i class="fas fa-save"></i> 保存映射</button>
                        <button type="submit" name="clear_mapping" class="btn btn-outline" onclick="return confirm('确定清空所有映射规则？')"><i class="fas fa-trash"></i> 清空映射</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="config-card">
            <div class="config-card-header"><i class="fas fa-info-circle"></i> 发布接口介绍</div>
            <div class="config-card-body">
                <div class="info-item">
                    <div class="info-label">适配软件：</div>
                    <div class="info-text">Enovelcms采集器、火车头采集器、蓝天采集器以及其他可以利用接口发布的采集器</div>
                </div>
                <div class="info-item">
                    <div class="info-label">下载地址：</div>
                    <div class="info-text">
                        <a href="https://www.enovelcms.cn" target="_blank" class="download-btn-link">
                            <i class="fas fa-download"></i> 到官网下载EnovelCms采集器
                        </a>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">分类映射说明：</div>
                    <div class="info-text">发布小说时传入 <code>category</code> 字段，系统将根据映射规则自动转换；若未映射且设置了默认分类，则归入默认分类；否则自动创建新分类。</div>
                </div>
            </div>
        </div>

        <!-- ===== 接口使用文档 ===== -->
        <div class="config-card">
            <div class="config-card-header"><i class="fas fa-book"></i> 接口使用文档</div>
            <div class="config-card-body">
                <p><strong>接口地址：</strong> <code><?= BASE_URL ?>/api/crawler/publish.php</code></p>
                <p><strong>请求方式：</strong> POST（支持 application/x-www-form-urlencoded 或 multipart/form-data）</p>

                <p style="font-weight:600; font-size:1rem; margin-top:1rem;">通用请求参数（所有接口必须携带）：</p>
                <div class="global-param-wrap">
                    <div class="global-param-card">
                        <div class="param-name">
                            <code>api_key</code>
                            <span class="param-required-tag">（必填）</span>
                        </div>
                        <div class="param-desc">在「API密钥」配置项中设置的密钥，用于接口身份校验。</div>
                    </div>
                    <div class="global-param-card">
                        <div class="param-name">
                            <code>action</code>
                            <span class="param-required-tag">（必填）</span>
                        </div>
                        <div class="param-desc">接口操作类型，不同取值对应不同功能，下方列出全部可用值。</div>
                    </div>
                </div>

                <div class="doc-section-title"><i class="fas fa-search"></i> 1. check_novel – 检测小说是否存在</div>
                <table class="doc-table">
                    <tr><th style="width:120px;">参数</th><th>说明</th></tr>
                    <tr><td><code>title</code></td><td>小说标题 <span class="param-required-tag">必填</span></td></tr>
                    <tr><td><code>author</code></td><td>作者 <span class="param-required-tag">必填</span></td></tr>
                </table>
                <div class="doc-example">
                    <span class="hljs-comment">// 请求示例</span><br>
                    action=check_novel&api_key=xxxx&title=凡人修仙传&author=忘语
                </div>
                <div class="doc-example">
                    <span class="hljs-comment">// 返回示例</span><br>
                    {<br>
                    &nbsp;&nbsp;"code": 1,<br>
                    &nbsp;&nbsp;"message": "查询成功",<br>
                    &nbsp;&nbsp;"data": {<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;"exists": true,<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;"novel_id": 123<br>
                    &nbsp;&nbsp;}<br>
                    }
                </div>

                <div class="doc-section-title"><i class="fas fa-list"></i> 2. list_chapters – 获取小说所有章节</div>
                <table class="doc-table">
                    <tr><th style="width:120px;">参数</th><th>说明</th></tr>
                    <tr><td><code>novel_id</code></td><td>本站小说ID <span class="param-required-tag">必填</span></td></tr>
                </table>
                <div class="doc-example">
                    <span class="hljs-comment">// 请求示例</span><br>
                    action=list_chapters&api_key=xxxx&novel_id=123
                </div>
                <div class="doc-example">
                    <span class="hljs-comment">// 返回示例</span><br>
                    {<br>
                    &nbsp;&nbsp;"code": 1,<br>
                    &nbsp;&nbsp;"message": "获取成功",<br>
                    &nbsp;&nbsp;"data": [<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;{<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"id": 1,<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"title": "第一章 入山",<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"sort": 1,<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"source_url": "http://...",<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"source_chapter_id": "c123",<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"volume_title": "第一卷"<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;}<br>
                    &nbsp;&nbsp;]<br>
                    }
                </div>

                <div class="doc-section-title"><i class="fas fa-plus-circle"></i> 3. add_novel – 添加或更新小说</div>
                <table class="doc-table">
                    <tr><th style="width:120px;">参数</th><th>说明</th></tr>
                    <tr><td><code>title</code></td><td>小说标题 <span class="param-required-tag">必填</span></td></tr>
                    <tr><td><code>author</code></td><td>作者 <span class="param-required-tag">必填</span></td></tr>
                    <tr><td><code>category</code></td><td>分类名称（可选，自动映射或创建）</td></tr>
                    <tr><td><code>description</code></td><td>小说简介（可选）</td></tr>
                    <tr><td><code>status</code></td><td>状态：0=连载中，1=已完结（可选，默认0）</td></tr>
                    <tr><td><code>source_id</code></td><td>源站小说ID（可选，用于去重）</td></tr>
                    <tr><td><code>cover</code></td><td>封面文件（multipart/form-data 上传）</td></tr>
                </table>
                <p><strong>逻辑说明：</strong> 若标题+作者已存在，则更新信息；否则创建新小说。</p>
                <div class="doc-example">
                    <span class="hljs-comment">// 请求示例（URL编码）</span><br>
                    action=add_novel&api_key=xxxx&title=凡人修仙传&author=忘语&category=仙侠&description=一个普通山村小子...
                </div>
                <div class="doc-example">
                    <span class="hljs-comment">// 返回示例</span><br>
                    {<br>
                    &nbsp;&nbsp;"code": 1,<br>
                    &nbsp;&nbsp;"message": "小说发布成功",<br>
                    &nbsp;&nbsp;"data": { "novel_id": 123 }<br>
                    }
                </div>

                <div class="doc-section-title"><i class="fas fa-pen"></i> 4. update_novel – 更新小说信息</div>
                <table class="doc-table">
                    <tr><th style="width:120px;">参数</th><th>说明</th></tr>
                    <tr><td><code>novel_id</code></td><td>本站小说ID <span class="param-required-tag">必填</span></td></tr>
                    <tr><td><code>title</code></td><td>新标题（可选）</td></tr>
                    <tr><td><code>author</code></td><td>新作者（可选）</td></tr>
                    <tr><td><code>category</code></td><td>新分类（可选）</td></tr>
                    <tr><td><code>description</code></td><td>新简介（可选）</td></tr>
                    <tr><td><code>status</code></td><td>新状态（可选）</td></tr>
                    <tr><td><code>source_id</code></td><td>源站ID（可选）</td></tr>
                    <tr><td><code>cover</code></td><td>新封面文件（multipart/form-data）</td></tr>
                </table>
                <div class="doc-example">
                    <span class="hljs-comment">// 请求示例</span><br>
                    action=update_novel&api_key=xxxx&novel_id=123&status=1
                </div>

                <div class="doc-section-title"><i class="fas fa-file-alt"></i> 5. add_chapter – 添加章节</div>
                <table class="doc-table">
                    <tr><th style="width:120px;">参数</th><th>说明</th></tr>
                    <tr><td><code>novel_id</code></td><td>本站小说ID（若提供则优先使用）</td></tr>
                    <tr><td><code>novel_title</code></td><td>小说标题（当 novel_id 未提供时，与 author 联合查找）</td></tr>
                    <tr><td><code>author</code></td><td>作者（当 novel_id 未提供时，与 title 联合查找）</td></tr>
                    <tr><td><code>volume</code></td><td>分卷名称（可选，默认“默认卷”）</td></tr>
                    <tr><td><code>chapter_title</code></td><td>章节标题 <span class="param-required-tag">必填</span></td></tr>
                    <tr><td><code>content</code></td><td>章节内容 <span class="param-required-tag">必填</span></td></tr>
                    <tr><td><code>sort</code></td><td>排序序号（可选，自动追加到最后）</td></tr>
                    <tr><td><code>source_url</code></td><td>源站章节URL（可选）</td></tr>
                    <tr><td><code>source_chapter_id</code></td><td>源站章节ID（可选，用于去重）</td></tr>
                </table>
                <p><strong>去重逻辑：</strong> 如果提供 <code>source_chapter_id</code> 且已存在相同小说ID+source_chapter_id，则返回错误，避免重复添加。</p>
                <div class="doc-example">
                    <span class="hljs-comment">// 请求示例</span><br>
                    action=add_chapter&api_key=xxxx&novel_id=123&chapter_title=第二章&content=第二章内容...
                </div>
                <div class="doc-example">
                    <span class="hljs-comment">// 返回示例</span><br>
                    {<br>
                    &nbsp;&nbsp;"code": 1,<br>
                    &nbsp;&nbsp;"message": "章节发布成功",<br>
                    &nbsp;&nbsp;"data": { "chapter_id": 456 }<br>
                    }
                </div>

                <div class="doc-section-title"><i class="fas fa-exclamation-triangle"></i> 错误码说明</div>
                <ul>
                    <li><code>code: 0</code> – 操作失败，查看 <code>message</code> 字段获取错误信息。</li>
                    <li><code>code: 1</code> – 操作成功。</li>
                </ul>

                <div class="doc-section-title"><i class="fas fa-code"></i> 通用返回格式</div>
                <div class="doc-example">
                    {<br>
                    &nbsp;&nbsp;"code": 0 或 1,<br>
                    &nbsp;&nbsp;"message": "描述信息",<br>
                    &nbsp;&nbsp;"data": { ... }  // 可选，具体内容依 action 而定<br>
                    }
                </div>

                <p><strong>注意事项：</strong></p>
                <ul>
                    <li>所有文本参数需进行 URL 编码（如中文、特殊字符）。</li>
                    <li>封面文件上传需使用 <code>multipart/form-data</code> 格式，字段名固定为 <code>cover</code>。</li>
                    <li>建议每次请求前先调用 <code>check_novel</code> 避免重复创建。</li>
                    <li>接口支持同时接收 <code>application/x-www-form-urlencoded</code> 和 <code>multipart/form-data</code> 两种 Content-Type。</li>
                </ul>
            </div>
        </div>
        <!-- ===== 文档结束 ===== -->

    </div>
</div>

<script>
document.getElementById('addMappingBtn').addEventListener('click', function() {
    const container = document.getElementById('mappingContainer');
    const row = document.createElement('div');
    row.className = 'mapping-row';
    row.innerHTML = `
        <input type="text" name="mapping_from[]" placeholder="采集分类名称">
        <select name="mapping_to[]">
            <option value="">-- 选择 --</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>"><?= h($cat['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="button" class="btn-remove" onclick="this.parentNode.remove()"><i class="fas fa-times-circle"></i></button>
    `;
    container.appendChild(row);
});
</script>
</body>
</html>