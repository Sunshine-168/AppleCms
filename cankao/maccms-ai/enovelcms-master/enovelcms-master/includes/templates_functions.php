<?php

function home_handler() {
    global $db, $lang;
    $banners = $db->fetchAll($db->query("SELECT * FROM ads WHERE position='home_banner' AND enabled=1 ORDER BY sort"));
    $latestUpdates = DiyString('latest_updates');
    if (empty($latestUpdates)) {
        $latestUpdates = $db->fetchAll($db->query("SELECT * FROM novels ORDER BY updated_at DESC LIMIT 12"));
    }
    if (!empty($latestUpdates)) {
        $categories = [];
        $catRes = $db->query("SELECT id, name FROM categories");
        while ($cat = $db->fetch($catRes)) {
            $categories[$cat['id']] = $cat['name'];
        }
        $novelIds = array_column($latestUpdates, 'id');
        $latestChapters = batch_latest_chapters($novelIds);
        foreach ($latestUpdates as &$novel) {
            $novel['category_name'] = $categories[$novel['category_id']] ?? '';
            $novel['latest_chapter_id'] = $latestChapters[$novel['id']]['id'] ?? 0;
            $novel['latest_chapter_title'] = $latestChapters[$novel['id']]['title'] ?? '';
            $novel['latest_chapter_url'] = $latestChapters[$novel['id']]['url'] ?? '';
        }
        unset($novel);
    }
    $topViewsWeek = DiyString('top_views_week');
    if (empty($topViewsWeek)) {
        $topViewsWeek = $db->fetchAll($db->query("SELECT * FROM novels ORDER BY views DESC LIMIT 10"));
    }
    $topUpdate = DiyString('top_update');
    if (empty($topUpdate)) {
        $topUpdate = $db->fetchAll($db->query("SELECT * FROM novels ORDER BY updated_at DESC LIMIT 10"));
    }
    return [
        'banners' => $banners,
        'latestUpdates' => $latestUpdates,
        'topViewsWeek' => $topViewsWeek,
        'topUpdate' => $topUpdate,
        'seoPage' => 'home',
        'seoVariables' => [],
        'pageTitle' => $lang->get('home')
    ];
}

function library_handler() {
    global $db, $lang;
    
    $limit = (int)getSetting('library_per_page', $db);
    if ($limit <= 0) $limit = 20;
    
    $categoryId = (int)input('category', 0, 'GET');
    $status = input('status', 'all', 'GET');
    $orderBy = input('order_by', 'id', 'GET');
    $minWords = (int)input('min_words', 0, 'GET');
    $maxWords = (int)input('max_words', 0, 'GET');
    $page = max(1, (int)input('page', 1, 'GET'));
    $offset = ($page - 1) * $limit;
    
    $where = "1=1";
    $params = [];
    if ($categoryId > 0) {
        $where .= " AND n.category_id = ?";
        $params[] = $categoryId;
    }
    if ($status === 'ongoing') {
        $where .= " AND n.status = 0";
    } elseif ($status === 'finished') {
        $where .= " AND n.status = 1";
    }
    if ($minWords > 0) {
        $where .= " AND n.total_words >= ?";
        $params[] = $minWords;
    }
    if ($maxWords > 0) {
        $where .= " AND n.total_words <= ?";
        $params[] = $maxWords;
    }
    
    $orderSql = '';
    switch ($orderBy) {
        case 'updated_at':
            $orderSql = "ORDER BY n.updated_at DESC";
            break;
        case 'views':
            $orderSql = "ORDER BY n.views DESC";
            break;
        case 'favorites':
            $orderSql = "ORDER BY n.favorites DESC";
            break;
        default:
            $orderSql = "ORDER BY n.id DESC";
    }
    
    $total = $db->fetch($db->query("SELECT COUNT(*) as cnt FROM novels n WHERE $where", $params))['cnt'];
    
    $sql = "SELECT n.*, 
            (SELECT id FROM chapters WHERE novel_id = n.id ORDER BY sort DESC LIMIT 1) as last_chapter_id,
            (SELECT title FROM chapters WHERE novel_id = n.id ORDER BY sort DESC LIMIT 1) as last_chapter_title
            FROM novels n 
            WHERE $where 
            $orderSql 
            LIMIT $offset, $limit";
    $novels = $db->fetchAll($db->query($sql, $params));
    
    $categories = $db->fetchAll($db->query("SELECT * FROM categories ORDER BY sort"));
    $totalPages = ceil($total / $limit);
    
    $seoVariables = [
        '{page}' => $page,
        '{total_pages}' => $totalPages,
        '{category_id}' => $categoryId,
        '{status}' => $status,
        '{order_by}' => $orderBy
    ];

    return [
        'categories' => $categories,
        'novels' => $novels,
        'categoryId' => $categoryId,
        'status' => $status,
        'orderBy' => $orderBy,
        'minWords' => $minWords,
        'maxWords' => $maxWords,
        'page' => $page,
        'totalPages' => $totalPages,
        'limit' => $limit,
        'total' => $total,
        'pageTitle' => $lang->get('library'),
        'seoPage' => 'library',
        'seoVariables' => $seoVariables
    ];
}

