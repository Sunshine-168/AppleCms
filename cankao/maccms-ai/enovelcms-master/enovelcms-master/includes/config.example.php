<?php
if (session_status() === PHP_SESSION_NONE) session_start();
define('ROOT_PATH', realpath(__DIR__ . '/../') . '/');
define('BASE_URL', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']);

define('DB_HOST', 'localhost');
define('DB_NAME', '');
define('DB_USER', '');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

$versionFile = ROOT_PATH . '/includes/version.php';
if (file_exists($versionFile)) {
    $version = include $versionFile;
    define('ENOVELCMS_VERSION', $version ?: '1.0.0');
} else {
    define('ENOVELCMS_VERSION', '1.0.0');
}

$dirs = [ROOT_PATH . 'data', ROOT_PATH . 'data/chapters', ROOT_PATH . 'data/covers', ROOT_PATH . 'assets/uploads/ads', ROOT_PATH . 'assets/uploads/logo'];
foreach ($dirs as $dir) { if (!is_dir($dir)) mkdir($dir, 0755, true); }

spl_autoload_register(function ($class) {
    $base_dir = ROOT_PATH . 'includes/';
    $file = $base_dir . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) { require $file; return true; }
    return false;
});

require_once ROOT_PATH . 'includes/functions.php';
$db = new Database();

$isAdminArea = (strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false);
$lang = new Language($isAdminArea ? 'backend' : 'frontend');
$GLOBALS['db'] = $db;
$GLOBALS['lang'] = $lang;
?>