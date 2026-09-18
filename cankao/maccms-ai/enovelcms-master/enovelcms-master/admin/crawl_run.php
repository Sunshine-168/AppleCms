<?php
/**
 * 采集运行脚本
 */
require_once __DIR__ . '/../includes/config.php';
require_once ROOT_PATH . 'includes/Crawler.php';
require_once ROOT_PATH . 'includes/crawl_helper.php';
checkMbstring();
if (!isAdmin()) redirect(BASE_URL . '/admin/login.php');

$ruleId = (int) input('rule_id', 0, 'GET');
$step   = input('step', '', 'GET');

if (!$ruleId) die('缺少规则ID');

$rule = $db->fetch($db->query("SELECT * FROM crawl_rules WHERE id=?", [$ruleId]));
if (!$rule) die('规则不存在');

$config = json_decode($rule['config'], true);
$GLOBALS['crawl_rule_config'] = $config;

$crawlerDataDir = ROOT_PATH . 'data/crawler/';
if (!is_dir($crawlerDataDir)) mkdir($crawlerDataDir, 0755, true);

function getCategoryId($catName, $config, $db) {
    if (empty($catName)) return 0;
    if (isset($config['category_mapping'][$catName])) return (int)$config['category_mapping'][$catName];
    $first = $db->fetch($db->query("SELECT id FROM categories ORDER BY sort ASC LIMIT 1"));
    return $first ? (int)$first['id'] : 0;
}

if ($step === 'list') {
    $page = (int) input('page', 1, 'GET');
    $maxPage = isset($config['max_crawl_page']) ? (int)$config['max_crawl_page'] : 100;
    if ($page > $maxPage) {
        echo "<div class='alert alert-success'>所有列表页采集完成！</div><a href='crawl.php' class='btn btn-primary'>返回规则列表</a>";
        exit;
    }
    $listUrl = removeSpacesFromUrl(str_replace('{page}', $page, $config['list_url']));
    echo "<h3>采集列表页第 {$page} 页</h3>";
    echo "<table class='data-table'><tr><th>URL</th><td><a href='{$listUrl}' target='_blank'>{$listUrl}</a></td></table>";
    
    $crawler = new Crawler($rule['charset']);
    try {
        fetchWithRetry($crawler, $listUrl, 3, 10);
        $novels = $crawler->extractIdAndTitle(
            $config['list_area_start'] ?? '', $config['list_area_end'] ?? '',
            $config['novel_id_start'] ?? '', $config['novel_id_end'] ?? '',
            $config['novel_title_start'] ?? '', $config['novel_title_end'] ?? ''
        );
        if (empty($novels)) {
            echo "<div class='alert alert-warning'>第 {$page} 页无小说，结束。</div><a href='crawl.php'>返回</a>";
            exit;
        }
        $listFile = $crawlerDataDir . "list_page_{$page}.json";
        file_put_contents($listFile, json_encode($novels, JSON_UNESCAPED_UNICODE));
        echo "<div class='alert alert-success'>第 {$page} 页采集到 " . count($novels) . " 部小说，已保存。</div>";
        redirectDelay("crawl_run.php?rule_id={$ruleId}&step=info&list_file=list_page_{$page}.json&index=0&page={$page}", 2, "开始处理小说信息");
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>列表采集失败：{$e->getMessage()}</div>";
        exit;
    }
}

