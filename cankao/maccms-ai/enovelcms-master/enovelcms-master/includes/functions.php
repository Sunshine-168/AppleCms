<?php
function redirect($url) {
    header("Location: $url");
    exit;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['admin']);
}

function getSetting($key, $db = null) {
    global $db;
    if (!$db) $db = $GLOBALS['db'];
    $result = $db->fetch($db->query("SELECT value FROM settings WHERE `key` = ?", [$key]));
    return $result ? $result['value'] : null;
}

function h($str) {
    if ($str === null) return '';
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function generateOutTradeNo() {
    return date('YmdHis') . mt_rand(1000, 9999);
}

function csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function verify_admin_csrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isAdmin()) return false;
        $token = input('csrf_token', '', 'POST');
        if (!verify_csrf_token($token)) {
            die('CSRF token validation failed');
        }
    }
    return true;
}

function getActiveTheme() {
    global $db;
    $theme = getSetting('active_theme', $db);
    return $theme ?: 'default';
}

function getThemePath() {
    return ROOT_PATH . 'templates/' . getActiveTheme();
}

function getThemeUrl() {
    return BASE_URL . '/templates/' . getActiveTheme();
}

function input($key, $default = '', $method = 'REQUEST', $htmlspecialchars = true) {
    $data = null;
    switch (strtoupper($method)) {
        case 'GET': $data = $_GET; break;
        case 'POST': $data = $_POST; break;
        case 'COOKIE': $data = $_COOKIE; break;
        default: $data = $_REQUEST; break;
    }
    $value = isset($data[$key]) ? $data[$key] : $default;
    if (is_array($value)) {
        foreach ($value as $k => $v) {
            $value[$k] = input_filter_value($v, $htmlspecialchars);
        }
        return $value;
    }
    return input_filter_value($value, $htmlspecialchars);
}

function input_filter_value($value, $htmlspecialchars) {
    if ($htmlspecialchars && is_string($value)) {
        $value = trim($value);
        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    } elseif (is_string($value)) {
        $value = trim($value);
    }
    return $value;
}

function safe_nl2br($str) {
    if ($str === null) return '';
    
    $hasHtml = preg_match('/<(br|p)[\s\/>]/i', $str);
    
    if ($hasHtml) {
        $str = strip_tags($str, '<br><p>');
        $str = preg_replace('/<(\/?(?:br|p))[^>]*>/i', '<$1>', $str);
        $str = preg_replace('/<br\s*\/?>/i', '<br>', $str);
    } else {
        $str = htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
        $str = str_replace("\n", "<br><br>\n", $str);
    }
    $lines = explode("\n", $str);
    
    $filteredLines = [];
    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed === '' || $trimmed === '<br>' || $trimmed === '<br><br>') {
            continue;
        }
        $line = ltrim($line);
        $filteredLines[] = '  ' . $line;
    }
    $str = implode("\n", $filteredLines);

    return $str;
}

function verify_captcha($input) {
    if (empty($input) || empty($_SESSION['captcha_code'])) {
        return false;
    }
    return strtolower(trim($input)) === strtolower($_SESSION['captcha_code']);
}

function clear_captcha() {
    unset($_SESSION['captcha_code']);
}

function is_search_frequency_exceeded($limit = 5, $window = 60) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $sessionId = session_id();
    $key = 'search_freq_' . md5($ip . $sessionId);
    $now = time();
    $records = isset($_SESSION[$key]) ? $_SESSION[$key] : [];
    $records = array_filter($records, function($ts) use ($window, $now) {
        return ($now - $ts) <= $window;
    });
    $count = count($records);
    $_SESSION[$key] = $records;
    return $count >= $limit;
}

function record_search_request() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $sessionId = session_id();
    $key = 'search_freq_' . md5($ip . $sessionId);
    $records = isset($_SESSION[$key]) ? $_SESSION[$key] : [];
    $records[] = time();
    $_SESSION[$key] = $records;
}

function reset_search_frequency() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $sessionId = session_id();
    $key = 'search_freq_' . md5($ip . $sessionId);
    unset($_SESSION[$key]);
}

