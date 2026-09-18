<?php
/**
 * 单篇/批量采集脚本
 * 功能：支持单个ID、ID范围（1-100）、逗号分隔列表（1,5,20）
 */
require_once __DIR__ . '/../includes/config.php';
require_once ROOT_PATH . 'includes/Crawler.php';
require_once ROOT_PATH . 'includes/crawl_helper.php';
checkMbstring();
if (!isAdmin()) redirect(BASE_URL . '/admin/login.php');

$step = input('step', 'form', 'GET');
$ruleId = (int)input('rule_id', 2, 'GET');
$rule = $db->fetch($db->query("SELECT * FROM crawl_rules WHERE id=?", [$ruleId]));
if (!$rule) die('规则不存在');
$config = json_decode($rule['config'], true);
$GLOBALS['crawl_rule_config'] = $config;

$queueDir = ROOT_PATH . 'data/crawler/';
if (!is_dir($queueDir)) mkdir($queueDir, 0755, true);

function getCategoryId($catName, $config, $db) {
    if (empty($catName)) return 0;
    if (isset($config['category_mapping'][$catName])) return (int)$config['category_mapping'][$catName];
    $first = $db->fetch($db->query("SELECT id FROM categories ORDER BY sort ASC LIMIT 1"));
    return $first ? (int)$first['id'] : 0;
}

function getVolumeId($db, $nid, $volumeTitle) {
    $vol = $db->fetch($db->query("SELECT id FROM volumes WHERE novel_id=? AND title=?", [$nid, $volumeTitle]));
    if ($vol) return $vol['id'];
    $db->query("INSERT INTO volumes (novel_id, title) VALUES (?,?)", [$nid, $volumeTitle]);
    return $db->lastInsertId();
}

function parseIdRange($input) {
    $ids = [];
    $input = trim($input);
    if (empty($input)) return [];
    if (strpos($input, '-') !== false) {
        list($start, $end) = explode('-', $input, 2);
        $start = (int)$start;
        $end = (int)$end;
        if ($start > 0 && $end >= $start) {
            for ($i = $start; $i <= $end; $i++) $ids[] = $i;
        }
    } elseif (strpos($input, ',') !== false) {
        $parts = explode(',', $input);
        foreach ($parts as $p) {
            $p = (int)trim($p);
            if ($p > 0) $ids[] = $p;
        }
    } else {
        $id = (int)$input;
        if ($id > 0) $ids[] = $id;
    }
    return array_unique($ids);
}