if ($step === 'info') {
    $listFile = input('list_file', '', 'GET');
    $index = (int) input('index', 0, 'GET');
    $page = (int) input('page', 1, 'GET');
    $listFilePath = $crawlerDataDir . $listFile;
    if (!file_exists($listFilePath)) die("列表文件不存在：{$listFilePath}");
    $novels = json_decode(file_get_contents($listFilePath), true);
    if ($index >= count($novels)) {
        @unlink($listFilePath);
        redirectDelay("crawl_run.php?rule_id={$ruleId}&step=list&page=" . ($page + 1), 2, "本页处理完成，进入下一页");
    }
    $currentNovel = $novels[$index];
    $sourceNovelId = $currentNovel['id'];
    $novelTitle = $currentNovel['title'];
    echo "<h3>处理小说：{$novelTitle}（源站ID：{$sourceNovelId}）</h3>";
    
    if (empty($config['info_url_template'])) die('未配置详情页URL模板');
    $infoUrl = removeSpacesFromUrl(Crawler::buildUrl($config['info_url_template'], ['novel_id' => $sourceNovelId]));
    echo "<p>详情页URL：<a href='{$infoUrl}' target='_blank'>{$infoUrl}</a></p>";
    
    $crawler = new Crawler($rule['charset']);
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
            } else {
                echo "<div class='alert alert-secondary'>封面不是默认图片，跳过更新。</div>";
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
            } else {
                echo "<div class='alert alert-info'>未提取到封面URL，使用默认封面。</div>";
            }
        }
        
        $tempInfoFile = $crawlerDataDir . "temp_{$nid}_source.txt";
        file_put_contents($tempInfoFile, $sourceNovelId);
        
        redirectDelay("crawl_run.php?rule_id={$ruleId}&step=chapters&nid={$nid}&page={$page}&list_file={$listFile}&index=" . ($index + 1), 2, "开始提取章节列表");
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>小说信息采集失败：{$e->getMessage()}</div>";
        exit;
    }
}