function DiyString($blockName) {
    global $db;
    $block = $db->fetch($db->query("SELECT * FROM diy_blocks WHERE name = ?", [$blockName]));
    if (!$block) return [];
    if ($block['updated_at'] && (time() - strtotime($block['updated_at']) < $block['cache_time'])) {
        return json_decode($block['data'], true) ?: [];
    }
    $data = generateBlockData($block);
    $json = json_encode($data);
    $db->query("UPDATE diy_blocks SET data = ?, updated_at = NOW() WHERE id = ?", [$json, $block['id']]);
    return $data;
}

function DiyStrTitle($blockName) {
    global $db;
    $block = $db->fetch($db->query("SELECT title FROM diy_blocks WHERE name = ?", [$blockName]));
    return $block ? $block['title'] : '';
}

function generateBlockData($block) {
    global $db;
    $where = "1=1";
    $params = [];
    if ($block['category_id'] > 0) {
        $where .= " AND n.category_id = ?";
        $params[] = $block['category_id'];
    }
    if ($block['status'] !== 'all') {
        $where .= " AND n.status = ?";
        $params[] = ($block['status'] == 'ongoing') ? 0 : 1;
    }
    if ($block['min_words'] > 0) {
        $where .= " AND n.total_words >= ?";
        $params[] = $block['min_words'];
    }
    if ($block['max_words'] > 0) {
        $where .= " AND n.total_words <= ?";
        $params[] = $block['max_words'];
    }
    $timeCondition = '';
    $range = $block['time_range'];
    if ($range !== 'all') {
        $interval = null;
        switch ($range) {
            case 'today': $interval = '1 DAY'; break;
            case 'week': $interval = '7 DAY'; break;
            case 'month': $interval = '1 MONTH'; break;
            case 'year': $interval = '1 YEAR'; break;
            default: $interval = null;
        }
        if ($interval) {
            $timeCondition = " AND n.created_at >= DATE_SUB(NOW(), INTERVAL $interval)";
        }
    }

    $orderBy = '';
    switch ($block['order_by']) {
        case 'views':
            $orderBy = 'n.views DESC';
            break;
        case 'new':
            $orderBy = 'n.created_at DESC';
            break;
        case 'update':
            $orderBy = 'n.updated_at DESC';
            break;
        case 'favorites':
            $orderBy = 'n.favorites DESC';
            break;
        case 'chapters':
            $orderBy = 'total_chapters DESC';
            break;
        case 'words':
            $orderBy = 'n.total_words DESC';
            break;
        case 'random':
            $orderBy = 'RAND()';
            break;
        case 'custom':
            $orderBy = '';
            break;
        default:
            $orderBy = 'n.id DESC';
    }

    $sql = "SELECT 
                n.id, n.title, n.author, n.category_id, n.cover, n.description, 
                n.status, n.views, n.total_words, n.favorites, n.created_at, n.updated_at,
                c.name AS category_name,
                (SELECT id FROM chapters WHERE novel_id = n.id ORDER BY sort DESC LIMIT 1) AS latest_chapter_id,
                (SELECT title FROM chapters WHERE novel_id = n.id ORDER BY sort DESC LIMIT 1) AS latest_chapter_title,
                (SELECT COUNT(*) FROM chapters WHERE novel_id = n.id) AS total_chapters
            FROM novels n
            LEFT JOIN categories c ON n.category_id = c.id
            WHERE $where $timeCondition";

    if ($block['order_by'] != 'custom') {
        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        }
        $sql .= " LIMIT " . intval($block['num']);
        $result = $db->query($sql, $params);
        $novels = $db->fetchAll($result);
    } else {
        $result = $db->query($sql, $params);
        $allNovels = $db->fetchAll($result);
        if (empty($allNovels)) {
            return [];
        }
        $novelMap = [];
        foreach ($allNovels as $novel) {
            $novelMap[$novel['id']] = $novel;
        }
        $customIds = array_map('trim', explode(',', $block['custom_ids'] ?? ''));
        $customIds = array_filter($customIds, 'is_numeric');
        $orderedNovels = [];
        foreach ($customIds as $id) {
            if (isset($novelMap[$id])) {
                $orderedNovels[] = $novelMap[$id];
                unset($novelMap[$id]);
            }
        }
        if (!empty($novelMap)) {
            $remaining = array_values($novelMap);
            usort($remaining, function($a, $b) {
                return $b['id'] - $a['id'];
            });
            $orderedNovels = array_merge($orderedNovels, $remaining);
        }
        $novels = array_slice($orderedNovels, 0, $block['num']);
    }

    foreach ($novels as &$novel) {
        $novel['latest_chapter_id'] = (int)$novel['latest_chapter_id'];
        $novel['latest_chapter_title'] = $novel['latest_chapter_title'] ?? '';
        $novel['total_chapters'] = (int)$novel['total_chapters'];
        $novel['category_name'] = $novel['category_name'] ?? '';
    }
    return $novels;
}

