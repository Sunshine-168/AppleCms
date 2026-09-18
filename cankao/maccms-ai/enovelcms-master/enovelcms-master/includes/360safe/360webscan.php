<?php
webscan_error();
// 加载防护开关、白名单配置
require_once('webscan_cache.php');
require_once('security.php');
// ===================== 分请求类型拦截正则规则 =====================
// GET 参数过滤规则
$getfilter = "\\<.+javascript:window\\[.{1}\\\\x|<.*=(&#\\d+?;?)+?>|<.*(data|src)=data:text\\/html.*>|\\b(alert\\(|confirm\\(|expression\\(|prompt\\(|benchmark\s*?\(.*\)|sleep\s*?\(.*\)|\\b(group_)?concat[\\s\\/\\*]*?\\([^\\)]+?\\)|\bcase[\s\/\*]*?when[\s\/\*]*?\([^\)]+?\)|load_file\s*?\\()|<[a-z]+?\\b[^>]*?\\bon([a-z]{4,})\s*?=|^\\+\\/v(8|9)|\\b(and|or)\\b\\s*?([\\(\\)'\"\\d]+?=[\\(\\)'\"\\d]+?|[\\(\\)'\"a-zA-Z]+?=[\\(\\)'\"a-zA-Z]+?|>|<|\s+?[\\w]+?\\s+?\\bin\\b\\s*?\(|\\blike\\b\\s+?[\"'])|\\/\\*.*\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT\s*(\(.+\)\s*|@{1,2}.+?\s*|\s+?.+?|(`|'|\").*?(`|'|\")\s*)|UPDATE\s*(\(.+\)\s*|@{1,2}.+?\s*|\s+?.+?|(`|'|\").*?(`|'|\")\s*)SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE)@{0,2}(\\(.+\\)|\\s+?.+?\\s+?|(`|'|\").*?(`|'|\"))FROM(\\(.+\\)|\\s+?.+?|(`|'|\").*?(`|'|\"))|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)|<.*(iframe|frame|style|embed|object|frameset|meta|xml)";
// POST 参数过滤规则
$postfilter = "<.*=(&#\\d+?;?)+?>|<.*data=data:text\\/html.*>|\\b(alert\\(|confirm\\(|expression\\(|prompt\\(|benchmark\s*?\(.*\)|sleep\s*?\(.*\)|\\b(group_)?concat[\\s\\/\\*]*?\\([^\\)]+?\\)|\bcase[\s\/\*]*?when[\s\/\*]*?\([^\)]+?\)|load_file\s*?\\()|<[^>]*?\\b(onerror|onmousemove|onload|onclick|onmouseover)\\b|\\b(and|or)\\b\\s*?([\\(\\)'\"\\d]+?=[\\(\\)'\"\\d]+?|[\\(\\)'\"a-zA-Z]+?=[\\(\\)'\"a-zA-Z]+?|>|<|\s+?[\\w]+?\\s+?\\bin\\b\\s*?\(|\\blike\\b\\s+?[\"'])|\\/\\*.*\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT\s*(\(.+\)\s*|@{1,2}.+?\s*|\s+?.+?|(`|'|\").*?(`|'|\")\s*)|UPDATE\s*(\(.+\)\s*|@{1,2}.+?\s*|\s+?.+?|(`|'|\").*?(`|'|\")\s*)SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE)(\\(.+\\)|\\s+?.+?\\s+?|(`|'|\").*?(`|'|\"))FROM(\\(.+\\)|\\s+?.+?|(`|'|\").*?(`|'|\"))|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)|<.*(iframe|frame|style|embed|object|frameset|meta|xml)";
// COOKIE 参数过滤规则
$cookiefilter = "benchmark\s*?\(.*\)|sleep\s*?\(.*\)|load_file\s*?\\(|\\b(and|or)\\b\\s*?([\\(\\)'\"\\d]+?=[\\(\\)'\"\\d]+?|[\\(\\)'\"a-zA-Z]+?=[\\(\\)'\"a-zA-Z]+?|>|<|\s+?[\\w]+?\\s+?\\bin\\b\\s*?\(|\\blike\\b\\s+?[\"'])|\\/\\*.*\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT\s*(\(.+\)\s*|@{1,2}.+?\s*|\s+?.+?|(`|'|\").*?(`|'|\")\s*)|UPDATE\s*(\(.+\)\s*|@{1,2}.+?\s*|\s+?.+?|(`|'|\").*?(`|'|\")\s*)SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE)@{0,2}(\\(.+\\)|\\s+?.+?\\s+?|(`|'|\").*?(`|'|\"))FROM(\\(.+\\)|\\s+?.+?|(`|'|\").*?(`|'|\"))|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";