function rank_handler() {
    global $db, $lang;
    $rankType = input('type', 'views', 'GET');
    $validTypes = ['views', 'words', 'favorites'];
    if (!in_array($rankType, $validTypes)) {
        $rankType = 'views';
    }
    if ($rankType == 'views') {
        $novels = $db->fetchAll($db->query("SELECT * FROM novels ORDER BY views DESC LIMIT 50"));
    } elseif ($rankType == 'words') {
        $novels = $db->fetchAll($db->query("SELECT * FROM novels ORDER BY total_words DESC LIMIT 50"));
    } else {
        $novels = $db->fetchAll($db->query("SELECT * FROM novels ORDER BY favorites DESC LIMIT 50"));
    }
    return [
        'rankType' => $rankType,
        'novels' => $novels,
        'seoPage' => 'rank',
        'seoVariables' => ['{rank_type}' => $rankType],
        'pageTitle' => $lang->get('rank')
    ];
}

function author_handler() {
    global $db, $lang;
    $authors = $db->fetchAll($db->query("SELECT DISTINCT author, COUNT(*) as novel_count FROM novels WHERE author IS NOT NULL AND author != '' GROUP BY author ORDER BY novel_count DESC LIMIT 50"));
    return [
        'authors' => $authors,
        'seoPage' => 'author',
        'seoVariables' => [],
        'pageTitle' => $lang->get('author')
    ];
}

function novel_detail_handler() {
    global $db, $lang;
    $id = (int)input('id', 0, 'GET');
    $novel = $db->fetch($db->query("SELECT n.*, c.name as category_name FROM novels n LEFT JOIN categories c ON n.category_id=c.id WHERE n.id=?", [$id]));
    if (!$novel) {
        http_response_code(404);
        include ROOT_PATH . 'templates/404.php';
        exit;
    }
    $db->query("UPDATE novels SET views = views + 1 WHERE id=?", [$id]);
    $volumes = $db->fetchAll($db->query("SELECT * FROM volumes WHERE novel_id=? ORDER BY sort", [$id]));
    if (empty($volumes)) {
        $volumes = [['id' => 0, 'title' => $lang->get('chapters'), 'sort' => 0]];
    }

    $chaptersRaw = $db->fetchAll($db->query("SELECT id, title, word_count, sort, volume_id FROM chapters WHERE novel_id=? ORDER BY sort", [$id]));
    $chaptersByVolume = [];
    foreach ($volumes as $vol) {
        $chaptersByVolume[$vol['id']] = [
            'volume' => $vol,
            'chapters' => []
        ];
    }
    $firstVolumeId = $volumes[0]['id'];
    foreach ($chaptersRaw as $ch) {
        $vid = $ch['volume_id'];
        if (empty($vid) || $vid == 0) {
            $vid = $firstVolumeId;
        }
        if (isset($chaptersByVolume[$vid])) {
            $chaptersByVolume[$vid]['chapters'][] = $ch;
        } else {
            $chaptersByVolume[$firstVolumeId]['chapters'][] = $ch;
        }
    }

    foreach ($chaptersByVolume as $vid => $group) {
        if (empty($group['chapters'])) {
            unset($chaptersByVolume[$vid]);
        }
    }

    $totalWords = $novel['total_words'];
    $totalChapters = array_reduce($chaptersByVolume, function($carry, $group) { return $carry + count($group['chapters']); }, 0);
    if ($totalWords == 0 && !empty($chaptersRaw)) {
        $totalWords = array_sum(array_column($chaptersRaw, 'word_count'));
        $db->query("UPDATE novels SET total_words = ? WHERE id = ?", [$totalWords, $id]);
        $novel['total_words'] = $totalWords;
    }

    $plainDescription = strip_tags($novel['description']);
    $descriptionPreview = mb_substr($plainDescription, 0, 150);

    $seoVariables = [
        '{novel_title}'       => $novel['title'],
        '{author}'            => $novel['author'],
        '{category_name}'     => $novel['category_name'],
        '{status}'            => $novel['status'] ? $lang->get('finished') : $lang->get('ongoing'),
        '{novel_description}' => $plainDescription,
        '{novel_keywords}'    => $novel['title'] . ',' . $novel['author'],
        '{total_words}'       => number_format($totalWords),
        '{total_chapters}'    => $totalChapters,
        '{views}'             => number_format($novel['views']),
        '{favorites}'         => number_format($novel['favorites']),
        '{description_preview}' => $descriptionPreview
    ];

    return [
        'novel' => $novel,
        'chaptersByVolume' => $chaptersByVolume,
        'totalWords' => $totalWords,
        'seoPage' => 'novel_detail',
        'seoVariables' => $seoVariables,
        'pageTitle' => $novel['title']
    ];
}