function isSpider() {
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $spiders = ['bot', 'spider', 'crawl', 'baidu', 'google', 'bing', 'yahoo', 'yandex',
                'facebookexternalhit', 'twitterbot', 'slackbot', 'Bytespider', 'Sogou'];
    foreach ($spiders as $spider) {
        if (stripos($userAgent, $spider) !== false) return true;
    }
    return false;
}

function recordVisit() {
    global $db;
    $today = date('Y-m-d');
    $isSpider = isSpider() ? 1 : 0;
    $record = $db->fetch($db->query("SELECT id FROM stats_visit WHERE visit_date = ?", [$today]));
    if ($record) {
        if ($isSpider) {
            $db->query("UPDATE stats_visit SET spider_count = spider_count + 1, pv = pv + 1 WHERE visit_date = ?", [$today]);
        } else {
            $db->query("UPDATE stats_visit SET real_count = real_count + 1, pv = pv + 1 WHERE visit_date = ?", [$today]);
        }
    } else {
        $spiderInit = $isSpider ? 1 : 0;
        $realInit = $isSpider ? 0 : 1;
        $db->query("INSERT INTO stats_visit (visit_date, spider_count, real_count, pv) VALUES (?, ?, ?, 1)", [$today, $spiderInit, $realInit]);
    }
}

function getSeoMeta($page, $variables = []) {
    global $db;
    $seo = $db->fetch($db->query("SELECT * FROM seo_settings WHERE page = ?", [$page]));
    if (!$seo) {
        return ['title' => '', 'keywords' => '', 'description' => ''];
    }
    $siteName = getSetting('site_name', $db) ?: 'Novel Site';
    $siteKeywords = getSetting('site_keywords', $db) ?: '';
    $siteDesc = getSetting('site_description', $db) ?: '';

    $replace = [
        '{site_name}'        => $siteName,
        '{site_keywords}'    => $siteKeywords,
        '{site_description}' => $siteDesc,
    ];
    $replace = array_merge($replace, $variables);
    $title = $seo['title'];
    $keywords = $seo['keywords'];
    $description = $seo['description'];

    for ($i = 0; $i < 5; $i++) {
        $newTitle = str_replace(array_keys($replace), array_values($replace), $title);
        $newKeywords = str_replace(array_keys($replace), array_values($replace), $keywords);
        $newDesc = str_replace(array_keys($replace), array_values($replace), $description);
        if ($newTitle === $title && $newKeywords === $keywords && $newDesc === $description) {
            break;
        }
        $title = $newTitle;
        $keywords = $newKeywords;
        $description = $newDesc;
    }

    return [
        'title'       => $title,
        'keywords'    => $keywords,
        'description' => $description
    ];
}

function showAd($position) {
    global $db;
    if (isLoggedIn()) {
        $user = $db->fetch($db->query("SELECT vip_level FROM users WHERE id = ?", [$_SESSION['user_id']]));
        if ($user && $user['vip_level'] == 1) {
            return '';
        }
    }
    $ads = $db->fetchAll($db->query("SELECT * FROM ads WHERE position = ? AND enabled = 1 ORDER BY sort", [$position]));
    $html = '';
    foreach ($ads as $ad) {
        if (!empty($ad['html_code'])) {
            $html .= '<div class="ad-item">' . $ad['html_code'] . '</div>';
        }
    }
    return $html;
}