// 来源页Referer数据
$webscan_referer = empty($_SERVER['HTTP_REFERER']) ? array() : array('HTTP_REFERER' => $_SERVER['HTTP_REFERER']);

/**
 * 关闭PHP原生错误输出，避免泄露路径信息
 */
function webscan_error(): void
{
    if (ini_get('display_errors')) {
        ini_set('display_errors', '0');
    }
}

/**
 * 攻击日志记录函数，可自行扩展写入文件/数据库
 * @param array $logs 攻击日志数组
 * @return bool
 */
function webscan_slog(array $logs): bool
{
    // 此处可自定义日志落地逻辑
    return true;
}

/**
 * 递归扁平化多维数组，修复原版静态变量全局污染BUG
 * @param mixed $input 待遍历参数（支持字符串/多维数组）
 * @return string 拼接后完整参数文本（键+值）
 */
function webscan_arr_foreach($input): string
{
    $collect = [];
    $loop = function ($data, $prefix = '') use (&$loop, &$collect) {
        if (!is_array($data)) {
            $collect[] = $prefix . $data;
            return;
        }
        foreach ($data as $k => $v) {
            $newPrefix = $prefix . $k;
            $loop($v, $newPrefix);
        }
    };
    $loop($input);
    return implode('', $collect);
}

/**
 * 多语言拦截提示页面（保留原版中英文自动识别）
 */
