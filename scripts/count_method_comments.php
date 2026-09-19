<?php

$dirs = ['app', 'plugins'];
$missing = 0;
$with = 0;
$samples = [];

foreach ($dirs as $d) {
    if (! is_dir($d)) {
        continue;
    }
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($d, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $f) {
        if ($f->getExtension() !== 'php') {
            continue;
        }
        $path = str_replace('\\', '/', $f->getPathname());
        if (str_contains($path, '/migrations/') || str_contains($path, '/vendor/')) {
            continue;
        }
        $code = file_get_contents($path);
        if (! preg_match('/\bclass\s+\w+/', $code)) {
            continue;
        }
        if (! preg_match_all('/^([ \t]*)((?:public|protected|private)\s+)?(?:static\s+)?function\s+(\w+)\s*\(/m', $code, $m, PREG_OFFSET_CAPTURE)) {
            continue;
        }
        foreach ($m[0] as $i => $full) {
            $name = $m[3][$i][0];
            if (in_array($name, ['__construct', '__destruct', '__get', '__set', '__call', '__toString', '__invoke', 'boot', 'register'], true)) {
                continue;
            }
            $start = $full[1];
            $before = substr($code, max(0, $start - 500), min(500, $start));
            $hasDoc = preg_match('/\/\*\*.*?\*\/\s*$/s', $before) === 1;
            $hasCn = $hasDoc && preg_match('/[\x{4e00}-\x{9fff}]/u', $before) === 1;
            if ($hasCn) {
                $with++;
            } else {
                $missing++;
                if (count($samples) < 25) {
                    $samples[] = $path.'::'.$name;
                }
            }
        }
    }
}

echo "with_cn={$with} missing={$missing}\n";
foreach ($samples as $s) {
    echo $s."\n";
}