function addGold($userId, $goldChange, $type, $remark = '') {
    global $db;
    $user = $db->fetch($db->query("SELECT gold FROM users WHERE id = ?", [$userId]));
    if (!$user) return false;
    $newGold = $user['gold'] + $goldChange;
    if ($newGold < 0) return false;
    $db->query("UPDATE users SET gold = ? WHERE id = ?", [$newGold, $userId]);
    $db->query("INSERT INTO gold_logs (user_id, type, gold_change, gold_after, remark) VALUES (?, ?, ?, ?, ?)",
        [$userId, $type, $goldChange, $newGold, $remark]);
    return true;
}

function getSignReward($userId) {
    global $db;
    $user = $db->fetch($db->query("SELECT sign_days, last_sign FROM users WHERE id = ?", [$userId]));
    if (!$user) {
        return [
            'continuous_days' => 0,
            'base_gold' => 0,
            'bonus_percent' => 0,
            'incremental' => 0,
            'total' => 0,
            'bonus' => 0
        ];
    }
    $today = date('Y-m-d');
    $isConsecutive = ($user['last_sign'] == date('Y-m-d', strtotime('-1 day')));
    $continuousDays = $isConsecutive ? $user['sign_days'] + 1 : 1;
    $baseGold = (int)getSetting('sign_base_gold', $db);
    $percent = (int)getSetting('sign_continue_percent', $db);
    $minBonus = (int)getSetting('sign_min_continue_bonus', $db);
    $maxBonus = (int)getSetting('sign_max_continue_bonus', $db);

    if ($continuousDays == 1) {
        return [
            'continuous_days' => 1,
            'base_gold' => $baseGold,
            'bonus_percent' => 0,
            'incremental' => 0,
            'total' => $baseGold,
            'bonus' => 0
        ];
    }

    $prevLog = $db->fetch($db->query(
        "SELECT gold_change FROM gold_logs WHERE user_id = ? AND type = 'sign' ORDER BY created_at DESC LIMIT 1",
        [$userId]
    ));
    $prevTotal = $prevLog ? (int)$prevLog['gold_change'] : $baseGold;
    $bonusPercent = (int)round($prevTotal * $percent / 100);
    $bonusPercent = max($minBonus, min($maxBonus, $bonusPercent));
    $incremental = $continuousDays - 1;
    $total = $baseGold + $bonusPercent + $incremental;

    return [
        'continuous_days' => $continuousDays,
        'base_gold' => $baseGold,
        'bonus_percent' => $bonusPercent,
        'incremental' => $incremental,
        'total' => $total,
        'bonus' => $bonusPercent + $incremental
    ];
}

function doSign($userId) {
    global $db;
    $lang = $GLOBALS['lang'];
    $today = date('Y-m-d');
    $exists = $db->fetch($db->query(
        "SELECT id FROM gold_logs WHERE user_id = ? AND type = 'sign' AND DATE(created_at) = ?",
        [$userId, $today]
    ));
    if ($exists) {
        return ['success' => false, 'message' => $lang->get('already_signed')];
    }

    $reward = getSignReward($userId);

    $db->query("UPDATE users SET sign_days = ?, last_sign = ? WHERE id = ?",
        [$reward['continuous_days'], $today, $userId]);

    $goldAdded = addGold($userId, $reward['total'], 'sign', sprintf($lang->get('sign_log_remark'), $reward['total']));
    if (!$goldAdded) {
        return ['success' => false, 'message' => $lang->get('gold_faild')];
    }

    return [
        'success' => true,
        'reward' => $reward['total'],
        'continuous_days' => $reward['continuous_days']
    ];
}

function exchangeVipWithGold($userId, $months) {
    global $db;
    $lang = $GLOBALS['lang'];
    $goldPerMonth = (int)getSetting('vip_gold_per_month', $db);
    if ($goldPerMonth <= 0) return ['success' => false, 'message' => $lang->get('gold_exchange_rate_error')];
    $needGold = $months * $goldPerMonth;
    $user = $db->fetch($db->query("SELECT gold, vip_level, vip_expire FROM users WHERE id = ?", [$userId]));
    if (!$user) return ['success' => false, 'message' => $lang->get('operation_failed')];
    if ($user['gold'] < $needGold) return ['success' => false, 'message' => $lang->get('gold_insufficient')];
    addGold($userId, -$needGold, 'exchange_vip', sprintf($lang->get('exchange_vip_log_remark'), $months, $needGold));
    $newExpire = $user['vip_expire'];
    if ($newExpire && strtotime($newExpire) > time()) {
        $newExpire = date('Y-m-d H:i:s', strtotime($newExpire . " + $months months"));
    } else {
        $newExpire = date('Y-m-d H:i:s', strtotime("+ $months months"));
    }
    $db->query("UPDATE users SET vip_level = 1, vip_expire = ? WHERE id = ?", [$newExpire, $userId]);
    return ['success' => true, 'message' => sprintf($lang->get('exchange_success'), $months), 'new_expire' => $newExpire];
}

