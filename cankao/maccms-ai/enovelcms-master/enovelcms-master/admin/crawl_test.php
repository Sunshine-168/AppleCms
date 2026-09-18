<?php
/**
 * 采集规则测试页面
 */
require_once __DIR__ . '/../includes/config.php';
require_once ROOT_PATH . 'includes/Crawler.php';
require_once ROOT_PATH . 'includes/crawl_helper.php';
checkMbstring();
if (!isAdmin()) redirect(BASE_URL . '/admin/login.php');
checkMbstring();
$ruleId = (int)input('rule_id', 0, 'GET');
$rules = $db->fetchAll($db->query("SELECT id, name, rule_key FROM crawl_rules WHERE status=1 ORDER BY id"));
$rule = null; $config = null;
if ($ruleId) {
    $rule = $db->fetch($db->query("SELECT * FROM crawl_rules WHERE id=?", [$ruleId]));
    if ($rule) $config = json_decode($rule['config'], true);
}

function removeSpaces($url) { return str_replace(' ', '', $url); }

$stepReports = [];
if ($rule && isset($_GET['test'])) {
    $crawler = new Crawler($rule['charset']);

    // 步骤1：列表采集
    $t1 = microtime(true);
    $step1 = ['title' => '步骤1：从列表页提取小说ID和标题'];
    try {
        $listUrl = removeSpaces(str_replace('{page}', 1, $config['list_url']));
        $step1['request_url'] = $listUrl;
        $crawler->fetch($listUrl, 30, 2);
        $step1['debug_http_code'] = $crawler->getHttpCode();
        $novels = $crawler->extractIdAndTitle($config['list_area_start'] ?? '', $config['list_area_end'] ?? '', $config['novel_id_start'] ?? '', $config['novel_id_end'] ?? '', $config['novel_title_start'] ?? '', $config['novel_title_end'] ?? '');
        if (empty($novels)) throw new Exception('未提取到任何小说');
        $rand = $novels[array_rand($novels)];
        $step1['data'] = $rand;
        $step1['status'] = 'success';
    } catch (Exception $e) { $step1['status'] = 'error'; $step1['message'] = $e->getMessage(); }
    $step1['time'] = round(microtime(true) - $t1, 3);
    $stepReports[] = $step1;

    // 步骤2：小说信息
    $t2 = microtime(true);
    $step2 = ['title' => '步骤2：采集小说信息'];
    if ($step1['status'] === 'success') {
        try {
            $sid = $step1['data']['id'];
            if (empty($config['info_url_template'])) throw new Exception("未配置详情页URL模板");
            $infoUrl = removeSpaces(Crawler::buildUrl($config['info_url_template'], ['novel_id' => $sid]));
            $step2['request_url'] = $infoUrl;
            $crawler->fetch($infoUrl, 30, 2);
            $is = $config['info_area_start'] ?? ''; $ie = $config['info_area_end'] ?? '';
            $title = $crawler->cutInArea($is, $ie, $config['book_start'], $config['book_end']);
            $author = $crawler->cutInArea($is, $ie, $config['author_start'], $config['author_end']);
            $cat = $crawler->cutInArea($is, $ie, $config['category_start'], $config['category_end']);
            $desc = $crawler->cutInArea($is, $ie, $config['desc_start'], $config['desc_end']);
            $cover = $crawler->cutInArea($is, $ie, $config['cover_start'], $config['cover_end']);
            $status = false;
            if (!empty($config['status_start']) && !empty($config['status_end'])) {
                $statusText = $crawler->cutInArea($is, $ie, $config['status_start'], $config['status_end']);
                foreach (explode(',', $config['status_keywords'] ?? '') as $kw) if (mb_stripos($statusText, trim($kw)) !== false) { $status = true; break; }
            }
            $step2['data'] = ['novel_id' => $sid, 'title' => $title, 'author' => $author, 'category' => $cat, 'status' => $status ? '已完结' : '连载中', 'description' => mb_substr(strip_tags($desc), 0, 100).'...', 'cover' => $cover];
            $step2['status'] = 'success';
        } catch (Exception $e) { $step2['status'] = 'error'; $step2['message'] = $e->getMessage(); }
    } else $step2['status'] = 'skipped';
    $step2['time'] = round(microtime(true) - $t2, 3);
    $stepReports[] = $step2;

    // 步骤3：章节列表
    $t3 = microtime(true);
    $step3 = ['title' => '步骤3：提取分卷与章节列表'];
    if ($step2['status'] === 'success') {
        try {
            $sid = $step2['data']['novel_id'];
            if (empty($config['chapter_list_url'])) throw new Exception("未配置章节列表页URL模板");
            $baseUrl = removeSpaces(Crawler::buildUrl($config['chapter_list_url'], ['novel_id' => $sid]));
            $hasPage = strpos($baseUrl, '{page}') !== false;
            $totalPages = 1;
            $usePagination = false;
            $firstUrl = $hasPage ? str_replace('{page}', 1, $baseUrl) : $baseUrl;
            $step3['request_url'] = $firstUrl;

            if (!empty($config['chapter_total_pages_start']) && !empty($config['chapter_total_pages_end'])) {
                $crawler->fetch($firstUrl, 30, 2);
                $pagesHtml = $crawler->cut($config['chapter_total_pages_start'], $config['chapter_total_pages_end'], false, false);
                if ($pagesHtml && preg_match('/(\d+)/', $pagesHtml, $m)) { $totalPages = (int)$m[1]; $usePagination = true; }
            } elseif ($hasPage) {
                $usePagination = true;
                $totalPages = 2;
                $crawler->fetch($firstUrl, 30, 2);
            } else {
                $crawler->fetch($firstUrl, 30, 2);
            }

            $allVolumes = [];
            for ($p = 1; $p <= $totalPages; $p++) {
                $url = $usePagination ? str_replace('{page}', $p, $baseUrl) : $baseUrl;
                if ($p > 1) $crawler->fetch($url, 30, 2);
                $vd = $crawler->extractVolumesAndChapters($config);
                if ($vd['total_chapters'] == 0 && $p > 1) break;
                foreach ($vd['volumes'] as $vol) {
                    $vn = trim($vol['title']) ?: '默认卷';
                    if (!isset($allVolumes[$vn])) $allVolumes[$vn] = [];
                    foreach ($vol['chapters'] as $ch) {
                        $allVolumes[$vn][] = ['id' => $ch['id'] ?? '', 'title' => $ch['title'] ?? '无标题'];
                    }
                }
                if (!$usePagination) break;
            }
            $volumes = [];
            foreach ($allVolumes as $name => $chs) $volumes[] = ['title' => $name, 'chapters' => $chs];
            $totalCh = array_reduce($volumes, function($c, $v) { return $c + count($v['chapters']); }, 0);
            if ($totalCh == 0) throw new Exception('未提取到任何章节');

            $ctpl = $config['chapter_url_template'] ?? '';
            if (empty($ctpl)) throw new Exception("未配置章节详情页URL模板");
            $flat = [];
            foreach ($volumes as $v) $flat = array_merge($flat, $v['chapters']);
            $sample = [];
            $keys = array_rand($flat, min(3, count($flat)));
            if (!is_array($keys)) $keys = [$keys];
            foreach ($keys as $k) {
                $ch = $flat[$k];
                $chUrl = removeSpaces(Crawler::buildUrl($ctpl, ['novel_id' => $sid, 'chapter_id' => $ch['id'], 'page' => 1]));
                $sample[] = ['id' => $ch['id'], 'title' => $ch['title'], 'url' => $chUrl];
            }
            $step3['data'] = ['volumes' => $volumes, 'total_volumes' => count($volumes), 'total_chapters' => $totalCh, 'sample_chapters' => $sample];
            $step3['status'] = 'success';
        } catch (Exception $e) { $step3['status'] = 'error'; $step3['message'] = $e->getMessage(); }
    } else $step3['status'] = 'skipped';
    $step3['time'] = round(microtime(true) - $t3, 3);
    $stepReports[] = $step3;

    // 步骤4：内容采集
    $t4 = microtime(true);
    $step4 = ['title' => '步骤4：随机章节内容采集'];
    if ($step3['status'] === 'success' && !empty($step3['data']['sample_chapters'])) {
        try {
            $sid = $step2['data']['novel_id'];
            $samples = [];
            foreach ($step3['data']['sample_chapters'] as $ch) {
                $chapterUrlTemplate = $config['chapter_url_template'] ?? '';
                $contentPageTemplate = $config['content_page_url'] ?? '';
                $chapterId = $ch['id'];
                $chapterTitle = $ch['title'];
                $content = '';
                $totalPages = 1;
                $requestUrl = '';

                if (!empty($contentPageTemplate) && strpos($contentPageTemplate, '{page}') !== false) {
                    $firstPageUrl = removeSpaces(str_replace(
                        ['{chapter_url}','{novel_id}','{chapter_id}','{novel_id/1000}','{page}'],
                        [$ch['url'], $sid, $chapterId, floor($sid/1000), 1],
                        $contentPageTemplate
                    ));
                    $requestUrl = $firstPageUrl;
                } elseif (!empty($chapterUrlTemplate) && strpos($chapterUrlTemplate, '{page}') !== false) {
                    $firstPageUrl = removeSpaces(Crawler::buildUrl($chapterUrlTemplate, ['novel_id' => $sid, 'chapter_id' => $chapterId, 'page' => 1]));
                    $requestUrl = $firstPageUrl;
                } else {
                    $requestUrl = $ch['url'];
                }

                try {
                    $crawler->fetch($requestUrl);
                    $content = $crawler->cut($config['content_start'], $config['content_end']);
                    if (!empty($config['content_total_pages_start']) && !empty($config['content_total_pages_end'])) {
                        $pagesHtml = $crawler->cut($config['content_total_pages_start'], $config['content_total_pages_end'], false, false);
                        if ($pagesHtml && preg_match('/(\d+)/', $pagesHtml, $m)) $totalPages = (int)$m[1];
                    }
                } catch (Exception $e) {
                    $content = '';
                    $errorMsg = $e->getMessage();
                }
                $samples[] = [
                    'chapter_id' => $chapterId,
                    'chapter_title' => $chapterTitle,
                    'chapter_url' => $ch['url'],
                    'request_url' => $requestUrl,
                    'content_preview' => empty($content) ? '❌ 采集失败' : mb_substr(strip_tags($content), 0, 200).'...',
                    'length' => mb_strlen($content),
                    'total_pages' => $totalPages,
                    'error' => $errorMsg ?? null
                ];
            }
            $step4['data'] = $samples;
            $step4['status'] = 'success';
        } catch (Exception $e) { $step4['status'] = 'error'; $step4['message'] = $e->getMessage(); }
    } else { $step4['status'] = 'skipped'; $step4['message'] = '无样本章节'; }
    $step4['time'] = round(microtime(true) - $t4, 3);
    $stepReports[] = $step4;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8"><title>采集规则测试</title>
    <link rel="stylesheet" href="../assets/css/admin.css"><link rel="stylesheet" href="/assets/fontawesome/css/all.min.css">
    <style>
        .step-box{background:white;border-radius:12px;border:1px solid var(--gray-200);margin-bottom:1.5rem;padding:1rem 1.5rem;}
        .step-title{font-weight:600;font-size:1.1rem;margin-bottom:0.8rem;}
        .step-time{font-size:0.8rem;color:#888;float:right;}
        .success{color:var(--success);}.error{color:var(--danger);}.skipped{color:#999;}
        .data-table{width:100%;border-collapse:collapse;}
        .data-table th,.data-table td{padding:0.3rem 0.6rem;border:1px solid #ddd;text-align:left;}
        .volume-group{margin-bottom:1rem;}.volume-group h4{background:var(--gray-50);padding:0.3rem 0.8rem;border-radius:6px;}
        .chapter-list{list-style:none;padding-left:1.5rem;}.chapter-list li{font-size:0.9rem;margin-bottom:0.2rem;}
        .url-display{background:#f8f9fa;padding:4px 8px;border-left:3px solid #3b82f6;font-family:monospace;font-size:0.8rem;margin:8px 0;word-break:break-all;}
    </style>
</head>
<body>
<div class="admin-container">
    <?php include 'sidebar.php'; ?>
    <div class="content">
        <h1><i class="fas fa-vial"></i> 采集规则测试</h1>
        <form method="get">
            <select name="rule_id" style="padding:0.5rem;min-width:250px;">
                <option value="">-- 选择规则 --</option>
                <?php foreach($rules as $r): ?><option value="<?=$r['id']?>" <?=$ruleId==$r['id']?'selected':''?>>><?=h($r['name'])?></option><?php endforeach; ?>
            </select>
            <button type="submit" name="test" class="btn btn-primary">开始测试</button>
        </form>
        <?php foreach($stepReports as $i=>$step): ?>
        <div class="step-box">
            <div class="step-title"><span class="<?=$step['status']?>"><?=h($step['title'])?></span><span class="step-time">耗时 <?=$step['time']?> 秒</span></div>
            <?php if(!empty($step['request_url'])): ?><div class="url-display">🌐 实际请求URL：<a href="<?=h($step['request_url'])?>" target="_blank"><?=h($step['request_url'])?></a></div><?php endif; ?>
            <?php if($step['status']==='error'): ?><p class="error">❌ <?=h($step['message'])?></p><?php endif; ?>
            <?php if(isset($step['debug_http_code'])): ?><div><strong>HTTP状态码：</strong><?=$step['debug_http_code']?></div><?php endif; ?>
            <?php if($step['status']==='success' && isset($step['data'])): ?>
                <?php if($i===0): ?>
                    <table class="data-table"><tr><th>小说ID</th><td><?=h($step['data']['id'])?></td></tr><tr><th>标题</th><td><?=h($step['data']['title'])?></td></tr></table>
                <?php elseif($i===1): ?>
                    <table class="data-table">
                        <tr><th>小说ID</th><td><?=h($step['data']['novel_id'])?></td></tr>
                        <tr><th>书名</th><td><?=h($step['data']['title'])?></td></tr>
                        <tr><th>作者</th><td><?=h($step['data']['author'])?></td></tr>
                        <tr><th>分类</th><td><?=h($step['data']['category'])?></td></tr>
                        <tr><th>状态</th><td><?=h($step['data']['status'])?></td></tr>
                        <tr><th>简介</th><td><?=h($step['data']['description'])?></td></tr>
                        <tr><th>封面</th><td><?=h($step['data']['cover'])?></td></tr>
                    </table>
                <?php elseif($i===2): ?>
                    <p>共 <?=$step['data']['total_volumes']?> 个分卷，总计 <?=$step['data']['total_chapters']?> 章</p>
                    <?php foreach($step['data']['volumes'] as $vol): ?>
                    <div class="volume-group">
                        <h4><?=h($vol['title'])?></h4>
                        <ul class="chapter-list">
                        <?php foreach(array_slice($vol['chapters'], 0, 10) as $ch): ?>
                            <li>ID:<?=h($ch['id']?:'?')?> <?=h($ch['title'])?></li>
                        <?php endforeach; ?>
                        <?php if(count($vol['chapters'])>10): ?><li>...共<?=count($vol['chapters'])?>章</li><?php endif; ?>
                        </ul>
                    </div>
                    <?php endforeach; ?>
                <?php elseif($i===3): ?>
                    <?php foreach($step['data'] as $s): ?>
                    <div class="volume-group">
                        <h4>章节ID:<?=h($s['chapter_id']?:'?')?> <?=h($s['chapter_title'])?> (<?=$s['length']?>字)</h4>
                        <?php if($s['error']): ?><p class="error">❌ <?=h($s['error'])?></p><?php endif; ?>
                        <div class="url-display">🌐 <?=h($s['request_url'])?></div>
                        <p><?=nl2br(h($s['content_preview']))?></p>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>
</body>
</html>