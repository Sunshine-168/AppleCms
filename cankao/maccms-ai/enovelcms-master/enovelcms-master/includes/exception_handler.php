<?php
set_exception_handler(function($exception) {
    $lang = $GLOBALS['lang'] ?? null;
    if (!$lang || !method_exists($lang, 'get')) {
        $fallbackLang = function($key, $default = '') {
            $dict = [
                'system_error_title' => 'System Error',
                'system_error_message' => 'Sorry, the server encountered an unexpected error. Please try again later.',
                'error_detail_label' => 'Error Details',
                'back_to_home' => 'Back to Home'
            ];
            return $dict[$key] ?? $default;
        };
        $t = $fallbackLang;
    } else {
        $t = [$lang, 'get'];
    }
    
    $error_detail = '';
    if (defined('DEBUG_MODE') && DEBUG_MODE === true) {
        $error_detail = $exception->getMessage() . "\n" .
                        "File: " . $exception->getFile() . " (Line " . $exception->getLine() . ")\n" .
                        "Stack trace:\n" . $exception->getTraceAsString();
    }
    
    $error_page = ROOT_PATH . 'templates/500.php';
    if (file_exists($error_page)) {
        $error_detail_for_view = $error_detail;
        include $error_page;
    } else {
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            echo "<pre>", htmlspecialchars($error_detail), "</pre>";
        } else {
            echo "<h1>" . $t('system_error_message') . "</h1>";
        }
    }
    exit;
});