function sendMail($to, $subject, $body) {
    global $db;
    $lang = $GLOBALS['lang'];
    $host = getSetting('smtp_host', $db);
    $port = (int)getSetting('smtp_port', $db);
    $secure = getSetting('smtp_secure', $db);
    $user = getSetting('smtp_user', $db);
    $pass = getSetting('smtp_pass', $db);
    $from = getSetting('smtp_from', $db);
    $fromName = getSetting('smtp_fromname', $db) ?: $lang->get('site_default_name');
    if (empty($host) || empty($user) || empty($pass) || empty($from)) {
        return ['success' => false, 'message' => $lang->get('email_config_incomplete')];
    }
    $phpmailerPath = ROOT_PATH . 'includes/Phpmail/';
    if (!file_exists($phpmailerPath . 'Exception.php') || 
        !file_exists($phpmailerPath . 'PHPMailer.php') || 
        !file_exists($phpmailerPath . 'SMTP.php')) {
        return ['success' => false, 'message' => $lang->get('email_component_missing')];
    }
    require_once $phpmailerPath . 'Exception.php';
    require_once $phpmailerPath . 'PHPMailer.php';
    require_once $phpmailerPath . 'SMTP.php';
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = $host;
        $mail->SMTPAuth = true;
        $mail->Username = $user;
        $mail->Password = $pass;
        $mail->SMTPSecure = $secure ?: '';
        $mail->Port = $port;
        $mail->setFrom($from, $fromName);
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->send();
        return ['success' => true, 'message' => $lang->get('mail_sent')];
    } catch (Exception $e) {
        return ['success' => false, 'message' => $lang->get('mail_failed') . $mail->ErrorInfo];
    }
}

function sendVerifyCode($email, $type = 'register') {
    global $db;
    $lang = $GLOBALS['lang'];
    $code = sprintf("%06d", mt_rand(0, 999999));
    $expire = date('Y-m-d H:i:s', time() + 600);
    $db->query("DELETE FROM email_verify WHERE email = ? AND used = 0", [$email]);
    $db->query("INSERT INTO email_verify (email, code, expire) VALUES (?, ?, ?)", [$email, $code, $expire]);
    if ($type == 'register') {
        $subject = $lang->get('register_verify_subject');
        $body = sprintf($lang->get('register_verify_body'), $code);
    } else {
        $subject = $lang->get('reset_verify_subject');
        $body = sprintf($lang->get('reset_verify_body'), $code);
    }
    return sendMail($email, $subject, $body);
}

function verifyCode($email, $code) {
    global $db;
    $record = $db->fetch($db->query("SELECT id FROM email_verify WHERE email = ? AND code = ? AND expire > NOW() AND used = 0", [$email, $code]));
    if ($record) {
        $db->query("UPDATE email_verify SET used = 1 WHERE id = ?", [$record['id']]);
        return true;
    }
    return false;
}

function resetPassword($email) {
    global $db;
    $lang = $GLOBALS['lang'];
    $user = $db->fetch($db->query("SELECT id, username FROM users WHERE email = ?", [$email]));
    if (!$user) return ['success' => false, 'message' => $lang->get('user_not_exists')];
    $newPassword = substr(md5(uniqid() . mt_rand()), 0, 8);
    $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
    $db->query("UPDATE users SET password = ? WHERE id = ?", [$hashed, $user['id']]);
    $subject = $lang->get('reset_password_success_subject');
    $body = sprintf($lang->get('reset_password_success_body'), $user['username'], $newPassword);
    $result = sendMail($email, $subject, $body);
    if ($result['success']) {
        return ['success' => true, 'message' => $lang->get('reset_password_success')];
    } else {
        return ['success' => false, 'message' => $result['message']];
    }
}