function webscan_pape(): void
{
    $lang = 'en';
    $acceptLang = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
    if (!empty($acceptLang) && strpos(strtolower($acceptLang), 'zh') === 0) {
        $lang = 'zh';
    }

    if ($lang === 'zh') {
        $title = '请求被拦截';
        $message = '您的请求包含危险字符，已被系统安全机制拦截。';
        $backText = '返回上一页';
        $homeText = '返回首页';
    } else {
        $title = 'Request Blocked';
        $message = 'Your request contains dangerous characters and has been blocked for security reasons.';
        $backText = 'Go Back';
        $homeText = 'Go Home';
    }

    $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title}</title>
    <style>
        :root { --bg-body: #f5f7fb; --bg-card: #ffffff; --text-primary: #1e293b; --text-secondary: #475569; --border-light: #e2e8f0; --primary: #3b82f6; --primary-dark: #2563eb; --primary-light: #eff6ff; --danger: #ef4444; --danger-light: #fee2e2; --radius-lg: 20px; --shadow-sm: 0 1px 2px 0 rgba(0,0,0,0.03); }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: var(--bg-body); color: var(--text-primary); display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; }
        .error-box { background: var(--bg-card); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border: 1px solid var(--border-light); max-width: 500px; width: 100%; padding: 2rem; text-align: center; }
        .error-box h1 { font-size: 4rem; font-weight: 700; color: var(--danger); margin-bottom: 1rem; }
        .error-box h2 { font-size: 1.5rem; font-weight: 600; margin-bottom: 1rem; }
        .error-box p { color: var(--text-secondary); margin-bottom: 2rem; }
        .error-box .btn { display: inline-flex; padding: 0.5rem 1.2rem; border-radius: 40px; font-weight: 500; text-decoration: none; margin: 0 0.5rem; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-outline { border: 1px solid var(--border-light); color: var(--text-secondary); }
    </style>
</head>
<body>
    <div class="error-box">
        <h1>!</h1>
        <h2>{$title}</h2>
        <p>{$message}</p>
        <div>
            <a href="javascript:history.go(-1)" class="btn btn-outline">{$backText}</a>
            <a href="/" class="btn btn-primary">{$homeText}</a>
        </div>
    </div>
</body>
</html>
HTML;
    echo $html;
    exit;
}

/**
 * 核心攻击检测拦截函数
 * @param string $filterKey 参数键名
 * @param mixed $filterValue 参数值（支持多维数组）
 * @param string $regexRule 对应请求类型正则规则
 * @param string $method 请求方式 GET/POST/COOKIE/REFERRER
 */
function webscan_StopAttack(string $filterKey, $filterValue, string $regexRule, string $method): void
{
    // 扁平化所有层级参数（键+值拼接）
    $concatStr = webscan_arr_foreach($filterValue);
    $regexFlag = '/'.$regexRule.'/is';

    // 检测参数值包含恶意特征
    if (preg_match($regexFlag, $concatStr)) {
        webscan_slog([
            'ip' => $_SERVER["REMOTE_ADDR"],
            'time' => date("Y-m-d H:i:s"),
            'page' => $_SERVER["PHP_SELF"],
            'method' => $method,
            'rkey' => $filterKey,
            'rdata' => $filterValue,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'request_url' => $_SERVER["REQUEST_URI"]
        ]);
        webscan_pape();
    }

    // 检测参数键名包含恶意特征（防键注入）
    if (preg_match($regexFlag, $filterKey)) {
        webscan_slog([
            'ip' => $_SERVER["REMOTE_ADDR"],
            'time' => date("Y-m-d H:i:s"),
            'page' => $_SERVER["PHP_SELF"],
            'method' => $method,
            'rkey' => $filterKey,
            'rdata' => $filterKey,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'request_url' => $_SERVER["REQUEST_URI"]
        ]);
        webscan_pape();
    }
}

/**
 * 白名单校验：匹配白名单目录/URL时跳过防护
 * @param string $whiteDir 白名单目录关键词
 * @param array $whiteUrl 白名单URL数组 [路径=>参数标识]
 * @return bool true=开启检测 false=白名单跳过
 */
function webscan_white(string $whiteDir, array $whiteUrl = []): bool
{
    $urlPath = $_SERVER['SCRIPT_NAME'];
    $urlVar = $_SERVER['QUERY_STRING'] ?? '';

    // 目录白名单匹配直接放行
    if (!empty($whiteDir) && preg_match('/'.$whiteDir.'/is', $urlPath)) {
        return false;
    }

    // 路由参数白名单循环校验
    foreach ($whiteUrl as $pathKey => $queryVal) {
        if (!empty($urlVar) && !empty($queryVal)) {
            if (stristr($urlPath, $pathKey) && stristr($urlVar, $queryVal)) {
                return false;
            }
        } elseif (empty($urlVar) && empty($queryVal)) {
            if (stristr($urlPath, $pathKey)) {
                return false;
            }
        }
    }
    return true;
}

// ===================== 全局防护入口 =====================
global $webscan_switch, $webscan_get, $webscan_post, $webscan_cookie, $webscan_referre, $webscan_white_directory, $webscan_white_url;
if ($webscan_switch && webscan_white($webscan_white_directory, $webscan_white_url)) {
    // GET参数检测
    if ($webscan_get) {
        foreach ($_GET as $key => $value) {
            webscan_StopAttack((string)$key, $value, $getfilter, "GET");
        }
    }
    // POST参数检测
    if ($webscan_post) {
        foreach ($_POST as $key => $value) {
            webscan_StopAttack((string)$key, $value, $postfilter, "POST");
        }
    }
    // COOKIE检测
    if ($webscan_cookie) {
        foreach ($_COOKIE as $key => $value) {
            webscan_StopAttack((string)$key, $value, $cookiefilter, "COOKIE");
        }
    }
    // Referer来源页检测（复用POST规则）
    if ($webscan_referre) {
        foreach ($webscan_referer as $key => $value) {
            webscan_StopAttack((string)$key, $value, $postfilter, "REFERRER");
        }
    }
}
?>