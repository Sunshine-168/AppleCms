<?php
/**
 * 将本地 addons/{name} 打包为可发布的插件 zip
 *
 * 用法：
 *   php application/data/addon_market_cloud/build_package.php --name=adminloginbg
 *   php application/data/addon_market_cloud/build_package.php --name=adminloginbg --out=./packages/adminloginbg/package.zip
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "CLI only\n");
    exit(1);
}

$opts = getopt('', ['name:', 'out::', 'root::', 'help']);
if (isset($opts['help']) || empty($opts['name'])) {
    echo "Usage: php build_package.php --name=ADDON_NAME [--out=path] [--root=project_root]\n";
    exit(isset($opts['help']) ? 0 : 1);
}

$name = strtolower(preg_replace('/[^a-z0-9_]/', '', (string) $opts['name']));
if ($name === '' || !preg_match('/^[a-z][a-z0-9_]{0,31}$/', $name)) {
    fwrite(STDERR, "Invalid --name\n");
    exit(1);
}

$projectRoot = isset($opts['root']) ? rtrim($opts['root'], '/\\') : dirname(dirname(dirname(__DIR__)));
$src = $projectRoot . DIRECTORY_SEPARATOR . 'addons' . DIRECTORY_SEPARATOR . $name;
if (!is_dir($src) || !is_file($src . DIRECTORY_SEPARATOR . 'info.ini')) {
    fwrite(STDERR, "Addon not found or missing info.ini: {$src}\n");
    exit(1);
}

$main = $src . DIRECTORY_SEPARATOR . ucfirst($name) . '.php';
if (!is_file($main)) {
    fwrite(STDERR, "Main class missing: {$main}\n");
    exit(1);
}

$outPath = isset($opts['out'])
    ? $opts['out']
    : __DIR__ . '/packages/' . $name . '/package.zip';

$outDir = dirname($outPath);
if (!is_dir($outDir)) {
    mkdir($outDir, 0755, true);
}

if (!class_exists('ZipArchive')) {
    fwrite(STDERR, "ZipArchive extension required\n");
    exit(1);
}

$denyExt = ['phtml', 'phar', 'php3', 'php4', 'php5', 'php7', 'phps', 'shtml', 'asp', 'aspx', 'jsp'];
$denyBase = ['.htaccess', '.htpasswd', '.user.ini', 'web.config'];

$zip = new ZipArchive();
if ($zip->open($outPath, ZipArchive::OVERWRITE | ZipArchive::CREATE) !== true) {
    fwrite(STDERR, "Cannot create zip: {$outPath}\n");
    exit(1);
}

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($src, FilesystemIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($iterator as $file) {
    /** @var SplFileInfo $file */
    $rel = str_replace('\\', '/', substr($file->getPathname(), strlen($src) + 1));
    if ($rel === false || $rel === '') {
        continue;
    }
    if (preg_match('#(^|/)\.\.(/|$)#', $rel)) {
        fwrite(STDERR, "Unsafe path skipped: {$rel}\n");
        continue;
    }
    $base = strtolower(basename($rel));
    if (in_array($base, $denyBase, true)) {
        continue;
    }
    if ($file->isDir()) {
        $zip->addEmptyDir($rel . '/');
        continue;
    }
    $ext = strtolower(pathinfo($file->getFilename(), PATHINFO_EXTENSION));
    if (in_array($ext, $denyExt, true)) {
        continue;
    }
    $zip->addFile($file->getPathname(), $rel);
}

$zip->close();
echo "Wrote {$outPath}\n";
echo "sha256:" . hash_file('sha256', $outPath) . "\n";
