<?php
require_once __DIR__ . '/../../includes/config.php';

$langCode = input('lang', 'zh-cn', 'GET');
$allowed = ['zh-cn', 'en-us'];
if (!in_array($langCode, $allowed)) {
    $langCode = 'zh-cn';
}

$_SESSION['lang'] = $langCode;
setcookie('lang', $langCode, time() + 86400 * 30, '/');

redirect(BASE_URL);