function updateNovelTotalWords($novelId) {
    global $db;
    $total = $db->fetch($db->query("SELECT SUM(word_count) as total FROM chapters WHERE novel_id = ?", [$novelId]))['total'] ?? 0;
    $db->query("UPDATE novels SET total_words = ? WHERE id = ?", [$total, $novelId]);
    return $total;
}

function getChapterFilePath($novelId) {
    $dir = ROOT_PATH . 'data/chapters/' . $novelId . '/';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $fileName = bin2hex(random_bytes(8)) . '.txt';
    return 'data/chapters/' . $novelId . '/' . $fileName;
}

function getChapterFullPath($filePath) {
    return ROOT_PATH . $filePath;
}

function smartWordCount($content, $lang = null) {
    $text = strip_tags($content);
    $text = preg_replace('/\s+/', ' ', $text);
    $text = trim($text);
    if (preg_match('/[\x{4e00}-\x{9fff}]/u', $text)) {
        return mb_strlen($text, 'UTF-8');
    } else {
        $text = preg_replace_callback('/\d+/', function($m) {
            return implode(' ', str_split($m[0]));
        }, $text);
        $words = preg_split('/[^\p{L}\p{N}]+/u', $text, -1, PREG_SPLIT_NO_EMPTY);
        return count($words);
    }
}

function getAvailableLanguages() {
    global $lang;
    return $lang->getAllLanguages();
}

function installLanguagePackage($file, $langCode, $langName) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => $GLOBALS['lang']->get('upload_failed')];
    }
    $allowedExt = ['php'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt)) {
        return ['success' => false, 'message' => $GLOBALS['lang']->get('invalid_lang_file')];
    }
    $targetFile = ROOT_PATH . "includes/lang/{$langCode}.php";
    if (!move_uploaded_file($file['tmp_name'], $targetFile)) {
        return ['success' => false, 'message' => $GLOBALS['lang']->get('cannot_write_lang')];
    }
    global $lang;
    $lang->addLanguage($langCode, $langName);
    return ['success' => true, 'message' => $GLOBALS['lang']->get('lang_installed')];
}

function uninstallLanguagePackage($langCode) {
    global $lang, $db;
    $default = getSetting('site_lang', $db);
    if ($langCode === $default) {
        return ['success' => false, 'message' => $lang->get('cannot_delete_default_lang')];
    }
    $file = ROOT_PATH . "includes/lang/{$langCode}.php";
    if (file_exists($file)) {
        unlink($file);
    }
    $lang->removeLanguage($langCode);
    return ['success' => true, 'message' => $lang->get('lang_uninstalled')];
}

function applyAdLinkReplacements($content) {
    global $db;
    $rules = getSetting('keyword_links', $db);
    if (empty($rules)) return $content;
    
    $lines = explode("\n", str_replace("\r", "", $rules));
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        $parts = explode('|', $line, 2);
        if (count($parts) != 2) continue;
        $keyword = trim($parts[0]);
        $url = trim($parts[1]);
        if (empty($keyword) || empty($url)) continue;
        $pattern = '/\b(' . preg_quote($keyword, '/') . ')\b/iu';
        $replacement = '<a href="' . htmlspecialchars($url) . '" target="_blank" rel="nofollow">$1</a>';
        $content = preg_replace($pattern, $replacement, $content);
    }
    return $content;
}

function batch_latest_chapters($novelIds) {
    global $db;
    if (empty($novelIds)) return [];
    $placeholders = implode(',', array_fill(0, count($novelIds), '?'));
    $sql = "SELECT c.novel_id, c.id, c.title FROM chapters c 
            INNER JOIN (
                SELECT novel_id, MAX(sort) as max_sort 
                FROM chapters 
                WHERE novel_id IN ($placeholders) 
                GROUP BY novel_id
            ) t ON c.novel_id = t.novel_id AND c.sort = t.max_sort";
    $stmt = $db->query($sql, $novelIds);
    $result = [];
    while ($row = $db->fetch($stmt)) {
        $result[$row['novel_id']] = [
            'id' => $row['id'],
            'title' => $row['title'],
            'url' => "/read/{$row['novel_id']}/{$row['id']}"
        ];
    }
    return $result;
}
?>