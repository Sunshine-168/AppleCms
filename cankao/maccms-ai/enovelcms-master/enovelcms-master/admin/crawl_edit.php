<?php
require_once __DIR__ . '/../includes/config.php';
if (!isAdmin()) redirect(BASE_URL . '/admin/login.php');
verify_admin_csrf();

function generateRuleKey($name) {
    $slug = preg_replace('/[^a-zA-Z0-9_\x{4e00}-\x{9fa5}]+/u', '_', $name);
    $slug = trim($slug, '_');
    if (empty($slug)) $slug = 'rule_' . uniqid();
    return $slug;
}

$message = '';
$error = '';
$ruleId = (int)input('id', 0, 'GET');
$rule = null;
if ($ruleId) {
    $rule = $db->fetch($db->query("SELECT * FROM crawl_rules WHERE id=?", [$ruleId]));
}
$config = [];
if ($rule) {
    $decoded = json_decode($rule['config'], true);
    if (is_array($decoded)) $config = $decoded;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim(input('name', '', 'POST'));
    $site_url = trim(input('site_url', '', 'POST'));
    $charset = input('charset', 'UTF-8', 'POST');
    $configJson = input('config_json', '', 'POST', false);
    $status = isset($_POST['status']) ? 1 : 0;

    if (empty($name)) {
        $error = '规则名称不能为空';
    } else {
        $rule_key = $rule['rule_key'] ?? '';
        if (empty($rule_key) || !$ruleId) {
            $rule_key = generateRuleKey($name);
            $exists = $db->fetch($db->query("SELECT id FROM crawl_rules WHERE rule_key=? AND id!=?", [$rule_key, $ruleId ?: 0]));
            if ($exists) {
                $rule_key .= '_' . uniqid();
            }
        }
        if (empty($configJson) || json_decode($configJson) === null) $configJson = '{}';

        if ($ruleId) {
            $db->query("UPDATE crawl_rules SET rule_key=?, name=?, site_url=?, charset=?, config=?, status=? WHERE id=?",
                [$rule_key, $name, $site_url, $charset, $configJson, $status, $ruleId]);
        } else {
            $db->query("INSERT INTO crawl_rules (rule_key, name, site_url, charset, config, status) VALUES (?,?,?,?,?,?)",
                [$rule_key, $name, $site_url, $charset, $configJson, $status]);
            $ruleId = $db->lastInsertId();
        }
        $message = '保存成功！';
    }
    $rule = $db->fetch($db->query("SELECT * FROM crawl_rules WHERE id=?", [$ruleId]));
    $config = json_decode($rule['config'], true) ?: [];
}