function read_handler() {
    global $db, $lang;
    
    $novelId = (int)input('novel_id', 0, 'GET');
    $chapterId = (int)input('chapter_id', 0, 'GET');
    $novel = $db->fetch($db->query("SELECT * FROM novels WHERE id=?", [$novelId]));
    $chapter = $db->fetch($db->query("SELECT * FROM chapters WHERE id=?", [$chapterId]));
    if (!$novel || !$chapter) {
        die($lang->get('invalid_params'));
    }
    
    $db->query("UPDATE novels SET views = views + 1 WHERE id=?", [$novelId]);
    
    $fullPath = ROOT_PATH . $chapter['file_path'];
    if (file_exists($fullPath)) {
        $content = file_get_contents($fullPath);
    } else {
        $content = $lang->get('chapter_not_exist');
    }
    
    if (isLoggedIn()) {
        $userId = $_SESSION['user_id'];
        $db->query("INSERT INTO reading_history (user_id, novel_id, chapter_id) VALUES (?,?,?)",
            [$userId, $novelId, $chapterId]);
        
        $exists = $db->fetch($db->query("SELECT id FROM bookshelf WHERE user_id=? AND novel_id=?", [$userId, $novelId]));
        if ($exists) {
            $db->query("UPDATE bookshelf SET last_read_chapter=? WHERE user_id=? AND novel_id=?", [$chapterId, $userId, $novelId]);
        } else {
            $db->query("INSERT INTO bookshelf (user_id, novel_id, last_read_chapter) VALUES (?,?,?)", [$userId, $novelId, $chapterId]);
        }
    }
    
    $prev = null;
    $next = null;
    if ($chapter['sort'] > 0) {
        $prev = $db->fetch($db->query("SELECT id, title FROM chapters WHERE novel_id=? AND sort < ? ORDER BY sort DESC LIMIT 1", [$novelId, $chapter['sort']]));
        $next = $db->fetch($db->query("SELECT id, title FROM chapters WHERE novel_id=? AND sort > ? ORDER BY sort ASC LIMIT 1", [$novelId, $chapter['sort']]));
    }
    if (!$prev && !$next) {
        $prev = $db->fetch($db->query("SELECT id, title FROM chapters WHERE novel_id=? AND id < ? ORDER BY id DESC LIMIT 1", [$novelId, $chapterId]));
        $next = $db->fetch($db->query("SELECT id, title FROM chapters WHERE novel_id=? AND id > ? ORDER BY id ASC LIMIT 1", [$novelId, $chapterId]));
    }
    
    $plainContent = strip_tags($content);
    $chapterIntro = mb_substr($plainContent, 0, 150);
    $chapterWordCount = $chapter['word_count'] ?: smartWordCount($content);

    $seoVariables = [
        '{novel_title}'      => $novel['title'],
        '{chapter_title}'    => $chapter['title'],
        '{novel_keywords}'   => $novel['title'],
        '{chapter_word_count}' => number_format($chapterWordCount),
        '{chapter_intro}'    => $chapterIntro,
        '{chapter_views}'    => number_format($novel['views']),
        '{total_chapters}'   => $db->fetch($db->query("SELECT COUNT(*) as cnt FROM chapters WHERE novel_id=?", [$novelId]))['cnt']
    ];
    
    return [
        'novel' => $novel,
        'chapter' => $chapter,
        'content' => $content,
        'prev' => $prev,
        'next' => $next,
        'novelId' => $novelId,
        'seoPage' => 'read',
        'seoVariables' => $seoVariables,
        'pageTitle' => $chapter['title'] . ' - ' . $novel['title']
    ];
}