if ($step === 'chapters') {
    $nid = (int) input('nid', 0, 'GET');
    $page = (int) input('page', 1, 'GET');
    $listFile = input('list_file', '', 'GET');
    $nextIndex = (int) input('index', 0, 'GET');
    if (!$nid) die('缺少小说ID');
    $tempInfoFile = $crawlerDataDir . "temp_{$nid}_source.txt";
    if (!file_exists($tempInfoFile)) die('源站ID文件不存在');
    $sourceNovelId = (int) file_get_contents($tempInfoFile);
    echo "<h3>提取小说ID {$nid} 的章节列表（源站ID：{$sourceNovelId}）</h3>";
    
    try {
        $chapterListUrlTemplate = $config['chapter_list_url'] ?? '';
        if (empty($chapterListUrlTemplate)) throw new Exception("未配置章节列表页URL模板");
        $chapterListBaseUrl = str_replace('{novel_id/1000}', floor($sourceNovelId / 1000), $chapterListUrlTemplate);
        $chapterListBaseUrl = removeSpacesFromUrl(Crawler::buildUrl($chapterListBaseUrl, ['novel_id' => $sourceNovelId]));
        $hasPageVar = strpos($chapterListBaseUrl, '{page}') !== false;
        $totalPages = 1;
        $usePagination = false;
        $crawler = new Crawler($rule['charset']);
        $firstPageUrl = removeSpacesFromUrl($hasPageVar ? str_replace('{page}', 1, $chapterListBaseUrl) : $chapterListBaseUrl);
        echo "<p>章节列表第一页URL: <a href='{$firstPageUrl}' target='_blank'>{$firstPageUrl}</a></p>";
        
        fetchWithRetry($crawler, $firstPageUrl, 3, 10);
        if (!empty($config['chapter_total_pages_start']) && !empty($config['chapter_total_pages_end'])) {
            $pagesHtml = $crawler->cut($config['chapter_total_pages_start'], $config['chapter_total_pages_end'], false, false);
            if (!empty($pagesHtml) && preg_match('/(\d+)/', $pagesHtml, $pageMatch)) {
                $totalPages = (int) $pageMatch[1];
                $usePagination = true;
                echo "<div class='alert alert-info'>检测到章节列表分页，总页数：{$totalPages}</div>";
            }
        } elseif ($hasPageVar) {
            $usePagination = true;
            $totalPages = 20;
            echo "<div class='alert alert-warning'>未配置总页数规则，将尝试最多 {$totalPages} 页，遇到空白页则停止。</div>";
        }
        
        $allVolumes = [];
        $totalChapters = 0;
        for ($currentPage = 1; $currentPage <= $totalPages; $currentPage++) {
            $pageUrl = $usePagination ? str_replace('{page}', $currentPage, $chapterListBaseUrl) : $chapterListBaseUrl;
            if ($currentPage > 1) {
                echo "<div>正在采集第 {$currentPage} 页章节列表...</div>";
                fetchWithRetry($crawler, $pageUrl, 3, 10);
            }
            $volumeData = $crawler->extractVolumesAndChapters($config);
            if ($volumeData['total_chapters'] == 0 && $currentPage > 1) {
                echo "<div>第 {$currentPage} 页无章节，停止分页。</div>";
                break;
            }
            foreach ($volumeData['volumes'] as $vol) {
                $volTitle = trim($vol['title']) ?: '默认卷';
                if (!isset($allVolumes[$volTitle])) $allVolumes[$volTitle] = [];
                foreach ($vol['chapters'] as $ch) {
                    $allVolumes[$volTitle][] = [
                        'id'    => $ch['id'] ?? '',
                        'title' => $ch['title'] ?? '无标题',
                        'url'   => $ch['url'] ?? ''
                    ];
                }
            }
            $totalChapters += $volumeData['total_chapters'];
            if (!$usePagination) break;
            if (!empty($config['chapter_total_pages_end']) && $currentPage >= $totalPages) break;
            if (!$hasPageVar) break;
        }
        if (empty($allVolumes) || $totalChapters == 0) throw new Exception("未提取到任何章节");
        
        $chapterUrlTemplate = $config['chapter_url_template'] ?? '';
        if (empty($chapterUrlTemplate)) throw new Exception("未配置章节详情页URL模板");
        
        $existingUrls = [];
        $existingRes = $db->query("SELECT source_url FROM chapters WHERE novel_id=?", [$nid]);
        while ($row = $db->fetch($existingRes)) {
            if (!empty($row['source_url'])) $existingUrls[$row['source_url']] = true;
        }
        
        $queueFile = $crawlerDataDir . "chapters_{$nid}.json";
        @unlink($queueFile);
        $tasks = [];
        $newCount = 0;
        foreach ($allVolumes as $volTitle => $chapters) {
            foreach ($chapters as $ch) {
                $chapterId = $ch['id'];
                $chTitle = trim($ch['title']) ?: '章节';
                $tempUrl = str_replace('{novel_id/1000}', floor($sourceNovelId / 1000), $chapterUrlTemplate);
                $chapterUrl = removeSpacesFromUrl(Crawler::buildUrl($tempUrl, [
                    'novel_id'   => $sourceNovelId,
                    'chapter_id' => $chapterId,
                    'page'       => 1
                ]));
                if (!isset($existingUrls[$chapterUrl])) {
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
        }
        if ($newCount == 0) {
            echo "<div class='alert alert-info'>没有新章节，跳过此小说。</div>";
            @unlink($tempInfoFile);
            redirectDelay("crawl_run.php?rule_id={$ruleId}&step=info&list_file={$listFile}&index={$nextIndex}&page={$page}", 2, "无新章节，继续下一部小说");
        } else {
            file_put_contents($queueFile, json_encode($tasks, JSON_PRETTY_PRINT));
            echo "<div class='alert alert-success'>发现 {$newCount} 个新章节，已生成任务队列。</div>";
            redirectDelay("crawl_run.php?rule_id={$ruleId}&step=fetch&nid={$nid}&source_novel_id={$sourceNovelId}&list_file={$listFile}&index={$nextIndex}&page={$page}", 2, "开始采集章节内容");
        }
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>章节列表提取失败：{$e->getMessage()}</div>";
        exit;
    }
}

if ($step === 'fetch') {
    $nid = (int) input('nid', 0, 'GET');
    $sourceNovelId = (int) input('source_novel_id', 0, 'GET');
    $listFile = input('list_file', '', 'GET');
    $nextIndex = (int) input('index', 0, 'GET');
    $page = (int) input('page', 1, 'GET');
    if (!$nid || !$sourceNovelId) die('缺少必要参数');
    
    $queueFile = $crawlerDataDir . "chapters_{$nid}.json";
    if (!file_exists($queueFile)) {
        @unlink($crawlerDataDir . "temp_{$nid}_source.txt");
        echo "<div class='alert alert-success'>小说 {$nid} 所有新章节采集完成！</div>";
        redirectDelay("crawl_run.php?rule_id={$ruleId}&step=info&list_file={$listFile}&index={$nextIndex}&page={$page}", 2, "继续下一部小说");
    }
    
    $tasks = json_decode(file_get_contents($queueFile), true);
    if (empty($tasks)) {
        @unlink($queueFile);
        @unlink($crawlerDataDir . "temp_{$nid}_source.txt");
        redirectDelay("crawl_run.php?rule_id={$ruleId}&step=info&list_file={$listFile}&index={$nextIndex}&page={$page}", 2, "队列为空，继续下一部小说");
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
        redirectDelay("crawl_run.php?rule_id={$ruleId}&step=fetch&nid={$nid}&source_novel_id={$sourceNovelId}&list_file={$listFile}&index={$nextIndex}&page={$page}", $minWait, "等待重试队列");
        exit;
    }
    
    $task = $tasks[$indexTask];
    echo "<h3>采集章节：{$task['title']}</h3>";
    echo "<table class='data-table'>";
    echo "<table><th>章节URL</th><td><a href='{$task['url']}' target='_blank'>{$task['url']}</a></td></tr>";
    echo "<tr><th>重试次数</th><td>{$task['retry_count']}</td></tr>";
    echo "</table>";
    
    $volume = $db->fetch($db->query("SELECT id FROM volumes WHERE novel_id=? AND title=?", [$nid, $task['volume']]));
    if (!$volume) {
        $db->query("INSERT INTO volumes (novel_id, title) VALUES (?,?)", [$nid, $task['volume']]);
        $volumeId = $db->lastInsertId();
    } else {
        $volumeId = $volume['id'];
    }
    
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
                if ($pagesHtml && preg_match('/(\d+)/', $pagesHtml, $m)) $totalContentPages = (int) $m[1];
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
        
        $maxSort = (int) ($db->fetch($db->query("SELECT MAX(sort) as max_sort FROM chapters WHERE novel_id=?", [$nid]))['max_sort'] ?? -1);
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
            redirectDelay("crawl_run.php?rule_id={$ruleId}&step=fetch&nid={$nid}&source_novel_id={$sourceNovelId}&list_file={$listFile}&index={$nextIndex}&page={$page}", 1, "跳过此章节，继续下一章");
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
            echo "<div class='alert alert-warning'>⚠️ 网络异常，将在 " . ceil($delay/60) . " 分钟后重试（第 {$retryCount} 次）</div>";
            redirectDelay("crawl_run.php?rule_id={$ruleId}&step=fetch&nid={$nid}&source_novel_id={$sourceNovelId}&list_file={$listFile}&index={$nextIndex}&page={$page}", $delay, "等待重试");
        }
        exit;
    }
    
    if ($success) {
        array_splice($tasks, $indexTask, 1);
        file_put_contents($queueFile, json_encode($tasks, JSON_PRETTY_PRINT));
        redirectDelay("crawl_run.php?rule_id={$ruleId}&step=fetch&nid={$nid}&source_novel_id={$sourceNovelId}&list_file={$listFile}&index={$nextIndex}&page={$page}", 1, "继续下一章");
    }
    exit;
}
?>
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>采集器</title><link rel="stylesheet" href="../assets/css/admin.css"></head>
<body><div class="admin-container"><?php include 'sidebar.php'; ?><div class="content">
<h1>采集规则执行器（最终版 - 简介单行 & 章节保留换行）</h1>
<p><a href="?rule_id=<?= $ruleId ?>&step=list&page=1" class="btn btn-primary">开始采集（规则ID=<?= $ruleId ?>）</a></p>
<p><strong>封面规则：</strong> <code>data/covers/uniqid().扩展名</code><br>
<strong>简介处理：</strong> 自动压缩为单行纯文本，无多余空格和换行。<br>
<strong>章节处理：</strong> 保留段落换行，正常显示。</p>
</div></div></body>
</html>