<?php
/**
 * 统一错误和异常处理器（支持中英文自动切换）
 * 兼容 PHP 7.0 - 8.4
 * 根据 DEBUG_MODE、请求类型（AJAX/非AJAX）和浏览器语言输出不同的错误界面
 */

// 定义错误处理开关（如果未定义 DEBUG_MODE，则默认为 false）
if (!defined('DEBUG_MODE')) {
    define('DEBUG_MODE', true);
}

function isPreferChinese() {
    $acceptLang = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
    if (empty($acceptLang)) {
        return false;
    }
    $lang = strtolower(substr($acceptLang, 0, 2));
    return $lang === 'zh';
}

function isAjaxRequest() {
    return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
           (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
}
function outputFriendlyError($message, $details = '', $statusCode = 500) {
    http_response_code($statusCode);
    
    if (isAjaxRequest()) {
        header('Content-Type: application/json; charset=utf-8');
        $response = [
            'code' => 0,
            'message' => $message
        ];
        if (DEBUG_MODE && !empty($details)) {
            $response['debug'] = $details;
        }
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $isChinese = isPreferChinese();
    
    if ($isChinese) {
        $title = '系统错误';
        $backText = '返回上一页';
        $homeText = '返回首页';
    } else {
        $title = 'System Error';
        $backText = 'Go Back';
        $homeText = 'Home';
    }
    
    $output = '<!DOCTYPE html>
    <html>
    <head><meta charset="UTF-8"><title>' . htmlspecialchars($title) . '</title>
    <style>
        body{font-family: "Segoe UI", Arial, sans-serif; text-align: center; padding: 50px; background: #f5f7fa; margin:0;}
        .error-container{max-width: 600px; margin: 0 auto; background: #fff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); padding: 30px;}
        h1{color: #d9534f; margin-top: 0;}
        p{color: #555; line-height: 1.6;}
        .detail{background: #f8f8f8; border-left: 4px solid #d9534f; padding: 15px; margin-top: 20px; text-align: left; font-family: monospace; font-size: 12px; overflow: auto; max-height: 300px;}
        a{color: #0275d8; text-decoration: none;}
        a:hover{text-decoration: underline;}
    </style>
    </head>
    <body>
    <div class="error-container">
        <h1>⚠️ ' . htmlspecialchars($title) . '</h1>
        <p>' . nl2br(htmlspecialchars($message)) . '</p>';
    
    if (DEBUG_MODE && !empty($details)) {
        if ($isChinese) {
            $detailLabel = '调试信息：';
        } else {
            $detailLabel = 'Debug Info：';
        }
        $output .= '<div class="detail"><strong>' . $detailLabel . '</strong><br>' . nl2br(htmlspecialchars($details)) . '</div>';
    }
    
    $output .= '<p><a href="javascript:history.back()">' . $backText . '</a> | <a href="/">' . $homeText . '</a> | <a href="https://www.wszzw.cn/post/628.html" target="_blank">EnovelCMS</a></p>
    </div>
    </body>
    </html>';
    echo $output;
    exit;
}

set_exception_handler(function($exception) {
    $isChinese = isPreferChinese();
    $message = $isChinese ? '发生了一个系统异常，请稍后再试。' : 'A system exception occurred, please try again later.';
    $details = '';
    
    if (DEBUG_MODE) {
        $details = ($isChinese ? '异常：' : 'Exception: ') . $exception->getMessage() . "\n" .
                   ($isChinese ? '文件：' : 'File: ') . $exception->getFile() . ' (' . ($isChinese ? '行' : 'line') . ' ' . $exception->getLine() . ")\n" .
                   ($isChinese ? '堆栈跟踪：' : 'Stack trace: ') . $exception->getTraceAsString();
    }
    
    error_log('[Exception] ' . $exception->getMessage() . ' in ' . $exception->getFile() . ':' . $exception->getLine());
    
    outputFriendlyError($message, $details, 500);
});

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    if (error_reporting() === 0) {
        return false;
    }
    
    $isChinese = isPreferChinese();
    $message = $isChinese ? '发生了一个系统错误。' : 'A system error occurred.';
    $details = '';
    
    if (DEBUG_MODE) {
        $errorType = '';
        switch ($errno) {
            case E_WARNING: $errorType = $isChinese ? '警告' : 'Warning'; break;
            case E_NOTICE: $errorType = $isChinese ? '注意' : 'Notice'; break;
            case E_USER_ERROR: $errorType = $isChinese ? '用户错误' : 'User Error'; break;
            case E_USER_WARNING: $errorType = $isChinese ? '用户警告' : 'User Warning'; break;
            case E_USER_NOTICE: $errorType = $isChinese ? '用户注意' : 'User Notice'; break;
            default: $errorType = $isChinese ? '未知错误' : 'Unknown Error'; break;
        }
        $details = "[{$errorType}] {$errstr}\n" . ($isChinese ? '文件：' : 'File: ') . "{$errfile} (" . ($isChinese ? '行' : 'line') . " {$errline})";
    }
    
    error_log("[PHP Error] {$errstr} in {$errfile}:{$errline}");
    
    outputFriendlyError($message, $details, 500);
    return true;
});

register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR])) {
        $isChinese = isPreferChinese();
        $message = $isChinese ? '发生了一个致命系统错误，请稍后再试。' : 'A fatal system error occurred, please try again later.';
        $details = '';
        if (DEBUG_MODE) {
            $details = ($isChinese ? '致命错误：' : 'Fatal error: ') . $error['message'] . "\n" .
                       ($isChinese ? '文件：' : 'File: ') . $error['file'] . ' (' . ($isChinese ? '行' : 'line') . ' ' . $error['line'] . ')';
        }
        error_log("[Fatal Error] {$error['message']} in {$error['file']}:{$error['line']}");
        while (ob_get_level()) ob_end_clean();
        outputFriendlyError($message, $details, 500);
    }
});

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', 0);
}