$categories = $db->fetchAll($db->query("SELECT id, name FROM categories ORDER BY sort"));
$configJsonForJs = json_encode($config, JSON_UNESCAPED_UNICODE);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?= $ruleId ? '编辑' : '新增' ?>采集规则</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="/assets/fontawesome/css/all.min.css">
    <style>
        .form-section {
            background: white;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .form-section h3 {
            margin: 0 0 1.2rem 0;
            font-size: 1.1rem;
            font-weight: 600;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 8px;
            border-left: 4px solid #3b82f6;
            padding-left: 0.75rem;
        }
        /* 双列网格布局 */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem 1.5rem;
        }
        .form-grid-full {
            grid-column: span 2;
        }
        .form-field {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
        }
        .form-field label {
            font-weight: 600;
            color: #334155;
            font-size: 0.85rem;
            letter-spacing: 0.3px;
        }
        .form-field input, 
        .form-field select, 
        .form-field textarea {
            padding: 0.5rem 0.75rem;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 0.9rem;
            transition: all 0.2s;
            background-color: #fff;
        }
        .form-field input:focus, 
        .form-field select:focus, 
        .form-field textarea:focus {
            border-color: #3b82f6;
            outline: none;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
        }
        .form-field textarea {
            font-family: monospace;
            resize: vertical;
        }
        .help-text {
            font-size: 0.75rem;
            color: #64748b;
            margin-top: 0.25rem;
        }
        .checkbox-field {
            flex-direction: row;
            align-items: center;
            gap: 0.5rem;
        }
        .checkbox-field label {
            font-weight: normal;
            cursor: pointer;
        }
        .checkbox-field input {
            width: auto;
            margin: 0;
            transform: scale(1.05);
        }
        .hint-box {
            background: #f1f5f9;
            border-left: 4px solid #3b82f6;
            padding: 0.8rem 1rem;
            margin-bottom: 1.5rem;
            border-radius: 0 8px 8px 0;
            font-size: 0.85rem;
            line-height: 1.5;
        }
        .hint-box code {
            background: #e2e8f0;
            padding: 0.2rem 0.4rem;
            border-radius: 4px;
            font-family: monospace;
            font-size: 0.8rem;
        }
        .mapping-row {
            display: flex;
            gap: 0.5rem;
            align-items: center;
            margin-bottom: 0.6rem;
            flex-wrap: wrap;
        }
        .mapping-row input, .mapping-row select {
            flex: 1;
            padding: 0.4rem 0.6rem;
            font-size: 0.9rem;
        }
        .btn-sm {
            padding: 0.25rem 0.8rem;
            font-size: 0.8rem;
        }
        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 0.8rem;
        }
        .export-btn {
            background: #10b981;
            color: white;
            border: none;
        }
        .export-btn:hover {
            background: #059669;
        }
        .btn-outline {
            background: transparent;
            border: 1px solid #cbd5e1;
        }
        .separator {
            margin: 0.5rem 0;
            border-top: 1px dashed #e2e8f0;
            grid-column: span 2;
        }
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
                gap: 0.8rem;
            }
            .form-grid-full {
                grid-column: span 1;
            }
            .form-section {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
<div class="admin-container">
    <?php include 'sidebar.php'; ?>
    <div class="content">
        <div class="action-bar">
            <h1 style="margin:0;"><i class="fas fa-cloud-download-alt"></i> <?= $ruleId ? '编辑' : '新增' ?>采集规则</h1>
        </div>
        <?php if($message): ?><div class="alert" style="margin-bottom:0.8rem;"><?=h($message)?></div><?php endif; ?>
        <?php if($error): ?><div class="error" style="margin-bottom:0.8rem;"><?=h($error)?></div><?php endif; ?>

        <!-- 通配符说明 -->
        <div class="hint-box">
            <strong>通配符说明：</strong><br>
            <code>{num}</code> 纯数字 &nbsp;|&nbsp; <code>{str}</code> 任意非空白字符 &nbsp;|&nbsp; <code>{blank}</code> 空格 &nbsp;|&nbsp; <code>{rn}</code> 换行符<br>
            <strong>动态URL变量：</strong> <code>{novel_id}</code>、<code>{chapter_id}</code>、<code>{novel_id/1000}</code>（取整）、<code>{page}</code>（分页）<br>
            <strong>分页支持：</strong> 在章节列表URL中使用 <code>{page}</code> 变量，并配置总页数提取规则即可自动采集所有分页章节。<br>
            <strong>章节内容分页：</strong> 类似配置 <code>content_page_url</code> 和总页数提取规则，内容将自动合并。
        </div>

        <form method="post" id="mainForm" onsubmit="return saveConfig()">
            <?= csrf_field() ?>
            <input type="hidden" name="config_json" id="configJsonField" value="">

            <!-- 基本信息 -->
            <div class="form-section">
                <h3><i class="fas fa-info-circle"></i> 基本信息</h3>
                <div class="form-grid">
                    <div class="form-field">
                        <label>规则名称 *</label>
                        <input type="text" name="name" value="<?=h($rule['name']??'')?>" required>
                    </div>
                    <div class="form-field">
                        <label>目标网站</label>
                        <input type="text" name="site_url" value="<?=h($rule['site_url']??'')?>" placeholder="https://example.com">
                    </div>
                    <div class="form-field">
                        <label>网页编码</label>
                        <select name="charset">
                            <option value="UTF-8">UTF-8</option>
                            <option value="GBK" <?=($rule['charset']??'')=='GBK'?'selected':''?>>GBK</option>
                        </select>
                    </div>
                    <div class="form-field checkbox-field">
                        <input type="checkbox" name="status" value="1" id="statusCheck" <?=($rule['status']??1)?'checked':''?>>
                        <label for="statusCheck">启用规则</label>
                    </div>
                </div>
            </div>

            <!-- 小说列表采集 -->
            <div class="form-section">
                <h3><i class="fas fa-list"></i> 小说列表采集</h3>
                <div class="form-grid">
                    <div class="form-field form-grid-full">
                        <label>列表页URL模板</label>
                        <input type="text" id="list_url" placeholder="https://example.com/list/{page}.html">
                        <div class="help-text">支持 {page} 变量，如无分页则固定URL</div>
                    </div>
                    <div class="form-field">
                        <label>采集区域开始</label>
                        <input type="text" id="list_area_start">
                    </div>
                    <div class="form-field">
                        <label>采集区域结束</label>
                        <input type="text" id="list_area_end">
                    </div>
                    <div class="form-field">
                        <label>小说ID提取：开始标记</label>
                        <input type="text" id="novel_id_start" placeholder='<a href="/{num}/'>
                    </div>
                    <div class="form-field">
                        <label>小说ID提取：结束标记</label>
                        <input type="text" id="novel_id_end" placeholder='/'>
                    </div>
                    <div class="form-field">
                        <label>小说标题提取：开始标记</label>
                        <input type="text" id="novel_title_start" placeholder='>{str}<'>
                    </div>
                    <div class="form-field">
                        <label>小说标题提取：结束标记</label>
                        <input type="text" id="novel_title_end" placeholder='</a>'>
                    </div>
                    <div class="form-field form-grid-full">
                        <label>小说详情页URL模板</label>
                        <input type="text" id="info_url_template" placeholder="https://example.com/book/{novel_id}/">
                        <div class="help-text">支持 {novel_id}，若留空则无法采集详情</div>
                    </div>
                </div>
            </div>

            <!-- 小说信息提取 -->
            <div class="form-section">
                <h3><i class="fas fa-book"></i> 小说信息提取</h3>
                <div class="form-grid">
                    <div class="form-field">
                        <label>采集区域开始</label>
                        <input type="text" id="info_area_start">
                    </div>
                    <div class="form-field">
                        <label>采集区域结束</label>
                        <input type="text" id="info_area_end">
                    </div>
                    <div class="form-field">
                        <label>书名开始</label>
                        <input type="text" id="book_start">
                    </div>
                    <div class="form-field">
                        <label>书名结束</label>
                        <input type="text" id="book_end">
                    </div>
                    <div class="form-field">
                        <label>作者开始</label>
                        <input type="text" id="author_start">
                    </div>
                    <div class="form-field">
                        <label>作者结束</label>
                        <input type="text" id="author_end">
                    </div>
                    <div class="form-field">
                        <label>分类开始</label>
                        <input type="text" id="category_start">
                    </div>
                    <div class="form-field">
                        <label>分类结束</label>
                        <input type="text" id="category_end">
                    </div>
                    <div class="form-field">
                        <label>简介开始</label>
                        <input type="text" id="desc_start">
                    </div>
                    <div class="form-field">
                        <label>简介结束</label>
                        <input type="text" id="desc_end">
                    </div>
                    <div class="form-field">
                        <label>封面开始</label>
                        <input type="text" id="cover_start">
                    </div>
                    <div class="form-field">
                        <label>封面结束</label>
                        <input type="text" id="cover_end">
                    </div>
                    <div class="form-field">
                        <label>状态开始</label>
                        <input type="text" id="status_start">
                    </div>
                    <div class="form-field">
                        <label>状态结束</label>
                        <input type="text" id="status_end">
                    </div>
                    <div class="form-field">
                        <label>完结关键词</label>
                        <input type="text" id="status_keywords" placeholder="完本,完结">
                    </div>
                    <div class="form-field form-grid-full">
                        <label>分类映射</label>
                        <div id="categoryMapContainer"></div>
                        <button type="button" onclick="addCategoryMap()" class="btn-outline btn-sm" style="width:fit-content; margin-top:6px;">+ 添加映射</button>
                    </div>
                </div>
            </div>

            <!-- 分卷与章节列表采集 -->
            <div class="form-section">
                <h3><i class="fas fa-layer-group"></i> 分卷与章节列表</h3>
                <div class="form-grid">
                    <div class="form-field form-grid-full">
                        <label>章节列表页URL模板</label>
                        <input type="text" id="chapter_list_url" placeholder="https://example.com/book/{novel_id}/list_{page}.html">
                        <div class="help-text">支持{novel_id}和{page}，若含{page}则自动分页采集</div>
                    </div>
                    <div class="form-field">
                        <label>采集区域开始</label>
                        <input type="text" id="chapter_area_start">
                    </div>
                    <div class="form-field">
                        <label>采集区域结束</label>
                        <input type="text" id="chapter_area_end">
                    </div>
                    <div class="form-field">
                        <label>卷名开始标记</label>
                        <input type="text" id="volume_title_start">
                    </div>
                    <div class="form-field">
                        <label>卷名结束标记</label>
                        <input type="text" id="volume_title_end">
                    </div>
                    <div class="form-field">
                        <label>章节ID提取：开始标记</label>
                        <input type="text" id="chapter_id_start" placeholder='href="/read/{num}/'>
                    </div>
                    <div class="form-field">
                        <label>章节ID提取：结束标记</label>
                        <input type="text" id="chapter_id_end" placeholder='"'>
                    </div>
                    <div class="form-field">
                        <label>章节标题提取：开始标记</label>
                        <input type="text" id="chapter_title_start" placeholder='>{str}<'>
                    </div>
                    <div class="form-field">
                        <label>章节标题提取：结束标记</label>
                        <input type="text" id="chapter_title_end" placeholder='</a>'>
                    </div>
                    <div class="form-field form-grid-full">
                        <label>章节详情页URL模板</label>
                        <input type="text" id="chapter_url_template" placeholder="https://example.com/read/{novel_id}/{chapter_id}.html">
                        <div class="help-text">支持 {novel_id}, {chapter_id}</div>
                    </div>
                    <div class="form-field">
                        <label>章节列表总页数开始</label>
                        <input type="text" id="chapter_total_pages_start" placeholder="<div id='pages'>">
                    </div>
                    <div class="form-field">
                        <label>章节列表总页数结束</label>
                        <input type="text" id="chapter_total_pages_end" placeholder="</div>">
                    </div>
                </div>
            </div>

            <!-- 章节内容提取 -->
            <div class="form-section">
                <h3><i class="fas fa-file-alt"></i> 章节内容提取</h3>
                <div class="form-grid">
                    <div class="form-field">
                        <label>内容开始</label>
                        <input type="text" id="content_start">
                    </div>
                    <div class="form-field">
                        <label>内容结束</label>
                        <input type="text" id="content_end">
                    </div>
                    <div class="form-field form-grid-full">
                        <label>内容分页URL模板</label>
                        <input type="text" id="content_page_url" placeholder="{chapter_url}?page={page}">
                        <div class="help-text">支持{chapter_url}和{page}变量</div>
                    </div>
                    <div class="form-field">
                        <label>内容分页总页数开始</label>
                        <input type="text" id="content_total_pages_start">
                    </div>
                    <div class="form-field">
                        <label>内容分页总页数结束</label>
                        <input type="text" id="content_total_pages_end">
                    </div>
                </div>
            </div>

            <!-- 封面处理 -->
            <div class="form-section">
                <h3><i class="fas fa-image"></i> 封面处理</h3>
                <div class="form-grid">
                    <div class="form-field form-grid-full">
                        <label>忽略封面URL特征</label>
                        <input type="text" id="skip_cover_pattern" placeholder="nocover.jpg">
                        <div class="help-text">包含此关键词的封面将被忽略</div>
                    </div>
                </div>
            </div>

            <!-- 内容过滤 -->
            <div class="form-section">
                <h3><i class="fas fa-filter"></i> 内容过滤与防采集</h3>
                <div class="form-grid">
                    <div class="form-field form-grid-full">
                        <label>过滤黑名单（每行一个关键词）</label>
                        <textarea id="filter_blacklist" rows="4" placeholder="每行一个关键词，内容中包含则过滤"></textarea>
                    </div>
                    <div class="form-field form-grid-full">
                        <label>随机插入字符（每行一个，随机插入）</label>
                        <textarea id="random_insert_chars" rows="3" placeholder="每行一个字符或字符串"></textarea>
                    </div>
                </div>
            </div>

            <div style="display: flex; gap: 0.8rem; justify-content: flex-end; margin-top: 1rem;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> 保存规则</button>
                <a href="crawl.php" class="btn btn-outline">返回列表</a>
            </div>
        </form>
    </div>
</div>

<script>
var fieldIds = [
    'list_url', 'list_area_start', 'list_area_end',
    'novel_id_start', 'novel_id_end', 'novel_title_start', 'novel_title_end',
    'info_url_template',
    'info_area_start','info_area_end',
    'book_start','book_end','author_start','author_end','category_start','category_end',
    'desc_start','desc_end','cover_start','cover_end',
    'status_start','status_end','status_keywords',
    'volume_title_start','volume_title_end',
    'chapter_area_start','chapter_area_end',
    'chapter_list_url',
    'chapter_id_start', 'chapter_id_end', 'chapter_title_start', 'chapter_title_end',
    'chapter_url_template',
    'content_start','content_end',
    'skip_cover_pattern',
    'filter_blacklist','random_insert_chars',
    'chapter_total_pages_start','chapter_total_pages_end',
    'content_page_url','content_total_pages_start','content_total_pages_end'
];
var currentConfig = <?= $configJsonForJs ?: '{}' ?>;

function fillForm() {
    for (var i=0; i<fieldIds.length; i++) {
        var key = fieldIds[i];
        var el = document.getElementById(key);
        if (el && currentConfig[key] !== undefined) {
            el.value = currentConfig[key];
        }
    }
    var mappings = currentConfig.category_mapping || {};
    for (var k in mappings) addCategoryMap(k, mappings[k]);
}

window.addCategoryMap = function(from, to) {
    var div = document.createElement('div');
    div.className = 'mapping-row';
    div.innerHTML = '<input type="text" class="map-from" value="'+(from||'')+'" placeholder="采集分类名" style="flex:1">' +
                    '<span>→</span>' +
                    '<select class="map-to" style="flex:1"><option value="">-- 选择本站分类 --</option>' +
                    <?php foreach($categories as $cat): ?>
                    '<option value="<?=$cat['id']?>"><?=h($cat['name'])?></option>' +
                    <?php endforeach; ?>
                    '</select>' +
                    '<button type="button" onclick="this.parentNode.remove()" class="btn-outline btn-sm">删除</button>';
    document.getElementById('categoryMapContainer').appendChild(div);
    if (to) div.querySelector('.map-to').value = to;
};

function getCategoryMapping() {
    var rows = document.querySelectorAll('#categoryMapContainer .mapping-row');
    var map = {};
    rows.forEach(function(r) {
        var from = r.querySelector('.map-from').value.trim();
        var to = r.querySelector('.map-to').value;
        if (from && to) map[from] = to;
    });
    return map;
}

function saveConfig() {
    var config = {};
    for (var i=0; i<fieldIds.length; i++) {
        var key = fieldIds[i];
        var el = document.getElementById(key);
        if (el) config[key] = el.value.trim();
    }
    config.category_mapping = getCategoryMapping();
    document.getElementById('configJsonField').value = JSON.stringify(config);
    return true;
}

fillForm();
</script>
</body>
</html>