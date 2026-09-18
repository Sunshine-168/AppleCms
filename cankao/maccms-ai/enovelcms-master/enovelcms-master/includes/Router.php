<?php
class Router {
    private $routes = [];
    public function add($pattern, $handler, $view, $seoPage) {
        $this->routes[] = compact('pattern', 'handler', 'view', 'seoPage');
    }
    public function dispatch($path) {
        $activeTheme = getActiveTheme();
        foreach ($this->routes as $route) {
            if (preg_match($route['pattern'], $path, $matches)) {
                foreach ($matches as $key => $val) {
                    if (is_string($key)) $_GET[$key] = (int)$val;
                }
                $data = call_user_func($route['handler']);
                if (isset($matches['id'])) $data['id'] = (int)$matches['id'];
                $viewFile = ROOT_PATH . 'templates/' . $activeTheme . '/' . $route['view'];
                return [
                    'data' => $data,
                    'viewFile' => $viewFile,
                    'seoPage' => $route['seoPage']
                ];
            }
        }
        return null;
    }
}

$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);
$path = trim($path, '/');
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

$viewFile = null;
$data = [];
$seoPage = 'home';
$seoVariables = [];

$frontRoutes = [
    ''        => ['handler' => 'home_handler',        'view' => 'index/home.php',        'seoPage' => 'home'],
    'index.php' => ['handler' => 'home_handler',        'view' => 'index/home.php',        'seoPage' => 'home'],
    'library' => ['handler' => 'library_handler',    'view' => 'index/library.php',    'seoPage' => 'library'],
    'rank'    => ['handler' => 'rank_handler',       'view' => 'index/rank.php',       'seoPage' => 'rank'],
    'authors' => ['handler' => 'author_handler',     'view' => 'index/author.php',     'seoPage' => 'author'],
    'search' => ['handler' => 'search_handler', 'view' => 'index/search.php', 'seoPage' => 'search'],
];

$userMap = [
    'index'     => ['handler' => 'user_index_handler',     'view' => 'user/index.php',     'seoPage' => 'user_center'],
    'profile'   => ['handler' => 'user_profile_handler',   'view' => 'user/profile.php',   'seoPage' => 'profile'],
    'bookshelf' => ['handler' => 'user_bookshelf_handler', 'view' => 'user/bookshelf.php', 'seoPage' => 'bookshelf'],
    'history'   => ['handler' => 'user_history_handler',   'view' => 'user/history.php',   'seoPage' => 'history'],
    'gold'      => ['handler' => 'user_gold_handler',      'view' => 'user/gold.php',      'seoPage' => 'gold_log'],
    'sign'      => ['handler' => 'user_sign_handler',      'view' => 'user/sign.php',      'seoPage' => 'sign_today'],
    'vip'       => ['handler' => 'user_vip_handler',       'view' => 'user/vip.php',       'seoPage' => 'vip_center'],
    'login'     => ['handler' => 'user_login_handler',     'view' => 'user/login.php',     'seoPage' => 'login'],
    'register'  => ['handler' => 'user_register_handler',  'view' => 'user/register.php',  'seoPage' => 'register'],
    'forgot'    => ['handler' => 'user_forgot_handler',    'view' => 'user/forgot.php',    'seoPage' => 'forgot'],
    'logout'    => ['handler' => 'user_logout_handler',    'view' => null,                 'seoPage' => null],
];

$activeTheme = getActiveTheme();

if (preg_match('/^novel\/(\d+)$/', $path, $matches)) {
    $_GET['id'] = (int)$matches[1];
    $data = novel_detail_handler();
    $data['id'] = $_GET['id'];
    $viewFile = ROOT_PATH . 'templates/' . $activeTheme . '/index/novel_detail.php';
    $seoPage = 'novel_detail';
    $seoVariables = $data['seoVariables'] ?? [];
}
elseif (preg_match('/^read\/(\d+)\/(\d+)$/', $path, $matches)) {
    $_GET['novel_id'] = (int)$matches[1];
    $_GET['chapter_id'] = (int)$matches[2];
    $data = read_handler();
    $viewFile = ROOT_PATH . 'templates/' . $activeTheme . '/index/read.php';
    $seoPage = 'read';
    $seoVariables = $data['seoVariables'] ?? [];
}
elseif (preg_match('/^user(?:\/([a-zA-Z0-9_]+)(?:\.php)?)?$/', $path, $matches)) {
    $action = $matches[1] ?? 'index';
    if (isset($userMap[$action])) {
        if ($userMap[$action]['view'] === null) {
            call_user_func($userMap[$action]['handler']);
            exit;
        }
        $data = call_user_func($userMap[$action]['handler']);
        $viewFile = ROOT_PATH . 'templates/' . $activeTheme . '/' . $userMap[$action]['view'];
        $seoPage = $userMap[$action]['seoPage'];
        $seoVariables = $data['seoVariables'] ?? [];
    }
}
elseif (isset($frontRoutes[$path])) {
    $route = $frontRoutes[$path];
    $data = call_user_func($route['handler']);
    $viewFile = ROOT_PATH . 'templates/' . $activeTheme . '/' . $route['view'];
    $seoPage = $route['seoPage'];
    $seoVariables = $data['seoVariables'] ?? [];
}
else {
    $action = input('action', '', 'GET');
    if ($action === 'novel') {
        $_GET['id'] = (int)input('id', 0, 'GET');
        $data = novel_detail_handler();
        $data['id'] = $_GET['id'];
        $viewFile = ROOT_PATH . 'templates/' . $activeTheme . '/index/novel_detail.php';
        $seoPage = 'novel_detail';
        $seoVariables = $data['seoVariables'] ?? [];
    } elseif ($action === 'read') {
        $_GET['novel_id'] = (int)input('novel_id', 0, 'GET');
        $_GET['chapter_id'] = (int)input('chapter_id', 0, 'GET');
        $data = read_handler();
        $viewFile = ROOT_PATH . 'templates/' . $activeTheme . '/index/read.php';
        $seoPage = 'read';
        $seoVariables = $data['seoVariables'] ?? [];
    } else {
        http_response_code(404);
        $viewFile = ROOT_PATH . 'templates/' . $activeTheme . '/404.php';
        $isAjax = false;
    }
}

if (!$viewFile || !file_exists($viewFile)) {
    http_response_code(404);
    include ROOT_PATH . 'templates/' . $activeTheme . '/404.php';
    exit;
}

$current_lang = $lang->current();
$siteLogo = getSetting('site_logo', $db) ?: '/assets/images/default_logo.png';
$userGold = 0;
if (isLoggedIn()) {
    $userRow = $db->fetch($db->query("SELECT gold FROM users WHERE id = ?", [$_SESSION['user_id']]));
    $userGold = $userRow ? $userRow['gold'] : 0;
}

$seo = getSeoMeta($seoPage, $seoVariables);
if (isset($data['seo']) && is_array($data['seo'])) {
    $seo = array_merge($seo, $data['seo']);
}

$viewData = array_merge([
    'current_lang' => $current_lang,
    'siteLogo' => $siteLogo,
    'userGold' => $userGold,
    'seo' => $seo,
    'lang' => $lang,
    'db' => $db,
], $data);