if ($step === 'form') {
    ?>
    <!DOCTYPE html>
    <html>
    <head><meta charset="UTF-8"><title>单篇/批量采集</title><link rel="stylesheet" href="../assets/css/admin.css"><link rel="stylesheet" href="/assets/fontawesome/css/all.min.css">
    <style>
        .crawl-form { max-width: 700px; margin: 2rem auto; background: white; border-radius: 20px; padding: 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 0.5rem; }
        .form-group input, .form-group select { width: 100%; padding: 0.6rem 1rem; border: 1px solid #cbd5e1; border-radius: 12px; }
        .btn-group { display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem; }
    </style>
    </head>
    <body>
    <div class="admin-container"><?php include 'sidebar.php'; ?>
        <div class="content">
            <h1><i class="fas fa-cloud-download-alt"></i> 单篇/批量采集</h1>
            <div class="crawl-form">
                <form method="get" action="">
                    <input type="hidden" name="step" value="batch_start">
                    <input type="hidden" name="rule_id" value="<?= $ruleId ?>">
                    <div class="form-group">
                        <label>采集规则</label>
                        <select name="rule_id" onchange="this.form.submit()">
                            <?php
                            $rules = $db->fetchAll($db->query("SELECT id, name FROM crawl_rules ORDER BY id"));
                            foreach ($rules as $r) {
                                $selected = ($r['id'] == $ruleId) ? 'selected' : '';
                                echo "<option value='{$r['id']}' $selected>" . h($r['name']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>小说ID（支持范围或列表）</label>
                        <input type="text" name="novel_ids" placeholder="示例: 1001 或 1-100 或 1,5,20" required>
                    </div>
                    <div class="btn-group">
                        <a href="crawl.php" class="btn-outline">返回规则列表</a>
                        <button type="submit" class="btn-primary">开始批量采集</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    </body>
    </html>
    <?php
    exit;
}

if ($step === 'batch_start') {
    $rawIds = input('novel_ids', '', 'GET');
    $idList = parseIdRange($rawIds);
    if (empty($idList)) die('<div class="alert alert-danger">无效的ID范围，请返回重试</div>');
    $_SESSION['batch_novel_ids'] = $idList;
    $_SESSION['batch_current_index'] = 0;
    redirectDelay("single_crawl.php?step=batch_next&rule_id={$ruleId}", 1, "准备开始采集共 " . count($idList) . " 部小说");
}

if ($step === 'batch_next') {
    $idList = $_SESSION['batch_novel_ids'] ?? [];
    $index = $_SESSION['batch_current_index'] ?? 0;
    if ($index >= count($idList)) {
        unset($_SESSION['batch_novel_ids'], $_SESSION['batch_current_index']);
        echo "<div class='alert alert-success'>🎉 所有小说采集任务已完成！</div>";
        echo '<a href="crawl.php" class="btn btn-primary">返回规则列表</a>';
        exit;
    }
    $sourceNovelId = $idList[$index];
    $_SESSION['current_batch_novel_id'] = $sourceNovelId;
    $total = count($idList);
    $percent = round(($index / $total) * 100);
    echo "<div class='progress-box'><div class='progress-bar' style='width:{$percent}%; background:#3b82f6; height:8px; border-radius:4px;'></div>";
    echo "<p>批量进度: {$index}/{$total} (已完成 {$percent}%)</p>";
    echo "<p>当前采集小说ID: {$sourceNovelId}</p></div>";
    redirectDelay("single_crawl.php?step=info&source_id={$sourceNovelId}&rule_id={$ruleId}", 2, "开始采集小说信息");
    exit;
}

if ($step === 'info') {
    $sourceNovelId = (int)input('source_id', 0, 'GET');
    if (!$sourceNovelId) die('缺少小说ID');
    $crawler = new Crawler($rule['charset']);
    echo "<h2>📖 正在采集小说信息 (源站ID: {$sourceNovelId})</h2>";
    
    if (empty($config['info_url_template'])) die('未配置小说详情页URL模板');
    $infoUrl = removeSpacesFromUrl(Crawler::buildUrl($config['info_url_template'], ['novel_id' => $sourceNovelId]));
    echo "<p>详情页URL：<a href='{$infoUrl}' target='_blank'>{$infoUrl}</a></p>";
    
    try {
        fetchWithRetry($crawler, $infoUrl, 3, 10);
        $infoAreaStart = $config['info_area_start'] ?? '';
        $infoAreaEnd   = $config['info_area_end'] ?? '';
        
        $title   = $crawler->cutInArea($infoAreaStart, $infoAreaEnd, $config['book_start'], $config['book_end']);
        $author  = $crawler->cutInArea($infoAreaStart, $infoAreaEnd, $config['author_start'], $config['author_end']);
        $catName = $crawler->cutInArea($infoAreaStart, $infoAreaEnd, $config['category_start'], $config['category_end']);
        $desc    = $crawler->cutInArea($infoAreaStart, $infoAreaEnd, $config['desc_start'], $config['desc_end']);
        $coverUrl= $crawler->cutInArea($infoAreaStart, $infoAreaEnd, $config['cover_start'], $config['cover_end']);
        $desc = cleanCrawledContent($desc, $config, false);
        $statusText = '';
        if (!empty($config['status_start']) && !empty($config['status_end'])) {
            $statusText = $crawler->cutInArea($infoAreaStart, $infoAreaEnd, $config['status_start'], $config['status_end']);
        }
        $novelStatus = 0;
        $keywords = array_filter(array_map('trim', explode(',', $config['status_keywords'] ?? '')));
        foreach ($keywords as $kw) if (mb_stripos($statusText, $kw) !== false) { $novelStatus = 1; break; }
        $catId = getCategoryId($catName, $config, $db);
        
        echo "<table class='data-table'>";
        echo "<tr><th>书名</th><td>" . h($title) . "</td></tr>";
        echo "<tr><th>作者</th><td>" . h($author) . "</td></tr>";
        echo "<tr><th>分类</th><td>" . h($catName) . "</td></tr>";
        echo "<tr><th>简介（清理后）</th><td>" . h(mb_substr($desc, 0, 200)) . "…</td></tr>";
        echo "<tr><th>封面URL</th><td><a href='{$coverUrl}' target='_blank'>{$coverUrl}</a></td></tr>";
        echo "</table>";
        
        $exist = $db->fetch($db->query("SELECT id, cover FROM novels WHERE title=? AND author=?", [$title, $author]));
        if ($exist) {
            $nid = $exist['id'];
            echo "<div class='alert alert-info'>小说已存在（本站ID：{$nid}），将只采集新章节。</div>";
            
            $currentCover = $exist['cover'];
            $isDefaultCover = empty($currentCover) || $currentCover === 'assets/images/default_cover.jpg' || strpos($currentCover, 'default_cover') !== false;
            if ($isDefaultCover && !empty($coverUrl)) {
                echo "<div>当前封面为默认图片，尝试更新封面...</div>";
                $coverError = '';
                $newCover = downloadImage($coverUrl, $nid, $coverError, $config);
                if ($newCover && file_exists(ROOT_PATH . $newCover)) {
                    $db->query("UPDATE novels SET cover=? WHERE id=?", [$newCover, $nid]);
                    echo "<div class='alert alert-success'>✅ 封面已更新为：{$newCover}</div>";
                } else {
                    echo "<div class='alert alert-warning'>❌ 封面更新失败：{$coverError}</div>";
                }
            }
        } else {
            $tempCoverPath = 'assets/images/default_cover.jpg';
            $db->query("INSERT INTO novels (title, author, category_id, cover, description, status) VALUES (?,?,?,?,?,?)",
                [$title, $author, $catId, $tempCoverPath, $desc, $novelStatus]);
            $nid = $db->lastInsertId();
            echo "<div class='alert alert-success'>新增小说记录（ID:{$nid}），默认封面已设置。</div>";
            
            if (!empty($coverUrl)) {
                $coverError = '';
                $newCover = downloadImage($coverUrl, $nid, $coverError, $config);
                if ($newCover && file_exists(ROOT_PATH . $newCover)) {
                    $db->query("UPDATE novels SET cover=? WHERE id=?", [$newCover, $nid]);
                    echo "<div class='alert alert-success'>✅ 封面下载并保存成功：{$newCover}</div>";
                } else {
                    echo "<div class='alert alert-warning'>❌ 封面下载失败：{$coverError}，已保留默认封面。</div>";
                }
            }
        }
        
        $tempInfoFile = $queueDir . "temp_{$nid}_source.txt";
        file_put_contents($tempInfoFile, $sourceNovelId);
        $_SESSION['current_nid'] = $nid;
        $_SESSION['current_source_novel_id'] = $sourceNovelId;
        
        redirectDelay("single_crawl.php?step=build_queue&rule_id={$ruleId}", 2, "小说信息处理完成，开始生成章节任务队列");
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>小说信息采集失败：{$e->getMessage()}</div>";
        $_SESSION['batch_current_index'] = ($_SESSION['batch_current_index'] ?? 0) + 1;
        redirectDelay("single_crawl.php?step=batch_next&rule_id={$ruleId}", 3, "采集失败，跳过此小说");
    }
    exit;
}

if ($step === 'build_queue') {
    $nid = $_SESSION['current_nid'] ?? 0;
    $sourceNovelId = $_SESSION['current_source_novel_id'] ?? 0;
    if (!$nid || !$sourceNovelId) die('缺少必要参数');
    $crawler = new Crawler($rule['charset']);
    echo "<h2>📑 生成章节任务队列 (本站ID:{$nid}, 源站ID:{$sourceNovelId})</h2>";
    
    try {
        $chapterListUrlTemplate = $config['chapter_list_url'] ?? '';
        if (empty($chapterListUrlTemplate)) throw new Exception("未配置章节列表页URL模板");
        $chapterListBaseUrl = str_replace('{novel_id/1000}', floor($sourceNovelId / 1000), $chapterListUrlTemplate);
        $chapterListBaseUrl = removeSpacesFromUrl(Crawler::buildUrl($chapterListBaseUrl, ['novel_id' => $sourceNovelId]));
        $hasPageVar = strpos($chapterListBaseUrl, '{page}') !== false;
        $totalPages = 1;
        $usePagination = false;
        $firstPageUrl = $hasPageVar ? str_replace('{page}', 1, $chapterListBaseUrl) : $chapterListBaseUrl;
        echo "<p>章节列表第一页URL: <a href='{$firstPageUrl}' target='_blank'>{$firstPageUrl}</a></p>";
        
        fetchWithRetry($crawler, $firstPageUrl, 3, 10);
        if (!empty($config['chapter_total_pages_start']) && !empty($config['chapter_total_pages_end'])) {
            $pagesHtml = $crawler->cut($config['chapter_total_pages_start'], $config['chapter_total_pages_end'], false, false);
            if (!empty($pagesHtml) && preg_match('/(\d+)/', $pagesHtml, $pageMatch)) {
                $totalPages = (int)$pageMatch[1];
                $usePagination = true;
                echo "<div class='alert alert-info'>检测到章节列表分页，总页数：{$totalPages}</div>";
            }
        } elseif ($hasPageVar) {
            $usePagination = true;
            $totalPages = 20;
            echo "<div class='alert alert-warning'>未配置总页数规则，将尝试最多 {$totalPages} 页。</div>";
        }
        
        $allChapters = [];
        for ($p = 1; $p <= $totalPages; $p++) {
            $pageUrl = $usePagination ? str_replace('{page}', $p, $chapterListBaseUrl) : $chapterListBaseUrl;
            if ($p > 1) {
                echo "<div>正在采集第 {$p} 页章节列表...</div>";
                fetchWithRetry($crawler, $pageUrl, 3, 10);
            }
            $volumeData = $crawler->extractVolumesAndChapters($config);
            if ($volumeData['total_chapters'] == 0 && $p > 1) break;
            foreach ($volumeData['volumes'] as $vol) {
                $volTitle = trim($vol['title']) ?: '默认卷';
                if (!isset($allChapters[$volTitle])) $allChapters[$volTitle] = [];
                foreach ($vol['chapters'] as $ch) {
                    $allChapters[$volTitle][] = [
                        'id' => $ch['id'] ?? '',
                        'title' => $ch['title'] ?? '无标题'
                    ];
                }
            }
            if (!$usePagination) break;
        }
        if (empty($allChapters)) throw new Exception("未提取到任何章节");
        
        $chapterUrlTemplate = $config['chapter_url_template'] ?? '';
        if (empty($chapterUrlTemplate)) throw new Exception("未配置章节详情页URL模板");
        
        $existing = $db->fetchAll($db->query("SELECT source_url FROM chapters WHERE novel_id=?", [$nid]));
        $existingUrls = [];
        foreach ($existing as $e) if (!empty($e['source_url'])) $existingUrls[$e['source_url']] = true;
        
        $queueFile = $queueDir . "batch_queue_{$nid}.json";
        $tasks = [];
        $newCount = 0;
        foreach ($allChapters as $volTitle => $chapters) {
            foreach ($chapters as $ch) {
                $chapterId = $ch['id'];
                $chTitle = trim($ch['title']) ?: '章节';
                $tempUrl = str_replace('{novel_id/1000}', floor($sourceNovelId / 1000), $chapterUrlTemplate);
                $chapterUrl = removeSpacesFromUrl(Crawler::buildUrl($tempUrl, [
                    'novel_id' => $sourceNovelId,
                    'chapter_id' => $chapterId,
                    'page' => 1
                ]));
                if (isset($existingUrls[$chapterUrl])) continue;
                $tasks[] = [
                    'url' => $chapterUrl,
                    'title' => $chTitle,
                    'volume' => $volTitle,
                    'source_chapter_id' => $chapterId,
                    'retry_count' => 0,
                    'next_retry_time' => 0,
                    'failed' => false
                ];
                $newCount++;
            }
        }
        if ($newCount == 0) {
            echo "<div class='alert alert-info'>没有新章节，跳过此小说。</div>";
            @unlink($queueDir . "temp_{$nid}_source.txt");
            $_SESSION['batch_current_index']++;
            redirectDelay("single_crawl.php?step=batch_next&rule_id={$ruleId}", 2, "无新章节，继续下一部");
        } else {
            file_put_contents($queueFile, json_encode($tasks, JSON_PRETTY_PRINT));
            echo "<div class='alert alert-success'>发现 {$newCount} 个新章节，已生成任务队列。</div>";
            redirectDelay("single_crawl.php?step=process_chapter&rule_id={$ruleId}", 2, "开始采集章节内容");
        }
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>章节列表提取失败：{$e->getMessage()}</div>";
        $_SESSION['batch_current_index']++;
        redirectDelay("single_crawl.php?step=batch_next&rule_id={$ruleId}", 3, "章节列表失败，跳过此小说");
    }
    exit;
}

if ($step === 'process_chapter') {
    $nid = $_SESSION['current_nid'] ?? 0;
    $sourceNovelId = $_SESSION['current_source_novel_id'] ?? 0;
    if (!$nid || !$sourceNovelId) die('小说ID丢失');
    $queueFile = $queueDir . "batch_queue_{$nid}.json";
    if (!file_exists($queueFile)) {
        $_SESSION['batch_current_index']++;
        @unlink($queueDir . "temp_{$nid}_source.txt");
        redirectDelay("single_crawl.php?step=batch_next&rule_id={$ruleId}", 2, "本小说章节采集完成，继续下一部");
    }
    
    $tasks = json_decode(file_get_contents($queueFile), true);
    if (empty($tasks)) {
        @unlink($queueFile);
        $_SESSION['batch_current_index']++;
        redirectDelay("single_crawl.php?step=batch_next&rule_id={$ruleId}", 2, "队列为空，继续下一部");
    }
    
    $now = time();
    $indexTask = -1;
    foreach ($tasks as $idx => $task) {
        if (!$task['failed'] && $task['next_retry_time'] <= $now) {
            $indexTask = $idx;
            break;
        }
    }
    if ($indexTask === -1) {
        $minWait = PHP_INT_MAX;
        foreach ($tasks as $task) {
            if (!$task['failed'] && $task['next_retry_time'] > $now) {
                $wait = $task['next_retry_time'] - $now;
                if ($wait < $minWait) $minWait = $wait;
            }
        }
        if ($minWait == PHP_INT_MAX) $minWait = 10;
        echo "<div class='progress-message'>⏳ 所有章节都在等待重试，自动等待 {$minWait} 秒后继续...</div>";
        redirectDelay("single_crawl.php?step=process_chapter&rule_id={$ruleId}", $minWait, "等待重试队列");
    }
    
    $task = $tasks[$indexTask];
    echo "<h3>📖 采集章节：{$task['title']}</h3>";
    echo "<table class='data-table'>";
    echo "<tr><th>章节URL</th><td><a href='{$task['url']}' target='_blank'>{$task['url']}</a></td></tr>";
    echo "<tr><th>重试次数</th><td>{$task['retry_count']}</td></tr>";
    echo "</table>";
    
    $volumeId = getVolumeId($db, $nid, $task['volume']);
    $crawler = new Crawler($rule['charset']);
    $success = false;
    
    try {
        fetchWithRetry($crawler, $task['url'], 3, 10);
        $content = '';
        $contentPageUrlTemplate = $config['content_page_url'] ?? '';
        if (empty($contentPageUrlTemplate) && !empty($config['chapter_url_template']) && strpos($config['chapter_url_template'], '{page}') !== false) {
            $contentPageUrlTemplate = $config['chapter_url_template'];
        }
        $hasPageVar = !empty($contentPageUrlTemplate) && strpos($contentPageUrlTemplate, '{page}') !== false;
        
        if ($hasPageVar) {
            $tempUrl = str_replace('{novel_id/1000}', floor($sourceNovelId / 1000), $contentPageUrlTemplate);
            $firstPageUrl = str_replace(['{chapter_url}', '{novel_id}', '{chapter_id}', '{page}'], [$task['url'], $sourceNovelId, $task['source_chapter_id'], 1], $tempUrl);
            echo "<div>内容第1页URL: <a href='{$firstPageUrl}' target='_blank'>{$firstPageUrl}</a></div>";
            $crawler->fetch($firstPageUrl, 30, 0);
            $content = $crawler->cut($config['content_start'], $config['content_end']);
            $totalContentPages = 1;
            if (!empty($config['content_total_pages_start']) && !empty($config['content_total_pages_end'])) {
                $pagesHtml = $crawler->cut($config['content_total_pages_start'], $config['content_total_pages_end'], false, false);
                if ($pagesHtml && preg_match('/(\d+)/', $pagesHtml, $m)) $totalContentPages = (int)$m[1];
            } else {
                $totalContentPages = 10;
            }
            for ($p = 2; $p <= $totalContentPages; $p++) {
                $pageUrl = str_replace(['{chapter_url}', '{novel_id}', '{chapter_id}', '{page}'], [$task['url'], $sourceNovelId, $task['source_chapter_id'], $p], $tempUrl);
                echo "<div>正在采集第{$p}页: <a href='{$pageUrl}' target='_blank'>{$pageUrl}</a></div>";
                $crawler->fetch($pageUrl, 30, 0);
                $more = $crawler->cut($config['content_start'], $config['content_end']);
                if (empty($more)) break;
                $content .= "\n\n" . $more;
            }
        } else {
            $content = $crawler->cut($config['content_start'], $config['content_end']);
        }
        
        if (empty($content)) throw new Exception("内容为空");
        $cleanContent = cleanCrawledContent($content, $config, true);
        $wordCount = smartWordCount($cleanContent);
        $filePath = getChapterFilePath($nid);
        $fullPath = ROOT_PATH . $filePath;
        if (!is_dir(dirname($fullPath))) mkdir(dirname($fullPath), 0755, true);
        file_put_contents($fullPath, $cleanContent);
        
        $maxSort = (int)($db->fetch($db->query("SELECT MAX(sort) as max_sort FROM chapters WHERE novel_id=?", [$nid]))['max_sort'] ?? -1);
        $sort = $maxSort + 1;
        $db->query("INSERT INTO chapters (novel_id, volume_id, title, source_url, file_path, sort, word_count) VALUES (?,?,?,?,?,?,?)",
            [$nid, $volumeId, $task['title'], $task['url'], $filePath, $sort, $wordCount]);
        updateNovelTotalWords($nid);
        $success = true;
        echo "<div class='alert alert-success'>✅ 采集成功，本文字数：{$wordCount}</div>";
    } catch (Exception $e) {
        $errorMsg = $e->getMessage();
        $isNetworkError = (strpos($errorMsg, 'cURL错误') !== false || strpos($errorMsg, 'HTTP状态码: 5') !== false || strpos($errorMsg, 'HTTP状态码: 0') !== false);
        if (!$isNetworkError && strpos($errorMsg, '网络请求失败') === false) {
            echo "<div class='alert alert-danger'>❌ 章节采集失败（非网络错误），跳过：{$errorMsg}</div>";
            $tasks[$indexTask]['failed'] = true;
            file_put_contents($queueFile, json_encode($tasks, JSON_PRETTY_PRINT));
            redirectDelay("single_crawl.php?step=process_chapter&rule_id={$ruleId}", 1, "跳过此章节，继续下一章");
        } else {
            $retryCount = $task['retry_count'] + 1;
            $delay = 10;
            if ($retryCount >= 10 && $retryCount < 15) $delay = 60;
            elseif ($retryCount >= 15) $delay = 600;
            $nextRetry = time() + $delay;
            $tasks[$indexTask]['retry_count'] = $retryCount;
            $tasks[$indexTask]['next_retry_time'] = $nextRetry;
            $tasks[$indexTask]['failed'] = false;
            file_put_contents($queueFile, json_encode($tasks, JSON_PRETTY_PRINT));
            echo "<div class='alert alert-warning'>⚠️ 章节采集网络异常，将在 " . ceil($delay/60) . " 分钟后重试（第 {$retryCount} 次）</div>";
            redirectDelay("single_crawl.php?step=process_chapter&rule_id={$ruleId}", $delay, "等待重试");
        }
        exit;
    }
    
    if ($success) {
        array_splice($tasks, $indexTask, 1);
        file_put_contents($queueFile, json_encode($tasks, JSON_PRETTY_PRINT));
        redirectDelay("single_crawl.php?step=process_chapter&rule_id={$ruleId}", 1, "继续采集下一章");
    }
    exit;
}

die('无效的步骤');
?>