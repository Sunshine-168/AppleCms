<?php
$lockFile = __DIR__ . '/data/install.lock';
if (!file_exists($lockFile)) {
    header('Location: install/install.php');
    exit;
}
require_once __DIR__ . '/includes/error_handler.php';
require_once __DIR__ . '/includes/360safe/360webscan.php';
require_once __DIR__ . '/includes/config.php';
require_once ROOT_PATH . 'includes/templates_functions.php';
require_once ROOT_PATH . 'includes/user_functions.php';
require_once ROOT_PATH . 'includes/Router.php';
require_once ROOT_PATH . 'includes/exception_handler.php';

$activeTheme = getActiveTheme();
$lang->setArea('frontend');
$lang->setTheme($activeTheme);
$lang->reload();

define('THEME_PATH', ROOT_PATH . 'templates/' . $activeTheme . '/');
define('THEME_URL', BASE_URL . '/templates/' . $activeTheme . '/');

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

extract($viewData);
if (!$isAjax) {
    require_once THEME_PATH . 'index/header.php';
}
include $viewFile;
if (!$isAjax && basename($viewFile) !== '404.php') {
    require_once THEME_PATH . 'index/footer.php';
}