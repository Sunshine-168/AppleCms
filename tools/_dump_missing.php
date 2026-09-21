<?php

$root = dirname(__DIR__);
$zh = include $root.'/resources/lang/zh_cn/admin.php';

function flatten(array $a, string $p = ''): array
{
    $o = [];
    foreach ($a as $k => $v) {
        $kk = $p === '' ? (string) $k : $p.'.'.$k;
        if (is_array($v)) {
            $o += flatten($v, $kk);
        } else {
            $o[$kk] = (string) $v;
        }
    }

    return $o;
}

$have = flatten($zh);
$files = [];
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/resources/views/admin'));
foreach ($it as $f) {
    if ($f->isFile() && str_ends_with($f->getFilename(), '.blade.php')) {
        $files[] = $f->getPathname();
    }
}
foreach (glob($root.'/plugins/*/resources/views/admin/*.blade.php') ?: [] as $p) {
    $files[] = $p;
}
$php = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/app'));
foreach ($php as $f) {
    if ($f->isFile() && str_ends_with($f->getFilename(), '.php')) {
        $files[] = $f->getPathname();
    }
}
$missing = [];
foreach ($files as $path) {
    $src = (string) file_get_contents($path);
    if (! preg_match_all("/admin_t\(\s*'([^']+)'/", $src, $m)) {
        continue;
    }
    foreach ($m[1] as $key) {
        if (str_ends_with($key, '_') || str_ends_with($key, '.')) {
            continue;
        }
        if (! isset($have[$key])) {
            $missing[$key] = ($missing[$key] ?? 0) + 1;
        }
    }
}
ksort($missing);
file_put_contents($root.'/tools/_missing_now.txt', implode("\n", array_keys($missing))."\n");
echo 'missing='.count($missing).PHP_EOL;