function search_handler() {
    global $db, $lang;
    
    $limit = (int)getSetting('library_per_page', $db);
    if ($limit <= 0) $limit = 20;
    
    $keyword = trim(input('q', '', 'GET'));
    $author = trim(input('author', '', 'GET'));
    $categoryId = (int)input('category', 0, 'GET');
    $status = input('status', 'all', 'GET');
    $orderBy = input('order_by', 'id', 'GET');
    $minWords = (int)input('min_words', 0, 'GET');
    $maxWords = (int)input('max_words', 0, 'GET');
    $page = max(1, (int)input('page', 1, 'GET'));
    $captcha = input('captcha', '', 'GET');
    $offset = ($page - 1) * $limit;
    
    $needCaptcha = false;
    $captchaError = '';
    $searchExecuted = false;
    $novels = [];
    $total = 0;
    $categories = $db->fetchAll($db->query("SELECT * FROM categories ORDER BY sort"));
    
    $hasSearchCondition = !empty($keyword) || !empty($author) || $categoryId > 0 || $status !== 'all' || $minWords > 0 || $maxWords > 0;
    
    if ($hasSearchCondition) {
        if (is_search_frequency_exceeded(5, 60)) {
            $needCaptcha = true;
            if (!empty($captcha)) {
                if (verify_captcha($captcha)) {
                    reset_search_frequency();
                    clear_captcha();
                    $needCaptcha = false;
                } else {
                    $captchaError = $lang->get('captcha_error');
                    clear_captcha();
                    $needCaptcha = true;
                }
            }
        }
        
        if (!$needCaptcha) {
            if (!is_search_frequency_exceeded(5, 60) || !empty($captcha)) {
                record_search_request();
            }
            
            $where = "1=1";
            $params = [];
            
            if (!empty($keyword)) {
                $where .= " AND (title LIKE ? OR author LIKE ?)";
                $like = "%{$keyword}%";
                $params[] = $like;
                $params[] = $like;
            }
            if (!empty($author)) {
                $where .= " AND author = ?";
                $params[] = $author;
            }
            if ($categoryId > 0) {
                $where .= " AND category_id = ?";
                $params[] = $categoryId;
            }
            if ($status === 'ongoing') {
                $where .= " AND status = 0";
            } elseif ($status === 'finished') {
                $where .= " AND status = 1";
            }
            if ($minWords > 0) {
                $where .= " AND total_words >= ?";
                $params[] = $minWords;
            }
            if ($maxWords > 0) {
                $where .= " AND total_words <= ?";
                $params[] = $maxWords;
            }
            
            $orderSql = '';
            switch ($orderBy) {
                case 'updated_at':
                    $orderSql = "ORDER BY updated_at DESC";
                    break;
                case 'views':
                    $orderSql = "ORDER BY views DESC";
                    break;
                case 'favorites':
                    $orderSql = "ORDER BY favorites DESC";
                    break;
                default:
                    $orderSql = "ORDER BY id DESC";
            }
            
            $total = $db->fetch($db->query("SELECT COUNT(*) as cnt FROM novels WHERE $where", $params))['cnt'];
            $novels = $db->fetchAll($db->query("SELECT * FROM novels WHERE $where $orderSql LIMIT $offset, $limit", $params));
            $searchExecuted = true;
        }
    }
    
    $totalPages = $total > 0 ? ceil($total / $limit) : 0;
    $searchKeyword = $keyword ?: ($author ?: '');
    
    $seoVariables = [
        '{keyword}' => $searchKeyword,
        '{site_name}' => getSetting('site_name', $db) ?: 'Enovel CMS',
        '{page}' => $page,
        '{total_results}' => $total
    ];
    
    return [
        'categories' => $categories,
        'novels' => $novels,
        'keyword' => $keyword,
        'author' => $author,
        'categoryId' => $categoryId,
        'status' => $status,
        'orderBy' => $orderBy,
        'minWords' => $minWords,
        'maxWords' => $maxWords,
        'page' => $page,
        'totalPages' => $totalPages,
        'limit' => $limit,
        'total' => $total,
        'needCaptcha' => $needCaptcha,
        'captchaError' => $captchaError,
        'searchExecuted' => $searchExecuted,
        'pageTitle' => $lang->get('search_results') . ($keyword ? ": {$keyword}" : ($author ? " - {$author}" : '')),
        'seoPage' => 'search',
        'seoVariables' => $seoVariables
    ];
}
?>